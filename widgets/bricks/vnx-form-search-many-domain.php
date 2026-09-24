<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Form_Search_Many_Domain_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-form-search-many-domain';
  public $icon = 'ion-md-search';
  public $scripts = ['vnxDomainSearchRedirect'];

  public function get_label()
  {
    return esc_html__('VNX Form Search Many Domain ', 'vietnix');
  }

  public function enqueue_scripts()
  {
    wp_register_script('ajax_post_use_all-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/ajax_post_use_all.js');
    wp_enqueue_script('ajax_post_use_all-center');
    wp_register_script('form_search_many_domain-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/form_search_many_domain.js');
    wp_enqueue_script('form_search_many_domain-center');
    wp_register_script('xlsx-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/xlsx.min.js');
    wp_enqueue_script('xlsx-center');

    wp_register_style('form_search_many_domain_css-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/form_search_many_domain.css');
    wp_enqueue_style('form_search_many_domain_css-center');
  }
  public function set_control_groups()
  {
    $this->control_groups['custom_link'] = [
      'title' => esc_html__('Custom Link', 'vietnix'),
      'tab'   => 'content',
    ];
    $this->control_groups['style_form_search'] = [
      'title' => esc_html__('Style form search ', 'vietnix'),
      'tab'   => 'content',
    ];
    $this->control_groups['style_button'] = [
      'title' => esc_html__('Style button', 'vietnix'),
      'tab'   => 'content',
    ];
    $this->control_groups['style_button_import'] = [
      'title' => esc_html__('Style button import', 'vietnix'),
      'tab'   => 'content',
    ];
    $this->control_groups['style_text_on_form'] = [
      'title' => esc_html__('Style text on form', 'vietnix'),
      'tab'   => 'content',
    ];
  }

  public function set_controls()
  {
    $this->controls['form_style'] = [
      'tab'         => 'content',
      'label'       => esc_html__('Select Form Style', 'vietnix'),
      'type'        => 'select',
      'options'     => [
        'form_search_many_domain' => esc_html__('Tìm kiếm nhiều tên miền', 'vietnix'),
        'whois_domain' => esc_html__('Whois nhiều tên miền', 'vietnix'),
      ],
      'inline'      => true,
      'placeholder' => esc_html__('Select style', 'vietnix'),
      'multiple'    => false,
      'searchable'  => true,
      'clearable'   => true,
      'default'     => '',
    ];

    $this->controls['default_tld_whois'] = [
      'tab'         => 'content',
      'label'       => esc_html__('Default TLD', 'vietnix'),
      'type'        => 'select',
      'options'     => [
        'vn' => esc_html__('.vn', 'textdomain'),
        'com'  => esc_html__('.com', 'textdomain'),
        'net' => esc_html__('.net', 'textdomain'),
        'info' => esc_html__('.info', 'textdomain'),
      ],
      'inline'      => true,
      'placeholder' => esc_html__('Select default tld', 'vietnix'),
      'multiple'    => false,
      'searchable'  => true,
      'clearable'   => false,
      'default'     => 'vn',
      'required' => ['form_style', '=', 'whois_domain'],
    ];

    $this->controls['link_redirect'] = [
      'tab' => 'content',
      'label' => esc_html__('Link Redirect', 'vietnix'),
      'placeholder' => esc_html__('Nhập link chuyển hướng redirect', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => '',
    ];
    $this->controls['redirectInfo'] = [
      'tab'      => 'content',
      'content'  => __('Redirect tới trang kết quả sau khi submit form, cần điền URL trang kết quả. Lưu ý: <b style="color:yellow">Có / ở cuối + Không điền params</b>', 'vietnix'),
      'type'     => 'info',
      'required' => ['form_style', '=', 'form_search_many_domain'],
    ];
    $this->controls['link_file_domain'] = [
      'tab' => 'content',
      'label' => esc_html__('Link file mẫu domain', 'vietnix'),
      'placeholder' => esc_html__('Nhập link file mẫu', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => '',
    ];
    $this->controls['show_form_search'] = [
      'tab' => 'content',
      'label' => esc_html__('Show form', 'bricks'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => true,
    ];
    // group custom link
    $this->controls['custom_link_register'] = [
      'tab' => 'content',
      'group' => 'custom_link',
      'label' => esc_html__('Link đăng ký', 'vietnix'),
      'placeholder' => esc_html__('Nhập link đăng ký', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => '/',
    ];
    $this->controls['custom_link_whois'] = [
      'tab' => 'content',
      'group' => 'custom_link',
      'label' => esc_html__('Link xem Whois', 'vietnix'),
      'placeholder' => esc_html__('Nhập link whois', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => '/',
    ];
    // group style form search
    $this->controls['style_form_search_color'] = [
      'tab' => 'content',
      'group' => 'style_form_search',
      'label' => esc_html__('Background form', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'background',
          'selector' => '.vnx-bgform-search',
        ]
      ],
    ];
    $this->controls['style_form_search_color_input'] = [
      'tab' => 'content',
      'group' => 'style_form_search',
      'label' => esc_html__('Background input', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'background-color',
          'selector' => '#vnx-textarea-search',
        ]
      ],
    ];
    $this->controls['style_form_search_border'] = [
      'tab' => 'content',
      'group' => 'style_form_search',
      'label' => esc_html__('Border radius form', 'vietnix'),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => '.vnx-bgform-search',
        ],
      ],
      'inline' => true,
      'small' => true,
      'default' => [
        'width' => [
          'top' => 1,
          'right' => 0,
          'bottom' => 0,
          'left' => 0,
        ],
        'style' => 'solid',
        'color' => [
          'hex' => '#ffff00',
        ],
        'radius' => [
          'top' => 1,
          'right' => 1,
          'bottom' => 1,
          'left' => 1,
        ],
      ],
    ];
    // group style button
    $this->controls['style_form_search_color_button'] = [
      'tab' => 'content',
      'group' => 'style_button',
      'label' => esc_html__('Background Button', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'background-color',
          'selector' => 'button.submmit_form_search',
        ]
      ],
    ];
    $this->controls['style_form_search_button_color_text'] = [
      'tab' => 'content',
      'group' => 'style_button',
      'label' => esc_html__('Color text button', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => 'button.submmit_form_search',
        ]
      ],
    ];
    $this->controls['style_form_search_button_border'] = [
      'tab' => 'content',
      'group' => 'style_button',
      'label' => esc_html__('Border radius button', 'vietnix'),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => 'button.submmit_form_search',
        ],
      ],
      'inline' => true,
      'small' => true,
      'default' => [
        'width' => [
          'top' => 1,
          'right' => 0,
          'bottom' => 0,
          'left' => 0,
        ],
        'style' => 'solid',
        'color' => [
          'hex' => '#ffff00',
        ],
        'radius' => [
          'top' => 1,
          'right' => 1,
          'bottom' => 1,
          'left' => 1,
        ],
      ],
    ];
    // group style button import
    $this->controls['style_button_import_color'] = [
      'tab' => 'content',
      'group' => 'style_button_import',
      'label' => esc_html__('Background Button import', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'background-color',
          'selector' => '#open-popup',
        ]
      ],
    ];
    $this->controls['style_button_import_text_color'] = [
      'tab' => 'content',
      'group' => 'style_button_import',
      'label' => esc_html__('Color text button import', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '#open-popup',
        ]
      ],
    ];
    $this->controls['style_button_import_border'] = [
      'tab' => 'content',
      'group' => 'style_button_import',
      'label' => esc_html__('Border radius button import', 'vietnix'),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => '#open-popup',
        ],
      ],
      'inline' => true,
      'small' => true,
      'default' => [
        'width' => [
          'top' => 1,
          'right' => 0,
          'bottom' => 0,
          'left' => 0,
        ],
        'style' => 'solid',
        'color' => [
          'hex' => '#ffff00',
        ],
        'radius' => [
          'top' => 1,
          'right' => 1,
          'bottom' => 1,
          'left' => 1,
        ],
      ],
    ];
    // group style text on form
    $this->controls['style_text_on_form_color_text'] = [
      'tab' => 'content',
      'group' => 'style_text_on_form',
      'label' => esc_html__('Color text on form', 'vietnix'),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx-custom-result-domain',
        ],
        [
          'property' => 'color',
          'selector' => '.vnx-custom-title-popup',
        ],
        [
          'property' => 'color',
          'selector' => '#open-popup-extension span',
        ],
        [
          'property' => 'color',
          'selector' => '#deleteDataTextarea',
        ],
        [
          'property' => 'color',
          'selector' => '.hidden_tooltip_input_search',
        ],

      ],
    ];
  }

  public function render()
  {
    View::render("widgets/bricks/vnx-form-search-many-domain", $this);
  }
}
