<?php
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;

class Vnx_Posts_Filter_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-posts-filter';
  public $icon = 'ti-write';

  public function get_label()
  {
    return esc_html__('VNX Posts Filter', 'vietnix');
  }

  public function enqueue_scripts()
  {
    wp_register_script('posts_filter-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/posts_filter.js', ['jquery'], '1.0', true);
    wp_enqueue_script('posts_filter-center');
  }
  public function set_control_groups()
  {
    $this->control_groups[ 'field_settings' ] = [ 
        'title' => esc_html__( 'Field Settings', 'vietnix' ),
        'tab'   => 'content',
        'required' => ['style_layout', '=', ['case_studies']],
    ];
  }
  public function set_controls()
  {
    $this->controls['post_type'] = [
      'tab'         => 'content',
      'label'       => esc_html__('Post Type', 'vietnix'),
      'type'        => 'select',
      'options'     => $this->get_post_type_options(),
      'inline'      => true,
      'placeholder' => esc_html__('Select Post Type', 'vietnix'),
      'multiple'    => false,
      'searchable'  => true,
      'clearable'   => true,
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
      'required' => ['style_layout', '=', ['case_studies']],
      'default' => '',
      'fields' => [
        'title'    => [
          'label'       => esc_html__('Field name', 'vietnix'),
          'type'        => 'text',
          'placeholder' => 'Field name',
        ],
        'icon'=>[
          'tab' => 'content',
          'label' => esc_html__('Field Icon', 'vietnix'),
          'type' => 'icon',
          'default' => [
            'library' => 'fontawesomeRegular',
            'icon' => 'fa fa-question-circle',
          ],
          'css' => [
            [
              'selector' => '.vnx_tooltip_icon',
            ],
          ],
        ],
      ],
    ];
  }

  public function render()
  {
    $settings = $this->settings;
    $style_layout = isset($settings['style_layout']) ? $settings['style_layout'] : '';
    if($style_layout == 'case_studies'){
      View::render("widgets/bricks/post/filter/vietnix-case-filter", $this);
    }else{
      View::render("widgets/bricks/post/filter/vietnix-jobs-filter", $this);
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
