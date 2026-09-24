<?php 
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;
use Bricks\Templates;
use Bricks\Query;
use Bricks\Frontend;

class VNX_Theme_Posts_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-theme-posts';
  public $icon = 'fa-brands fa-wordpress';

  public function get_label()
  {
    return esc_html__('VNX Theme Posts', 'vietnix');
  }

  public function enqueue_scripts()
  {
    wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('vuejs-library-center');
    wp_register_script('vnx_theme_posts-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/posts_handle/vnx_theme_posts.js', ['jquery', 'vuejs-library-center'], '1.0', true);
    wp_enqueue_script('vnx_theme_posts-center');
  }

  public function set_control_groups()
  {

    $this->control_groups['theme_posts'] = [
      'title' => esc_html__('Theme Posts', 'vietnix'),
      'tab' => 'content',
    ];

    $this->control_groups['fillter'] = [
      'title' => esc_html__('Fillter', 'vietnix'),
      'tab' => 'content',
    ];

    $this->control_groups['pagination'] = [
      'title' => esc_html__('Pagination', 'vietnix'),
      'tab' => 'content',
    ];

    $this->control_groups['template'] = [
      'title' => esc_html__('Template', 'vietnix'),
      'tab' => 'content',
    ];
  }

  public function set_controls()
  {

    $this->controls['widget_style'] = [
      'tab' => 'content',
      'label' => esc_html__('Widget Style', 'vietnix'),
      'type' => 'select',
      'options' => [
        'theme_posts' => esc_html__('Style 1: Theme Posts', 'vietnix'),
      ],
      'inline' => true,
      'placeholder' => esc_html__('Select Style', 'vietnix'),
      'multiple' => false,
      'searchable' => true,
      'clearable' => true,
      'default' => '1',
      'group' => 'theme_posts',
    ];

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
      'group' => 'theme_posts',
    ];

    $this->controls['text_filter'] = [
      'tab' => 'content',
      'label' => esc_html__('Text Filter', 'vietnix'),
      'type' => 'text',
      'inline' => true,
      'default' => 'Bộ lọc',
      'group' => 'fillter',
    ];

    $this->controls['icon_filter'] = [
      'tab' => 'content',
      'label' => esc_html__('Icon Filter', 'vietnix'),
      'type' => 'icon',
      'inline' => true,
      'default' => [
        'library' => 'themify',
        'icon' => 'ti-filter',
      ],
      'group' => 'fillter',
    ];

    $this->controls['num_post'] = [
      'tab' => 'content',
      'label' => esc_html__('Posts per page', 'vietnix'),
      'type' => 'number',
      'min' => 1,
      'step' => 1,
      'max' => 24,
      'inline' => true,
      'default' => 6,
      'placeholder' => 6,
      'responsive' => true,
      'group' => 'pagination',
    ];

    $this->controls['pagination_show'] = [
      'tab' => 'content',
      'label' => esc_html__('Show Pagination', 'vietnix'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => true,
      'group' => 'pagination',
    ];

    $this->controls['pagination_icon_prev'] = [
      'tab' => 'content',
      'label' => esc_html__('Pagination Icon Prev', 'vietnix'),
      'type' => 'icon',
      'inline' => true,
      'default' => [
        'library' => 'themify',
        'icon' => 'ti-angle-left',
      ],
      'required' => ['pagination_show', '=', true],
      'group' => 'pagination',
    ];

    $this->controls['pagination_icon_next'] = [
      'tab' => 'content',
      'label' => esc_html__('Pagination Icon Next', 'vietnix'),
      'type' => 'icon',
      'inline' => true,
      'default' => [
        'library' => 'themify',
        'icon' => 'ti-angle-right',
      ],
      'required' => ['pagination_show', '=', true],
      'group' => 'pagination',
    ];
    $this->controls['pagination_column'] = [
      'tab' => 'content',
      'label' => esc_html__('Pagination Column', 'vietnix'),
      'type' => 'text',
      'default' => '2',
      'group' => 'pagination',
    ];

    $this->controls['loop_item_template'] = [
      'tab' => 'content',
      'group' => 'template',
      'label' => esc_html__('Template Item', 'vietnix'),
      'type' => 'select',
      'options' => bricks_is_builder() ? Templates::get_templates_list(['section', 'content', 'popup'], get_the_ID()) : [],
      'placeholder' => esc_html__('Select template', 'vietnix'),
      'default' => '',
    ];

    $this->controls['loop_item_not_template'] = [
      'tab' => 'content',
      'group' => 'template',
      'label' => esc_html__('Template Item Not Found', 'vietnix'),
      'type' => 'select',
      'options' => bricks_is_builder() ? Templates::get_templates_list(['section', 'content', 'popup'], get_the_ID()) : [],
      'placeholder' => esc_html__('Select template', 'vietnix'),
      'default' => '',
    ];
  }

  public function render()
  {
    $template_id = $this->settings['loop_item_template'];
    $template_not_id = $this->settings['loop_item_not_template'];
    if ($template_id) {
      wp_register_style('css_post_template-center', content_url() . '/uploads/bricks/css/post-' . $template_id . '.min.css');
      wp_enqueue_style('css_post_template-center');
    }
    if ($template_not_id) {
      wp_register_style('css_post_template_not-center', content_url() . '/uploads/bricks/css/post-' . $template_not_id . '.min.css');
      wp_enqueue_style('css_post_template_not-center');
    }
    View::render("widgets/bricks/vnx-theme-posts", $this);
  }
  protected function get_post_type_options()
  {
    $post_types = get_post_types(['public' => true], 'objects');
    $options = [];
    foreach ($post_types as $post_type) {
      $options[$post_type->name] = $post_type->label;
    }
    return $options;
  }

  public function get_category_options()
  {
    $post_type = isset($this->settings['post_type']) ? $this->settings['post_type'] : '';

    if (empty($post_type)) {
      return [];
    }

    $taxonomies = get_object_taxonomies($post_type, 'objects');
    $options = [];

    if (empty($taxonomies)) {
      return $options;
    }

    foreach ($taxonomies as $taxonomy) {
      $terms = get_terms([
        'taxonomy' => $taxonomy->name,
        'orderby' => 'name',
        'hide_empty' => false,
      ]);

      if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
          $key = "{$term->term_id}";
          $options[$key] = $term->name;
        }
      }
    }
    return $options;
  }

} 