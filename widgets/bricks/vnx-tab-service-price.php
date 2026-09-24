<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Tab_Service_Price_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-tab-service-price';
  public $icon = 'ion-md-cash';

  public function get_label()
  {
    return esc_html__('VNX Tab Service Price', 'vietnix');
  }
  public function enqueue_scripts()
  {
    wp_enqueue_script('bricks-splide');
    wp_enqueue_style('bricks-splide');
    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_register_script('vnx-tab-service-price-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx-tab-service-price.js', ['jquery'], random_int(111, 9999), true);
    wp_enqueue_script('vnx-tab-service-price-center');
  }

  public function set_control_groups()
  {
    $this->control_groups['import-csv'] = [
      'title' => esc_html__('Import CSV', 'vietnix'),
      'tab' => 'content',
      'required' => ['version', '=', ['tab-service-price', 'tab-service-price-vps', 'tab-service-price-group', 'tab-service-price-all-group', 'tab-service-price-object-storage']],
    ];
  }

  public function set_controls()
  {

    $this->controls['version'] = [
      'tab' => 'content',
      'label' => esc_html__('Version', 'vietnix'),
      'type' => 'select',
      'options' => [
        'tab-service-price' => esc_html__('Tab Service Price', 'vietnix'),
        'tab-service-price-vps' => esc_html__('Tab Service Price VPS', 'vietnix'),
        'tab-service-price-group' => esc_html__('Tab Service Price Group', 'vietnix'),
        'tab-service-price-all-group' => esc_html__('Tab Service Price All Group', 'vietnix'),
        'tab-service-price-object-storage' => esc_html__('Tab Service Price Object Storage', 'vietnix'),
      ],
    ];

    $this->controls['show_only'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Show Only One Service List', 'vietnix'),
      'type' => 'checkbox',
      'default' => esc_html__(false, 'vietnix'),
      'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group', 'tab-service-price-object-storage']],
    ];

    $this->controls['list-service'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('List Service', 'vietnix'),
      'type' => 'repeater',
      'titleProperty' => 'title',
      'default' => [
        [
          'title' => 'Service 1',
          'import-csv' => '',
        ],
      ],
      'placeholder' => esc_html__('Service name placeholder', 'vietnix'),
      'required' => ['version', '=', ['tab-service-price', 'tab-service-price-vps', 'tab-service-price-group', 'tab-service-price-all-group', 'tab-service-price-object-storage']],
      'fields' => [
        'title' => [
          'label' => esc_html__('Title', 'vietnix'),
          'type' => 'text',
        ],
        'import-csv' => [
          'label' => esc_html__('Data File', 'vietnix'),
          'type' => 'file',
          'description' => 'Please select the file format is .csv',
        ],
        'period' => [
          'label' => esc_html__('Period', 'vietnix'),
          'type' => 'text',
          'placeholder' => esc_html__('Thời gian', 'vietnix'),
        ],
        'period_tag' => [
          'label' => esc_html__('Period Tag', 'vietnix'),
          'type' => 'image',
          'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group', 'tab-service-price-object-storage']],
        ],
        'category' => [
          'label' => esc_html__('Category', 'vietnix'),
          'type' => 'text',
          'placeholder' => esc_html__('Danh mục sản phẩm', 'vietnix'),
        ],
        'cycle' => [
          'label' => esc_html__('Cycle', 'vietnix'),
          'type' => 'text',
          'placeholder' => esc_html__('Chu kỳ', 'vietnix'),
          'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group', 'tab-service-price-object-storage']],
        ],
        'item_popular' => [
          'label' => esc_html__('Item Popular', 'vietnix'),
          'type' => 'text',
          'placeholder' => esc_html__('Item popular', 'vietnix'),
          'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group', 'tab-service-price-object-storage']],
        ],
        'image_popular' => [
          'label' => esc_html__('Image Popular', 'vietnix'),
          'type' => 'image',
          'required' => ['version', '=', ['tab-service-price-object-storage']],
        ],
        'item_special' => [
          'label' => esc_html__('Item Special', 'vietnix'),
          'type' => 'text',
          'placeholder' => esc_html__('Item special', 'vietnix'),
          'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group']],
        ],
        'unit_price' => [
          'label' => esc_html__('Unit Price', 'vietnix'),
          'type' => 'text',
          'placeholder' => esc_html__('Đơn vị tính', 'vietnix'),
          'required' => ['version', '=', ['tab-service-price-object-storage']],
        ],
      ],
    ];

    $this->controls['icon_img'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Icon Image', 'vietnix'),
      'type' => 'image',
      'required' => ['version', '=', ['tab-service-price', 'tab-service-price-vps']],
    ];

    $this->controls['text_button'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Text Button', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Mua ngay', 'vietnix'),
    ];

    $this->controls['default_service'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Gói mặc định', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Gói mặc định', 'vietnix'),
      'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group', 'tab-service-price-object-storage']],
    ];

    $this->controls['icon_copy'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Icon Copy', 'vietnix'),
      'type' => 'image',
      'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group']],
    ];

    $this->controls['icon_paste'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Icon Paste', 'vietnix'),
      'type' => 'image',
      'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group']],
    ];

    $this->controls['icon_tooltip'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Icon Tooltip', 'vietnix'),
      'type' => 'image',
      'required' => ['version', '=', ['tab-service-price-group']],
    ];

    $this->controls['icon_special'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Icon Special', 'vietnix'),
      'type' => 'image',
      'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group']],
    ];

    $this->controls['turn_on_icon_popular'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Turn On Icon Popular', 'vietnix'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => esc_html__(false, 'vietnix'),
      'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group']],
    ];

    $this->controls['icon_popular_background'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Background Color Popular', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Background color', 'vietnix'),
      'required' => ['version', '=', ['tab-service-price-group', 'tab-service-price-all-group']],
    ];

    $this->controls['icon_text_popular'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Icon Text Popular', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Text popular', 'vietnix'),
      'required' => ['turn_on_icon_popular', '=', false, 'version', '=', ['tab-service-price-group', 'tab-service-price-all-group']],
    ];

    $this->controls['icon_popular_icon'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Icon Popular Icon', 'vietnix'),
      'type' => 'image',
      'required' => ['turn_on_icon_popular', '=', true, 'version', '=', ['tab-service-price-group', 'tab-service-price-all-group']],
    ];

    $this->controls['icon_tooltip_feature'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Icon Tooltip Feature', 'vietnix'),
      'type' => 'icon',
      'default'  => [
        'library' => 'fontawesomeRegular',
        // fontawesome/ionicons/themify
        'icon'    => 'fa fa-question-circle',
        // Example: Themify icon class
      ],
      'css'      => [
        [
          'selector' => '.vnx_tooltip_icon',
          // Use to target SVG file
        ],
      ],
      'required' => ['version', '=', ['tab-service-price-object-storage']],
    ];
  }
  public function render()
  {
    $settings = $this->settings;
    View::render("widgets/bricks/vnx-tab-service-price", $this);
  }
  public function get_upload_file_data()
  {
    $file = $this->settings['import-csv'];
    if ($file) {
      return $file;
    }
    return null;
  }

  public function read_csv_file($file_data)
  {
    if (!isset($file_data['url']) || empty($file_data['url'])) {
      return [
        'status' => 'error',
        'message' => 'File URL not found',
      ];
    }

    $url = $file_data['url'];
    $file_path = '';

    $regex = '/wp-content\/(.*)/';
    if (preg_match($regex, $url, $matches)) {
      $file_path = ABSPATH . $matches[0];
    } else {
      return [
        'status' => 'error',
        'message' => 'Invalid file URL format',
      ];
    }

    if (!file_exists($file_path)) {
      return [
        'status' => 'error',
        'message' => 'CSV file not found at: ' . $file_path,
      ];
    }

    $handle = fopen($file_path, 'r');
    if (!$handle) {
      return [
        'status' => 'error',
        'message' => 'Failed to open CSV file',
      ];
    }

    $csv_data = [];
    while (($line = fgetcsv($handle)) !== false) {
      $csv_data[] = $line;
    }
    fclose($handle);

    return [
      'status' => 'success',
      'data' => $csv_data,
    ];
  }

  public function transform_csv_to_packages($csv_result)
  {
    if ($csv_result['status'] !== 'success' || empty($csv_result['data'])) {
      return [
        'status' => 'error',
        'message' => 'Invalid CSV data',
      ];
    }

    $csv_data = $csv_result['data'];
    if (empty($csv_data) || empty($csv_data[0])) {
      return [
        'status' => 'error',
        'message' => 'CSV data is empty',
      ];
    }

    $headers = $csv_data[0];
    $packages = [];

    $group_boundaries = [];
    $current_group_index = null;
    $current_group_start = null;
    $group_counter = 0;

    for ($row_index = 1; $row_index < count($csv_data); $row_index++) {
      $field_name = isset($csv_data[$row_index][0]) ? trim($csv_data[$row_index][0]) : '';

      if (strpos($field_name, ' | *') !== false || strpos($field_name, '| *') !== false) {
        if ($current_group_index !== null && $current_group_start !== null) {
          $group_boundaries[] = [
            'index' => $current_group_index,
            'start' => $current_group_start,
            'end' => $row_index,
          ];
        }

        $group_counter++;
        $current_group_index = $group_counter;
        $current_group_start = $row_index + 1;
      }
    }

    if ($current_group_index !== null && $current_group_start !== null) {
      $group_boundaries[] = [
        'index' => $current_group_index,
        'start' => $current_group_start,
        'end' => count($csv_data),
      ];
    }

    for ($col = 1; $col < count($headers); $col++) {
      $package = [];
      $grouped_data = [];

      foreach ($csv_data as $row_index => $row) {
        if ($row_index === 0) {
          $package['name'] = isset($row[$col]) ? trim($row[$col]) : '';
        } else {
          $field_name = isset($row[0]) ? trim($row[0]) : '';
          $field_value = isset($row[$col]) ? trim($row[$col]) : '';

          if (!empty($field_name)) {
            $is_group_header = strpos($field_name, ' | *') !== false || strpos($field_name, '| *') !== false;

            if ($is_group_header) {
              continue;
            }

            $is_in_group = false;

            foreach ($group_boundaries as $boundary) {
              if ($row_index >= $boundary['start'] && $row_index < $boundary['end']) {
                $group_key = 'group_' . $boundary['index'];
                if (!isset($grouped_data[$group_key])) {
                  $grouped_data[$group_key] = [];
                }

                $parsed = $this->parse_feature_with_icon($field_value);
                if (!empty($parsed['text']) || !empty($parsed['icon'])) {
                  $grouped_data[$group_key][] = [
                    'label' => $field_name,
                    'icon' => $parsed['icon'],
                    'value' => $parsed['text'],
                  ];
                }
                $is_in_group = true;
                break;
              }
            }

            if (!$is_in_group) {
              $package[$field_name] = $field_value;
            }
          }
        }
      }

      if (!empty($grouped_data)) {
        $package['groups'] = $grouped_data;
      }

      if (!empty($package['name'])) {
        $packages[] = $package;
      }
    }

    return [
      'status' => 'success',
      'packages' => $packages,
    ];
  }

  public function parse_price($price_string)
  {
    if (empty($price_string)) {
      return [
        'original' => '',
        'discounted' => '',
        'unit' => '',
      ];
    }

    $parts = array_map('trim', explode('|', $price_string));
    return [
      'original' => isset($parts[0]) ? $parts[0] : '',
      'discounted' => isset($parts[1]) ? $parts[1] : '',
      'unit' => isset($parts[2]) ? $parts[2] : '',
    ];
  }

  public function parse_feature_value($value_string)
  {
    if (empty($value_string)) {
      return '';
    }

    $parts = array_map('trim', explode('|', $value_string));
    return !empty($parts[1]) ? $parts[1] : (!empty($parts[0]) ? $parts[0] : '');
  }

  public function parse_feature_with_icon($value_string)
  {
    if (empty($value_string)) {
      return [
        'icon' => '',
        'text' => '',
      ];
    }

    $parts = array_map('trim', explode('|', $value_string));
    return [
      'icon' => !empty($parts[0]) ? $parts[0] : '',
      'text' => !empty($parts[1]) ? $parts[1] : (!empty($parts[0]) ? $parts[0] : ''),
    ];
  }

  public function transform_csv_to_services($csv_result)
  {
    try {
      if ($csv_result['status'] !== 'success' || empty($csv_result['data'])) {
        return [
          'status' => 'error',
          'message' => 'Invalid CSV data',
        ];
      }

      $csv_data = $csv_result['data'];
      if (empty($csv_data) || empty($csv_data[0])) {
        return [
          'status' => 'error',
          'message' => 'CSV data is empty',
        ];
      }

      $num_rows = count($csv_data);
      $num_cols = count($csv_data[0]);
      $services = [];
      $cycle_data = '';

      // Chuyển đổi từ dạng hàng sang dạng cột
      for ($col = 0; $col < $num_cols; $col++) {
        $column_data = [];
        $current_group_index = null;
        $current_group_data = [];
        // $group_counter = 0;

        for ($row = 0; $row < $num_rows; $row++) {
          $row_label = isset($csv_data[$row][0]) ? trim($csv_data[$row][0]) : '';
          $row_value = isset($csv_data[$row][$col]) ? trim($csv_data[$row][$col]) : '';

          // Kiểm tra nếu đây là header của một nhóm (có chứa | *)
          if (strpos($row_label, '| *') !== false || strpos($row_label, '|*') !== false) {
            // Lưu nhóm trước đó nếu có
            if ($current_group_index !== null && !empty($current_group_data)) {
              $column_data[$current_group_index] = array_values($current_group_data);
            }

            // Bắt đầu nhóm mới với numeric key
            $current_group_index = count($column_data);
            $current_group_data = [];
          } else {
            // Nếu đang trong một nhóm, thêm vào mảng con
            if ($current_group_index !== null) {
              $current_group_data[] = $row_value;
            } else {
              // Nếu không thuộc nhóm nào, thêm trực tiếp
              $column_data[] = $row_value;
            }
          }
        }

        // Lưu nhóm cuối cùng nếu có
        if ($current_group_index !== null && !empty($current_group_data)) {
          $column_data[$current_group_index] = array_values($current_group_data);
        }
        $services[] = $column_data;
      }
      $cycle_data = $services[0][1];

      return [
        'status' => 'success',
        'services' => $services,
        'cycle_data' => $cycle_data,
      ];
    } catch (Exception $e) {
      error_log($e->getMessage());
      return [
        'status' => 'error',
        'message' => $e->getMessage(),
      ];
    }
  }

  public function transform_csv_to_objectstorage($csv_result)
  {
    try {
      if ($csv_result['status'] !== 'success' || empty($csv_result['data'])) {
        return [
          'status' => 'error',
          'message' => 'Invalid CSV data',
        ];
      }

      $csv_data = $csv_result['data'];
      if (empty($csv_data) || empty($csv_data[0])) {
        return [
          'status' => 'error',
          'message' => 'CSV data is empty',
        ];
      }

      $num_rows = count($csv_data);
      $num_cols = count($csv_data[0]);
      $services = [];
      $cycle_data = '';

      // Chuyển đổi từ dạng hàng sang dạng cột
      for ($col = 0; $col < $num_cols; $col++) {
        $column_data = [];
        $current_group_index = null;
        $current_group_data = [];
        // $group_counter = 0;

        for ($row = 0; $row < $num_rows; $row++) {
          $row_label = isset($csv_data[$row][0]) ? trim($csv_data[$row][0]) : '';
          $row_value = isset($csv_data[$row][$col]) ? trim($csv_data[$row][$col]) : '';

          // Kiểm tra nếu đây là header của một nhóm (có chứa | *)
          if (strpos($row_label, '| *') !== false || strpos($row_label, '|*') !== false) {
            // Lưu nhóm trước đó nếu có
            if ($current_group_index !== null && !empty($current_group_data)) {
              $column_data[$current_group_index] = array_values($current_group_data);
            }

            // Bắt đầu nhóm mới với numeric key
            $current_group_index = count($column_data);
            $current_group_data = [];
          } else {
            // Nếu đang trong một nhóm, thêm vào mảng con
            if ($current_group_index !== null) {
              $current_group_data[] = $row_value;
            } else {
              // Nếu không thuộc nhóm nào, thêm trực tiếp
              $column_data[] = $row_value;
            }
          }
        }

        // Lưu nhóm cuối cùng nếu có
        if ($current_group_index !== null && !empty($current_group_data)) {
          $column_data[$current_group_index] = array_values($current_group_data);
        }
        $services[] = $column_data;
      }
      $cycle_data = $services[0][4];

      return [
        'status' => 'success',
        'services' => $services,
        'cycle_data' => $cycle_data,
      ];
    } catch (Exception $e) {
      error_log($e->getMessage());
      return [
        'status' => 'error',
        'message' => $e->getMessage(),
      ];
    }
  }
}