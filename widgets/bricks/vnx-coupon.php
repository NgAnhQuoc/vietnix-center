<?php
namespace VNXCenter\Widgets\Bricks;
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly
use HelperCenter\View;

if ( class_exists( 'VNXCenter\Widgets\Bricks\Coupon' ) )
    return;

class Coupon extends \Bricks\Element
{
    public $category = 'vietnix';
    public $name = 'vnx-coupon';
    public $icon = 'fa-solid fa-hand-holding-dollar';

    public $scripts = [ 'vnxCouponScript' ];

    public function get_label()
    {
        return esc_html__( 'VNX Coupon', 'vietnix' );
    }

    public function enqueue_scripts()
    {
        wp_register_script( 'vnx-coupon-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/coupon.js', [ 'jquery' ], '1.0', true );
        wp_enqueue_script( 'vnx-coupon-center' );

        wp_enqueue_style( 'vnx-bricks-tailwind-base-center', VNX_PLUGIN_URL_CENTER . 'build/css/brickstailwindBase.css', [ 'bricks-frontend' ], false, 'all' );
        wp_enqueue_style( 'vnx-coupon-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/coupon.css', [ 'bricks-frontend', 'vnx-bricks-tailwind-base-center' ], false, 'all' );
    }

    public function set_control_groups()
    {
        $this->control_groups[ 'coupon_settings' ] = [ 
            'title' => esc_html__( 'coupon_settings', 'vietnix' ),
            'tab'   => 'content',
        ];
    }

    public function set_controls()
    {
        $this->controls[ 'title_tag' ] = [ 
            'tab'         => 'content',
            'label'       => esc_html__( 'Title tag', 'vietnix' ),
            'type'        => 'select',
            'group'       => 'coupon_settings',
            'options'     => [ 
                'div'  => 'Div',
                'h1'   => 'H1',
                'h2'   => 'H2',
                'h3'   => 'H3',
                'h4'   => 'H4',
                'h5'   => 'H5',
                'h6'   => 'H6',
                'p'    => 'P',
                'span' => 'Span',
            ],
            'inline'      => true,
            'placeholder' => esc_html__( 'Select tag', 'vietnix' ),
            'multiple'    => false,
            'searchable'  => false,
            'clearable'   => false,
            'default'     => 'div',
        ];

        $this->controls[ 'title' ] = [ 
            'tab'           => 'content',
            'label'         => esc_html__( 'Title', 'vietnix' ),
            'type'          => 'text',
            'group'         => 'coupon_settings',
            'spellcheck'    => false, // Default: false
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => true,
            'placeholder'   => 'Here goes your title..',
        ];

        $this->controls[ 'description' ] = [ 
            'tab'           => 'content',
            'label'         => esc_html__( 'Description', 'vietnix' ),
            'type'          => 'editor',
            'group'         => 'coupon_settings',
            'inlineEditing' => [ 
                'selector' => '.text-editor', // Mount inline editor to this CSS selector
                'toolbar'  => true, // Enable/disable inline editing toolbar
            ],
            'default'       => esc_html__( 'Here goes the content ..', 'vietnix' ),
        ];

        $this->controls[ 'coupon_code' ] = [ 
            'tab'           => 'content',
            'label'         => esc_html__( 'Coupon Code', 'vietnix' ),
            'type'          => 'text',
            'group'         => 'coupon_settings',
            'spellcheck'    => false, // Default: false
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => false,
            'placeholder'   => 'Here goes your code..',
        ];

        $this->controls[ 'notice' ] = [ 
            'tab'           => 'content',
            'label'         => esc_html__( 'Notice', 'vietnix' ),
            'description'   => esc_html__( 'This notice will be displayed below the coupon code.', 'vietnix' ),
            'type'          => 'text',
            'group'         => 'coupon_settings',
            'spellcheck'    => false, // Default: false
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => false,
            'placeholder'   => 'Here goes your text..',
        ];

        $this->controls[ 'button_class' ] = [ 
            'tab'           => 'content',
            'label'         => esc_html__( 'Button class', 'vietnix' ),
            'type'          => 'text',
            'group'         => 'coupon_settings',
            'spellcheck'    => false, // Default: false
            'inlineEditing' => false,
            'placeholder'   => 'Example: btn btn-primary',
        ];

        $this->controls[ 'button_text' ] = [ 
            'tab'           => 'content',
            'label'         => esc_html__( 'Button text', 'vietnix' ),
            'type'          => 'text',
            'group'         => 'coupon_settings',
            'spellcheck'    => false, // Default: false
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => false,
            'placeholder'   => 'Here goes your text..',
        ];

        $this->controls[ 'button_clicked_text' ] = [ 
            'tab'           => 'content',
            'label'         => esc_html__( 'Button clicked text', 'vietnix' ),
            'type'          => 'text',
            'group'         => 'coupon_settings',
            'spellcheck'    => false, // Default: false
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => false,
            'placeholder'   => 'Here goes your text..',
        ];

        $this->controls[ 'image' ] = [ 
            'tab'   => 'content',
            'group' => 'coupon_settings',
            'label' => esc_html__( 'Image', 'vietnix' ),
            'type'  => 'image',
        ];
    }

    public function render()
    {
        View::render( "widgets/bricks/vnx-coupon/render", $this );
    }

}
