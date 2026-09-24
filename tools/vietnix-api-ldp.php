<?php

/**
 * API: GET Landing Page Content (text + pricing tables as string)
 * Route: GET /wp-json/vnx_api/v1/get-ldp?page={slug}
 *
 * Example:
 *   https://stag.vietnix.dev/wp-json/vnx_api/v1/get-ldp?page=cheap-hosting
 *
 * Response: JSON with full text content extracted from the page,
 *           including pricing data from CSV widgets.
 */

class VNX_API_LDP_Center
{
    /**
     * Map widget_name → settings key chứa CSV URL.
     * Dùng chung cấu trúc với VNX_API_Price_Table_Center.
     */
    private $widget_csv_keys = [
        'vnx-service-price-v2'      => 'import-csv',
        'vnx-service-price'         => 'upload',
        'vnx-table'                 => 'upload',
        'vnx-table-compare-service' => 'import-csv',
        'vnx-tab-service-price'     => 'list-service',
    ];

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    // ===================================================================
    // Route
    // ===================================================================

    /**
     * Đăng ký REST route: /wp-json/vnx_api/v1/get-ldp
     */
    public function register_routes()
    {
        register_rest_route(VNX_Api_Prefix_V1, '/get-ldp', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_ldp_content'],
            'permission_callback' => ['\VNX_API_Center\VietnixPluginAPI', 'authenticate'],
            'args'                => [
                'page' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_title',
                    'validate_callback' => function ($param) {
                        return !empty(trim($param));
                    },
                ],
            ],
        ]);
    }

    // ===================================================================
    // Callback
    // ===================================================================

    /**
     * Trả về nội dung text đầy đủ của landing page (text + bảng giá).
     */
    public function get_ldp_content(\WP_REST_Request $request)
    {
        try {
            $slug = sanitize_title($request->get_param('page'));
            $post = $this->find_post_by_slug($slug);

            if (!$post) {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => "Không tìm thấy trang với slug: {$slug}",
                    'data'    => null,
                ], 404);
            }

            // Lấy text từ Bricks elements + WP native content
            $content = $this->extract_text_content($post);

            // Gộp thêm nội dung bảng giá từ CSV của các pricing widgets
            foreach ($this->extract_price_tables_from_bricks($post->ID) as $table) {
                if (!empty($table['content'])) {
                    $content .= ($content ? "\n\n" : '') . $table['content'];
                }
            }

            return new \WP_REST_Response([
                'success' => true,
                'message' => 'Lấy nội dung landing page thành công',
                'data'    => [
                    'id'      => (int) $post->ID,
                    'title'   => $post->post_title,
                    'content' => $content,
                ],
            ], 200);

        } catch (\Exception $e) {
            error_log('VNX_API_LDP_Center::get_ldp_content – ' . $e->getMessage());
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy nội dung trang.',
                'data'    => ['error' => $e->getMessage()],
            ], 500);
        }
    }

    // ===================================================================
    // Tìm post
    // ===================================================================

    /**
     * Tìm post theo slug, ưu tiên page trước, sau đó mọi post_type public.
     */
    private function find_post_by_slug(string $slug): ?\WP_Post
    {
        $post = get_page_by_path($slug, OBJECT, 'page');
        if ($post && $post->post_status === 'publish') {
            return $post;
        }

        $query = new \WP_Query([
            'name'           => $slug,
            'post_type'      => array_values(get_post_types(['public' => true], 'names')),
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        ]);

        return $query->have_posts() ? $query->posts[0] : null;
    }

    // ===================================================================
    // Trích xuất text từ Bricks
    // ===================================================================

    /**
     * Trích xuất toàn bộ text từ post (WP native + Bricks Builder).
     * Loại bỏ HTML, shortcode, ảnh, video.
     */
    private function extract_text_content(\WP_Post $post): string
    {
        $results = [];

        // WP native post_content
        if (!empty($post->post_content)) {
            $raw = $this->clean_text(wp_strip_all_tags(do_shortcode($post->post_content)));
            if (!empty($raw)) {
                $results[] = $raw;
            }
        }

        // Excerpt
        if (!empty($post->post_excerpt)) {
            $results[] = $this->clean_text($post->post_excerpt);
        }

        // Bricks Builder content
        $all_templates = get_post_meta($post->ID, '_bricks_page_content_2', false);
        if (!$all_templates || !is_array($all_templates)) {
            return implode("\n\n", $results);
        }

        $template_ids = [];
        foreach ($all_templates as $serialized) {
            $data = $this->unserialize_bricks($serialized);
            if (!$data) continue;

            $this->extract_text_from_bricks($data, $results);
            $this->extract_template_ids($data, $template_ids);
        }

        // Template con
        foreach ($template_ids as $template_id) {
            $data = $this->unserialize_bricks(
                get_post_meta($template_id, '_bricks_page_content_2', true)
            );
            if (!$data) continue;
            $this->extract_text_from_bricks($data, $results);
        }

        return implode("\n\n", $results);
    }

    /**
     * Deserialize Bricks meta value (string hoặc array).
     */
    private function unserialize_bricks($value): ?array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && !empty($value)) {
            $data = @unserialize($value);
            return is_array($data) ? $data : null;
        }
        return null;
    }

    /**
     * Thu thập template IDs từ Bricks elements (đệ quy).
     */
    private function extract_template_ids(array $elements, array &$template_ids): void
    {
        foreach ($elements as $el) {
            if (isset($el['name']) && $el['name'] === 'template' && !empty($el['settings']['template'])) {
                $template_ids[] = $el['settings']['template'];
            }
            if (!empty($el['children']) && is_array($el['children'])) {
                $this->extract_template_ids($el['children'], $template_ids);
            }
        }
    }

    /**
     * Đệ quy qua Bricks elements, lấy text từ các field phổ biến.
     * Bỏ qua image, video, icon, svg, lottie.
     */
    private function extract_text_from_bricks(array $elements, array &$texts): void
    {
        static $skip_types  = ['image', 'video', 'icon', 'svg', 'lottie'];
        static $text_fields = ['text', 'content', 'heading', 'label', 'title', 'caption', 'description', 'subtitle'];
        static $rpt_fields  = ['items', 'tabs', 'accordions', 'list', 'slides', 'testimonials', 'pricing'];

        foreach ($elements as $el) {
            $type     = $el['name']     ?? '';
            $settings = $el['settings'] ?? [];

            if (in_array($type, $skip_types, true)) {
                continue;
            }

            // Top-level text fields
            foreach ($text_fields as $field) {
                if (!empty($settings[$field]) && is_string($settings[$field])) {
                    $clean = $this->clean_text(wp_strip_all_tags($settings[$field]));
                    if (!empty($clean)) {
                        $texts[] = $clean;
                    }
                }
            }

            // Repeater fields
            foreach ($rpt_fields as $rfield) {
                if (empty($settings[$rfield]) || !is_array($settings[$rfield])) continue;
                foreach ($settings[$rfield] as $item) {
                    if (!is_array($item)) continue;
                    foreach ($text_fields as $field) {
                        if (!empty($item[$field]) && is_string($item[$field])) {
                            $clean = $this->clean_text(wp_strip_all_tags($item[$field]));
                            if (!empty($clean)) {
                                $texts[] = $clean;
                            }
                        }
                    }
                }
            }

            // Đệ quy children
            if (!empty($el['children']) && is_array($el['children'])) {
                $this->extract_text_from_bricks($el['children'], $texts);
            }
        }
    }

    // ===================================================================
    // Bảng giá từ CSV
    // ===================================================================

    /**
     * Quét Bricks elements của post, parse tất cả CSV pricing widgets,
     * trả về mảng [{widget, content}].
     */
    private function extract_price_tables_from_bricks(int $post_id): array
    {
        $tables = [];
        foreach ($this->find_all_csv_in_bricks($post_id) as $item) {
            $csv_data = $this->read_csv_file($item['csv_url']);
            if (empty($csv_data)) continue;

            $converted = $this->auto_convert_csv($csv_data, $item['widget_name']);
            if (!empty($converted)) {
                $tables[] = $converted;
            }
        }
        return $tables;
    }

    /**
     * Quét tất cả Bricks elements (kể cả template con), trả về [{csv_url, widget_name}].
     */
    private function find_all_csv_in_bricks(int $post_id): array
    {
        $all_templates = get_post_meta($post_id, '_bricks_page_content_2', false);
        if (!$all_templates || !is_array($all_templates)) return [];

        $results      = [];
        $template_ids = [];

        foreach ($all_templates as $serialized) {
            $data = $this->unserialize_bricks($serialized);
            if (!$data) continue;
            $this->extract_csv_from_elements($data, $results);
            $this->extract_template_ids($data, $template_ids);
        }

        foreach ($template_ids as $tid) {
            $data = $this->unserialize_bricks(
                get_post_meta($tid, '_bricks_page_content_2', true)
            );
            if (!$data) continue;
            $this->extract_csv_from_elements($data, $results);
        }

        return $results;
    }

    /**
     * Đệ quy quét elements tìm CSV URLs dựa trên widget_csv_keys.
     */
    private function extract_csv_from_elements(array $elements, array &$results): void
    {
        foreach ($elements as $el) {
            $widget_name = $el['name']     ?? '';
            $settings    = $el['settings'] ?? [];

            if (isset($this->widget_csv_keys[$widget_name])) {
                $import_key = $this->widget_csv_keys[$widget_name];

                if ($import_key === 'list-service' && !empty($settings['list-service']) && is_array($settings['list-service'])) {
                    foreach ($settings['list-service'] as $item) {
                        if (!empty($item['import-csv']['url'])) {
                            $results[] = ['csv_url' => $item['import-csv']['url'], 'widget_name' => $widget_name];
                        }
                    }
                } elseif (!empty($settings[$import_key]['url'])) {
                    $this->add_unique_csv($results, $settings[$import_key]['url'], $widget_name);
                }
            }

            // Fallback: quét settings tìm bất kỳ URL .csv nào
            $this->scan_settings_for_csv($settings, $widget_name, $results);

            if (!empty($el['children']) && is_array($el['children'])) {
                $this->extract_csv_from_elements($el['children'], $results);
            }
        }
    }

    /**
     * Thêm CSV URL vào results nếu chưa tồn tại (tránh duplicate).
     */
    private function add_unique_csv(array &$results, string $url, string $widget_name): void
    {
        foreach ($results as $r) {
            if ($r['csv_url'] === $url) return;
        }
        $results[] = ['csv_url' => $url, 'widget_name' => $widget_name];
    }

    /**
     * Quét tất cả settings tìm URL .csv chưa được đăng ký qua widget_csv_keys.
     */
    private function scan_settings_for_csv(array $settings, string $widget_name, array &$results): void
    {
        foreach ($settings as $value) {
            if (is_array($value) && isset($value['url']) && is_string($value['url'])) {
                if (preg_match('/\.csv$/i', $value['url'])) {
                    $this->add_unique_csv($results, $value['url'], $widget_name ?: 'unknown');
                }
            } elseif (is_string($value) && preg_match('/^https?:\/\/.*\.csv$/i', $value)) {
                $this->add_unique_csv($results, $value, $widget_name ?: 'unknown');
            }
        }
    }

    // ===================================================================
    // Đọc & convert CSV
    // ===================================================================

    /**
     * Đọc file CSV từ URL hoặc path local.
     * Trả về dạng transposed columns (giống VNX_API_Price_Table_Center).
     */
    private function read_csv_file(string $csv_url): array
    {
        $file = null;

        if (preg_match('/wp-content\/(.*)/', $csv_url, $matches)) {
            $file = ABSPATH . 'wp-content/' . $matches[1];
            if (!file_exists($file)) {
                $file = wp_upload_dir()['basedir'] . '/' . $matches[1];
            }
        } elseif (filter_var($csv_url, FILTER_VALIDATE_URL)) {
            $file = $csv_url;
        }

        if (!$file || (!file_exists($file) && !filter_var($file, FILTER_VALIDATE_URL))) {
            return [];
        }

        $handle = @fopen($file, 'r');
        if (!$handle) return [];

        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            $rows[] = $line;
        }
        fclose($handle);

        // Transpose rows → columns
        $transposed = [];
        foreach ($rows as $ri => $row) {
            foreach ($row as $ci => $val) {
                $transposed[$ci][$ri] = $val;
            }
        }
        return $transposed;
    }

    /**
     * Convert CSV sang string dễ đọc.
     * Ưu tiên dùng VNX_API_Price_Table_Center converter (qua Reflection).
     * Fallback: đọc thô toàn bộ cell.
     */
    private function auto_convert_csv(array $data, string $widget_name): array
    {
        if (class_exists('VNX_API_Price_Table_Center')) {
            try {
                $instance = new \VNX_API_Price_Table_Center();
                $method   = (new \ReflectionClass($instance))->getMethod('auto_convert');
                $method->setAccessible(true);
                $structured = $method->invoke($instance, $data, $widget_name);

                return [
                    'widget'  => $widget_name,
                    'content' => $this->convert_table_to_string($structured),
                ];
            } catch (\Throwable $e) {
                error_log('VNX_API_LDP_Center::auto_convert_csv fallback – ' . $e->getMessage());
            }
        }

        // Fallback: lấy thô tất cả cell có nghĩa
        $texts = [];
        foreach ($data as $col) {
            foreach ($col as $cell) {
                if (!is_string($cell)) continue;
                $clean = trim(strip_tags($cell));
                if (empty($clean) || $clean === '*' || preg_match('/^https?:\/\//', $clean)) continue;
                foreach (explode('|', $clean) as $p) {
                    $p = trim($p);
                    if (!empty($p) && !in_array($p, $texts, true)) {
                        $texts[] = $p;
                    }
                }
            }
        }

        return [
            'widget'  => $widget_name,
            'content' => implode("\n", $texts),
        ];
    }

    /**
     * Serialize cấu trúc tablePrice {name, plans[name, prices, technicalSpecs]}
     * thành chuỗi text dễ đọc.
     */
    private function convert_table_to_string(array $table): string
    {
        if (empty($table)) return '';

        $lines = [];

        if (!empty($table['name'])) {
            $lines[] = $table['name'];
        }

        foreach ($table['plans'] ?? [] as $plan) {
            if (!empty($plan['name'])) {
                $lines[] = $plan['name'];
            }

            foreach ($plan['prices'] ?? [] as $price) {
                $parts = array_filter([
                    $price['cycle']        ?? '',
                    !empty($price['regular'])      ? 'Giá: '       . $price['regular']      : '',
                    !empty($price['sale'])         ? 'Giảm còn: '  . $price['sale']         : '',
                    !empty($price['codeDiscount']) ? 'Mã: '        . $price['codeDiscount'] : '',
                    !empty($price['discount'])     ? 'Giảm: '      . $price['discount']     : '',
                ]);
                if (!empty($parts)) {
                    $lines[] = implode(' | ', $parts);
                }
            }

            foreach ($plan['technicalSpecs'] ?? [] as $spec) {
                if (!empty($spec)) {
                    $lines[] = $spec;
                }
            }
        }

        // Một số converter trả về raw_data
        foreach ($table['raw_data'] ?? [] as $row) {
            $lines[] = implode(' | ', array_filter($row, 'strlen'));
        }

        return implode("\n", array_filter($lines, 'strlen'));
    }

    // ===================================================================
    // Helpers
    // ===================================================================

    /**
     * Làm sạch chuỗi text: decode HTML entities, chuẩn hóa whitespace.
     */
    private function clean_text(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}

new VNX_API_LDP_Center();
