<?php

namespace ToolsCenter\VNX_Search_Post_AI;



class VNX_GetCsvWidgetBricks
{
    private $option_name = 'vnx_get_csv_widgetbricks';
    private $listSelectedFiles = [
        [
            'name' => 'Bảng giá vps/hosting',
            'widget_name' => 'vnx-service-price-v2',
            'import_key' => 'import-csv'
        ],
        [
            'name' => 'Bảng giá so sánh vps/hosting/firewall',
            'widget_name' => 'vnx-service-price',
            'import_key' => 'upload'
        ],
        [
            'name' => 'Bảng giá firewall',
            'widget_name' => 'vnx-service-price',
            'import_key' => 'upload'
        ],
        [
            'name' => 'Bảng giá thuê máy chủ',
            'widget_name' => 'vnx-table',
            'import_key' => 'upload'
        ],
        [
            'name' => 'Bảng giá Email',
            'widget_name' => 'vnx-table',
            'import_key' => 'upload'
        ],
        [
            'name' => 'Bảng giá so sánh Email',
            'widget_name' => 'vnx-table',
            'import_key' => 'upload'
        ],
        [
            'name' => 'Email doanh nghiệp',
            'widget_name' => 'vnx-table',
            'import_key' => 'upload'
        ],
          [
            'name' => 'Bảng giá tên miền',
            'widget_name' => 'vnx-table',
            'import_key' => 'upload'
        ],
        [
            'name' => 'Lấy nội dung từ Bricks',
            'widget_name' => null,
            'import_key' => null
        ]
    ];

    public function __construct()
    {

        $this->settings = get_option($this->option_name);
        add_action('wp_ajax_vnx_get_csv_widgetbricks_get_settings_center', [$this, 'get_settings']);
        add_action('wp_ajax_vnx_csv_widgetbricks_save_settings_center', [$this, 'save_settings']);
    }

    /**
     * Lấy option đã lưu
     */
    public function get_settings()
    {
        try {
            $option = get_option($this->option_name);
            if (!$option) {
                $option = [
                    'prompt_system' => '',
                    'token' => '',
                    'models' => 'gpt-4.1',
                    'list_url' => []
                ];
                update_option($this->option_name, $option);
            }
            $option['listSelectedFiles'] = array_map(function ($item) {
                return $item['name'] ?? '';
            }, $this->listSelectedFiles);

            wp_send_json_success($option);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Lưu option từ form (POST)
     */
    public function save_settings()
    {
        try {
            $option = get_option($this->option_name);
            $option['prompt_system'] = isset($_POST['prompt_system']) ? stripslashes($_POST['prompt_system']) : '';
            $option['token'] = $_POST['token'] ?? '';
            $option['models'] = $_POST['models'] ?? '';

            if (isset($_POST['list_url'])) {
                $list_url = $_POST['list_url'];
                if (is_string($list_url)) {
                    $list_url = stripslashes($list_url);
                    $decoded = json_decode($list_url, true);
                    $list_url = is_array($decoded) ? $decoded : [];
                }
                if (is_array($list_url)) {
                    $option['list_url'] = array_map(function ($item) {
                        // Nếu chỉ có name, tra cứu widget_name và import_key từ listSelectedFiles
                        $name = $item['name'] ?? null;
                        $widget_name = $item['widget_name'] ?? null;
                        $import_key = $item['import_key'] ?? null;
                        if ($name && (!$widget_name || !$import_key)) {
                            $found = null;
                            foreach ($this->listSelectedFiles as $row) {
                                if ($row['name'] === $name) {
                                    $found = $row;
                                    break;
                                }
                            }
                            if ($found) {
                                $widget_name = $found['widget_name'];
                                $import_key = $found['import_key'];
                            }
                        }
                        return [
                            'name' => $name,
                            'url' => $item['url'] ?? '',
                            'widget_name' => $widget_name,
                            'import_key' => $import_key,
                        ];
                    }, $list_url);
                } else {
                    $option['list_url'] = [];
                }
            } else {
                $option['list_url'] = [];
            }

            update_option($this->option_name, $option);
            $this->settings = $option;
            wp_send_json_success(['message' => 'Settings saved']);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
