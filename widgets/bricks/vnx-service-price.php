<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Service_Price_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-service-price';
  public $icon = 'ion-md-cash';


  public function get_label()
  {
    return esc_html__('VNX Service Price', 'vietnix');
  }

  public function enqueue_scripts()
  {
    wp_enqueue_script('bricks-splide');
    wp_enqueue_style('bricks-splide');
    wp_enqueue_script('bricks-swiper');
    wp_enqueue_style('bricks-swiper');
    wp_enqueue_script('vnx-service-price-name-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx_service_price.js', ['jquery'], '1.0.2', true);
    wp_enqueue_style('vnx-service-price-name-center',  VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/service_price.css', '1.0.2', 'all');

    // đăng ký vue
    wp_register_script('vnx-vue-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vue.js', []);
  }

  public function set_control_groups()
  {
    $this->control_groups['chat_button_settings'] = [
      'title' => esc_html__('Chat Button Settings', 'vietnix'),
      'tab' => 'content',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2','compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->control_groups['register_button_settings'] = [
      'title' => esc_html__('Register Buttons Settings', 'vietnix'),
      'tab' => 'content',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2','compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->control_groups['slide_pagination_settings'] = [
      'title' => esc_html__('Slide Pagination Settings', 'vietnix'),
      'tab' => 'content',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2','compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->control_groups['subtable_settings'] = [
      'title' => esc_html__('Sub-Table Settings', 'vietnix'),
      'tab' => 'content',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2','compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->control_groups['carousel_style_section'] = [
      'title' => esc_html__('Price style', 'vietnix'),
      'tab' => 'content',
      'required' => ['table_style', '=', ['hosting_price_v1', 'compare_hosting_v1', 'compare_hosting_v2', 'multiple_service_hosting', 'banner_hosting', 'list_hosting_price_v1', 'firewall_anti','maxspeed_hosting','compare_ssl','compare_server','table_compare_v1']],
    ];
  }

  public function set_controls()
  {
    $this->controls['table_style'] = [
      'tab' => 'content',
      'label' => esc_html__('Select Table Style', 'vietnix'),
      'type' => 'select',
      'options' => [
        'hosting_price_v1' => esc_html__('Bảng giá Hosting Ver.1', 'vietnix'),
        'list_hosting_price_v1' => esc_html__('Danh sách bảng giá Ver.1', 'vietnix'),
        'compare_hosting_v1' => esc_html__('Bảng So Sánh Hosting Ver.1', 'vietnix'),
        'compare_hosting_v2' => esc_html__('Bảng So Sánh Hosting Ver.2', 'vietnix'),
        'multiple_service_hosting' => esc_html__('Danh Sách Dịch Vụ Hosting Ver.1', 'vietnix'),
        'banner_hosting' => esc_html__('Dịch Vụ Hosting cho Bài viết', 'vietnix'),
        'firewall_anti' => esc_html__('Bảng giá Firewall Ver.1', 'vietnix'),
        'maxspeed_hosting' => esc_html__('Bảng giá MaxSpeed Ver.1', 'vietnix'),
        'compare_ssl' => esc_html__('Bảng So Sánh Giá SSL', 'vietnix'),
        'compare_server' => esc_html__('Bảng So Sánh Giá server', 'vietnix'),
        'table_compare_v1' => esc_html__('Bảng So Sánh Giá Chung Ver.1', 'vietnix'),
      ],
      'inline' => true,
      'placeholder' => esc_html__('Select style', 'vietnix'),
      'multiple' => false,
      'searchable' => true,
      'clearable' => true,
      'default' => '',
    ];

    $this->controls['upload'] = [
      'tab' => 'content',
      'label' => esc_html__('Data File', 'vietnix'),
      'description' => 'Please select the file format is .csv',
      'type' => 'file',
      'required' => ['table_style', '=', ['hosting_price_v1', 'compare_hosting_v1','compare_hosting_v2', 'multiple_service_hosting', 'banner_hosting', 'list_hosting_price_v1','firewall_anti', 'maxspeed_hosting', 'compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->controls['cycle_price'] = [
      'tab' => 'content',
      'label' => esc_html__('Cycle Popular', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('1 Năm', 'vietnix'),
      'default' => __('1 Năm', 'vnx'),
      'description' => 'Chu kỳ phổ biến trong file.',
      'required' => ['table_style', '=', ['hosting_price_v1','list_hosting_price_v1','firewall_anti', 'maxspeed_hosting']],
    ];
    $this->controls['table_key'] = [
      'tab' => 'content',
      'label' => esc_html__('Table Key Name', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Example: maxspeedhosting', 'vietnix'),
      'default' => '',
      'description' => __('The key name for other functions', 'vnx'),
    ];
    $this->controls['service_price'] = [
      'tab' => 'content',
      'label' => esc_html__('Service Popular', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Hosting Cheap 1', 'vietnix'),
      'default' => __('Hosting Cheap 1', 'vnx'),
      'description' => 'Dịch vụ phổ biến trong file.',
      'required' => ['table_style', '=', ['hosting_price_v1', 'compare_hosting_v1', 'compare_hosting_v2','firewall_anti', 'maxspeed_hosting', 'compare_ssl','compare_server','table_compare_v1']],
    ];
    $this->controls['service_label_active'] = [
      'tab' => 'content',
      'label' => esc_html__('Active Label Popular', 'vietnix'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => true,
      'description' => 'Bật/Tắt nhãn dịch vụ phổ biến.',
      'required' => ['table_style', '=', ['hosting_price_v1', 'firewall_anti', 'maxspeed_hosting']],
    ];
    $this->controls['service_label_desktop'] = [
      'tab' => 'content',
      'label' => esc_html__('Popular Lable Desktop', 'vietnix'),
      'type' => 'image',
      'required' => ['table_style', '=', ['firewall_anti', 'maxspeed_hosting']],
    ];
    $this->controls['service_label_mobile'] = [
      'tab' => 'content',
      'label' => esc_html__('Popular Lable Mobile', 'vietnix'),
      'type' => 'image',
      'required' => ['table_style', '=', ['firewall_anti', 'maxspeed_hosting']],
    ];

    $this->controls['loop_slide'] = [
      'tab' => 'content',
      'label' => esc_html__('Select Type Loop', 'vietnix'),
      'type' => 'select',
      'options' => [
        'loop' => esc_html__('Loop', 'vietnix'),
        'slide' => esc_html__('Slide', 'vietnix'),
        'fade' => esc_html__('Fade', 'vietnix'),
      ],
      'inline' => true,
      'placeholder' => esc_html__('Select Type Loop', 'vietnix'),
      'multiple' => false,
      'default' => '',
      'fullAccess' => true,
      'group' => 'carousel_style_section',
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1']],
    ];
    $this->controls['item_start'] = [
      'group' => 'carousel_style_section',
      'label' => esc_html__('Start index', 'vietnix'),
      'type' => 'number',
      'placeholder' => 1,
      'breakpoints' => true,
      'fullAccess' => true,
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1', 'firewall_anti', 'maxspeed_hosting']],
    ];
    $this->controls['item_focus'] = [
      'group' => 'carousel_style_section',
      'label' => esc_html__('Focus item', 'vietnix'),
      'type' => 'text',
      'placeholder' => esc_html__('center', 'vietnix'),
      'breakpoints' => true,
      'fullAccess' => true,
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1']],
    ];
    $this->controls['item_show'] = [
      'group' => 'carousel_style_section',
      'label' => esc_html__('Items to show', 'vietnix'),
      'type' => 'number',
      'placeholder' => 1,
      'breakpoints' => true,
      'fullAccess' => true,
      'default' => 3,
      'required' => ['table_style', '=', ['hosting_price_v1', 'banner_hosting', 'list_hosting_price_v1']],
    ];
    $this->controls['item_show_scroll'] = [
      'tab' => 'content',
      'label' => esc_html__('Item Scroll', 'vietnix'),
      'inline' => true,
      'type' => 'number',
      'placeholder' => 1,
      'default' => 1,
      'group' => 'carousel_style_section',
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1']],
    ];
    $this->controls['spacing_item_show'] = [
      'group' => 'carousel_style_section',
      'label' => esc_html__('Spacing Items', 'vietnix'),
      'type' => 'number',
      'placeholder' => '0px',
      'breakpoints' => true,
      'fullAccess' => true,
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1', 'maxspeed_hosting']],
    ];
    $this->controls['height_item_show'] = [
      'group' => 'carousel_style_section',
      'label' => esc_html__('Height Items', 'vietnix'),
      'type' => 'number',
      'placeholder' => 'auto',
      'breakpoints' => true,
      'fullAccess' => true,
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1']],
    ];
    $this->controls['button_register'] = [
      'tab' => 'content',
      'label' => esc_html__('Button Register', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Type button register here', 'vietnix'),
      'default' => __('Đăng ký ngay', 'vnx'),
      'required' => ['table_style', '=', ['hosting_price_v1', 'banner_hosting', 'list_hosting_price_v1','firewall_anti', 'maxspeed_hosting','compare_hosting_v2','compare_ssl','compare_server','table_compare_v1']],
      'group' => 'carousel_style_section',
    ];
    $this->controls['button_show'] = [
      'group' => 'carousel_style_section',
      'label' => esc_html__('Show button', 'vietnix'),
      'type' => 'checkbox',
      'required' => ['table_style', '=', ['hosting_price_v1', 'banner_hosting', 'list_hosting_price_v1']],
    ];
    $this->controls['list_button_text'] = [
      'tab' => 'content',
      'label' => esc_html__('Button text', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Type button text here', 'vietnix'),
      'default' => __('Click me', 'vnx'),
      'required' => ['table_style', '=', ['hosting_price_v1', 'multiple_service_hosting', 'list_hosting_price_v1']],
      'group' => 'carousel_style_section',
    ];
    $this->controls['list_button_url'] = [
      'tab' => 'content',
      'label' => esc_html__('Button url', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Type button url here', 'vietnix'),
      'required' => ['table_style', '=', ['hosting_price_v1', 'multiple_service_hosting']],
      'group' => 'carousel_style_section',
    ];

    $this->controls['list_button_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Button Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'Fontawesome - Solid',
        'icon' => 'fas fa-cart-shopping',
      ],
      'required' => ['table_style', '=', ['hosting_price_v1', 'banner_hosting', 'list_hosting_price_v1']],
      'group' => 'carousel_style_section',
    ];

    $this->controls['show_arrow'] = [
      'tab' => 'content',
      'label' => esc_html__('Show arrow', 'vietnix'),
      'inline' => true,
      'type' => 'checkbox',
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1']],
      'group' => 'carousel_style_section',
      'css' => [],
    ];

    $this->controls['show_dot'] = [
      'tab' => 'content',
      'label' => esc_html__('Show dot', 'vietnix'),
      'inline' => true,
      'type' => 'checkbox',
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1']],
      'group' => 'carousel_style_section',
      'css' => [],
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
      'required' => ['table_style', '=', ['hosting_price_v1', 'compare_hosting_v1', 'compare_hosting_v2', 'list_hosting_price_v1','firewall_anti', 'maxspeed_hosting', 'compare_ssl','compare_server','table_compare_v1']],
      'group' => 'carousel_style_section',
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
        ['table_style', '=', ['hosting_price_v1', 'compare_hosting_v1', 'compare_hosting_v2', 'list_hosting_price_v1', 'firewall_anti', 'maxspeed_hosting', 'compare_ssl','compare_server','table_compare_v1']],
        ['yes_icon.library', '!=', ['svg']],
      ],
      'group' => 'carousel_style_section',
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
      'required' => ['table_style', '=', ['hosting_price_v1', 'compare_hosting_v1', 'compare_hosting_v2', 'list_hosting_price_v1', 'firewall_anti', 'maxspeed_hosting', 'compare_ssl','compare_server','table_compare_v1']],
      'group' => 'carousel_style_section',
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
      'required' => ['table_style', '=', ['hosting_price_v1', 'compare_hosting_v1', 'compare_hosting_v2', 'list_hosting_price_v1', 'firewall_anti', 'maxspeed_hosting', 'compare_ssl','compare_server','table_compare_v1']],
      'group' => 'carousel_style_section',
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
      'required' => ['table_style', '=', ['hosting_price_v1', 'compare_hosting_v1', 'compare_hosting_v2', 'list_hosting_price_v1', 'firewall_anti', 'maxspeed_hosting', 'compare_ssl','compare_server','table_compare_v1']],
      'group' => 'carousel_style_section',
    ];

    $this->controls['text_chatbutton'] = [
      'tab' => 'content',
      'label' => esc_html__('Text Button Chat', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('Chat với hỗ trợ', 'vietnix'),
      'description' => 'Nội dung nút chat.',
      'group' => 'chat_button_settings',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2', 'compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->controls['class_chatbutton'] = [
      'tab' => 'content',
      'label' => esc_html__('Class Button Chat', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('example_class_1 example_class_2', 'vietnix'),
      'description' => 'Example: example_class_1 example_class_1',
      'group' => 'chat_button_settings',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2', 'compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->controls['register_class_prefix'] = [
      'tab' => 'content',
      'label' => esc_html__('Button Class Prefix', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('vnx_prefix_', 'vietnix'),
      'description' => __('Prefix of Table Key class. Example: class_before vnx_prefix_ -> Register button class is "class_before vnx_prefix_<span style="color:yellow">[table_key]</span><span style="color:#28f95d">[column_order]</span>[suffix]"', 'vietnix'),
      'group' => 'register_button_settings',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2', 'compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->controls['register_class_suffix'] = [
      'tab' => 'content',
      'label' => esc_html__('Button Class Suffix', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('_vnx_suffix', 'vietnix'),
      'description' => __('Suffix of Table Key class. Example: _vnx_suffix class_after -> Register button class is "[prefix]<span style="color:yellow">[table_key]</span><span style="color:#28f95d">[column_order]</span>_vnx_suffix class_after"', 'vietnix'),
      'group' => 'register_button_settings',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2', 'compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->controls['next_arrow_class'] = [
      'tab' => 'content',
      'label' => esc_html__('Next Arrow Class', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('vnx_next', 'vietnix'),
      'description' => __('Additional class for slide next arrow button', 'vietnix'),
      'group' => 'slide_pagination_settings',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2', 'compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->controls['prev_arrow_class'] = [
      'tab' => 'content',
      'label' => esc_html__('Previous Arrow Class', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('vnx_prev', 'vietnix'),
      'description' => __('Additional class for slide previous arrow button', 'vietnix'),
      'group' => 'slide_pagination_settings',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2', 'compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->controls['subtable_keys' ] = [ 
      'tab'           => 'content',
      'label'         => esc_html__( 'Sub-table key names for other functions', 'vietnix' ),
      'type'          => 'repeater',
      'titleProperty' => 'key', // Default 'title'
      'default'       => [ 
        'key' => '',
      ],
      'placeholder'   => esc_html__( 'Sub-table', 'vietnix' ),
      'fields'        => [ 
        'key' => [ 
          'label'   => esc_html__( 'Table Key', 'vietnix' ),
          'type'    => 'text',
          'default' => '',
        ],
      ],
      'group'         => 'subtable_settings',
      'required'      => [ 'table_style', '=', [ 'compare_hosting_v1', 'compare_hosting_v2', 'compare_ssl','compare_server','table_compare_v1' ] ],
    ];

    $this->controls['subtable_viewmore_class'] = [
      'tab' => 'content',
      'label' => esc_html__('View more Class', 'vietnix'),
      'type' => 'text',
      'placeholder' => __('vnx_class', 'vietnix'),
      'description' => __( 'Additional class for view more button. Example: class_before vnx_class_ -> View more button class is "class_before vnx_class_<span style="color:yellow">[subtable_keyname_by_order]</span>"', 'vietnix' ),
      'group' => 'subtable_settings',
      'required' => ['table_style', '=', ['compare_hosting_v1', 'compare_hosting_v2', 'compare_ssl','compare_server','table_compare_v1']],
    ];

    $this->controls['mode_table'] = [
      'tab' => 'content',
      'label' => esc_html__('Dark mode table', 'vietnix'),
      'inline' => true,
      'type' => 'checkbox',
      'description' => 'Chọn bật nếu muốn màu nền của bảng giá là màu tối.',
      'required' => ['table_style', '=', ['compare_hosting_v2']],
    ];

    $this->controls['highlight_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Highlight Icon', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        'icon' => 'fa-solid fa-bolt',
      ],
      'css' => [
        [
          'selector' => '.vnx_highlight_icon',
        ],
      ],
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1', 'maxspeed_hosting']],
      'group' => 'carousel_style_section',
    ];

    $this->controls['tooltip_icon_price'] = [
      'tab' => 'content',
      'label' => esc_html__('Tooltip Icon Price', 'vietnix'),
      'type' => 'icon',
      'default' => [
        'library' => 'fontawesomeSolid',
        'icon' => 'fa-solid fa-bolt',
      ],
      'css' => [
        [
          'selector' => '.vnx_tooltip_icon_price',
        ],
      ],
      'required' => ['table_style', '=', ['maxspeed_hosting', 'compare_hosting_v2','table_compare_v1']],
      'group' => 'carousel_style_section',
    ];

    $this->controls['highlight_icon_color_pp'] = [
      'tab' => 'content',
      'label' => esc_html__('Highlight Icon Color Popular', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx-price-special .vnx_highlight_icon',
        ],
      ],
      'default' => [
        'hex' => '#3ce77b',
        'rgb' => 'rgba(60, 231, 123, 0.9)',
      ],
      'required' => ['table_style', '=', ['hosting_price_v1', 'maxspeed_hosting']],
      'group' => 'carousel_style_section',
    ];
    $this->controls['highlight_icon_color_reg'] = [
      'tab' => 'content',
      'label' => esc_html__('Highlight Icon Color Regular', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx_highlight_icon',
        ],
      ],
      'default' => [
        'hex' => '#3ce77b',
        'rgb' => 'rgba(60, 231, 123, 0.9)',
      ],
      'required' => ['table_style', '=', ['hosting_price_v1', 'list_hosting_price_v1', 'maxspeed_hosting']],
      'group' => 'carousel_style_section',
    ];

    $this->controls['highlight_lable'] = [
      'tab' => 'content',
      'label' => esc_html__('Highlight Lable', 'vietnix'),
      'type' => 'image',
      'required' => ['table_style', '=', ['hosting_price_v1','compare_hosting_v1', 'compare_hosting_v2', 'firewall_anti', 'maxspeed_hosting', 'compare_ssl','compare_server','table_compare_v1']],
      'group' => 'carousel_style_section',
    ];
   

    $this->controls['text_cycle_price'] = [
      'tab' => 'content',
      'label' => esc_html__('Text cycle price', 'vietnix'),
      'type' => 'text',
      'default' => '/Năm',
      'placeholder' => '/Năm',
      'required' => ['table_style', '=', ['compare_server']],
      'group' => 'carousel_style_section',
    ];

    $this->controls['text_unit_price'] = [
      'tab' => 'content',
      'label' => esc_html__('Text Unit Price', 'vietnix'),
      'type' => 'text',
      'default' => '/tháng',
      'placeholder' => '/tháng',
      'required' => ['table_style', '=', ['table_compare_v1']],
      'group' => 'carousel_style_section',
    ];
    $this->controls['text_unit_price_mb'] = [
      'tab' => 'content',
      'label' => esc_html__('Text Unit Price Mobile', 'vietnix'),
      'type' => 'text',
      'default' => '/th',
      'placeholder' => '/th',
      'required' => ['table_style', '=', ['table_compare_v1']],
      'group' => 'carousel_style_section',
    ];
  }

  public function render()
  {
    $settings = $this->settings;
    $table_style = isset($settings['table_style']) ? $settings['table_style'] : '';
    $group_1 = array('compare_hosting_v1', 'compare_hosting_v2', 'multiple_service_hosting','firewall_anti','maxspeed_hosting', 'compare_ssl','compare_server','table_compare_v1');
    $post_style = array('banner_hosting');
    if (in_array($table_style, $group_1)) {
      View::render("widgets/bricks/vnx-service/" . $table_style, $this);
    } elseif (in_array($table_style, $post_style)){
      View::render("widgets/bricks/vnx-service/post/" . $table_style, $this);
    } else {
      View::render("widgets/bricks/vnx-service-price", $this);
    }
  }

  public function get_upload_file_data()
  {
    try{
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
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }
  public function getToSearchExcelCompare(int $star, int $end, $array)
  {
    if (is_array($array) && !empty($star) && !empty($end)) {
      $cycle_data = array();
      for ($i = $star + 1; $i < $end; $i++) {
        if (!empty($array[$i])) {
          $cycle_data[] = $array[$i];
        }
      }
      return $cycle_data;
    }
  }
  public function find_second_occurrence($array, $value)
  {
    $keys = array_keys($array, $value);
    if (count($keys) < 2) {
      return false;
    } else {
      return $keys[1];
    }
  }
  public function findCycleBoundaries(array $array)
  {
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
      return $boundaries;
    }
  }
  function getToSearchExcel(string $star, string $end, $array, $count)
  {
    if (is_array($array) && !empty($star) && !empty($end) && !empty($count)) {
      $cycStart = array_search($star, $array);
      $cycEnd = array_search($end, $array);
      $count_row = count($count);
      $cycle_data = array();
      for ($i = 1; $i < $count_row; $i++) {
        if ($i > $cycStart && $i < $cycEnd) {
          if (!empty($array[$i])) {
            array_push($cycle_data, $array[$i]);
          }
        }
      }
      return $cycle_data;
    }
  }
  function getParaminRow(string $value){
    $arr = explode(" | ", $value);
    return $arr;
  }
}