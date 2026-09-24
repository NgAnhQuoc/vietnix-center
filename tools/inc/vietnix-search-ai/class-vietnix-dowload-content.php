<?php

namespace ToolsCenter\VNX_Search_Post_AI;

require_once __DIR__ . '/shared-storage.php';
require_once __DIR__ . '/class-vietnix-get-csv-widget.php';
require_once __DIR__ . '/class-vietnix-convert-tableprice.php';

use ToolsCenter\VNX_Search_Post_AI\VNX_GetCsvWidgetBricks;

class VNX_Download_Content
{
    private $option_name = 'vnx_get_csv_widgetbricks';
    private $ignore_keywords = [];
    private $settings = [];
    private $csv;
    private $convertTablePrice;

    public function __construct($args = [])
    {
        $this->convertTablePrice = new VNX_Convert_TablePrice();

        $this->settings = get_option($this->option_name);
        $this->setIgnoreKeywords($args['ignoreContent'] ?? '');
        $this->csv = new VNX_GetCsvWidgetBricks();
        add_action('wp_ajax_vnx_search_ai_get_info_by_url_center', [$this, 'get_info_by_url']);
        add_action('wp_ajax_nopriv_vnx_search_ai_get_info_by_url_center', [$this, 'get_info_by_url']);
    }

    // bỏ trường ignore keywords
    public function setIgnoreKeywords($keywords)
    {
        $lines = is_string($keywords) ? preg_split('/\r?\n/', $keywords) : (is_array($keywords) ? $keywords : []);
        $this->ignore_keywords = array_filter(array_map(fn($v) => mb_strtolower(trim($v)), $lines));
    }

    // Helper: lấy post object từ url
    private function getPostByUrl($url)
    {
        $home_url = home_url('/');
        $path = trim(str_replace($home_url, '', $url), '/');
        $post = get_page_by_path($path, OBJECT, 'page')
            ?: get_page_by_path($path, OBJECT, 'post')
            ?: get_page_by_path($path, OBJECT);
        return $post;
    }

    /**
     * Lấy thông tin post hoặc page theo URL truyền vào POST['url']
     * Trả về JSON với các trường: ID, title, link, content, excerpt, categories, thumbnail
     */
    public function get_info_by_single_url($url)
    {
        $url = esc_url_raw($url);
        if (!$url) return ['success' => false, 'message' => 'Thiếu tham số url'];
        // Nếu là huongdan.vietnix.vn thì lấy từ file huongdan_embeddings_ai.json
        if (strpos($url, 'huongdan.vietnix.vn') !== false) {
            $json_file = \vnx_search_ai_dir_Center() . '/huongdan_embeddings_ai.json';
            if (!file_exists($json_file)) return ['success' => false, 'message' => 'Không tìm thấy file huongdan_embeddings_ai.json', 'link' => $url];
            $json = file_get_contents($json_file);
            $dataArr = json_decode($json, true);
            if (!is_array($dataArr)) return ['success' => false, 'message' => 'Dữ liệu huongdan_embeddings_ai.json không hợp lệ', 'link' => $url];
            foreach ($dataArr as $item) {
                if (isset($item['link']) && $item['link'] == $url) {
                    unset($item['embedding']);
                    $item['success'] = true;
                    return $item;
                }
            }
            return ['success' => false, 'message' => 'Không tìm thấy thông tin cho url này trong huongdan_embeddings_ai.json', 'link' => $url];
        }
        // Kiểm tra option list_url
        $option = get_option($this->option_name);
        $list_url = $option['list_url'] ?? [];
        $matched_items = array_filter($list_url, fn($item) => isset($item['url']) && $item['url'] == $url);

        $tablePrices = [];
        $openaiResult = null;



        if (!empty($matched_items)) {
            $idx = 0;
            foreach ($matched_items as $item) {
                $import_key = $item['import_key'] ?? null;
                $widget_name = $item['widget_name'] ?? null;
                $name = $item['name'] ?? null;

                if (is_null($import_key) || is_null($widget_name)) {

                    $post = $this->getPostByUrl($url);
                    $post_type = $post ? get_post_type($post) : '';
                    $content = $this->getFullContentFromPost($post, $post_type);


                    $content = trim($content);
                    if (!empty($content)) {
                        $openaiResult = $this->convertDataByOpenAI($content, ['id' => $post ? $post->ID : null, 'url' => $url]);
                        $tablePrices[] = null;
                    }
                } else {
                    $result = $this->getDataWidgetService($url, $widget_name, $import_key, $name);
                    if (isset($result['success']) && $result['success'] && !empty($result['data']['data'])) {
                        $tablePrices[] = $result['data']['data'];
                    } else {
                        $tablePrices[] = null;
                    }
                }
                $idx++;
            }
        }
        // Nếu có kết quả từ OpenAI thì trả về luôn
        if ($openaiResult && ($openaiResult['success'] ?? false)) {
            $data = $openaiResult['data'];
            $data['link'] = $url;
            $data['success'] = true;
            return $data;
        }
        $post = $this->getPostByUrl($url);
        $post_type = $post ? get_post_type($post) : '';
        $content = $this->getFullContentFromPost($post, $post_type);
        $content = wp_strip_all_tags($content);
        $content = str_replace("\n", ' ', $content);
        $excerpt = $post ? (get_post_field('post_excerpt', $post->ID) ?: '') : '';
        $data = [
            'ID'         => $post ? $post->ID : 0,
            'title'      => $post ? get_the_title($post) : '',
            'link'       => $url,
            'content'    => $content,
            'excerpt'    => $excerpt,
            'categories' => $post_type === 'page' ? 'Page' : ($post_type === 'post' ? ($post ? (get_the_category($post->ID)[0]->name ?? '') : '') : $post_type),
            'thumbnail'  => $post ? (get_the_post_thumbnail_url($post->ID, 'full') ?: '') : '',
            'success'    => $post ? true : false,
        ];
        if (!empty($tablePrices)) $data['tablePrice'] = $tablePrices;
        if ($post) wp_reset_postdata();
        return $data;
    }

    public function get_info_by_url()
    {
        $urls = $_POST['url'] ?? '';
        if (empty($urls)) return wp_send_json_error(['message' => 'Thiếu tham số url']);
        $url_list = is_array($urls) ? $urls : array_filter(array_map('trim', preg_split('/[\n,]+/', $urls)));
        $results = array_map(fn($url) => $this->get_info_by_single_url($url), $url_list);
        return wp_send_json_success(array_values($results));
    }

    /**
     * Lấy content ưu tiên: Bricks (đệ quy template) -> content chuẩn -> HTML thực tế (nếu là page)
     */
    private function getFullContentFromPost($post, $post_type)
    {
        if (!$post) return '';
        // 1. Lấy content từ Bricks (đệ quy template)
        $bricks_content = get_post_meta($post->ID, '_bricks_page_content_2', false);
        if ($bricks_content && is_array($bricks_content)) {
            $content = '';
            foreach ($bricks_content as $serialized) {
                $data = is_string($serialized) ? @unserialize($serialized) : (is_array($serialized) ? $serialized : null);
                if (!$data || !is_array($data)) continue;
                $content .= $this->renderBricksContentRecursive($data);
            }
            $content = trim($content);
            if (!empty($content)) return $content;
        }
        // 2. Lấy content chuẩn
        $content = apply_filters('the_content', get_post_field('post_content', $post->ID));
        if (!empty($content)) return $content;
        // 3. Nếu là page, lấy HTML thực tế
        if ($post_type === 'page') {
            $response = wp_remote_get(get_permalink($post));
            $html = '';
            if (!is_wp_error($response)) {
                $html = wp_remote_retrieve_body($response);
            }
            if ($html) {
                libxml_use_internal_errors(true);
                $dom = new \DOMDocument('1.0', 'UTF-8');
                if (stripos($html, '<meta charset') === false) {
                    $html = preg_replace('/<head(.*?)>/', '<head$1><meta charset="utf-8">', $html, 1);
                }
                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
                $body = $dom->getElementsByTagName('body')->item(0);
                $content = '';
                if ($body) {
                    foreach ($body->childNodes as $node) {
                        $content .= $dom->saveHTML($node);
                    }
                    $content = trim($content);
                }
                if (!empty($content)) return $content;
            }
        }
        return '';
    }

    /**
     * Render Bricks content array to HTML (text join, support template recursion)
     */
    private function renderBricksContentRecursive($elements, $depth = 0)
    {
        $html = '';
        foreach ($elements as $el) {
            // Nếu là template, kiểm tra toàn bộ text của template (bao gồm children) có chứa ignore không
            if (isset($el['name']) && $el['name'] === 'template' && !empty($el['settings']['template']) && $depth < 3) {
                $template_id = $el['settings']['template'];
                $template_content = get_post_meta(
                    $template_id,
                    '_bricks_page_content_2',
                    true
                );
                if ($template_content) {
                    $template_data = is_string($template_content) ? @unserialize($template_content) : (is_array($template_content) ? $template_content : null); // Kiểm tra toàn bộ text trong template con
                    $template_text = $this->getAllTextFromBricks($template_data);
                    $skip_template = false;
                    $template_text = $this->getAllTextFromBricks($template_data);
                    if (!empty($this->ignore_keywords) && is_array($template_text)) {
                        foreach ($template_text as $txt) {
                            $txt_lc = mb_strtolower($txt);
                            foreach ($this->ignore_keywords as $kw) {
                                if (mb_strpos($txt_lc, $kw) !== false) {
                                    $skip_template = true;
                                    break 2;
                                }
                            }
                        }
                    }
                    if ($skip_template) continue;
                    if ($template_data && is_array($template_data)) {
                        $html .= $this->renderBricksContentRecursive($template_data, $depth + 1);
                    }
                }
                continue;
            }
            // Bỏ qua widget nếu text chứa keyword ignore
            $skip = false;
            if (!empty($this->ignore_keywords)) {
                if (isset($el['settings']['text'])) {
                    $text_lc = mb_strtolower($el['settings']['text']);
                    foreach ($this->ignore_keywords as $kw) {
                        if (mb_strpos($text_lc, $kw) !== false) {
                            $skip = true;
                            break;
                        }
                    }
                }
            }
            if ($skip) continue;
            // Lấy text nếu có, chỉ khi không có children (tránh lặp text khi có children)
            if (isset($el['settings']['text']) && (empty($el['children']) || !is_array($el['children']))) {
                $html .= $el['settings']['text'] . "\n";
            }
            // Đệ quy children
            if (!empty($el['children']) && is_array($el['children'])) {
                $html .= $this->renderBricksContentRecursive($el['children'], $depth);
            }
        }
        return $html;
    }

    // Helper: Lấy tất cả text từ bricks template (bao gồm children), trả về lowercase
    private function getAllTextFromBricks($elements)
    {
        $texts = [];
        if (!is_array($elements)) return $texts;
        foreach ($elements as $el) {
            if (isset($el['settings']['text']) && is_string($el['settings']['text'])) {
                $texts[] = mb_strtolower($el['settings']['text']);
            }
            if (!empty($el['children']) && is_array($el['children'])) {
                $texts = array_merge($texts, $this->getAllTextFromBricks($el['children']));
            }
        }
        return $texts;
    }


    public function getDataWidgetService($page_id_or_url, $widget_name, $import_key, $name)
    {




        $page_id = is_numeric($page_id_or_url) ? intval($page_id_or_url) : url_to_postid($page_id_or_url);
        $page_url = is_numeric($page_id_or_url) ? get_permalink($page_id_or_url) : $page_id_or_url;
        if (!$page_id) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy page_id',
            ];
        }
        $all_templates = get_post_meta($page_id, '_bricks_page_content_2', false); // lấy tất cả template
        if (!$all_templates || !is_array($all_templates)) {
            return [
                'success' => false,
                'message' => 'Không có dữ liệu bricks',
            ];
        }
        // Check if this URL is in list_url and if widget_name/import_key is missing




        if (empty($widget_name) || empty($import_key)) {
            // Extract Bricks content as plain text (like fallback)
            $content = '';
            foreach ($all_templates as $serialized) {
                $data = is_string($serialized) ? @unserialize($serialized) : (is_array($serialized) ? $serialized : null);
                if (!$data || !is_array($data)) continue;
                $content .= $this->getBricksPlainText($data);
            }
            $content = trim($content);


            if (empty($content)) {
                return [
                    'success' => false,
                    'message' => 'Không lấy được nội dung bricks để phân tích',
                ];
            }


            $filtered = $this->convertDataByOpenAI($content, ['id' => $page_id, 'url' => $page_url]);
            if (isset($filtered['success']) && $filtered['success'] === false) {
                return $filtered;
            }
            return [
                'success' => true,
                'data' => $filtered,
            ];
        }
        // 1. Kiểm tra trực tiếp page content có widget_name không
        // kiểm tra name có có trùng widget_name không, nếu có trùng thì phải csv theo thứ tự tại 1 page có nhiều widget có chung widget_name nên 

        $csv_url = [];
        foreach ($all_templates as $serialized) {
            $data = is_string($serialized) ? @unserialize($serialized) : (is_array($serialized) ? $serialized : null);
            if (!$data || !is_array($data)) continue;
            $find_csv_url = function ($elements) use (&$find_csv_url, &$csv_url, $widget_name, $import_key) {
                foreach ($elements as $el) {
                    if (isset($el['name']) && $el['name'] === $widget_name) {
                        if (!empty($el['settings'][$import_key]['url'])) {
                            $csv_url[] = $el['settings'][$import_key]['url'];
                        }
                    }
                    if (!empty($el['children']) && is_array($el['children'])) {
                        $find_csv_url($el['children']);
                    }
                }
            };

            $find_csv_url($data);
        }





        // 2. Nếu không có, tìm các phần tử name=template để lấy id template, duyệt từng template
        if (!$csv_url) {
            $template_ids = [];
            foreach ($all_templates as $serialized) {
                $data = is_string($serialized) ? @unserialize($serialized) : (is_array($serialized) ? $serialized : null);
                if (!$data || !is_array($data)) continue;
                foreach ($data as $el) {
                    if (isset($el['name']) && $el['name'] === 'template' && !empty($el['settings']['template'])) {
                        $template_ids[] = $el['settings']['template'];
                    }
                }
            }
            foreach ($template_ids as $template_id) {
                $template_content = get_post_meta($template_id, '_bricks_page_content_2', true);
                if (!$template_content) continue;
                $template_data = is_string($template_content) ? @unserialize($template_content) : (is_array($template_content) ? $template_content : null);
                if (!$template_data || !is_array($template_data)) continue;
                $find_csv_url = function ($elements) use (&$find_csv_url, &$csv_url, $widget_name, $import_key) {
                    foreach ($elements as $el) {
                        if (isset($el['name']) && $el['name'] === $widget_name) {
                            if (!empty($el['settings'][$import_key]['url'])) {
                                $csv_url[] = $el['settings'][$import_key]['url'];
                            }
                        }
                        if (!empty($el['children']) && is_array($el['children'])) {
                            $find_csv_url($el['children']);
                        }
                    }
                };
                $find_csv_url($template_data);
            }
        }


        $index = $this->findIndexInGroup($this->settings['list_url'], $name, $page_url);
        $csv_url = $csv_url[$index] ?? null;


        if (!$csv_url) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy file CSV trong widget',
            ];
        }
        // return $this->readCsvFile($csv_url, $widget_name);
        return [
            'success' => true,
            'data' => $this->readCsvFile($csv_url, $name),
            'page_id' => $page_id,
            'page_url' => $page_url
        ];
    }

    function findIndexInGroup(array $listUrl, string $currentName, string $url): ?int
    {
        $curentItem = null;

        // Tìm item theo name và đúng url truyền vào
        foreach ($listUrl as $item) {
            if (
                isset($item['name'], $item['url']) &&
                $item['name'] === $currentName &&
                $item['url'] === $url
            ) {
                $curentItem = $item;
                break;
            }
        }

        if (!$curentItem || !isset($curentItem['widget_name'])) {
            return null;
        }

        // Lọc các item có cùng url và widget_name
        $filtered = array_filter($listUrl, function ($item) use ($curentItem) {
            return isset($item['url'], $item['widget_name']) &&
                $item['url'] === $curentItem['url'] &&
                $item['widget_name'] === $curentItem['widget_name'];
        });

        // Reset index 0,1,...
        $filtered = array_values($filtered);

        // Trả về vị trí của currentName trong nhóm
        foreach ($filtered as $i => $item) {
            if (isset($item['name']) && $item['name'] === $currentName) {
                return $i;
            }
        }

        return 0;
    }



    // Helper: Lấy plain text từ bricks content (không lặp lại text children), đệ quy vào template con nếu gặp widget 'template'
    private function getBricksPlainText($elements)
    {
        $text = '';
        $text_fields = ['text', 'title', 'heading', 'content', 'subtitle', 'description'];
        foreach ($elements as $el) {
            // Nếu là widget template, đệ quy lấy nội dung từ template con
            if (isset($el['name']) && $el['name'] === 'template' && !empty($el['settings']['template'])) {
                $template_id = $el['settings']['template'];
                $template_content = get_post_meta($template_id, '_bricks_page_content_2', true);
                if ($template_content) {
                    $template_data = is_string($template_content) ? @unserialize($template_content) : (is_array($template_content) ? $template_content : null);
                    if ($template_data && is_array($template_data)) {
                        $text .= $this->getBricksPlainText($template_data);
                    }
                }
                continue;
            }
            $found = false;
            if (isset($el['settings']) && is_array($el['settings'])) {
                foreach ($text_fields as $field) {
                    if (!empty($el['settings'][$field]) && is_string($el['settings'][$field])) {
                        $text .= $el['settings'][$field] . "\n";
                        $found = true;
                    }
                }
                // Nếu không có trường nào, lấy toàn bộ textContent của settings (nếu là string)
                if (!$found) {
                    foreach ($el['settings'] as $val) {
                        if (is_string($val) && trim($val) !== '') {
                            $text .= $val . "\n";
                        }
                    }
                }
            }
            if (!empty($el['children']) && is_array($el['children'])) {
                $text .= $this->getBricksPlainText($el['children']);
            }
        }
        return $text;
    }

    private function readCsvFile(string $csv_url, $name): array
    {
        if (!$csv_url) {
            return [
                'success' => false,
                'message' => 'Thiếu đường dẫn file CSV',
            ];
        }

        // Convert URL to local file path if possible
        $file = null;
        if (preg_match('/wp-content\/(.*)/', $csv_url, $matches)) {
            $upload_dir = wp_upload_dir();
            $base_dir = ABSPATH;
            // Try to find the file in the WordPress root
            $file = $base_dir . 'wp-content/' . $matches[1];
            if (!file_exists($file)) {
                // Try uploads dir if not found
                $file = $upload_dir['basedir'] . '/' . $matches[1];
            }
        } else if (filter_var($csv_url, FILTER_VALIDATE_URL)) {
            $file = $csv_url; // Remote file
        }

        if (!$file || (!file_exists($file) && !filter_var($file, FILTER_VALIDATE_URL))) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy file CSV: ' . $csv_url,
            ];
        }

        // Open local or remote file
        $handle = @fopen($file, "r");
        if (!$handle) {
            return [
                'success' => false,
                'message' => 'File open failed: ' . $file
            ];
        }

        $csvData = [];
        while (($line = fgetcsv($handle)) !== false) {
            $csvData[] = $line;
        }



        $transposed = [];

        foreach ($csvData as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $transposed[$colIndex][$rowIndex] = $value;
            }
        }

        $csvData = $transposed; // Replace original data with transposed data




        fclose($handle);


        switch ($name) {
            case 'Bảng giá vps/hosting':
                $filtered = $this->convertTablePrice->vnx_price_hosting_v2($csvData);
                break;
            case 'Bảng giá so sánh vps/hosting/firewall':
                $filtered = $this->convertTablePrice->vnx_price_hosting_v1($csvData);
                break;
            case 'Bảng giá thuê máy chủ':
                $filtered = $this->convertTablePrice->local_sever($csvData);
                break;
            case 'Bảng giá Email':
                $filtered = $this->convertTablePrice->email($csvData);
                break;
            case 'Bảng giá so sánh Email':
                $filtered = $this->convertTablePrice->compare_email($csvData);
                break;
            case 'Bảng giá firewall':
                $filtered = $this->convertTablePrice->firewall($csvData);
                break;
            case 'Bảng giá tên miền':
                $filtered = $this->convertTablePrice->doamin($csvData);
                break;
            default:
                return [
                    'success' => false,
                    'message' => 'Widget name không hợp lệ: ',
                ];
        }

        if (isset($filtered['success']) && $filtered['success'] === false) {
            return $filtered;
        }

        return [
            'success' => true,
            'data' => $filtered,
        ];
    }

    public function convertDataByOpenAI($data, $extra = [])
    {
        $apiKey = isset($this->settings['token']) ? $this->settings['token'] : getenv('OPENAI_API_KEY');
        $models = isset($this->settings['models']) ? $this->settings['models'] : 'gpt-4.1';
        $prompt_user = json_encode($data, JSON_UNESCAPED_SLASHES); // 8k

        // Định nghĩa JSON schema cho sản phẩm plans
        $response_format = [
            "type" => "json_schema",
            "json_schema" => [
                "name" => "ProductPlanListWrapper",
                "strict" => true,
                "schema" => [
                    "type" => "object",
                    "properties" => [
                        "name" => [
                            "type" => "string",
                            "description" => "Tên nhóm các gói sản phẩm"
                        ],
                        "plans" => [
                            "type" => "array",
                            "description" => "Danh sách các gói sản phẩm",
                            "items" => [
                                "type" => "object",
                                "properties" => [
                                    "name" => [
                                        "type" => "string",
                                        "description" => "Tên gói sản phẩm, là tên chung của các types sản phẩm trong gói này"
                                    ],
                                    "prices" => [
                                        "type" => "array",
                                        "description" => "Danh sách giá cho gói sản phẩm",
                                        "items" => [
                                            "type" => "object",
                                            "properties" => [
                                                "cycle" => [
                                                    "type" => "string",
                                                    "description" => "Chu kỳ thanh toán (ví dụ: '3 tháng', '6 tháng', '12 tháng')"
                                                ],
                                                "regular" => [
                                                    "type" => "string",
                                                    "description" => "Giá trị nằm ngay trước dấu '|' đầu tiên trong chuỗi giá. Nếu không có để null"
                                                ],
                                                "sale" => [
                                                    "type" => "string",
                                                    "description" => "Là phần nằm sau dấu '|' đầu tiên trong chuỗi — tương ứng với 'Sale Price' (giá khuyến mãi).  '' | 499.000 | 5.880.000 | | Nhập mã giảm thêm 5%: | HTH5 | -> sale = 499.000"
                                                ],

                                                "codeDiscount" => [
                                                    "type" => ["string", "null"],
                                                    "description" => "Mã giảm giá áp dụng (nếu có)"
                                                ],
                                                "discount" => [
                                                    "type" => ["string", "null"],
                                                    "description" => "% giảm giá áp dụng (nếu có), trích xuất từ đoạn mô tả giảm giá vd: Nhập mã giảm thêm 60% -> 60%:"
                                                ]
                                            ],
                                            "required" => ["cycle", "regular", "sale", "codeDiscount", "discount"],
                                            "additionalProperties" => false
                                        ]
                                    ],
                                    "technicalSpecs" => [
                                        "type" => "array",
                                        "description" => "Thông số kỹ thuật của gói sản phẩm",
                                        "items" => [
                                            "type" => "string"
                                        ]
                                    ]
                                ],
                                "required" => ["name", "prices", "technicalSpecs"],
                                "additionalProperties" => false
                            ]
                        ]
                    ],
                    "required" => ["name", "plans"],
                    "additionalProperties" => false
                ]
            ]
        ];


        $prompt_system = $this->settings['prompt_system'];
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'Thiếu OPENAI_API_KEY trong biến môi trường',
            ];
        }

        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $postData = [
            'model' => $models,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => str_replace('\\', '', $prompt_user)
                ],
                [
                    'role' => 'system',
                    'content' => str_replace('\\', '', $prompt_system)
                ]
            ],
            'temperature' => 0,
            'top_p' => 0,
            'max_tokens' => 32768,
            'response_format' => $response_format

        ];
        $args = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ],
            'body'    => json_encode($postData),
            'timeout' => 300,
        ];



        $response = wp_remote_post($endpoint, $args);
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Lỗi khi gọi OpenAI: ' . $response->get_error_message(),
            ];
        }
        $body = wp_remote_retrieve_body($response);

        $result = json_decode($body, true);
        $content = $result['choices'][0]['message']['content'] ?? null;
        if (!$content) {
            return [
                'success' => false,
                'message' => 'Không nhận được dữ liệu từ OpenAI',
            ];
        }
        $content = trim($content);
        if (preg_match('/^```json\\n(.+)```$/s', $content, $matches)) {
            $content = $matches[1];
        }
        $json = json_decode($content, true);
        if (is_array($json)) {
            $result_item = array_merge([
                'id'   => $extra['id'] ?? null,
                'url'  => $extra['url'] ?? null
            ], $json);
            return [
                'success' => true,
                'data' => $result_item
            ];
        }
        return [
            'success' => false,
            'message' => 'Dữ liệu trả về không đúng định dạng JSON',
        ];
    }
}
