<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use HelperCenter\View;

class Vnx_layer_Toggle_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name     = 'vnx-layer-toggle';
  public $icon     = 'fa-solid fa-layer-group';

  public function get_label()
  {
    return esc_html__('VNX Layer Toggle', 'bricks');
  }
  public function set_control_groups()
  {
    $this->control_groups['style_image'] = [
      'title' => esc_html__('Style Image', 'vietnix'),
      'tab'   => 'content',
    ];
  }
  public function set_controls()
  {
    $this->controls['layer_toggle'] = [
      'tab' => 'content',
      'label' => esc_html__('List item', 'vietnix'),
      'type' => 'repeater',
      'titleProperty' => 'title', // Default 'title'
      'default' => [
        [
          'title' => 'Item 1',
          'description' => 'Here goes the description for repeater item.',
        ],
      ],
      'placeholder' => esc_html__('Title placeholder', 'vietnix'),
      'fields' => [
        'title' => [
          'label' => esc_html__('Title', 'vietnix'),
          'type' => 'text',
        ],
        'description' => [
          'label' => esc_html__('Description', 'vietnix'),
          'type' => 'textarea',
        ],
        'image' => [
          'label' => esc_html__('Image', 'vietnix'),
          'type' => 'image',
        ],
      ],
    ];
    $this->controls['image_width'] = [
      'tab' => 'content',
      'group' => 'style_image',
      'label' => esc_html__('Image width', 'vietnix'),
      'type' => 'number',
      'unit' => 'px',
      'css' => [
        [
          'property' => 'width',
          'selector' => '.vnx-layer-toggle-image-wrap',
          'important' => true,
        ],
      ],
      'default' => '60px',
      'placeholder' => '60px',
    ];


    $this->controls['image_height'] = [
      'tab' => 'content',
      'group' => 'style_image',
      'label' => esc_html__('Image height', 'vietnix'),
      'type' => 'number',
      'unit' => 'px',
      'css' => [
        [
          'property' => 'height',
          'selector' => '.vnx-layer-toggle-image-wrap',
          'important' => true,
        ],
      ],
      'default' => '60px',
      'placeholder' => '60px',
    ];
  }

  public function render()
  {
    View::render("widgets/bricks/vnx-layer-toggle", $this);
  }
}
