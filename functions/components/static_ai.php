<?php

class vnx_StaticAi_Center
{

    public function send_gemini_request($data, $url, $headers = [])
    {
        $args = [
            'headers' => array_merge([
                'Content-Type' => 'application/json',
            ], $headers),
            'body' => json_encode($data),
            'timeout' => 30,
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("Gemini API lỗi: $error_message");
            return [
                'success' => false,
                'message' => $error_message,
            ];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code !== 200) {
            return [
                'success' => false,
                'message' => "HTTP $code: " . $body
            ];
        }

        $decoded = json_decode($body, true);

        if (!isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'success' => false,
                'message' => "Cấu trúc phản hồi không khớp: " . $body
            ];
        }

        return [
            'success' => true,
            'data' => $decoded['candidates'][0]['content']['parts'][0]['text']
        ];
    }

    public function get_tld_suggest_ai()
    {
        $cache_key = 'vnx_ai_tld_list';
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $url = "https://whois.vietnix.vn/price/list";
        $response = wp_remote_get($url, ['timeout' => 10]);

        if (is_wp_error($response)) {
            // Trả về lỗi nếu có
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Lấy danh sách key (ví dụ: [com, net, ...])
        $tld_keys = isset($data['pricing']) ? array_keys($data['pricing']) : [];

        if (!empty($tld_keys)) {
            set_transient($cache_key, $tld_keys, 12 * HOUR_IN_SECONDS);
        }

        return $tld_keys;
    }
}
