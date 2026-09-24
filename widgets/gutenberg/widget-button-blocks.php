<?php
/*
 * @Author VNX
 * Add widget: vnx-button_widget_block
 * Create: 13 -07 -2023
 */
function register_button_widget_block_Center()
{
    wp_register_script(
        'button-widget-block-script-center',
        VNX_PLUGIN_URL_CENTER . 'widgets/gutenberg/js/button-widget-block.js',
        array('wp-blocks', 'wp-i18n', 'wp-editor', 'wp-components'),
    );
    wp_register_style('vnx-custom_button-block-center', VNX_PLUGIN_URL_CENTER . 'build/css/gutenberg/custom_button.css', array(), 'all');
    register_block_type('vnx/button-widget-block', array(
        'editor_script' => 'button-widget-block-script-center',
        'render_callback' => function ($attributes) {
            return sprintf('<button>%s</button>', esc_html($attributes['text']));
        },
        'attributes' => array(
            'text' => array(
                'type' => 'string',
                'default' => 'Click me!'
            )
        )
    ));
    if ( !function_exists( 'button_widget_block_assets_Center' ) ) {
        function button_widget_block_assets_Center()
        {
            if ( function_exists( 'is_use_bricks_Center' ) && !is_use_bricks_Center() ) {
                wp_enqueue_style( 'vnx-custom_button-block-center' );
            }
        }
        add_action( 'wp_enqueue_scripts', 'button_widget_block_assets_Center' );
    }
}
add_action('init', 'register_button_widget_block_Center');
