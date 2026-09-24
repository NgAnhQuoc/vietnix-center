<?php
if ( !function_exists( 'register_vnx_compare_block_Center' ) ) {
  function register_vnx_compare_block_Center()
  {
    wp_register_style( 'vnx-compare-block-center', VNX_PLUGIN_URL_CENTER . 'build/css/gutenberg/compare-block.css', array(), 'all' );
    register_block_type( __DIR__ . '/compare-block' );
  }
  if ( !function_exists( 'vnx_compare_block_style_Center' ) ) {
    function vnx_compare_block_style_Center()
    {
      if ( function_exists( 'is_use_bricks_Center' ) && !is_use_bricks_Center() ) {
        wp_enqueue_style( 'vnx-compare-block-center' ); // if can use editorStyle in block.json -> remove this line
      }
    }
    add_action( 'wp_enqueue_scripts', 'vnx_compare_block_style_Center' );
  }
  add_action( 'init', 'register_vnx_compare_block_Center' );
}