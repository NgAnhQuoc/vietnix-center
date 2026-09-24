<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class Vnx_Posts_Filter_V2_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-posts-fillter-v2';
  public $icon = 'ti-write';

  public function get_label()
  {
    return esc_html__('VNX Posts Filter V2', 'vietnix');
  }

  public function enqueue_scripts()
  {
    wp_register_script('tagifyjs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/tagify.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('tagifyjs-library-center');
    wp_enqueue_style('tagifycss-library-center', VNX_PLUGIN_URL_CENTER . 'assets/css/tagify.css', '1.0.0', 'all');
    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_register_script('vnx_posts_filter-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/posts_handle/vnx_post_fillter.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vnx_posts_filter-center');
  }
  public function set_control_groups()
  {
    $this->control_groups['field_settings'] = [
      'title' => esc_html__('Field Settings', 'vietnix'),
      'tab' => 'content',
      'required' => ['style_layout', '=', ['case_studies', 'jobs']],
    ];
  }
  public function set_controls()
  {
    $this->controls['post_type'] = [
      'tab' => 'content',
      'label' => esc_html__('Post Type', 'vietnix'),
      'type' => 'select',
      'options' => $this->get_post_type_options(),
      'inline' => true,
      'placeholder' => esc_html__('Select Post Type', 'vietnix'),
      'multiple' => false,
      'searchable' => true,
      'clearable' => true,
      'default' => 'jobs',
    ];
    $this->controls['style_layout'] = [
      'tab' => 'content',
      'label' => esc_html__('Style Fillter', 'vietnix'),
      'type' => 'select',
      'options' => [
        'jobs' => esc_html__('Style Jobs', 'vietnix'),
        'case_studies' => esc_html__('Style Case Studies', 'vietnix'),
      ],
      'inline' => true,
      'placeholder' => esc_html__('Select style Fillter', 'vietnix'),
      'multiple' => false,
      'searchable' => true,
      'clearable' => true,
      'default' => 'jobs',
    ];
    $this->controls['acf_key_group'] = [
      'tab' => 'content',
      'label' => esc_html__('ACF key group', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'placeholder' => esc_html__('Type your ACF key group', 'vietnix'),
      'default' => '',
    ];

    $this->controls['widget_target_id'] = [
      'tab' => 'content',
      'label' => esc_html__('Widget Target ID', 'vietnix'),
      'type' => 'text',
      'spellcheck' => true,
      'inlineEditing' => true,
      'placeholder' => esc_html__('Enter widget ID of Vietnix Posts List widget', 'vietnix'),
      'default' => 'posts_1',
    ];

    $this->controls['filter_layout'] = [
      'tab' => 'content',
      'group' => 'field_settings',
      'label' => esc_html__('Field Type', 'vietnix'),
      'type' => 'repeater',
      'inline' => false,
      'titleProperty' => 'field',
      'required' => ['style_layout', '=', ['case_studies', 'jobs']],
      'default' => '',
      'fields' => [
        'title' => [
          'label' => esc_html__('Field name', 'vietnix'),
          'type' => 'text',
          'placeholder' => 'Field name',
        ],
      ],
    ];

    $this->controls['per_page']=[
      'group' => 'field_settings',
      'tab' => 'content',
      'label' => esc_html__('Posts Per Page', 'vietnix'),
      'type' => 'number',
      'default' => 6,
      'required' => ['style_layout', '=', ['case_studies', 'jobs']],
    ];
    $this->controls['text_btn'] = [
      'group' => 'field_settings',
      'tab' => 'content',
      'label' => esc_html__('Text Button', 'vietnix'),
      'type' => 'text',
      'default' => 'Lọc',
      'placeholder' => esc_html__('Type your text button', 'vietnix'),
      'required' => ['style_layout', '=', ['case_studies', 'jobs']],
    ];
    $this->controls['text_btn_clear'] = [
      'group' => 'field_settings',
      'tab' => 'content',
      'label' => esc_html__('Text Button Clear', 'vietnix'),
      'type' => 'text',
      'default' => 'Clear all',
      'placeholder' => esc_html__('Type your text button clear', 'vietnix'),
      'required' => ['style_layout', '=', ['jobs']],
    ];
  }

  public function render()
  {
    $settings = $this->settings;
    $style_layout = isset($settings['style_layout']) ? $settings['style_layout'] : '';

    $my_class = [
      'vnx_element',
      $settings['style_layout']
    ];
    $this->set_attribute('_root', 'class', $my_class);
    switch ($style_layout) {
      case 'jobs':
        echo "<div {$this->render_attributes('_root')}>";
        View::render("widgets/bricks/post/filter/vnx-jobs-filter", $this);
        echo '</div>';
        break;
      case 'case_studies':
        echo "<div {$this->render_attributes('_root')}>";
        View::render("widgets/bricks/post/filter/vnx-case-fillter", $this);
        echo '</div>';
        break;
    }
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
