<?php
/**
 * VNX Sitemap Helper
 * Các hàm tiện ích dùng chung cho sitemap
 */

if (!defined('ABSPATH')) {
    exit;
}

class VNX_Sitemap_Helper_Center
{
    private static $instance = null;
    private $original_upload_baseurl = null;
    private $base_priorities = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_settings();
    }

    /**
     * Load settings from database
     */
    private function load_settings()
    {
        $settings = get_option('vnx_sitemap_settings', []);
        
        // Load base_priorities từ settings, fallback về mảng rỗng
        if (!empty($settings['base_priorities']) && is_array($settings['base_priorities'])) {
            $this->base_priorities = $settings['base_priorities'];
        } else {
            $this->base_priorities = [];
        }
    }

    /**
     * Escape XML entities safe for sitemap
     */
    public function esc_xml($text)
    {
        if ($text === null) return '';
        $text = (string) $text;

        // Dùng preg_replace với unicode modifier để xử lý đúng UTF-8
        $text = preg_replace('/[\x{0000}-\x{0008}\x{000B}\x{000C}\x{000E}-\x{001F}]/u', '', $text);

        // Fallback nếu preg_replace fail (invalid UTF-8)
        if ($text === null) {
            $text = '';
        }

        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Check whether a post should be excluded via RankMath meta (noindex)
     */
    public function is_post_noindex($post_id)
    {
        $rm = get_post_meta($post_id, 'rank_math_robots', true);
        if ($rm) {
            if ((is_array($rm) && in_array('noindex', $rm)) || (is_string($rm) && strpos($rm, 'noindex') !== false)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Parse ISO 8601 duration to seconds
     * Example: PT1H30M15S = 5415 seconds
     */
    public function parse_duration($duration)
    {
        if (empty($duration)) {
            return 0;
        }

        $seconds = 0;

        // Match hours
        if (preg_match('/(\d+)H/', $duration, $matches)) {
            $seconds += (int) $matches[1] * 3600;
        }

        // Match minutes
        if (preg_match('/(\d+)M/', $duration, $matches)) {
            $seconds += (int) $matches[1] * 60;
        }

        // Match seconds
        if (preg_match('/(\d+)S/', $duration, $matches)) {
            $seconds += (int) $matches[1];
        }

        return $seconds;
    }

    /**
     * Convert GMT datetime to WordPress timezone in ISO 8601 format
     */
    public function format_lastmod_datetime($gmt_datetime)
    {
        if (empty($gmt_datetime)) {
            return $this->get_current_datetime_iso();
        }

        try {
            $date = new DateTime($gmt_datetime, new DateTimeZone('UTC'));
            $wp_timezone = wp_timezone();
            $date->setTimezone($wp_timezone);
            return $date->format('c');
        } catch (Exception $e) {
            return $this->get_current_datetime_iso();
        }
    }

    /**
     * Get current datetime in ISO 8601 format with WordPress timezone
     */
    public function get_current_datetime_iso()
    {
        $wp_timezone = wp_timezone();
        $date = new DateTime('now', $wp_timezone);
        return $date->format('c');
    }

    /**
     * Get original upload base URL (bypass CDN filters)
     */
    public function get_original_upload_baseurl()
    {
        if ($this->original_upload_baseurl !== null) {
            return $this->original_upload_baseurl;
        }

        global $wpdb;

        $site_url = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'siteurl' LIMIT 1");

        if (empty($site_url)) {
            $site_url = site_url();
        }

        $upload_path = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'upload_path' LIMIT 1");

        if (defined('UPLOADS')) {
            $upload_path = UPLOADS;
        } elseif (empty($upload_path) || $upload_path === 'wp-content/uploads') {
            $upload_path = 'wp-content/uploads';
        }

        $this->original_upload_baseurl = rtrim($site_url, '/') . '/' . trim($upload_path, '/');

        return $this->original_upload_baseurl;
    }

    

    /**
     * Get change frequency based on post age
     */
    public function get_changefreq($post_id)
    {
        $modified = get_post_modified_time('U', false, $post_id);
        $now = current_time('timestamp');
        $days = ($now - $modified) / (24 * 3600);

        if ($days < 1) return 'hourly';
        if ($days < 7) return 'daily';
        if ($days < 30) return 'weekly';
        if ($days < 365) return 'monthly';

        return 'yearly';
    }

    /**
     * Get changefreq for sitemap entries based on lastmod
     */
    public function get_sitemap_changefreq($post_type = null, $lastmod = null)
    {
        if (!empty($lastmod)) {
            $last_update = strtotime($lastmod);
            $now = time();
            $days_since_update = ($now - $last_update) / (24 * 3600);

            if ($days_since_update < 1) return 'hourly';
            if ($days_since_update < 7) return 'daily';
            if ($days_since_update < 30) return 'weekly';
            if ($days_since_update < 365) return 'monthly';
            return 'yearly';
        }
        return 'weekly';
    }

    /**
     * Get base priority by post type (loaded from settings)
     * Supports patterns like:
     * - "post" → exact post type match
     * - "post-1" → post type page 1
     * - "post-*" → post type all other pages (wildcard)
     */
    public function get_base_priority($post_type, $page_num = null)
    {
        // Nếu có page_num, thử match pattern cụ thể trước
        if ($page_num !== null) {
            // 1. Thử match chính xác: "post-1"
            $exact_key = $post_type . '-' . $page_num;
            if (isset($this->base_priorities[$exact_key])) {
                return number_format($this->base_priorities[$exact_key], 1);
            }
            
            // 2. Thử match wildcard: "post-*" (cho các page > 1 hoặc không match exact)
            $wildcard_key = $post_type . '-*';
            if (isset($this->base_priorities[$wildcard_key])) {
                return number_format($this->base_priorities[$wildcard_key], 1);
            }
        }
        
        // 3. Fallback về post_type gốc
        if (isset($this->base_priorities[$post_type])) {
            return number_format($this->base_priorities[$post_type], 1);
        }
        
        // 4. Default fallback
        return '0.5';
    }

    /**
     * Get priority for sitemap entries (index page)
     */
    public function get_priority($sitemap_type)
    {
        // Parse sitemap_type: "post-1", "post-2", "page", etc.
        if (preg_match('/^(.+)-(\d+)$/', $sitemap_type, $matches)) {
            $base_type = $matches[1];
            $page_num = (int) $matches[2];
            
            return $this->get_base_priority($base_type, $page_num);
        }

        // Không có page number
        return $this->get_base_priority($sitemap_type);
    }

    /**
     * Get priority for individual URLs within a sitemap
     */
    public function get_url_priority($post_type, $post_id = null, $page_number = 1)
    {
        return $this->get_base_priority($post_type, $page_number);
    }

    /**
     * Check if post type should be included in sitemap (RankMath aware)
     */
    public function should_include_post_type_in_sitemap($post_type)
    {
        if (!class_exists('RankMath')) {
            $excluded_types = ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation'];

            if (in_array($post_type, $excluded_types)) {
                return false;
            }

            $post_type_obj = get_post_type_object($post_type);
            return $post_type_obj && $post_type_obj->public;
        }

        $rm_options = get_option('rank-math-options-sitemap');
        if (!$rm_options) {
            return false;
        }

        $setting_key = 'pt_' . $post_type . '_sitemap';

        if (isset($rm_options[$setting_key])) {
            return $rm_options[$setting_key] === 'on' || $rm_options[$setting_key] === true;
        }

        return false;
    }

    /**
     * Get post thumbnail URL directly from SQL (bypass CDN)
     */
    public function get_thumbnail_url_direct($post_id)
    {
        global $wpdb;

        $thumbnail_id = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_thumbnail_id' LIMIT 1",
            $post_id
        ));

        if (empty($thumbnail_id)) {
            return null;
        }

        $file_path = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_wp_attached_file' LIMIT 1",
            (int) $thumbnail_id
        ));

        if (empty($file_path)) {
            return null;
        }

        return $this->get_original_upload_baseurl() . '/' . $file_path;
    }

    /**
     * Get attachment URL and alt text directly from SQL
     */
    public function get_attachment_data_direct($attachment_ids)
    {
        global $wpdb;

        $single_mode = !is_array($attachment_ids);
        if ($single_mode) {
            $attachment_ids = [$attachment_ids];
        }

        $attachment_ids = array_filter(array_map('intval', $attachment_ids));
        if (empty($attachment_ids)) {
            return $single_mode ? null : [];
        }

        $safe_ids = implode(',', $attachment_ids);

        $sql = "
            SELECT 
                p.ID,
                pm_file.meta_value as file_path,
                pm_alt.meta_value as alt_text
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm_file ON p.ID = pm_file.post_id AND pm_file.meta_key = '_wp_attached_file'
            LEFT JOIN {$wpdb->postmeta} pm_alt ON p.ID = pm_alt.post_id AND pm_alt.meta_key = '_wp_attachment_image_alt'
            WHERE p.post_type = 'attachment'
            AND p.ID IN ({$safe_ids})
        ";

        $results = $wpdb->get_results($sql);

        if (empty($results)) {
            return $single_mode ? null : [];
        }

        $upload_baseurl = $this->get_original_upload_baseurl();

        $output = [];
        foreach ($results as $row) {
            $output[(int) $row->ID] = [
                'url' => $upload_baseurl . '/' . $row->file_path,
                'alt' => $row->alt_text ?: ''
            ];
        }

        if ($single_mode) {
            $first_id = reset($attachment_ids);
            return isset($output[$first_id]) ? $output[$first_id] : null;
        }

        return $output;
    }

    /**
     * Get lastmod for a post type
     */
    public function get_lastmod($post_type)
    {
        global $wpdb;
        $last = $wpdb->get_var($wpdb->prepare(
            "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1",
            $post_type
        ));
        return $this->format_lastmod_datetime($last);
    }
}
