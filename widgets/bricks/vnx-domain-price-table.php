<?php
namespace VNXCenter\Widgets\Bricks;
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly
use HelperCenter\View;

class DomainPriceTable extends \Bricks\Element
{
    public $category = 'vietnix';
    public $name = 'vnx-domain-price-table';
    public $icon = 'ion-md-cash';

    public $scripts = [ 'vnxDomainPrice4post' ];

    public function get_label()
    {
        return esc_html__( 'VNX Domain Price Table', 'vietnix' );
    }

    public function enqueue_scripts()
    {
        wp_register_script( 'vnx-domain-price-4post-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/domain_price_4post.js', [ 'jquery' ], '1.0', true );
        wp_enqueue_script( 'vnx-domain-price-4post-center' );
        wp_register_style( 'vnx-domain-price-4post-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/domain_price_4post.css', [ 'bricks-frontend' ], '1.0', 'all' );
        wp_enqueue_style( 'vnx-domain-price-4post-center' );
        wp_enqueue_style( 'vnx-bricks-tailwind-base-center', VNX_PLUGIN_URL_CENTER . 'build/css/brickstailwindBase.css', [ 'bricks-frontend' ], false, 'all' );
    }

    public function set_controls()
    {
        $this->controls[ 'table_style' ] = [ 
            'tab'         => 'content',
            'label'       => esc_html__( 'Select Table Style', 'vietnix' ),
            'type'        => 'select',
            'options'     => [ 
                'widget_for_post' => esc_html__( 'Bảng giá tên miền VN cho Post', 'vietnix' ),
            ],
            'inline'      => true,
            'placeholder' => esc_html__( 'Select style', 'vietnix' ),
            'multiple'    => false,
            'searchable'  => true,
            'clearable'   => true,
            'default'     => '',
        ];

        $this->controls[ 'upload' ] = [ 
            'tab'         => 'content',
            'label'       => esc_html__( 'Data File', 'vietnix' ),
            'description' => 'Please select the file format is .csv',
            'type'        => 'file',
            'required'    => [ 'table_style', '=', [ 'widget_for_post' ] ],
        ];

        $this->controls[ 'tooltip_icon' ] = [ 
            'tab'      => 'content',
            'label'    => esc_html__( 'Tooltip Icon', 'vietnix' ),
            'type'     => 'icon',
            'default'  => [ 
                'library' => 'fontawesomeRegular',
                // fontawesome/ionicons/themify
                'icon'    => 'fa fa-question-circle',
                // Example: Themify icon class
            ],
            'css'      => [ 
                [ 
                    'selector' => '.vnx_tooltip_icon',
                    // Use to target SVG file
                ],
            ],
            'required' => [ 'table_style', '=', [ 'widget_for_post' ] ],
        ];

    }

    public function render()
    {
        View::render( "widgets/bricks/domain-price/render", $this );
    }

    public function get_upload_file_data()
    {
        $settings = $this->settings;
        if ( !isset( $settings[ 'upload' ] ) || $settings[ 'upload' ] == "" )
            return array(
                'status'  => 'error',
                'message' => 'You have no file uploaded',
            );
        $csvdata = [];
        $file = '';

        $link = $settings[ 'upload' ][ 'url' ];
        // Biểu thức chính quy để lấy từ "wp-content" đến cuối link
        $regex = '/wp-content\/(.*)/';
        // Sử dụng preg_match để tìm kiếm
        if ( preg_match( $regex, $link, $matches ) ) {
            // $matches[0] sẽ chứa toàn bộ phần match
            $file = ABSPATH . $matches[ 0 ];
        } else {
            $message = "Can't replace domain form File URL<br/>";
            $message .= 'File URL form $settings: ' . $settings[ 'upload' ][ 'url' ] . '<br/>';
            $message .= 'File URL after replace: ' . $file . '<br/>';
            return array(
                'status'  => 'error',
                'message' => $message,
            );
        }

        if ( !file_exists( $file ) ) {
            $message = 'CSV File not found.<br/>';
            $message .= 'File URL form $settings: ' . $settings[ 'upload' ][ 'url' ] . '<br/>';
            $message .= 'File URL after replace: ' . $file . '<br/>';
            return array(
                'status'  => 'error',
                'message' => $message,
            );
        }
        $handle = fopen( $file, "r" );
        if ( !$handle ) {
            $message = 'File open failed.<br/>';
            $message .= 'File URL form $settings: ' . $settings[ 'upload' ][ 'url' ] . '<br/>';
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
