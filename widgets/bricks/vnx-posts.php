<?php
if ( !defined( 'ABSPATH' ) )
  exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Posts_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-posts';
  public $icon = 'ti-write';
  public $scripts = [ 'vnxPostsElement' ];

  public function get_label()
  {
    return esc_html__( 'VNX Posts', 'vietnix' );
  }

  public function enqueue_scripts()
  {
    wp_register_script( 'vnx-posts-bricks-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx_posts_bricks.js', [ 'jquery' ], '1.0', true );
    wp_enqueue_script( 'vnx-posts-bricks-center' );

    wp_register_style( 'vnx-posts-bricks-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/css/vnx_posts_bricks.css', [ 'bricks-frontend' ], '1.0', 'all' );
    wp_enqueue_style( 'vnx-posts-bricks-center' );
  }

  public function set_control_groups()
  {
    // $this->control_groups[ 'query_settings' ] = [ 
    //     'title' => esc_html__( 'Query Settings', 'vietnix' ),
    //     'tab'   => 'content',
    // ];
  }

  public function set_controls()
  {
    $this->controls[ 'categories' ] = [ 
      'tab'         => 'content',
      'label'       => esc_html__( 'Categories', 'vietnix' ),
      'type'        => 'select',
      'options'     => $this->list_categories(),
      'inline'      => true,
      'placeholder' => esc_html__( 'Select tag', 'vietnix' ),
      'multiple'    => true,
      'searchable'  => true,
      'clearable'   => true,
    ];

    $this->controls[ 'posts_per_page' ] = [ 
      'tab'     => 'content',
      'label'   => esc_html__( 'Posts per page', 'vietnix' ),
      'type'    => 'number',
      'min'     => 1,
      'step'    => '1', // Default: 1
      'inline'  => true,
      'default' => 12,
    ];

    $this->controls[ 'paginate' ] = [ 
      'tab'     => 'content',
      'label'   => esc_html__( 'Show Pagination', 'vietnix' ),
      'type'    => 'checkbox',
      'inline'  => true,
      'small'   => true,
      'default' => false, // Default: false
    ];

    $this->controls[ 'prev_icon' ] = [ 
      'tab'      => 'content',
      'label'    => esc_html__( 'Prev Icon', 'vietnix' ),
      'type'     => 'icon',
      'default'  => [ 
        'library' => 'themify',
        // fontawesome/ionicons/themify
        'icon'    => 'ti-angle-left',
        // Example: Themify icon class
      ],
      'css'      => [ 
        [ 
          'selector' => '.vnx_icon_prev',
          // Use to target SVG file
        ],
      ],
      'required' => [ 'paginate', '=', [ 1 ] ],
    ];

    $this->controls[ 'next_icon' ] = [ 
      'tab'      => 'content',
      'label'    => esc_html__( 'Next Icon', 'vietnix' ),
      'type'     => 'icon',
      'default'  => [ 
        'library' => 'themify',
        // fontawesome/ionicons/themify
        'icon'    => 'ti-angle-right',
        // Example: Themify icon class
      ],
      'css'      => [ 
        [ 
          'selector' => '.vnx_icon_next',
          // Use to target SVG file
        ],
      ],
      'required' => [ 'paginate', '=', [ 1 ] ],
    ];
  }

  public function render()
  {
    View::render( "widgets/bricks/vnx-posts", $this );
  }

  protected function list_categories()
  {
    $return = [];
    foreach ( get_categories() as $key => $cate ) {
      $return[ $cate->term_id ] = $cate->name;
    }
    return $return;
  }
}