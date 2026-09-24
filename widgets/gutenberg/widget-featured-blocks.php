<?php
/*
 * @Author VNX
 * Add widget: vnx-featured-snippet
 * Create: 16 -09 -2022
 */
function register_vnx_featured_snippet_Center()
{

    $path_Script = VNX_PLUGIN_URL_CENTER . 'widgets/gutenberg/js/widget_featured.js';
    if((is_singular()) || is_user_logged_in()){
    wp_register_style(
        'vnx-styles-block-center',
        array(),
        'all'
    );
    wp_register_script(
        'vnix-script-featured-snippet-center',
        $path_Script,
        array('wp-blocks', 'wp-i18n', 'wp-editor', 'wp-components'),
        true,
        false
    );
    register_block_type(
        'vnix/featured-snippet',
        array(
            'editor_script' => 'vnix-script-featured-snippet-center',
        ),

    );
  }
}
add_action('enqueue_block_editor_assets', 'register_vnx_featured_snippet_Center');
