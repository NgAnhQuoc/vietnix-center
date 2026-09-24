<?php

if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Breadcrumbs_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-breadcrumbs';
  public $icon = 'fa-solid fa-house-chimney';

  public $css_selector = '&.vnx-breadcrumbs';
  public function get_label()
  {
    return esc_html__('VNX Breadcrumbs', 'vnx');
  }

  public function set_controls()
  {
    $this->controls['home_text'] = [
      'tab' => 'content',
      'label' => esc_html__('Home text', 'vnx'),
      'type' => 'text',
      'spellcheck' => true,
      // 'trigger' => 'enter', // Default: 'enter'
      'inlineEditing' => true,
      'default' => 'Home',
    ];

    $this->controls['home_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Home Icon', 'bricks'),
      'type' => 'icon',
      'css' => [
        [
          'selector' => '.vnx_home_icon',
          // NOTE: Undocumented: & = no space (add to element root)
        ],
      ],
      'default' => [
        'library' => 'ionicons',
        'icon' => 'ion-ios-home',
      ],
    ];
    $this->controls['delimiter_icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Delimiter Icon', 'bricks'),
      'type' => 'icon',
      'css' => [
        [
          'selector' => '.vnx_delimiter_icon',
          // NOTE: Undocumented: & = no space (add to element root)
        ],
      ],
      'default' => [
        'library' => 'fontawesome',
        'icon' => 'fas fa-angle-right',
      ],
    ];

    $this->controls['iconsize'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon Size', 'bricks'),
      'type' => 'number',
      'units' => true,
      'css' => [
        [
          'property' => 'font-size',
          'selector' => '.vnx_breadcrumbs_icon'
        ],
      ],
      'placeholder' => '20px',
      'default' => '20px'
    ];

    $this->controls['_typography']['placeholder']['font-size'] = 24;
  }

  public function render()
  {
    View::render("widgets/bricks/vnx-breadcrumbs", $this);
  }
}