<?php
/**
 * VNX Sitemap Custom Page Handler
 * Xử lý tạo sitemap cho các trang sản phẩm (ACF custom pages)
 */

if (!defined('ABSPATH')) {
    exit;
}

class VNX_Sitemap_Custom_Page_Center
{
    private static $instance = null;
    private $helper;
    private $image_handler;
    private $posts_per_sitemap = 200;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->helper = VNX_Sitemap_Helper_Center::instance();
        $this->image_handler = VNX_Sitemap_Image_Center::instance();
    }

    /**
     * Set posts per sitemap limit 
     */
    public function set_posts_per_sitemap($limit)
    {
        $this->posts_per_sitemap = (int) $limit;
    }

    /**
     * Check if should include custom page sitemap (ACF-based)
     */
    public function should_include()
    {
        // Check if ACF field exists
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'meta_key' => 'vnx_check_product_page',
            'meta_value' => 'san-pham',
            'meta_compare' => 'LIKE',
            'posts_per_page' => 1,
            'fields' => 'ids'
        ]);

        return !empty($pages);
    }

    /**
     * Get count of custom pages
     */
    public function get_count()
    {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'meta_key' => 'vnx_check_product_page',
            'meta_value' => 'san-pham',
            'meta_compare' => 'LIKE',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        return count($pages);
    }

    /**
     * Get lastmod for custom pages
     * Trả về ISO 8601 datetime với timezone WordPress
     */
    public function get_lastmod()
    {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'meta_key' => 'vnx_check_product_page',
            'meta_value' => 'san-pham',
            'meta_compare' => 'LIKE',
            'posts_per_page' => 1,
            'orderby' => 'modified',
            'order' => 'DESC'
        ]);

        if (!empty($pages)) {
            // Lấy GMT time và convert sang WP timezone
            $gmt_time = get_post_modified_time('Y-m-d H:i:s', true, $pages[0]);
            return $this->helper->format_lastmod_datetime($gmt_time);
        }

        return $this->helper->get_current_datetime_iso();
    }

    /**
     * Generate custom page sitemap (san-pham)
     * 
     * @param int $page Page number
     * @return string XML content
     */
    public function generate($page = 1)
    {
        global $wpdb;

        $offset = ($page - 1) * $this->posts_per_sitemap;

        // Get pages with ACF field - sorted by lastmod DESC
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'meta_key' => 'vnx_check_product_page',
            'meta_value' => 'san-pham',
            'meta_compare' => 'LIKE',
            'posts_per_page' => $this->posts_per_sitemap,
            'offset' => $offset,
            'orderby' => 'modified',
            'order' => 'DESC'
        ]);

        if (empty($pages)) {
            return '<error>No san-pham pages found</error>';
        }

        // OPTIMIZATION: Batch fetch all images for all pages at once
        $page_ids = array_map(function ($p) {
            return (int) $p->ID;
        }, $pages);
        $all_images = $this->image_handler->batch_get_all_images($page_ids);

        ob_start();
        // Output XML
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        foreach ($pages as $page_item) {
            // Check noindex
            if ($this->helper->is_post_noindex($page_item->ID)) {
                continue;
            }

            // Lấy lastmod với timezone WordPress
            $gmt_time = get_post_modified_time('Y-m-d H:i:s', true, $page_item);
            $lastmod = $this->helper->format_lastmod_datetime($gmt_time);

            echo "\t<url>\n";
            echo "\t\t<loc>" . $this->helper->esc_xml(get_permalink($page_item->ID)) . "</loc>\n";
            echo "\t\t<lastmod>" . $this->helper->esc_xml($lastmod) . "</lastmod>\n";

            // Add priority and changefreq for custom pages
            $url_priority = $this->helper->get_url_priority('san-pham', $page_item->ID, 1);
            echo "\t\t<priority>" . $url_priority . "</priority>\n";
            $changefreq = $this->helper->get_changefreq($page_item->ID);
            echo "\t\t<changefreq>" . $changefreq . "</changefreq>\n";

            // Images: use pre-fetched batch data
            $images = isset($all_images[$page_item->ID]) ? $all_images[$page_item->ID] : [];
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
        return ob_get_clean();
    }
}
