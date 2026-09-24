<?php
/*
 * @Author VNX
 * Add widget: vnx-button_widget_block
 * Create: 13 -07 -2023
 */
function register_shortcode_widget_block_Center() {
    wp_register_script(
        'shortcode-widget-block-script-center',
        VNX_PLUGIN_URL_CENTER . 'widgets/gutenberg/js/shortcode-widget-block.js',
        array('wp-blocks', 'wp-i18n', 'wp-editor', 'wp-components'),
    );

    register_block_type( 'vnx/shortcode-widget-blocks', array(
        'editor_script' => 'shortcode-widget-block-script-center',
    ) );
}
add_action( 'init', 'register_shortcode_widget_block_Center' );