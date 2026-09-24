<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Search_Domain_Form_Center extends \Bricks\Element
{
    public $category = 'vietnix';
    public $name = 'vnx-search-domain-form';
    public $icon = 'ion-md-search';
    public $scripts = [ 
        'vnxDomainSearchRedirect',
        'vnxDomainSearchOnpage',
        'vnxDomainSearchPageWhois',
    ];

    public function get_label()
    {
        return esc_html__( 'VNX Search Domain Form', 'vietnix' );
    }

    public function enqueue_scripts()
    {
        wp_register_script( 'vnx-domain-functions-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_functions.js', [ 'jquery' ], '1.0', true );
        wp_enqueue_script( 'vnx-domain-functions-center' );

        wp_register_script( 'vnx-bricks-domain-search-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_search_form.js', [ 'jquery', 'vnx-domain-functions-center' ], '1.0', true );
        wp_enqueue_script( 'vnx-bricks-domain-search-center' );

        wp_register_style( 'vnx-bricks-domain-search-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/domain_search_form.css', [ 'bricks-frontend' ], '1.0', 'all' );
        wp_enqueue_style( 'vnx-bricks-domain-search-center' );

        wp_register_style( 'vnx-bricks-whois-domain-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/whois_check.css', [ 'bricks-frontend' ], '1.0', 'all' );
    }

    public function set_control_groups()
    {
        $this->control_groups[ 'form_content' ] = [ 
            'title' => esc_html__( 'Form Content', 'vietnix' ),
            'tab'   => 'content',
        ];
    }

    public function set_controls()
    {
        $this->controls[ 'form_style' ] = [ 
            'tab'         => 'content',
            'label'       => esc_html__( 'Select Form Style', 'vietnix' ),
            'type'        => 'select',
            'options'     => [ 
                'redirect' => esc_html__( 'Redirect', 'vietnix' ),
                'onpage'   => esc_html__( 'On page', 'vietnix' ),
                'whois_landing_page' => esc_html__( 'Whois Landingpage', 'vietnix' ),
                'whois_result_page'  => esc_html__( 'Whois Result Page', 'vietnix' ),
            ],
            'inline'      => true,
            'placeholder' => esc_html__( 'Select style', 'vietnix' ),
            'multiple'    => false,
            'searchable'  => true,
            'clearable'   => true,
            'default'     => '',
        ];
        $this->controls[ 'redirectInfo' ] = [ 
            'tab'      => 'content',
            'content'  => __( 'Redirect tới trang kết quả sau khi submit form, cần điền URL trang kết quả. Lưu ý: <b style="color:yellow">Có / ở cuối + Không điền params</b>', 'vietnix' ),
            'type'     => 'info',
            'required' => [ 'form_style', '=', 'redirect' ],
        ];

        $this->controls[ 'onPageInfo' ] = [ 
            'tab'      => 'content',
            'content'  => __( 'Trả kết quả ở trang hiện tại, cần có Element <b style="color:yellow">VNX Domain Result</b> để có thể trả kết quả', 'vietnix' ),
            'type'     => 'info',
            'required' => [ 'form_style', '=', 'onpage' ],
        ];

        $this->controls[ 'placeholder' ] = [ 
            'tab'         => 'content',
            'group'       => 'form_content',
            'type'        => 'text',
            'label'       => esc_html__( 'Placeholder', 'vietnix' ),
            'description' => 'Enter the placeholder',
            'placeholder' => 'Tìm kiếm tên miền của bạn',
        ];

        $this->controls[ 'button_text' ] = [ 
            'tab'         => 'content',
            'group'       => 'form_content',
            'type'        => 'text',
            'label'       => esc_html__( 'Button text', 'vietnix' ),
            'description' => 'Enter the button text',
            'placeholder' => 'Tìm kiếm',
            'default'     => 'Tìm kiếm',
        ];

        $this->controls[ 'button_icon' ] = [ 
            'tab'     => 'content',
            'group'   => 'form_content',
            'label'   => esc_html__( 'Button icon', 'vietnix' ),
            'type'    => 'icon',
            'default' => [ 
                'library' => 'themify',
                // fontawesome/ionicons/themify
                'icon'    => 'ti-search',
                // Example: Themify icon class
            ],
            'css'     => [ 
                [ 
                    'selector' => '.icon-svg',
                    // Use to target SVG file
                ],
            ],
        ];

        $this->controls[ 'clear_icon' ] = [ 
            'tab'     => 'content',
            'group'   => 'form_content',
            'label'   => esc_html__( 'Clear icon', 'vietnix' ),
            'type'    => 'icon',
            'default' => [ 
                'library' => 'themify',
                // fontawesome/ionicons/themify
                'icon'    => 'ti-close',
                // Example: Themify icon class
            ],
            'css'     => [ 
                [ 
                    'selector' => '.icon-svg',
                    // Use to target SVG file
                ],
            ],
        ];

        $this->controls[ 'redirect_url' ] = [ 
            'tab'         => 'content',
            'group'       => 'form_content',
            'type'        => 'text',
            'label'       => esc_html__( 'Redirect URL', 'vietnix' ),
            'description' => 'Enter the url to redirect after form submit',
            'placeholder' => 'https://domain.com/....',
            'required'    => [ 'form_style', '=', [ 'redirect' ] ],
        ];

        $this->controls[ 'susggest_tld' ] = [ 
            'tab'         => 'content',
            'group'       => 'form_content',
            'type'        => 'text',
            'label'       => esc_html__( 'Susggest TLD', 'vietnix' ),
            'description' => 'Text the default tld',
            'default'     => 'com',
            'placeholder' => 'Text your tld',
            'required'    => [ 'form_style', '=', [ 'onpage' ] ],
        ];

        $this->controls[ 'prioritize_tld' ] = [ 
            'tab'      => 'content',
            'group'    => 'form_content',
            'type'     => 'text',
            'label'    => esc_html__( 'Prioritize TLD', 'vietnix' ),
            'default'  => 'vn, com, com.vn, net',
            'required' => [ 'form_style', '=', [ 'onpage' ] ],
        ];
    }

    public function render()
    {
      $form_style = isset( $this->settings['form_style'] ) && $this->settings['form_style'] ? $this->settings['form_style'] : 'redirect';
      $domain_search = ['redirect','onpage'];
      $domain_whois = ['whois_result_page','whois_landing_page'];
      if (in_array($form_style, $domain_search)){
        View::render( "widgets/bricks/vnx-search-domain-form", $this );
      }
      else if(in_array($form_style, $domain_whois)){
        View::render( "widgets/bricks/domain/check-whois-form", $this );
      }
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