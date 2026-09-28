<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Domain_Result_V2_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-domain-result-v2';
  public $icon = 'ion-md-book';
  // public $scripts = [ 'vnxDomainSearchRedirect' ];

  public function get_label()
  {
    return esc_html__('VNX Domain Result V2', 'vietnix');
  }

  public function enqueue_scripts()
  {

    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_register_script('vnx-domain-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_handle/vnx_domain.js', ['jquery'], '1.1', true);
    wp_enqueue_script('vnx-domain-center');
    wp_localize_script('vnx-domain-center', 'vnxDomainNonce', array('nonce' => wp_create_nonce('vnx_domain_nonce')));
    wp_register_script('vnx-domain-result-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_handle/vnx_box_result.js', ['jquery'], filemtime(VNX_PLUGIN_PATH_CENTER . 'widgets/inc/bricks/js/domain_handle/vnx_box_result.js'), true);
    wp_enqueue_script('vnx-domain-result-center');
  }

  public function set_controls()
  {
    $this->controls['result_style'] = [
      'tab' => 'content',
      'label' => esc_html__('Select Result Style', 'vietnix'),
      'type' => 'select',
      'options' => [
        'domain-result' => esc_html__('Domain search', 'vietnix'),
        'domain-result-page-whois' => esc_html__('Domain search in page whois', 'vietnix'),
        'result-main-domain-page-whois' => esc_html__('Domain result main domain in page whois', 'vietnix'),
        'result-whois' => esc_html__('Return whois result of domain', 'vietnix'),
        'result-suggets-detail-whois' => esc_html__('Return suggets domain in page detail whois', 'vietnix'),
        'result-muti-whois' => esc_html__('Return muti whois', 'vietnix'),
        'result-muti-domain' => esc_html__('Return muti search domain', 'vietnix'),
        'result-domain-ai-onpage' => esc_html__('Return result domain ai on page', 'vietnix'),
        'result-domain-onpage' => esc_html__('Return result domain on page', 'vietnix'),
        'result-muti-domain-onpage' => esc_html__('Return result muti domain on page', 'vietnix'),
      ],
      'inline' => true,
      'placeholder' => esc_html__('Select style', 'vietnix'),
      'multiple' => false,
      'searchable' => true,
      'clearable' => true,
      'default' => 'domain-result',
    ];

    $this->controls['whois_url'] = [
      'tab' => 'content',
      'type' => 'text',
      'label' => esc_html__('Detail whois URL', 'vietnix'),
      'description' => 'Enter the path to the whois details page/Enter the path to the whois page',
      'placeholder' => 'https://vietnix.vn/detail-whois/',
      'required' => ['result_style', '=', ['domain-result-page-whois', 'result-main-domain-page-whois', 'result-whois', 'result-muti-whois', 'result-domain-onpage', 'result-muti-domain-onpage']],
    ];
    $this->controls['el_show_total_result'] = [
      'tab' => 'content',
      'type' => 'text',
      'label' => esc_html__('Selector total result', 'vietnix'),
      'description' => 'Enter ID element to show total result',
      'placeholder' => '#total-result',
      'required' => ['result_style', '=', ['result-muti-domain']],
    ];
    $this->controls['icon_div_domain_ai'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon div domain ai', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'themify',
        // fontawesome/ionicons/themify
        'icon' => 'ti-star',
        // Example: Themify icon class
      ],
      'css' => [
        [
          'selector' => '.icon-svg',
          // Use to target SVG file
        ],
      ],
      'required' => ['result_style', '=', ['result-domain-ai-onpage']],
    ];

    $this->controls['icon_add_to_cart'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon add to cart', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'themify',
        // fontawesome/ionicons/themify
        'icon' => 'ti-close',
        // Example: Themify icon class
      ],
      'css' => [
        [
          'selector' => '.icon-svg',
          // Use to target SVG file
        ],
      ],
      'required' => ['result_style', '=', ['result-domain-ai-onpage', 'result-domain-onpage', 'result-muti-domain-onpage']],
    ];

    $this->controls['icon_add_to_cart_remove'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon add to cart remove', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'themify',
        // fontawesome/ionicons/themify
        'icon' => 'ti-close',
        // Example: Themify icon class
      ],
      'css' => [
        [
          'selector' => '.icon-svg',
          // Use to target SVG file
        ],
      ],
      'required' => ['result_style', '=', ['result-domain-ai-onpage', 'result-domain-onpage', 'result-muti-domain-onpage']],
    ];

    $this->controls['domain_suggest'] = [
      'tab' => 'content',
      'label' => esc_html__('Domain suggest', 'vietnix'),
      'type' => 'text',
      'default' => 'vn,com,net,org',
      'placeholder' => 'vn,com,net,org',
      'description' => 'Enter domain suggestions.',
      'required' => ['result_style', '=', ['result-domain-onpage']],
    ];

    $this->controls['domain_category'] = [
      'tab' => 'content',
      'label' => esc_html__('Domain category', 'vietnix'),
      'type' => 'file',
      'default' => '',
      'placeholder' => '',
      'description' => 'Upload file category domain',
      'required' => ['result_style', '=', ['result-domain-onpage', 'result-muti-domain-onpage']],
    ];

    $this->controls['domain_combo_hidden'] = [
      'tab' => 'content',
      'label' => esc_html__('Domain combo hidden', 'vietnix'),
      'type' => 'checkbox',
      'default' => false,
      'description' => 'Hidden domain combo',
      'required' => ['result_style', '=', ['result-domain-onpage']],
    ];

    $this->controls['domain_combo_data'] = [
      'tab' => 'content',
      'label' => esc_html__('Domain combo data', 'vietnix'),
      'type' => 'file',
      'default' => '',
      'placeholder' => '',
      'description' => 'Upload file combo domain',
      'required' => ['result_style', '=', ['result-domain-onpage']],
    ];
  }

  public function render()
  {
    $style_setting = isset($this->settings['result_style']) && $this->settings['result_style'] ? $this->settings['result_style'] : 'domain-result';
    $list_style = ['domain-result', 'domain-result-page-whois', 'result-main-domain-page-whois', 'result-whois', 'result-suggets-detail-whois', 'result-muti-domain', 'result-muti-whois', 'result-domain-ai-onpage', 'result-domain-onpage', 'result-muti-domain-onpage'];
    if (in_array($style_setting, $list_style)) {
      View::render("widgets/bricks/vnx-domain-result-v2", $this);
    }
  }

  public function get_upload_file_data()
  {
    $file_url = $this->settings['domain_category']['url'];
    if (!$file_url) {
      return array(
        'status' => 'error',
        'message' => 'File not found or invalid file data',
      );
    }
    // Convert URL to system path
    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];
    $base_path = $upload_dir['basedir'];

    $file_path = str_replace($base_url, $base_path, $file_url);

    if (!file_exists($file_path)) {
      return array(
        'status' => 'error',
        'message' => 'File not found at path: ' . $file_path,
      );
    }

    $handle = fopen($file_path, "r");
    if (!$handle) {
      return array(
        'status' => 'error',
        'message' => 'Failed to open file',
      );
    }

    $headers = [];
    $columns = [];
    $is_first_line = true;

    while (($line = fgetcsv($handle)) !== false) {
      // Lọc bỏ những dòng hoàn toàn trống
      $filtered_line = array_filter($line, function ($value) {
        return !empty(trim($value));
      });

      if (!empty($filtered_line)) {
        if ($is_first_line) {
          // Chỉ lấy những header không rỗng
          $headers = array_map('trim', $line);
          $valid_headers = [];
          foreach ($headers as $index => $header) {
            if (!empty($header)) {
              $valid_headers[$index] = $header;
              $columns[$index] = []; // Thay đổi key từ header thành index
            }
          }
          $is_first_line = false;
          continue;
        }

        // Chỉ xử lý những cột có header hợp lệ và có dữ liệu
        foreach ($line as $index => $value) {
          if (isset($valid_headers[$index])) {
            $trimmed_value = trim($value);
            if (!empty($trimmed_value)) {
              $columns[$index][] = $trimmed_value; // Thay đổi key từ header thành index
            }
          }
        }
      }
    }
    fclose($handle);

    // Chuyển đổi cấu trúc dữ liệu theo yêu cầu
    $result = [];

    // Lấy tên tab từ header (dòng đầu tiên)
    foreach ($columns as $index => $domain_list) {
      $tab_name = isset($valid_headers[$index]) ? $valid_headers[$index] : 'Tab ' . $index;
      $result[$index] = [
        'name_tab' => $tab_name,
        'list_tld' => $domain_list
      ];
    }

    return $result;
  }

  public function get_list_tld()
  {
    $domain_category_data = $this->get_upload_file_data($this->settings['domain_category']['url']);
    $all_tlds = [];

    if (is_array($domain_category_data) && count($domain_category_data) > 0) {
      foreach ($domain_category_data as $key => $value) {
        if (isset($value['list_tld']) && is_array($value['list_tld'])) {
          // Giữ nguyên cấu trúc array riêng lẻ
          $all_tlds[] = $value['list_tld'];
        }
      }
    }

    return $all_tlds;
  }

  public function get_upload_file_custom($file_url, $sort_type = 'row')
  {
    if (!$file_url) {
      return array(
        'status' => 'error',
        'message' => 'File not found or invalid file data',
      );
    }
    // Validate sort_type
    if (!in_array($sort_type, ['row', 'column'])) {
      $sort_type = 'row'; // Default to row if invalid
    }
    // Convert URL to system path
    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];
    $base_path = $upload_dir['basedir'];
    $file_path = str_replace($base_url, $base_path, $file_url);
    if (!file_exists($file_path)) {
      return array(
        'status' => 'error',
        'message' => 'File not found at path: ' . $file_path,
      );
    }
    $handle = fopen($file_path, "r");
    if (!$handle) {
      return array(
        'status' => 'error',
        'message' => 'Failed to open file',
      );
    }
    $headers = [];
    $columns = [];
    $is_first_line = true;
    while (($line = fgetcsv($handle)) !== false) {
      // Lọc bỏ những dòng hoàn toàn trống
      $filtered_line = array_filter($line, function ($value) {
        return !empty(trim($value));
      });
      if (!empty($filtered_line)) {
        if ($is_first_line) {
          // Chỉ lấy những header không rỗng
          $headers = array_map('trim', $line);
          $valid_headers = [];
          foreach ($headers as $index => $header) {
            if (!empty($header)) {
              $valid_headers[$index] = $header;
              $columns[$index] = []; // Thay đổi key từ header thành index
            }
          }
          $is_first_line = false;
          continue;
        }
        // Chỉ xử lý những cột có header hợp lệ và có dữ liệu
        foreach ($line as $index => $value) {
          if (isset($valid_headers[$index])) {
            $trimmed_value = trim($value);
            if (!empty($trimmed_value)) {
              $columns[$index][] = $trimmed_value; // Thay đổi key từ header thành index
            }
          }
        }
      }
    }
    fclose($handle);
    // Chuyển đổi cấu trúc dữ liệu theo sort_type
    $result = [];
    if ($sort_type === 'row') {
      // Cấu trúc theo hàng (ngang) - mỗi hàng là một object
      $column_names = array_values($valid_headers);
      // Tạo cấu trúc theo hàng
      $max_rows = 0;
      foreach ($columns as $column_data) {
        $max_rows = max($max_rows, count($column_data));
      }
      // Tạo mảng kết quả theo hàng
      for ($row = 0; $row < $max_rows; $row++) {
        $row_data = [];
        foreach ($column_names as $col_index => $col_name) {
          $value = isset($columns[$col_index][$row]) ? $columns[$col_index][$row] : '';
          $row_data[$col_name] = $value;
        }
        $result[] = $row_data;
      }
    } else {
      // Cấu trúc theo cột (dọc) - mỗi cột là một object
      foreach ($columns as $index => $domain_list) {
        $tab_name = isset($valid_headers[$index]) ? $valid_headers[$index] : 'Tab ' . $index;
        $result[$index] = [
          'name_tab' => $tab_name,
          'list_tld' => $domain_list
        ];
      }
    }

    return $result;
  }
}
