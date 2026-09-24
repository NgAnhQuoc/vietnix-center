<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Slide_Domain_V1_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-slide-domain-v1';
  public $icon = 'ion-md-cash';

  public function get_label()
  {
    return esc_html__('VNX Slide Domain V1', 'vietnix');
  }

  public function enqueue_scripts()
  {
    wp_enqueue_script('bricks-splide');
    wp_enqueue_style('bricks-splide');
    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_register_script('vnx-slide-domain-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx_slide_domain.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vnx-slide-domain-center');

  }

  public function set_control_groups()
  {
    $this->control_groups['slide_control'] = [
      'title' => esc_html__('Slide Control', 'vietnix'),
      'tab' => 'content',
    ];
    $this->control_groups['link_control'] = [
      'title' => esc_html__('Link Control', 'vietnix'),
      'tab' => 'content',
    ];
  }

  public function set_controls()
  {
    $this->controls['import_csv'] = [
      'tab' => 'content',
      'label' => esc_html__('Data File', 'vietnix'),
      'description' => 'Please select the file format is .csv',
      'type' => 'file',
    ];

    $this->controls['button_register'] = [
      'tab' => 'content',
      'label' => esc_html__('Button Register', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Type button register here', 'vietnix'),
      'default' => __('Kiểm tra tên miền', 'vnx'),
    ];

    $this->controls['button_register_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Button Register Icon', 'vietnix'),
      'type' => 'icon',
      'default' => 'fa-solid fa-arrow-right',
    ];

    $this->controls['loop_slide'] = [
      'tab' => 'content',
      'label' => esc_html__('Select Type', 'vietnix'),
      'type' => 'select',
      'options' => [
        'loop' => esc_html__('Loop', 'vietnix'),
        'slide' => esc_html__('Slide', 'vietnix'),
        'fade' => esc_html__('Fade', 'vietnix'),
      ],
      'inline' => true,
      'placeholder' => esc_html__('Select Type Loop', 'vietnix'),
      'fullAccess' => true,
      'group' => 'slide_control',
    ];

    $this->controls['item_start'] = [
      'group' => 'slide_control',
      'label' => esc_html__('Start index', 'vietnix'),
      'type' => 'number',
      'placeholder' => 1,
      'breakpoints' => true,
      'fullAccess' => true,
    ];

    $this->controls['perPage'] = [
      'group' => 'slide_control',
      'label' => esc_html__('Per Page', 'vietnix'),
      'type' => 'number',
      'placeholder' => 1,
      'breakpoints' => true,
      'fullAccess' => true,
    ];

    $this->controls['perMove'] = [
      'group' => 'slide_control',
      'label' => esc_html__('Per Move', 'vietnix'),
      'type' => 'number',
      'placeholder' => 1,
      'breakpoints' => true,
      'fullAccess' => true,
    ];

    $this->controls['gap'] = [
      'group' => 'slide_control',
      'label' => esc_html__('Gap Items', 'vietnix'),
      'type' => 'number',
      'placeholder' => '10px',
      'breakpoints' => true,
      'fullAccess' => true,
    ];

    $this->controls['rel'] = [
      'group' => 'link_control',
      'tab' => 'content',
      'label' => 'Attribute: rel',
      'type' => 'text',
      'inline' => true,
    ];

    $this->controls['ariaLabel'] = [
      'group' => 'link_control',
      'tab' => 'content',
      'label' => 'Attribute: aria-label',
      'type' => 'text',
      'inline' => true,
    ];

    $this->controls['title'] = [
      'group' => 'link_control',
      'tab' => 'content',
      'label' => 'Attribute: title',
      'type' => 'text',
      'inline' => true,
    ];

    $this->controls['new_tab'] = [
      'group' => 'link_control',
      'tab' => 'content',
      'label' => esc_html__('Open in new tab', 'vietnix'),
      'type' => 'checkbox',
    ];

  }

  public function render()
  {
    $my_class = ['vnx_element'];
    $this->set_attribute('_root', 'class', $my_class);

    echo "<div {$this->render_attributes('_root')}>";
    View::render("widgets/bricks/domain/vnx-slide-domain-v1", $this);
    echo '</div>';
  }

  public function get_upload_file_data()
  {
    try {
      $settings = $this->settings;
      if (!isset($settings['import_csv']) || $settings['import_csv'] == "")
        return array(
          'status' => 'error',
          'message' => 'You have no file uploaded',
        );
      $csvdata = [];
      $file = '';

      $link = $settings['import_csv']['url'];
      // Biểu thức chính quy để lấy từ "wp-content" đến cuối link
      $regex = '/wp-content\/(.*)/';
      // Sử dụng preg_match để tìm kiếm
      if (preg_match($regex, $link, $matches)) {
        // $matches[0] sẽ chứa toàn bộ phần match
        $file = ABSPATH . $matches[0];
      } else {
        $message = "Can't replace domain form File URL<br/>";
        $message .= 'File URL form $settings: ' . $settings['import_csv']['url'] . '<br/>';
        $message .= 'File URL after replace: ' . $file . '<br/>';
        return array(
          'status' => 'error',
          'message' => $message,
        );
      }

      if (!file_exists($file)) {
        $message = 'CSV File not found.<br/>';
        $message .= 'File URL form $settings: ' . $settings['import_csv']['url'] . '<br/>';
        $message .= 'File URL after replace: ' . $file . '<br/>';
        return array(
          'status' => 'error',
          'message' => $message,
        );
      }
      $handle = fopen($file, "r");
      if (!$handle) {
        $message = 'File open failed.<br/>';
        $message .= 'File URL form $settings: ' . $settings['import_csv']['url'] . '<br/>';
        $message .= 'File URL after replace: ' . $file . '<br/>';
        return array(
          'status' => 'error',
          'message' => $message,
        );
      }
      while (($line = fgetcsv($handle)) !== false) {
        array_push($csvdata, $line);
      }
      fclose($handle);
      return array(
        'status' => 'success',
        'data' => $csvdata,
      );
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }
}