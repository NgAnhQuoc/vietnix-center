<?php
namespace VNXCenter\Widgets\Bricks;
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly
use HelperCenter\View;

if ( class_exists( 'VNXCenter\Widgets\Bricks\BonusServiceTime' ) )
    return;

class BonusServiceTime extends \Bricks\Element
{
    public $category = 'vietnix';
    public $name = 'vnx-bonus-service-time';
    public $icon = 'fa-solid fa-business-time';

    public $scripts = [ 'vnxBonusServiceTimeScript' ];

    public function get_label()
    {
        return esc_html__( 'VNX Bonus Service Time', 'vietnix' );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script( 'bricks-splide' );
        wp_register_script( 'vnx-bonus-service-time-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/bonus_service_time.js', [ 'jquery', 'bricks-splide' ], '1.0', true );
        wp_enqueue_script( 'vnx-bonus-service-time-center' );

        wp_enqueue_style( 'bricks-splide' );
        wp_enqueue_style( 'vnx-bricks-tailwind-base-center', VNX_PLUGIN_URL_CENTER . 'build/css/brickstailwindBase.css', [ 'bricks-frontend' ], false, 'all' );
        wp_enqueue_style( 'vnx-bonus-service-time-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/bonus_service_time.css', [ 'vnx-bricks-tailwind-base-center', 'bricks-splide' ], false, 'all' );
    }

    public function set_control_groups()
    {
        $this->control_groups[ 'content_settings' ] = [ 
            'title' => esc_html__( 'Content', 'vietnix' ),
            'tab'   => 'content',
        ];
        $this->control_groups[ 'element_style' ] = [ 
            'title' => esc_html__( 'Element Style', 'vietnix' ),
            'tab'   => 'content',
        ];
    }

    public function set_controls()
    {
        $this->controls[ 'circle_title' ] = [ 
            'tab'           => 'content',
            'group'         => 'content_settings',
            'label'         => esc_html__( 'Circle Title', 'vietnix' ),
            'type'          => 'text',
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => false,
            'default'       => 'CHU KỲ',
        ];

        $this->controls[ 'bonus_title' ] = [ 
            'tab'           => 'content',
            'group'         => 'content_settings',
            'label'         => esc_html__( 'Bonus Title', 'vietnix' ),
            'type'          => 'text',
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => false,
            'default'       => 'TẶNG THÊM',
        ];

        $this->controls[ 'register_text' ] = [ 
            'tab'           => 'content',
            'group'         => 'content_settings',
            'label'         => esc_html__( 'Register Button Text', 'vietnix' ),
            'type'          => 'text',
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => false,
            'default'       => 'ĐĂNG KÝ NGAY',
        ];

        $this->controls[ 'content' ] = [ 
            'tab'           => 'content',
            'group'         => 'content_settings',
            'label'         => esc_html__( 'Repeater', 'vietnix' ),
            'type'          => 'repeater',
            'titleProperty' => 'service', // Default 'title'
            'default'       => [ 
                [ 
                    'service' => 'Hosting',
                    'items'   => [ 
                        [ 
                            'circle'     => '6 THÁNG',
                            'bonus_time' => '1 THÁNG',
                        ],
                    ],
                ],
            ],
            'placeholder'   => esc_html__( 'Service', 'vietnix' ),
            'fields'        => [ 
                'service' => [ 
                    'label'       => esc_html__( 'Service Name', 'vietnix' ),
                    'description' => esc_html__( 'Service Name should be unique', 'vietnix' ),
                    'type'        => 'text',
                ],
                'link'    => [ 
                    'label'       => esc_html__( 'Service Link', 'vietnix' ),
                    'description' => esc_html__( 'Service Link for register button', 'vietnix' ),
                    'type'        => 'link',
                ],
                'items'   => [ 
                    'label'         => esc_html__( 'Items', 'vietnix' ),
                    'type'          => 'repeater',
                    'titleProperty' => 'circle', // Default 'title'
                    'default'       => [ 
                        [ 
                            'circle'     => '6 THÁNG',
                            'bonus_time' => '1 THÁNG',
                        ],
                    ],
                    'placeholder'   => esc_html__( 'Item', 'vietnix' ),
                    'fields'        => [ 
                        'circle'     => [ 
                            'label' => esc_html__( 'Circle', 'vietnix' ),
                            'type'  => 'text',
                        ],
                        'bonus_time' => [ 
                            'label' => esc_html__( 'Bonus Time', 'vietnix' ),
                            'type'  => 'text',
                        ],
                    ],
                ],
            ],
        ];

        $this->controls[ 'tab_title_bg' ] = [ 
            'tab'         => 'content',
            'group'       => 'element_style',
            'label'       => esc_html__( 'Tab Title Background', 'vietnix' ),
            'description' => esc_html__( 'This background will be applied to the tab title when it active', 'vietnix' ),
            'type'        => 'image',
        ];

        $this->controls[ 'item_bg' ] = [ 
            'tab'   => 'content',
            'group' => 'element_style',
            'label' => esc_html__( 'Item Background', 'vietnix' ),
            'type'  => 'image',
        ];

        $this->controls[ 'title_bg' ] = [ 
            'tab'   => 'content',
            'group' => 'element_style',
            'label' => esc_html__( 'Bonus Title Background', 'vietnix' ),
            'type'  => 'image',
        ];
    }

    public function render()
    {
        View::render( "widgets/bricks/bonus-service-time/render", $this );
    }

}
