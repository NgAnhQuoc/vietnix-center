<?php

use HelperCenter\GoogleAuth;
use HelperCenter\UploadImage;
use HelperCenter\View;


/**
 * Class VNX_ImportDocs_Center
 * 
 * Xử lý việc import tài liệu từ Google Docs và chuyển đổi thành nội dung WordPress
 */
class VNX_ImportDocs_Center
{

    public $gg_auth;
    private $keyword = '';
    private $thumbnail = [
        'html' => '',
        'alt' => '',
        'description' => ''
    ];
    public $image_uploader;
    private $inlineObjects = [];
    private $objectLists = [];
    public $option_name = 'vnx_import_docs';

    public $install_composer_message = "";


    /**
     * Constructor
     */
    public function __construct()
    {


        $this->loadComposer();


        // Đăng ký các action hooks. Chỉ dành cho user đã đăng nhập (không có nopriv):
        // các action này tạo bài, tải file vào Media và ghi option.
        add_action('wp_ajax_vnx_import_docs_center', [$this, 'vnx_import_docs']);
        add_action('wp_ajax_vnx_create_post_from_content_center', [$this, 'createPostFromContent']);
        add_action('wp_ajax_vnx_async_image_to_wordpress_media_library_center', [$this, 'asyncImageToWordpressMediaLibrary']);
        add_action('wp_ajax_vnx_get_list_docs_in_folder_center', [$this, 'getListDocsInFolder']);
        add_action('wp_ajax_vnx_save_settings_center', [$this, 'save_settings']);
        add_action('wp_ajax_vnx_get_settings_center', [$this, 'get_settings']);


        add_action('admin_enqueue_scripts', [$this, 'load_css']);
        add_action('admin_enqueue_scripts', [$this, 'load_js']);

        add_action('admin_notices', [$this, 'display_composer_error']);

        add_action('admin_menu', [$this, 'add_menu_page']);
    }


    public function loadComposer()
    {
        // Khong nap vendor Composer (Guzzle/google-auth) nua: vietnix-plugin cung nap Guzzle/psr7 khac phien ban
        // vao cung namespace -> xung dot class gay fatal. GoogleAuth goi API qua WordPress HTTP API.
        if (!function_exists('openssl_sign')) {
            $this->install_composer_message = 'Tool Import Docs cần PHP extension openssl để xác thực Google.';
            return;
        }

        $this->gg_auth = new GoogleAuth();
        $this->image_uploader = new UploadImage();
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

        wp_enqueue_script('vietnix-import-docs-center', VNX_PLUGIN_URL_CENTER . 'tools/inc/js/vietnix-import-docs.js', array('jquery', 'vuejs-library-center'), vnx_asset_version_Center('tools/inc/js/vietnix-import-docs.js'), true);
    }

    /**
     * Load CSS
     */
    public function load_css()
    {
        if (!vnx_center_is_own_admin_page()) {
            return;
        }



        // Chỉ load CSS khi đang ở trang Import Docs
        wp_enqueue_style(
            'vietnix-plugin-blockquote-center',
            VNX_PLUGIN_URL_CENTER . 'assets/css/gutenberg/blockquote-block.css'
        );

        wp_enqueue_style('vnx-single-post-center', VNX_PLUGIN_URL_CENTER . 'build/css/single_post_v2.css');

        wp_enqueue_style(
            'rank-math-custom-center',
            plugins_url('seo-by-rank-math/assets/front/css/rank-math.css')
        );

        wp_enqueue_style('wp-block-library');

        wp_enqueue_style(
            'google-fonts-roboto-center',
            'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap'
        );

        $pagenow = get_current_screen();

        if ($pagenow->id == 'toplevel_page_vnx_import_docs_center') {
            vietnix_plugin_enqueue_admin_style_Center();
        }


        try {
            $option = get_option($this->option_name);

            if (isset($option['listIdCss']) && is_string($option['listIdCss'])) {
                $listIdCss = explode(',', $option['listIdCss']);
                $listIdCss = array_map('trim', $listIdCss);

                foreach ($listIdCss as $item) {
                    if (!empty($item)) {
                        wp_enqueue_style(
                            'bricks-post-style--center' . esc_attr($item), // nên thêm ID để tránh đụng nhau
                            get_site_url() . '/wp-content/uploads/bricks/css/post-' . $item . '.min.css',
                            [],
                            null
                        );
                    }
                }
            } else {
                error_log('listIdCss is not set or not a string.');
            }
        } catch (\Throwable $e) {
            error_log('Error loading CSS: ' . $e->getMessage());
        }
    }

    /**
     * Thêm vào menu
     */
    public function add_menu_page()
    {
        add_menu_page(
            vnx_center_menu_title('Import Docs'),                         // Page title
            vnx_center_menu_title('Import Docs'),                         // Menu title
            'edit_pages',                          // Capability (Editor trở lên)
            'vnx_import_docs_center',              // Menu slug (khac vietnix-plugin de khong render chong)
            [$this, 'render_import_docs_page'],    // Callback function
            'dashicons-download',                  // Icon
            100                                    // Position
        );
    }
    /**
     * Hiển thị thông báo lỗi khi không cài đặt composer
     */
    public function display_composer_error()
    {
        if (empty($this->install_composer_message)) {
            return;
        }

        // Chi hien tren trang cua center, khong lan sang trang cua vietnix-plugin (cung chua chu vnx/vietnix).
        if (!vnx_center_is_own_admin_page()) {
            return;
        }

        echo '<div class="notice notice-error"><p>' . esc_html($this->install_composer_message) . '</p></div>';
    }

    /**
     * Hiển thị giao diện
     */
    public function render_import_docs_page()
    {
        View::render('tools/partials/standalone_tool_page', [
            'key' => 'vietnix-import-docs',
            'view' => 'tools/vietnix_import_docs',
        ]);
    }


    /**
     * Chặn request AJAX không đủ quyền hoặc sai nonce (dừng luôn bằng JSON 403).
     */
    private function authorize_request($capability = 'edit_posts')
    {
        if (!current_user_can($capability) || !check_ajax_referer('vnx_import_docs_nonce', 'nonce', false)) {
            wp_send_json_error('Bạn không có quyền thực hiện thao tác này', 403);
        }
    }

    /**
     * Lấy cài đặt
     */
    public function get_settings()
    {
        // Cung capability voi menu (edit_pages): doi manage_options thi Editor vao trang
        // nhung tab cai dat khong tai/luu duoc (403).
        $this->authorize_request('edit_pages');

        try {
            $option = get_option($this->option_name);

            if (!$option) {
                $option = [
                    'listIdCss' => []
                ];
                update_option($this->option_name, $option);
            }

            wp_send_json_success($option);
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Lưu cài đặt
     */
    public function save_settings()
    {
        $this->authorize_request('edit_pages');

        try {
            $listIdCss = isset($_POST['listIdCss']) ? sanitize_text_field(wp_unslash($_POST['listIdCss'])) : '';

            $option = get_option($this->option_name);
            $option['listIdCss'] = $listIdCss;

            update_option($this->option_name, $option);

            wp_send_json_success(['message' => 'Đã lưu cài đặt']);
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }


    /**
     * Tạo khối block liên kết liên quan
     * 
     * @param string $slug Slug của category
     * @return string HTML của khối block liên kết liên quan
     */
    public function createRelatedLinksBlock($nameCategory)
    {

        try {
            $category_obj = get_term_by('name', $nameCategory, 'category');

            $category_id = $category_obj->term_id;

            $args = array(
                'post_type' => 'post',
                'cat' => $category_id,
                'posts_per_page' => 3,
                'orderby' => 'date',
                'order' => 'DESC',
                'post_status' => 'publish'
            );

            $query = new WP_Query($args);

            // Tạo cấu trúc dữ liệu mới theo mẫu
            $linksData = [];

            // Thêm các link vào cấu trúc dữ liệu mới
            foreach ($query->posts as $index => $post) {
                $linksData["row-{$index}"] = [
                    "field_66c6aa052e7a5" => [
                        "title" => $post->post_title,
                        "url" => get_the_permalink($post->ID),
                        "target" => "_blank"
                    ],
                    "field_66c6df017a208" => ""
                ];
            }

            // Tạo cấu trúc dữ liệu block theo mẫu mới
            $blockData = [
                "field_66c6a3442e795" => "urls",
                "field_66c6a9802e7a2" => [
                    "field_66c6ab6ef29a5" => "Mọi người cũng xem",
                    "field_66c6a9a22e7a3" => "",
                    "field_66c6a9be2e7a4" => $linksData
                ]
            ];

            $json = json_encode([
                'name' => 'nvx/blockquote-block',
                'data' => $blockData,
                'mode' => 'preview'
            ]);

            $html = "<!-- wp:nvx/blockquote-block {$json} /-->";

            return $html;
        } catch (\Throwable $e) {
            error_log('Error creating related links block: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Tạo khối FAQ từ danh sách câu hỏi và câu trả lời
     * 
     * @param array $listFAQ Danh sách câu hỏi và câu trả lời
     * @return string HTML của khối FAQ
     */
    function createFAQ($listFAQ)
    {
        try {
            if (empty($listFAQ)) {
                return '';
            }

            // Tạo phần JSON cho WordPress block
            $questions = [];
            foreach ($listFAQ as $item) {
                if (!isset($item['question']) || !isset($item['answer'])) {
                    continue;
                }

                $random_id = uniqid();
                $questions[] = [
                    'id' => "faq-question-{$random_id}",
                    'title' => strip_tags($item['question']),
                    'content' => $item['answer'],
                    'visible' => true
                ];
            }

            $json = json_encode(['questions' => $questions]);

            // Tạo phần HTML hiển thị
            $html = '<div class="wp-block-rank-math-faq-block">';
            foreach ($listFAQ as $item) {
                if (!isset($item['question']) || !isset($item['answer'])) {
                    continue;
                }

                $html .= '<div class="rank-math-faq-item">';
                $html .= '<h3 class="rank-math-question">' . esc_html($item['question']) . '</h3>';
                $html .= '<div class="rank-math-answer">' . $item['answer'] . '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';

            // Kết hợp tất cả thành một chuỗi hoàn chỉnh
            return "<!-- wp:rank-math/faq-block {$json} -->\n{$html}\n<!-- /wp:rank-math/faq-block -->";
        } catch (\Throwable $e) {
            error_log('Error creating FAQ: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Escape HTML entities in a string
     * 
     * @param string $string Chuỗi cần escape
     * @return string Chuỗi đã được escape
     */

    function escapeHtml($string)
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }


    /**
     * Xử lý định dạng văn bản từ Google Docs
     * 
     * @param array $textRun Đối tượng textRun từ Google Docs
     * @return string Văn bản đã được định dạng HTML
     */
    private function processTextRun($textRun)
    {
        try {
            $textContent = $this->escapeHtml($textRun['content']);

            if (!isset($textRun['textStyle'])) {
                return $textContent;
            }

            $style = $textRun['textStyle'];
            $tags = [];

            // Xác định các thẻ cần áp dụng bằng switch case
            if (isset($style['bold']) || isset($style['italic']) || isset($style['underline']) || isset($style['strikethrough'])) {
                foreach ($style as $property => $value) {
                    if ($value) {
                        switch ($property) {
                            case 'bold':
                                $tags[] = 'strong';
                                break;
                            case 'italic':
                                $tags[] = 'em';
                                break;
                            case 'underline':
                                $tags[] = 'u';
                                break;
                            case 'strikethrough':
                                $tags[] = 'strike';
                                break;
                        }
                    }
                }
            }

            // Áp dụng các thẻ theo thứ tự
            foreach ($tags as $tag) {
                $textContent = "<{$tag}>{$textContent}</{$tag}>";
            }

            // Xử lý liên kết
            if (isset($textRun['textStyle']['link'])) {
                $link = $textRun['textStyle']['link'];
                $textContent = preg_replace('/<\/?u[^>]*>/i', '', $textContent);
                $textContent = "<a target='_blank' href='" . esc_url($link['url']) . "'>{$textContent}</a>";
            }

            return $textContent;
        } catch (\Throwable $e) {
            error_log('Error processing text run: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Xử lý đoạn văn từ Google Docs
     * 
     * @param array $paragraph Đoạn văn từ Google Docs
     * @return array Thông tin về đoạn văn đã xử lý
     */
    private function processParagraph($paragraph)
    {
        try {
            $text = '';
            $originalContent = '';
            $headingType = '';

            // Xử lý các phần tử trong đoạn văn
            if (isset($paragraph['elements'])) {
                foreach ($paragraph['elements'] as $element) {
                    if (isset($element['textRun'])) {
                        $textRun = $element['textRun'];
                        $originalContent .= $textRun['content'];
                        $text .= $this->processTextRun($textRun);
                    }
                }
            }

            // Xác định kiểu tiêu đề
            if (isset($paragraph['paragraphStyle']['namedStyleType'])) {
                $styleType = $paragraph['paragraphStyle']['namedStyleType'];
                if (strpos($styleType, 'HEADING_') === 0) {
                    $level = substr($styleType, -1);
                    $headingType = "h{$level}";
                }
            }

            // Xác định căn lề bằng switch case
            $alignment = isset($paragraph['paragraphStyle']['alignment']) ? $paragraph['paragraphStyle']['alignment'] : '';
            $style = '';

            switch ($alignment) {
                case 'CENTER':
                    $style = 'text-align: center;';
                    break;
                case 'END':
                    $style = 'text-align: right;';
                    break;
                case 'JUSTIFIED':
                    $style = 'text-align: justify;';
                    break;
                default:
                    $style = 'text-align: left;';
                    break;
            }

            return [
                'text' => $text,
                'originalContent' => $originalContent,
                'headingType' => $headingType,
                'style' => $style,
                'alignment' => $alignment
            ];
        } catch (\Throwable $e) {
            error_log('Error processing paragraph: ' . $e->getMessage());
            return [
                'text' => '',
                'originalContent' => '',
                'headingType' => '',
                'style' => '',
                'alignment' => ''
            ];
        }
    }

    /**
     * Xử lý đoạn văn chứa hình ảnh
     * 
     * @param array $paragraph Đoạn văn từ Google Docs
     * @param int $indexImage Số thứ tự hình ảnh
     * @return string HTML của đoạn văn chứa hình ảnh
     */
    private function processParagraphIamge($paragraph, $indexImage)
    {
        try {
            // Xử lý các phần tử trong đoạn văn
            if (isset($paragraph['elements'])) {

                $index = 0;
                foreach ($paragraph['elements'] as $element) {
                    // Kiểm tra nếu không có textRun thì có thể là hình ảnh
                    if (!isset($element['textRun']) && isset($element['inlineObjectElement'])) {
                        $id = $element['inlineObjectElement']['inlineObjectId'];

                        // Lấy thông tin hình ảnh từ inlineObjects
                        if (isset($this->inlineObjects[$id])) {
                            $object = $this->inlineObjects[$id];

                            // Xử lý đối tượng nội tuyến
                            if (isset($object['inlineObjectProperties']['embeddedObject'])) {
                                $embeddedObject = $object['inlineObjectProperties']['embeddedObject'];

                                // Xử lý hình ảnh
                                if (isset($embeddedObject['imageProperties'])) {
                                    $imageProperties = $embeddedObject['imageProperties'];

                                    if (isset($imageProperties['contentUri'])) {
                                        $imageUrl = $imageProperties['contentUri'];
                                        $imageId = mt_rand(100000, 999999);

                                        if ($indexImage === 0) {
                                            $imageName = sanitize_title($this->keyword);
                                            $html = "<img id='thumbnail' name='{$imageName}' data-description_='_description-image-0_' src=\"" . esc_url($imageUrl) . "\" data-alt_=\"" . "_alt-image-{$indexImage}_" . "\" class=\"wp-image-" . $imageId . "\"/>";
                                            $this->thumbnail['html'] = $html;
                                            return "";
                                        }
                                        // Tạo ID ngẫu nhiên cho hình ảnh
                                        $imageName = sanitize_title($this->keyword) . '-' . $indexImage;

                                        // Tạo định dạng HTML theo yêu cầu
                                        $html = "<!-- wp:image {\"id\":" . $imageId . ",\"sizeSlug\":\"full\",\"linkDestination\":\"none\"} -->";
                                        $html .= "<figure class=\"wp-block-image size-full\"><img name='{$imageName}' src=\"" . esc_url($imageUrl) . "\" alt=\"" . "_alt-image-{$indexImage}_" . "\" class=\"wp-image-" . $imageId . "\"/><figcaption class=\"wp-element-caption\">" . "_description-image-{$indexImage}_" . "</figcaption></figure>";
                                        $html .= "<!-- /wp:image -->";

                                        return $html;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('Error processing paragraph: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Summary of createDescriptionImage
     * @param mixed $originalContent
     * @param mixed $indexImage
     * @param mixed $html
     */
    private function createDescriptionImage($originalContent, $indexImage, $html)
    {
        try {
            $indexImage = $indexImage - 1;



            // Xác định loại mô tả bằng switch case
            $descriptionType = '';
            if (strpos($originalContent, '/des') !== false) {
                $descriptionType = 'description';
            } elseif (strpos($originalContent, '/alt') !== false) {
                $descriptionType = 'alt';
            }

            if (!empty($descriptionType)) {
                switch ($descriptionType) {
                    case 'description':
                        $originalContent = str_replace("/des", "", $originalContent);

                        if ($this->thumbnail['description'] === '') {
                            $this->thumbnail['description'] = $originalContent;
                        }
                        $html = str_replace("_description-image-{$indexImage}_", $originalContent, $html);
                        break;
                    case 'alt':
                        $originalContent = str_replace("/alt", "", $originalContent);
                        if ($this->thumbnail['alt'] === '') {
                            $this->thumbnail['alt'] = $originalContent;
                        }
                        $html = str_replace("_alt-image-{$indexImage}_", $originalContent, $html);
                        break;
                }
            }

            return $html;
        } catch (\Throwable $e) {
            error_log('Error creating description image: ' . $e->getMessage());
            return '';
        }
    }




    /**
     * Xử lý bảng từ Google Docs
     * 
     * @param array $table Bảng từ Google Docs
     * @param string $caption Chú thích cho bảng
     * @return string HTML của bảng
     */
    private function processTable($table, $caption)
    {
        try {
            $rows = [];
            $rowCount = 0;
            $columnCount = 0;

            if (isset($table['tableRows'])) {
                foreach ($table['tableRows'] as $row) {
                    $rowData = [];

                    if (isset($row['tableCells'])) {
                        foreach ($row['tableCells'] as $cell) {
                            $cellContent = '';

                            if (isset($cell['content'])) {
                                foreach ($cell['content'] as $cellElement) {
                                    if (isset($cellElement['paragraph'])) {
                                        $paragraph = $cellElement['paragraph'];
                                        if (isset($paragraph['elements'])) {
                                            foreach ($paragraph['elements'] as $elem) {
                                                if (isset($elem['textRun'])) {
                                                    $cellContent .= $elem['textRun']['content'];
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $rowData[] = $cellContent;
                        }

                        $columnCount = max($columnCount, count($rowData));
                        $rows[] = $rowData;
                        $rowCount++;
                    }
                }
            }

            // Tạo JSON cho block
            $json = json_encode([
                'caption' => $caption,
                'rowCount' => $rowCount,
                'columnCount' => $columnCount
            ]);

            // Tạo HTML
            $html = '<figure class="wp-block-table">
        <table>';

            foreach ($rows as $row) {
                $html .= '<tr>';

                foreach ($row as $cellContent) {
                    $html .= "<td>{$cellContent}</td>";
                }

                $html .= '</tr>';
            }

            $html .= "</table>
        <figcaption class='wp-element-caption'>
        " . esc_html($caption) . "
        </figcaption>
        </figure>";

            // Kết hợp thành block WordPress
            return "<!-- wp:table {$json} -->\n{$html}\n<!-- /wp:table -->";
        } catch (\Throwable $e) {
            error_log('Error processing table: ' . $e->getMessage());
            return '';
        }
    }
    /**
     * Xử lý bảng đầu tiên và tạo khối wp
     * 
     * @param array $table Bảng từ Google Docs
     * @return array Danh sách các liên kết và tiêu đề
     */
    private function processFirstTable($table)
    {
        try {
            $category = "";
            $titleLinks = '';
            $metaTitleContent = '';
            $this->keyword = '';
            $slug = '';
            // Kiểm tra xem bảng có dữ liệu không
            if (!isset($table['tableRows']) || empty($table['tableRows'])) {
                return [
                    'title' => $titleLinks,
                    'category' => $category,
                    'metaTitle' => $metaTitleContent
                ];
            }

            // Lấy tiêu đề từ hàng đầu tiên
            if (isset($table['tableRows'][0]['tableCells'][1]['content'])) {
                $titleLinks = $this->extractTextFromCell($table['tableRows'][0]['tableCells'][1]);
            }

            // Xử lý từng hàng trong bảng
            foreach ($table['tableRows'] as $row) {
                if (!isset($row['tableCells']) || count($row['tableCells']) < 2) {
                    continue;
                }

                // Lấy nội dung của cột đầu tiên
                $firstColumn = $this->extractTextFromCell($row['tableCells'][0]);
                $firstColumn = trim($firstColumn);


                // Xử lý các trường hợp đặc biệt
                switch (strtolower($firstColumn)) {
                    case 'category':
                        $category = $this->extractTextFromCell($row['tableCells'][1]);
                        break;

                    case 'keywords':
                        $this->keyword = trim($this->extractTextFromCell($row['tableCells'][1]));
                        break;

                    case 'meta title':
                        $metaTitleContent = trim($this->extractTextFromCell($row['tableCells'][1]));
                        break;

                    case 'meta des':
                        $metaDescriptionContent = trim($this->extractTextFromCell($row['tableCells'][1]));
                        break;

                    case 'slug':
                        $slug = trim($this->extractTextFromCell($row['tableCells'][1]));
                        break;
                }
            }

            // Trả về kết quả
            return [
                'title' => $titleLinks,
                'category' => $category,
                'metaTitle' => $metaTitleContent,
                'metaDescription' => $metaDescriptionContent,
                'slug' => $slug,
                'keyword' => $this->keyword
            ];
        } catch (\Throwable $e) {
            error_log('Error processing first table: ' . $e->getMessage());
            return [
                'title' => '',
                'category' => '',
                'metaTitle' => '',
                'metaDescription' => '',
                'slug' => '',
                'keyword' => ''
            ];
        }
    }

    /**
     * Trích xuất văn bản từ một ô trong bảng
     * 
     * @param array $cell Ô trong bảng
     * @return string Văn bản được trích xuất
     */
    private function extractTextFromCell($cell)
    {
        try {
            $text = '';

            if (isset($cell['content'])) {
                foreach ($cell['content'] as $content) {
                    if (isset($content['paragraph']['elements'])) {
                        foreach ($content['paragraph']['elements'] as $element) {
                            if (isset($element['textRun']['content'])) {
                                $text .= $element['textRun']['content'];
                            }
                        }
                    }
                }
            }

            return $text;
        } catch (\Throwable $e) {
            error_log('Error extracting text from cell: ' . $e->getMessage());
            return '';
        }
    }


    /**
     * Tạo khối featured snippet từ danh sách các thẻ h3
     * 
     * @param string $h2Title Tiêu đề h2 hiện tại
     * @param array $h3Items Danh sách các thẻ h3
     * @param string $description Mô tả ngắn cho featured snippet
     * @return string HTML của khối featured snippet
     */
    private function createFeaturedSnippet($h2Title, $h3Items, $description = '')
    {
        try {
            $h2Title = str_replace("/featured", "", $h2Title);
            // Tạo danh sách các liên kết từ các thẻ h3
            $linksHtml = '';
            foreach ($h3Items as $index => $item) {
                // Tạo id từ text của h3
                $id = sanitize_title($item);
                // Tạo số thứ tự cho mỗi mục
                $linksHtml .= "<a href=\"#{$id}\">{$item}</a><br>";
            }

            // Tạo HTML cho block
            $html = "<!-- wp:vnx/featured-snippet -->\n";
            $html .= "<div class=\"wp-block-vnx-featured-snippet\">";

            // Thêm tiêu đề h2
            $html .= "<!-- wp:heading {\"placeholder\":\"Adding Featured...\"} -->\n";
            $html .= "<h2 class=\"wp-block-heading\">{$h2Title}</h2>\n";
            $html .= "<!-- /wp:heading -->\n\n";

            // Thêm đoạn mô tả nếu có
            if (!empty($description)) {
                $html .= "<!-- wp:paragraph -->\n";
                $html .= "<p>{$description}</p>\n";
                $html .= "<!-- /wp:paragraph -->\n\n";
            }

            // Thêm danh sách liên kết
            $html .= "<!-- wp:paragraph -->\n";
            $html .= "<p>{$linksHtml}</p>\n";
            $html .= "<!-- /wp:paragraph -->";

            $html .= "</div>\n";
            $html .= "<!-- /wp:vnx/featured-snippet -->";

            return $html;
        } catch (\Throwable $e) {
            error_log('Error creating featured snippet: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Tạo block paragraph với căn lề
     * 
     * @param string $text Nội dung văn bản
     * @param string $alignment Căn lề (CENTER, END, JUSTIFIED, START)
     * @return string HTML của block paragraph
     */
    private function createParagraphBlock($text, $alignment)
    {
        try {
            $json = json_encode([
                'align' => $alignment === 'CENTER' ? 'center' : ($alignment === 'END' ? 'right' : ($alignment === 'JUSTIFIED' ? 'justify' : 'left'))
            ]);

            return "<!-- wp:paragraph {$json} -->\n<p class=\"has-text-align-" . ($alignment === 'CENTER' ? 'center' : ($alignment === 'END' ? 'right' : ($alignment === 'JUSTIFIED' ? 'justify' : 'left'))) . "\">{$text}</p>\n<!-- /wp:paragraph -->";
        } catch (\Throwable $e) {
            error_log('Error creating paragraph block: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Tạo block heading với căn lề
     * 
     * @param string $text Nội dung tiêu đề
     * @param string $headingType Loại heading (h1, h2, h3, ...)
     * @param string $alignment Căn lề (CENTER, END, JUSTIFIED, START)
     * @return string HTML của block heading
     */
    private function createHeadingBlock($text, $headingType, $alignment)
    {
        try {
            $level = (int) substr($headingType, 1);
            $json = json_encode([
                'textAlign' => $alignment === 'CENTER' ? 'center' : ($alignment === 'END' ? 'right' : ($alignment === 'JUSTIFIED' ? 'justify' : 'left')),
                'level' => $level
            ]);

            return "<!-- wp:heading {$json} -->\n<{$headingType} class=\"wp-block-heading has-text-align-" . ($alignment === 'CENTER' ? 'center' : ($alignment === 'END' ? 'right' : ($alignment === 'JUSTIFIED' ? 'justify' : 'left'))) . "\">{$text}</{$headingType}>\n<!-- /wp:heading -->";
        } catch (\Throwable $e) {
            error_log('Error creating heading block: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Tạo khối blockquote với nội dung tùy chỉnh
     * 
     * @param string $content Nội dung của blockquote
     * @param string $type Loại blockquote (warning, info, tip, etc.)
     * @return string HTML của khối blockquote
     */
    private function createBlockquoteBlock($content, $type = 'warning')
    {
        try {
            $content = str_replace("/warning", "", $content);
            $content = str_replace("/quote", "", $content);

            // Tạo dữ liệu cho block
            $blockData = [
                'block_type' => 'basic',
                '_block_type' => 'field_66c6a3442e795',
                'basic_block_basic_block_type' => $type,
                '_basic_block_basic_block_type' => 'field_66c6a4842e799',
                'basic_block_warning_group_content' => $content,
                '_basic_block_warning_group_content' => 'field_66c6ae617b671',
                'basic_block_warning_group' => '',
                '_basic_block_warning_group' => 'field_66c6ae07e911a',
                'basic_block' => '',
                '_basic_block' => 'field_66c6a43b2e798'
            ];

            // Tạo JSON cho block
            $json = json_encode([
                'name' => 'nvx/blockquote-block',
                'data' => $blockData,
                'mode' => 'edit'
            ]);

            // Tạo HTML cho block
            return "<!-- wp:nvx/blockquote-block {$json} /-->";
        } catch (\Throwable $e) {
            error_log('Error creating blockquote block: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Tạo thumbnail cho hình ảnh
     * 
     * @return string HTML của thumbnail hình ảnh
     */
    private function createImageThumbnail()
    {
        try {
            $htmlThumbnail = $this->thumbnail['html'];
            $htmlThumbnail = str_replace("_description-image-0_", $this->thumbnail['description'], $htmlThumbnail);
            $htmlThumbnail = str_replace("_alt-image-0_", $this->thumbnail['alt'], $htmlThumbnail);
            return $htmlThumbnail;
        } catch (\Throwable $e) {
            error_log('Error creating image thumbnail: ' . $e->getMessage());
            return '';
        }
    }

    private function inlineGoogleImages($html)
    {
        if (strpos($html, 'googleusercontent.com') === false) {
            return $html;
        }

        return preg_replace_callback(
            '/(<img\b[^>]*\bsrc=")([^"]*googleusercontent\.com[^"]*)(")/i',
            function ($matches) {
                $dataUri = $this->fetchImageAsDataUri($matches[2]);
                return $matches[1] . ($dataUri ?: $matches[2]) . $matches[3];
            },
            $html
        );
    }

    /**
     * @param string $url
     * @return string|false
     */
    private function fetchImageAsDataUri($url)
    {
        try {
            $response = wp_remote_get($url, ['timeout' => 15]);
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                return false;
            }

            $body = wp_remote_retrieve_body($response);
            if (empty($body)) {
                return false;
            }

            $mime = wp_remote_retrieve_header($response, 'content-type') ?: 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode($body);
        } catch (\Throwable $e) {
            error_log('Error fetching preview image: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Chuyển đổi tài liệu Google Docs sang HTML
     * 
     * @param array $document Tài liệu Google Docs
     * @return array HTML đã được chuyển đổi
     */
    function convertToHtml($document)
    {
        try {
            $html = '';
            $count_table = 0;
            $last_h2 = '';
            $listFAQ = [];
            $inFAQSection = false;
            $indexFAQquestion = 0;
            $currentQuestion = '';
            $contentFirstTable = '';
            $infoFirstTable = [];
            $indexImage = 0;
            $inImageSection = false;

            // Thêm biến để theo dõi các thẻ /featured, /item
            $inFeaturedSnippetSection = false;
            $indexFeaturedSnippet = 0;
            $titleFeaturedSnippet = '';
            $featuredSnippetDescription = '';
            $itemFeaturedSnippet = [];
            $nextParagraphIsDescription = false;

            // Thêm biến để theo dõi danh sách
            $currentListId = '';
            $currentListItems = [];
            $inListSection = false;

            if (!isset($document['body']['content'])) {
                return $html;
            }

            // Lấy danh sách các đối tượng nội tuyến
            $this->inlineObjects = [];
            if (isset($document['inlineObjects'])) {
                $this->inlineObjects = $document['inlineObjects'];
                $this->objectLists = $document['lists'] ?? [];
            }

            foreach ($document['body']['content'] as $content) {
                $contentType = $this->determineContentType($content);

                switch ($contentType) {
                    case 'table':
                        // Nếu đang trong danh sách, kết thúc danh sách trước khi xử lý bảng
                        if ($inListSection) {

                            $html .= $this->createListBlock($currentListItems);
                            $currentListItems = [];
                            $inListSection = false;
                            $currentListId = '';
                        }

                        $count_table++;
                        if ($count_table === 1) {
                            $contentFirstTable = $content['table'];
                            $infoFirstTable = $this->processFirstTable($contentFirstTable);
                            break;
                        }
                        $html .= $this->processTable($content['table'], $last_h2);
                        break;

                    case 'paragraph':

                        $paragraph = $content['paragraph'];

                        // Kiểm tra nếu đoạn văn có thuộc tính bullet (là một phần của danh sách)
                        if (isset($paragraph['bullet'])) {
                            // Nếu chưa bắt đầu danh sách, bắt đầu một danh sách mới
                            if (!$inListSection) {
                                $inListSection = true;
                                $currentListId = $paragraph['bullet']['listId'];
                                $currentListItems = [];
                            }

                            // Lấy nội dung văn bản từ đoạn văn
                            $result = $this->processParagraph($paragraph);
                            $text = $result['text'];


                            $originalContent = $result['originalContent'];
                            // Thêm mục vào danh sách hiện tại

                            if (strpos($text, "/item") !== false) {
                                $text = str_replace("/item", "", $text);
                                $originalContent = str_replace("/item", "", $originalContent);
                                $itemFeaturedSnippet[] = $originalContent;
                            }

                            $currentListItems[] = trim($text);
                            break;
                        }

                        //nếu có hình ảnh thì xử lý hình ảnh
                        $paragraphHasImage = false;
                        if (isset($paragraph['elements'])) {
                            foreach ($paragraph['elements'] as $paragraphElement) {
                                if (isset($paragraphElement['inlineObjectElement'])) {
                                    $paragraphHasImage = true;
                                    break;
                                }
                            }
                        }

                        if ($paragraphHasImage) {
                            // Nếu đang trong danh sách, kết thúc danh sách trước khi xử lý hình ảnh
                            if ($inListSection) {
                                $html .= $this->createListBlock($currentListItems, $currentListId);
                                $currentListItems = [];
                                $inListSection = false;
                                $currentListId = '';
                            }


                            $result = $this->processParagraphIamge($paragraph, $indexImage);
                            $indexImage++;
                            $inImageSection = true;

                            $html .= $result;




                            break;
                        } else {
                            // Nếu đang trong danh sách, kết thúc danh sách trước khi xử lý đoạn văn
                            if ($inListSection) {


                                if ($inFAQSection) {
                                    $fomartlistinFAQ = '<ul><li>' . implode('</li><li>', $currentListItems) . '</li></ul>';
                                    $listFAQ[$indexFAQquestion - 1]['answer'] .= $fomartlistinFAQ;
                                    $inListSection = false;
                                } else {
                                    $html .= $this->createListBlock($currentListItems, $currentListId);
                                    $currentListItems = [];
                                    $inListSection = false;
                                    $currentListId = '';
                                }
                            }

                            $result = $this->processParagraph($paragraph);
                        }
                        $text = $result['text'];
                        $originalContent = $result['originalContent'];
                        $headingType = $result['headingType'];
                        $alignment = $result['alignment'];

                        // Bỏ qua đoạn văn trống
                        if (empty(trim($text)) || trim($originalContent) == 'OUTLINE') {
                            break;
                        }

                        // xử lý tiêu đề h1
                        if ($headingType === 'h1') {
                            // Nếu có tiêu đề h1, tạo tiêu đề và lưu thông tin
                            if (empty($infoFirstTable['metaTitle'])) {
                                $infoFirstTable['metaTitle'] = $originalContent;
                                break;
                            }
                        }
                        // Xử lý tiêu đề h2 và FAQ
                        if ($headingType === 'h2') {
                            // Nếu đang trong phần FAQ và gặp h2 mới, kết thúc phần FAQ
                            if ($inFAQSection) {
                                $faqHtml = $this->createFAQ($listFAQ);
                                $html .= $faqHtml;
                                $listFAQ = [];
                                $inFAQSection = false;
                            }

                            // Nếu đang trong phần featured snippet và gặp h2 mới, kết thúc phần featured snippet
                            if ($inFeaturedSnippetSection) {
                                $featuredSnippetHtml = $this->createFeaturedSnippet($titleFeaturedSnippet, $itemFeaturedSnippet, $featuredSnippetDescription);
                                $index = $indexFeaturedSnippet - 1;
                                $html = str_replace("_featured-snippet-{$index}_", $featuredSnippetHtml, $html);
                                $itemFeaturedSnippet = [];
                                $featuredSnippetDescription = '';
                            }

                            $last_h2 = $originalContent;

                            // Nếu h2 là "FAQ", bắt đầu phần FAQ
                            if (strpos(strtolower($last_h2), "faq") !== false) {
                                $inFAQSection = true;
                                break;
                            }
                        }

                        // Thêm đoạn văn hoặc tiêu đề vào HTML
                        if (!empty($text)) {

                            // Xử lý FAQ
                            if ($inFAQSection) {
                                if ($headingType === 'h3') {
                                    // Lưu câu hỏi hiện tại
                                    $currentQuestion = $originalContent;

                                    $listFAQ[$indexFAQquestion] = [
                                        'question' => $originalContent,
                                    ];
                                    $indexFAQquestion++;
                                    break;
                                } else {
                                    // Nếu đang trong phần FAQ và có câu hỏi hiện tại, thêm câu trả lời
                                    if (!empty($currentQuestion)) {
                                        $listFAQ[$indexFAQquestion - 1]['answer'] .= '<br>' . $text;
                                    } else {
                                        // Nếu không có câu hỏi hiện tại, thêm vào HTML bình thường
                                        if ($headingType) {
                                            $html .= $this->createHeadingBlock($text, $headingType, $alignment);
                                        } else {
                                            $html .= $this->createParagraphBlock($text, $alignment);
                                        }
                                    }
                                    break;
                                }
                            }

                            // Xử lý featured snippet
                            // có tồn tại /featured thì bắt đầu phần featured snippet
                            if (strpos($originalContent, "/featured") !== false) {
                                $inFeaturedSnippetSection = true;
                                $html .= "_featured-snippet-{$indexFeaturedSnippet}_";
                                $indexFeaturedSnippet++;
                                $nextParagraphIsDescription = true;
                                $originalContent = str_replace("/featured", "", $originalContent);
                                $text = str_replace("/featured", "", $text);
                                $titleFeaturedSnippet = $originalContent;

                                break;
                            }

                            // có tồn tại /item thì thêm vào mảng itemFeaturedSnippet
                            if (strpos($originalContent, "/item") !== false) {
                                $originalContent = str_replace("/item", "", $originalContent);
                                $text = str_replace("/item", "", $text);
                                $itemFeaturedSnippet[] = $originalContent;
                            }

                            if ($nextParagraphIsDescription) {
                                if ($headingType === 'h3') {
                                    $featuredSnippetDescription = "";
                                    $nextParagraphIsDescription = false;
                                } else {
                                    $featuredSnippetDescription = $originalContent;
                                    $nextParagraphIsDescription = false;
                                    break;
                                }
                            }


                            if ($inImageSection && (strpos($originalContent, '/des') !== false || strpos($originalContent, '/alt') !== false)) {
                                $description = $this->createDescriptionImage($originalContent, $indexImage, $html);
                                $html = $description;
                                break;
                            }

                            if (strpos(strtolower($originalContent), '/warning') !== false || strpos(strtolower($originalContent), '/quote') !== false) {
                                $html .= $this->createBlockquoteBlock($text, 'warning');
                                break;
                            }

                            // Thêm tiêu đề hoặc đoạn văn vào HTML bình thường
                            if ($headingType) {
                                $html .= $this->createHeadingBlock($text, $headingType, $alignment);
                            } else {
                                $html .= $this->createParagraphBlock($text, $alignment);
                            }
                        }

                        break;

                    case 'list':

                        // không lọt vào đây
                        if ($inListSection) {
                            $html .= $this->createListBlock($currentListItems, $currentListId);
                            $currentListItems = [];
                            $inListSection = false;
                            $currentListId = '';
                        }
                        break;
                }
            }

            // Xử lý phần FAQ cuối cùng nếu còn
            if ($inFAQSection && !empty($listFAQ)) {
                $faqHtml = $this->createFAQ($listFAQ);
                $html .= $faqHtml;
            }

            // Xử lý phần featured snippet cuối cùng nếu còn
            if ($inFeaturedSnippetSection && !empty($h3Items)) {
                $featuredSnippetHtml = $this->createFeaturedSnippet($last_h2, $h3Items, $featuredSnippetDescription);
                $index = $indexFeaturedSnippet - 1;
                $html = str_replace("_featured-snippet-{$index}_", $featuredSnippetHtml, $html);
                $h3Items = [];
                $featuredSnippetDescription = '';
            }

            // Xử lý danh sách cuối cùng nếu còn
            if ($inListSection && !empty($currentListItems)) {

                $html .= $this->createListBlock($currentListItems, $currentListId);
                $inListSection = false;
            }

            if ($infoFirstTable['category']) {

                $html .= $this->createRelatedLinksBlock($infoFirstTable['category']);
            }



            $contentPreview = do_blocks($html);
            $contentPreview = apply_filters('the_content', $contentPreview);
            $contentPreview = $this->addFixedToc($contentPreview);

            $html = $this->createImageThumbnail() . $html;
            $contentPreview = $this->createImageThumbnail()  . $contentPreview;

            $contentPreview = $this->inlineGoogleImages($contentPreview);

            return [
                'infoFirstTable' => $infoFirstTable,
                'contentBlocksWP' => $html,
                'contentPreview' => $contentPreview
            ];
        } catch (\Throwable $e) {
            error_log('Error converting to HTML: ' . $e->getMessage());
            return [
                'infoFirstTable' => [],
                'contentBlocksWP' => '',
                'contentPreview' => ''
            ];
        }
    }


    // tạo hàm tự động thêm ftwp-heading vào các tiêu đề h2, h3, h4, h5, h6
    private function addFixedToc($content)
    {
        try {
            libxml_use_internal_errors(true); // Tránh warning nếu HTML không chuẩn

            $doc = new DOMDocument();
            $doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

            foreach (range(1, 6) as $i) {
                $headings = $doc->getElementsByTagName("h{$i}");
                foreach ($headings as $heading) {
                    $existingClass = $heading->getAttribute('class');
                    if (strpos($existingClass, 'ftwp-heading') === false) {
                        $newClass = trim($existingClass . ' ftwp-heading');
                        $heading->setAttribute('class', $newClass);
                    }
                }
            }

            // Loại bỏ thẻ <html><body> được thêm tự động
            $body = $doc->getElementsByTagName('body')->item(0);
            $innerHTML = '';
            foreach ($body->childNodes as $child) {
                $innerHTML .= $doc->saveHTML($child);
            }

            return $innerHTML;
        } catch (\Throwable $e) {
            error_log('Error adding fixed toc: ' . $e->getMessage());
            return $content;
        }
    }


    /**
     * Xác định loại nội dung
     * 
     * @param array $content Nội dung từ Google Docs
     * @return string Loại nội dung (table, paragraph, list)
     */
    private function determineContentType($content)
    {
        try {
            if (isset($content['table'])) {
                return 'table';
            } elseif (isset($content['paragraph'])) {
                return 'paragraph';
            } elseif (isset($content['list'])) {
                return 'list';
            }
            return '';
        } catch (\Throwable $e) {
            error_log('Error determining content type: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Tạo block list từ danh sách các mục
     * 
     * @param array $listItems Danh sách các mục
     * @return string HTML của block list
     */
    private function createListBlock($listItems, $listId = '')
    {
        try {
            if (empty($listItems)) {
                return '';
            }

            $tag = 'ul';
            $ordered = false;
            $typeList = $this->objectLists[$listId]['listProperties']['nestingLevels'][0]['glyphType'] ?? '';

            if ($typeList === 'DECIMAL') {
                $tag = 'ol';
                $ordered = true;
            }

            // JSON block attributes
            $json = json_encode([
                'ordered' => $ordered,
                'values' => $listItems
            ]);

            // Tạo nội dung HTML sử dụng wp:list-item
            $html = "<$tag class=\"wp-block-list\">";
            foreach ($listItems as $item) {
                $html .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->";
            }
            $html .= "</$tag>";

            // Kết hợp thành block WordPress
            return "<!-- wp:list {$json} -->\n{$html}\n<!-- /wp:list -->";
        } catch (\Throwable $e) {
            error_log('Error creating list block: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Tạo bài viết từ nội dung
     */
    function createPostFromContent()
    {
        $this->authorize_request();

        try {

            $content = isset($_POST['content']) ? $_POST['content'] : '';
            if (empty($content)) {
                wp_send_json_error('Không có nội dung để tạo bài viết');
                return;
            }

            $thumbnailId = isset($_POST['thumbnailId']) ? $_POST['thumbnailId'] : '';
            $thumbnailDes = isset($_POST['thumbnailDes']) ? $_POST['thumbnailDes'] : '';
            $thumbnailAlt = isset($_POST['thumbnailAlt']) ? $_POST['thumbnailAlt'] : '';
            $metaTitle = isset($_POST['metaTitle']) ? $_POST['metaTitle'] : '';
            $metaDescription = isset($_POST['metaDescription']) ? $_POST['metaDescription'] : '';
            $slug = isset($_POST['slug']) ? $_POST['slug'] : '';
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
            $category = isset($_POST['category']) ? $_POST['category'] : '';
            $content = str_replace("\n", '', $content);


            // category hiện đang là name category
            // lấy ra id của category
            $category_obj = get_category_by_slug($category);

            if ($category_obj) {
                $category_id = $category_obj->term_id;
            } else {
                // Nếu chưa có, tạo mới
                $category_id = wp_create_category($category);
            }

            // Tạo bài viết
            $post = array(
                'post_title' => $metaTitle,
                'post_content' => $content,
                'post_status' => 'draft',
                'post_type' => 'post',
                'post_date'     => '',
                'post_date_gmt' => '',
                'post_author' => get_current_user_id() ?: 1,
                'post_modified' => current_time('mysql'),
                'post_excerpt' => $metaDescription,
                'post_name' => $slug,
                'post_category' => [$category_id],
            );


            $post_id = wp_insert_post($post);



            // Thêm thumbnail
            if (!empty($thumbnailId)) {
                set_post_thumbnail($post_id, $thumbnailId);
                // Cập nhật ALT text (lưu trong post meta `_wp_attachment_image_alt`)
                update_post_meta($thumbnailId, '_wp_attachment_image_alt', $thumbnailAlt);

                // Cập nhật Description (nội dung chính của attachment post)
                wp_update_post([
                    'ID' => $thumbnailId,
                    'post_content' => $thumbnailDes,
                ]);
            }

            update_post_meta($post_id, 'rank_math_description', $metaDescription);
            update_post_meta($post_id, 'rank_math_title', $metaTitle);
            update_post_meta($post_id, 'rank_math_focus_keyword', $keyword);
            if (is_wp_error($post_id)) {
                error_log($post_id->get_error_message());
                wp_send_json_error('Không thể tạo bài viết');
            } else {
                wp_send_json_success(['post_id' => $post_id]);
            }
        } catch (\Throwable $e) {
            error_log('Error creating post: ' . $e->getMessage());
            wp_send_json_error('Có lỗi khi tạo bài viết');
        }
    }

    /**
     * Import tài liệu từ Google Docs
     */
    function vnx_import_docs()
    {
        $this->authorize_request();

        try {
            if ($this->install_composer_message) {
                wp_send_json_error($this->install_composer_message);
                return;
            }

            $document_id = isset($_POST['documentId']) ? sanitize_text_field($_POST['documentId']) : '';
            if (empty($document_id)) {
                wp_send_json_error('Document ID không hợp lệ');
                return;
            }

            $result = $this->gg_auth->getDocs($document_id);
            if (is_wp_error($result)) {
                error_log($result->get_error_message());
                wp_send_json_error('Không lấy được tài liệu');
                return;
            }

            $html = $this->convertToHtml($result);

            wp_send_json_success($html);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            wp_send_json_error('Có lỗi khi chuyển đổi tài liệu');
        }
    }

    // tạo hàm lấy danh sách link dos trong ggdrive theo id folder
    function getListDocsInFolder()
    {
        $this->authorize_request();

        try {

            if ($this->install_composer_message) {
                wp_send_json_error($this->install_composer_message);
                return;
            }

            $folderId = isset($_POST['folderId']) ? sanitize_text_field($_POST['folderId']) : '';
            if (empty($folderId)) {
                wp_send_json_error('ID Google Drive không hợp lệ');
                return;
            }

            $result = $this->gg_auth->getListDocsInFolder($folderId);
            wp_send_json_success($result);
        } catch (\Throwable $e) {

            error_log($e->getMessage());
            wp_send_json_error('Có lỗi khi lấy danh sách tài liệu trong folder');
        }
    }



    /**
     * Tải ảnh từ URL và tải lên thư viện ảnh của WordPress
     */
    function asyncImageToWordpressMediaLibrary()
    {
        $this->authorize_request('upload_files');

        try {
            $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
            $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';

            if (empty($url)) {
                wp_send_json_error('URL không hợp lệ');
                return;
            }

            $res = $this->image_uploader->upload_from_url($url, $name, $name, '');

            wp_send_json_success($res);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            wp_send_json_error('Có lỗi khi tải ảnh lên thư viện media');
        }
    }
}
new VNX_ImportDocs_Center();
