<?php

/**
 * @VNX
 * Call Styles: block.scss and Script: block.js
 * Register-Block-type
 * Add vnx-note-block
 */
function register_vnx_note_block_Center()
{
    // $path_Styles = get_stylesheet_directory_uri() .'';
    $path_Script = VNX_PLUGIN_URL_CENTER . 'widgets/gutenberg/js/block.js';
    if((is_singular()) || is_user_logged_in()){
    wp_register_style(
        'vnx-styles-block-center',
        array(),
        'all'
    );
    wp_register_script(
        'vnx-script-block-center',
        $path_Script,
        array('wp-blocks', 'wp-i18n', 'wp-editor'),
        true,
        false
    );
    register_block_type(
        'vnx/note-block',
        array(
            'render_callback' => function ($block_attributes, $content) {
                return;
            },
            'editor_script' => 'vnx-script-block-center',
            'editor_styles' =>  'vnx-styles-block-center'
        ),

    );
  }
}
add_action('enqueue_block_editor_assets', 'register_vnx_note_block_Center');
