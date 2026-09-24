<?php
if ( !function_exists( 'register_vnx_view_more_block_Center' ) ) {
    function register_vnx_view_more_block_Center()
    {
        wp_register_style( 'vnx-view-more-block-center', VNX_PLUGIN_URL_CENTER . 'build/css/gutenberg/view-more-block.css', array(), 'all' );
        wp_register_style( 'vnx-view-more-block-editor-center', VNX_PLUGIN_URL_CENTER . 'assets/css/gutenberg/view-more-block-editor.css', array(), 'all' );
        register_block_type( __DIR__ . '/view-more-block' );
    }

    if ( !function_exists( 'vnx_view_more_style_Center' ) ) {
        function vnx_view_more_style_Center()
        {
            if ( function_exists( 'is_use_bricks_Center' ) && !is_use_bricks_Center() ) {
                wp_enqueue_style( 'vnx-view-more-block-center' ); // if can use editorStyle in block.json -> remove this line

            }
        }
        add_action( 'wp_enqueue_scripts', 'vnx_view_more_style_Center' );
    }

    add_action( 'init', 'register_vnx_view_more_block_Center' );
}