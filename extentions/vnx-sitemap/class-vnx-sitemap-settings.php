<?php
/**
 * VNX Sitemap Settings Handler
 * Xử lý lưu và load settings cho sitemap
 */

if (!defined('ABSPATH')) {
    exit;
}

class VNX_Sitemap_Settings_Center
{
    private static $instance = null;
    
    /**
     * Option name trong database
     */
    const OPTION_NAME = 'vnx_sitemap_settings';
    
    /**
     * Default settings
     */
    private $defaults = [
        'ttl_cache' => 1800,
        'posts_per_sitemap' => 200,
        'images_per_post' => 500,
        'base_priorities' => [],
        'sitemap_accept_domains' => [],
    ];

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Get all settings
     * 
     * @return array
     */
    public function get_settings()
    {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        
        // Đảm bảo tất cả keys tồn tại
        return wp_parse_args($settings, $this->defaults);
    }

    /**
     * Get single setting value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $settings = $this->get_settings();
        
        if (isset($settings[$key])) {
            return $settings[$key];
        }
        
        return $default !== null ? $default : ($this->defaults[$key] ?? null);
    }

    /**
     * Save settings from POST data
     * 
     * @param array $post_data $_POST data
     * @return array ['success' => bool, 'message' => string]
     */
    public function save_from_post($post_data)
    {
        // Xử lý base_priorities từ form
        $base_priorities = $this->parse_base_priorities($post_data);
        
        // Xử lý sitemap_accept_domain từ textarea (mỗi domain một dòng)
        $accept_domains = $this->parse_accept_domains($post_data['sitemap_accept_domain'] ?? '');

        $new_settings = [
            'ttl_cache' => $this->sanitize_ttl_cache($post_data['ttl_cache'] ?? 1800),
            'posts_per_sitemap' => $this->sanitize_posts_per_sitemap($post_data['posts_per_sitemap'] ?? 200),
            'images_per_post' => $this->sanitize_images_per_post($post_data['images_per_post'] ?? 500),
            'base_priorities' => $base_priorities,
            'sitemap_accept_domains' => $accept_domains,
        ];

        $updated = update_option(self::OPTION_NAME, $new_settings);

        return [
            'success' => true,
            'message' => 'Cài đặt đã được lưu thành công!',
            'settings' => $new_settings
        ];
    }

    /**
     * Parse base_priorities từ form arrays
     * 
     * @param array $post_data
     * @return array
     */
    private function parse_base_priorities($post_data)
    {
        $base_priorities = [];
        
        if (empty($post_data['priority_type']) || empty($post_data['priority_value'])) {
            return $base_priorities;
        }

        $types = $post_data['priority_type'];
        $values = $post_data['priority_value'];

        foreach ($types as $i => $type) {
            $type = sanitize_text_field(trim($type));
            
            if (!empty($type) && isset($values[$i])) {
                $value = floatval($values[$i]);
                $value = max(0.0, min(1.0, $value)); // Giới hạn 0.0 - 1.0
                $base_priorities[$type] = round($value, 1);
            }
        }

        return $base_priorities;
    }

    /**
     * Parse accept domains từ textarea (mỗi domain một dòng)
     * 
     * @param string $textarea_value
     * @return array
     */
    private function parse_accept_domains($textarea_value)
    {
        if (empty($textarea_value)) {
            return [];
        }

        $lines = explode("\n", $textarea_value);
        $domains = [];

        foreach ($lines as $line) {
            $domain = trim($line);
            
            if (empty($domain)) {
                continue;
            }

            // Chuẩn hóa domain: loại bỏ http://, https://, trailing slash
            $domain = preg_replace('#^https?://#i', '', $domain);
            $domain = rtrim($domain, '/');
            $domain = strtolower($domain);

            if (!empty($domain)) {
                $domains[] = $domain;
            }
        }

        return array_unique($domains);
    }

    public function get_accept_domains_string()
    {
        $domains = $this->get('sitemap_accept_domains', []);
        
        if (empty($domains) || !is_array($domains)) {
            return '';
        }

        return implode("\n", $domains);
    }

    /**
     * Sanitize TTL cache value
     * 
     * @param mixed $value
     * @return int
     */
    private function sanitize_ttl_cache($value)
    {
        $value = intval($value);
        return max(60, min(86400, $value)); // 60 giây - 24 giờ
    }

    /**
     * Sanitize posts per sitemap value
     * 
     * @param mixed $value
     * @return int
     */
    private function sanitize_posts_per_sitemap($value)
    {
        $value = intval($value);
        return max(10, min(1000, $value));
    }

    /**
     * Sanitize images per post value
     * 
     * @param mixed $value
     * @return int
     */
    private function sanitize_images_per_post($value)
    {
        $value = intval($value);
        return max(0, min(1000, $value));
    }

    /**
     * Clear cache và rebuild
     * 
     * @return array ['success' => bool, 'message' => string]
     */
    public function clear_and_rebuild_cache()
    {
        // Chay song song voi vietnix-plugin thi center khong nap module sitemap (vietnix-plugin
        // phuc vu sitemap). Transient dung chung ten nen nho cron rebuild cua vietnix-plugin
        // (cung hook vnx_sitemap_rebuild_cache) dung lai cache ngay.
        if (!class_exists('VNX_Sitemap_Cache_Center') && function_exists('vnx_center_companion_mode') && vnx_center_companion_mode()) {
            if (!has_action('vnx_sitemap_rebuild_cache')) {
                return [
                    'success' => false,
                    'message' => 'Sitemap đang do vietnix-plugin phục vụ nhưng extension sitemap của nó chưa bật, không rebuild được.'
                ];
            }

            do_action('vnx_sitemap_rebuild_cache');

            return [
                'success' => true,
                'message' => 'Đã rebuild cache sitemap (qua vietnix-plugin đang chạy song song).'
            ];
        }

        if (!class_exists('VNX_Sitemap_Cache_Center')) {
            return [
                'success' => false,
                'message' => 'Lỗi: VNX_Sitemap_Cache_Center class không tồn tại!'
            ];
        }

        global $wpdb;
        
        // Xóa cache cũ
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_vnx_sitemap_%' 
             OR option_name LIKE '_transient_timeout_vnx_sitemap_%'"
        );
        
        // Rebuild cache mới thông qua cache handler
        $cache_handler = VNX_Sitemap_Cache_Center::instance();
        $result = $cache_handler->rebuild_all_cache();

        return [
            'success' => true,
            'message' => "Đã xóa {$deleted} transient và rebuild cache thành công!",
            'deleted_count' => $deleted,
            'rebuild_result' => $result
        ];
    }

    /**
     * Reset settings về default
     * 
     * @return array
     */
    public function reset_to_defaults()
    {
        delete_option(self::OPTION_NAME);
        
        return [
            'success' => true,
            'message' => 'Đã reset settings về mặc định!',
            'settings' => $this->defaults
        ];
    }

    /**
     * Get default settings
     * 
     * @return array
     */
    public function get_defaults()
    {
        return $this->defaults;
    }
}
