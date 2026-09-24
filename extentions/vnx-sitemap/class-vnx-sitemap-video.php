<?php
/**
 * VNX Sitemap Video Handler
 * Xử lý tạo video sitemap theo Google Video Sitemap Protocol
 */

if (!defined('ABSPATH')) {
    exit;
}

class VNX_Sitemap_Video_Center
{
    private static $instance = null;
    private $helper;
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
    }

    /**
     * Set posts per sitemap limit (called from main VNX_Sitemap_Center class)
     */
    public function set_posts_per_sitemap($limit)
    {
        $this->posts_per_sitemap = (int) $limit;
    }

    /**
     * Check if video sitemap is enabled in RankMath
     */
    public function should_include()
    {
        if (!class_exists('RankMath')) {
            return false;
        }

        $rm_options = get_option('rank-math-options-sitemap');
        if (!$rm_options) {
            return false;
        }

        // RankMath video sitemap: check if video_sitemap_post_type has any post types
        return isset($rm_options['video_sitemap_post_type']) && !empty($rm_options['video_sitemap_post_type']);
    }

    /**
     * Get count of posts with video
     */
    public function get_count()
    {
        global $wpdb;
        $count = $wpdb->get_var("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_status = 'publish'
            AND p.post_type IN ('post', 'page')
            AND pm.meta_key = 'rank_math_schema_VideoObject'
            AND pm.meta_value != ''
        ");

        return (int) $count;
    }

    /**
     * Get lastmod for video posts
     * Trả về ISO 8601 datetime với timezone WordPress
     */
    public function get_lastmod()
    {
        global $wpdb;
        $lastmod = $wpdb->get_var("
            SELECT p.post_modified_gmt
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_status = 'publish'
            AND p.post_type IN ('post', 'page')
            AND pm.meta_key = 'rank_math_schema_VideoObject'
            AND pm.meta_value != ''
            ORDER BY p.post_modified_gmt DESC
            LIMIT 1
        ");

        return $this->helper->format_lastmod_datetime($lastmod);
    }

    /**
     * Parse RankMath placeholder variables in video schema
     * Replaces %seo_title%, %seo_description%, %post_thumbnail%, %date(...)% etc.
     * 
     * @param mixed $value The value to parse (string or array)
     * @param int $post_id The post ID for context
     * @return mixed Parsed value
     */
    public function parse_rankmath_variables($value, $post_id)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->parse_rankmath_variables($v, $post_id);
            }
            return $value;
        }

        if (!is_string($value) || empty($value)) {
            return $value;
        }

        // Skip if no variables
        if (strpos($value, '%') === false) {
            return $value;
        }

        $post = get_post($post_id);
        if (!$post) {
            return $value;
        }

        // Get RankMath SEO data
        $seo_title = get_post_meta($post_id, 'rank_math_title', true);
        $seo_description = get_post_meta($post_id, 'rank_math_description', true);

        // Fallback to post title/excerpt if empty
        if (empty($seo_title)) {
            $seo_title = $post->post_title;
        }
        if (empty($seo_description)) {
            $seo_description = wp_trim_words(wp_strip_all_tags($post->post_content), 30, '...');
        }

        // Get thumbnail URL - dùng SQL trực tiếp (bypass CDN)
        $thumbnail_url = $this->helper->get_thumbnail_url_direct($post_id);
        if (empty($thumbnail_url)) {
            $thumbnail_url = ''; // Empty if no thumbnail
        }

        // Replace common RankMath variables
        $replacements = [
            '%seo_title%' => $seo_title,
            '%title%' => $post->post_title,
            '%seo_description%' => $seo_description,
            '%excerpt%' => wp_trim_words(wp_strip_all_tags($post->post_content), 30, '...'),
            '%post_thumbnail%' => $thumbnail_url,
            '%url%' => get_permalink($post_id),
            '%name%' => $post->post_title,
        ];

        $value = str_replace(array_keys($replacements), array_values($replacements), $value);

        // Handle %date(format)% patterns
        if (preg_match('/%date\(([^)]+)\)%/', $value, $matches)) {
            $date_format = $matches[1];
            // Convert WP timezone date
            $wp_timezone = wp_timezone();
            $date = new DateTime($post->post_date, $wp_timezone);
            $formatted_date = $date->format(str_replace(['Y-m-dTH:i:sP', 'c'], ['c', 'c'], $date_format));
            $value = preg_replace('/%date\([^)]+\)%/', $formatted_date, $value);
        }

        // If still contains %, it's an unresolved variable - return empty or fallback
        if (strpos($value, '%') !== false && preg_match('/%[a-z_]+%/i', $value)) {
            return '';
        }

        return $value;
    }

    /**
     * Check if a video schema field is valid (not empty and not a raw variable)
     */
    public function is_valid_video_field($value)
    {
        if (empty($value)) {
            return false;
        }
        // Check if it's still a raw RankMath variable
        if (is_string($value) && preg_match('/^%[a-z_]+%$/i', trim($value))) {
            return false;
        }
        return true;
    }

    /**
     * Generate video sitemap (Google Video Sitemap Protocol)
     * 
     * @param int $page Page number
     * @return string XML content
     */
    public function generate($page = 1)
    {
        global $wpdb;

        $offset = ($page - 1) * $this->posts_per_sitemap;

        // Get DISTINCT posts with video schema  
        $posts = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT p.ID, p.post_title, p.post_modified_gmt
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_status = 'publish'
            AND p.post_type IN ('post', 'page')
            AND pm.meta_key = 'rank_math_schema_VideoObject'
            AND pm.meta_value != ''
            ORDER BY p.post_modified_gmt DESC
            LIMIT %d OFFSET %d
        ", $this->posts_per_sitemap, $offset));

        if (empty($posts)) {
            return '<error>No videos found</error>';
        }

        ob_start();
 
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        echo 'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";

        foreach ($posts as $post) {
            // Check if post should be indexed
            if ($this->helper->is_post_noindex($post->ID)) {
                continue;
            }

            // Get ALL video schema data from RankMath 
            $video_schemas = get_post_meta($post->ID, 'rank_math_schema_VideoObject', false); // false = get all
            if (empty($video_schemas)) {
                continue;
            }

            // Collect valid videos for this post
            $valid_videos = [];

            foreach ($video_schemas as $video_schema) {
                if (empty($video_schema)) {
                    continue;
                }

                // Parse RankMath variables in the schema
                $video_schema = $this->parse_rankmath_variables($video_schema, $post->ID);

                // Skip if no valid video URL (contentUrl or embedUrl)
                $has_video_url = !empty($video_schema['contentUrl']) || !empty($video_schema['embedUrl']);
                if (!$has_video_url) {
                    continue;
                }

                $valid_videos[] = $video_schema;
            }

            // Skip post if no valid videos
            if (empty($valid_videos)) {
                continue;
            }

            echo "\t<url>\n";
            echo "\t\t<loc>" . $this->helper->esc_xml(get_permalink($post->ID)) . "</loc>\n";

            // Add priority and changefreq for video posts
            $url_priority = $this->helper->get_url_priority('video', $post->ID, $page);
            echo "\t\t<priority>" . $url_priority . "</priority>\n";
            $changefreq = $this->helper->get_changefreq($post->ID);
            echo "\t\t<changefreq>" . $changefreq . "</changefreq>\n";

            // Output ALL valid videos for this post
            foreach ($valid_videos as $video_schema) {
                echo "\t\t<video:video>\n";

                // Video title (required) - fallback to post title
                $video_title = $this->is_valid_video_field($video_schema['name'] ?? '') ? $video_schema['name'] : $post->post_title;
                echo "\t\t\t<video:title>" . $this->helper->esc_xml($video_title) . "</video:title>\n";

                // Video description (required) - fallback to post excerpt
                $video_description = $this->is_valid_video_field($video_schema['description'] ?? '')
                    ? $video_schema['description']
                    : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post->ID)), 50, '...');
                if (!empty($video_description)) {
                    echo "\t\t\t<video:description>" . $this->helper->esc_xml($video_description) . "</video:description>\n";
                }

                // Thumbnail URL (required) - fallback to post thumbnail (SQL trực tiếp bypass CDN)
                $thumbnail_url = $this->is_valid_video_field($video_schema['thumbnailUrl'] ?? '')
                    ? $video_schema['thumbnailUrl']
                    : $this->helper->get_thumbnail_url_direct($post->ID);
                if (!empty($thumbnail_url)) {
                    echo "\t\t\t<video:thumbnail_loc>" . $this->helper->esc_xml($thumbnail_url) . "</video:thumbnail_loc>\n";
                }

                // Content URL or Player URL (required)
                if (!empty($video_schema['contentUrl'])) {
                    echo "\t\t\t<video:content_loc>" . $this->helper->esc_xml($video_schema['contentUrl']) . "</video:content_loc>\n";
                } elseif (!empty($video_schema['embedUrl'])) {
                    echo "\t\t\t<video:player_loc>" . $this->helper->esc_xml($video_schema['embedUrl']) . "</video:player_loc>\n";
                }

                // Duration (optional but recommended)
                if (!empty($video_schema['duration'])) {
                    // Convert ISO 8601 duration to seconds
                    $duration_seconds = $this->helper->parse_duration($video_schema['duration']);
                    if ($duration_seconds > 0) {
                        echo "\t\t\t<video:duration>" . $duration_seconds . "</video:duration>\n";
                    }
                }

                // Upload date (optional) - fallback to post publish date
                $upload_date = $this->is_valid_video_field($video_schema['uploadDate'] ?? '')
                    ? $video_schema['uploadDate']
                    : $this->helper->format_lastmod_datetime($post->post_modified_gmt);
                if (!empty($upload_date)) {
                    echo "\t\t\t<video:publication_date>" . $this->helper->esc_xml($upload_date) . "</video:publication_date>\n";
                }

                // View count (optional)
                if (!empty($video_schema['interactionCount'])) {
                    echo "\t\t\t<video:view_count>" . (int) $video_schema['interactionCount'] . "</video:view_count>\n";
                }

                echo "\t\t</video:video>\n";
            }

            echo "\t</url>\n";
        }

        echo '</urlset>';
        return ob_get_clean();
    }
}
