<?php
if ( !function_exists( 'register_vnx_note_icon_block_Center' ) ) {
    function register_vnx_note_icon_block_Center()
    {
        wp_register_style( 'vnx-note-icon-block-center', VNX_PLUGIN_URL_CENTER . 'build/css/gutenberg/note-icon-block.css', array(), 'all' );
        wp_register_style( 'vnx-note-icon-block-editor-center', VNX_PLUGIN_URL_CENTER . 'assets/css/gutenberg/note-icon-block-editor.css', array(), 'all' );
        register_block_type( __DIR__ . '/note-icon-block' );
    }
    if ( !function_exists( 'vnx_note_icon_style_Center' ) ) {
        function vnx_note_icon_style_Center()
        {
            if ( function_exists( 'is_use_bricks_Center' ) && !is_use_bricks_Center() ) {
                wp_enqueue_style( 'vnx-note-icon-block-center' );

            }
        }
        add_action( 'wp_enqueue_scripts', 'vnx_note_icon_style_Center' );
    }
    add_action( 'init', 'register_vnx_note_icon_block_Center' );
}