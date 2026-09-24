<?php
if (!function_exists('register_vnx_block_table_coupon_Center')) {
  function register_vnx_block_table_coupon_Center()
  {
    wp_register_style('vnx-block-table-coupon-center', VNX_PLUGIN_URL_CENTER . 'assets/css/gutenberg/block-table-coupon.css', array(), 'all');
    wp_register_script('vnx-block-table-coupon-center', VNX_PLUGIN_URL_CENTER . 'widgets/gutenberg/block-table-coupon/block-table-coupon.js', array('jquery'), 'all', true);
    register_block_type(__DIR__ . '/block-table-coupon', array(
      'editor_script' => 'vnx-block-table-coupon-center',
      'script' => 'vnx-block-table-coupon-center'
    ));
  }
  if (!function_exists('vnx_block_table_coupon_style_Center')) {
    function vnx_block_table_coupon_style_Center()
    {
      if (function_exists('is_use_bricks_Center') && !is_use_bricks_Center()) {
        wp_enqueue_style('vnx-block-table-coupon-center'); // if can use editorStyle in block.json -> remove this line
        wp_enqueue_script('vnx-block-table-coupon-center');
      }
    }
    add_action('wp_enqueue_scripts', 'vnx_block_table_coupon_style_Center');
  }
  add_action('init', 'register_vnx_block_table_coupon_Center');
}