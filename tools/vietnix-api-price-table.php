<?php

class VNX_API_Price_Table_Center
{
    private $option_name = 'vnx_api_price_table_urls';

    /**
     * Map widget_name → import_key (dùng để biết key nào chứa CSV URL)
     */
    private $widget_csv_keys = [
        'vnx-service-price-v2' => 'import-csv',
        'vnx-service-price' => 'upload',
        'vnx-table' => 'upload',
        'vnx-tab-service-price' => 'list-service',
    ];

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Đăng ký REST API route: /wp-json/vnx_api/v1/thong-tin-khuyen-mai
     */
    public function register_routes()
    {
        register_rest_route(VNX_Api_Prefix_V1, '/thong-tin-khuyen-mai', [
            'methods' => 'GET',
            'callback' => [$this, 'get_promotion_info'],
            'permission_callback' => ['\\VNX_API_Center\\VietnixPluginAPI', 'authenticate'],
        ]);
    }

    // ===================================================================
    // API Callback
    // ===================================================================

    public function get_promotion_info(\WP_REST_Request $request)
    {
        try {
            $urls_raw = get_option($this->option_name, '');
            $urls = $this->parse_urls($urls_raw);

            if (empty($urls)) {
                return new \WP_REST_Response([
                    'success' => true,
                    'message' => 'Không có URL nào được cấu hình',
                    'data' => [],
                ], 200);
            }

            $results = [];
            foreach ($urls as $url) {
                $results[] = $this->process_url($url);
            }

            return new \WP_REST_Response([
                'success' => true,
                'message' => 'Lấy thông tin khuyến mãi thành công',
                'data' => $results,
            ], 200);
        } catch (\Throwable $e) {
            error_log('VNX_API_Price_Table_Center error: ' . $e->getMessage());
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Lỗi khi xử lý: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    // ===================================================================
    // Xử lý chính
    // ===================================================================

    /**
     * Xử lý 1 URL: tìm page → quét Bricks tìm tất cả CSV → parse → convert
     */
    private function process_url($url)
    {
        $post = $this->get_post_by_url($url);
        $post_type = $post ? get_post_type($post) : '';

        $base_data = [
            'ID' => $post ? $post->ID : 0,
            'title' => $post ? get_the_title($post) : '',
            'link' => $url,
            'success' => $post ? true : false,
        ];

        if (!$post) {
            $base_data['tablePrice'] = [];
            return $base_data;
        }

        // Quét tất cả Bricks widgets tìm CSV files
        $csv_items = $this->find_all_csv_in_bricks($post->ID);

        if (empty($csv_items)) {
            $base_data['tablePrice'] = [];
            return $base_data;
        }

        // Parse từng CSV tìm được
        $tablePrices = [];
        foreach ($csv_items as $item) {
            $csv_url = $item['csv_url'];
            $widget_name = $item['widget_name'];

            $csv_data = $this->read_csv_file($csv_url);
            if (empty($csv_data))
                continue;

            // Auto-detect converter dựa trên widget_name
            $converted = $this->auto_convert($csv_data, $widget_name);
            if (!empty($converted)) {
                $tablePrices[] = $converted;
            }
        }

        $base_data['tablePrice'] = $tablePrices;
        return $base_data;
    }

    /**
     * Tự động chọn converter dựa trên widget_name
     */
    private function auto_convert($csv_data, $widget_name)
    {
        switch ($widget_name) {
            case 'vnx-service-price-v2':
            case 'vnx-tab-service-price':
                return $this->vnx_price_hosting_v2($csv_data);

            case 'vnx-service-price':
                return $this->vnx_price_hosting_v1($csv_data);

            case 'vnx-table':
                // Thử detect dựa trên nội dung header
                return $this->auto_detect_table($csv_data);

            default:
                // Widget không rõ loại → trả về raw CSV dạng JSON
                return $this->raw_csv_to_json($csv_data);
        }
    }

    /**
     * Auto-detect loại bảng giá vnx-table dựa trên nội dung
     */
    private function auto_detect_table($csv_data)
    {
        // Kiểm tra header row đầu tiên để xác định loại
        $firstCol = $csv_data[0] ?? [];
        $headerText = implode(' ', $firstCol);
        $headerLower = mb_strtolower($headerText);

        // Detect tên miền
        if (strpos($headerLower, 'tld') !== false || strpos($headerLower, 'tên miền') !== false || strpos($headerLower, 'domain') !== false) {
            return $this->domain($csv_data);
        }

        // Detect email so sánh (có header row dạng table thường)
        if (strpos($headerLower, 'email') !== false && isset($csv_data[0][1]) && strpos(mb_strtolower($csv_data[0][1] ?? ''), 'giá') !== false) {
            return $this->compare_email($csv_data);
        }

        // Detect local server / email dựa trên row count & structure
        // Nếu có plan names ở row[1]
        if (isset($csv_data[1]) && is_array($csv_data[1])) {
            $planNames = $csv_data[1];
            $uniqueNames = array_unique(array_filter($planNames));

            if (count($uniqueNames) > 1) {
                // Kiểm tra có spec rows pattern của email
                $emailKeywords = ['dung lượng', 'email', 'storage', 'mailbox'];
                $hasEmailKeyword = false;
                foreach ($csv_data as $row) {
                    $rowText = mb_strtolower(implode(' ', $row));
                    foreach ($emailKeywords as $kw) {
                        if (strpos($rowText, $kw) !== false) {
                            $hasEmailKeyword = true;
                            break 2;
                        }
                    }
                }

                if ($hasEmailKeyword) {
                    return $this->email($csv_data);
                }

                // Default cho vnx-table với plan names → local_server
                return $this->local_server($csv_data);
            }
        }

        // Fallback: trả về raw CSV
        return $this->raw_csv_to_json($csv_data);
    }

    /**
     * Trả về raw CSV dạng JSON khi không detect được loại
     */
    private function raw_csv_to_json($csv_data)
    {
        $rows = [];

        // Transpose lại từ columns → rows
        if (empty($csv_data))
            return ['name' => '', 'raw_data' => []];

        $numRows = 0;
        foreach ($csv_data as $col) {
            $numRows = max($numRows, count($col));
        }

        for ($r = 0; $r < $numRows; $r++) {
            $row = [];
            foreach ($csv_data as $col) {
                $row[] = $col[$r] ?? '';
            }
            $rows[] = $row;
        }

        return [
            'name' => 'Raw CSV Data',
            'raw_data' => $rows,
        ];
    }

    // ===================================================================
    // Tìm CSV trong Bricks content
    // ===================================================================

    /**
     * Quét tất cả Bricks widgets trong page, trả về mảng [{csv_url, widget_name}]
     */
    private function find_all_csv_in_bricks($page_id)
    {
        $all_templates = get_post_meta($page_id, '_bricks_page_content_2', false);
        if (!$all_templates || !is_array($all_templates))
            return [];

        $results = [];

        // 1. Tìm trực tiếp trong page content
        foreach ($all_templates as $serialized) {
            $data = is_string($serialized) ? @unserialize($serialized) : (is_array($serialized) ? $serialized : null);
            if (!$data || !is_array($data))
                continue;
            $this->extract_csv_from_elements($data, $results);
        }

        // 2. Tìm trong template con
        $template_ids = [];
        foreach ($all_templates as $serialized) {
            $data = is_string($serialized) ? @unserialize($serialized) : (is_array($serialized) ? $serialized : null);
            if (!$data || !is_array($data))
                continue;
            $this->extract_template_ids($data, $template_ids);
        }

        foreach ($template_ids as $template_id) {
            $template_content = get_post_meta($template_id, '_bricks_page_content_2', true);
            if (!$template_content)
                continue;
            $template_data = is_string($template_content) ? @unserialize($template_content) : (is_array($template_content) ? $template_content : null);
            if (!$template_data || !is_array($template_data))
                continue;
            $this->extract_csv_from_elements($template_data, $results);
        }

        return $results;
    }

    /**
     * Đệ quy quét elements, tìm tất cả CSV URL dựa trên widget_csv_keys
     */
    private function extract_csv_from_elements($elements, &$results)
    {
        foreach ($elements as $el) {
            $widget_name = $el['name'] ?? '';

            // Kiểm tra widget có trong danh sách không
            if (isset($this->widget_csv_keys[$widget_name])) {
                $import_key = $this->widget_csv_keys[$widget_name];

                // Repeater: list-service chứa mảng items, mỗi item có import-csv.url
                if ($import_key === 'list-service' && !empty($el['settings']['list-service']) && is_array($el['settings']['list-service'])) {
                    foreach ($el['settings']['list-service'] as $item) {
                        if (!empty($item['import-csv']['url'])) {
                            $results[] = [
                                'csv_url' => $item['import-csv']['url'],
                                'widget_name' => $widget_name,
                            ];
                        }
                    }
                } elseif (!empty($el['settings'][$import_key]['url'])) {
                    $results[] = [
                        'csv_url' => $el['settings'][$import_key]['url'],
                        'widget_name' => $widget_name,
                    ];
                }
            }

            // Ngoài ra, quét tất cả settings tìm bất kỳ URL .csv nào
            if (isset($el['settings']) && is_array($el['settings'])) {
                $this->scan_settings_for_csv($el['settings'], $widget_name, $results);
            }

            // Đệ quy children
            if (!empty($el['children']) && is_array($el['children'])) {
                $this->extract_csv_from_elements($el['children'], $results);
            }
        }
    }

    /**
     * Quét settings tìm bất kỳ URL .csv nào (ngoài widget_csv_keys đã check)
     */
    private function scan_settings_for_csv($settings, $widget_name, &$results)
    {
        foreach ($settings as $key => $value) {
            // Nếu value là mảng có key 'url'
            if (is_array($value) && isset($value['url']) && is_string($value['url'])) {
                if (preg_match('/\.csv$/i', $value['url'])) {
                    // Tránh duplicate
                    $already_exists = false;
                    foreach ($results as $r) {
                        if ($r['csv_url'] === $value['url']) {
                            $already_exists = true;
                            break;
                        }
                    }
                    if (!$already_exists) {
                        $results[] = [
                            'csv_url' => $value['url'],
                            'widget_name' => $widget_name ?: 'unknown',
                        ];
                    }
                }
            }
            // Nếu value là string URL .csv
            if (is_string($value) && preg_match('/^https?:\/\/.*\.csv$/i', $value)) {
                $already_exists = false;
                foreach ($results as $r) {
                    if ($r['csv_url'] === $value) {
                        $already_exists = true;
                        break;
                    }
                }
                if (!$already_exists) {
                    $results[] = [
                        'csv_url' => $value,
                        'widget_name' => $widget_name ?: 'unknown',
                    ];
                }
            }
        }
    }

    /**
     * Lấy template IDs từ elements
     */
    private function extract_template_ids($elements, &$template_ids)
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

    // ===================================================================
    // Đọc CSV file
    // ===================================================================

    private function read_csv_file($csv_url)
    {
        $file = null;

        if (preg_match('/wp-content\/(.*)/', $csv_url, $matches)) {
            $file = ABSPATH . 'wp-content/' . $matches[1];
            if (!file_exists($file)) {
                $upload_dir = wp_upload_dir();
                $file = $upload_dir['basedir'] . '/' . $matches[1];
            }
        } elseif (filter_var($csv_url, FILTER_VALIDATE_URL)) {
            $file = $csv_url;
        }

        if (!$file || (!file_exists($file) && !filter_var($file, FILTER_VALIDATE_URL)))
            return [];

        $handle = @fopen($file, 'r');
        if (!$handle)
            return [];

        $csvData = [];
        while (($line = fgetcsv($handle)) !== false) {
            $csvData[] = $line;
        }
        fclose($handle);

        // Transpose rows → columns
        $transposed = [];
        foreach ($csvData as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $transposed[$colIndex][$rowIndex] = $value;
            }
        }

        return $transposed;
    }

    // ===================================================================
    // Converters (copy từ VNX_Convert_TablePrice, tự chứa)
    // ===================================================================

    private function getRangeIndexTypeDataCSV($data)
    {
        if (!is_array($data))
            return [];
        $boundaries = [];
        $start = null;
        foreach ($data[0] as $index => $item) {
            if (strpos($item, '*') !== false) {
                if ($start !== null)
                    $boundaries[] = [$start, $index];
                $start = $index;
            }
        }
        if ($start !== null)
            $boundaries[] = [$start, count($data)];
        return $boundaries;
    }

    private function getByTypeDataCSV(array $data, string $type, string $column): array
    {
        try {
            $boundaries = $this->getRangeIndexTypeDataCSV($data);
            switch ($type) {
                case 'cycle':
                case 'prices':
                    $start = $boundaries[0][0] + 1;
                    // Lấy tất cả rows giữa boundary[0] và boundary[1]
                    $end = $boundaries[0][1] - $start;
                    break;
                case 'description':
                    $start = $boundaries[1][0] + 1;
                    $end = $boundaries[1][1] - $start;
                    break;
                default:
                    return [];
            }
            if ($end <= 0)
                return [];
            return array_slice($data[$column] ?? [], $start, $end);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function vnx_price_hosting_v1($data): array
    {
        $boundaries = $this->getRangeIndexTypeDataCSV($data);
        if (count($boundaries) < 1)
            return $this->raw_csv_to_json($data);

        // Lấy cycle labels từ column 0
        $cycles = $this->getByTypeDataCSV($data, 'cycle', 0);

        // Tech specs section
        $rangeInfo = isset($boundaries[1]) ? ['start' => $boundaries[1][0] + 1, 'end' => $boundaries[1][1]] : null;

        // Xác định column bắt đầu cho products (bỏ qua label + description columns)
        $startCol = 1;
        if (
            isset($data[1][0]) && (
                strpos(mb_strtolower($data[1][0] ?? ''), 'diễn giải') !== false ||
                strpos(mb_strtolower($data[1][0] ?? ''), 'description') !== false
            )
        ) {
            $startCol = 2;
        }

        $services = [];
        for ($col = $startCol; $col < count($data); $col++) {
            $name = $data[$col][0] ?? '';
            if (empty(trim($name)))
                continue;

            $prices = $this->getByTypeDataCSV($data, 'prices', $col);

            // Parse tất cả cycles
            $pricesArr = [];
            foreach ($prices as $cycleIdx => $price) {
                $p = explode('|', $price);
                $cycleName = isset($cycles[$cycleIdx]) ? trim(explode('|', $cycles[$cycleIdx])[0]) : '';
                $pricesArr[] = [
                    'cycle' => $cycleName,
                    'regular' => trim($p[0] ?? ''),
                    'sale' => trim($p[1] ?? ''),
                    'codeDiscount' => '',
                    'discount' => '',
                ];
            }

            if (empty($pricesArr)) {
                $pricesArr = [['cycle' => '', 'regular' => '', 'sale' => '', 'codeDiscount' => '', 'discount' => '']];
            }

            // Tech specs
            $techSpecs = [];
            if ($rangeInfo) {
                for ($r = $rangeInfo['start']; $r < $rangeInfo['end']; $r++) {
                    $label = trim($data[0][$r] ?? '');
                    $value = trim($data[$col][$r] ?? '');
                    if (!empty($label) && strpos($label, '*') === false) {
                        $valueParts = explode('|', $value);
                        $cleanValue = trim($valueParts[0] ?? '');
                        $techSpecs[] = !empty($cleanValue) ? "$label: $cleanValue" : $label;
                    }
                }
            }

            $services[] = [
                'name' => $name,
                'prices' => $pricesArr,
                'technicalSpecs' => $techSpecs,
            ];
        }

        return ['name' => preg_replace('/[\d\[\]]/', '', $services[0]['name'] ?? ''), 'plans' => $services];
    }

    private function vnx_price_hosting_v2($data): array
    {
        $boundaries = $this->getRangeIndexTypeDataCSV($data);

        // Auto-detect: format Cloud (flat) nếu row đầu là boundary (Tên dịch vụ | *)
        // hoặc nếu có > 3 boundaries
        if (!empty($boundaries) && $boundaries[0][0] === 0) {
            return $this->vnx_price_cloud($data, $boundaries);
        }

        // Format VPS multi-cycle: boundaries[0]=Giá, boundaries[1]=Info, boundaries[2]=URL
        if (count($boundaries) < 2)
            return $this->raw_csv_to_json($data);

        $cycles = $this->getByTypeDataCSV($data, 'cycle', 0);
        $listDescription = $this->getByTypeDataCSV($data, 'description', 1);

        $rangeInfo = isset($boundaries[1]) ? ['start' => $boundaries[1][0] + 1, 'end' => $boundaries[1][1]] : null;
        $rangeURLButton = isset($boundaries[2]) ? ['start' => $boundaries[2][0] + 2, 'end' => $boundaries[2][1]] : null;

        $productNames = [];
        foreach (array_slice($data, 2) as $row) {
            $productNames[] = $row[0] ?? null;
        }

        $services = [];
        foreach ($productNames as $index => $name) {
            $prices = $this->getByTypeDataCSV($data, 'prices', $index + 2);

            $infoArr = [];
            if ($rangeInfo) {
                for ($info = $rangeInfo['start']; $info < $rangeInfo['end']; $info++) {
                    $itemInfo = explode('|', $data[$index + 2][$info] ?? '');
                    $description = isset($listDescription[$info - $rangeInfo['start']]) ? trim($listDescription[$info - $rangeInfo['start']]) : '';
                    $infoArr[] = ['label' => trim($itemInfo[0]), 'extra' => isset($itemInfo[1]) ? trim($itemInfo[1]) : '', 'icon' => trim(end($itemInfo)), 'description' => $description];
                }
            }

            $urls = [];
            if ($rangeURLButton) {
                for ($u = $rangeURLButton['start']; $u <= $rangeURLButton['end']; $u++) {
                    $urls[] = isset($data[$u][$index + 2]) ? $data[$u][$index + 2] : '';
                }
            }

            $pricesArr = [];
            foreach ($prices as $cycleIdx => $price) {
                $p = explode('|', $price);
                $pricesArr[] = [
                    'original' => trim($p[0] ?? ''),
                    'discounted' => trim($p[1] ?? ''),
                    'temporary' => trim($p[2] ?? ''),
                    'discountLabel' => trim($p[3] ?? ''),
                    'discountCode' => isset($p[4]) ? trim(str_replace('/', '|', $p[4])) : '',
                    'code' => trim($p[5] ?? ''),
                    'labelSpecial' => trim($p[6] ?? ''),
                    'cycle' => isset($cycles[$cycleIdx]) ? trim(explode('|', $cycles[$cycleIdx])[0]) : '',
                    'url' => $urls[$cycleIdx] ?? '',
                ];
            }

            $services[] = ['name' => $name, 'prices' => $pricesArr, 'info' => $infoArr, 'urls' => $urls];
        }

        return $this->convertToApiFormat($services);
    }
    private function vnx_price_cloud(array $data, array $boundaries): array
    {
        // Tìm các section dựa trên label
        $sectionMap = [];
        foreach ($boundaries as $idx => $b) {
            $label = mb_strtolower(trim(str_replace('| *', '', $data[0][$b[0]] ?? '')));
            $label = trim(str_replace('|*', '', $label));
            $sectionMap[$label] = $b;
        }

        // Lấy plan names từ row 0 (Tên dịch vụ | *)
        $nameRow = $boundaries[0][0];
        $planNames = [];
        for ($col = 1; $col < count($data); $col++) {
            $planNames[] = trim($data[$col][$nameRow] ?? '');
        }

        // Lấy giá
        $priceRow = $boundaries[1][0] ?? null;
        // Lấy ưu đãi/code
        $discountRow = isset($boundaries[2]) ? $boundaries[2][0] : null;
        $codeRow = isset($boundaries[3]) ? $boundaries[3][0] : null;

        // Tìm section thông tin dịch vụ
        $specStart = null;
        $specEnd = null;
        foreach ($boundaries as $idx => $b) {
            $label = mb_strtolower(trim(str_replace(['| *', '|*'], '', $data[0][$b[0]] ?? '')));
            if (strpos($label, 'thông tin') !== false || strpos($label, 'thong tin') !== false) {
                $specStart = $b[0] + 1;
                // end là boundary tiếp theo hoặc cuối data
                $specEnd = isset($boundaries[$idx + 1]) ? $boundaries[$idx + 1][0] : count($data[0]);
                break;
            }
        }

        $result = ['name' => '', 'plans' => []];
        $groupName = '';

        foreach ($planNames as $colIdx => $name) {
            if (empty($name))
                continue;

            $dataCol = $colIdx + 1;

            // Lấy giá
            $price = $priceRow !== null ? trim($data[$dataCol][$priceRow] ?? '') : '';
            $discount = $discountRow !== null ? trim($data[$dataCol][$discountRow] ?? '') : '';
            $code = $codeRow !== null ? trim($data[$dataCol][$codeRow] ?? '') : '';

            // Lấy technical specs
            $specs = [];
            if ($specStart !== null && $specEnd !== null) {
                for ($r = $specStart; $r < $specEnd; $r++) {
                    $specValue = trim($data[$dataCol][$r] ?? '');
                    if (!empty($specValue)) {
                        $parts = explode('|', $specValue);
                        $label = trim($parts[0] ?? '');
                        $value = trim($parts[1] ?? '');
                        $specs[] = !empty($value) ? "$label: $value" : $label;
                    }
                }
            }

            if (empty($groupName))
                $groupName = trim(preg_replace('/\d+$/', '', $name));

            $result['plans'][] = [
                'name' => $name,
                'prices' => [
                    [
                        'cycle' => '',
                        'regular' => $price,
                        'sale' => '',
                        'codeDiscount' => $code,
                        'discount' => $discount,
                    ]
                ],
                'technicalSpecs' => $specs,
            ];
        }

        $result['name'] = $groupName;
        return $result;
    }

    private function local_server(array $data): array
    {
        $cycles = $data[0];
        $planNames = $data[1];
        $plans = [];
        $numCols = count($cycles);
        $uniquePlanNames = [];
        for ($i = 1; $i < count($planNames); $i++) {
            if ($planNames[$i] && !in_array($planNames[$i], $uniquePlanNames, true))
                $uniquePlanNames[] = $planNames[$i];
        }
        foreach ($uniquePlanNames as $planName) {
            $plan = ['name' => $planName, 'prices' => [], 'technicalSpecs' => []];
            $firstCol = array_search($planName, $planNames, true);
            for ($s = 3; $s <= 6; $s++)
                $plan['technicalSpecs'][] = $data[$s][$firstCol] ?? '';
            for ($col = 1; $col < $numCols; $col++) {
                if ($planNames[$col] === $planName) {
                    $plan['prices'][] = ['cycle' => $cycles[$col], 'regular' => $data[7][$col] ?? '', 'sale' => $data[9][$col] ?? '', 'codeDiscount' => null, 'discount' => ($data[8][$col] ?? '') !== '' ? $data[8][$col] : null];
                }
            }
            if (!empty($plan['prices']))
                $plans[] = $plan;
        }
        return ['name' => trim(preg_replace('/[\d\[\]]/', '', $plans[0]['name'] ?? '')), 'plans' => $plans];
    }

    private function email(array $data): array
    {
        $cycles = $data[0];
        $planNames = $data[1];
        $plans = [];
        $numCols = count($cycles);
        $uniquePlanNames = [];
        for ($i = 1; $i < count($planNames); $i++) {
            if ($planNames[$i] && !in_array($planNames[$i], $uniquePlanNames, true))
                $uniquePlanNames[] = $planNames[$i];
        }
        foreach ($uniquePlanNames as $planName) {
            $plan = ['name' => $planName, 'prices' => [], 'technicalSpecs' => []];
            $firstCol = array_search($planName, $planNames, true);
            foreach ([4, 5, 6, 10, 11, 13] as $specRow) {
                if (isset($data[$specRow][0])) {
                    $label = $data[$specRow][0];
                    if (strpos($label, 'tạm tính') !== false || strpos($label, 'Loại') !== false)
                        continue;
                    $plan['technicalSpecs'][] = $label . ': ' . ($data[$specRow][$firstCol] ?? '');
                }
            }
            for ($col = 1; $col < $numCols; $col++) {
                if ($planNames[$col] === $planName) {
                    $plan['prices'][] = ['cycle' => $cycles[$col], 'regular' => $data[7][$col] ?? '', 'sale' => $data[9][$col] ?? '', 'codeDiscount' => null, 'discount' => $data[8][$col] ?? ''];
                }
            }
            if (!empty($plan['prices']))
                $plans[] = $plan;
        }
        return ['name' => trim(preg_replace('/[\d\[\]]/', '', $plans[0]['name'] ?? '')), 'plans' => $plans];
    }

    private function compare_email(array $data): array
    {
        $header = $data[0];
        $plans = [];
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            $specs = [];
            for ($j = 6; $j < count($header); $j++)
                $specs[] = ($header[$j] ?? '') . ': ' . ($row[$j] ?? '');
            $plans[] = ['name' => $row[0] ?? '', 'prices' => [['cycle' => $row[4] ?? '', 'regular' => $row[1] ?? '', 'sale' => $row[3] ?? '', 'codeDiscount' => null, 'discount' => $row[2] ?? '']], 'technicalSpecs' => $specs];
        }
        return ['name' => trim(preg_replace('/[\d\[\]]/', '', $plans[0]['name'] ?? '')), 'plans' => $plans];
    }

    private function firewall(array $data): array
    {
        $header = $data[0];
        $cycles = [];
        for ($i = 2; $i <= 7; $i++) {
            $parts = explode('|', $header[$i]);
            $cycles[] = ['cycle' => trim($parts[0]), 'discount' => isset($parts[1]) ? trim($parts[1]) : ''];
        }
        $specLabels = [];
        for ($i = 9; $i <= 14; $i++)
            $specLabels[] = $header[$i];

        $plans = [];
        for ($row = 2; $row < count($data); $row++) {
            $r = $data[$row];
            $prices = [];
            for ($i = 0; $i < count($cycles); $i++) {
                $pp = explode('|', $r[2 + $i] ?? '');
                $prices[] = ['cycle' => $cycles[$i]['cycle'], 'regular' => trim($pp[0] ?? ''), 'sale' => trim($pp[1] ?? ''), 'codeDiscount' => '', 'discount' => $cycles[$i]['discount']];
            }
            $specs = [];
            for ($i = 0; $i < count($specLabels); $i++) {
                $specs[] = preg_replace('/\s*\|.*/', '', $specLabels[$i]) . ': ' . preg_replace('/\s*\|.*/', '', $r[9 + $i] ?? '');
            }
            $plans[] = ['name' => $r[0], 'prices' => $prices, 'technicalSpecs' => $specs];
        }
        return ['name' => trim(preg_replace('/[\d\[\]]/', '', $plans[0]['name'] ?? '')), 'plans' => $plans];
    }

    private function domain(array $data): array
    {
        $columns = [];
        foreach ($data as $row) {
            foreach ($row as $ci => $v) {
                $columns[$ci][] = $v;
            }
        }
        $plans = [];
        for ($i = 1; $i < count($columns); $i++) {
            $c = $columns[$i];
            $plans[] = [
                'tld' => $c[0] ?? '',
                'type' => $c[15] ?? '',
                'tooltip' => $c[1] ?? '',
                'status' => isset($c[2]) ? explode(',', $c[2])[0] : '',
                'gift' => $c[3] ?? '',
                'register_price' => $c[4] ?? '',
                'register_sale_price' => $c[5] ?? '',
                'register_fee' => $c[6] ?? '',
                'maintain_fee' => $c[7] ?? '',
                'admin_service_first' => $c[8] ?? '',
                'vat_first' => $c[9] ?? '',
                'renew_price' => $c[10] ?? '',
                'renew_maintain_fee' => $c[11] ?? '',
                'admin_service_next' => $c[12] ?? '',
                'vat_next' => $c[13] ?? '',
                'transfer_fee' => $c[14] ?? '',
                'description' => $c[1] ?? '',
            ];
        }
        return ['name' => 'Tên miền Việt Nam', 'plans' => $plans];
    }

    // ===================================================================
    // Helpers
    // ===================================================================

    private function convertToApiFormat(array $services): array
    {
        $name = '';
        $result = ['name' => '', 'plans' => []];
        foreach ($services as $idx => $service) {
            if ($idx === 0)
                $name = trim(preg_replace('/[\d\[\]]/', '', $service['name']));
            $plan = ['name' => $service['name'], 'prices' => [], 'technicalSpecs' => array_map(fn($i) => $i['label'], $service['info'])];
            foreach ($service['prices'] as $price) {
                $discount = '';
                if (!empty($price['discountLabel']))
                    $discount = preg_replace('/[^0-9%]/', '', $price['discountLabel']);
                elseif (!empty($price['discountCode']))
                    $discount = preg_replace('/[^0-9%]/', '', $price['discountCode']);
                $plan['prices'][] = ['cycle' => $price['cycle'], 'regular' => $price['original'], 'sale' => $price['discounted'], 'codeDiscount' => $price['code'], 'discount' => $discount];
            }
            $result['plans'][] = $plan;
        }
        $result['name'] = $name;
        return $result;
    }

    private function get_post_by_url($url)
    {
        $home_url = home_url('/');
        $path = trim(str_replace($home_url, '', $url), '/');

        // Trang chủ: path rỗng → lấy static front page
        if (empty($path) || $path === trim($url, '/')) {
            $front_page_id = get_option('page_on_front');
            if ($front_page_id) {
                return get_post($front_page_id);
            }
            // Nếu không có static page, thử url_to_postid
            $post_id = url_to_postid($url);
            if ($post_id)
                return get_post($post_id);
            return null;
        }

        return get_page_by_path($path, OBJECT, 'page')
            ?: get_page_by_path($path, OBJECT, 'post')
            ?: get_page_by_path($path, OBJECT);
    }

    private function parse_urls($urls_raw)
    {
        if (empty($urls_raw))
            return [];
        $urls = [];
        foreach (explode("\n", $urls_raw) as $line) {
            $line = trim($line);
            if (!empty($line) && filter_var($line, FILTER_VALIDATE_URL))
                $urls[] = $line;
        }
        return $urls;
    }
}

new VNX_API_Price_Table_Center();