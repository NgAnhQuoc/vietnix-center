<?php
require_once VNX_PLUGIN_PATH_CENTER . '/extentions/helper/SendLogCenter_Center.php';

class Vietnix_Order_Product_Center
{
    private $api_url = "https://portal.vietnix.vn/integration-api.php";
    private $logger;
    private $last_http = []; // http code/redirects/ip cua lan goi portal gan nhat, ghi vao debug.log khi loi

    public function __construct()
    {
        $this->logger = new SendLogCenter_Center();

        // Đăng ký AJAX action
        // Ten cu cua vietnix-plugin (vietnix_order_product, vnx_order_obj_storage) van duoc Code element
        // Bricks tren trang goi truc tiep (vd template 421390 - bang gia Enterprise Cloud). Extension chi
        // nap khi vietnix-plugin tat (vnx_active.php) nen dang ky ca ten cu khong bi trung.
        add_action('wp_ajax_vietnix_order_product', [$this, 'handle_request']);
        add_action('wp_ajax_nopriv_vietnix_order_product', [$this, 'handle_request']);
        add_action('wp_ajax_vietnix_order_product_center', [$this, 'handle_request']);
        add_action('wp_ajax_nopriv_vietnix_order_product_center', [$this, 'handle_request']);

        // AJAX cho V2 (jQuery Ajax)
        add_action('wp_ajax_vnx_order_obj_storage', [$this, 'send_request_object_storage_v2']);
        add_action('wp_ajax_nopriv_vnx_order_obj_storage', [$this, 'send_request_object_storage_v2']);
        add_action('wp_ajax_vnx_order_obj_storage_center', [$this, 'send_request_object_storage_v2']);
        add_action('wp_ajax_nopriv_vnx_order_obj_storage_center', [$this, 'send_request_object_storage_v2']);

        // Đăng ký action để tạo nonce
        // THÊM SỐ 20 Ở ĐÂY để đảm bảo nhận được script handle
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts'], 20);
    }

    /**
     * Enqueue scripts và localize nonce
     */
    public function enqueue_scripts()
    {
        wp_localize_script('jquery', 'vietnix_order_product', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('vietnix_order_product_nonce')
        ]);

        wp_localize_script('jquery', 'vnx_order_obj_storage', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('vnx_order_obj_storage_nonce')
        ]);
    }

    /**
     * Xử lý POST request
     */
    public function handle_request()
    {


        // Nhận dữ liệu từ POST
        $pid          = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
        $options      = isset($_POST['options']) ? (array) $_POST['options'] : [];
        $billingcycle = isset($_POST['billingcycle']) ? sanitize_text_field($_POST['billingcycle']) : 'quarterly';

        // Gọi API
        $response = $this->call_api($pid, $billingcycle, $options, 'v1');

        if (!$response) {
            // Trở về trang trước thay vì báo lỗi
            wp_redirect(wp_get_referer() ?: home_url());
            exit;
        }

        $data = json_decode($response, true);

        if (isset($data['status']) && $data['status'] === 'success') {
            // Chuyển hướng trực tiếp
            wp_redirect($data['redirectTo']);
            exit;
        } else {
            error_log('[vietnix-center] order product: portal khong tra success - http=' . wp_json_encode($this->last_http) . ' body=' . substr((string) $response, 0, 500));
            $this->logger->send(
                'Vietnix order product: API trả về lỗi (v1)',
                'error',
                [
                    'pid'          => $pid,
                    'billingcycle' => $billingcycle,
                    'options'      => $options,
                    'response'     => $data ?? $response,
                ]
            );

            // Trở về trang trước thay vì báo lỗi
            wp_redirect(wp_get_referer() ?: home_url());
            exit;
        }
    }

    /**
     * Hàm gọi API Vietnix
     */
    private function call_api($pid, $billingcycle, $options = [], $version = 'v1')
    {
        $postFields = [
            'action'       => 'config_options_product',
            'pid'          => $pid,
            'billingcycle' => $billingcycle,
            'source_ver'   => $version, // Phân biệt nguồn gọi từ V1 hay V2
        ];

        foreach ($options as $key => $value) {
            $postFields["options[$key]"] = $value;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL        => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => http_build_query($postFields),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
            ],
        ]);

        $response = curl_exec($ch);
        $this->last_http = [
            'code'      => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'redirects' => curl_getinfo($ch, CURLINFO_REDIRECT_COUNT),
            'ip'        => curl_getinfo($ch, CURLINFO_PRIMARY_IP),
        ];

        if (curl_errno($ch)) {
            error_log('[vietnix-center] order product: curl loi - ' . curl_error($ch) . ' http=' . wp_json_encode($this->last_http));
            $this->logger->send(
                'Vietnix order product: không kết nối được tới portal API',
                'error',
                [
                    'pid'        => $pid,
                    'version'    => $version,
                    'curl_error' => curl_error($ch),
                ]
            );
            curl_close($ch);
            return false; // Trả về false để handle_request xử lý redirect
        }

        curl_close($ch);
        return $response;
    }

    /**
     * Xử lý request Object Storage V2 (AJAX)
     */
    public function send_request_object_storage_v2()
    {
        // 2. Lấy dữ liệu từ Frontend
        $pid            = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
        $storage_gb     = isset($_POST['options']['user_quota']) ? floatval($_POST['options']['user_quota']) : 0;
        $dt_total_gb    = isset($_POST['options']['data_transfer']) ? floatval($_POST['options']['data_transfer']) : 0;
        $req_tr         = isset($_POST['options']['api_req']) ? floatval($_POST['options']['api_req']) : 0;
        $billingcycle   = isset($_POST['billingcycle']) ? sanitize_text_field($_POST['billingcycle']) : 'monthly';
        // 3. Mapping sang options API
        // Chuyển đổi req_tr (triệu req) sang số lượng thực tế (nhân 1.000.000)
        $options = [
            'user_quota'    => $storage_gb,
            'data_transfer' => $dt_total_gb,
            'api_req'       => $req_tr * 1000000,
        ];
        // 4. Gọi API với tham số 'v2'
        $response = $this->call_api($pid, $billingcycle, $options, 'v2');
        if (!$response) {
            wp_send_json_error(['message' => 'API Connection Error']);
        }
        $data = json_decode($response, true);
        if (isset($data['status']) && $data['status'] === 'success') {
            // Trả về kết quả thành công kèm link redirect
            wp_send_json_success(['redirect' => $data['redirectTo']]);
        } else {
            $this->logger->send(
                'Vietnix order object storage V2: API trả về lỗi',
                'error',
                [
                    'pid'          => $pid,
                    'billingcycle' => $billingcycle,
                    'options'      => $options,
                    'response'     => $data ?? $response,
                ]
            );

            wp_send_json_error(['message' => $data['message'] ?? 'Unknown Error']);
        }
    }
}

// Khởi tạo class
new Vietnix_Order_Product_Center();
