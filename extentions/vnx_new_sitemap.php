<?php

if (!defined('ABSPATH')) {
    exit;
}

// Load sitemap module classes
require_once __DIR__ . '/vnx-sitemap/loader.php';

final class VNX_Sitemap_Center
{
    private $posts_per_sitemap = 200;
    private $images_per_post = 500;
    private $TTL_cache = 1800;
    private static $instance = null;

    // Module instances
    private $helper;
    private $image_handler;
    private $video_handler;
    private $custom_page_handler;
    private $cache_handler;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Load settings 
        $this->load_settings();

        // Initialize module instances
        $this->helper = VNX_Sitemap_Helper_Center::instance();
        $this->image_handler = VNX_Sitemap_Image_Center::instance();
        $this->video_handler = VNX_Sitemap_Video_Center::instance();
        $this->custom_page_handler = VNX_Sitemap_Custom_Page_Center::instance();
        $this->cache_handler = VNX_Sitemap_Cache_Center::instance();

        // Pass settings to modules
        $this->image_handler->set_images_per_post($this->images_per_post);
        $this->video_handler->set_posts_per_sitemap($this->posts_per_sitemap);
        $this->custom_page_handler->set_posts_per_sitemap($this->posts_per_sitemap);
        $this->cache_handler->set_ttl_cache($this->TTL_cache);
        $this->cache_handler->set_posts_per_sitemap($this->posts_per_sitemap);
        $this->cache_handler->set_content_generator_callback([$this, 'generate_sitemap_content_with_headers']);
        $this->cache_handler->set_post_type_checker_callback([$this->helper, 'should_include_post_type_in_sitemap']);

        // Register hooks
        add_action('init', [$this, 'register_rewrite_rules']);
        add_action('init', [$this, 'maybe_flush_rewrites']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_sitemap_request'], 0);

        // Setup cron for automatic cache rebuild (delegate to cache handler)
        add_action('init', [$this->cache_handler, 'setup_cron']);
        add_action('vnx_sitemap_rebuild_cache', [$this->cache_handler, 'rebuild_all_cache']);
        add_filter('cron_schedules', [$this->cache_handler, 'add_cron_schedules']);

        // Activation hook will flush
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);
    }

    /** Activation - register rules and flush once */
    public function on_activation()
    {
        $this->register_rewrite_rules();
        flush_rewrite_rules();
        update_option('vnx_sitemap_flushed', '1');

        // Setup cron on activation
        $this->cache_handler->setup_cron();
    }

    public function on_deactivation()
    {
        delete_option('vnx_sitemap_flushed');
        flush_rewrite_rules();

        // Clear cron on deactivation
        $this->cache_handler->clear_cron();
    }


    public function register_rewrite_rules()
    {
        add_rewrite_rule('^vnx_sitemap\.xml$', 'index.php?vnx_sitemap=index', 'top');
        add_rewrite_rule('^vnx_sitemap-([^/]+?)-([0-9]+)\.xml$', 'index.php?vnx_sitemap=$matches[1]&sitemap_page=$matches[2]', 'top');
        add_rewrite_rule('^vnx_sitemap-([^/]+?)\.xml$', 'index.php?vnx_sitemap=$matches[1]', 'top');
        add_rewrite_tag('%vnx_sitemap%', '([^&]+)');
        add_rewrite_tag('%sitemap_page%', '([0-9]+)');
    }

    public function maybe_flush_rewrites()
    {
        $flushed = get_option('vnx_sitemap_flushed', '0');
        if ($flushed !== '1') {
            $this->register_rewrite_rules();
            flush_rewrite_rules(false);
            update_option('vnx_sitemap_flushed', '1');
        }
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'vnx_sitemap';
        $vars[] = 'sitemap_page';
        return $vars;
    }

    public function handle_sitemap_request()
    {
        $sitemap_type = get_query_var('vnx_sitemap');
        if (empty($sitemap_type))
            return;

        // Generate cache key
        $page_num = (int) get_query_var('sitemap_page', 1);
        if ($page_num < 1) {
            $page_num = 1;
        }

        // Try to get cached content using cache handler
        $cached_content = $this->cache_handler->get_cached_content($sitemap_type, $page_num);
        if ($cached_content !== false) {
            // Serve from cache
            status_header(200);
            header('Content-Type: application/xml; charset=UTF-8');
            header('Cache-Control: max-age=' . $this->TTL_cache . ', public');
            header('X-VNX-Cache: HIT');
            echo $cached_content;
            exit;
        }

        // Cache miss - generate fresh content
        status_header(200);
        header('Content-Type: application/xml; charset=UTF-8');
        header('Cache-Control: max-age=' . $this->TTL_cache . ', public');
        header('X-VNX-Cache: MISS');
        // ETag for browser caching (without version - based on content only)
        $etag = md5($sitemap_type . '-' . $page_num);
        header('ETag: "' . $etag . '"');

        // Check if client has cached version
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
            status_header(304);
            exit;
        }

        // Start output buffering to capture XML
        ob_start();

        // Output XML declaration once
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        if ($sitemap_type === 'index') {
            $this->generate_sitemap_index();
        } else {
            // Parse post_type and page number from query vars
            $post_type = $sitemap_type;

            // Handle video sitemap separately (not a real post type)
            if ($post_type === 'video') {
                echo $this->video_handler->generate($page_num);
            } elseif ($post_type === 'san-pham') {
                // Handle custom page sitemap (san-pham with ACF)
                echo $this->custom_page_handler->generate($page_num);
            } else {
                // Validate post type exists
                if (!post_type_exists($post_type)) {
                    ob_end_clean();
                    status_header(404);
                    echo '<error>Invalid sitemap</error>';
                    exit;
                }

                // Check if post type is enabled in RankMath
                if (!$this->helper->should_include_post_type_in_sitemap($post_type)) {
                    ob_end_clean();
                    status_header(404);
                    echo '<error>Post type not enabled in sitemap</error>';
                    exit;
                }

                $this->generate_sitemap($post_type, $page_num);
            }
        }

        // Get generated content and cache it
        $xml_content = ob_get_contents();
        ob_end_flush(); // Send to browser

        // Cache for TTL_cache seconds (30 minutes default) using cache handler
        if (!empty($xml_content)) {
            $this->cache_handler->set_cached_content($sitemap_type, $page_num, $xml_content);
        }

        exit;
    }

    /** Helper: Add sitemap entries with pagination support - lastmod per page */
    private function add_sitemap_entries($base_url, $type, $count, $lastmod)
    {
        $entries = [];
        $total_pages = (int) ceil($count / $this->posts_per_sitemap);

        if ($total_pages <= 1) {
            $sitemap_name = $type;
            $entries[] = [
                'loc' => "{$base_url}/vnx_sitemap-{$type}.xml",
                'lastmod' => $lastmod,
                'priority' => $this->helper->get_priority($sitemap_name),
                'changefreq' => $this->helper->get_sitemap_changefreq($type, $lastmod),
            ];
        } else {
            for ($i = 1; $i <= $total_pages; $i++) {
                $sitemap_name = "{$type}-{$i}";
                $page_lastmod = $this->get_lastmod_for_page($type, $i);
                $entries[] = [
                    'loc' => "{$base_url}/vnx_sitemap-{$type}-{$i}.xml",
                    'lastmod' => $page_lastmod,
                    'priority' => $this->helper->get_priority($sitemap_name),
                    'changefreq' => $this->helper->get_sitemap_changefreq($type, $page_lastmod),
                ];
            }
        }

        return $entries;
    }

    /**
     * Lấy lastmod của bài viết mới nhất trong một page cụ thể
     * Trả về ISO 8601 datetime với timezone WordPress
     */
    private function get_lastmod_for_page($post_type, $page)
    {
        global $wpdb;

        $offset = ($page - 1) * $this->posts_per_sitemap;

        // Xử lý riêng cho video sitemap
        if ($post_type === 'video') {
            $lastmod = $wpdb->get_var($wpdb->prepare("
                SELECT p.post_modified_gmt
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_status = 'publish'
                AND p.post_type IN ('post', 'page')
                AND pm.meta_key = 'rank_math_schema_VideoObject'
                AND pm.meta_value != ''
                ORDER BY p.post_modified_gmt DESC
                LIMIT 1 OFFSET %d
            ", $offset));

            return $this->helper->format_lastmod_datetime($lastmod);
        }

        // Xử lý riêng cho san-pham (custom page sitemap)
        if ($post_type === 'san-pham') {
            $pages = get_posts([
                'post_type' => 'page',
                'post_status' => 'publish',
                'meta_key' => 'vnx_check_product_page',
                'meta_value' => 'san-pham',
                'meta_compare' => 'LIKE',
                'posts_per_page' => 1,
                'offset' => $offset,
                'orderby' => 'modified',
                'order' => 'DESC'
            ]);

            if (!empty($pages)) {
                // Lấy timestamp GMT và convert sang WP timezone
                $gmt_time = get_post_modified_time('Y-m-d H:i:s', true, $pages[0]);
                return $this->helper->format_lastmod_datetime($gmt_time);
            }
            return $this->helper->get_current_datetime_iso();
        }

        // Xử lý cho các post type thông thường
        $lastmod = $wpdb->get_var($wpdb->prepare(
            "SELECT post_modified_gmt 
             FROM {$wpdb->posts} 
             WHERE post_type = %s 
             AND post_status = 'publish' 
             ORDER BY post_modified_gmt DESC 
             LIMIT 1 OFFSET %d",
            $post_type,
            $offset
        ));

        return $this->helper->format_lastmod_datetime($lastmod);
    }

    /** Generate sitemap index for all enabled post types (pagination aware) */
    private function generate_sitemap_index()
    {
        $base_url = site_url();
        $entries = [];

        // Default builtins to include
        $post_types = ['post', 'page'];
        // Merge public custom types (exclude attachments)
        $cpts = get_post_types(['public' => true, '_builtin' => false], 'names');
        $post_types = array_merge($post_types, $cpts);

        global $wpdb;

        foreach ($post_types as $pt) {
            // Check if post type is enabled in RankMath
            if (!$this->helper->should_include_post_type_in_sitemap($pt)) {
                continue;
            }

            $count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'", $pt));
            if ($count <= 0)
                continue;

            $entries = array_merge($entries, $this->add_sitemap_entries($base_url, $pt, $count, $this->helper->get_lastmod($pt)));
        }

        // Video sitemap (if RankMath video sitemap is enabled)
        if ($this->video_handler->should_include()) {
            $video_count = $this->video_handler->get_count();
            if ($video_count > 0) {
                $entries = array_merge($entries, $this->add_sitemap_entries($base_url, 'video', $video_count, $this->video_handler->get_lastmod()));
            }
        }

        // Custom page sitemap (san-pham with ACF)
        if ($this->custom_page_handler->should_include()) {
            $custom_page_count = $this->custom_page_handler->get_count();
            if ($custom_page_count > 0) {
                $entries = array_merge($entries, $this->add_sitemap_entries($base_url, 'san-pham', $custom_page_count, $this->custom_page_handler->get_lastmod()));
            }
        }

        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($entries as $e) {
            echo "\t<sitemap>\n";
            echo "\t\t<loc>" . $this->helper->esc_xml($e['loc']) . "</loc>\n";
            if (!empty($e['lastmod'])) {
                echo "\t\t<lastmod>" . $this->helper->esc_xml($e['lastmod']) . "</lastmod>\n";
            } 
            echo "\t</sitemap>\n";
        }
        echo '</sitemapindex>';
    }

    /** Generate sitemap for a post_type (fast SQL-driven) */
    private function generate_sitemap($post_type, $page = 1)
    {
        global $wpdb;

        $offset = ($page - 1) * $this->posts_per_sitemap;

        // ORDER BY lastmod DESC to show newest first
        $sql = $wpdb->prepare(
            "SELECT ID, post_modified_gmt FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT %d, %d",
            $post_type,
            $offset,
            $this->posts_per_sitemap
        );

        $rows = $wpdb->get_results($sql);

        if (empty($rows)) {
            status_header(404);
            echo '<error>No posts found</error>';
            return;
        }

        $post_ids = array_map(function ($r) {
            return (int) $r->ID;
        }, $rows);

        // Exclude pages with ACF field vnx_check_product_page='san-pham'
        if ($post_type === 'page') {
            $excluded_pages = $wpdb->get_col($wpdb->prepare("
                SELECT post_id 
                FROM {$wpdb->postmeta} 
                WHERE meta_key = 'vnx_check_product_page' 
                AND meta_value = 'san-pham'
                AND post_id IN (" . implode(',', $post_ids) . ")
            "));

            if (!empty($excluded_pages)) {
                // Remove excluded pages from rows
                $rows = array_filter($rows, function ($r) use ($excluded_pages) {
                    return !in_array($r->ID, $excluded_pages);
                });

                // Update post_ids for image fetching
                $post_ids = array_map(function ($r) {
                    return (int) $r->ID;
                }, $rows);
            }
        }

        $all_images = $this->image_handler->batch_get_all_images($post_ids);

        // Output header of urlset (including image namespace)
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        foreach ($rows as $r) {
            $post_id = (int) $r->ID;

            // RankMath-aware: skip noindex or excluded posts
            if ($this->helper->is_post_noindex($post_id))
                continue;

            // Prepare loc and lastmod (convert GMT to WordPress timezone)
            $loc = get_permalink($post_id);
            $lastmod = $this->helper->format_lastmod_datetime($r->post_modified_gmt);

            echo "\t<url>\n";
            echo "\t\t<loc>" . $this->helper->esc_xml($loc) . "</loc>\n";
            echo "\t\t<lastmod>" . $this->helper->esc_xml($lastmod) . "</lastmod>\n";
            
            $url_priority = $this->helper->get_url_priority($post_type, $post_id, $page);
            echo "\t\t<priority>" . $url_priority . "</priority>\n";

            $changefreq = $this->helper->get_changefreq($post_id);
            echo "\t\t<changefreq>" . $changefreq . "</changefreq>\n";

            // Images: use pre-fetched batch data
            $images = isset($all_images[$post_id]) ? $all_images[$post_id] : [];
            if (!empty($images)) {
                foreach ($images as $img) {
                    echo "\t\t<image:image>\n";
                    echo "\t\t\t<image:loc>" . $this->helper->esc_xml($img['url']) . "</image:loc>\n";
                    if (!empty($img['title'])) {
                        echo "\t\t\t<image:title>" . $this->helper->esc_xml($img['title']) . "</image:title>\n";
                    }
                    echo "\t\t</image:image>\n";
                }
            }

            echo "\t</url>\n";
        }

        echo '</urlset>';
    }

    /**
     * Generate sitemap content with proper XML headers
     * Used as callback for cache handler
     * 
     * @param string $type Sitemap type identifier (e.g., 'post_1', 'index_1')
     * @return string XML content
     */
    public function generate_sitemap_content_with_headers($type)
    {
        // Parse type: "post_1", "index_1", etc.
        $parts = explode('_', $type);
        $sitemap_type = $parts[0];
        $page_num = isset($parts[1]) ? (int) $parts[1] : 1;

        ob_start();

        // XML headers
        echo '<?xml version="1.0" encoding="UTF-8"?>';

        // Generate content based on type
        if ($sitemap_type === 'index') {
            $this->generate_sitemap_index();
        } elseif ($sitemap_type === 'video') {
            echo $this->video_handler->generate($page_num);
        } elseif ($sitemap_type === 'san-pham') {
            echo $this->custom_page_handler->generate($page_num);
        } elseif (post_type_exists($sitemap_type)) {
            $this->generate_sitemap($sitemap_type, $page_num);
        }

        return ob_get_clean();
    }

    /**
     * Load settings from database (saved by vietnix_sitemap_settings.php form)
     */
    private function load_settings()
    {
        $settings = get_option('vnx_sitemap_settings', []);

        if (!empty($settings['ttl_cache'])) {
            $this->TTL_cache = (int) $settings['ttl_cache'];
        }

        if (!empty($settings['posts_per_sitemap'])) {
            $this->posts_per_sitemap = (int) $settings['posts_per_sitemap'];
        }

        if (!empty($settings['images_per_post'])) {
            $this->images_per_post = (int) $settings['images_per_post'];
        }
    }
}

VNX_Sitemap_Center::instance();