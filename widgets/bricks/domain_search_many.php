<?php
if ( !defined( 'ABSPATH' ) )
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Domain_Search_Many_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-domain-search-many';
  public $icon = 'ion-md-globe';
  // public $scripts = [ 'vnxDomainSearchRedirect' ];

  public function get_label()
  {
    return esc_html__( 'VNX Many Domain Search', 'vietnix' );
  }

  public function enqueue_scripts()
  {
    wp_register_script( 'ajax_post_use_all-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/ajax_post_use_all.js', [ 'jquery' ], '1.0', true );
    wp_enqueue_script( 'ajax_post_use_all-center' );

    wp_register_script( 'table_result_search_many_domain-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/search_many_domain.js', [ 'jquery', 'ajax_post_use_all-center' ], '1.0', true );
    wp_enqueue_script( 'table_result_search_many_domain-center' );

    wp_register_style( 'table_search_many_domain_css-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/table_search_many_domain.css', [ 'bricks-frontend' ], '1.0', 'all' );
    wp_enqueue_style( 'table_search_many_domain_css-center' );

    wp_register_style('vnx_table_price-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/table_price.css');
  }

  public function set_control_groups()
  {
    $this->control_groups['whois'] = [
      'title' => esc_html__('Whois', 'vietnix'),
      'tab' => 'content',
      'required' => [ 'element_type', '=', 'whois_domain' ],
    ];
  }

  public function set_controls()
  {
    $this->controls[ 'elementInfo' ] = [ 
      'tab'     => 'content',
      'content' => __( 'Bảng giá TLD tải lên tại menu <b style="color:yellow">Services Data</b> trong Admin page', 'vietnix' ),
      'type'    => 'info',
    ];

    $this->controls[ 'element_type' ] = [ 
      'tab'         => 'content',
      'label'       => esc_html__( 'Select Content Type', 'vietnix' ),
      'type'        => 'select',
      'options'     => [ 
        'search_domain' => esc_html__( 'Tìm kiếm nhiều tên miền', 'vietnix' ),
        'whois_domain' => esc_html__( 'Whois nhiều tên miền', 'vietnix' ),
      ],
      'inline'      => true,
      'placeholder' => esc_html__( 'Select Type', 'vietnix' ),
      'multiple'    => false,
      'searchable'  => true,
      'clearable'   => true,
      'default'     => 'search_domain',
    ];
    $this->controls[ 'search_domainInfo' ] = [ 
      'tab'      => 'content',
      'content'  => __( 'Type hiện tại dùng cho trang Kết quả tìm kiếm nhiều tên miền.', 'vietnix' ),
      'type'     => 'info',
      'required' => [ 'element_type', '=', 'search_domain' ],
    ];
    $this->controls['back_portal'] = [
      'tab' => 'content',
      'label' => esc_html__( 'Back to portal', 'vnx' ),
      'type' => 'checkbox',
      'inline' => true,
      'small' => false,
      'default' => true,
      'required' => ['element_type', '=', 'whois_domain'],
      'group' => 'whois',
    ];

    $this->controls['vnx_link_portal_redirect'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Link to portal', 'vnx' ),
      'type'        => 'link',
      'pasteStyles' => false,
      'default' => [
        'type' => 'external',
        'url' => 'https://portal.stag.vietnix.dev/cart.php?a=view'
      ],
      'required' => [ ['back_portal', '=', true], ['element_type', '=', 'whois_domain'] ],
      'group' => 'whois',
    ];

    $this->controls['vnx_link_see_whois'] = [
      'tab'         => 'content',
      'label'       => esc_html__( 'Link whois', 'vnx' ),
      'type'        => 'link',
      'pasteStyles' => false,
      'default' => [
        'type' => 'external',
        'url' => 'https://vietnix.vn/whois?domain='
      ],
      'required' => [ ['element_type', '=', 'whois_domain'] ],
      'group' => 'whois',
    ];


  }

  public function render()
  {
    if ( isset( $this->settings['back_portal'] ) ) {
      $this->set_link_attributes( 'a', $this->settings['back_portal'] );
    }
    View::render( "widgets/bricks/vnx-domain-search-many", $this );
  }

  public function get_tld_file_data()
  {
    $csvdata = [];
    $file = '';

    // $link = $link;
    if ( !function_exists( 'get_field' ) ) {
      $message = "Advance Custom Fields plugin is not activated";
      return array(
        'status'  => 'error',
        'message' => $message,
      );
    }
    $link = get_field( 'tld_data', 'option' ) ?? '';
    if ( !$link ) {
      $message = "Have no TLD Data file uploaded in Services Data ( Admin menu )";
      return array(
        'status'  => 'error',
        'message' => $message,
      );
    }
    // Biểu thức chính quy để lấy từ "wp-content" đến cuối link
    $regex = '/wp-content\/(.*)/';
    // Sử dụng preg_match để tìm kiếm
    if ( preg_match( $regex, $link, $matches ) ) {
      // $matches[0] sẽ chứa toàn bộ phần match
      $file = ABSPATH . $matches[ 0 ];
    } else {
      $message = "Can't replace domain form File URL<br/>";
      $message .= 'File URL form $settings: ' . $link . '<br/>';
      $message .= 'File URL after replace: ' . $file . '<br/>';
      return array(
        'status'  => 'error',
        'message' => $message,
      );
    }

    if ( !file_exists( $file ) ) {
      $message = 'CSV File not found.<br/>';
      $message .= 'File URL form $settings: ' . $link . '<br/>';
      $message .= 'File URL after replace: ' . $file . '<br/>';
      return array(
        'status'  => 'error',
        'message' => $message,
      );
    }
    $handle = fopen( $file, "r" );
    if ( !$handle ) {
      $message = 'File open failed.<br/>';
      $message .= 'File URL form $settings: ' . $link . '<br/>';
      $message .= 'File URL after replace: ' . $file . '<br/>';
      return array(
        'status'  => 'error',
        'message' => $message,
      );
    }
    while ( ( $line = fgetcsv( $handle ) ) !== false ) {
      array_push( $csvdata, $line );
    }
    fclose( $handle );
    return array(
      'status' => 'success',
      'data'   => $csvdata,
    );
  }
}