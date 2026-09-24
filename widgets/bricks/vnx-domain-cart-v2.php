<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Domain_Cart_v2_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-domain-cart-v2';
  public $icon = 'ion-md-cart';

  public function get_label()
  {
    return esc_html__('VNX Domain Cart V2', 'vietnix');
  }

  public function enqueue_scripts()
  {

    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_register_script('vnx-domain-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_handle/vnx_domain.js', ['jquery'], '1.1', true);
    wp_enqueue_script('vnx-domain-center');
    wp_register_script('vnx-domain-cart-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_handle/vnx_box_cart.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vnx-domain-cart-center');
  }

  public function set_controls()
  {
    $this->controls['type_cart'] = [
      'tab' => 'content',
      'label' => esc_html__('Type Cart', 'vietnix'),
      'type' => 'select',
      'inline' => true,
      'small' => true,
      'default' => 'default',
      'options' => [
        'default' => esc_html__('Default', 'vietnix'),
        'cart-v1' => esc_html__(' New Layout Cart V1', 'vietnix'),
      ],
    ];

    $this->controls['link_to_portal'] = [
      'tab' => 'content',
      'label' => esc_html__('Link to portal', 'vietnix'),
      'type' => 'link',
      'pasteStyles' => false,
      'placeholder' => esc_html__('https://portal.vietnix.vn', 'vietnix'),
    ];

    $this->controls['cart_cookie_age'] = [
      'tab' => 'content',
      'label' => esc_html__('Cart Cookie Age', 'vietnix'),
      'type' => 'number',
      'min' => 1,
      'step' => '1', // Default: 1
      'inline' => true,
      'default' => 30,
    ];

    $this->controls['show_sticky_cart_mobile'] = [
      'tab' => 'content',
      'label' => esc_html__('Show Mobile Sticky Cart', 'vietnix'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => true, // Default: false
    ];
    $this->controls['icon_cart_domain'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon Cart Domain', 'vietnix'),
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
      'required' => ['type_cart', '=', ['cart-v1']],
    ];

    $this->controls['icon_cart_domain_buy'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon Cart Domain Buy', 'vietnix'),
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
      'required' => ['type_cart', '=', ['cart-v1']],
    ];
    $this->controls['icon_cart_domain_trash'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon Cart Domain Trash', 'vietnix'),
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
      'required' => ['type_cart', '=', ['cart-v1']],
    ];
  }

  public function render()
  {
    $type_cart = $this->settings['type_cart'] ?? 'default';
    $my_class = ['vnx_element', $type_cart];
    $this->set_attribute('_root', 'class', $my_class);
    echo "<div {$this->render_attributes('_root')}>";
    if ($type_cart == 'default') {
      View::render("widgets/bricks/vnx-domain-cart-v2", $this);
    } else {
      View::render("widgets/bricks/domain/vnx-cart/" . $type_cart, $this);
    }

    echo '</div>';
  }
}
