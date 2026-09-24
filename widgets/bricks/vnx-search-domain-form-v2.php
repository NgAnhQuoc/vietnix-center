<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Search_Domain_Form_V2_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-search-domain-form-v2';
  public $icon = 'ion-md-search';
  public $scripts = [
    'vnxDomainSearchRedirect',
    'vnxDomainSearchOnpage',
    'vnxDomainSearchPageWhois',
  ];

  public function get_label()
  {
    return esc_html__('VNX Search Domain Form V2', 'vietnix');
  }

  public function enqueue_scripts()
  {
    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_register_script('vnx-domain-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_handle/vnx_domain.js', ['jquery'], '1.1', true);
    wp_enqueue_script('vnx-domain-center');
    wp_register_script('vnx-domain-handle-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_handle/vnx_box_search.js', ['jquery'], filemtime(VNX_PLUGIN_PATH_CENTER . 'widgets/inc/bricks/js/domain_handle/vnx_box_search.js'), true);
    wp_enqueue_script('vnx-domain-handle-center');
    wp_register_script('xlsx-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/xlsx.min.js');
    wp_enqueue_script('xlsx-center');
  }

  public function set_control_groups()
  {
    $this->control_groups['form_content'] = [
      'title' => esc_html__('Form Content', 'vietnix'),
      'tab' => 'content',
    ];
  }

  public function set_controls()
  {
    $this->controls['form_style'] = [
      'tab' => 'content',
      'label' => esc_html__('Select Form Style', 'vietnix'),
      'type' => 'select',
      'options' => [
        'redirect' => esc_html__('Redirect', 'vietnix'),
        'onpage' => esc_html__('On page', 'vietnix'),
        'page-whois' => esc_html__('In page whois', 'vietnix'),
        'search-muti-whois' => esc_html__('Search muti whois domain', 'vietnix'),
        'form-search-domain-redirect' => esc_html__('Form search domain redirect', 'vietnix'),
        'form-search-domain-ai' => esc_html__('Form search domain AI', 'vietnix'),
        'form-search-whois-popup' => esc_html__('Form search whois popup', 'vietnix'),
        'form-search-muti-domain' => esc_html__('Form search muti domain', 'vietnix'),
        'form-search-muti-domain-mini' => esc_html__('Form search muti domain mini', 'vietnix'),
      ],
      'inline' => true,
      'placeholder' => esc_html__('Select style', 'vietnix'),
      'multiple' => false,
      'searchable' => true,
      'clearable' => true,
      'default' => '',
    ];

    $this->controls['link_result_id'] = [
      'tab' => 'content',
      'label' => esc_html__('ID Box Result Domain', 'vietnix'),
      'type' => 'text',
      'description' => 'Nhập ID của Box Result Domain',
      'required' => ['form_style', '=', ['onpage', 'page-whois', 'search-muti-whois']],
    ];

    $this->controls['redirectInfo'] = [
      'tab' => 'content',
      'content' => __('Redirect tới trang kết quả sau khi submit form, cần điền URL trang kết quả. Lưu ý: <b style="color:yellow">Có / ở cuối + Không điền params</b>', 'vietnix'),
      'type' => 'info',
      'required' => ['form_style', '=', ['redirect', 'form-search-domain-redirect', 'form-search-domain-ai']],
    ];

    $this->controls['is_redirect'] = [
      'tab' => 'content',
      'label' => __('Redrect search', 'vietnix'),
      'description' => 'Chuyến hướng để hiển thị kết quả tìm kiếm.',
      'type' => 'checkbox',
      'default' => false,
      'required' => ['form_style', '=', 'search-muti-whois', 'form-search-muti-domain'],
    ];

    $this->controls['url_redirect_search_muti'] = [
      'tab' => 'content',
      'type' => 'text',
      'label' => esc_html__('Link redirect result', 'vietnix'),
      'required' => ['is_redirect', '=', [true]],
    ];

    $this->controls['onPageInfo'] = [
      'tab' => 'content',
      'content' => __('Trả kết quả ở trang hiện tại, cần có Element <b style="color:yellow">VNX Domain Result</b> để có thể trả kết quả', 'vietnix'),
      'type' => 'info',
      'required' => ['form_style', '=', 'onpage', 'page-whois', 'search-muti-whois', 'form-search-muti-domain'],
    ];

    $this->controls['placeholder'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Placeholder', 'vietnix'),
      'description' => 'Enter the placeholder',
      'placeholder' => 'Tìm kiếm tên miền của bạn',
      'required' => ['form_style', '=', ['onpage', 'page-whois', 'form-search-domain-redirect', 'form-search-domain-ai', 'form-search-whois-popup', 'form-search-muti-domain']],
    ];

    $this->controls['status_form'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'select',
      'label' => esc_html__('Status form', 'vietnix'),
      'description' => 'Enter the status form',
      'placeholder' => 'Status form search',
      'options' => [
        'onpage' => esc_html__('On Page', 'vietnix'),
        'redirect' => esc_html__('Redirect Page', 'vietnix')
      ],
      'default' => 'redirect',
      'inline' => true,
      'required' => ['form_style', '=', ['form-search-domain-redirect', 'form-search-domain-ai', 'form-search-muti-domain', 'form-search-muti-domain-mini']],
    ];

    $this->controls['id_box_result'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('ID Box Result Domain', 'vietnix'),
      'description' => 'Enter the id or class box result domain',
      'placeholder' => 'ID Or Class Box Result Domain',
      'default' => '',
      'required' => ['status_form', '=', ['onpage', 'form-search-muti-domain', 'form-search-muti-domain-mini']],
    ];

    $this->controls['button_text'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Button text', 'vietnix'),
      'description' => 'Enter the button text',
      'placeholder' => 'Tìm kiếm',
      'default' => 'Tìm kiếm',
      'required' => ['form_style', '=', ['onpage', 'page-whois', 'form-search-domain-redirect', 'form-search-domain-ai', 'form-search-whois-popup', 'form-search-muti-domain', 'form-search-muti-domain-mini']],
    ];

    $this->controls['button_text'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Button text', 'vietnix'),
      'description' => 'Enter the button text',
      'placeholder' => 'Tìm kiếm',
      'default' => 'Tìm kiếm',
      'required' => ['form_style', '=', ['onpage', 'page-whois', 'form-search-domain-redirect', 'form-search-domain-ai', 'form-search-whois-popup', 'form-search-muti-domain-mini']],
    ];

    $this->controls['button_icon'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'label' => esc_html__('Button icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'themify',
        // fontawesome/ionicons/themify
        'icon' => 'ti-search',
        // Example: Themify icon class
      ],
      'css' => [
        [
          'selector' => '.icon-svg',
          // Use to target SVG file
        ],
      ],
      'required' => ['form_style', '=', ['onpage', 'page-whois', 'form-search-domain-redirect', 'form-search-domain-ai', 'form-search-whois-popup', 'form-search-muti-domain', 'form-search-muti-domain-mini']],
    ];

    $this->controls['button_icon_filter'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'label' => esc_html__('Button icon filter', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'themify',
        // fontawesome/ionicons/themify
        'icon' => 'ti-filter',
        // Example: Themify icon class
      ],
      'required' => ['form_style', '=', ['form-search-domain-redirect', 'form-search-muti-domain-mini']],
    ];

    $this->controls['button_class'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Button class', 'vietnix'),
      'description' => 'Example: btn btn-primary',
      'placeholder' => 'Example: btn btn-primary',
      'default' => '',
      'required' => ['form_style', '=', ['onpage', 'page-whois', 'redirect', 'form-search-domain-redirect', 'form-search-domain-ai', 'form-search-whois-popup', 'form-search-muti-domain', 'form-search-muti-domain-mini']],
    ];

    $this->controls['clear_icon'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'label' => esc_html__('Clear icon', 'vietnix'),
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
      'required' => ['form_style', '=', ['onpage', 'page-whois', 'form-search-muti-domain-mini']],
    ];

    $this->controls['redirect_url'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Redirect URL', 'vietnix'),
      'description' => 'Enter the url to redirect after form submit',
      'placeholder' => 'https://domain.com/....',
      'required' => ['form_style', '=', ['redirect', 'form-search-domain-redirect', 'form-search-domain-ai', 'form-search-whois-popup', 'form-search-muti-domain', 'form-search-muti-domain-mini']],
    ];

    $this->controls['instructions_url'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Instructions URL', 'vietnix'),
      'description' => 'Enter the url to instructions',
      'placeholder' => 'https://domain.com/....',
      'required' => ['form_style', '=', ['form-search-muti-domain', 'form-search-muti-domain-mini']],
    ];

    $this->controls['susggest_tld'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Susggest TLD', 'vietnix'),
      'description' => 'Text the default tld',
      'default' => 'com',
      'placeholder' => 'Text your tld',
      'required' => ['form_style', '=', ['onpage', 'page-whois']],
    ];

    $this->controls['prioritize_tld'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Prioritize TLD', 'vietnix'),
      'default' => 'vn, com, com.vn, net',
      'required' => ['form_style', '=', ['onpage', 'page-whois', 'form-search-muti-domain', 'form-search-muti-domain-mini']],
    ];

    $this->controls['domain_sample_csv'] = [
      'tab' => 'content',
      'type' => 'text',
      'label' => esc_html__('Domain csv sample file link', 'vietnix'),
      'required' => ['form_style', '=', ['search-muti-whois']],
    ];

    $this->controls['file_tld_search'] = [
      'tab' => 'content',
      'type' => 'text',
      'label' => esc_html__('File TLD Search', 'vietnix'),
      'required' => ['form_style', '=', ['form-search-domain-redirect', 'form-search-muti-domain', 'form-search-muti-domain-mini']],
    ];

    $this->controls['whois_text_link'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Whois Text Link', 'vietnix'),
      'description' => 'Enter the whois text link',
      'placeholder' => 'https://whois.vn/....',
      'required' => ['form_style', '=', ['form-search-whois-popup']],
    ];

    $this->controls['dns_tooltip'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('DNS Tooltip', 'vietnix'),
      'description' => 'Enter the dns tooltip',
      'placeholder' => 'DNS Tooltip',
      'required' => ['form_style', '=', ['form-search-whois-popup']],
    ];

    $this->controls['registry_lock_tooltip'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Registry Lock Tooltip', 'vietnix'),
      'description' => 'Enter the registry lock tooltip',
      'placeholder' => 'Registry Lock Tooltip',
      'required' => ['form_style', '=', ['form-search-whois-popup']],
    ];

    $this->controls['hidden_information_tooltip'] = [
      'tab' => 'content',
      'group' => 'form_content',
      'type' => 'text',
      'label' => esc_html__('Hidden Information Tooltip', 'vietnix'),
      'description' => 'Enter the hidden information tooltip',
      'placeholder' => 'Hidden Information Tooltip',
      'required' => ['form_style', '=', ['form-search-whois-popup']],
    ];
  }

  public function render()
  {
    $form_style = isset($this->settings['form_style']) && $this->settings['form_style'] ? $this->settings['form_style'] : 'redirect';
    $domain_search = ['redirect', 'onpage', 'page-whois', 'search-muti-whois', 'form-search-domain-redirect', 'form-search-domain-ai', 'form-search-whois-popup', 'form-search-muti-domain', 'form-search-muti-domain-mini'];
    if (in_array($form_style, $domain_search)) {
      View::render("widgets/bricks/vnx-search-domain-form-v2", $this);
    }
  }

  public function get_data_tld_search()
  {
    $csvdata = [];
    $file = '';

    // $link = $link;
    if (!function_exists('get_field')) {
      $message = "Advance Custom Fields plugin is not activated";
      return array(
        'status' => 'error',
        'message' => $message,
      );
    }
    $link = get_field('tld_data', 'option') ?? '';
    if (!$link) {
      $message = "Have no TLD Data file uploaded in Services Data ( Admin menu )";
      return array(
        'status' => 'error',
        'message' => $message,
      );
    }
    // Biểu thức chính quy để lấy từ "wp-content" đến cuối link
    $regex = '/wp-content\/(.*)/';
    // Sử dụng preg_match để tìm kiếm
    if (preg_match($regex, $link, $matches)) {
      // $matches[0] sẽ chứa toàn bộ phần match
      $file = ABSPATH . $matches[0];
    } else {
      $message = "Can't replace domain form File URL<br/>";
      $message .= 'File URL form $settings: ' . $link . '<br/>';
      $message .= 'File URL after replace: ' . $file . '<br/>';
      return array(
        'status' => 'error',
        'message' => $message,
      );
    }

    if (!file_exists($file)) {
      $message = 'CSV File not found.<br/>';
      $message .= 'File URL form $settings: ' . $link . '<br/>';
      $message .= 'File URL after replace: ' . $file . '<br/>';
      return array(
        'status' => 'error',
        'message' => $message,
      );
    }
    $handle = fopen($file, "r");
    if (!$handle) {
      $message = 'File open failed.<br/>';
      $message .= 'File URL form $settings: ' . $link . '<br/>';
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
  }
  public function vnx_get_data_tld_search()
  {
    $file_url = isset($this->settings['file_tld_search']) ? $this->settings['file_tld_search'] : '';
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
      if (!empty(array_filter($line))) {
        if ($is_first_line) {
          $headers = array_map('trim', $line);
          foreach ($headers as $header) {
            $columns[$header] = [];
          }
          $is_first_line = false;
          continue;
        }

        foreach ($line as $index => $value) {
          if (isset($headers[$index])) {
            $columns[$headers[$index]][] = trim($value);
          }
        }
      }
    }
    fclose($handle);

    return $columns;
  }
}
