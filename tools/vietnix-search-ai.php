<?php
require_once __DIR__ . '/inc/vietnix-search-ai/shared-storage.php';
require_once __DIR__ . '/inc/vietnix-search-ai/class-vietnix-dowload-content.php';
require_once __DIR__ . '/inc/vietnix-search-ai/class-vietnix-get-csv-widget.php';

use ToolsCenter\VNX_Search_Post_AI\VNX_GetCsvWidgetBricks;
use ToolsCenter\VNX_Search_Post_AI\VNX_Download_Content;

class VNXSearchAI_Center
{
    private $embedding_file;
    private $huongdan_embedding_file;
    public $option_name = 'vnx_search_ai';
    public $settings = [];
    private $ignoreContent;
    private $download_content;

    public function __construct()
    {
        new VNX_GetCsvWidgetBricks();
        // Dung chung file embeddings voi vietnix-plugin (xem vnx_search_ai_dir_Center).
        $this->embedding_file = vnx_search_ai_dir_Center() . '/storage/post_embeddings_ai.json';
        $this->huongdan_embedding_file = vnx_search_ai_dir_Center() . '/storage/huongdan_embeddings_ai.json';
        $this->settings = get_option($this->option_name);
        $this->ignoreContent = isset($this->settings['ignoreContent']) ? $this->settings['ignoreContent'] : '';
        $this->download_content = new VNX_Download_Content(['ignoreContent' => $this->ignoreContent]);

        add_action('wp_ajax_vnx_search_ai_get_settings_center', [$this, 'get_settings']);
        add_action('wp_ajax_vnx_search_ai_save_settings_center', [$this, 'save_settings']);
        add_action('wp_ajax_vnx_search_ai_save_embeddings_center', [$this, 'save_post_embeddings']);
        add_action('wp_ajax_vnx_search_ai_search_posts_center', [$this, 'search_posts_by_keyword']);
        add_action('wp_ajax_vnx_search_ai_import_huongdan_embeddings_center', [$this, 'import_huongdan_embeddings']);
        add_action('wp_ajax_vnx_search_ai_list_models_center', [$this, 'get_openai_models']);
        add_action('wp_ajax_vnx_save_post_embeddings_today_center', [$this, 'save_post_embeddings_today']);
        add_action('wp_ajax_vnx_search_ai_download_embeddings_json_center', [$this, 'download_post_embeddings_json']);

        add_action('admin_enqueue_scripts', [$this, 'load_js']);
    }

    /**
     * Đăng ký và load JavaScript
     */
    public function load_js()
    {
        // Chi nap tren trang cua center: nap o moi trang admin (ke ca trang cua vietnix-plugin) thi Vue/CSS
        // cua center chay tren giao dien cua plugin kia va lam nang admin.
        if (!vnx_center_is_own_admin_page()) {
            return;
        }


        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', array('jquery'), '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        wp_enqueue_script('vietnix-search-ai-center', VNX_PLUGIN_URL_CENTER . 'tools/inc/js/vietnix-search-ai.js', array('jquery', 'vuejs-library-center'), '1.0', true);

        wp_localize_script('vietnix-search-ai-center', 'ajax_url',  admin_url('admin-ajax.php'));
    }

    /**
     * Gửi một batch các văn bản lên OpenAI để lấy embedding
     * @param array $texts Mảng các văn bản cần lấy embedding
     * @return array|null Trả về mảng các embedding hoặc null nếu có lỗi
     */
    public function get_openai_embedding_batch($texts)
    {

        $url = 'https://api.openai.com/v1/embeddings';
        $data = [
            'input' => $texts,
            'model' => 'text-embedding-3-small',
        ];


        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->settings['apiKey'],
            ],
            'body' => json_encode($data),
            'timeout' => 15,
        ];
        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("Batch lỗi: $error_message");
            return null;
        }
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        if (!isset($decoded['data'])) {
            error_log("Batch lỗi: " . $body);
            return null;
        }
        return $decoded['data'];
    }

    /**
     * Lưu embedding cho tất cả bài viết đã publish (phân trang, tiết kiệm bộ nhớ)
     * Lưu vào file post_embeddings_ai.json
     * Ghi log chi tiết quá trình xử lý vào embedding_process.txt
     * @return \WP_REST_Response
     */
    public function save_post_embeddings()
    {
        set_time_limit(0);
        try {
            $paged = 1;
            $per_page = 25;
            $all_posts = [];
            $all_posts_count = 0;
            // Đọc dữ liệu output.json để merge thêm vào từng post
            $output_data = [];
            $output_file = vnx_search_ai_dir_Center() . '/storage/output.json';
            if (file_exists($output_file)) {
                $json = file_get_contents($output_file);
                $output_data = json_decode($json, true) ?: [];
            }
            // Lấy multiUrls từ settings và lấy thông tin các page tương ứng
            $multiUrls = isset($this->settings['multiUrls']) ? $this->settings['multiUrls'] : '';
            $multiUrlsArr = array_filter(array_map('trim', explode("\n", $multiUrls)));
            $pages_data = [];
            if (!empty($multiUrlsArr)) {
                $pages_data = $this->get_pages_info_by_urls($multiUrlsArr);
            }
            do {
                $args = array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => $per_page,
                    'paged' => $paged,
                );
                $posts = $this->get_normalized_posts($args);
                $count = count($posts);
                $all_posts_count += $count;
                if ($count === 0) break;
                // Merge output_data vào trang đầu tiên (nếu có)
                if ($paged === 1 && is_array($output_data) && count($output_data) > 0) {
                    $posts = array_merge($posts, $output_data);
                }
                // Merge pages_data vào trang đầu tiên (nếu có)
                if ($paged === 1 && is_array($pages_data) && count($pages_data) > 0) {
                    $posts = array_merge($posts, $pages_data);
                }
                $all_posts = array_merge($all_posts, $posts);
                $paged++;
                unset($posts);
                gc_collect_cycles();
            } while ($count === $per_page);
            $total = $this->process_and_save_embeddings($all_posts);
            return wp_send_json_success(['message' => 'Đã lưu embedding cho tất cả bài viết (phân trang)', 'total_posts_sent' => $total, 'all_posts_count' => $all_posts_count]);
        } catch (\Throwable $th) {
            error_log('VNXSearchAI_Center: Lỗi khi lưu embedding: ' . $th->getMessage());
            return wp_send_json_error(['message' => 'Lỗi khi lưu embedding: ' . $th->getMessage()]);
        }
    }

    /**
     * Chỉ lấy và lưu embedding cho các bài viết mới đăng hôm nay.
     * @return \WP_REST_Response Trả về response thành công hoặc lỗi
     */
    public function save_post_embeddings_today()
    {
        error_log("VNXSearchAI_Center: Bắt đầu lưu embedding cho bài viết mới hôm nay");
        try {
            // 1. Lấy danh sách ID các post hiện tại theo từng trang nhỏ để tránh tràn bộ nhớ
            $current_post_ids = [];
            $paged = 1;
            $per_page = 500; // hoặc 200 tuỳ server
            do {
                $args_all = [
                    'post_type'      => 'post',
                    'post_status'    => 'publish',
                    'posts_per_page' => $per_page,
                    'fields'         => 'ids',
                    'paged'          => $paged,
                ];
                $ids = get_posts($args_all);
                if (empty($ids)) break;
                $current_post_ids = array_merge($current_post_ids, array_map('intval', $ids));
                $paged++;
            } while (count($ids) === $per_page);

            // 2. Đọc ID các bài đã có embedding (không load toàn bộ file vào RAM)
            $existing_ids = [];
            if (file_exists($this->embedding_file)) {
                $handle = fopen($this->embedding_file, 'r');
                if ($handle) {
                    $first = true;
                    $buffer = '';
                    while (($line = fgets($handle)) !== false) {
                        $line = trim($line);
                        if ($first) {
                            $line = ltrim($line, "[");
                            $first = false;
                        }
                        $buffer .= $line;
                        while (($pos = strpos($buffer, '}')) !== false) {
                            $jsonObj = substr($buffer, 0, $pos + 1);
                            $buffer = substr($buffer, $pos + 1);
                            $jsonObj = trim($jsonObj, ",\n ");
                            if (!$jsonObj) continue;
                            $item = json_decode($jsonObj, true);
                            if (is_array($item) && isset($item['ID'])) {
                                $existing_ids[(int)$item['ID']] = true;
                            }
                        }
                    }
                    fclose($handle);
                }
            }

            $option = get_option($this->option_name);
            $syncTime = isset($option['syncTime']) ? $option['syncTime'] : '00:00';
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $today = date('Y-m-d');
            $start = $yesterday . ' ' . $syncTime . ':00';
            $end = $today . ' ' . $syncTime . ':00';
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'date_query' => array(
                    array(
                        'after' => $start,
                        'before' => $end,
                        'inclusive' => true,
                    ),
                ),
                'posts_per_page' => -1,
            );
            $posts = $this->get_normalized_posts($args);
            if (empty($posts)) {
                return wp_send_json_success(['message' => 'Không có bài viết mới trong khoảng thời gian đồng bộ']);
            }

            // Lọc ra các post chưa có embedding
            $new_posts = array_filter($posts, function ($post) use ($existing_ids) {
                return !isset($existing_ids[(int)$post['ID']]);
            });
            if (empty($new_posts)) {
                return wp_send_json_success(['message' => 'Không có bài viết mới cần thêm embedding']);
            }

            // Lấy embedding cho các bài mới
            $total = 0;
            $new_items = [];
            $chunks = array_chunk($new_posts, 25);
            foreach ($chunks as $chunkPosts) {
                $all_content_chunks = [];
                $post_part_map = [];
                foreach ($chunkPosts as $idx => $post) {
                    $text_for_embedding = $post['title'] . "\n" . $post['excerpt'] . "\nCategories: " . $post["categories"] . "\n" . $post['content'];
                    $content_chunks = $this->split_content_chunks($text_for_embedding, 10000);
                    foreach ($content_chunks as $part) {
                        $all_content_chunks[] = $part;
                        $post_part_map[] = $idx;
                    }
                }
                $batch_embeddings = $this->get_openai_embedding_batch($all_content_chunks);
                if (!$batch_embeddings) {
                    error_log("VNXSearchAI_Center: Lỗi khi lấy embedding từ OpenAI");
                    continue;
                }
                $emb_map = [];
                foreach ($batch_embeddings as $k => $emb) {
                    $idx = $post_part_map[$k];
                    if (!isset($emb_map[$idx])) $emb_map[$idx] = [];
                    $emb_map[$idx][] = $emb['embedding'] ?? [];
                }
                foreach ($chunkPosts as $j => $post) {
                    $item = [
                        'ID' => $post['ID'],
                        'title' => $post['title'],
                        'link' => $post['link'],
                        'content' => $post['content'],
                        'excerpt' => $post['excerpt'],
                        'categories' => $post['categories'],
                        'thumbnail' => $post['thumbnail'],
                        'embedding' => $this->merge_embeddings($emb_map[$j] ?? []),
                    ];
                    foreach ($post as $k => $v) {
                        if (!isset($item[$k])) {
                            $item[$k] = $v;
                        }
                    }
                    $new_items[] = $item;
                    $total++;
                }
                sleep(2);
            }

            // Ghi thêm vào file embedding (không ghi đè)
            if (!empty($new_items)) {
                $file = $this->embedding_file;
                // Đảm bảo file tồn tại và có dạng mảng JSON
                if (!file_exists($file) || filesize($file) < 2) {
                    // Tạo file mới với mảng JSON
                    file_put_contents($file, "[]");
                }
                // Đọc file, loại bỏ ký tự ] cuối cùng, thêm dấu phẩy nếu cần
                $fp = fopen($file, 'c+');
                if ($fp) {
                    // Lock file để tránh ghi đồng thời
                    flock($fp, LOCK_EX);
                    fseek($fp, 0, SEEK_END);
                    $size = ftell($fp);
                    if ($size < 2) {
                        // File rỗng hoặc chỉ có []
                        ftruncate($fp, 0);
                        fwrite($fp, "[");
                        $first = true;
                    } else {
                        // Tìm vị trí trước dấu ] cuối cùng
                        $pos = $size - 1;
                        fseek($fp, $pos);
                        $last = fgetc($fp);
                        while ($last !== false && $last !== ']') {
                            $pos--;
                            if ($pos < 0) break;
                            fseek($fp, $pos);
                            $last = fgetc($fp);
                        }
                        if ($pos > 0) {
                            ftruncate($fp, $pos);
                            fseek($fp, $pos);
                            // Kiểm tra nếu file chỉ có [ thì không thêm dấu phẩy
                            $first = ($pos == 1);
                        } else {
                            // File chỉ có [
                            ftruncate($fp, 1);
                            fseek($fp, 1);
                            $first = true;
                        }
                    }
                    // Ghi các item mới
                    foreach ($new_items as $item) {
                        if (!$first) {
                            fwrite($fp, ",\n");
                        }
                        fwrite($fp, json_encode($item, JSON_UNESCAPED_UNICODE));
                        $first = false;
                    }
                    fwrite($fp, "]");
                    fflush($fp);
                    flock($fp, LOCK_UN);
                    fclose($fp);
                }
            }

            return wp_send_json_success(['message' => 'Đã lưu embedding cho bài viết mới trong khoảng thời gian đồng bộ', 'total_posts_sent' => $total]);
        } catch (\Throwable $th) {
            error_log('VNXSearchAI_Center: Lỗi khi lưu embedding hôm nay: ' . $th->getMessage());
            return wp_send_json_error(['message' => 'Lỗi khi lưu embedding: ' . $th->getMessage()]);
        }
    }

    /**
     * Import huongdan: chỉ nhận array post từ request (POST['posts']), lấy embedding và lưu vào huongdan_embeddings_ai.json
     */
    public function import_huongdan_embeddings()
    {
        try {
            $data = null;
            if (isset($_POST['posts'])) {
                $data = json_decode(stripslashes($_POST['posts']), true);
            }
            if (empty($data) || !is_array($data)) {
                return wp_send_json_error(['message' => 'Dữ liệu truyền lên không hợp lệ']);
            }
            // Validate định dạng từng post
            $required = ['ID', 'title', 'link', 'excerpt', 'categories', 'thumbnail'];
            foreach ($data as $item) {
                foreach ($required as $f) {
                    if (!isset($item[$f])) {
                        return wp_send_json_error(['message' => 'Mỗi post phải có đủ các trường: ' . implode(', ', $required)]);
                    }
                }
            }
            // Nếu file chưa tồn tại thì tạo file rỗng trước khi ghi
            if (!file_exists($this->huongdan_embedding_file)) {
                file_put_contents($this->huongdan_embedding_file, json_encode([], JSON_UNESCAPED_UNICODE));
            }
            $all_content_chunks = [];
            $item_map = [];
            foreach ($data as $idx => $item) {
                $text = '';
                if (isset($item['title'])) $text .= $item['title'] . "\n";
                if (isset($item['excerpt'])) $text .= $item['excerpt'] . "\n";
                if (isset($item['content'])) $text .= $item['content'];
                $chunks = $this->split_content_chunks($text, 10000);
                foreach ($chunks as $chunk) {
                    $all_content_chunks[] = $chunk;
                    $item_map[] = $idx;
                }
            }
            $batch_embeddings = $this->get_openai_embedding_batch($all_content_chunks);
            if (!$batch_embeddings) {
                return wp_send_json_error(['message' => 'Lỗi khi lấy embedding từ OpenAI']);
            }
            $emb_map = [];
            foreach ($batch_embeddings as $k => $emb) {
                $idx = $item_map[$k];
                if (!isset($emb_map[$idx])) $emb_map[$idx] = [];
                $emb_map[$idx][] = $emb['embedding'] ?? [];
            }
            $result = [];
            foreach ($data as $j => $item) {
                $item['embedding'] = $this->merge_embeddings($emb_map[$j] ?? []);
                $result[] = $item;
            }
            file_put_contents($this->huongdan_embedding_file, json_encode($result, JSON_UNESCAPED_UNICODE));
            return wp_send_json_success(['message' => 'Đã import và lưu embedding cho huongdan', 'total' => count($result)]);
        } catch (\Throwable $e) {
            error_log('VNXSearchAI_Center: Lỗi import_huongdan_embeddings: ' . $e->getMessage());
            return wp_send_json_error(['message' => 'Lỗi import_huongdan_embeddings: ' . $e->getMessage()]);
        }
    }

    /**
     * Tìm kiếm bài viết theo keyword (tối ưu bộ nhớ, không load toàn bộ file embedding)
     * Sử dụng OpenAI để lấy embedding cho keyword
     * Đọc từng post một từ file embedding, chỉ giữ lại các post có score phù hợp
     * @param WP_REST_Request|string $request
     * @return \WP_REST_Response
     */
    public function search_posts_by_keyword($request)
    {
        try {
            if (is_string($request)) {
                $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
            } else {
                $keyword = $request->get_param('keyword');
            }
            if (!$keyword) {
                error_log("Lỗi search_posts_by_keyword: Không có keyword");
                return [];
            }
            $embeddings = $this->get_openai_embedding_batch([$keyword]);
            $embedding = $embeddings && isset($embeddings[0]['embedding']) ? $embeddings[0]['embedding'] : null;
            if (!$embedding) {
                error_log("Lỗi search_posts_by_keyword: Không lấy được embedding cho keyword $keyword");
                return [];
            }
            $limitScore = isset($this->settings['limitScore']) ? floatval($this->settings['limitScore']) : 0.3;
            $results = [];
            $embedding_files = [$this->embedding_file, $this->huongdan_embedding_file];
            foreach ($embedding_files as $file) {
                if (!file_exists($file) || !is_readable($file)) continue;
                $handle = fopen($file, 'r');
                if (!$handle) continue;
                // Bỏ ký tự đầu tiên ([)
                $first = true;
                $buffer = '';
                while (($line = fgets($handle)) !== false) {
                    $line = trim($line);
                    if ($first) {
                        $line = ltrim($line, "[");
                        $first = false;
                    }
                    $buffer .= $line;
                    // Tìm dấu }, kết thúc một object
                    while (($pos = strpos($buffer, '}')) !== false) {
                        $jsonObj = substr($buffer, 0, $pos + 1);
                        $buffer = substr($buffer, $pos + 1);
                        $jsonObj = trim($jsonObj, ",\n ");
                        if (!$jsonObj) continue;
                        $post = json_decode($jsonObj, true);
                        if (!is_array($post) || !isset($post['embedding'])) continue;
                        $score = $this->cosine_similarity_fast($embedding, $post['embedding']);
                        if ($score === false || is_nan($score)) continue;
                        if ($score > $limitScore) {
                            $item = [
                                'title' => $post['title'] ?? '',
                                'link' => $post['link'] ?? '',
                                'excerpt' => $post['excerpt'] ?? '',
                                'categories' => $post['categories'] ?? [],
                                'score' => $score,
                            ];
                            $results[] = $item;
                        }
                    }
                }
                fclose($handle);
            }
            usort($results, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            return wp_send_json_success($results);
        } catch (\Throwable $e) {
            error_log('VNXSearchAI_Center: Lỗi search_posts_by_keyword: ' . $e->getMessage());
            return wp_send_json_error(['message' => 'Lỗi search_posts_by_keyword: ' . $e->getMessage()]);
        }
    }

    /**
     * Tính cosine similarity nhanh giữa 2 vector (không kiểm tra nhiều điều kiện)
     */
    private function cosine_similarity_fast($vec1, $vec2)
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;
        $count = min(count($vec1), count($vec2));
        if ($count === 0) return 0;
        for ($i = 0; $i < $count; $i++) {
            $dot += $vec1[$i] * $vec2[$i];
            $normA += $vec1[$i] * $vec1[$i];
            $normB += $vec2[$i] * $vec2[$i];
        }
        if ($normA === 0 || $normB === 0) return 0;
        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Lấy cài đặt từ database
     * Nếu chưa có thì khởi tạo với giá trị mặc định
     */
    public function get_settings()
    {
        try {
            $option = get_option($this->option_name);
            if (!$option) {
                $option = [
                    'apiKey' => '',
                    'syncTime' => '',
                    'limitScore' => 0.75,
                    'ignoreContent' => '',
                ];
                update_option($this->option_name, $option);
            }
            // Thêm multiUrls nếu chưa có
            if (!isset($option['multiUrls'])) {
                $option['multiUrls'] = '';
            }
            if (!isset($option['ignoreContent'])) {
                $option['ignoreContent'] = '';
            }
            wp_send_json_success($option);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Lưu cài đặt từ form
     * Chỉ lưu các trường cần thiết: apiKey, syncTime, limitScore
     * @return \WP_REST_Response Trả về response thành công hoặc lỗi
     */
    public function save_settings()
    {
        try {
            $apiKey = isset($_POST['apiKey']) ? sanitize_text_field($_POST['apiKey']) : '';
            $syncTime = isset($_POST['syncTime']) ? sanitize_text_field($_POST['syncTime']) : '';
            $limitScore = isset($_POST['limitScore']) ? floatval($_POST['limitScore']) : 0.75;
            $multiUrls = isset($_POST['multiUrls']) ? sanitize_textarea_field($_POST['multiUrls']) : '';
            $ignoreContent = isset($_POST['ignoreContent']) ? sanitize_textarea_field($_POST['ignoreContent']) : '';

            $option = get_option($this->option_name);
            $option['apiKey'] = $apiKey;
            $option['syncTime'] = $syncTime;
            $option['limitScore'] = $limitScore;
            $option['multiUrls'] = $multiUrls;
            $option['ignoreContent'] = $ignoreContent;

            update_option($this->option_name, $option);

            error_log("VNXSearchAI_Center: Cập nhật thời gian đồng bộ 1: $syncTime");

            wp_send_json_success(['message' => 'Settings saved']);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /** Hàm gộp các embedding thành một vector trung bình
     * Nếu không có embedding nào thì trả về mảng rỗng
     * Nếu có thì tính trung bình cộng từng phần tử
     * @param array $embeddings Mảng các embedding vector
     * @return array Mảng embedding trung bình
     *
     * Ví dụ:
     * $embeddings = [
     *     [0.1, 0.2, 0.3],
     *     [0.4, 0.5, 0.6],
     * ];
     * $avg = $this->merge_embeddings($embeddings);
     * // $avg sẽ là [0.25, 0.35, 0.45]
     */
    private function merge_embeddings($embeddings)
    {
        if (empty($embeddings)) return [];
        $dim = count($embeddings[0]);
        $sum = array_fill(0, $dim, 0);
        foreach ($embeddings as $emb) {
            for ($i = 0; $i < $dim; $i++) {
                $sum[$i] += $emb[$i];
            }
        }
        $count = count($embeddings);
        return array_map(function ($v) use ($count) {
            return $v / $count;
        }, $sum);
    }

    /**
     * Chia nội dung thành các chunk nhỏ hơn để gửi lên OpenAI
     * Mỗi chunk tối đa 10000 ký tự (theo giới hạn của OpenAI)
     * @param string $content Nội dung cần chia
     * @param int $max_length Độ dài tối đa mỗi chunk
     * @return array Mảng các chunk nội dung
     */
    private function split_content_chunks($content, $max_length = 10000)
    {
        $chunks = [];
        $len = mb_strlen($content);
        for ($i = 0; $i < $len; $i += $max_length) {
            $chunks[] = mb_substr($content, $i, $max_length);
        }
        return $chunks;
    }

    /**
     * Lấy danh sách bài viết theo điều kiện truy vấn WP_Query
     * @param array $args Tham số WP_Query
     * @return array Mảng bài viết đã chuẩn hóa
     */
    private function get_normalized_posts($args)
    {
        $query = new \WP_Query($args);
        $posts = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $link = get_permalink();
                $title = get_the_title();
                $raw_excerpt = get_the_excerpt();
                $excerpt = preg_replace('/<!--.*?-->/s', '', $raw_excerpt);
                $excerpt = strip_tags($excerpt);
                $content = get_the_content();
                $content = preg_replace('/<!--.*?-->/s', '', $content);
                $content = strip_tags($content);
                $content = str_replace('&nbsp;', ' ', $content);
                $posts[] = [
                    'ID' => get_the_ID(),
                    'title' => $title,
                    'link' => $link,
                    'content' => $content,
                    'excerpt' => $excerpt,
                    'categories' => (isset(get_the_category()[0]) && get_the_category()[0]->name === 'Uncategorized') ? "" : (isset(get_the_category()[0]) ? get_the_category()[0]->name : ""),
                    'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'full') ?: '',
                ];
            }
            wp_reset_postdata();
        }
        return $posts;
    }

    /**
     * Xử lý embedding cho 1 mảng bài viết, merge vào file embedding hiện tại
     * @param array $posts
     * @return int Số bài đã xử lý
     */
    private function process_and_save_embeddings($posts)
    {
        try {
            if (empty($posts)) {
                return 0;
            }
            $file = $this->embedding_file;
            $dir = dirname($file);
            if (!is_writable($dir)) {
                $msg = 'VNXSearchAI_Center: Không có quyền ghi vào thư mục: ' . $dir;
                error_log($msg);
                return 0;
            }
            // Đọc dữ liệu output.json để merge thêm vào từng post
            $output_data = [];
            $output_file = vnx_search_ai_dir_Center() . '/storage/output.json';
            if (file_exists($output_file)) {
                $json = file_get_contents($output_file);
                $output_data = json_decode($json, true) ?: [];
            }
            // Merge output.json vào mảng posts (giống [...posts, ...output_data])
            $posts = array_merge($posts, is_array($output_data) ? $output_data : []);

            // Luôn ghi đè file embedding cũ
            $handle = fopen($file, 'w');
            if (!$handle) {
                $msg = 'VNXSearchAI_Center: Không mở được file để ghi: ' . $file;
                error_log($msg);
                return 0;
            }
            fwrite($handle, "[");

            // Chia mảng bài viết thành các chunk nhỏ hơn (tối đa 25 bài mỗi chunk)
            $chunks = array_chunk($posts, 25);
            $total = 0;
            foreach ($chunks as $chunk_idx => $chunkPosts) {
                $all_content_chunks = [];
                $post_part_map = [];
                foreach ($chunkPosts as $idx => $post) {
                    $text_for_embedding = $post['title'] . "\n" . $post['excerpt'] . "\nCategories: " . $post["categories"] . "\n" . $post['content'];
                    $content_chunks = $this->split_content_chunks($text_for_embedding, 10000);
                    foreach ($content_chunks as $part) {
                        $all_content_chunks[] = $part;
                        $post_part_map[] = $idx;
                    }
                }
                $batch_embeddings = $this->get_openai_embedding_batch($all_content_chunks);
                if (!$batch_embeddings) {
                    error_log("VNXSearchAI_Center: Lỗi khi lấy embedding từ OpenAI");
                    continue;
                }
                $emb_map = [];
                foreach ($batch_embeddings as $k => $emb) {
                    $idx = $post_part_map[$k];
                    if (!isset($emb_map[$idx])) $emb_map[$idx] = [];
                    $emb_map[$idx][] = $emb['embedding'] ?? [];
                }
                foreach ($chunkPosts as $j => $post) {
                    $item = [
                        'ID' => $post['ID'],
                        'title' => $post['title'],
                        'link' => $post['link'],
                        'content' => $post['content'],
                        'excerpt' => $post['excerpt'],
                        'categories' => $post['categories'],
                        'thumbnail' => $post['thumbnail'],
                        'embedding' => $this->merge_embeddings($emb_map[$j] ?? []),
                    ];
                    foreach ($post as $k => $v) {
                        if (!isset($item[$k])) {
                            $item[$k] = $v;
                        }
                    }
                    if ($total > 0) {
                        fwrite($handle, ",\n");
                    }
                    fwrite($handle, json_encode($item, JSON_UNESCAPED_UNICODE));
                    $total++;
                }
                sleep(2);
            }
            // Đóng mảng JSON
            fwrite($handle, "]");
            fclose($handle);
            return $total;
        } catch (\Throwable $e) {
            $msg = 'VNXSearchAI_Center: Lỗi process_and_save_embeddings: ' . $e->getMessage();
            error_log($msg);
            return 0;
        }
    }

    /**
     * Lấy danh sách model id từ OpenAI API
     * @return void
     */
    public function get_openai_models()
    {
        $apiKey = isset($this->settings['apiKey']) ? $this->settings['apiKey'] : '';
        if (!$apiKey) {
            wp_send_json_error(['message' => 'Chưa cấu hình API Key']);
        }
        $url = 'https://api.openai.com/v1/models';
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
            ],
            'timeout' => 20,
        ];
        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!isset($data['data']) || !is_array($data['data'])) {
            wp_send_json_error(['message' => 'Không lấy được danh sách models']);
        }
        $ids = array_map(function ($item) {
            return $item['id'];
        }, $data['data']);
        wp_send_json_success($ids);
    }

    /**
     * Lấy thông tin các trang theo danh sách URL (tái sử dụng VNX_Download_Content)
     * @param array $urls Danh sách URL của các trang
     * @return array Mảng thông tin các trang
     */
    public function get_pages_info_by_urls($urls)
    {
        $result = [];
        foreach ($urls as $url) {
            $info = $this->download_content->get_info_by_single_url($url);
            if (!empty($info)) {
                // Thay thế &nbsp; thành ' ' trong content
                if (isset($info['content'])) {
                    $info['content'] = str_replace('&nbsp;', ' ', $info['content']);
                }
                // Force categories to 'chính sách'
                $info['categories'] = 'chính sách';
                // Excerpt is first 100 chars of content
                // Lấy excerpt là 100 từ đầu tiên của content
                $words = preg_split('/\s+/', strip_tags($info['content']));
                $excerpt_words = array_slice($words, 0, 100);
                $info['excerpt'] = implode(' ', $excerpt_words);
                $result[] = $info;
            }
        }
        return $result;
    }

    /**
     * Download file post_embeddings_ai.json dưới dạng JSON thuần,
     * chỉ giữ lại các trường: title, link, excerpt, content, categories.
     * Loại bỏ trường embedding và các trường nội bộ khác.
     *
     * Endpoint: wp_ajax_vnx_search_ai_download_embeddings_json
     * Method: POST (hoặc GET) với nonce admin
     */
    public function download_post_embeddings_json()
    {
        $file = $this->embedding_file;

        if (!file_exists($file) || !is_readable($file)) {
            wp_send_json_error(['message' => 'File post_embeddings_ai.json không tồn tại hoặc không thể đọc.']);
            return;
        }

        $result = [];

        $handle = fopen($file, 'r');
        if (!$handle) {
            wp_send_json_error(['message' => 'Không thể mở file post_embeddings_ai.json.']);
            return;
        }

        $first    = true;
        $buffer   = '';

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($first) {
                $line  = ltrim($line, '[');
                $first = false;
            }
            $buffer .= $line;

            // Tìm từng JSON object trong buffer
            while (($pos = strpos($buffer, '}')) !== false) {
                $jsonObj = substr($buffer, 0, $pos + 1);
                $buffer  = substr($buffer, $pos + 1);
                $jsonObj = trim($jsonObj, ",\n ");
                if (!$jsonObj) continue;

                $item = json_decode($jsonObj, true);
                if (!is_array($item)) continue;

                $result[] = [
                    'title'      => $item['title']      ?? '',
                    'link'       => $item['link']       ?? '',
                    'excerpt'    => $item['excerpt']    ?? '',
                    'content'    => $item['content']    ?? '',
                    'categories' => $item['categories'] ?? '',
                ];
            }
        }
        fclose($handle);

        // Trả về JSON thuần để JS tự convert sang định dạng mong muốn (txt/csv/json)
        wp_send_json($result);
    }
}


new VNXSearchAI_Center();
