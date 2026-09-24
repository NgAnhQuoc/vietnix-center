<?php

if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

add_filter( 'bricks/elements/post-content/controls', function( $controls ) {
  $controls['border_all_image'] = [
      'tab'      => 'content',
      'label'    => esc_html__( 'Border all images', 'vnx' ),
      'type'     => 'checkbox'
  ];
  $controls['imagesBorder'] = [
    'tab' => 'content',
    'label' => esc_html__( 'Border Images', 'vnx' ),
    'type' => 'border',
    'required'    => [ 'border_all_image', '!=', '' ],
    'css' => [
      [
        'property' => 'border',
        'selector' => '.wp-block-image figure img, figure.wp-block-image img',
      ],
    ],
    'inline' => true,
    'small' => true,
    'default' => [
      'width' => [
        'top' => 0,
        'right' => 0,
        'bottom' => 0,
        'left' => 0,
      ],
      'style' => 'solid',
      'color' => [
        'hex' => '#38a7ff',
      ],
      'radius' => [
        'top' => 4,
        'right' => 4,
        'bottom' => 4,
        'left' => 4,
      ],
    ],


  ];
  return $controls;
} );