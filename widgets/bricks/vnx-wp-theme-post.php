<?php

if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

use HelperCenter\View;
use Bricks\Templates;

class VNX_Wp_Theme_Post_Center extends \Bricks\Element
{
  public $category = 'vietnix';
  public $name = 'vnx-wp-theme-posts';
  public $icon = 'fa-brands fa-wordpress';
  public $scripts = ['vnxPostsElement'];

  public function get_label()
  {
    return esc_html__('VNX WP Theme Posts', 'vietnix');
  }

  public function enqueue_scripts()
  {
    wp_register_script('themes_posts_filter-center', VNX_PLUGIN_URL_CENTER . 'widgets/inc/bricks/js/themes_post_filter.js');
    wp_enqueue_script('themes_posts_filter-center');
    wp_localize_script(
      'themes_posts_filter-center',
      'vietnix_themes_post_filter_js',
      array(
        'url' => admin_url('admin-ajax.php'),
        // 'load_post_nonce' => wp_create_nonce( 'ajax_load_post_nonce' ),
      )
    );
  }

  public function set_control_groups()
  {
    $this->control_groups['content'] = [
      'title' => esc_html__('Content', 'vietnix'),
      'tab' => 'content',
    ];

    $this->control_groups['pagination'] = [
      'title' => esc_html__('Paginatiom', 'vietnix'),
      'tab' => 'content',
    ];

    $this->control_groups['fillter'] = [
      'title' => esc_html__('Fillter', 'vietnix'),
      'tab' => 'content',
    ];
  }

  public function set_controls()
  {
    $this->controls['post_type_slug'] = [
      'tab' => 'content',
      'group' => 'content',
      'label' => esc_html__('Post Type', 'vietnix'),
      'type' => 'select',
      'options' => $this->get_post_type_options(),
      'placeholder' => esc_html__('Chọn Post Type muốn hiển thị', 'vietnix'),
      'multiple' => false,
      'searchable' => true,
      'clearable' => true,
      'inline' => true,
      'default' => '',
    ];

    $this->controls['num_post'] = [
      'tab' => 'content',
      'group' => 'content',
      'label' => esc_html__('Posts per page', 'vietnix'),
      'type' => 'number',
      'min' => 1,
      'step' => 1,
      'max' => 24,
      'inline' => true,
      'default' => 6,
    ];

    $this->controls['widget_style'] = [
      'tab' => 'content',
      'group' => 'content',
      'label' => esc_html__('Widget Style', 'vietnix'),
      'type' => 'select',
      'options' => [
        '1' => esc_html__('Style 1, Tặng Theme WP', 'vietnix'),
        '2' => esc_html__('Style 2, Lập trình', 'vietnix'),
      ],
      'multiple' => false,
      'inline' => true,
      'default' => '1',
    ];

    $this->controls['template'] = [
      'label' => esc_html__('Template', 'vietnix'),
      'group' => 'content',
      'type' => 'select',
      'options' => bricks_is_builder() ? Templates::get_templates_list(['section', 'content', 'popup'], get_the_ID()) : [],
      'searchable' => true,
      'placeholder' => esc_html__('Không dùng cho Widget Style 1', 'vietnix'),
      'required' => ['widget_style', '!=', ['1']],
    ];

    $this->controls['pagination_show'] = [
      'tab' => 'content',
      'group' => 'pagination',
      'label' => esc_html__('Show Pagination', 'vietnix'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => true,
    ];

    $this->controls['show_filter'] = [
      'tab' => 'content',
      'group' => 'fillter',
      'label' => esc_html__('Show Pagination', 'vietnix'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => true,
    ];

    $this->controls['show_all_tab'] = [
      'tab' => 'content',
      'group' => 'fillter',
      'label' => esc_html__('Show ab "All Post', 'vietnix'),
      'type' => 'checkbox',
      'inline' => true,
      'small' => true,
      'default' => true,
    ];

    $this->controls['filter_setting'] = [
      'tab' => 'content',
      'group' => 'fillter',
      'label' => esc_html__('Theme Filter Setting', 'vietnix'),
      'type' => 'repeater',
      'inline' => false,
      'titleProperty' => 'term',
      'default' => [
        [
          'term' => '-1|Select Category',
        ],
      ],
      'fields' => [
        'term' => [
          'label' => esc_html__('Select Category', 'vietnix'),
          'inline' => true,
          'type' => 'select',
          'options' => $this->get_theme_categories(),
          'placeholder' => esc_html__('Chọn Post Type muốn hiển thị', 'vietnix'),
          'multiple' => false,
          'searchable' => true,
          'clearable' => true,
          'inline' => true,
          'default' => '-1|Select Category',
        ]
      ],
    ];

    $this->controls['tax_id'] = [
      'tab' => 'content',
      'label' => esc_html__('Taxonomy to filter', 'vietnix'),
      'type' => 'text',
      'group' => 'fillter',
      'placeholder' => esc_html__('Không dùng cho Widget Style 1', 'vietnix'),
      'inline' => true,
      'default' => '',
      'required' => ['widget_style', '!=', ['1']],
    ];

    $this->controls['tax_filter'] = [
      'tab' => 'content',
      'group' => 'fillter',
      'label' => esc_html__('Term to filter', 'vietnix'),
      'type' => 'repeater',
      'inline' => false,
      'titleProperty' => 'term_name',
      'fields' => [
        'term_id' => [
          'label' => esc_html__('Term ID', 'vietnix'),
          'inline' => true,
          'type' => 'text',
          'placeholder' => esc_html__('Chọn Post Type muốn hiển thị', 'vietnix'),
          'default'     => esc_html__('0', 'vietnix'),
          'clearable' => true,
          'inline' => true,
        ],
        'term_name' => [
          'label' => esc_html__('Term Name', 'vietnix'),
          'type' => 'text',
          'default' => esc_html__('Term name', 'vietnix'),
          'placeholder' => esc_html__('Nhập tên Term của ID tương ứng', 'vietnix'),
        ]
      ],
    ];
  }

  public function render()
  {
    // View::render("widgets/bricks/vnx_theme_post", $this);
    $widget_style = isset($this->settings['widget_style']) && $this->settings['widget_style'] ? $this->settings['widget_style'] : '1';
    switch ($widget_style) {
      case '1':
?>
        <div class="blog main-post py-12 flex flex-col w-full">
          <?php View::render('widgets/bricks/theme_post/widget-theme-posts', [
            'settings' => $this,
          ]); ?>
        </div>
      <?php
        break;

      case '2':
      ?>
        <div class="blog theme_post w-full">
          <?php View::render('widgets/bricks/theme_post/widget-theme-posts-1', [
            'settings' => $this,
          ]);
          $template_id = $this->settings['template'];
          wp_register_style('css_post_template-center', content_url() . '/uploads/bricks/css/post-' . $template_id . '.min.css');
          wp_enqueue_style('css_post_template-center');
          ?>
        </div>
      <?php
        break;

      default:
      ?>
        <div class="blog main-post py-12 flex flex-col w-full">
          <?php View::render('widgets/bricks/theme_post/widget-theme-posts', [
            'settings' => $this,
          ]); ?>
        </div>
<?php
        break;
    }
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

  protected function get_theme_categories()
  {
    $args = array(
      'taxonomy' => 'loai-theme',
      'orderby' => 'name',
      'hide_empty' => false,
    );
    $get_terms = get_terms($args);
    $return = array('-1|Select Category' => 'Select your category');
    if (!empty($get_terms)) {
      foreach ($get_terms as $key => $term) {
        if (!$term)
          continue;
        $a_key = ($term->term_id) ?? '';
        $a_val = ($term->name) ?? '';
        $return["$a_key|$a_val"] = $a_val;
      }
    }
    return $return;
  }
}
