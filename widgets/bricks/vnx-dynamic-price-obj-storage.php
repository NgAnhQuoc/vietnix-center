<?php

namespace VNXCenter\Widgets\Bricks;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
use HelperCenter\View;

if (class_exists('VNXCenter\Widgets\Bricks\Dynamic_Price_Obj_Storage'))
    return;

class Dynamic_Price_Obj_Storage extends \Bricks\Element
{
    public $category = 'vietnix';
    public $name = 'vnx-dynamic-price-obj-storage';
    public $icon = 'fa-solid fa-hand-holding-dollar';


    public function get_label()
    {
        return esc_html__('VNX Dynamic Price Object Storage', 'vietnix');
    }

    public function enqueue_scripts()
    {
        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        $script_path = VNX_PLUGIN_PATH_CENTER . 'widgets/inc/bricks/js/vnx-dynamic-price-obj-storage.js';
        $script_ver  = file_exists($script_path) ? filemtime($script_path) : '1.1';
        wp_register_script('vnx-dynamic-price-obj-storage-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/vnx-dynamic-price-obj-storage.js', ['vuejs-library-center'], $script_ver, true);
        wp_enqueue_script('vnx-dynamic-price-obj-storage-center');

        wp_enqueue_style('vnx-bricks-tailwind-base-center', VNX_PLUGIN_URL_CENTER . 'build/css/brickstailwindBase.css', ['bricks-frontend'], 'all', 'all');
    }


    public function set_control_groups()
    {
        $this->control_groups['info_field'] = [
            'title' => esc_html__('Information Field', 'vietnix'),
            'tab' => 'content',
            'group' => 'info_field',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
        ];

        $this->control_groups['info_cycle'] = [
            'title' => esc_html__('Information Cycle', 'vietnix'),
            'tab' => 'content',
            'group' => 'info_cycle',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
        ];
    }

    public function set_controls()
    {

        $this->controls['style'] = [
            'tab'         => 'content',
            'label'       => esc_html__('Select Style Dynamic', 'vietnix'),
            'type'        => 'select',
            'options'     => [
                'vnx-dynamic-price-obj-storage' => esc_html__('Bảng giá tên miền VN cho Post', 'vietnix'),
                'vnx-dynamic-price-obj-storage-v2' => esc_html__('Bảng giá tên miền VN cho Post V2', 'vietnix'),
            ],
            'inline'      => true,
            'placeholder' => esc_html__('Select style', 'vietnix'),
            'multiple'    => false,
            'searchable'  => true,
            'clearable'   => true,
            'default'     => '',
        ];

        $this->controls['title'] = [
            'tab' => 'content',
            'label' => esc_html__('Title product', 'vietnix'),
            'placeholder' => esc_html__('Nhập tiêu đề sản phẩm', 'vietnix'),
            'type' => 'text',
            'inlineEditing' => true,
            'default' => 'DUNG LƯỢNG (GB)',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage']],
        ];

        $this->controls['unit'] = [
            'tab' => 'content',
            'label' => esc_html__('Unit product', 'vietnix'),
            'placeholder' => esc_html__('Nhập đơn vị sản phẩm', 'vietnix'),
            'type' => 'text',
            'inlineEditing' => true,
            'default' => 'GB',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage']],
        ];

        $this->controls['prices'] = [
            'tab' => 'content',
            'label' => esc_html__('Range price product', 'vietnix'),
            'type' => 'repeater',
            'titleProperty' => 'label',
            'default' => [
                [
                    'range' => '100',
                    'price' => '300000',
                    'prid' => '583',
                ],
            ],
            'placeholder' => esc_html__('Range', 'vietnix'),
            'fields' => [
                'range' => [
                    'label' => esc_html__('Range', 'vietnix'),
                    'type' => 'number',
                ],
                'price' => [
                    'label' => esc_html__('Price', 'vietnix'),
                    'type' => 'number',
                ],
                'pid' => [
                    'label' => esc_html__('pid', 'vietnix'),
                    'type' => 'number',
                ],
            ],
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage']],
        ];


        $this->controls['value'] = [
            'tab' => 'content',
            'label' => esc_html__('Default value', 'vietnix'),
            'placeholder' => esc_html__('Nhập giá trị mặc định', 'vietnix'),
            'type' => 'number',
            'inlineEditing' => true,
            'default' => '500',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage']],
        ];


        $this->controls['min'] = [
            'tab' => 'content',
            'label' => esc_html__('Min product', 'vietnix'),
            'placeholder' => esc_html__('Giá trị nhỏ nhất', 'vietnix'),
            'type' => 'number',
            'inlineEditing' => true,
            'default' => '100',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage']],
        ];

        $this->controls['max'] = [
            'tab' => 'content',
            'label' => esc_html__('Max product', 'vietnix'),
            'placeholder' => esc_html__('Giá trị lớn nhất', 'vietnix'),
            'type' => 'number',
            'inlineEditing' => true,
            'default' => '10240',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage']],
        ];

        $this->controls['step'] = [
            'tab' => 'content',
            'label' => esc_html__('Step product', 'vietnix'),
            'placeholder' => esc_html__('Giá trị bước', 'vietnix'),
            'type' => 'number',
            'inlineEditing' => true,
            'default' => '10',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage']],
        ];


        $this->controls['billingcycle'] = [
            'tab' => 'content',
            'label' => esc_html__('Billing Cycle', 'vietnix'),
            'placeholder' => esc_html__('Nhập chu kỳ thanh toán', 'vietnix'),
            'type' => 'text',
            'inlineEditing' => true,
            'default' => 'quarterly',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage']],
        ];



        //control for vnx-dynamic-price-obj-storage-v2

        $this->controls['storage_field'] = [
            'tab' => 'content',
            'label' => esc_html__('Storage Field', 'vietnix'),
            'type' => 'repeater',
            'group' => 'info_field',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
            'fields' => [
                'type' => [
                    'label' => esc_html__('Type', 'vietnix'),
                    'type' => 'select',
                    'options' => [
                        'storage' => esc_html__('Storage', 'vietnix'),
                        'data_transfer' => esc_html__('Data Transfer', 'vietnix'),
                        'request' => esc_html__('Request', 'vietnix'),
                    ],
                ],
                'free_quota_ratio' => [
                    'label' => esc_html__('Free Quota Ratio', 'vietnix'),
                    'type' => 'number',
                    'required' => ['type', '=', ['data_transfer']],
                ],
                'title' => [
                    'label' => esc_html__('Title', 'vietnix'),
                    'type' => 'text',
                ],
                'unit' => [
                    'label' => esc_html__('Unit', 'vietnix'),
                    'type' => 'text',
                ],
                'prices' => [
                    'label' => esc_html__('Prices', 'vietnix'),
                    'type' => 'repeater',
                    'required' => ['type', '=', ['storage', 'data_transfer']],
                    'fields' => [
                        'title' => [
                            'label' => esc_html__('Title', 'vietnix'),
                            'type' => 'text',
                        ],
                        'range_price' => [
                            'label' => esc_html__('Comparison Type', 'vietnix'),
                            'type' => 'select',
                            'options' => [
                                'between' => esc_html__('Between (A - B)', 'vietnix'),
                                'greater_than' => esc_html__('Greater than (> A)', 'vietnix'),
                                'less_than' => esc_html__('Less than (< A)', 'vietnix'),
                            ],
                            'default' => 'between',
                        ],
                        'range' => [
                            'label' => esc_html__('Range From', 'vietnix'),
                            'type' => 'number',
                        ],
                        'range_to' => [
                            'label' => esc_html__('Range To', 'vietnix'),
                            'type' => 'number',
                            'required' => ['range_price', '=', ['between']],
                        ],
                        'action_price' => [
                            'label' => esc_html__('Action Price', 'vietnix'),
                            'type' => 'select',
                            'options' => [
                                'contact' => esc_html__('Contact', 'vietnix'),
                                'price' => esc_html__('Price', 'vietnix'),
                            ],
                        ],
                        'price' => [
                            'label' => esc_html__('Price (per GB)', 'vietnix'),
                            'type' => 'number',
                            'required' => ['action_price', '=', 'price'],
                        ],
                        'id_contact_btn' => [
                            'label' => esc_html__('ID Contact Button', 'vietnix'),
                            'type' => 'text',
                            'required' => ['action_price', '=', 'contact'],
                        ],
                        // 'pid' => [
                        //     'label' => esc_html__('pid', 'vietnix'),
                        //     'type' => 'number',
                        // ],
                    ],
                ],
                'value' => [
                    'label' => esc_html__('Default value', 'vietnix'),
                    'type' => 'number',
                ],
                'min' => [
                    'label' => esc_html__('Min product', 'vietnix'),
                    'type' => 'number',
                ],
                'max' => [
                    'label' => esc_html__('Max product', 'vietnix'),
                    'type' => 'number',
                ],
                'step' => [
                    'label' => esc_html__('Step product', 'vietnix'),
                    'type' => 'number',
                ],
                'unit_price' => [
                    'label' => esc_html__('Unit Price', 'vietnix'),
                    'type' => 'number',
                    'required' => ['type', '=', ['request']],
                ],
                'text_content' => [
                    'label' => esc_html__('Text Content', 'vietnix'),
                    'type' => 'text',
                ],
                'icon_content' => [
                    'label' => esc_html__('Icon Content', 'vietnix'),
                    'type' => 'icon',
                    'library' => 'svg',
                ],
            ],
        ];

        $this->controls['cycle_field'] = [
            'tab' => 'content',
            'label' => esc_html__('Cycle Field', 'vietnix'),
            'type' => 'repeater',
            'group' => 'info_cycle',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
            'fields' => [
                'title' => [
                    'label' => esc_html__('Title', 'vietnix'),
                    'type' => 'text',
                ],
                'months' => [
                    'label' => esc_html__('Số tháng', 'vietnix'),
                    'type' => 'number',
                    'placeholder' => '1',
                ],
                'percent' => [
                    'label' => esc_html__('Giảm giá (%)', 'vietnix'),
                    'type' => 'number',
                    'placeholder' => '0',
                ],
                'pid' => [
                    'label' => esc_html__('ID Cycle', 'vietnix'),
                    'type' => 'number',
                ],
            ],
        ];

        $this->controls['left_heading'] = [
            'tab'   => 'content',
            'label' => esc_html__('Left Panel Heading', 'vietnix'),
            'type'  => 'text',
            'default' => 'Tuỳ chỉnh tài nguyên',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
        ];

        $this->controls['right_heading'] = [
            'tab'   => 'content',
            'label' => esc_html__('Right Panel Heading', 'vietnix'),
            'type'  => 'text',
            'default' => 'Ước tính chi phí/ tháng',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
        ];

        $this->controls['vat_note'] = [
            'tab'   => 'content',
            'label' => esc_html__('VAT Note', 'vietnix'),
            'type'  => 'text',
            'default' => 'Giá chưa bao gồm VAT',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
        ];

        $this->controls['cta_label'] = [
            'tab'   => 'content',
            'label' => esc_html__('CTA Label', 'vietnix'),
            'type'  => 'text',
            'default' => 'Đăng ký ngay',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
        ];

        $this->controls['cta_contact_label'] = [
            'tab'     => 'content',
            'label'   => esc_html__('Nút Liên hệ - Label', 'vietnix'),
            'type'    => 'text',
            'default' => 'Liên hệ',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
        ];

        $this->controls['cta_contact_url'] = [
            'tab'         => 'content',
            'label'       => esc_html__('Nút Liên hệ - URL / Anchor (#id)', 'vietnix'),
            'type'        => 'text',
            'placeholder' => '#contact hoặc https://',
            'description' => esc_html__('Nhập #id để scroll đến section, hoặc URL đầy đủ để mở tab mới.', 'vietnix'),
            'required'    => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
        ];
        $this->controls['id_product'] = [
            'tab'   => 'content',
            'label' => esc_html__('ID Product', 'vietnix'),
            'type'  => 'number',
            'default' => '54',
            'required' => ['style', '=', ['vnx-dynamic-price-obj-storage-v2']],
        ];
    }

    public function render()
    {
        $settings = $this->settings;
        if ($settings['style'] == 'vnx-dynamic-price-obj-storage') {
            View::render("widgets/bricks/vnx-dynamic-price-obj-storage", $this);
        } else {
            View::render("widgets/bricks/vnx-dynamic/" . $settings['style'], $this);
        }
    }
}