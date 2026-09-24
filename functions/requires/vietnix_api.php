<?php

use HelperCenter\VnxApiKeyCrypt;

class VietnixAPI_Center
{
    public function __construct()
    {
        add_action('admin_post_vnx_api_submit_Center', array($this, 'vnx_api_submit'));
    }

    // ─── Public static proxy — cho view và register-api dùng mà không cần use ──

    /**
     * Số ký tự key đang lưu (dùng render mask trong view).
     */
    public static function get_api_key_count(): int
    {
        return VnxApiKeyCrypt::stored_length();
    }

    /**
     * Giải mã và trả về API key gốc (dùng trong authenticate).
     */
    public static function get_decrypted_api_key(): ?string
    {
        return VnxApiKeyCrypt::decrypt();
    }

    // ─── Admin form handler ───────────────────────────────────────────────────

    public function vnx_api_submit(): void
    {
        try {
            $referrer = !empty($_POST['_wp_http_referer'])
                ? site_url($_POST['_wp_http_referer'])
                : wp_get_referer();

            if (!isset($_POST['vnx-api-nonce']) || !wp_verify_nonce($_POST['vnx-api-nonce'], 'vnx_api_security')) {
                vnx_tool_notice_set_Center('Lỗi bảo mật! Vui lòng thử lại.', 'error', 'vietnix-api');
                wp_safe_redirect(esc_url_raw($referrer));
                return;
            }

            if (isset($_POST['vnx_api_submit']) && $_POST['vnx_api_submit'] === 'save') {

                // Chỉ admin mới được thực hiện thay đổi
                if (!current_user_can('manage_options')) {
                    wp_die('Unauthorized', '', ['response' => 403]);
                }

                // Lưu danh sách IP được phép
                $api_allow_ip = isset($_POST['allow_api']) ? $_POST['allow_api'] : '';
                $api_allow_ip = $this->br_bookmarks_tagify_json_to_array_Center($api_allow_ip);
                update_option('vnx_api_allow_ip', implode(',', $api_allow_ip));

                // Xử lý API Key — chỉ update khi user nhập key mới (không rỗng)
                $raw_api_key = isset($_POST['api_key']) ? $_POST['api_key'] : '';
                $key_saved = true;
                if (!empty($raw_api_key)) {
                    if (!current_user_can('manage_options')) {
                        wp_die('Unauthorized', '', ['response' => 403]);
                    }
                    $new_key = sanitize_text_field($raw_api_key);
                    $key_saved = VnxApiKeyCrypt::save($new_key); // false nếu key ngắn hơn MIN_KEY_LEN
                }

                if ($key_saved) {
                    vnx_tool_notice_set_Center('Đã lưu cài đặt.', 'success', 'vietnix-api');
                } else {
                    vnx_tool_notice_set_Center('Đã lưu Allow IP, nhưng API Key quá ngắn (tối thiểu 32 ký tự) nên không được lưu.', 'error', 'vietnix-api');
                }
            }

            wp_safe_redirect(esc_url_raw($referrer));
        } catch (\Throwable $e) {
            vnx_tool_notice_set_Center('Có lỗi xảy ra, vui lòng thử lại.', 'error', 'vietnix-api');
            $push_log = new Vnx_Push_Logger_Center();
            $push_log->vnxPushLogger($e->getMessage(), ['File' => __FILE__, 'Line' => $e->getLine()]);
            wp_safe_redirect(esc_url_raw($referrer));
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function br_bookmarks_tagify_json_to_array_Center($value): array
    {
        if (empty($value)) {
            return [];
        }

        $value       = str_replace(['[', ']'], '', $value);
        $value       = str_replace('\"', '"', $value);
        $value       = explode(',', $value);
        $value_array = [];

        if (is_array($value) && count($value) > 0) {
            foreach ($value as $value_inner) {
                $value_array[] = json_decode($value_inner);
            }

            $value_array = json_decode(json_encode($value_array), true);

            $output = [];
            foreach ($value_array as $value_array_inner) {
                foreach ($value_array_inner as $key => $val) {
                    $output[] = $val;
                }
            }

            return $output;
        }

        return $value_array;
    }
}

new VietnixAPI_Center();