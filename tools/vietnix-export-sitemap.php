<?php

class VietnixExportSitemap_Center
{
    private const OPTION_NAME = 'vnx_export_sitemap_setting';

    private const DEFAULT_SETTINGS = [
        'sheet_url' => '',
        'sheet_tab' => '',
        'site_domain' => '',
    ];


    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_ajax_save_export_sitemap_settings_center', [$this, 'handleSaveSettings']);
        add_action('wp_ajax_get_export_sitemap_setting_center', [$this, 'handleGetSettings']);
        add_action('wp_ajax_export_sitemap_now_center', [$this, 'handleExportNow']);
    }

    // =========================================================================
    // Admin Scripts
    // =========================================================================

    /**
     * Enqueue scripts và localize data
     */
    public function enqueueScripts()
    {
        // Chi nap tren trang cua center: nap o moi trang admin (ke ca trang cua vietnix-plugin) thi Vue/CSS
        // cua center chay tren giao dien cua plugin kia va lam nang admin.
        if (!vnx_center_is_own_admin_page()) {
            return;
        }

        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        wp_enqueue_script('vietnix-export-sitemap-center', VNX_PLUGIN_URL_CENTER . 'tools/inc/js/vietnix-export-sitemap.js', ['jquery', 'vuejs-library-center'], '1.0', true);

        wp_localize_script('vietnix-export-sitemap-center', 'vietnixExportSitemapData', [
            'nonce' => wp_create_nonce('vietnix_export_sitemap_nonce'),
        ]);
    }

    // =========================================================================
    // Settings CRUD
    // =========================================================================

    /**
     * Lấy cài đặt dạng JSON string
     *
     * @return string JSON string
     */
    private function getSetting()
    {
        $dataOption = get_option(self::OPTION_NAME);

        if (!$dataOption) {
            $jsonData = json_encode(self::DEFAULT_SETTINGS);
            add_option(self::OPTION_NAME, $jsonData, '', 'yes');
            return $jsonData;
        }

        return $dataOption;
    }

    /**
     * Lấy cài đặt dạng array (parsed)
     *
     * @return array
     */
    private function getSettingArray()
    {
        return json_decode($this->getSetting(), true);
    }

    /**
     * Cập nhật cài đặt
     *
     * @param array $data Dữ liệu settings
     * @return bool
     */
    private function updateSettings($data)
    {
        try {
            update_option(self::OPTION_NAME, json_encode($data), '');
            return true;
        } catch (\Exception $ex) {
            error_log('Lỗi updateSettings ExportSitemap: ' . $ex->getMessage());
            return false;
        }
    }

    // =========================================================================
    // AJAX Handlers
    // =========================================================================

    /**
     * API lấy cài đặt (AJAX)
     */
    public function handleGetSettings()
    {
        check_ajax_referer('vietnix_export_sitemap_nonce', 'nonce');

        wp_send_json_success($this->getSettingArray());
    }

    /**
     * Xử lý lưu cài đặt (AJAX)
     */
    public function handleSaveSettings()
    {
        try {
            check_ajax_referer('vietnix_export_sitemap_nonce', 'nonce');

            if (!isset($_POST['settings']) || !is_array($_POST['settings'])) {
                wp_send_json_error('Dữ liệu settings không hợp lệ');
            }

            $settings = [
                'sheet_url' => sanitize_text_field($_POST['settings']['sheet_url']),
                'sheet_tab' => sanitize_text_field($_POST['settings']['sheet_tab']),
                'site_domain' => sanitize_text_field($_POST['settings']['site_domain']),
            ];

            $this->updateSettings($settings);

            wp_send_json_success('Cài đặt đã được lưu thành công');
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Xử lý export ngay (AJAX)
     */
    public function handleExportNow()
    {
        try {
            check_ajax_referer('vietnix_export_sitemap_nonce', 'nonce');

            $result = $this->exportToGoogleSheet();

            if ($result === true) {
                wp_send_json_success('Export thành công lên Google Sheet');
            } else {
                wp_send_json_error('Export thất bại: ' . $result);
            }
        } catch (\Exception $e) {
            wp_send_json_error('Lỗi: ' . $e->getMessage());
        }
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
    private function validateSiteDomain()
    {
        $settings = $this->getSettingArray();

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
    // Google Sheets API
    // =========================================================================

    /**
     * Lấy Spreadsheet ID từ URL
     *
     * @param string $url Google Sheet URL
     * @return string Spreadsheet ID hoặc rỗng
     */
    private function getSpreadsheetId($url)
    {
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }

    /**
     * Lấy access token từ service account credentials
     *
     * @return string|false Access token hoặc false nếu thất bại
     */
    private function getAccessToken()
    {
        try {
            $credentials = vnx_center_gbot_credentials();

            if (empty($credentials['client_email']) || empty($credentials['private_key'])) {
                return false;
            }

            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $now = time();
            $claim = json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/spreadsheets',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]);

            $base64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
            $base64Claim = rtrim(strtr(base64_encode($claim), '+/', '-_'), '=');
            $signatureInput = $base64Header . '.' . $base64Claim;

            openssl_sign($signatureInput, $signature, $credentials['private_key'], 'SHA256');
            $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

            $jwt = $signatureInput . '.' . $base64Signature;

            $response = wp_remote_post('https://oauth2.googleapis.com/token', [
                'body' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
                'timeout' => 30,
            ]);

            if (is_wp_error($response)) {
                error_log('Lỗi lấy access token: ' . $response->get_error_message());
                return false;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($body['access_token'])) {
                return $body['access_token'];
            }

            error_log('Lỗi lấy access token: ' . wp_remote_retrieve_body($response));
            return false;
        } catch (\Exception $ex) {
            error_log('Lỗi getAccessToken: ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * Export dữ liệu lên Google Sheet (incremental diff)
     * Chỉ thêm/xóa những row thay đổi thay vì xóa hết rồi ghi lại
     *
     * @return true|string True nếu thành công, string lỗi nếu thất bại
     */
    public function exportToGoogleSheet()
    {
        try {
            $domainCheck = $this->validateSiteDomain();
            if ($domainCheck !== true) {
                return $domainCheck;
            }

            $settings = $this->getSettingArray();

            if (empty($settings['sheet_url']) || empty($settings['sheet_tab'])) {
                return 'Vui lòng cấu hình đầy đủ Sheet URL và Tab trước khi export';
            }

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return 'Không thể lấy access token. Vui lòng kiểm tra lại cấu hình Service Account.';
            }

            $spreadsheetId = $this->getSpreadsheetId($settings['sheet_url']);
            if (empty($spreadsheetId)) {
                return 'Không thể lấy Spreadsheet ID từ URL. Vui lòng kiểm tra lại URL.';
            }

            $sheetTab = $settings['sheet_tab'];

            // 1. Lấy dữ liệu mới từ WordPress
            $newData = $this->getAllPostsAndPages();

            // 2. Đọc dữ liệu hiện tại từ Google Sheet
            $existingData = $this->readSheetData($accessToken, $spreadsheetId, $sheetTab);

            // 3. Tính toán diff
            $diff = $this->calculateDiff($existingData, $newData);

            // 4. Không có thay đổi → bỏ qua
            if (!$diff['hasChanges']) {
                error_log('Export sitemap: không có thay đổi');
                return true;
            }

            // 5. Áp dụng thay đổi incremental
            return $this->applyDiff($accessToken, $spreadsheetId, $sheetTab, $diff);
        } catch (\Exception $ex) {
            error_log('Lỗi exportToGoogleSheet: ' . $ex->getMessage());
            return $ex->getMessage();
        }
    }

    // =========================================================================
    // Google Sheets Read / Diff / Apply
    // =========================================================================

    /**
     * Đọc dữ liệu hiện tại từ Google Sheet (từ hàng 2 trở đi)
     *
     * @param string $accessToken
     * @param string $spreadsheetId
     * @param string $sheetTab
     * @return array Mảng 2D rows, rỗng nếu không có data hoặc lỗi
     */
    private function readSheetData($accessToken, $spreadsheetId, $sheetTab)
    {
        $range = urlencode($sheetTab . '!A2:D');
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}";

        $response = wp_remote_get($url, [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            error_log('Lỗi đọc sheet data: ' . $response->get_error_message());
            return [];
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        if ($responseCode !== 200) {
            error_log('Lỗi đọc sheet data, HTTP code: ' . $responseCode);
            return [];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $rows = isset($body['values']) ? $body['values'] : [];

        // Normalize: đảm bảo mỗi row luôn có đủ 4 cột
        return array_map([$this, 'normalizeRow'], $rows);
    }

    /**
     * Normalize row luôn có đủ 4 phần tử
     * Google Sheets API bỏ trailing empty cells
     *
     * @param mixed $row
     * @return array [url, post_type, child_cat, parent_cat]
     */
    private function normalizeRow($row)
    {
        return array_pad(is_array($row) ? $row : [], 4, '');
    }

    /**
     * Tạo composite key từ row data để so sánh
     *
     * @param array $row [url, post_type, child_cat, parent_cat]
     * @return string
     */
    private function buildRowKey($row)
    {
        $normalized = $this->normalizeRow($row);
        return implode("\t", $normalized);
    }

    /**
     * Parse composite key thành row data
     *
     * @param string $key
     * @return array [url, post_type, child_cat, parent_cat]
     */
    private function parseRowKey($key)
    {
        return explode("\t", $key);
    }

    /**
     * Tính toán diff giữa dữ liệu hiện tại trên sheet và dữ liệu mới từ WP
     *
     * @param array $existingData Dữ liệu đang có trên sheet
     * @param array $newData Dữ liệu mới từ WordPress
     * @return array ['hasChanges' => bool, 'toAdd' => array, 'toRemoveIndices' => array]
     */
    private function calculateDiff($existingData, $newData)
    {
        // Bag (multiset) cho existing: key => [index1, index2, ...]
        $existingBag = [];
        foreach ($existingData as $index => $row) {
            $key = $this->buildRowKey($row);
            if (!isset($existingBag[$key])) {
                $existingBag[$key] = [];
            }
            $existingBag[$key][] = $index;
        }

        // Bag cho new: key => ['count' => int, 'row' => array]
        $newBag = [];
        foreach ($newData as $row) {
            $key = $this->buildRowKey($row);
            if (!isset($newBag[$key])) {
                $newBag[$key] = ['count' => 0, 'row' => $row];
            }
            $newBag[$key]['count']++;
        }

        $toRemoveIndices = [];
        $toAdd = [];

        // Rows thừa trên sheet (có trên sheet nhưng không cần nữa)
        foreach ($existingBag as $key => $indices) {
            $needed = isset($newBag[$key]) ? $newBag[$key]['count'] : 0;
            $have = count($indices);

            if ($have > $needed) {
                // Giữ $needed row đầu, xóa phần thừa
                $excess = array_slice($indices, $needed);
                $toRemoveIndices = array_merge($toRemoveIndices, $excess);
            }
        }

        // Rows thiếu trên sheet (có trong WP nhưng chưa có trên sheet)
        foreach ($newBag as $key => $info) {
            $have = isset($existingBag[$key]) ? count($existingBag[$key]) : 0;

            if ($info['count'] > $have) {
                $addCount = $info['count'] - $have;
                for ($i = 0; $i < $addCount; $i++) {
                    $toAdd[] = $info['row'];
                }
            }
        }

        return [
            'hasChanges' => !empty($toAdd) || !empty($toRemoveIndices),
            'toAdd' => $toAdd,
            'toRemoveIndices' => $toRemoveIndices,
        ];
    }

    /**
     * Áp dụng diff lên Google Sheet
     *
     * @param string $accessToken
     * @param string $spreadsheetId
     * @param string $sheetTab
     * @param array $diff Kết quả từ calculateDiff
     * @return true|string
     */
    private function applyDiff($accessToken, $spreadsheetId, $sheetTab, $diff)
    {
        $removeCount = count($diff['toRemoveIndices']);
        $addCount = count($diff['toAdd']);

        // Thêm rows mới trước (nếu có). Phải làm trước bước xóa để sheet
        // không bao giờ rơi về 0 dòng dữ liệu giữa chừng — Google Sheets API
        // từ chối deleteDimension nếu nó xóa hết mọi dòng không bị đóng băng.
        if ($addCount > 0) {
            $result = $this->appendSheetRows($accessToken, $spreadsheetId, $sheetTab, $diff['toAdd']);
            if ($result !== true) {
                return $result;
            }
        }

        // Xóa rows thừa sau (nếu có)
        if ($removeCount > 0) {
            $sheetId = $this->getSheetId($accessToken, $spreadsheetId, $sheetTab);

            if ($sheetId === null) {
                return 'Không tìm thấy Sheet ID cho tab: ' . $sheetTab;
            }

            $result = $this->deleteSheetRows($accessToken, $spreadsheetId, $sheetId, $diff['toRemoveIndices']);
            if ($result !== true) {
                return $result;
            }
        }

        error_log("Export sitemap incremental: thêm {$addCount}, xóa {$removeCount} dòng");
        return true;
    }

    /**
     * Lấy Sheet ID (numeric) từ tab name
     * Cần cho deleteDimension API
     *
     * @param string $accessToken
     * @param string $spreadsheetId
     * @param string $sheetTab Tab name
     * @return int|null Sheet ID hoặc null
     */
    private function getSheetId($accessToken, $spreadsheetId, $sheetTab)
    {
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}?fields=sheets.properties";

        $response = wp_remote_get($url, [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('Lỗi lấy sheet metadata: ' . $response->get_error_message());
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['sheets'])) {
            return null;
        }

        foreach ($body['sheets'] as $sheet) {
            if (
                isset($sheet['properties']['title']) &&
                $sheet['properties']['title'] === $sheetTab
            ) {
                return (int) $sheet['properties']['sheetId'];
            }
        }

        return null;
    }

    /**
     * Xóa các row cụ thể khỏi Google Sheet bằng deleteDimension
     *
     * @param string $accessToken
     * @param string $spreadsheetId
     * @param int $sheetId Numeric sheet ID
     * @param array $rowIndices Mảng 0-based data indices (tính từ hàng dữ liệu, không tính header)
     * @return true|string
     */
    private function deleteSheetRows($accessToken, $spreadsheetId, $sheetId, $rowIndices)
    {
        // Sort giảm dần để xóa từ dưới lên, tránh lệch index
        rsort($rowIndices);

        $requests = [];
        foreach ($rowIndices as $dataIndex) {
            // dataIndex 0-based từ data → sheet row = dataIndex + 1 (skip header row 0)
            $sheetRowIndex = $dataIndex + 1;

            $requests[] = [
                'deleteDimension' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'dimension' => 'ROWS',
                        'startIndex' => $sheetRowIndex,
                        'endIndex' => $sheetRowIndex + 1,
                    ],
                ],
            ];
        }

        // Batch tối đa 100 request/lần để tránh vượt giới hạn API
        $chunks = array_chunk($requests, 100);

        foreach ($chunks as $chunk) {
            $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}:batchUpdate";

            $response = wp_remote_post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(['requests' => $chunk]),
                'timeout' => 30,
            ]);

            if (is_wp_error($response)) {
                return 'Lỗi xóa dòng: ' . $response->get_error_message();
            }

            $responseCode = wp_remote_retrieve_response_code($response);
            if ($responseCode !== 200) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                $errorMsg = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown';
                return "Lỗi xóa dòng ({$responseCode}): {$errorMsg}";
            }
        }

        return true;
    }

    /**
     * Thêm rows mới vào cuối Google Sheet
     *
     * @param string $accessToken
     * @param string $spreadsheetId
     * @param string $sheetTab Tab name
     * @param array $rows Mảng 2D rows cần thêm
     * @return true|string
     */
    private function appendSheetRows($accessToken, $spreadsheetId, $sheetTab, $rows)
    {
        $range = $sheetTab . '!A2:D';
        $encodedRange = urlencode($range);
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$encodedRange}:append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS";

        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'range' => $range,
                'majorDimension' => 'ROWS',
                'values' => $rows,
            ]),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            return 'Lỗi thêm dòng: ' . $response->get_error_message();
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        if ($responseCode !== 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $errorMsg = isset($body['error']['message']) ? $body['error']['message'] : 'Unknown';
            return "Lỗi thêm dòng ({$responseCode}): {$errorMsg}";
        }

        return true;
    }

    // =========================================================================
    // Sitemap Data Collection
    // =========================================================================

    /**
     * Lấy danh sách tất cả URL trong sitemap
     * Khớp logic với VNX_Sitemap_Center (vnx_new_sitemap.php):
     * - Tất cả post types được bật trong RankMath
     * - San-pham (custom page từ ACF)
     * - Loại bỏ noindex, redirect
     *
     * @return array Mảng 2D: [url, post_type, child_cat, parent_cat]
     */
    public function getAllPostsAndPages()
    {
        $data = [];
        $activeRedirects = $this->getActiveRedirects();
        $postTypes = $this->getEnabledPostTypes();

        foreach ($postTypes as $pt) {
            $urls = ($pt === 'page')
                ? $this->getPageUrls($activeRedirects)
                : $this->getPostTypeUrls($pt, $activeRedirects);

            $data = array_merge($data, $urls);
        }

        // Lấy san-pham (custom page từ ACF)
        $data = array_merge($data, $this->getSanPhamUrls($activeRedirects));

        return $data;
    }

    /**
     * Lấy danh sách post types được bật trong RankMath sitemap
     *
     * @return array Danh sách post type names
     */
    private function getEnabledPostTypes()
    {
        $postTypes = ['post', 'page'];
        $cpts = get_post_types(['public' => true, '_builtin' => false], 'names');
        $postTypes = array_merge($postTypes, $cpts);

        return array_filter($postTypes, [$this, 'shouldIncludePostType']);
    }

    /**
     * Kiểm tra post type có được bật trong RankMath sitemap không
     *
     * @param string $postType Post type name
     * @return bool
     */
    private function shouldIncludePostType($postType)
    {
        if (!class_exists('RankMath')) {
            return $this->shouldIncludePostTypeWithoutRankMath($postType);
        }

        $rmOptions = get_option('rank-math-options-sitemap');
        if (!$rmOptions) {
            return false;
        }

        $settingKey = 'pt_' . $postType . '_sitemap';

        return isset($rmOptions[$settingKey])
            && ($rmOptions[$settingKey] === 'on' || $rmOptions[$settingKey] === true);
    }

    /**
     * Fallback kiểm tra post type khi không có RankMath
     *
     * @param string $postType Post type name
     * @return bool
     */
    private function shouldIncludePostTypeWithoutRankMath($postType)
    {
        $excludedTypes = [
            'attachment', 'revision', 'nav_menu_item', 'custom_css',
            'customize_changeset', 'oembed_cache', 'user_request',
            'wp_block', 'wp_template', 'wp_template_part',
            'wp_global_styles', 'wp_navigation',
        ];

        if (in_array($postType, $excludedTypes, true)) {
            return false;
        }

        $postTypeObj = get_post_type_object($postType);
        return $postTypeObj && $postTypeObj->public;
    }

    /**
     * Kiểm tra post có bị noindex không (RankMath)
     *
     * @param int $postId Post ID
     * @return bool
     */
    private function isPostNoindex($postId)
    {
        $rm = get_post_meta($postId, 'rank_math_robots', true);
        if (!$rm) {
            return false;
        }

        if (is_array($rm)) {
            return in_array('noindex', $rm, true);
        }

        return is_string($rm) && strpos($rm, 'noindex') !== false;
    }

    // =========================================================================
    // Redirect Detection
    // =========================================================================

    /**
     * Lấy tất cả redirect đang active từ bảng RankMath
     *
     * @return array Danh sách redirect rules
     */
    private function getActiveRedirects()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'rank_math_redirections';

        $tableExists = $wpdb->get_var(
            $wpdb->prepare("SHOW TABLES LIKE %s", $table)
        );

        if (!$tableExists) {
            return [];
        }

        $results = $wpdb->get_results(
            "SELECT sources, header_code FROM {$table} WHERE status = 'active'",
            ARRAY_A
        );

        return !empty($results) ? $results : [];
    }

    /**
     * Kiểm tra URL có bị redirect hay không
     *
     * @param string $url URL cần kiểm tra
     * @param array $activeRedirects Danh sách redirect đang active
     * @return bool True nếu URL bị redirect
     */
    private function isRedirectedUrl($url, $activeRedirects)
    {
        if (empty($activeRedirects)) {
            return false;
        }

        $urlPath = parse_url($url, PHP_URL_PATH);
        if ($urlPath === null) {
            return false;
        }

        $urlPath = rtrim($urlPath, '/');

        foreach ($activeRedirects as $redirect) {
            if ($this->matchRedirectSources($urlPath, $redirect)) {
                return true;
            }
        }

        return false;
    }

    /**
     * So khớp URL path với redirect sources
     *
     * @param string $urlPath URL path (đã trim slash)
     * @param array $redirect Redirect rule chứa sources
     * @return bool
     */
    private function matchRedirectSources($urlPath, $redirect)
    {
        $sources = maybe_unserialize($redirect['sources']);

        if (!is_array($sources)) {
            return false;
        }

        foreach ($sources as $source) {
            if (!isset($source['pattern'])) {
                continue;
            }

            $pattern = rtrim($source['pattern'], '/');
            $comparison = isset($source['comparison']) ? $source['comparison'] : 'exact';

            if ($this->matchPattern($urlPath, $pattern, $comparison)) {
                return true;
            }
        }

        return false;
    }

    /**
     * So khớp URL path với pattern theo kiểu comparison
     *
     * @param string $urlPath URL path
     * @param string $pattern Pattern cần so khớp
     * @param string $comparison Kiểu so khớp: exact, contains, start, end, regex
     * @return bool
     */
    private function matchPattern($urlPath, $pattern, $comparison)
    {
        switch ($comparison) {
            case 'exact':
                return ($urlPath === $pattern || $urlPath === '/' . $pattern);

            case 'contains':
                return (strpos($urlPath, $pattern) !== false);

            case 'start':
                return (strpos($urlPath, $pattern) === 0 || strpos($urlPath, '/' . $pattern) === 0);

            case 'end':
                return (substr($urlPath, -strlen($pattern)) === $pattern);

            case 'regex':
                return (bool) @preg_match('/' . $pattern . '/', $urlPath);

            default:
                return ($urlPath === $pattern || $urlPath === '/' . $pattern);
        }
    }

    // =========================================================================
    // URL Collectors (per post type)
    // =========================================================================

    /**
     * Lấy URLs cho một post type (trừ page)
     * Tự động detect taxonomy và lấy category cha/con
     *
     * @param string $postType Post type name
     * @param array $activeRedirects Danh sách redirect đang active
     * @return array
     */
    private function getPostTypeUrls($postType, $activeRedirects)
    {
        $data = [];

        $posts = get_posts([
            'post_type' => $postType,
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);

        if (empty($posts)) {
            return $data;
        }

        $taxonomy = $this->getPrimaryTaxonomy($postType);

        foreach ($posts as $post) {
            $url = get_permalink($post->ID);

            if ($this->isRedirectedUrl($url, $activeRedirects)) {
                continue;
            }

            if ($this->isPostNoindex($post->ID)) {
                continue;
            }

            if ($taxonomy === null) {
                $data[] = [$url, $postType, '', ''];
                continue;
            }

            $terms = ($postType === 'post')
                ? get_the_category($post->ID)
                : get_the_terms($post->ID, $taxonomy);

            $data = array_merge($data, $this->buildTermRows($url, $postType, $terms, $taxonomy));
        }

        return $data;
    }

    /**
     * Lấy URLs cho pages (loại bỏ san-pham)
     *
     * @param array $activeRedirects Danh sách redirect đang active
     * @return array
     */
    private function getPageUrls($activeRedirects)
    {
        global $wpdb;

        $data = [];

        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);

        if (empty($pages)) {
            return $data;
        }

        // Lấy danh sách page IDs thuộc san-pham (xử lý riêng ở getSanPhamUrls)
        $sanPhamPageIds = $this->getSanPhamPageIds($pages);

        foreach ($pages as $page) {
            if (in_array($page->ID, $sanPhamPageIds)) {
                continue;
            }

            $url = get_permalink($page->ID);

            if ($this->isRedirectedUrl($url, $activeRedirects)) {
                continue;
            }

            if ($this->isPostNoindex($page->ID)) {
                continue;
            }

            $data[] = [$url, 'page', '', ''];
        }

        return $data;
    }

    /**
     * Lấy URLs cho san-pham (custom page từ ACF)
     *
     * @param array $activeRedirects Danh sách redirect đang active
     * @return array
     */
    private function getSanPhamUrls($activeRedirects)
    {
        $data = [];

        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'meta_key' => 'vnx_check_product_page',
            'meta_value' => 'san-pham',
            'meta_compare' => 'LIKE',
            'posts_per_page' => -1,
        ]);

        if (empty($pages)) {
            return $data;
        }

        foreach ($pages as $page) {
            $url = get_permalink($page->ID);

            if ($this->isRedirectedUrl($url, $activeRedirects)) {
                continue;
            }

            if ($this->isPostNoindex($page->ID)) {
                continue;
            }

            $data[] = [$url, 'san-pham', '', ''];
        }

        return $data;
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Lấy danh sách page IDs có ACF field san-pham
     *
     * @param array $pages Mảng WP_Post
     * @return array Mảng page IDs
     */
    private function getSanPhamPageIds($pages)
    {
        global $wpdb;

        $pageIds = array_map(function ($p) {
            return (int) $p->ID;
        }, $pages);

        if (empty($pageIds)) {
            return [];
        }

        $safeIds = implode(',', $pageIds);

        return $wpdb->get_col(
            "SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = 'vnx_check_product_page' 
            AND meta_value = 'san-pham' 
            AND post_id IN ({$safeIds})"
        );
    }

    /**
     * Build rows từ terms (category cha/con) cho một URL
     *
     * @param string $url URL
     * @param string $postType Post type name
     * @param array|false|\WP_Error $terms Danh sách terms
     * @param string $taxonomy Taxonomy name
     * @return array
     */
    private function buildTermRows($url, $postType, $terms, $taxonomy)
    {
        if (empty($terms) || is_wp_error($terms)) {
            return [[$url, $postType, '', '']];
        }

        $rows = [];

        foreach ($terms as $term) {
            $childCat = $term->name;
            $parentCat = '';

            if ($term->parent != 0) {
                $parentTerm = ($postType === 'post')
                    ? get_category($term->parent)
                    : get_term($term->parent, $taxonomy);

                $parentCat = ($parentTerm && !is_wp_error($parentTerm)) ? $parentTerm->name : '';
            }

            $rows[] = [$url, $postType, $childCat, $parentCat];
        }

        return $rows;
    }

    /**
     * Lấy taxonomy chính cho một post type
     *
     * @param string $postType Post type name
     * @return string|null Taxonomy name hoặc null
     */
    private function getPrimaryTaxonomy($postType)
    {
        if ($postType === 'post') {
            return 'category';
        }

        $taxonomies = get_object_taxonomies($postType, 'objects');

        if (empty($taxonomies)) {
            return null;
        }

        // Ưu tiên taxonomy hierarchical + public (giống category)
        foreach ($taxonomies as $tax) {
            if ($tax->hierarchical && $tax->public) {
                return $tax->name;
            }
        }

        // Fallback: taxonomy public đầu tiên
        foreach ($taxonomies as $tax) {
            if ($tax->public) {
                return $tax->name;
            }
        }

        return null;
    }
}

new VietnixExportSitemap_Center();
