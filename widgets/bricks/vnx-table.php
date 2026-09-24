<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Table_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-table';
  public $icon = 'ion-md-cash';

  public $scripts = ['vnxPriceTable'];

  public function get_label()
  {
    return esc_html__('VNX Table', 'vietnix');
  }

  public function enqueue_scripts()
  {
    wp_register_script('expand-collapse-table-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/expand_collapse_table.js', ['jquery'], '1.0.0', true);
    wp_enqueue_script('expand-collapse-table-center');
    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');

    wp_register_script('vnx-table-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/price_table.js', ['jquery'], '1.0', true);
    $script_array = array(
      'loading_icon' => VNX_PLUGIN_URL_CENTER . 'assets/images/icons/loading-icon.png',
    );
    wp_localize_script('vnx-table-center', 'vnx_table', $script_array);
    wp_enqueue_script('vnx-table-center');
    wp_register_style('vnx-table-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/price_table.css', ['bricks-frontend'], '1.0', 'all');
    wp_enqueue_style('vnx-table-center');
    wp_register_style('owl-carousel_stylesheet-center', VNX_PLUGIN_URL_CENTER . 'assets/css/owl.carousel.min.css', '1.0.0', true);
    wp_register_script('owl-carousel-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/owl.carousel.min.js', '1.0.0', true);
  }

  public function set_control_groups()
  {
    $this->control_groups['carousel_style_section'] = [
      'title' => esc_html__('Carousel style', 'vietnix'),
      'tab' => 'content',
      'required' => ['table_style', '=', ['layout_carousel_price']],
    ];
  }

  public function set_controls()
  {
    $this->controls['table_style'] = [
      'tab' => 'content',
      'label' => esc_html__('Select Table Style', 'vietnix'),
      'type' => 'select',
      'options' => [
        'vps_price_3' => esc_html__('Bảng giá VPS V3', 'vietnix'),
        'hosting_v2' => esc_html__('Bảng giá Hosting V2', 'vietnix'),
        'hosting_v3' => esc_html__('Bảng giá Hosting V3', 'vietnix'),
        'hosting_v5' => esc_html__('Bảng giá Hosting V5', 'vietnix'),
        'compare_hosting_v2' => esc_html__('Bảng giá so sánh Hosting V2', 'vietnix'),
        'firewall_2' => esc_html__('Bảng giá Firewall 2', 'vietnix'),
        'price_server' => esc_html__('Bảng giá Server', 'vietnix'),
        'domain_price' => esc_html__('Bảng giá tên miền VN Quốc Tế', 'vietnix'),
        'domain_price_v2' => esc_html__('Bảng giá tên miền VN Quốc Tế v2', 'vietnix'),
        'domain_price_v3' => esc_html__('Bảng giá tên miền VN Quốc Tế v3', 'vietnix'),
        'package_hosting' => esc_html__('Bảng giá Gói Hosting', 'vietnix'),
        'compare_wp_hosting' => esc_html__('Bảng so sánh WP Hosting', 'vietnix'),
        'layout_carousel_price' => esc_html__('Bảng giá layout carousel', 'vietnix'),
        'wordpress_hosting_price_table' => esc_html__('Bảng giá WordPress Hosting', 'vietnix'),
      ],
      'inline' => true,
      'placeholder' => esc_html__('Select style', 'vietnix'),
      'multiple' => false,
      'searchable' => true,
      'clearable' => true,
      'default' => '',
    ];

    $this->controls['mobile_layout_for_shared_hosting'] = [
      'tab' => 'content',
      'label' => esc_html__('Mobile layout for shared hosting', 'vietnix'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => false,
      'required' => ['table_style', '=', ['hosting_v5']],
    ];

    $this->controls['show_load_more'] = [
      'tab' => 'content',
      'label' => esc_html__('Nút "Xem thêm" cho mobile ', 'bricks'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => true,
      'required' => ['table_style', '=', ['vps_price_3', 'hosting_v2', 'hosting_v3', 'hosting_v5', 'price_server']],
    ];
    $this->controls['upload'] = [
      'tab' => 'content',
      'label' => esc_html__('Data File', 'vietnix'),
      'description' => 'Please select the file format is .csv',
      'type' => 'file',
      'required' => ['table_style', '=', ['vps_price_3', 'hosting_v2', 'hosting_v3', 'hosting_v5', 'compare_hosting_v2', 'firewall_2', 'price_server', 'domain_price', 'domain_price_v2', 'domain_price_v3', 'package_hosting', 'compare_wp_hosting', 'wordpress_hosting_price_table']],
    ];

    $this->controls['cycle'] = [
      'tab' => 'content',
      'label' => esc_html__('Cycle', 'vietnix'),
      'type' => 'repeater',
      'titleProperty' => 'title',
      // Default 'title'
      'default' => [
        [
          'title' => '1 Tháng',
          'sale' => '',
        ],
        [
          'title' => '3 Tháng',
          'sale' => '',
        ],
        [
          'title' => '6 Tháng',
          'sale' => '5%',
          'featured' => true,
        ],
      ],
      'placeholder' => esc_html__('Title placeholder', 'vietnix'),
      'fields' => [
        'title' => [
          'label' => esc_html__('Cycle name', 'vietnix'),
          'type' => 'text',
          'placeholder' => '1 Tháng',
        ],
        'sale' => [
          'label' => esc_html__('Sale label', 'vietnix'),
          'type' => 'text',
          'placeholder' => '5%',
        ],
        'featured' => [
          'label' => esc_html__('Featured', 'vietnix'),
          'type' => 'checkbox',
          'inline' => true,
          'small' => true,
          'default' => false,
          // Default: false
        ],
        'hide_cols' => [
          'tab' => 'content',
          'type' => 'text',
          'label' => esc_html__('Hide col list', 'vietnix'),
          'description' => 'Enter list [4,5,6,7] numbers of col in csv file separated by commas.',
          'placeholder' => '4,5,6,7',
          'default' => '',
          'required' => ['table_style', '=', ['vps_price_3']],
        ],
      ],
      'required' => ['table_style', '=', ['vps_price_3', 'hosting_v2', 'hosting_v3', 'hosting_v5', 'price_server', 'compare_wp_hosting']],
    ];

    $this->controls['repeater_layout_carousel_price'] = [
      'tab' => 'content',
      'label' => esc_html__('Data File', 'vietnix'),
      'type' => 'repeater',
      'titleProperty' => 'list_title',
      'default' => [
        [
          'list_title' => '1 Tháng',
          'list_upload' => '',
          'list_gift_title_show' => true,
          'list_gift_title' => 'Gift title',
          'list_gift_description' => 'Gift Description',
        ]
      ],
      'fields' => [
        'list_title' => [
          'label' => esc_html__('List title', 'vietnix'),
          'type' => 'text',
          'placeholder' => '1 Tháng',
        ],
        'list_gift_title_show' => [
          'label' => __('Show gift toolbox', 'vietnix'),
          'inline' => true,
          'type' => 'checkbox',
        ],
        'list_gift_title' => [
          'label' => esc_html__('Gift title', 'vietnix'),
          'type' => 'text',
          'default' => esc_html__('Gift title', 'vietnix'),
          'placeholder' => '1 Tháng',
          'required' => ['list_gift_title_show', '!=', ''],
        ],
        'list_gift_description' => [
          'label' => esc_html__('Gift Description', 'vietnix'),
          'type' => 'editor',
          'inlineEditing' => [
            'selector' => '.list_gift_description',
            'toolbar' => true,
          ],
          'default' => esc_html__('Gift Description', 'vietnix'),
          'required' => ['list_gift_title_show', '!=', ''],
        ],
        'list_upload' => [
          'label' => __('File upload', 'vietnix'),
          'type' => 'file',
          'description' => 'Please select the file format is .csv',
        ],
        'list_cycleInfo' => [
          'content' => esc_html__('Nhập chu kỳ trong file. vd: 3 Tháng', 'vietnix'),
          'type' => 'info',
          'required' => ['list_cycle', '=', ''],
        ],
        'custom_button_url_switch' => [
          'label' => __('Custom button url', 'vietnix'),
          'inline' => true,
          'type' => 'checkbox',
        ],
        'custom_button_url' => [
          'label' => __('Button url', 'vietnix'),
          'type' => 'link',
          'pasteStyles' => false,
          'required' => ['custom_button_url_switch', '!=', ''],
        ]
      ],
      'required' => ['table_style', '=', ['layout_carousel_price']],
    ];
    $this->controls['order_by'] = [
      'tab' => 'content',
      'label' => esc_html__('Order By Selling', 'vietnix'),
      'inline' => true,
      'type' => 'checkbox',
      'required' => ['table_style', '=', ['layout_carousel_price']],
    ];

    $this->controls['cycle_price'] = [
      'tab' => 'content',
      'label' => esc_html__('Cycle', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('1 Năm', 'vietnix'),
      'default' => __('1 Năm', 'vnx'),
      'description' => 'Chu kỳ trong file.',
      'required' => ['table_style', '=', ['wordpress_hosting_price_table']],
    ];

    $this->controls['list_tooltip_gift'] = [
      'tab' => 'content',
      'label' => esc_html__('Gift title', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Type button text here', 'vietnix'),
      'default' => __('Gift title', 'vnx'),
      'required' => ['table_style', '=', ['wordpress_hosting_price_table']],
    ];

    $this->controls['list_tooltip_gift_description'] = [
      'tab' => 'content',
      'label' => esc_html__('Gift Description', 'vietnix'),
      'type' => 'editor',
      'inlineEditing' => [
        'selector' => '.list_tooltip_gift_description',
        'toolbar' => true,
      ],
      'default' => esc_html__('Gift Description', 'vietnix'),
      'required' => ['table_style', '=', ['wordpress_hosting_price_table']],
    ];

    $this->controls['custom_button_url_switch'] = [
      'tab' => 'content',
      'label' => esc_html__('Custom button url', 'vietnix'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => false,
      'required' => ['table_style', '=', ['wordpress_hosting_price_table']],
    ];

    $this->controls['custom_button_url'] = [
      'tab' => 'content',
      'label' => esc_html__('Button url', 'vietnix'),
      'type' => 'link',
      'pasteStyles' => false,
      'placeholder' => 'https://example.com/',
      'default' => [
        'type' => 'external',
        'url' => '#'
      ],
      'required' => ['custom_button_url_switch', '!=', ''],
    ];

    $this->controls['list_button_text'] = [
      'tab' => 'content',
      'label' => esc_html__('Button text', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Type button text here', 'vietnix'),
      'default' => __('Click me', 'vnx'),
      'required' => ['table_style', '=', ['layout_carousel_price', 'wordpress_hosting_price_table']],
    ];

    $this->controls['list_viewmore_url'] = [
      'tab' => 'content',
      'label' => esc_html__('Url view more', 'vietnix'),
      'type' => 'link',
      'pasteStyles' => false,
      'placeholder' => 'https://example.com/',
      'default' => [
        'type' => 'external',
        'url' => '#'
      ],
      'required' => ['table_style', '=', ['wordpress_hosting_price_table']],
    ];

    $this->controls['list_button_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Button Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'Fontawesome - Solid',
        'icon' => 'fas fa-cart-shopping',
      ],
      'required' => ['table_style', '=', ['layout_carousel_price']],
    ];

    $this->controls['card_show_tab'] = [
      'tab' => 'content',
      'label' => esc_html__('Show tab', 'vietnix'),
      'inline' => true,
      'type' => 'checkbox',
      'required' => ['table_style', '=', ['layout_carousel_price']],
      'group' => 'carousel_style_section',
    ];

    $this->controls['tab_active'] = [
      'label' => __('Tab active', 'elementor'),
      'group' => 'carousel_style_section',
      'inline' => false,
      'type' => 'number',
      'min' => 1,
      'step' => 1,
      'required' => ['card_show_tab', '!=', ''],
      'default' => 1,
    ];

    $this->controls['loop_slide'] = [
      'tab' => 'content',
      'label' => esc_html__('Infinity loop', 'vietnix'),
      'inline' => true,
      'type' => 'checkbox',
      'required' => ['table_style', '=', ['layout_carousel_price']],
      'group' => 'carousel_style_section',
      'default' => true,
      'css' => [],
    ];

    $this->controls['item_show'] = [
      'tab' => 'content',
      'label' => esc_html__('Item', 'vietnix'),
      'inline' => true,
      'type' => 'number',
      'min' => 1,
      'max' => 10,
      'step' => 1,
      'default' => 1,
      'group' => 'carousel_style_section',
      'css' => [],
    ];

    $this->controls['show_arrow'] = [
      'tab' => 'content',
      'label' => esc_html__('Show arrow', 'vietnix'),
      'inline' => true,
      'type' => 'checkbox',
      'required' => ['table_style', '=', ['layout_carousel_price']],
      'group' => 'carousel_style_section',
      'css' => [],
    ];

    $this->controls['show_dot'] = [
      'tab' => 'content',
      'label' => esc_html__('Show dot', 'vietnix'),
      'inline' => true,
      'type' => 'checkbox',
      'required' => ['table_style', '=', ['layout_carousel_price']],
      'group' => 'carousel_style_section',
      'css' => [],
    ];

    $this->controls['hide_col_list'] = [
      'tab' => 'content',
      'type' => 'text',
      'label' => esc_html__('Hide col list', 'vietnix'),
      'description' => 'Enter list [4,5,6,7] numbers of col in csv file separated by commas.',
      'placeholder' => '4,5,6,7',
      'required' => ['table_style', '=', ['vps_price_3']],
    ];

    $this->controls['firewall_popular'] = [
      'tab' => 'content',
      'label' => esc_html__('Gói phổ biến', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Firewall 3', 'vietnix'),
      'description' => 'Gói firewall phổ biến.',
      'required' => ['table_style', '=', ['firewall_2']],
    ];

    $this->controls['firewall_row_mobile'] = [
      'tab' => 'content',
      'label' => esc_html__('Hiển thị số lượng dòng.', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('4', 'vietnix'),
      'description' => 'Số lượng dòng sẽ hiển thị ở mobile.',
      'required' => ['table_style', '=', ['firewall_2']],
    ];

    $this->controls['yes_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Yes Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        // fontawesome/ionicons/themify
        'icon' => 'fas fa-circle-check',
        // Example: Themify icon class
      ],
      'css' => [
        [
          'selector' => '.vnx_icon_yes',
          // Use to target SVG file
        ],
      ],
      'required' => ['table_style', '=', ['firewall_2']],
    ];

    $this->controls['icon_yes_color'] = [
      'tab' => 'content',
      'label' => esc_html__('Yes Icon Color', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx_icon_yes',
        ],
      ],
      'default' => [
        'hex' => '#3ce77b',
        'rgb' => 'rgba(60, 231, 123, 0.9)',
      ],
      'required' => [
        ['table_style', '=', ['firewall_2']],
        ['yes_icon.library', '!=', ['svg']],
      ],
    ];

    $this->controls['no_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('No Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        // fontawesome/ionicons/themify
        'icon' => 'fas fa-circle-xmark',
        // Example: Themify icon class
      ],
      'css' => [
        [
          'selector' => '.vnx_icon_no',
          // Use to target SVG file
        ],
      ],
      'required' => ['table_style', '=', ['firewall_2']],
    ];

    $this->controls['no_yes_color'] = [
      'tab' => 'content',
      'label' => esc_html__('No Icon Color', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx_icon_no',
        ],
      ],
      'default' => [
        'hex' => '#3ce77b',
        'rgb' => 'rgba(60, 231, 123, 0.9)',
      ],
      'required' => ['table_style', '=', ['firewall_2']],
    ];

    $this->controls['expand_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Expand Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        // fontawesome/ionicons/themify
        'icon' => 'fas fa-angles-down',
        // Example: Themify icon class
      ],
      'css' => [
        [
          'selector' => '.vnx_icon_expand',
          // Use to target SVG file
        ],
      ],
      'required' => ['table_style', '=', ['firewall_2', 'compare_wp_hosting']],
    ];

    $this->controls['collapse_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Collapse Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        // fontawesome/ionicons/themify
        'icon' => 'fas fa-angles-up',
        // Example: Themify icon class
      ],
      'css' => [
        [
          'selector' => '.vnx_icon_collapse',
          // Use to target SVG file
        ],
      ],
      'required' => ['table_style', '=', ['firewall_2', 'compare_wp_hosting']],
    ];

    $this->controls['domain_type'] = [
      'tab' => 'content',
      'label' => esc_html__('Select Domain Type', 'vietnix'),
      'type' => 'select',
      'options' => [
        'vn' => esc_html__('Tên miền Việt Nam', 'vietnix'),
        'qt' => esc_html__('Tên miền Quốc tế', 'vietnix'),
      ],
      'inline' => true,
      'placeholder' => esc_html__('Select type', 'vietnix'),
      'multiple' => false,
      'searchable' => false,
      'clearable' => false,
      'default' => 'vn',
      'required' => ['table_style', '=', ['domain_price', 'domain_price_v2', 'domain_price_v3']],
    ];


    $this->controls['domain_text_vat'] = [
      'tab' => 'content',
      'label' => esc_html__('Domain Text VAT', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('10%', 'vietnix'),
      'default' => '10%',
      'required' => ['table_style', '=', ['domain_price_v2', 'domain_price_v3']],
    ];

    $this->controls['tooltip_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Tooltip Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeRegular',
        // fontawesome/ionicons/themify
        'icon' => 'fa fa-question-circle',
        // Example: Themify icon class
      ],
      'css' => [
        [
          'selector' => '.vnx_tooltip_icon',
          // Use to target SVG file
        ],
      ],
      'required' => ['table_style', '=', ['domain_price', 'domain_price_v2', 'domain_price_v3']],
    ];

    $this->controls['domain_tag_image'] = [
      'tab' => 'content',
      'label' => esc_html__('Domain Tag Image', 'vietnix'),
      'type' => 'image',
      'required' => ['table_style', '=', ['domain_price_v3']],
    ];

    $this->controls['typeInfo_1'] = [
      'tab' => 'content',
      'content' => '<b>' . esc_html__('REGISTER LINK', 'vietnix') . '</b>',
      'type' => 'info',
      'required' => ['table_style', '=', ['domain_price', 'domain_price_v2', 'domain_price_v3']],
    ];

    $this->controls['domain_register_link'] = [
      'tab' => 'content',
      'label' => esc_html__('Register Link', 'vietnix'),
      'type' => 'link',
      'pasteStyles' => false,
      'placeholder' => esc_html__('http://yoursite.com', 'vietnix'),
      'required' => ['table_style', '=', ['domain_price', 'domain_price_v2', 'domain_price_v3']],
    ];

    $this->controls['typeInfo_2'] = [
      'tab' => 'content',
      'content' => '<b>' . esc_html__('EXTEND LINK', 'vietnix') . '</b>',
      'type' => 'info',
      'required' => ['table_style', '=', ['domain_price', 'domain_price_v2', 'domain_price_v3']],
    ];

    $this->controls['domain_extend_link'] = [
      'tab' => 'content',
      'label' => esc_html__('Extend Link', 'vietnix'),
      'type' => 'link',
      'pasteStyles' => false,
      'placeholder' => esc_html__('http://yoursite.com', 'vietnix'),
      'required' => ['table_style', '=', ['domain_price', 'domain_price_v2', 'domain_price_v3']],
    ];

    $this->controls['typeInfo_3'] = [
      'tab' => 'content',
      'content' => '<b>' . esc_html__('TRANSFER LINK', 'vietnix') . '</b>',
      'type' => 'info',
      'required' => ['table_style', '=', ['domain_price', 'domain_price_v2', 'domain_price_v3']],
    ];

    $this->controls['domain_transfer_link'] = [
      'tab' => 'content',
      'label' => esc_html__('Transfer Link', 'vietnix'),
      'type' => 'link',
      'pasteStyles' => false,
      'placeholder' => esc_html__('http://yoursite.com', 'vietnix'),
      'required' => ['table_style', '=', ['domain_price', 'domain_price_v2', 'domain_price_v3']],
    ];
  }

  public function render()
  {
    View::render("widgets/bricks/vnx-table", $this);
  }

  public function get_upload_file_data()
  {
    $settings = $this->settings;
    if (!isset($settings['upload']) || $settings['upload'] == "")
      return array(
        'status' => 'error',
        'message' => 'You have no file uploaded',
      );
    $csvdata = [];
    $file = '';

    $link = $settings['upload']['url'];
    // Biểu thức chính quy để lấy từ "wp-content" đến cuối link
    $regex = '/wp-content\/(.*)/';
    // Sử dụng preg_match để tìm kiếm
    if (preg_match($regex, $link, $matches)) {
      // $matches[0] sẽ chứa toàn bộ phần match
      $file = ABSPATH . $matches[0];
    } else {
      $message = "Can't replace domain form File URL<br/>";
      $message .= 'File URL form $settings: ' . $settings['upload']['url'] . '<br/>';
      $message .= 'File URL after replace: ' . $file . '<br/>';
      return array(
        'status' => 'error',
        'message' => $message,
      );
    }

    if (!file_exists($file)) {
      $message = 'CSV File not found.<br/>';
      $message .= 'File URL form $settings: ' . $settings['upload']['url'] . '<br/>';
      $message .= 'File URL after replace: ' . $file . '<br/>';
      return array(
        'status' => 'error',
        'message' => $message,
      );
    }
    $handle = fopen($file, "r");
    if (!$handle) {
      $message = 'File open failed.<br/>';
      $message .= 'File URL form $settings: ' . $settings['upload']['url'] . '<br/>';
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

  public function findValueIndex_Center($arr, $value)
  {
    foreach ($arr as $index => $object) {
      if ($object[0] === $value) {
        return $index;
      }
    }
    return -1;
  }

  public function findIndexInObject_Center($obj, $searchValue)
  {
    foreach ($obj as $key => $value) {
      if ($value === $searchValue) {
        return $key;
      }
    }
    return null;
  }

  public function convertStringToArray_Center($string)
  {
    $pattern = '/\[(.*?)\]/'; // Regular expression to match data within square brackets
    preg_match_all($pattern, $string, $matches); // Extract data within brackets

    $result = $matches[1]; // Extracted data will be in the first capture group

    return $result;
  }
}
