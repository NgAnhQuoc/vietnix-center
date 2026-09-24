<?php

namespace ToolsCenter\VNX_Search_Post_AI;


class VNX_Convert_TablePrice
{
    public  function getRangeIndexTypeDataCSV($data)
    {



        if (is_array($data)) {
            $boundaries = array();
            $start = null;

            foreach ($data[0] as $index => $item) {

                if (strpos($item, '*') !== false) {
                    if ($start !== null) {
                        $boundaries[] = array($start, $index);
                    }
                    $start = $index;
                }
            }
            if ($start !== null) {
                $boundaries[] = array($start, count($data));
            }


            // exam: [[1,6],[6,19],[19,24]] 
            // khoảng 1: Chu kì
            // Khoảng 2: Thông tin kỹ thuật
            // Khoảng 3: URL đăng ký
            return $boundaries;
        }
    }

    public function getByTypeDataCSV(array $data, string $type, string $column): array
    {
        try {
            $boundaries = $this->getRangeIndexTypeDataCSV($data);


            $start = 0;
            $end = 0;
            switch ($type) {
                case 'cycle':
                    $start = $boundaries[0][0] + 1;
                    $end = $boundaries[0][1] - 3;
                    break;
                case 'description':
                    $start = $boundaries[1][0] + 1;
                    $end = $boundaries[1][1] - 1;
                    break;
                case 'prices':
                    $start = $boundaries[0][0] + 1;
                    $end = $boundaries[0][1] - 3;
                    break;
                default:
                    throw new \Exception("Invalid type: $type");
            }
            $dataCSV = $data[$column];
            $subset = array_slice($dataCSV, $start, $end);


            return $subset;
        } catch (\Exception $e) {
            error_log("Error in getByTypeDataCSV: " . $e->getMessage());
            return [];
        }
    }




    public function vnx_price_hosting_v1($data): array
    {
        $boundaries = $this->getRangeIndexTypeDataCSV($data);

        // Range cho thông tin sản phẩm
        $rangeInfo = [
            'start' => $boundaries[1][0] + 1,
            'end' => $boundaries[1][1]
        ];;

        $productNames = [];

        foreach (array_slice($data, 1) as $row) {
            $productNames[] = $row[0] ?? null; // dùng null để tránh lỗi nếu $row rỗng
        }

        $labelInfo = array_slice($data[0], $rangeInfo['start'], count($data[0]));

        $services = [];
        foreach ($productNames as $index => $name) {
            $prices = $this->getByTypeDataCSV($data, 'prices', $index + 1);
            $price = $prices[0] ?? '';
            $columnProduct = $index + 1;
            $columnProduct = $data[$columnProduct] ?? [];
            $columnProduct = array_slice($columnProduct, $rangeInfo['start'], count($data[0]));
            $indexTrim = 0;
            foreach ($labelInfo as $index => $item) {


                if ($item === 'Chú thích | *') {
                    $indexTrim = $index;
                    break;
                }
                // nếu item có tồn tại * thì bỏ qua
                if (strpos($item, '*') !== false) {
                    $columnProduct[$index] = '';
                    continue;
                }
                $columnProduct[$index] =   $item . ' ' .  $columnProduct[$index];
            }
            $columnProduct = array_slice($columnProduct, 0, $indexTrim + 1);
            // bỏ qua tất cả phần tử rỗng
            $columnProduct = array_filter($columnProduct, function ($value) {
                return !empty($value);
            });


            $services[] = [
                'name' => $name,
                'prices' => [
                    [
                        'cycle' => '1 tháng',
                        'regular' => '',
                        'sale' => $price,
                        'codeDiscount' => '',
                        'discount' => ''
                    ]
                ],
                'technicalSpecs' => array_values($columnProduct)
            ];
        }

        $result = [
            'name' => preg_replace('/[\d\[\]]/', '', $services[0]['name']),
            'plans' => $services
        ];

        return $result;
    }




    public function vnx_price_hosting_v2($data): array
    {



        $boundaries = $this->getRangeIndexTypeDataCSV($data);

        $cycles = $this->getByTypeDataCSV($data, 'cycle', 0);



        $listDescription = $this->getByTypeDataCSV($data, 'description', 1);



        // Range cho thông tin sản phẩm
        $rangeInfo = [
            'start' => $boundaries[1][0] + 1,
            'end' => $boundaries[1][1]
        ];

        // Range cho URL đăng ký
        $rangeURLButton = [
            'start' => $boundaries[2][0] + 2,
            'end' => $boundaries[2][1]
        ];
        // Chu kỳ
        $cyclesArr = [];
        foreach ($cycles as $index => $item) {
            $parts = explode("|", $item);
            $main = trim($parts[0]);
            $extra = isset($parts[1]) ? trim($parts[1]) : "";
            $cyclesArr[] = [
                'main' => $main,
                'extra' => $extra,
            ];
        }



        $productNames = [];

        foreach (array_slice($data, 2) as $row) {
            $productNames[] = $row[0] ?? null; // dùng null để tránh lỗi nếu $row rỗng
        }



        $services = [];
        foreach ($productNames as $index => $name) {
            $prices = $this->getByTypeDataCSV($data, 'prices', $index + 2);

            // Thông tin sản phẩm
            $infoArr = [];
            for ($info = $rangeInfo['start']; $info < $rangeInfo['end']; $info++) {
                $itemInfo = explode("|", $data[$index + 2][$info]);


                $infoIcon = trim(end($itemInfo));
                $description = isset($listDescription[$info - $rangeInfo['start']]) ? trim($listDescription[$info - $rangeInfo['start']]) : '';
                $infoArr[] = [
                    'label' => trim($itemInfo[0]),
                    'extra' => isset($itemInfo[1]) ? trim($itemInfo[1]) : '',
                    'icon' => $infoIcon,
                    'description' => $description
                ];
            }


            // URLs
            $urls = [];
            for ($u = $rangeURLButton['start']; $u <= $rangeURLButton['end']; $u++) {
                $urls[] = isset($dataCSV[$u][$index + 2]) ? $dataCSV[$u][$index + 2] : '';
            }
            // Giá từng chu kỳ
            $pricesArr = [];
            foreach ($prices as $cycleIdx => $price) {
                $priceParts = explode('|', $price);
                $pricesArr[] = [
                    'original' => isset($priceParts[0]) ? trim($priceParts[0]) : '',
                    'discounted' => isset($priceParts[1]) ? trim($priceParts[1]) : '',
                    'temporary' => isset($priceParts[2]) ? trim($priceParts[2]) : '',
                    'discountLabel' => isset($priceParts[3]) ? trim($priceParts[3]) : '',
                    'discountCode' => isset($priceParts[4]) ? trim(str_replace('/', '|', $priceParts[4])) : '',
                    'code' => isset($priceParts[5]) ? trim($priceParts[5]) : '',
                    'labelSpecial' => isset($priceParts[6]) ? trim($priceParts[6]) : '',
                    'cycle' => isset($cycles[$cycleIdx]) ? trim(explode('|', $cycles[$cycleIdx])[0]) : '',
                    'url' => isset($urls[$cycleIdx]) ? $urls[$cycleIdx] : ''
                ];
            }
            $services[] = [
                'name' => $name,
                'prices' => $pricesArr,
                'info' => $infoArr,
                'urls' => $urls
            ];
        }

        $result = $this->convertVnxTablePriceToApiFormat([
            'services' => $services,
            'cycles' => $cyclesArr
        ], 0, '', '');


        return $result;
    }



    function convertVnxTablePriceToApiFormat(array $inputData): array
    {


        $name = '';
        $result = [
            'name' => '',
            'plans' => [] // Mảng chứa các gói dịch vụ
        ];
        foreach ($inputData['services'] as $idx => $service) {

            if ($idx === 0) {
                // Loại bỏ các ký tự số và dấu [] khỏi tên
                $name = preg_replace('/[\d\[\]]/', '', $service['name']);
                $name = trim($name);
            }

            $plan = [
                'name' => $service['name'],
                'prices' => [],
                'technicalSpecs' => array_map(function ($info) {
                    return $info['label'];
                }, $service['info']),
            ];


            foreach ($service['prices'] as $price) {

                // Lấy discount từ discountCode hoặc discountLabel, chỉ lấy số và %
                $discount = '';
                if (!empty($price['discountLabel'])) {
                    $discount = preg_replace('/[^0-9%]/', '', $price['discountLabel']);
                } elseif (!empty($price['discountCode'])) {
                    $discount = preg_replace('/[^0-9%]/', '', $price['discountCode']);
                }
                $plan['prices'][] = [
                    'cycle' => $price['cycle'],
                    'regular' => $price['original'],
                    'sale' => $price['discounted'],
                    'codeDiscount' => $price['code'],
                    'discount' => $discount,
                ];
            }
            $result['plans'][] = $plan;
        }
        $result['name'] = $name;
        return $result;
    }

    /**
     * Convert a table (array of arrays) to the required plans format (unique plan names, each plan has all cycles)
     *
     * @param array $data
     * @return array
     */
    public function local_sever(array $data): array
    {
        $cycles = $data[0];
        $planNames = $data[1];
        $specStart = 3;
        $specEnd = 6;
        $priceRow = 7;
        $discountRow = 8;
        $salePriceRow = 9;
        $urlRow = 11;
        $plans = [];
        $numCols = count($cycles);
        // Lấy danh sách tên gói duy nhất, giữ thứ tự xuất hiện
        $uniquePlanNames = [];
        for ($i = 1; $i < count($planNames); $i++) {
            $name = $planNames[$i];
            if ($name && !in_array($name, $uniquePlanNames, true)) {
                $uniquePlanNames[] = $name;
            }
        }
        // Với mỗi tên gói, gom tất cả các chu kỳ và giá trị liên quan
        foreach ($uniquePlanNames as $planName) {
            $plan = [
                'name' => $planName,
                'prices' => [],
                'technicalSpecs' => []
            ];
            // Lấy thông số kỹ thuật (lấy theo cột đầu tiên có tên gói này)
            $firstCol = array_search($planName, $planNames, true);
            for ($spec = $specStart; $spec <= $specEnd; $spec++) {
                $plan['technicalSpecs'][] = $data[$spec][$firstCol];
            }
            // Gom tất cả các chu kỳ cho gói này
            for ($col = 1; $col < $numCols; $col++) {
                if ($planNames[$col] === $planName) {
                    $cycle = $cycles[$col];
                    $regular = $data[$priceRow][$col];
                    $sale = $data[$salePriceRow][$col];
                    $discount = $data[$discountRow][$col] !== '' ? $data[$discountRow][$col] : null;
                    $plan['prices'][] = [
                        'cycle' => $cycle,
                        'regular' => $regular,
                        'sale' => $sale,
                        'registrationUrls' => '',
                        'codeDiscount' => null,
                        'discount' => $discount
                    ];
                }
            }
            if (!empty($plan['prices'])) {
                $plans[] = $plan;
            }
        }
        $groupName = isset($plans[0]['name']) ? preg_replace('/[\d\[\]]/', '', $plans[0]['name']) : '';
        return [
            'name' => trim($groupName),
            'plans' => $plans
        ];
    }

      /**
     * Convert a table (array of arrays) to the required plans format (unique plan names, each plan has all cycles)
     *
     * @param array $data
     * @return array
     */
    public function email(array $data): array
    {
        // $data is a 2D array, columns: [0]=cycle, [1]=planName, [7]=price, [8]=discount, [9]=sale, [12]=url, ...
        $cycles = $data[0]; // Chu kỳ
        $planNames = $data[1]; // Gói dịch vụ
        $priceRow = 7; // Giá
        $discountRow = 8; // Giảm
        $salePriceRow = 9; // Giá sau giảm
        $urlRow = 12; // URL
        $specRows = [4, 5, 6, 10, 11, 13]; // Các dòng thông số kỹ thuật (có thể điều chỉnh nếu cần)
        $plans = [];
        $numCols = count($cycles);
        // Lấy danh sách tên gói duy nhất, giữ thứ tự xuất hiện
        $uniquePlanNames = [];
        for ($i = 1; $i < count($planNames); $i++) {
            $name = $planNames[$i];
            if ($name && !in_array($name, $uniquePlanNames, true)) {
                $uniquePlanNames[] = $name;
            }
        }
        // Với mỗi tên gói, gom tất cả các chu kỳ và giá trị liên quan
        foreach ($uniquePlanNames as $planName) {
            $plan = [
                'name' => $planName,
                'prices' => [],
                'technicalSpecs' => []
            ];
            // Lấy cột đầu tiên có tên gói này
            $firstCol = array_search($planName, $planNames, true);
            // Lấy thông số kỹ thuật
            foreach ($specRows as $specRow) {
                if (isset($data[$specRow][0])) {
                    $label = $data[$specRow][0];
                    if (strpos($label, 'tạm tính') !== false) {
                        continue;
                    }
                    if (strpos($label, 'Loại') !== false) {
                        continue;
                    }
                    $value = isset($data[$specRow][$firstCol]) ? $data[$specRow][$firstCol] : '';
                    $plan['technicalSpecs'][] = $label . ': ' . $value;
                }
            }
            // Gom tất cả các chu kỳ cho gói này
            for ($col = 1; $col < $numCols; $col++) {
                if ($planNames[$col] === $planName) {
                    $cycle = $cycles[$col];
                    $regular = $data[$priceRow][$col];
                    $discount = $data[$discountRow][$col];
                    $sale = $data[$salePriceRow][$col];
                    $registrationUrl = $data[$urlRow][$col];
                    $plan['prices'][] = [
                        'cycle' => $cycle,
                        'regular' => $regular,
                        'sale' => $sale,
                        'codeDiscount' => null,
                        'discount' => $discount
                    ];
                }
            }
            if (!empty($plan['prices'])) {
                $plans[] = $plan;
            }
        }
        $groupName = isset($plans[0]['name']) ? preg_replace('/[\d\[\]]/', '', $plans[0]['name']) : '';
        return [
            'name' => trim($groupName),
            'plans' => $plans
        ];
    }

    /**
     * Convert a table (array of arrays) to the required plans format (unique plan names, each plan has all cycles)
     *
     * @param array $data
     * @return array
     */
    public function compare_email(array $data): array
    {
        // Header row
        $header = $data[0];
        $plans = [];
        // Each plan is a row from index 1
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            $planName = $row[0];
            $regular = $row[1];
            $discount = $row[2];
            $sale = $row[3];
            $cycle = $row[4];
            $registrationUrl = $row[5];
            // Technical specs: from col 6 to end
            $technicalSpecs = [];
            for ($j = 6; $j < count($header); $j++) {
                $label = $header[$j];
                $value = isset($row[$j]) ? $row[$j] : '';
                $technicalSpecs[] = $label . ': ' . $value;
            }
            $plans[] = [
                'name' => $planName,
                'prices' => [
                    [
                        'cycle' => $cycle,
                        'regular' => $regular,
                        'sale' => $sale,
                        'registrationUrls' => $registrationUrl,
                        'codeDiscount' => null,
                        'discount' => $discount
                    ]
                ],
                'technicalSpecs' => $technicalSpecs
            ];
        }
        $groupName = isset($plans[0]['name']) ? preg_replace('/[\d\[\]]/', '', $plans[0]['name']) : '';
        return [
            'name' => trim($groupName),
            'plans' => $plans
        ];
    }

    /**
     * Convert a table (array of arrays) to the required plans format (unique plan names, each plan has all cycles)
     *
     * @param array $data
     * @return array
     */
    public function firewall(array $data): array
    {
        // Extract cycles and discounts from header
        $header = $data[0];
        $cycleStart = 2;
        $cycleEnd = 7;
        $cycles = [];
        for ($i = $cycleStart; $i <= $cycleEnd; $i++) {
            // Example: "1 Tháng | ", "6 Tháng | -5%"
            $parts = explode('|', $header[$i]);
            $cycle = trim($parts[0]);
            $discount = isset($parts[1]) ? trim($parts[1]) : '';
            $cycles[] = [
                'cycle' => $cycle,
                'discount' => $discount
            ];
        }

        // Extract technical spec labels
        $specStart = 9;
        $specEnd = 14;
        $specLabels = [];
        for ($i = $specStart; $i <= $specEnd; $i++) {
            $specLabels[] = $header[$i];
        }

        // For each plan (row 2+)
        $plans = [];
        for ($row = 2; $row < count($data); $row++) {
            $planRow = $data[$row];
            $planName = $planRow[0];
            // Prices for each cycle
            $prices = [];
            for ($i = 0; $i < count($cycles); $i++) {
                $priceCell = $planRow[$cycleStart + $i];
                $priceParts = explode('|', $priceCell);
                $regular = isset($priceParts[0]) ? trim($priceParts[0]) : '';
                $sale = isset($priceParts[1]) ? trim($priceParts[1]) : '';
                // Registration URL
                $urlCol = 16 + $i;
                $prices[] = [
                    'cycle' => $cycles[$i]['cycle'],
                    'regular' => $regular,
                    'sale' => $sale,
                    'codeDiscount' => '',
                    'discount' => $cycles[$i]['discount'],
                ];
            }
            // Technical specs
            $technicalSpecs = [];
            for ($i = 0; $i < count($specLabels); $i++) {
                $label = preg_replace('/\s*\|.*/', '', $specLabels[$i]); // Remove trailing | *
                $value = isset($planRow[$specStart + $i]) ? preg_replace('/\s*\|.*/', '', $planRow[$specStart + $i]) : '';
                $technicalSpecs[] = $label . ': ' . $value;
            }
            $plans[] = [
                'name' => $planName,
                'prices' => $prices,
                'technicalSpecs' => $technicalSpecs
            ];
        }
        $groupName = isset($plans[0]['name']) ? preg_replace('/[\d\[\]]/', '', $plans[0]['name']) : '';
        return [
            'name' => trim($groupName),
            'plans' => $plans
        ];
    }

     /**
     * Convert a table (array of arrays) to the required plans format (unique plan names, each plan has all cycles)
     *
     * @param array $data
     * @return array
     */
    public function doamin(array $data): array
    {
        // Chuyển mảng dọc sang ngang (columns)
        $columns = [];
        foreach ($data as $row) {
            foreach ($row as $colIdx => $value) {
                if (!isset($columns[$colIdx])) {
                    $columns[$colIdx] = [];
                }
                $columns[$colIdx][] = $value;
            }
        }

        // Lấy header
        $header = $columns[0];
        $plans = [];
        for ($i = 1; $i < count($columns); $i++) {
            $col = $columns[$i];
            $plans[] = [
                'tld' => $col[0] ?? '',
                'type' => $col[15] ?? '',
                'tooltip' => $col[1] ?? '',
                'status' => isset($col[2]) ? explode(',', $col[2])[0] : '',
                'gift' => $col[3] ?? '',
                'register_price' => $col[4] ?? '',
                'register_sale_price' => $col[5] ?? '',
                'register_fee' => $col[6] ?? '',
                'maintain_fee' => $col[7] ?? '',
                'admin_service_first' => $col[8] ?? '',
                'vat_first' => $col[9] ?? '',
                'renew_price' => $col[10] ?? '',
                'renew_maintain_fee' => $col[11] ?? '',
                'admin_service_next' => $col[12] ?? '',
                'vat_next' => $col[13] ?? '',
                'transfer_fee' => $col[14] ?? '',
                'description' => $col[1] ?? '', // Tooltip là mô tả nếu có
            ];
        }
        return [
            'name' => 'Tên miền Việt Nam',
            'plans' => $plans
        ];
    }
}
