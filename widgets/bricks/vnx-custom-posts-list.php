<?php

namespace Bricks;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use HelperCenter\View;
use Bricks\Templates;
use Bricks\Query;
use Bricks\Frontend;

class Vnx_Custom_Posts_List_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name     = 'vnx-custom-posts-list';
  public $icon     = 'ti-write';

  public function get_label()
  {
    return esc_html__('VNX Custom Posts List', 'bricks');
  }
  public function enqueue_scripts()
  {
    wp_register_script('vnx_custom_posts_list-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/posts_list.js');
    wp_enqueue_script('vnx_custom_posts_list-center');
  }

  public function set_control_groups()
  {
    $this->control_groups['item'] = [
      'title' => esc_html__('Item', 'vietnix'),
      'tab'   => 'content',
    ];
    $this->control_groups['pagination'] = [
      'title' => esc_html__('Pagination', 'vietnix'),
      'tab'   => 'content',
    ];
  }
  public function set_controls()
  {
    $this->controls['widget_style'] = [
      'tab'         => 'content',
      'label'       => esc_html__('Widget Style', 'vietnix'),
      'type'        => 'select',
      'options'     => [
        '1' => esc_html__('Style 1', 'vietnix'),
        '2' => esc_html__('Style 2', 'vietnix'),
      ],
      'group' => 'item',
      'inline'      => true,
      'placeholder' => esc_html__('Select style', 'vietnix'),
      'multiple'    => false,
      'searchable'  => true,
      'clearable'   => true,
      'default'     => '',
    ];
    $this->controls['loop_item_template'] = [
      'tab'         => 'content',
      'group' => 'item',
      'label'       => esc_html__('Loop Item Template', 'vietnix'),
      'type'        => 'select',
      'options'     => bricks_is_builder() ? Templates::get_templates_list(['section', 'content', 'popup'], get_the_ID()) : [],
      'searchable'  => true,
      'placeholder' => esc_html__('Select template', 'vietnix'),
    ];
    $this->controls['post_type'] = [
      'tab'         => 'content',
      'label'       => esc_html__('Post Type', 'vietnix'),
      'type'        => 'select',
      'options'     => $this->get_post_type_options(),
      'group' => 'item',
      'inline'      => true,
      'placeholder' => esc_html__('Select Post Type', 'vietnix'),
      'multiple'    => false,
      'searchable'  => true,
      'clearable'   => true,
      'default'     => 'post',
    ];
    $this->controls['orderby'] = [
      'tab' => 'content',
      'group' => 'item',
      'label' => esc_html__('Order By', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => esc_html__('date', 'vietnix'),
      'description' => esc_html__('Muốn order theo meta key thì nhập meta_value_num hoặc meta_value, rồi điền meta_key vào ô dưới, nên nhập meta_value_num nếu giá trị Meta Key là số. Theo lượt xem thì nhập post_views', 'vietnix'),
      'placeholder' => esc_html__('date, name, post_views, meta_value_num,...', 'vietnix'),
    ];
    $this->controls['meta_key'] = [
      'tab' => 'content',
      'group' => 'item',
      'label' => esc_html__('Order Meta Key', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default' => esc_html__('', 'vietnix'),
      'description' => esc_html__('Chỉ nhập nếu muốn order theo meta key', 'vietnix'),
      'placeholder' => esc_html__('price, sale_price,... ', 'vietnix'),
    ];
    $this->controls['order'] = [
      'tab'         => 'content',
      'label'       => esc_html__('Order', 'vietnix'),
      'type'        => 'select',
      'options'     => [
        'DESC' => esc_html__('DESC', 'vietnix'),
        'ASC' => esc_html__('ASC', 'vietnix'),
      ],
      'group' => 'item',
      'default'     => '',
    ];
    $this->controls['template_nodata_id'] = [
      'tab'         => 'content',
      'group' => 'item',
      'label'       => esc_html__('Template when no have post', 'vietnix'),
      'type'        => 'select',
      'options'     => bricks_is_builder() ? Templates::get_templates_list(['section', 'content', 'popup'], get_the_ID()) : [],
      'searchable'  => true,
      'placeholder' => esc_html__('Select template', 'vietnix'),
    ];
    $this->controls['num_posts'] = [
      'tab' => 'content',
      'group' => 'item',
      'label' => esc_html__('Posts per view', 'vietnix'),
      'type' => 'number',
      'min' => 1,
      'max' => 24,
      'step' => 1,
      'default'     => '4',
    ];
    $this->controls['num_row'] = [
      'tab' => 'content',
      'group' => 'item',
      'label' => esc_html__('Columns', 'vietnix'),
      'type' => 'number',
      'min' => 1,
      'max' => 8,
      'step' => 1,
      'default'     => '4',
    ];
    $this->controls['widget_id'] = [
      'tab' => 'content',
      'group' => 'item',
      'label' => esc_html__('Widget ID', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'default'     => 'posts_1',
      'description' => esc_html__('Enter widget ID to using Filter widget', 'vietnix'),
    ];
    $this->controls['use_paginte'] = [
      'tab'         => 'content',
      'label'       => esc_html__('Use Pagitation', 'vietnix'),
      'type'        => 'select',
      'options'     => [
        '1' => esc_html__('Yes', 'vietnix'),
        '0' => esc_html__('No', 'vietnix'),
      ],
      'group' => 'pagination',
    ];
    $this->controls['page_text'] = [
      'tab' => 'content',
      'group' => 'pagination',
      'label' => esc_html__('Page text', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
    ];
    $this->controls['of_text'] = [
      'tab' => 'content',
      'group' => 'pagination',
      'label' => esc_html__('Of text', 'vietnix'),
      'placeholder' => 'Enter of text',
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
    ];
    $this->controls['next_btn_text'] = [
      'tab' => 'content',
      'group' => 'pagination',
      'label' => esc_html__('Next button text', 'vietnix'),
      'placeholder' => 'Enter Next button text',
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
    ];
    $this->controls['prv_btn_text'] = [
      'tab' => 'content',
      'group' => 'pagination',
      'label' => esc_html__('Previous button text', 'vietnix'),
      'placeholder' => 'Enter Previous button text',
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
    ];
  }

  public function render()
  {
    View::render("widgets/bricks/vnx-custom-posts-list", $this);
  }
  private function get_post_type_options()
  {
    $post_types = get_post_types(['public' => true], 'objects');
    $options = [];

    foreach ($post_types as $post_type) {
      $options[$post_type->name] = $post_type->label;
    }

    return $options;
  }
}
