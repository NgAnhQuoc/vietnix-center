<?php

if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Post_View_Counter_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-post-view-counter';
  public $icon = 'fa-solid fa-eye';

  public function get_label()
  {
    return esc_html__('VNX Post View Counter', 'vnx');
  }

  public function set_controls()
  {
    $this->controls['label'] = [
      'tab' => 'content',
      'label' => esc_html__('Label', 'vnx'),
      'type' => 'text',
      'spellcheck' => true,
      // Default: false
      // 'trigger' => 'enter', // Default: 'enter'
      'inlineEditing' => true,
      'default' => 'Lượt xem',
    ];
    $this->controls['icon'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon', 'vnx'),
      'type' => 'icon',
      'css' => [
        [
          'selector' => '&.vnx-view-icon',
          // NOTE: Undocumented: & = no space (add to element root)
        ],
      ],
      'default' => [
        'library' => 'fontawesome',
        'icon' => 'fa-solid fa-eye',
      ],
    ];
    $this->controls['iconColor'] = [
      'tab' => 'content',
      'label' => esc_html__('icon Color', 'vnx'),
      'type' => 'color',
      'css' => [
        [
          'property' => 'color',
          'selector' => '.vnx-view-icon'
        ],
      ],
      'required' => ['icon.icon', '!=', ''],
    ];

    $this->controls['iconSize'] = [
      'tab' => 'content',
      'label' => esc_html__('Size', 'vnx'),
      'type' => 'number',
      'units' => true,
      'css' => [
        [
          'property' => 'font-size',
          'selector' => '.vnx-view-icon'
        ],
      ],
      'placeholder' => '60px',
      'required' => ['icon.icon', '!=', ''],
    ];

    $this->controls['_typography']['placeholder']['font-size'] = 60;
    $this->controls['_typography']['placeholder']['line-height'] = 1;
  }

  public function render()
  {
    View::render("widgets/bricks/vnx-post-view-counter", $this);
  }
}