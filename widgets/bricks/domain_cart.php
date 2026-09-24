<?php
if ( !defined( 'ABSPATH' ) )
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Domain_Cart_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-domain-cart';
  public $icon = 'ion-md-cart';
  // public $scripts = [ 'vnxDomainSearchRedirect' ];

  public function get_label()
  {
    return esc_html__( 'VNX Domain Cart', 'vietnix' );
  }

  public function enqueue_scripts()
  {
    wp_register_script( 'cookie_func-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/cookie_func.js', [ 'jquery' ], '1.0', true );
    wp_enqueue_script( 'cookie_func-center' );

    wp_register_script( 'domain_cart-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_cart.js', [ 'jquery' ], '1.0', true );
    wp_enqueue_script( 'domain_cart-center' );
  }

  public function set_controls()
  {
    $this->controls[ 'link_to_portal' ] = [ 
      'tab'         => 'content',
      'label'       => esc_html__( 'Link to portal', 'vietnix' ),
      'type'        => 'link',
      'pasteStyles' => false,
      'placeholder' => esc_html__( 'https://portal.vietnix.vn', 'vietnix' ),
    ];

    $this->controls[ 'cart_cookie_age' ] = [ 
      'tab'     => 'content',
      'label'   => esc_html__( 'Cart Cookie Age', 'vietnix' ),
      'type'    => 'number',
      'min'     => 1,
      'step'    => '1', // Default: 1
      'inline'  => true,
      'default' => 30,
    ];

    $this->controls[ 'show_sticky_cart_mobile' ] = [ 
      'tab'     => 'content',
      'label'   => esc_html__( 'Show Mobile Sticky Cart', 'vietnix' ),
      'type'    => 'checkbox',
      'inline'  => true,
      'small'   => true,
      'default' => true, // Default: false
    ];
  }

  public function render()
  {
    $my_class = [ 'vnx_element' ];
    $this->set_attribute( '_root', 'class', $my_class );

    echo "<div {$this->render_attributes( '_root' )}>";
    View::render( "widgets/bricks/vnx-domain-cart", $this );
    echo '</div>';
  }
}