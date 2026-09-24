<?php
if ( !defined( 'ABSPATH' ) )
    die( 'Direct access forbidden.' );
require_once( __DIR__ . '/blockquote-block/block_class.php' );
if ( !function_exists( 'register_vnx_blockquote_block_Center' ) ) {
    function register_vnx_blockquote_block_Center()
    {
        wp_register_style( 'vnx-blockquote-block-center', VNX_PLUGIN_URL_CENTER . 'assets/css/gutenberg/blockquote-block.css', array(), 'all' );
        wp_register_style( 'vnx-blockquote-block-editor-center', VNX_PLUGIN_URL_CENTER . 'assets/css/gutenberg/blockquote-block.css', array(), 'all' );
        register_block_type( __DIR__ . '/blockquote-block' );
    }

    if ( !function_exists( 'vnx_blockquote_block_style_Center' ) ) {
        function vnx_blockquote_block_style_Center()
        {
            if ( function_exists( 'is_use_bricks_Center' ) && !is_use_bricks_Center() ) {
                wp_enqueue_style( 'vnx-blockquote-block-center' ); // if can use editorStyle in block.json -> remove this line
            }
        }
        add_action( 'wp_enqueue_scripts', 'vnx_blockquote_block_style_Center' );
    }

    add_action( 'init', 'register_vnx_blockquote_block_Center' );
}