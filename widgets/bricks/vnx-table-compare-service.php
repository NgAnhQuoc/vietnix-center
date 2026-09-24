<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Table_Compare_Service_Center extends \Bricks\Element
{

  public $category = 'vietnix';
  public $name = 'vnx-table-compare-service';
  public $icon = 'ion-md-cash';

  public function get_label()
  {
    return esc_html__('VNX Table Compare Service', 'bricks');
  }
  public function set_control_groups()
  {
    $this->control_groups['group-config'] = [
      'title' => esc_html__('Service', 'vietnix'),
      'tab' => 'content',
    ];
    $this->control_groups['group-extra-config'] = [
      'title' => esc_html__('Extra Service', 'vietnix'),
      'tab' => 'content',
    ];
  }

  public function enqueue_scripts()
  {
    wp_enqueue_script('bricks-splide');
    wp_enqueue_style('bricks-splide');
    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_register_script('vnx-table-compare-service-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/table_handle/vnx-table-compare.js', ['jquery'], random_int(111, 9999), true);
    wp_enqueue_script('vnx-table-compare-service-center');
  }
  public function set_controls()
  {
    $this->controls['version'] = [
      'tab' => 'content',
      'label' => esc_html__('Version', 'vietnix'),
      'type' => 'select',
      'group' => 'group-config',
      'options' => [
        'table-compare-service' => esc_html__('Table Compare Service', 'vietnix'),
        'table-compare-service-hosting' => esc_html__('Table Compare Service Hosting', 'vietnix'),
      ],
    ];

    $this->controls['import-csv'] = [
      'tab' => 'content',
      'group' => 'group-config',
      'label' => esc_html__('Import CSV', 'vietnix'),
      'type' => 'file',
      'description' => 'Please select the file format is .csv',
    ];

    $this->controls['sticky-header'] = [
      'tab' => 'content',
      'group' => 'group-config',
      'label' => esc_html__('Sticky Header', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('65px', 'vietnix'),
      'description' => esc_html__('The distance from the top of the page to the sticky header', 'vietnix'),
      'default' => '65px',
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['background-image-header'] = [
      'tab' => 'content',
      'group' => 'group-config',
      'label' => esc_html__('Background Image Header', 'vietnix'),
      'type' => 'image',
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['item-popular'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Item Popular', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Item popular', 'vietnix'),
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['img-popular'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Image Popular', 'vietnix'),
      'type' => 'image',
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['item-special'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Item Special', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Item special', 'vietnix'),
      'required' => ['version', '=', ['table-compare-service']],
    ];

    $this->controls['item-textbutton'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Item Text Button', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Item text button', 'vietnix'),
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['item-icon-yes'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Item Icon Yes', 'vietnix'),
      'type' => 'icon',
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['item-icon-yes-color'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Item Icon Yes Color', 'vietnix'),
      'type' => 'color',
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['item-icon-no'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Item Icon No', 'vietnix'),
      'type' => 'icon',
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['item-icon-no-color'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Item Icon No Color', 'vietnix'),
      'type' => 'color',
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['button-chat'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Button Chat', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Button chat', 'vietnix'),
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['img-chat'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Image Chat', 'vietnix'),
      'type' => 'image',
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['class-chat'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Class Button Chat', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Class button chat', 'vietnix'),
      'required' => ['version', '=', ['table-compare-service', 'table-compare-service-hosting']],
    ];

    $this->controls['item-tooltip'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Item Tooltip', 'vietnix'),
      'type' => 'image',
      'placeholder' => esc_html__('Item tooltip', 'vietnix'),
      'required' => ['version', '=', ['table-compare-service-hosting']],
    ];

    $this->controls['category-text'] = [
      'tab' => 'content',
      'group' => 'group-extra-config',
      'label' => esc_html__('Category Text', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('Category text', 'vietnix'),
      'required' => ['version', '=', ['table-compare-service-hosting']],
    ];

  }


  public function render()
  {
    $settings = $this->settings;
    View::render("widgets/bricks/vnx-table-compare-service", $this);
  }

  //function xử lý data từ file csv
  public function get_data_from_csv()
  {
    $settings = $this->settings;
    $file_data = $settings['import-csv'];
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

  //function xử lý data từ file csv chuyển data thành các cột
  public function transform_csv_to_columns($csv_data)
  {
    $result = [];
    for ($i = 0; $i < count($csv_data[0]); $i++) {
      $row = [];
      for ($j = 0; $j < count($csv_data); $j++) {
        $row[] = $csv_data[$j][$i];
      }
      $result[] = $row;
    }
    return ['status' => 'success', 'data' => $result];
  }

  //function xử lý data từ các cột cho table compare service
  public function transform_columns_to_data($columns_data)
  {
    if (empty($columns_data)) {
      return ['status' => 'success', 'data' => []];
    }
    
    $first_row = $columns_data[0];
    $first_marker_position = null;
    
    foreach ($first_row as $index => $value) {
      if (is_string($value) && strpos($value, '| *') !== false) {
        $first_marker_position = $index;
        break;
      }
    }
    
    if ($first_marker_position === null) {
      return ['status' => 'success', 'data' => $columns_data];
    }
    
    $result = [];
    
    foreach ($columns_data as $row) {
      $before_marker = [];
      $after_marker = [];
      
      for ($i = 0; $i < $first_marker_position; $i++) {
        if (isset($row[$i])) {
          $before_marker[] = $row[$i];
        }
      }
      
      for ($i = $first_marker_position; $i < count($row); $i++) {
        if (isset($row[$i])) {
          $value = $row[$i];
          if (!(is_string($value) && strpos($value, '| *') !== false)) {
            $after_marker[] = $value;
          }
        }
      }
      
      $result[] = [$before_marker, $after_marker];
    }
    
    return ['status' => 'success', 'data' => $result];
  }

  //function phân tích value của gói
  public function parse_package_value($value)
  {
    $result = [];
    $settings = $this->settings;
    $parts = explode('|', $value);
    $result['value'] = trim($parts[0]);
    $result['unit'] = trim($parts[1]);
    if($result['value'] == 'image'){
      $result['unit'] = '<img src="' . $parts[1] . '" alt="' . $result['value'] . '">';
    }else{
     if($result['value'] == 'no'){
      $result['unit'] = !empty($result['unit']) ? $result['unit'] : '<i class="' . $settings['item-icon-no']['icon'] . '" style="color: ' . $settings['item-icon-no-color']['hex'] . '"></i>';
     }else if($result['value'] == 'yes'){
      $result['unit'] = !empty($result['unit']) ? $result['unit'] : '<i class="' . $settings['item-icon-yes']['icon'] . '" style="color: ' . $settings['item-icon-yes-color']['hex'] . '"></i>';
     }elseif($result['value'] == 'text'){
      $result['unit'] = !empty($result['unit']) ? $result['unit'] : $result['value'];
     }
    }
    return $result;
  }


}