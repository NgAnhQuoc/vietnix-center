<?php

namespace VNXCenter\Widgets\Bricks;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
use HelperCenter\View;

if (class_exists('VNXCenter\Widgets\Bricks\VNX_Search_Whois_Domain'))
    return;

class VNX_Search_Whois_Domain extends \Bricks\Element
{
    public $category = 'vietnix';
    public $name = 'vnx-search-whois-domain';
    public $icon = 'fa-solid fa-hand-holding-dollar';

    public function __construct($data = [])
    {
        parent::__construct($data);
        add_action('wp_ajax_nopriv_vnx_search_domain_center', [$this, 'ajax_search_domain']);
        add_action('wp_ajax_vnx_search_domain_center', [$this, 'ajax_search_domain']);
        add_action('wp_ajax_nopriv_vnx_check_available_domain_center', [$this, 'ajax_check_available_domain']);
        add_action('wp_ajax_vnx_check_available_domain_center', [$this, 'ajax_check_available_domain']);
    }

    public function get_label()
    {
        return esc_html__('VNX Search Whois Domain', 'vietnix');
    }

    public function enqueue_scripts()
    {
        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        // Enqueue helper functions first
        wp_register_script('vnx-search-whois-helpers-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx-search-whois-domain/helpers.js', ['jquery'], '1.2', true);
        wp_enqueue_script('vnx-search-whois-helpers-center');

        // Enqueue main search domain script
        wp_register_script('vnx-search-whois-domain-main-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx-search-whois-domain/form-search.js', ['jquery', 'vuejs-library-center', 'vnx-search-whois-helpers-center'], '1.1', true);
        wp_enqueue_script('vnx-search-whois-domain-main-center');

        // Enqueue cycle domain script
        wp_register_script('vnx-search-whois-domain-cycle-component-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx-search-whois-domain/cycle-component.js', ['jquery', 'vuejs-library-center', 'vnx-search-whois-domain-main-center'], '1.0', true);
        wp_enqueue_script('vnx-search-whois-domain-cycle-component-center');

        // Enqueue show result script
        wp_register_script('vnx-search-whois-domain-show-result-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx-search-whois-domain/show-result.js.js', ['jquery', 'vuejs-library-center', 'vnx-search-whois-domain-main-center'], filemtime(VNX_PLUGIN_PATH_CENTER . 'widgets/inc/bricks/js/vnx-search-whois-domain/show-result.js'), true);
        wp_enqueue_script('vnx-search-whois-domain-show-result-center');

        // Enqueue suggest slide script
        wp_register_script('vnx-search-whois-domain-suggest-slide-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx-search-whois-domain/suggest-slide.js', ['jquery', 'vuejs-library-center', 'vnx-search-whois-domain-main-center'], '1.0', true);
        wp_enqueue_script('vnx-search-whois-domain-suggest-slide-center');

        wp_enqueue_style('vnx-bricks-tailwind-base-center', VNX_PLUGIN_URL_CENTER . 'build/css/brickstailwindBase.css', ['bricks-frontend'], false, 'all');

        // Localize script after enqueuing
        wp_localize_script('vnx-search-whois-domain-main-center', 'vnx_search_whois_domain', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vnx_search_whois_domain_nonce')
        ]);
    }

    public function set_control_groups()
    {
    }

    public function set_controls()
    {
        // tạo 1 control mới cho cho chọn section
        $this->controls['section'] = [
            'label' => esc_html__('Section', 'vietnix'),
            'type' => 'select',
            'default' => 'form-search',
            'options' => [
                'form-search' => esc_html__('Form search', 'vietnix'),
                'cycle-component' => esc_html__('Cycle component', 'vietnix'),
                'show-result' => esc_html__('Whois result VN', 'vietnix'),
                'suggest-slide' => esc_html__('Suggest slide', 'vietnix'),
            ],
        ];

        $this->controls['is-redirect'] = [
            'tab' => 'content',
            'label' => esc_html__('Is Redirect', 'vietnix'),
            'description' => 'Nếu bật, thì sẽ chuyển hướng sang trang kết quả',
            'type' => 'checkbox',
            'required' => ['section', '=', ['form-search']]
        ];

        $this->controls['link-redirect'] = [
            'tab' => 'content',
            'label' => esc_html__('Link Redirect', 'vietnix'),
            'description' => 'Nếu bật, thì sẽ chuyển hướng sang trang kết quả',
            'type' => 'text',
            'required' => ['is-redirect', '=', true]
        ];

        $this->controls['import-csv'] = [
            'tab' => 'content',
            'label' => esc_html__('Data File', 'vietnix'),
            'description' => 'Please select the file format is .csv',
            'type' => 'file',
            'required' => ['section', '=', ['show-result', 'suggest-slide']]
        ];

        $this->controls['import-csv-domain-0d'] = [
            'tab' => 'content',
            'label' => esc_html__('Data Domain 0đ', 'vietnix'),
            'description' => 'Please select the file format is .csv',
            'type' => 'file',
            'required' => ['section', '=', ['show-result']]
        ];

        $this->controls['import-csv-domain-combo'] = [
            'tab' => 'content',
            'label' => esc_html__('Data Domain combo', 'vietnix'),
            'description' => 'Please select the file format is .csv',
            'type' => 'file',
            'required' => ['section', '=', ['show-result']]
        ];

        $this->controls['unit'] = [
            'tab' => 'content',
            'label' => esc_html__('Unit', 'vietnix'),
            'type' => 'text',
            'required' => ['section', '=', ['show-result']]
        ];

        $this->controls['extra-services'] = [
            'tab' => 'content',
            'label' => esc_html__('Extra Services', 'vietnix'),
            'type' => 'repeater',
            'required' => ['section', '=', 'show-result'],
            'titleProperty' => 'name',
            'default' => [
                [
                    'pid' => 33,
                    'title' => 'Combo Tiêu Chuẩn',
                    'name' => 'WordPress Hosting 4',
                    'descriptions' => '4 CPU, 6GB RAM, 20GB NVMe, 4 tên miền',
                    'price' => 2700000,
                    'salePrice' => 2041000,
                    'url' => '',
                ],
            ],
            'placeholder' => esc_html__('Configuration', 'vietnix'),
            'fields' => [
                'pid' => [
                    'label' => esc_html__('Product ID', 'vietnix'),
                    'type' => 'number',
                ],
                'title' => [
                    'label' => esc_html__('Title', 'vietnix'),
                    'type' => 'text',
                ],
                'name' => [
                    'label' => esc_html__('Name', 'vietnix'),
                    'type' => 'text',
                ],
                'descriptions' => [
                    'label' => esc_html__('Descriptions', 'vietnix'),
                    'type' => 'textarea',
                    'default' => '4 CPU, 6GB RAM, 20GB NVMe, 4 tên miền',
                    'description' => 'Thông tin được phân cách nhau bằng dấu phẩy',
                ],
                'price' => [
                    'label' => esc_html__('Price', 'vietnix'),
                    'type' => 'number',
                ],
                'salePrice' => [
                    'label' => esc_html__('Sale Price', 'vietnix'),
                    'type' => 'number',
                ],
                'url' => [
                    'label' => esc_html__('URL order', 'vietnix'),
                    'type' => 'text',
                ],

                'background' => [
                    'label' => esc_html__('Background Image', 'vietnix'),
                    'type' => 'image',
                ],
                'imageLabel' => [
                    'label' => esc_html__('Image Label', 'vietnix'),
                    'type' => 'image',
                ],
                'imageSpeed' => [
                    'label' => esc_html__('Image Speed', 'vietnix'),
                    'type' => 'image',
                ],
                'tooltip' => [
                    'label' => esc_html__('Tooltip speed', 'vietnix'),
                    'type' => 'textarea',
                ],
                'imageSpecial' => [
                    'label' => esc_html__('Image Special', 'vietnix'),
                    'type' => 'image',
                ],
                'borderCard' => [
                    'label' => esc_html__('Border Card', 'vietnix'),
                    'type' => 'color',
                    'inline' => true,
                    'default' => '#007CFC',
                ],
            ],
        ];

        $this->controls['domain-combos'] = [
            'tab' => 'content',
            'label' => esc_html__('Domain combo', 'vietnix'),
            'type' => 'repeater',
            'required' => ['section', '=', 'show-result'],
            'titleProperty' => 'name',
            'default' => [
                [
                    'pid' => 33,
                    'title' => 'Combo Tiêu Chuẩn',
                    'name' => 'WordPress Hosting 4',
                    'descriptions' => '4 CPU, 6GB RAM, 20GB NVMe, 4 tên miền',
                    'price' => 2700000,
                    'salePrice' => 2041000,
                    'url' => '',
                ],
            ],
            'placeholder' => esc_html__('Configuration', 'vietnix'),
            'fields' => [
                'pid' => [
                    'label' => esc_html__('Product ID', 'vietnix'),
                    'type' => 'number',
                ],
                'title' => [
                    'label' => esc_html__('Title', 'vietnix'),
                    'type' => 'text',
                ],
                'name' => [
                    'label' => esc_html__('Name', 'vietnix'),
                    'type' => 'text',
                ],
                'descriptions' => [
                    'label' => esc_html__('Descriptions', 'vietnix'),
                    'type' => 'textarea',
                    'default' => '4 CPU, 6GB RAM, 20GB NVMe, 4 tên miền',
                    'description' => 'Thông tin được phân cách nhau bằng dấu phẩy',
                ],
                'url' => [
                    'label' => esc_html__('URL order', 'vietnix'),
                    'type' => 'text',
                ],

                'background' => [
                    'label' => esc_html__('Background Image', 'vietnix'),
                    'type' => 'image',
                ],
                'imageLabel' => [
                    'label' => esc_html__('Image Label', 'vietnix'),
                    'type' => 'image',
                ],
                'imageSpeed' => [
                    'label' => esc_html__('Image Speed', 'vietnix'),
                    'type' => 'image',
                ],
                'tooltip' => [
                    'label' => esc_html__('Tooltip speed', 'vietnix'),
                    'type' => 'textarea',
                ],
                'imageSpecial' => [
                    'label' => esc_html__('Image Special', 'vietnix'),
                    'type' => 'image',
                ],
                'borderCard' => [
                    'label' => esc_html__('Border Card', 'vietnix'),
                    'type' => 'color',
                    'inline' => true,
                    'default' => '#007CFC',
                ],
            ],
        ];
    }

    public function render()
    {
        $settings = $this->settings;
        $section = isset($settings['section']) ? $settings['section'] : '';

        if (empty($section))
            echo "<div>Please select section</div>";

        View::render("widgets/bricks/vnx-search-whois-domain/" . $section, $this);
    }


    //-------------------------------------- Xử lý LOGIC --------------------------------------//
    // Ajax handler for domain search
    public function ajax_search_domain()
    {
        // Verify nonce for security
        // if (!wp_verify_nonce($_POST['nonce'], 'vnx_search_whois_domain_nonce')) {
        //     wp_send_json_error(['message' => 'Kiểm tra bảo mật thất bại']);
        // }

        $domain = isset($_POST['domain']) ? sanitize_text_field($_POST['domain']) : '';

        if (empty($domain)) {
            wp_send_json_error(['message' => 'Tên miền là bắt buộc']);
        }

        /*
         * ĐÃ TẮT (2026-08-20): trước đó tên miền .vn được tra whois trực tiếp
         * qua api.inet.vn (VNNIC) thay vì whois.vietnix.vn. Theo yêu cầu, mọi
         * tên miền (kể cả .vn) quay lại dùng whois.vietnix.vn như cũ. Giữ lại
         * đoạn code này (không xoá) để dễ bật lại nếu cần, cùng với hàm
         * normalizeVnData() (cũng đã comment lại) bên dưới.
         *
         * // Tên miền .vn dùng API whois trực tiếp của api.inet.vn (VNNIC),
         * // tên miền quốc tế vẫn lấy như cũ qua whois.vietnix.vn
         * if (preg_match('/\.vn$/i', $domain)) {
         *     $response = wp_remote_post('https://api.inet.vn/api/public/whois/v1/whois/directly', [
         *         'timeout' => 60,
         *         'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
         *         'body' => ['domainName' => $domain],
         *     ]);
         *
         *     if (is_wp_error($response)) {
         *         wp_send_json_error(['message' => 'Không thể lấy thông tin tên miền']);
         *     }
         *
         *     $body = wp_remote_retrieve_body($response);
         *     $data = json_decode($body, true);
         *
         *     if (json_last_error() !== JSON_ERROR_NONE) {
         *         wp_send_json_error(['message' => 'Không thể lấy thông tin tên miền']);
         *     }
         *
         *     wp_send_json_success($this->normalizeVnData($data, $domain));
         * }
         */

        $response = wp_remote_get('https://whois.vietnix.vn/whois?domain=' . $domain . '&responsetype=json', ['timeout' => 60]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Không thể lấy thông tin tên miền']);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // API whois trả về text thay vì JSON (VD: tên miền .gov.vn được
            // nhà nước/VNNIC bảo mật, hoặc API tạm thời lỗi lookup). Domain đã
            // qua validate định dạng ở client nên không phải "không hợp lệ",
            // chỉ là server không lấy được dữ liệu whois cho domain này.
            $firstDotPosition = strpos($domain, '.');
            wp_send_json_success([
                'domain' => $domain,
                'sld' => $firstDotPosition !== false ? substr($domain, 0, $firstDotPosition) : '',
                'tld' => $firstDotPosition !== false ? substr($domain, $firstDotPosition + 1) : $domain,
                'domainStatus' => 'reserved',
            ]);
        }

        // Kiểm tra nếu API trả về data rỗng (TLD không tồn tại hoặc lookup fail)
        $hasData = false;
        foreach ($data['data'] as $item) {
            if (!empty(trim($item['value']))) {
                $hasData = true;
                break;
            }
        }

        $data = $this->normalizeData($data['data'], $domain);

        if (!$hasData) {
            $data['domainStatus'] = 'undefined';
        }

        wp_send_json_success($data);
    }


    // Hàm chuẩn hóa dữ liệu WHOIS
    private function normalizeData($data, $domain)
    {
        $aliases = [
            'domainName' => ['domainName', 'domain name', 'Domain Name'],
            'creationDate' => ['creationDate', 'creation date', 'Created', 'registered'],
            'expirationDate' => ['registrarExpirationDate', 'expiration date', 'expires'],
            'nameServers' => ['nameServers', 'name servers', 'nameserver'],
            'domainStatus' => ['Domain Status', 'domainStatus', 'status'],
        ];

        $result = [];

        foreach ($data as $item) {
            $label = strtolower(trim($item['label']));
            $value = $item['value'];
            $foundKey = null;

            // Tìm alias phù hợp
            foreach ($aliases as $key => $aliasList) {
                foreach ($aliasList as $alias) {
                    if (strtolower($alias) === $label) {
                        $foundKey = $key;
                        break 2; // Thoát cả 2 vòng lặp
                    }
                }
            }

            if ($foundKey) {
                $result[$foundKey] = $value;
            } else {
                $result[$item['label']] = $value; // fallback nếu không match alias nào
            }
        }

        $result['domain'] = $domain;
        $result = array_merge($result, $this->splitDomainParts($domain));

        return $result;
    }

    /*
     * ĐÃ TẮT (2026-08-20): hàm này chuẩn hóa dữ liệu WHOIS cho domain .vn lấy
     * từ api.inet.vn. Không còn được gọi vì đoạn rẽ nhánh sang .vn trong
     * ajax_search_domain() ở trên đã bị comment lại. Giữ nguyên nội dung để
     * tiện bật lại nếu sau này cần dùng lại api.inet.vn.
     *
     * private function normalizeVnData($data, $domain)
     * {
     *     $parts = $this->splitDomainParts($domain);
     *
     *     // code = "0" nghĩa là domain đã đăng ký và có dữ liệu whois
     *     $isRegistered = isset($data['code']) && $data['code'] === '0';
     *
     *     if (!$isRegistered) {
     *         return array_merge($parts, [
     *             'domain' => $domain,
     *             'domainStatus' => 'undefined',
     *         ]);
     *     }
     *
     *     $nameServers = !empty($data['nameServer']) && is_array($data['nameServer']) ? implode(', ', $data['nameServer']) : '';
     *     $status = !empty($data['status']) && is_array($data['status']) ? implode(', ', $data['status']) : '';
     *
     *     return array_merge($parts, [
     *         'domain' => $domain,
     *         'domainName' => $data['registrantName'] ?? '',
     *         'registrarName' => $data['registrar'] ?? '',
     *         // "Registrar" (viết hoa) là key mà cycle-component.php đang dùng để hiện
     *         // dòng "đăng ký bởi Nhà đăng ký ..." cho cả 2 nhánh vn/qt
     *         'Registrar' => $data['registrar'] ?? '',
     *         'creationDate' => $data['creationDate'] ?? '',
     *         'expirationDate' => $data['expirationDate'] ?? '',
     *         'nameServers' => $nameServers,
     *         'domainStatus' => $status !== '' ? $status : 'undefined',
     *         'dnssec' => $data['DNSSEC'] ?? '',
     *     ]);
     * }
     */

    // Tách SLD/TLD và xác định domain_type ('vn' hoặc 'qt') từ domain name
    private function splitDomainParts($domain)
    {
        $firstDotPosition = strpos($domain, '.');
        $sld = $firstDotPosition !== false ? substr($domain, 0, $firstDotPosition) : '';
        $tld = $firstDotPosition !== false ? substr($domain, $firstDotPosition + 1) : $domain;

        return [
            'sld' => $sld,
            'tld' => $tld,
            'domain_type' => preg_match('/\.vn$/i', $domain) ? 'vn' : 'qt',
        ];
    }

    // Ajax handler for checking available domain
    public function ajax_check_available_domain()
    {
        // Verify nonce for security
        // if (!wp_verify_nonce($_POST['nonce'], 'vnx_search_whois_domain_nonce')) {
        //     wp_send_json_error(['message' => 'Kiểm tra bảo mật thất bại']);
        // }

        $sld = isset($_POST['sld']) ? sanitize_text_field($_POST['sld']) : '';
        $tld = isset($_POST['tld']) ? sanitize_text_field($_POST['tld']) : '';

        if (empty($sld)) {
            wp_send_json_error(['message' => 'SLD là bắt buộc']);
        }

        if (empty($tld)) {
            wp_send_json_error(['message' => 'TLD là bắt buộc']);
        }

        // Build API URL với nhiều TLD
        $api_url = 'https://whois.vietnix.vn/available?sld=' . urlencode($sld) . '&tld=' . urlencode($tld) . '&withprice=1';

        $response = wp_remote_get($api_url, ['timeout' => 60]);



        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Không thể lấy thông tin domain available']);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(['message' => 'Phản hồi JSON không hợp lệ']);
        }

        wp_send_json_success($data);
    }


    public function dataCSV($fileName)
    {

        $file_url = $this->settings[$fileName]['url'] ?? '';
        if (empty($file_url) || !is_string($file_url)) {
            return [];
        }

        // Convert URL to server file path if needed
        if (preg_match('/wp-content\/(.*)/', $file_url, $matches)) {
            $file_path = ABSPATH . 'wp-content/' . $matches[1];
        } else {
            $file_path = $file_url;
        }

        if (!file_exists($file_path)) {
            return [];
        }

        $result = [];
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return [];
        }

        // Đọc 1 dòng để xác định delimiter
        $first_line = fgets($handle);
        rewind($handle);
        $delimiter = strpos($first_line, ';') !== false ? ';' : ',';

        // Bỏ qua header
        fgetcsv($handle, 0, $delimiter);

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($data) >= 2) {
                $result[] = [
                    'tld' => trim($data[0]),
                    'pricing' => floatval(trim($data[1])),
                ];
            }
        }

        fclose($handle);
        return $result;
    }

    public function getFileCSV($fileName, $skipHeaderLines = 0)
    {
        $file_url = $this->settings[$fileName]['url'] ?? '';
        if (empty($file_url) || !is_string($file_url)) {
            return [];
        }

        // Chuyển URL thành đường dẫn thực tế trong WordPress
        if (preg_match('/wp-content\/(.*)/', $file_url, $matches)) {
            $file_path = ABSPATH . 'wp-content/' . $matches[1];
        } else {
            $file_path = $file_url;
        }

        if (!file_exists($file_path)) {
            return [];
        }

        $result = [];
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return [];
        }

        // Xác định delimiter (; hoặc ,)
        $first_line = fgets($handle);
        rewind($handle);
        $delimiter = strpos($first_line, ';') !== false ? ';' : ',';

        // Bỏ qua số dòng header theo tham số truyền vào
        for ($i = 0; $i < $skipHeaderLines; $i++) {
            fgetcsv($handle, 0, $delimiter);
        }

        // Đọc dữ liệu còn lại thành mảng 2 chiều
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count(array_filter($data)) === 0) {
                continue; // bỏ qua dòng trống
            }
            $result[] = $data;
        }

        fclose($handle);
        return $result;
    }
}
