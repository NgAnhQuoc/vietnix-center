<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Tabs_Layout_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-tabs-layout';
  public $icon = 'fa-solid fa-layer-group';

  public function get_label()
  {
    return esc_html__('VNX Tabs Layout', 'vietnix');
  }
  public function enqueue_scripts()
  {

    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_register_script('vnx-tabs-layout-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx-tabs-layout.js', ['jquery'], random_int(111, 9999), true);
    wp_enqueue_script('vnx-tabs-layout-center');
  }

  public function set_control_groups()
  {
    $this->control_groups['import-csv'] = [
      'title' => esc_html__('Import CSV', 'vietnix'),
      'tab' => 'content',
      'required' => ['version', '=', ['tab-layout-content']],
    ];
  }

  public function set_controls()
  {

    $this->controls['version'] = [
      'tab' => 'content',
      'label' => esc_html__('Version', 'vietnix'),
      'type' => 'select',
      'options' => [
        'tab-layout-content' => esc_html__('Tab Layout Content', 'vietnix'),
      ],
    ];

    $this->controls['import-csv'] = [
      'tab' => 'content',
      'label' => esc_html__('Import CSV', 'vietnix'),
      'type' => 'file',
      'multiple' => false,
      'allowed_types' => ['csv'],
      'required' => ['version', '=', ['tab-layout-content']],
      'group' => 'import-csv',
    ];

    $this->controls['icon_tooltip'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon Tooltip', 'vietnix'),
      'type' => 'icon',
      'default' => 'fa-solid fa-question-circle',
      'required' => ['version', '=', ['tab-layout-content']],
      'group' => 'import-csv',
    ];

    $this->controls['icon_dropdown'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon Dropdown', 'vietnix'),
      'type' => 'icon',
      'default' => 'fa-solid fa-chevron-down',
      'required' => ['version', '=', ['tab-layout-content']],
      'group' => 'import-csv',
    ];
    
  }

  public function render()
  {
    View::render("widgets/bricks/vnx-tabs-layout", $this);
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

  public function transform_csv_to_tabs($csv_data)
  {
    $result = [];
    
    foreach ($csv_data as $row) {
      if (empty($row[0])) {
        continue;
      }

      $tab_info = explode('|', $row[0]);
      $tab_name = isset($tab_info[0]) ? trim($tab_info[0]) : '';
      $background_image = isset($tab_info[1]) ? trim($tab_info[1]) : '';

      $items = [];
      if (isset($row[1]) && !empty($row[1])) {
        $items_string = $row[1];
        
        preg_match_all('/\[(.*?)\]/', $items_string, $matches);
        
        if (!empty($matches[1])) {
          foreach ($matches[1] as $item_string) {
            $item_parts = array_map('trim', explode('|', $item_string));
            
            if (count($item_parts) >= 3) {
              $items[] = [
                'icon' => $item_parts[0],
                'title' => $item_parts[1],
                'description' => $item_parts[2],
              ];
            }
          }
        }
      }

      $result[] = [
        'tab_name' => $tab_name,
        'background_image' => $background_image,
        'items' => $items,
      ];
    }
    
    return $result;
  }

}