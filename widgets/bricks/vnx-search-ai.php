<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

use HelperCenter\View;

class VNX_Search_Posts_AI_Center extends \Bricks\Element
{
    public $category = 'vietnix';
    public $name = 'vnx-search-ai';
    public $icon = 'ion-md-search';

    public function get_label()
    {
        return esc_html__('VNX Search Posts AI', 'vietnix');
    }

    public function enqueue_scripts()
    {
        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        wp_register_script('xlsx-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/xlsx.min.js');
        wp_enqueue_script('xlsx-center');

        $version = filectime(VNX_PLUGIN_PATH_CENTER . 'widgets/inc/bricks/js/vnx_search_ai.js');
        wp_enqueue_script('vnx-search-ai-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx_search_ai.js', ['jquery'], $version, true);
        wp_localize_script('vnx-search-ai-center', 'vnxSearchPostsAI', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vnx_search_posts_ai_nonce'),
        ]);

        wp_localize_script('vietnix-search-ai-center', 'ajaxurl', admin_url('admin-ajax.php'));
    }



    public function set_controls()
    {
        $this->controls['display_mode'] = [
            'tab' => 'content',
            'type' => 'select',
            'label' => esc_html__('Display Mode', 'vietnix'),
            'options' => [
                'vnx-form-search' => esc_html__('Search Form', 'vietnix'),
                'vnx-form-dowload' => esc_html__('Search dowload content', 'vietnix'),
                'vnx-result-search' => esc_html__('Search Result', 'vietnix'),
                'vnx-result-dowload' => esc_html__('Result data dowload content', 'vietnix'),
            ],
            'default' => 'search',
            'description' => esc_html__('Chọn hiển thị form search hay kết quả.', 'vietnix'),
        ];
    }

    public function render()
    {
            View::render('widgets/bricks/vnx-search-ai', $this);
    }
}
