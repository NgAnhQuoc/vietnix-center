<?php

namespace VNXCenter\Widgets\Bricks;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
use HelperCenter\View;

if (class_exists('VNXCenter\Widgets\Bricks\Dynamic_Price_Table'))
    return;

class Dynamic_Price_Table extends \Bricks\Element
{
    public $category = 'vietnix';
    public $name = 'vnx-dynamic-price-table';
    public $icon = 'fa-solid fa-hand-holding-dollar';


    public function get_label()
    {
        return esc_html__('VNX Dynamic Price Table', 'vietnix');
    }

    public function enqueue_scripts()
    {
        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        $script_path = VNX_PLUGIN_PATH_CENTER . 'widgets/inc/bricks/js/vnx-dynamic-price-table.js';
        $script_ver  = file_exists($script_path) ? filemtime($script_path) : '1.1';
        wp_register_script('vnx-dynamic-price-table-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx-dynamic-price-table.js', ['jquery'], $script_ver, true);
        wp_enqueue_script('vnx-dynamic-price-table-center');

        wp_enqueue_style('vnx-bricks-tailwind-base-center', VNX_PLUGIN_URL_CENTER . 'build/css/brickstailwindBase.css', ['bricks-frontend'], false, 'all');
    }

    public function set_control_groups()
    {

        $this->control_groups['group-config'] = [
            'title' => esc_html__('Product', 'vietnix'),
            'tab'   => 'content',
        ];
        $this->control_groups['group-extra-config'] = [
            'title' => esc_html__('Extra Product', 'vietnix'),
            'tab'   => 'content',
        ];
    }

    public function set_controls()
    {

    $this->controls['style'] = [
            'tab' => 'content',
            'label' => esc_html__('Style', 'vietnix'),
            'type' => 'select',
            'default' => esc_html__('Dynamic Price Table', 'vietnix'),
            'options' => [
                'dynamic' => esc_html__('Dynamic Price Table', 'vietnix'),
                'vnx-dynamic-price-table-static' => esc_html__('Static Price Table Have Router', 'vietnix'),
            ],
        ];

        $this->controls['pid'] = [
            'tab' => 'content',
            'group' => 'group-config',
            'label' => esc_html__('Product ID', 'vietnix'),
            'placeholder' => esc_html__('Nhập ID sản phẩm', 'vietnix'),
            'type' => 'text',
            'inlineEditing' => true,
            'default' => '0',
        ];

        $this->controls['billingcycle'] = [
            'tab' => 'content',
            'group' => 'group-config',
            'label' => esc_html__('Billing Cycle', 'vietnix'),
            'placeholder' => esc_html__('Nhập chu kỳ thanh toán', 'vietnix'),
            'type' => 'text',
            'inlineEditing' => true,
            'default' => 'quarterly',
        ];

        $this->controls['main-resources'] = [
            'tab' => 'content',
            'label' => esc_html__('Product configuration', 'vietnix'),
            'type' => 'repeater',
            'titleProperty' => 'label',
            'group' => 'group-config',
            'default' => [
                [
                    'key' => 'price-ram',
                    'label' => 'RAM',
                    'value' => 1,
                    'max' => 100,
                    'step' => 1,
                    'min' => 0,
                    'price' => 7000,
                    'unit' => 'GB',
                    'desUnit' => 'Dung lượng (GB):',
                ],
            ],
            'placeholder' => esc_html__('Configuration', 'vietnix'),
            'fields' => [
                'key' => [
                    'label' => esc_html__('Key', 'vietnix'),
                    'type' => 'text',
                ],
                'label' => [
                    'label' => esc_html__('Label', 'vietnix'),
                    'type' => 'text',
                ],
                'value' => [
                    'label' => esc_html__('Default Value', 'vietnix'),
                    'type' => 'number',
                ],
                'min' => [
                    'label' => esc_html__('Min', 'vietnix'),
                    'type' => 'number',
                ],
                'max' => [
                    'label' => esc_html__('Max', 'vietnix'),
                    'type' => 'number',
                ],
                'step' => [
                    'label' => esc_html__('Step', 'vietnix'),
                    'type' => 'number',
                ],
                'price' => [
                    'label' => esc_html__('Price', 'vietnix'),
                    'type' => 'number',
                ],
                'unit' => [
                    'label' => esc_html__('Unit', 'vietnix'),
                    'type' => 'text',
                ],
                'desUnit' => [
                    'label' => esc_html__('Description Unit', 'vietnix'),
                    'type' => 'text',
                ],
            ],
        ];


        $this->controls['extra-resources'] = [
            'tab' => 'content',
            'label' => esc_html__('Product configuration', 'vietnix'),
            'type' => 'repeater',
            'titleProperty' => 'label',
            'group' => 'group-extra-config',
            'default' => [
                [
                    'key' => 'price-ram',
                    'label' => 'RAM',
                    'value' => 1,
                    'max' => 100,
                    'min' => 0,
                    'step' => 1,
                    'price' => 7000,
                    'unit' => 'GB',
                    'desUnit' => 'Dung lượng (GB):',
                ],
            ],
            'placeholder' => esc_html__('Configuration', 'vietnix'),
            'fields' => [
                'key' => [
                    'label' => esc_html__('Key', 'vietnix'),
                    'type' => 'text',
                ],
                'label' => [
                    'label' => esc_html__('Label', 'vietnix'),
                    'type' => 'text',
                ],
                'value' => [
                    'label' => esc_html__('Default Value', 'vietnix'),
                    'type' => 'number',
                ],
                'min' => [
                    'label' => esc_html__('Min', 'vietnix'),
                    'type' => 'number',
                ],
                'max' => [
                    'label' => esc_html__('Max', 'vietnix'),
                    'type' => 'number',
                ],
                'step' => [
                    'label' => esc_html__('Step', 'vietnix'),
                    'type' => 'number',
                ],
                'price' => [
                    'label' => esc_html__('Price', 'vietnix'),
                    'type' => 'number',
                ],
                'unit' => [
                    'label' => esc_html__('Unit', 'vietnix'),
                    'type' => 'text',
                ],
                 'desUnit' => [
                    'label' => esc_html__('Description Unit', 'vietnix'),
                    'type' => 'text',
                ],
            ],
        ];
    }

    public function render()
    {
        $settings = $this->settings;
        if ($settings['style'] == 'dynamic') {
            View::render("widgets/bricks/vnx-dynamic-price-table", $this);
        }else{
            View::render("widgets/bricks/vnx-dynamic/vnx-dynamic-price-table-static", $this);
        }
    }
}
