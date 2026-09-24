<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Service_Price_V2_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-service-price-v2';
  public $icon = 'ion-md-cash';
  public $css_selector = '.vnx-service-price-v2';

  public function get_label()
  {
    return esc_html__('VNX Service Price V2', 'vietnix');
  }

  public function set_control_groups()
  {
    $this->control_groups['import-csv'] = [
      'title' => esc_html__('Import CSV', 'vietnix'),
      'tab' => 'content',
    ];

    $this->control_groups['popular'] = [
      'title' => esc_html__('Popular', 'bricks'),
      'tab' => 'content',
      'required' => ['version', '=', ['vnx-price_hosting_v2', 'vnx-price-have-compare', 'vnx-price_ssl', 'vnx-price-email']],
    ];

    $this->control_groups['vnx-tooltip-icon-price'] = [
      'title' => esc_html__('Tooltip Price Icon', 'vietnix'),
      'tab' => 'content',
      'required' => ['version', '=', ['vnx-price_hosting_v2', 'vnx-price-have-compare', 'vnx-price-email']],
    ];

    $this->control_groups['yes_icon'] = [
      'title' => esc_html__('Yes Icon', 'vietnix'),
      'tab' => 'content',
      'required' => ['version', '=', ['vnx-price_hosting_v2', 'vnx-price-have-compare', 'vnx-price_scroll', 'vnx-price_ssl', 'vnx-price-email']],
    ];

    $this->control_groups['no_icon'] = [
      'title' => esc_html__('No Icon', 'vietnix'),
      'tab' => 'content',
      'required' => ['version', '=', ['vnx-price_hosting_v2', 'vnx-price-have-compare', 'vnx-price_scroll', 'vnx-price_ssl', 'vnx-price-email']],
    ];

    $this->control_groups['custom_icon'] = [
      'title' => esc_html__('Custom Icon', 'vietnix'),
      'tab' => 'content',
      'required' => ['version', '=', ['vnx-price_hosting_v2', 'vnx-price-have-compare', 'vnx-price_scroll', 'vnx-price_ssl', 'vnx-price-email']],
    ];

    $this->control_groups['tooltip_icon'] = [
      'title' => esc_html__('Tooltip Icon', 'vietnix'),
      'tab' => 'content',
      'required' => ['version', '=', ['vnx-price_hosting_v2', 'vnx-price-have-compare', 'vnx-price_ssl', 'vnx-price-have-range', 'vnx-price-email']],
    ];

    $this->control_groups['copy_icon'] = [
      'title' => esc_html__('Copy Icon', 'vietnix'),
      'tab' => 'content',
      'required' => ['version', '=', ['vnx-price_hosting_v2', 'vnx-price-have-compare', 'vnx-price_scroll', 'vnx-price_ssl', 'vnx-price-email']],
    ];

    $this->control_groups['circle_settings'] = [
      'title' => esc_html__('Circle Settings', 'vietnix'),
      'tab' => 'content',
      'required' => ['version', '=', ['vnx-price_hosting_v2', 'vnx-price-have-compare', 'vnx-price_ssl', 'vnx-price-email']],
    ];
  }

  public function set_controls()
  {
    $this->controls['version'] = [
      'tab' => 'content',
      'label' => esc_html__('Version', 'vietnix'),
      'type' => 'select',
      'options' => [
        'vnx-price_hosting_v2' => esc_html__('Price Hosting V2', 'vietnix'),
        'vnx-price_scroll' => esc_html__('Price Scroll', 'vietnix'),
        'vnx-price_ssl' => esc_html__('Price SSL', 'vietnix'),
        'vnx-price-have-compare' => esc_html__('Price Have Compare', 'vietnix'),
        'vnx-price-have-range' => esc_html__('Price Have Range', 'vietnix'),
        'vnx-price-email' => esc_html__('Price Email', 'vietnix'),
      ],
    ];

    $this->controls['import-csv'] = [
      'tab' => 'content',
      'group' => 'import-csv',
      'label' => esc_html__('Data File', 'vietnix'),
      'description' => 'Please select the file format is .csv',
      'type' => 'file',
    ];

    $this->controls['cycle_popular'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Index Cycle Popular', 'bricks'),
      'type' => 'number',
      'default' => esc_html__(0, 'bricks'),
    ];

    $this->controls['service_popular'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Index Service Popular', 'bricks'),
      'type' => 'number',
      'default' => esc_html__(-1, 'bricks'),
    ];

    $this->controls['show_service_popular'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Show Index Service Popular', 'bricks'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => esc_html__(false, 'vietnix'),
    ];
    $this->controls['cycle_lable'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Cycle popular Lable', 'vietnix'),
      'type' => 'image',
    ];

    $this->controls['cycle_lable_mb'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Cycle popular Lable mobile', 'vietnix'),
      'type' => 'image',
    ];

    $this->controls['highlight_lable'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Highlight Lable', 'vietnix'),
      'type' => 'image',
    ];
    $this->controls['highlight_lable_bg'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Highlight Lable Background', 'vietnix'),
      'type' => 'text',
    ];

    $this->controls['item_special'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Item Special', 'vietnix'),
      'type' => 'number',
      'default' => esc_html__(0, 'vietnix'),
      'required' => ['version', '=', 'vnx-price-email'],
    ];

    $this->controls['special_lable_bg'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Special Lable Background', 'vietnix'),
      'type' => 'text',
      'required' => ['version', '=', 'vnx-price-email'],
    ];

    $this->controls['special_text'] = [
      'tab' => 'content',
      'group' => 'popular',
      'label' => esc_html__('Special Text', 'vietnix'),
      'type' => 'text',
      'required' => ['version', '=', 'vnx-price-email'],
    ];


    $this->controls['vnx-tooltip-icon-price'] = [
      'tab' => 'content',
      'group' => 'vnx-tooltip-icon-price',
      'label' => esc_html__('Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        'icon' => 'fas fa-circle-check',
      ],
    ];
    $this->controls['vnx-tooltip-icon-price-hover'] = [
      'tab' => 'content',
      'group' => 'vnx-tooltip-icon-price',
      'label' => esc_html__('Icon hover', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        'icon' => 'fas fa-circle-check',
      ],
    ];

    $this->controls['yes_icon'] = [
      'tab' => 'content',
      'group' => 'yes_icon',
      'label' => esc_html__('Yes Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        'icon' => 'fas fa-circle-check',
      ],
    ];

    $this->controls['icon_yes_color'] = [
      'tab' => 'content',
      'label' => esc_html__('Color', 'vietnix'),
      'type' => 'color',
      'group' => 'yes_icon',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx-warp-item-price  .vnx-yes-icon',
          'important' => true,
        ],
      ]
    ];

    $this->controls['icon_yes_colorhv'] = [
      'tab' => 'content',
      'label' => esc_html__('Hover color', 'vietnix'),
      'type' => 'color',
      'group' => 'yes_icon',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx-warp-item-price :hover 
          .vnx-yes-icon',
          'important' => true,
        ],
      ]
    ];



    $this->controls['no_icon'] = [
      'tab' => 'content',
      'group' => 'no_icon',
      'label' => esc_html__('No Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        'icon' => 'fas fa-circle-xmark',
      ],
    ];

    $this->controls['icon_no_color'] = [
      'tab' => 'content',
      'label' => esc_html__('Color', 'vietnix'),
      'type' => 'color',
      'group' => 'no_icon',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx-warp-item-price  .vnx-no-icon',
        ],
      ]
    ];

    $this->controls['icon_no_colorhv'] = [
      'tab' => 'content',
      'label' => esc_html__('Hover color', 'vietnix'),
      'type' => 'color',
      'group' => 'no_icon',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx-warp-item-price :hover .vnx-no-icon',
          'important' => true,
        ],
      ]
    ];

    $this->controls['icon_ct_color'] = [
      'tab' => 'content',
      'label' => esc_html__('Color', 'vietnix'),
      'type' => 'color',
      'group' => 'custom_icon',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx-warp-item-price  .vnx-custom-icon, .vnx-text-info2',
          // 'important' => true,
        ],
      ]
    ];

    $this->controls['icon_ct_colorhv'] = [
      'tab' => 'content',
      'label' => esc_html__('Hover Color', 'vietnix'),
      'type' => 'color',
      'group' => 'custom_icon',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '
          .vnx-warp-item-price :hover .vnx-custom-icon, 
          .vnx-warp-item-price :hover .vnx-text-info2',
          'important' => true,
        ],
      ]
    ];

    $this->controls['tooltip_icon'] = [
      'tab' => 'content',
      'group' => 'tooltip_icon',
      'label' => esc_html__('Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        'icon' => 'fas fa-info-circle',
      ],
    ];

    $this->controls['copy_icon'] = [
      'tab' => 'content',
      'group' => 'copy_icon',
      'label' => esc_html__('Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        'icon' => 'fas fa-info-circle',
      ],
    ];

    $this->controls['paste_icon'] = [
      'tab' => 'content',
      'group' => 'copy_icon',
      'label' => esc_html__('Icon paste', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        'icon' => 'fas fa-info-circle',
      ],
    ];

    $this->controls['circle_class_prefix'] = [
      'tab' => 'content',
      'group' => 'circle_settings',
      'label' => esc_html__('Circle Tab Class Prefix', 'vietnix'),
      'type' => 'text',
      'description' => esc_html__('Example: vnx-circle-tab-', 'vietnix'),
      'default' => '',
    ];

    $this->controls['circle_class_suffix'] = [
      'tab' => 'content',
      'group' => 'circle_settings',
      'label' => esc_html__('Circle Tab Class Suffix', 'vietnix'),
      'type' => 'text',
      'description' => esc_html__('Example: -your-suffix', 'vietnix'),
      'default' => '',
    ];

    $this->controls['text_description'] = [
      'tab' => 'content',
      'label' => esc_html__('Text Description', 'vietnix'),
      'type' => 'text',
      'default' => 'Thông tin sản phẩm',
      'required' => ['version', '=', ['vnx-price-have-range']],
    ];

    $this->controls['category_button_register'] = [
      'tab' => 'content',
      'label' => esc_html__('Category Button', 'vietnix'),
      'type' => 'text',
      'default' => '',
      'required' => ['version', '=', ['vnx-price-have-range']],
    ];

    $this->controls['button_register_text'] = [
      'tab' => 'content',
      'label' => esc_html__('Button Register Text', 'vietnix'),
      'type' => 'text',
      'default' => 'ĐĂNG KÝ NGAY',
      'required' => ['version', '=', ['vnx-price-have-range']],
    ];

    $this->controls['button_register_unit'] = [
      'tab' => 'content',
      'label' => esc_html__('Button Register Unit', 'vietnix'),
      'type' => 'text',
      'required' => ['version', '=', ['vnx-price-have-range']],
    ];
  }

  public function enqueue_scripts()
  {
    wp_enqueue_script('bricks-splide');
    wp_enqueue_style('bricks-splide');
    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_enqueue_script('vnx-service-price-v2-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx_service_price_v2.js', ['jquery'], random_int(111, 9999), true);
  }

  public function render()
  {
    $settings = $this->settings;

    $table_style = isset($settings['version']) ? $settings['version'] : '';


    if ($table_style) {
      $filePath = "widgets/bricks/vnx-service/" . $table_style;

      View::render($filePath, $this);
    }
  }

  private function validateSettings(): bool
  {
    return isset($this->settings['import-csv']);
  }

  private function getFilePath(): ?string
  {
    $link = $this->settings['import-csv']['url'];
    if (preg_match('/wp-content\/(.*)/', $link, $matches)) {
      return ABSPATH . $matches[0];
    }
    return null;
  }

  private function readCsvFile(string $file): array
  {
    $handle = fopen($file, "r");
    if (!$handle) {
      return [
        'success' => false,
        'message' => 'File open failed'
      ];
    }

    $csvData = [];
    while (($line = fgetcsv($handle)) !== false) {
      $csvData[] = $line;
    }
    fclose($handle);

    return [
      'success' => true,
      'data' => $csvData
    ];
  }

  private function sendError(string $message): void
  {
    echo $message;
  }


  public function geFileDataUpload()
  {
    try {

      if (!$this->validateSettings()) {
        return $this->sendError('You have no file uploaded');
      }

      $file = $this->getFilePath();
      if (!$file) {

        return $this->sendError("Can't replace domain form File URL", [
          'settings_url' => $this->settings['import-csv']['url'],
          'file_path' => ''
        ]);
      }

      if (!file_exists($file)) {
        return $this->sendError('CSV File not found');
      }

      $csvData = $this->readCsvFile($file);
      if (!$csvData['success']) {
        return $this->sendError('readCsvFile failed');
      }


      // chuyển đổi mảng thành dạng hàng ngang
      $result = array();
      for ($i = 0; $i < count($csvData['data'][0]); $i++) {
        $row = array();
        for ($j = 0; $j < count($csvData['data']); $j++) {
          $row[] = $csvData['data'][$j][$i];
        }
        $result[] = $row;
      }


      return [
        'status' => $csvData['success'],
        'data' => $result
      ];
    } catch (Exception $e) {

      echo "Error: " . $e->getMessage();
    }
  }

  public function getRangeIndexTypeDataCSV()
  {

    $res = $this->geFileDataUpload();
    $array = $res['data'][0];


    if (is_array($array)) {
      $boundaries = array();
      $start = null;
      foreach ($array as $index => $item) {
        if (strpos($item, '*') !== false) {
          if ($start !== null) {
            $boundaries[] = array($start, $index);
          }
          $start = $index;
        }
      }
      if ($start !== null) {
        $boundaries[] = array($start, count($array));
      }


      // exam: [[1,6],[6,19],[19,24]] 
      // khoảng 1: Chu kì
      // Khoảng 2: Thông tin kỹ thuật
      // Khoảng 3: URL đăng ký
      return $boundaries;
    }
  }

  public function transform_columns_to_packages($csv_result)
  {
    if ($csv_result['status'] !== true || empty($csv_result['data'])) {
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

    $field_names = $csv_data[0];
    $packages = [];

    $row_boundaries = [];
    $current_group_index = null;
    $current_group_start = null;
    $group_counter = 0;

    for ($field_index = 0; $field_index < count($field_names); $field_index++) {
      $field_name = trim($field_names[$field_index]);

      if (strpos($field_name, ' | *') !== false || strpos($field_name, '| *') !== false) {
        if ($current_group_index !== null && $current_group_start !== null) {
          $row_boundaries[] = [
            'index' => $current_group_index,
            'start' => $current_group_start,
            'end' => $field_index,
          ];
        }

        $group_counter++;
        $current_group_index = $group_counter;
        $current_group_start = $field_index + 1;
      }
    }

    if ($current_group_index !== null && $current_group_start !== null) {
      $row_boundaries[] = [
        'index' => $current_group_index,
        'start' => $current_group_start,
        'end' => count($field_names),
      ];
    }

    for ($col = 0; $col < count($csv_data); $col++) {
      $package = [];
      $grouped_data = [];

      for ($field_index = 0; $field_index < count($field_names); $field_index++) {
        $field_name = trim($field_names[$field_index]);
        $field_value = isset($csv_data[$col][$field_index]) ? trim($csv_data[$col][$field_index]) : '';

        if ($field_index === 0) {
          $package['name'] = $field_value;
        } else if ($field_index === 1) {
          $package['image'] = $field_value;
        } else if ($field_index === 2) {
          $package['range'] = $field_value;
        } else {
          $is_group_header = strpos($field_name, ' | *') !== false || strpos($field_name, '| *') !== false;

          if ($is_group_header) {
            continue;
          }

          $is_in_group = false;

          foreach ($row_boundaries as $boundary) {
            if ($field_index >= $boundary['start'] && $field_index < $boundary['end']) {
              $group_key = 'group_' . $boundary['index'];
              if (!isset($grouped_data[$group_key])) {
                $grouped_data[$group_key] = [];
              }

              if (!empty($field_value)) {
                $grouped_data[$group_key][] = [
                  'label' => $field_name,
                  'value' => $field_value,
                ];
              }
              $is_in_group = true;
              break;
            }
          }

          if (!$is_in_group && !empty($field_name)) {
            $package[$field_name] = $field_value;
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

  public function circle_text_to_key($text)
  {
    $text = trim($text);
    if (!$text)
      return $text;
    $unicode = array(
      'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
      'd' => 'đ',
      'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
      'i' => 'í|ì|ỉ|ĩ|ị',
      'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
      'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
      'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
      'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
      'D' => 'Đ',
      'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
      'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
      'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
      'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
      'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
    );

    foreach ($unicode as $nonUnicode => $uni) {
      $text = preg_replace("/($uni)/i", $nonUnicode, $text);
    }
    $text = str_replace(' ', '_', $text);
    $text = strtolower($text);
    return $text;
  }

  public function getByTypeDataCSV(string $type, string $column)
  {
    try {

      $boundaries = $this->getRangeIndexTypeDataCSV();

      // điểm bắt đầu
      $start = 0;

      // điểm kết thúc
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
          throw new Exception("Invalid type: $type");
      }


      $dataCSV = $this->geFileDataUpload();
      $dataCSV = $dataCSV['data'][$column];


      $subset = array_slice($dataCSV, $start, $end);

      return $subset;
    } catch (Exception $e) {
      error_log("Error in getByTypeDataCSV: " . $e->getMessage());
      return null;
    }
  }

  public function showIcon_Center($nameIcon, $class)
  {

    $settingIcon = $this->settings[$nameIcon];
    if (!empty($settingIcon)) {
      if ($settingIcon['library'] == 'svg') {
        echo '<img src="' . trim($settingIcon['svg']['url']) . '" alt="icon tooltip">';
      } else {
        echo '<i aria-hidden="true" class="' . $class . ' ' . $settingIcon['icon'] . '"></i>';
      }
    }
  }
}
