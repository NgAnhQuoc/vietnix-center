<?php
if ( !defined( 'ABSPATH' ) )
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Domain_Result_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-domain-result';
  public $icon = 'ion-md-book';
  // public $scripts = [ 'vnxDomainSearchRedirect' ];

  public function get_label()
  {
    return esc_html__( 'VNX Domain Result', 'vietnix' );
  }

  public function enqueue_scripts()
  {
    wp_register_script( 'cookie_func-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/cookie_func.js', [ 'jquery' ], '1.0', true );
    wp_enqueue_script( 'cookie_func-center' );

    wp_register_script( 'domain_cart-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_cart.js', [ 'jquery' ], '1.0', true );
    wp_enqueue_script( 'domain_cart-center' );

    wp_register_script( 'domain_checking-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_checking.js', [ 'jquery' ], '1.0', true );
    wp_enqueue_script( 'domain_checking-center' );

    wp_register_script( 'vnx_export_csv-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx_export_csv.js', [ 'jquery' ], '1.0', true );
    wp_enqueue_script( 'vnx_export_csv-center' );

    wp_register_script( 'whois_check-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/whois_check.js', array( 'jquery', 'domain_checking-center' ), true );

  }

  public function set_controls()
  {
    // $this->controls[ 'elementInfo' ] = [ 
    //   'tab'     => 'content',
    //   'content' => __( 'Element này để chứa kết quả tìm kiếm tên miền, không có settings gì cả', 'vietnix' ),
    //   'type'    => 'info',
    // ];
    $this->controls[ 'result_style' ] = [ 
      'tab'         => 'content',
      'label'       => esc_html__( 'Select Result Style', 'vietnix' ),
      'type'        => 'select',
      'options'     => [ 
          'domain_result'   => esc_html__( 'Domain search', 'vietnix' ),
          'whois_landing_page' => esc_html__( 'Whois Landingpage', 'vietnix' ),
          'whois_result_page'  => esc_html__( 'Whois Result Page', 'vietnix' ),
          'whois_landing_suggest' => esc_html__( 'Whois Suggest Landingpage', 'vietnix' ),
          'whois_result_suggest'  => esc_html__( 'Whois Suggest Result Page', 'vietnix' ),
      ],
      'inline'      => true,
      'placeholder' => esc_html__( 'Select style', 'vietnix' ),
      'multiple'    => false,
      'searchable'  => true,
      'clearable'   => true,
      'default'     => 'domain_result',
  ];
  }

  public function render()
  {
    $result_style = isset( $this->settings['result_style'] ) && $this->settings['result_style'] ? $this->settings['result_style'] : 'domain_result';
    $domain_whois = ['whois_result_page','whois_landing_page'];
    $domain_whois_suggest = ['whois_result_suggest','whois_landing_suggest'];
    if($result_style == 'domain_result'){
      $my_class = [ 'vnx_element' ];
      $this->set_attribute( '_root', 'class', $my_class );

      echo "<div {$this->render_attributes( '_root' )}>";
      View::render( "widgets/bricks/vnx-domain-result", $this );
      echo '</div>';
    }
    else if(in_array($result_style, $domain_whois)){
      echo "<div {$this->render_attributes( '_root' )}>";
      View::render( "widgets/bricks/domain/vnx-whois-result", $this );
      echo '</div>';
    }
    else if(in_array($result_style, $domain_whois_suggest)){
      echo "<div {$this->render_attributes( '_root' )}>";
      View::render( "widgets/bricks/domain/vnx-whois-suggest", $this );
      echo '</div>';
    }
    // $my_class = [ 'vnx_element' ];
    // $this->set_attribute( '_root', 'class', $my_class );

    // echo "<div {$this->render_attributes( '_root' )}>";
    // View::render( "widgets/bricks/vnx-domain-result", $this );
    // echo '</div>';
    // echo $result_style;
  }

}