<?php

/**
 * Tool: Phân bổ Internal Link LDP
 *
 * Quét nội dung (post_content) của tất cả bài viết đã publish,
 * tìm các thẻ <a href> trỏ đến danh sách Landing Page (LDP) của Vietnix,
 * sau đó export kết quả lên Google Sheet theo format:
 *   [URL bài viết] | [Link LDP] | [Anchor Text] | [HTML Type]
 */
class VNX_InternalLinkLDP_Center
{
    private const OPTION_NAME = 'vnx_internal_link_ldp_setting';

    private const DEFAULT_SETTINGS = [
        'sheet_url'   => '',
        'sheet_tab'   => '',
        'site_domain' => '',
        'ldp_urls'    => '',
        'date_from'   => '', // YYYY-MM-DD — bỏ trống = không lọc ngày
        'date_to'     => '', // YYYY-MM-DD — bỏ trống = không lọc ngày
    ];


    /** Danh sách LDP mặc định của Vietnix */
    private const DEFAULT_LDP_URLS = [
        'https://vietnix.vn/',
    ];

    public function __construct()
    {
        add_action('admin_enqueue_scripts',           [$this, 'enqueueScripts']);
        add_action('wp_ajax_vnx_illdp_get_settings_center',  [$this, 'ajaxGetSettings']);
        add_action('wp_ajax_vnx_illdp_save_settings_center', [$this, 'ajaxSaveSettings']);
        add_action('wp_ajax_vnx_illdp_scan_chunk_center',    [$this, 'ajaxScanChunk']);
        add_action('wp_ajax_vnx_illdp_get_progress_center',  [$this, 'ajaxGetProgress']);
        add_action('wp_ajax_vnx_illdp_clear_progress_center', [$this, 'ajaxClearProgress']);
    }


    // =========================================================================
    // Admin Scripts
    // =========================================================================

    public function enqueueScripts()
    {
        // Chi nap tren trang cua center: nap o moi trang admin (ke ca trang cua vietnix-plugin) thi Vue/CSS
        // cua center chay tren giao dien cua plugin kia va lam nang admin.
        if (!vnx_center_is_own_admin_page()) {
            return;
        }

        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        wp_enqueue_script(
            'vnx-internal-link-ldp-center',
            VNX_PLUGIN_URL_CENTER . 'tools/inc/js/vnx-internal-link-ldp.js',
            ['jquery', 'vuejs-library-center'],
            'all',
            true
        );

        wp_localize_script('vnx-internal-link-ldp-center', 'vnxILLDPData', [
            'nonce' => wp_create_nonce('vnx_internal_link_ldp_nonce'),
        ]);
    }

    // =========================================================================
    // Settings CRUD
    // =========================================================================

    private function getSetting(): array
    {
        $raw = get_option(self::OPTION_NAME);
        if (!$raw) {
            return self::DEFAULT_SETTINGS;
        }
        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        return is_array($decoded) ? array_merge(self::DEFAULT_SETTINGS, $decoded) : self::DEFAULT_SETTINGS;
    }

    private function updateSetting(array $data): void
    {
        update_option(self::OPTION_NAME, json_encode($data));
    }

    // =========================================================================
    // AJAX Handlers
    // =========================================================================

    public function ajaxGetSettings()
    {
        check_ajax_referer('vnx_internal_link_ldp_nonce', 'nonce');
        $settings = $this->getSetting();

        // Nếu ldp_urls rỗng trả về default list để hiện sẵn trong textarea
        if (empty(trim($settings['ldp_urls']))) {
            $settings['ldp_urls'] = implode("\n", self::DEFAULT_LDP_URLS);
        }

        wp_send_json_success($settings);
    }

    public function ajaxSaveSettings()
    {
        check_ajax_referer('vnx_internal_link_ldp_nonce', 'nonce');

        if (!isset($_POST['settings']) || !is_array($_POST['settings'])) {
            wp_send_json_error('Dữ liệu settings không hợp lệ');
        }

        $raw_from = sanitize_text_field($_POST['settings']['date_from'] ?? '');
        $raw_to   = sanitize_text_field($_POST['settings']['date_to']   ?? '');

        // Validate định dạng YYYY-MM-DD
        $date_from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_from) ? $raw_from : '';
        $date_to   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_to)   ? $raw_to   : '';

        $settings = [
            'sheet_url'   => sanitize_text_field($_POST['settings']['sheet_url'] ?? ''),
            'sheet_tab'   => sanitize_text_field($_POST['settings']['sheet_tab'] ?? ''),
            'site_domain' => sanitize_text_field($_POST['settings']['site_domain'] ?? ''),
            'ldp_urls'    => sanitize_textarea_field($_POST['settings']['ldp_urls'] ?? ''),
            'date_from'   => $date_from,
            'date_to'     => $date_to,
        ];

        $this->updateSetting($settings);
        wp_send_json_success('Cài đặt đã được lưu thành công');
    }

    /**
     * AJAX: Quét 1 chunk (100 posts) và GHI NGAY vào Google Sheet.
     *
     * - Page 1 + fresh start: xóa data cũ (giữ header), khởi tạo tiến trình.
     * - Page 1 + resume: bỏ qua clear, chỉ append.
     * POST params: page (int), fresh (0|1)
     */
    public function ajaxScanChunk()
    {
        check_ajax_referer('vnx_internal_link_ldp_nonce', 'nonce');
        set_time_limit(120);

        $settings  = $this->getSetting();

        $domainCheck = $this->validateSiteDomain($settings);
        if ($domainCheck !== true) {
            wp_send_json_error($domainCheck);
        }

        $ldpUrls   = $this->parseLdpUrls($settings['ldp_urls']);
        $dateFrom  = $settings['date_from'] ?? '';
        $dateTo    = $settings['date_to']   ?? '';
        $page      = max(1, (int)($_POST['page'] ?? 1));
        $isFresh   = (bool)($_POST['fresh'] ?? false);
        $perPage   = 100;

        if (empty($ldpUrls)) {
            wp_send_json_error('Danh sách LDP URL trống.');
        }

        // Khởi tạo Google Sheets
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            wp_send_json_error('Không thể lấy access token. Kiểm tra Service Account.');
        }

        $spreadsheetId = $this->getSpreadsheetId($settings['sheet_url']);
        if (empty($spreadsheetId)) {
            wp_send_json_error('Spreadsheet ID không hợp lệ.');
        }

        $sheetTab = $settings['sheet_tab'];

        // Page 1: xóa data cũ (fresh) hoặc giữ nguyên (resume), rồi kiểm tra và ghi header nếu cần
        if ($page === 1) {
            if ($isFresh) {
                $clearErr = $this->clearSheetData($accessToken, $spreadsheetId, $sheetTab);
                if ($clearErr !== true) {
                    wp_send_json_error('Không thể xóa dữ liệu cũ trên Sheet: ' . $clearErr);
                }
            }
            // Chỉ ghi header nếu A1 đang trống
            $this->writeHeader($accessToken, $spreadsheetId, $sheetTab);
        }

        // Build date_query
        $dateQuery = [];
        if (!empty($dateFrom) || !empty($dateTo)) {
            $range = ['column' => 'post_date', 'inclusive' => true];
            if (!empty($dateFrom)) {
                [$y, $m, $d]    = explode('-', $dateFrom);
                $range['after'] = ['year' => (int)$y, 'month' => (int)$m, 'day' => (int)$d];
            }
            if (!empty($dateTo)) {
                [$y, $m, $d]     = explode('-', $dateTo);
                $range['before'] = ['year' => (int)$y, 'month' => (int)$m, 'day' => (int)$d];
            }
            $dateQuery = [$range];
        }

        $args = [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $perPage,
            'paged'          => $page,
            'no_found_rows'  => false,
        ];
        if (!empty($dateQuery)) {
            $args['date_query'] = $dateQuery;
        }

        $query      = new \WP_Query($args);
        $totalPages = (int)$query->max_num_pages;
        $chunkData  = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $postUrl = get_permalink($post->ID);
                $links   = $this->extractLinksFromHtml($post->post_content);

                foreach ($links as $link) {
                    $normalizedHref = $this->normalizeUrl($link['href']);
                    foreach ($ldpUrls as $ldpUrl) {
                        if ($this->isMatchingLdp($normalizedHref, $ldpUrl)) {
                            $chunkData[] = [$postUrl, $link['href'], $link['text'], $link['type']];
                            break;
                        }
                    }
                }
            }
            wp_reset_postdata();
        }

        // Ghi ngay vào Sheet nếu có dữ liệu
        $writeErr = null;
        if (!empty($chunkData)) {
            $writeResult = $this->appendSheetRows($accessToken, $spreadsheetId, $sheetTab, $chunkData);
            if ($writeResult !== true) {
                $writeErr = $writeResult;
            }
        }

        // Ghi Sheet lỗi thì KHÔNG cập nhật progress: progress vẫn trỏ trang trước, Resume sẽ
        // quét lại đúng trang này. Trước đây lưu progress trước rồi mới báo lỗi -> Resume nhảy
        // sang trang kế, dữ liệu trang lỗi mất hẳn.
        if ($writeErr) {
            wp_send_json_error('Quét trang ' . $page . ' được nhưng ghi Sheet thất bại: ' . $writeErr);
        }

        // Cập nhật progress transient (chỉ lưu page state, không lưu data)
        $done     = ($page >= $totalPages);
        // Lượt mới (fresh) bắt đầu đếm lại; getProgress() trả [] chứ không phải null nên dùng ?:.
        $progress = ($page === 1 && $isFresh) ? [] : $this->getProgress();
        $progress = $progress ?: ['count' => 0, 'started_at' => current_time('mysql')];
        $progress['page']        = $page;
        $progress['total_pages'] = $totalPages;
        $progress['count']       = ($progress['count'] ?? 0) + count($chunkData);

        if ($done) {
            $this->clearProgress(); // Xóa transient khi hoàn tất
        } else {
            $this->saveProgress($progress);
        }

        wp_send_json_success([
            'page'        => $page,
            'total_pages' => $totalPages,
            'count'       => $progress['count'],
            'chunk_count' => count($chunkData),
            'done'        => $done,
        ]);
    }

    // =========================================================================
    // Domain Validation
    // =========================================================================

    /**
     * Kiểm tra domain hiện tại có khớp với cấu hình không
     * Tránh trường hợp staging tự động export data lên Google Sheet
     *
     * @return true|string True nếu khớp, string lỗi nếu không khớp
     */
    private function validateSiteDomain(array $settings)
    {
        if (empty($settings['site_domain'])) {
            return 'Chưa cấu hình Site Domain. Vui lòng điền domain trước khi export.';
        }

        $currentHost = parse_url(site_url(), PHP_URL_HOST);

        // Chuẩn hoá domain: loại bỏ protocol + trailing slash
        $configuredDomain = preg_replace('#^https?://#', '', trim($settings['site_domain']));
        $configuredDomain = rtrim($configuredDomain, '/');

        if (strcasecmp($currentHost, $configuredDomain) !== 0) {
            return 'Site hiện tại (' . $currentHost . ') không khớp với domain đã cấu hình (' . $configuredDomain . ')';
        }

        return true;
    }

    // =========================================================================
    // Progress Helpers (Transient)
    // =========================================================================

    public function ajaxGetProgress()
    {
        check_ajax_referer('vnx_internal_link_ldp_nonce', 'nonce');
        wp_send_json_success($this->getProgress());
    }

    public function ajaxClearProgress()
    {
        check_ajax_referer('vnx_internal_link_ldp_nonce', 'nonce');
        $this->clearProgress();
        wp_send_json_success('Đã xoá trạng thái export.');
    }

    private function getProgress(): array
    {
        $data = get_transient('vnx_illdp_progress');
        return is_array($data) ? $data : [];
    }

    private function saveProgress(array $data): void
    {
        set_transient('vnx_illdp_progress', $data, 3 * HOUR_IN_SECONDS);
    }

    private function clearProgress(): void
    {
        delete_transient('vnx_illdp_progress');
    }

    // =========================================================================
    // Google Sheets – Write Header
    // =========================================================================

    private function writeHeader(string $token, string $spreadsheetId, string $tab): void
    {
        // Kiểm tra nếu A1 đã có nội dung thì bỏ qua (resume scenario)
        $checkUrl  = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/" . urlencode($tab . '!A1');
        $checkResp = wp_remote_get($checkUrl, [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'timeout' => 15,
        ]);
        if (!is_wp_error($checkResp)) {
            $body = json_decode(wp_remote_retrieve_body($checkResp), true);
            if (!empty($body['values'])) {
                return; // A1 đã có header, không ghi đè
            }
        }

        // A1 trống → ghi header mới
        $range   = $tab . '!A1:D1';
        $encoded = urlencode($range);
        $url     = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$encoded}?valueInputOption=USER_ENTERED";

        wp_remote_request($url, [
            'method'  => 'PUT',
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'body'    => json_encode([
                'range'          => $range,
                'majorDimension' => 'ROWS',
                'values'         => [['URL bài viết', 'Link LDP', 'Anchor Text', 'Loại HTML']],
            ]),
            'timeout' => 20,
        ]);
    }

    /**
     * Parse tất cả thẻ <a> từ HTML content.
     *
     * @param  string $html HTML content
     * @return array  Mảng các ['href', 'text', 'type']
     */
    private function extractLinksFromHtml(string $html): array
    {
        if (empty(trim($html))) {
            return [];
        }

        $links = [];

        // Dùng regex nhẹ thay DOMDocument để tránh vấn đề encode tiếng Việt
        // Lấy toàn bộ thẻ <a ...>...</a>
        if (!preg_match_all('/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER)) {
            return [];
        }

        foreach ($matches as $match) {
            $href = trim($match[1]);
            $text = trim(wp_strip_all_tags($match[2]));

            if (empty($href) || $href === '#' || strpos($href, 'javascript:') === 0) {
                continue;
            }

            $links[] = [
                'href' => $href,
                'text' => $text,
                'type' => '<a>',
            ];
        }

        return $links;
    }

    /**
     * So sánh href với một LDP URL (normalize cả hai).
     */
    private function isMatchingLdp(string $href, string $ldpUrl): bool
    {
        return $href === $ldpUrl;
    }

    /**
     * Normalize URL: bỏ trailing slash, lowercase scheme+host, giữ nguyên path.
     */
    private function normalizeUrl(string $url): string
    {
        $url = trim($url);

        // Thêm scheme nếu thiếu
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $parsed = wp_parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? 'https');
        $host   = strtolower($parsed['host']   ?? '');
        $path   = rtrim($parsed['path'] ?? '/', '/') . '/';
        $query  = !empty($parsed['query']) ? '?' . $parsed['query'] : '';

        return $scheme . '://' . $host . $path . $query;
    }

    /**
     * Parse danh sách LDP URLs từ textarea (mỗi dòng 1 URL).
     */
    private function parseLdpUrls(string $raw): array
    {
        if (empty(trim($raw))) {
            return array_map([$this, 'normalizeUrl'], self::DEFAULT_LDP_URLS);
        }

        $lines = preg_split('/[\r\n]+/', $raw);
        $urls  = [];

        foreach ($lines as $line) {
            $url = trim($line);
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $urls[] = $this->normalizeUrl($url);
            }
        }

        return array_unique($urls);
    }

    // =========================================================================
    // Google Sheets – Auth
    // =========================================================================

    private function getAccessToken()
    {
        try {
            $creds = vnx_center_gbot_credentials();

            if (empty($creds['client_email']) || empty($creds['private_key'])) {
                return false;
            }

            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $now    = time();
            $claim  = json_encode([
                'iss'   => $creds['client_email'],
                'scope' => 'https://www.googleapis.com/auth/spreadsheets',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'exp'   => $now + 3600,
                'iat'   => $now,
            ]);

            $b64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
            $b64Claim  = rtrim(strtr(base64_encode($claim),  '+/', '-_'), '=');
            $input     = $b64Header . '.' . $b64Claim;

            openssl_sign($input, $signature, $creds['private_key'], 'SHA256');
            $b64Sig = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
            $jwt    = $input . '.' . $b64Sig;

            $response = wp_remote_post('https://oauth2.googleapis.com/token', [
                'body'    => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ],
                'timeout' => 30,
            ]);

            if (is_wp_error($response)) {
                error_log('VNX_InternalLinkLDP_Center: getAccessToken error – ' . $response->get_error_message());
                return false;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            return $body['access_token'] ?? false;
        } catch (\Exception $e) {
            error_log('VNX_InternalLinkLDP_Center: getAccessToken exception – ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // Google Sheets – CRUD
    // =========================================================================

    private function getSpreadsheetId(string $url): string
    {
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return $matches[1] ?? '';
    }

    /**
     * Xóa toàn bộ data rows trong Sheet (giữ lại header dòng 1).
     *
     * @return true|string  true nếu thành công, string lỗi nếu xảy ra lỗi.
     */
    private function clearSheetData(string $token, string $spreadsheetId, string $tab)
    {
        $sheetId = $this->getSheetId($token, $spreadsheetId, $tab);
        if ($sheetId === null) {
            // Tab chưa tồn tại — không cần xóa
            return true;
        }

        // Xóa toàn bộ dữ liệu (kể cả A1) để data mới ghi từ đầu
        $range    = urlencode($tab . '!A1:Z100000');
        $url      = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}:clear";

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'body'    => '{}',
            'timeout' => 30,
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return 'Lỗi clear Sheet: ' . ($body['error']['message'] ?? 'Unknown');
        }

        return true;
    }

    private function getSheetId(string $token, string $spreadsheetId, string $tab): ?int
    {
        $url      = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}?fields=sheets.properties";
        $response = wp_remote_get($url, [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        foreach ($body['sheets'] ?? [] as $sheet) {
            if (($sheet['properties']['title'] ?? '') === $tab) {
                return (int) $sheet['properties']['sheetId'];
            }
        }

        return null;
    }

    private function appendSheetRows(string $token, string $spreadsheetId, string $tab, array $rows)
    {
        $range    = $tab . '!A2:D';
        $encoded  = urlencode($range);
        $url      = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$encoded}:append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS";

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'body'    => json_encode([
                'range'          => $range,
                'majorDimension' => 'ROWS',
                'values'         => $rows,
            ]),
            'timeout' => 60,
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return 'Lỗi thêm dòng: ' . ($body['error']['message'] ?? 'Unknown');
        }

        return true;
    }
}

new VNX_InternalLinkLDP_Center();
