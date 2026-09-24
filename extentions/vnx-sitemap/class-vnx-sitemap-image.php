<?php
/**
 * VNX Sitemap Image Handler
 * Xử lý trích xuất và validate hình ảnh cho sitemap
 */

if (!defined('ABSPATH')) {
    exit;
}

class VNX_Sitemap_Image_Center
{
    private static $instance = null;
    private $helper;
    private $images_per_post = 500;
    private $accept_domains = null;
    private $site_host = null;

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
     * Set images per post limit (called from main VNX_Sitemap_Center class)
     */
    public function set_images_per_post($limit)
    {
        $this->images_per_post = (int) $limit;
    }

    /**
     * Set accept domains từ settings
     * 
     * @param array $domains List domain được chấp nhận
     */
    public function set_accept_domains($domains)
    {
        $this->accept_domains = is_array($domains) ? $domains : [];
    }

    /**
     * Kiểm tra URL ảnh có hợp lệ không
     * - Check extension ảnh
     * - Check domain: site hiện tại hoặc domain trong accept list
     */
    public function is_valid_image_url($url)
    {
        if (empty($url)) {
            return false;
        }

        // Skip data URLs
        if (strpos($url, 'data:') === 0) {
            return false;
        }

        // Skip placeholder/tracking URLs
        if (strpos($url, 'placeholder') !== false || strpos($url, '1x1') !== false) {
            return false;
        }

        // PHẢI có extension ảnh hợp lệ
        if (!preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico|bmp|tiff?)(\?.*)?$/i', $url)) {
            return false;
        }

        $parsed = parse_url($url);

        // Relative URL - hợp lệ (thuộc site hiện tại)
        if (!isset($parsed['host'])) {
            return true;
        }

        // Phải có scheme http/https
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }

        // Kiểm tra domain có được chấp nhận không
        return $this->is_accepted_domain($parsed['host']);
    }

    /**
     * Kiểm tra domain có được chấp nhận không
     * 
     * @param string $host Host cần kiểm tra
     * @return bool
     */
    private function is_accepted_domain($host)
    {
        $host = strtolower($host);

        // 1. Check domain hiện tại của site
        if ($this->site_host === null) {
            $original_baseurl = $this->helper->get_original_upload_baseurl();
            $this->site_host = strtolower(parse_url($original_baseurl, PHP_URL_HOST));
        }

        if ($host === $this->site_host) {
            return true;
        }

        // 2. Load accept domains từ settings nếu chưa có
        if ($this->accept_domains === null) {
            $settings = VNX_Sitemap_Settings_Center::instance();
            $this->accept_domains = $settings->get('sitemap_accept_domains', []);
        }

        // 3. Check trong list accept domains
        if (!empty($this->accept_domains)) {
            foreach ($this->accept_domains as $accepted) {
                $accepted = strtolower($accepted);
                
                if ($host === $accepted) {
                    return true;
                }
                if (substr($host, -strlen($accepted) - 1) === '.' . $accepted) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Batch fetch all images for multiple posts at once
     * @return array [post_id => [['url' => '...', 'title' => '...'], ...]]
     */
    public function batch_get_all_images($post_ids)
    {
        if (empty($post_ids)) {
            return [];
        }

        global $wpdb;
        $result = [];
        $safe_ids = implode(',', array_map('intval', $post_ids));

        // 1) Batch fetch featured images
        $result = $this->fetch_featured_images($safe_ids, $result);

        // 2) Batch fetch from post content
        $result = $this->fetch_content_images($safe_ids, $result);

        // 3) Batch fetch Bricks data
        $result = $this->fetch_bricks_images($post_ids, $result);

        // Normalize, dedupe và limit
        foreach ($result as $post_id => &$images) {
            $seen_urls = [];
            $unique_images = [];
            foreach ($images as $img) {
                if (!isset($seen_urls[$img['url']])) {
                    $seen_urls[$img['url']] = true;
                    $unique_images[] = $img;
                }
            }
            $images = $unique_images;

            if (count($images) > $this->images_per_post) {
                $images = array_slice($images, 0, $this->images_per_post);
            }
        }

        return $result;
    }

    /**
     * Fetch featured images
     */
    private function fetch_featured_images($safe_ids, $result)
    {
        global $wpdb;

        $thumb_sql = "
            SELECT SQL_NO_CACHE post_id, meta_value as attachment_id
            FROM {$wpdb->postmeta} USE INDEX (meta_key)
            WHERE meta_key = '_thumbnail_id'
            AND post_id IN ({$safe_ids})
        ";
        $thumbs = $wpdb->get_results($thumb_sql);

        $thumb_ids = [];
        foreach ($thumbs as $t) {
            $thumb_ids[] = (int) $t->attachment_id;
        }

        if (!empty($thumb_ids)) {
            $safe_thumb_ids = implode(',', array_map('intval', $thumb_ids));

            $thumb_data_sql = "
                SELECT SQL_NO_CACHE 
                    p.ID, 
                    pm_file.meta_value as file_path,
                    pm_alt.meta_value as alt_text
                FROM {$wpdb->posts} p USE INDEX (PRIMARY)
                INNER JOIN {$wpdb->postmeta} pm_file ON p.ID = pm_file.post_id AND pm_file.meta_key = '_wp_attached_file'
                LEFT JOIN {$wpdb->postmeta} pm_alt ON p.ID = pm_alt.post_id AND pm_alt.meta_key = '_wp_attachment_image_alt'
                WHERE p.post_type = 'attachment'
                AND p.ID IN ({$safe_thumb_ids})
            ";
            $thumb_data = $wpdb->get_results($thumb_data_sql);

            $thumb_map = [];
            $upload_baseurl = $this->helper->get_original_upload_baseurl();
            foreach ($thumb_data as $td) {
                $url = $upload_baseurl . '/' . $td->file_path;
                $thumb_map[(int) $td->ID] = [
                    'url' => $url,
                    'title' => !empty($td->alt_text) ? $td->alt_text : ''
                ];
            }

            foreach ($thumbs as $t) {
                $post_id = (int) $t->post_id;
                $att_id = (int) $t->attachment_id;
                if (isset($thumb_map[$att_id])) {
                    $img_data = $thumb_map[$att_id];
                    if ($this->is_valid_image_url($img_data['url'])) {
                        if (!isset($result[$post_id])) {
                            $result[$post_id] = [];
                        }
                        $result[$post_id][] = $img_data;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Fetch images from post content
     */
    private function fetch_content_images($safe_ids, $result)
    {
        global $wpdb;

        $content_sql = "
            SELECT SQL_NO_CACHE ID, post_content
            FROM {$wpdb->posts} USE INDEX (PRIMARY)
            WHERE ID IN ({$safe_ids})
            AND post_content LIKE '%<img%'
        ";
        $contents = $wpdb->get_results($content_sql);

        foreach ($contents as $c) {
            $post_id = (int) $c->ID;

            if (isset($result[$post_id]) && count($result[$post_id]) >= $this->images_per_post) {
                continue;
            }

            preg_match_all('/<img[^>]+>/i', $c->post_content, $img_tags);
            if (!empty($img_tags[0])) {
                foreach ($img_tags[0] as $img_tag) {
                    $url = '';
                    if (preg_match('/(?:src|data-src)=["\']([^"\']+)["\']/i', $img_tag, $src_match)) {
                        $url = $src_match[1];
                    }

                    $alt = '';
                    if (preg_match("/alt=[\"']([^\"']*)[\"']/i", $img_tag, $alt_match)) {
                        $alt = $alt_match[1];
                    }

                    if (empty($url) || strpos($url, 'data:image') === 0) {
                        continue;
                    }
 
                    if (!$this->is_valid_image_url($url)) {
                        continue;
                    }

                    if (!isset($result[$post_id])) {
                        $result[$post_id] = [];
                    }

                    $result[$post_id][] = [
                        'url' => $url,
                        'title' => $alt
                    ];

                    if (count($result[$post_id]) >= $this->images_per_post) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Fetch images from Bricks builder data
     */
    private function fetch_bricks_images($post_ids, $result)
    {
        $bricks_data = $this->fetch_bricks_meta_bulk($post_ids);
        $template_ids = [];

        foreach ($bricks_data as $post_id => $data) {
            if (isset($result[$post_id]) && count($result[$post_id]) >= $this->images_per_post) {
                continue;
            }

            $images = $this->extract_bricks_images_from_tree($data);
            if (!empty($images)) {
                if (!isset($result[$post_id])) {
                    $result[$post_id] = [];
                }
                $needed = $this->images_per_post - count($result[$post_id]);
                $result[$post_id] = array_merge($result[$post_id], array_slice($images, 0, $needed));
            }

            if (!isset($result[$post_id]) || count($result[$post_id]) < $this->images_per_post) {
                $tpls = $this->extract_bricks_template_ids($data);
                $template_ids = array_merge($template_ids, $tpls);
            }
        }

        if (!empty($template_ids)) {
            $template_ids = array_unique($template_ids);
            $tpl_data = $this->fetch_bricks_meta_bulk($template_ids);

            foreach ($bricks_data as $post_id => $data) {
                $tpls = $this->extract_bricks_template_ids($data);
                foreach ($tpls as $tpl_id) {
                    if (isset($tpl_data[$tpl_id])) {
                        $tpl_images = $this->extract_bricks_images_from_tree($tpl_data[$tpl_id]);
                        if (!empty($tpl_images)) {
                            if (!isset($result[$post_id])) {
                                $result[$post_id] = [];
                            }
                            $result[$post_id] = array_merge($result[$post_id], $tpl_images);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Fetch bricks meta for an array of post IDs
     */
    private function fetch_bricks_meta_bulk($post_ids)
    {
        global $wpdb;
        $out = [];
        $post_ids = array_map('intval', $post_ids);
        if (empty($post_ids)) return $out;

        $meta_keys = "'_bricks_page_content_3','_bricks_page_content_2','_bricks_page_content','_bricks_data'";
        $ids = implode(',', $post_ids);

        $sql = "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ($meta_keys) AND post_id IN ($ids)";
        $rows = $wpdb->get_results($sql, ARRAY_A);

        foreach ($rows as $r) {
            $pid = (int) $r['post_id'];
            if (!empty($out[$pid])) continue;
            $out[$pid] = maybe_unserialize($r['meta_value']);
        }

        return $out;
    }

    /**
     * Extract template IDs used inside bricks tree
     */
    private function extract_bricks_template_ids($tree)
    {
        $tpls = [];
        if (!is_array($tree)) return $tpls;

        $walk = function ($node) use (&$walk, &$tpls) {
            if (!is_array($node)) return;
            foreach ($node as $n) {
                if (!is_array($n)) continue;
                if (!empty($n['settings']['template']))
                    $tpls[] = (int) $n['settings']['template'];
                if (!empty($n['children']))
                    $walk($n['children']);
            }
        };

        $walk($tree);
        return array_unique($tpls);
    }

    /**
     * Recursive extraction of image URLs from bricks tree
     */
    private function extract_bricks_images_from_tree($tree)
    {
        $out = [];
        if (!is_array($tree)) return $out;

        $keys = ['image', 'img', 'src', 'imageUrl', 'backgroundImage', 'background_image', 'bg_image', 'posterUrl', 'avatar', 'logo', 'icon', 'file', 'media', 'attachment', 'featured_image'];

        $self = $this;

        $walk = function ($node) use (&$walk, &$out, $keys, $self) {
            if (is_string($node)) {
                if (filter_var($node, FILTER_VALIDATE_URL) && $self->is_valid_image_url($node)) {
                    $out[] = ['url' => $node, 'title' => ''];
                }
                return;
            }

            if (!is_array($node)) return;

            foreach ($node as $k => $v) {
                if ($k === 'settings' && is_array($v)) {
                    foreach ($keys as $key) {
                        if (isset($v[$key]) && $v[$key]) {
                            $val = $v[$key];
                            $url = '';
                            $title = '';

                            if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) {
                                $url = $val;
                            } elseif (is_array($val) && !empty($val['url']) && filter_var($val['url'], FILTER_VALIDATE_URL)) {
                                $url = $val['url'];
                                if (!empty($val['alt'])) {
                                    $title = $val['alt'];
                                }
                            } elseif (is_array($val) && !empty($val['id']) && is_numeric($val['id'])) {
                                $att_data = $self->helper->get_attachment_data_direct((int) $val['id']);
                                if ($att_data) {
                                    $url = $att_data['url'];
                                    $title = $att_data['alt'];
                                }
                            } elseif (is_numeric($val)) {
                                $att_data = $self->helper->get_attachment_data_direct((int) $val);
                                if ($att_data) {
                                    $url = $att_data['url'];
                                    $title = $att_data['alt'];
                                }
                            } 
                            if (!empty($url) && $self->is_valid_image_url($url)) {
                                $out[] = ['url' => $url, 'title' => $title ?: ''];
                            }
                        }
                    }
                }

                if (is_array($v)) $walk($v);
            }
        };

        foreach ($tree as $node) $walk($node);

        // Dedupe by URL
        $seen = [];
        $unique = [];
        foreach ($out as $img) {
            if (!isset($seen[$img['url']])) {
                $seen[$img['url']] = true;
                $unique[] = $img;
            }
        }

        return $unique;
    }
}
