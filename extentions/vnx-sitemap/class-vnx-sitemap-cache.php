<?php
/**
 * VNX Sitemap Cache Handler
 * 
 * Handles cache management, cron scheduling, and cache rebuilding for sitemaps.
 * 
 * @package VNX_Sitemap_Center
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class VNX_Sitemap_Cache_Center
{
    private static $instance = null;

    private $TTL_cache = 1800; // 30 minutes default
    private $posts_per_sitemap = 200;

    // Dependencies
    private $helper;
    private $video_handler;
    private $custom_page_handler;

    // Callback for content generation
    private $content_generator_callback = null;
    private $post_type_checker_callback = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Initialize dependencies
        $this->helper = VNX_Sitemap_Helper_Center::instance();
        $this->video_handler = VNX_Sitemap_Video_Center::instance();
        $this->custom_page_handler = VNX_Sitemap_Custom_Page_Center::instance();
    }

    /**
     * Set TTL cache time (called from main VNX_Sitemap_Center class)
     * 
     * @param int $ttl TTL in seconds
     */
    public function set_ttl_cache($ttl)
    {
        $this->TTL_cache = (int) $ttl;
    }

    /**
     * Get TTL cache time
     * 
     * @return int
     */
    public function get_ttl_cache()
    {
        return $this->TTL_cache;
    }

    /**
     * Set posts per sitemap
     * 
     * @param int $count Number of posts per sitemap
     */
    public function set_posts_per_sitemap($count)
    {
        $this->posts_per_sitemap = (int) $count;
    }

    /**
     * Set content generator callback for generating sitemap content
     * 
     * @param callable $callback Callback function that generates sitemap content
     */
    public function set_content_generator_callback($callback)
    {
        $this->content_generator_callback = $callback;
    }

    /**
     * Set post type checker callback
     * 
     * @param callable $callback Callback function to check if post type should be in sitemap
     */
    public function set_post_type_checker_callback($callback)
    {
        $this->post_type_checker_callback = $callback;
    }

    /**
     * Get cached sitemap content
     * 
     * @param string $sitemap_type Sitemap type (e.g., 'post', 'page', 'video')
     * @param int $page_num Page number
     * @return string|false Cached content or false if not found
     */
    public function get_cached_content($sitemap_type, $page_num = 1)
    {
        $cache_key = $this->get_cache_key($sitemap_type, $page_num);
        return get_transient($cache_key);
    }

    /**
     * Set cached sitemap content
     * 
     * @param string $sitemap_type Sitemap type
     * @param int $page_num Page number
     * @param string $content XML content
     * @return bool Success status
     */
    public function set_cached_content($sitemap_type, $page_num, $content)
    {
        if (empty($content)) {
            return false;
        }

        $cache_key = $this->get_cache_key($sitemap_type, $page_num);
        return set_transient($cache_key, $content, $this->TTL_cache);
    }

    /**
     * Generate cache key for sitemap
     * 
     * @param string $sitemap_type Sitemap type
     * @param int $page_num Page number
     * @return string Cache key
     */
    public function get_cache_key($sitemap_type, $page_num = 1)
    {
        return "vnx_sitemap_{$sitemap_type}_{$page_num}";
    }

    /**
     * Clear all sitemap cache
     * 
     * @return int Number of deleted cache entries
     */
    public function clear_all_cache()
    {
        global $wpdb;

        $deleted_count = $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_vnx_sitemap_%' 
             OR option_name LIKE '_transient_timeout_vnx_sitemap_%'"
        );

        return $deleted_count;
    }

    /**
     * Clear cache for specific sitemap type
     * 
     * @param string $sitemap_type Sitemap type
     * @return bool Success status
     */
    public function clear_cache_for_type($sitemap_type)
    {
        global $wpdb;

        $pattern = "_transient_vnx_sitemap_{$sitemap_type}_%";
        $timeout_pattern = "_transient_timeout_vnx_sitemap_{$sitemap_type}_%";

        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $pattern,
            $timeout_pattern
        ));

        return true;
    }

    /**
     * Setup WordPress cron for automatic cache rebuild
     */
    public function setup_cron()
    {
        if (!wp_next_scheduled('vnx_sitemap_rebuild_cache')) {
            wp_schedule_event(time(), 'vnx_sitemap_30min', 'vnx_sitemap_rebuild_cache');
        }
    }

    /**
     * Clear cron schedule
     */
    public function clear_cron()
    {
        wp_clear_scheduled_hook('vnx_sitemap_rebuild_cache');
    }

    /**
     * Add custom cron schedule for sitemap rebuild
     * 
     * @param array $schedules Existing cron schedules
     * @return array Modified schedules
     */
    public function add_cron_schedules($schedules)
    {
        $schedules['vnx_sitemap_30min'] = [
            'interval' => $this->TTL_cache,
            'display' => __('Every 30 Minutes (VNX Sitemap)')
        ];
        return $schedules;
    }

    /**
     * Rebuild all sitemap cache efficiently
     * 
     * @return array Statistics about the rebuild process
     */
    public function rebuild_all_cache()
    {
        global $wpdb;
        $start_time = microtime(true);

        // Get list of current sitemaps to rebuild
        $sitemap_types = $this->get_all_sitemap_types();
        $rebuilt_count = 0;
        $failed_count = 0;

        // Pre-generate fresh content for each sitemap BEFORE clearing old cache
        $fresh_content = [];
        foreach ($sitemap_types as $type) {
            try {
                $content = $this->generate_sitemap_content($type);
                if (!empty($content)) {
                    $fresh_content[$type] = $content;
                    $rebuilt_count++;
                } else {
                    $failed_count++;
                }
            } catch (Exception $e) {
                $failed_count++;
                error_log("[VNX Sitemap] Failed to rebuild {$type}: " . $e->getMessage());
            }
        }

        // Clear old cache ONLY after new content is ready
        $deleted_count = $this->clear_all_cache();

        // Set fresh cache
        $cached_count = 0;
        foreach ($fresh_content as $type => $content) {
            $cache_key = "vnx_sitemap_{$type}";
            if (set_transient($cache_key, $content, $this->TTL_cache)) {
                $cached_count++;
            }
        }

        $total_time = round((microtime(true) - $start_time) * 1000, 2);

        // Log summary only
        error_log('[VNX Sitemap] Cache rebuilt - Generated: ' . $rebuilt_count . ', Cached: ' . $cached_count . ', Time: ' . $total_time . 'ms');

        return [
            'rebuilt' => $rebuilt_count,
            'cached' => $cached_count,
            'deleted' => $deleted_count,
            'failed' => $failed_count,
            'time_ms' => $total_time
        ];
    }

    /**
     * Get all sitemap types that should be rebuilt
     * 
     * @return array List of sitemap type identifiers
     */
    public function get_all_sitemap_types()
    {
        global $wpdb;
        $types = [];

        // Index sitemap
        $types[] = 'index_1';

        // Post type sitemaps
        $post_types = ['post', 'page'];
        $cpts = get_post_types(['public' => true, '_builtin' => false], 'names');
        $post_types = array_merge($post_types, $cpts);

        foreach ($post_types as $pt) {
            // Use callback to check if post type should be included
            if ($this->post_type_checker_callback && !call_user_func($this->post_type_checker_callback, $pt)) {
                continue;
            }

            $count = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
                $pt
            ));

            if ($count <= 0)
                continue;

            // Calculate pages
            $total_pages = (int) ceil($count / $this->posts_per_sitemap);

            if ($total_pages <= 1) {
                $types[] = "{$pt}_1";
            } else {
                for ($i = 1; $i <= $total_pages; $i++) {
                    $types[] = "{$pt}_{$i}";
                }
            }
        }

        // Video sitemap
        if ($this->video_handler->should_include()) {
            $video_count = $this->video_handler->get_count();
            if ($video_count > 0) {
                $video_pages = (int) ceil($video_count / $this->posts_per_sitemap);
                for ($i = 1; $i <= $video_pages; $i++) {
                    $types[] = "video_{$i}";
                }
            }
        }

        // Custom page sitemap
        if ($this->custom_page_handler->should_include()) {
            $custom_count = $this->custom_page_handler->get_count();
            if ($custom_count > 0) {
                $custom_pages = (int) ceil($custom_count / $this->posts_per_sitemap);
                for ($i = 1; $i <= $custom_pages; $i++) {
                    $types[] = "san-pham_{$i}";
                }
            }
        }

        return $types;
    }

    /**
     * Generate sitemap content with proper XML headers
     * 
     * @param string $type Sitemap type identifier (e.g., 'post_1', 'index_1')
     * @return string XML content
     */
    public function generate_sitemap_content($type)
    {
        // If callback is set, use it
        if ($this->content_generator_callback) {
            return call_user_func($this->content_generator_callback, $type);
        }

        // Parse type: "post_1", "index_1", etc.
        $parts = explode('_', $type);
        $sitemap_type = $parts[0];
        $page_num = isset($parts[1]) ? (int) $parts[1] : 1;

        ob_start();

        // XML headers
        echo '<?xml version="1.0" encoding="UTF-8"?>';

        // Generate content based on type
        if ($sitemap_type === 'video') {
            echo $this->video_handler->generate($page_num);
        } elseif ($sitemap_type === 'san-pham') {
            echo $this->custom_page_handler->generate($page_num);
        }
        // Note: index and post types need to be handled by callback or main class

        return ob_get_clean();
    }

    /**
     * Check if sitemap cache exists and is valid
     * 
     * @param string $sitemap_type Sitemap type
     * @param int $page_num Page number
     * @return bool True if valid cache exists
     */
    public function has_valid_cache($sitemap_type, $page_num = 1)
    {
        $cached = $this->get_cached_content($sitemap_type, $page_num);
        return $cached !== false && !empty($cached);
    }

    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public function get_cache_stats()
    {
        global $wpdb;

        $total_cache = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_vnx_sitemap_%' 
             AND option_name NOT LIKE '_transient_timeout_%'"
        );

        $cache_size = $wpdb->get_var(
            "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_vnx_sitemap_%' 
             AND option_name NOT LIKE '_transient_timeout_%'"
        );

        return [
            'total_entries' => $total_cache,
            'total_size_bytes' => (int) $cache_size,
            'total_size_kb' => round((int) $cache_size / 1024, 2),
            'ttl_seconds' => $this->TTL_cache,
            'ttl_minutes' => round($this->TTL_cache / 60, 1)
        ];
    }

    /**
     * Invalidate cache when post is updated
     * 
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     */
    public function invalidate_on_post_update($post_id, $post)
    {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        $post_type = get_post_type($post_id);
        if (!$post_type) {
            return;
        }

        // Clear cache for the specific post type
        $this->clear_cache_for_type($post_type);

        // Also clear index cache
        $this->clear_cache_for_type('index');

        // Clear video cache if post has video schema
        $video_schema = get_post_meta($post_id, 'rank_math_schema_VideoObject', true);
        if (!empty($video_schema)) {
            $this->clear_cache_for_type('video');
        }

        // Clear san-pham cache if it's a product page
        $product_page = get_post_meta($post_id, 'vnx_check_product_page', true);
        if ($product_page === 'san-pham') {
            $this->clear_cache_for_type('san-pham');
        }
    }

    /**
     * Register hooks for automatic cache invalidation
     */
    public function register_invalidation_hooks()
    {
        add_action('save_post', [$this, 'invalidate_on_post_update'], 10, 2);
        add_action('delete_post', [$this, 'invalidate_on_post_delete']);
        add_action('trash_post', [$this, 'invalidate_on_post_delete']);
    }

    /**
     * Invalidate cache when post is deleted
     * 
     * @param int $post_id Post ID
     */
    public function invalidate_on_post_delete($post_id)
    {
        $post_type = get_post_type($post_id);
        if (!$post_type) {
            return;
        }

        $this->clear_cache_for_type($post_type);
        $this->clear_cache_for_type('index');
    }

    /**
     * Warm up cache for all sitemaps
     * Useful after clearing cache or on activation
     * 
     * @return array Results of warmup
     */
    public function warmup_cache()
    {
        return $this->rebuild_all_cache();
    }
}
