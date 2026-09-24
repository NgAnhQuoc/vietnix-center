<?php

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

if ( is_plugin_active( 'vietnix-banner/vietnix-banner.php' ) ) {
  deactivate_plugins( array( 'vietnix-banner/vietnix-banner.php' ) );
}

if ( !is_plugin_active('vietnix-banner/vietnix-banner.php') ) {

  define_if_not_defined_Center('Vietnix_Banner_Center__FILE__', __FILE__);
  define_if_not_defined_Center('Vietnix_Banner_Center__URL',  plugins_url('/', Vietnix_Banner_Center__FILE__));
  define_if_not_defined_Center('Vietnix_Banner_Center__PATH', plugin_dir_path(Vietnix_Banner_Center__FILE__));
  define_if_not_defined_Center('VIETNIX_BANNER', 'vietnix_banner');
  define_if_not_defined_Center('Plugin_version', VNX_Plugin_version_CENTER);

  require_once(Vietnix_Banner_Center__PATH . 'inc/vietnix-banner/post-type.php');
  require_once(Vietnix_Banner_Center__PATH . 'inc/vietnix-banner/options.php');
  require_once(Vietnix_Banner_Center__PATH . 'inc/vietnix-banner/custom-post.php');
  require_once(Vietnix_Banner_Center__PATH . 'inc/vietnix-banner/meta-box.php');

  class Vietnix_Banner_Center
  {
    /**
     * __construct function
     */
    function __construct()
    {
      // Add custom colunm list banner
      add_action('manage_vietnix_banner_posts_custom_column', array($this, 'vietnix_banner_templates_columns'), 10, 2);
      add_filter('manage_vietnix_banner_posts_columns', array($this, 'vietnix_banner_templates_edit_columns'));

      $this ->activate_vietnix_banner();
      $this->define_plugin_hooks();
    }

    /**
     * define_plugin_hooks
     * The function to add plugin hook
     *
     * @return void
     */
    private function define_plugin_hooks()
    {
      add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
      add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 999999);
      add_action('wp_ajax_update_vietnix_banner_view_count_center', array($this, 'update_vietnix_banner_view_count'));
      add_action('wp_ajax_nopriv_update_vietnix_banner_view_count_center', array($this, 'update_vietnix_banner_view_count'));
      add_action('wp_ajax_update_vietnix_banner_click_count_center', array($this, 'update_vietnix_banner_click_count'));
      add_action('wp_ajax_nopriv_update_vietnix_banner_click_count_center', array($this, 'update_vietnix_banner_click_count'));
    }

    /**
     * enqueue_scripts
     * The code to register wp scripts
     *
     * @return void
     */
    public function enqueue_scripts()
    {
      wp_enqueue_script(VIETNIX_BANNER . '_view_count', plugin_dir_url(Vietnix_Banner_Center__FILE__) . 'inc/js/vietnix-banner-views.js', array('jquery'), Plugin_version, true);
      wp_localize_script(
        VIETNIX_BANNER . '_view_count',
        'vietnix_banner_count_js',
        array(
          'url' => admin_url('admin-ajax.php'),
        )
      );
    }

    /**
     * The code that runs during plugin activation.
     */
    function activate_vietnix_banner()
    {
      if (false == get_option('vietnix_banner_view_count')) {
        add_option('vietnix_banner_view_count', array());
      }
      if (false == get_option('vietnix_banner_click_count')) {
        add_option('vietnix_banner_click_count', array());
      }
    }

    /**
     * Add col in list Vietnix banner
     *
     * @param [type] $columns
     * @return void
     */
    function vietnix_banner_templates_edit_columns($columns)
    {
      $columns['vietnix_banner_shortcode_column'] = __('Shortcode', VIETNIX_BANNER);
      $columns['vietnix_banner_category_column'] = __('Category', VIETNIX_BANNER);
      $columns['vietnix_banner_position_column'] = __('Placement', VIETNIX_BANNER);
      $columns['vietnix_banner_views_column'] = __('Views', VIETNIX_BANNER);
      $columns['vietnix_banner_clicks_column'] = __('Clicks', VIETNIX_BANNER);
      return $columns;
    }

    /**
     * vietnix_banner_templates_columns function
     *
     * @param [type] $column
     * @param [type] $post_id
     * @return void
     */
    function vietnix_banner_templates_columns($column, $post_id)
    {
      $settings = get_post_meta($post_id, '_vietnix_banner_single_settings', true);
      switch ($column) {
        case 'vietnix_banner_shortcode_column':
          echo '<input type=\'text\' class=\'widefat\' value=\'[VIETNIX_BANNER id="' . $post_id . '"]\' readonly="">';
          break;
        case 'vietnix_banner_category_column':
          $banner_categories = $settings['category'] ? $settings['category'] : array();
          foreach ($banner_categories as $key => $cat_id) {
            echo '<a href="' . get_category_link($cat_id) . '">' . get_the_category_by_ID($cat_id) . '</a>';
            echo ($key !== count($banner_categories) - 1) ? ', ' : '';
          }
          break;
        case 'vietnix_banner_position_column':
          $position = $settings['position'];
          $pos_insert = $settings['pos_insert'];
          $tag_insert = $settings['tag_insert'];
          switch ($position) {
            case 'before_content':
              echo __("Begin the content", VIETNIX_BANNER);
              break;
            case 'after_content':
              echo __("End the content", VIETNIX_BANNER);
              break;
            case 'in_content':
              $number_p = $settings['number_p'];
              echo __("$pos_insert $tag_insert position $number_p ", VIETNIX_BANNER);
              break;
            default:
              echo __("Handmade with shortcode", VIETNIX_BANNER);
              break;
          }

          break;
        case 'vietnix_banner_views_column':
          $opt_array = get_option('vietnix_banner_view_count');
          if (isset($opt_array[$post_id])) {
            echo $opt_array[$post_id];
          } else {
            echo '0';
          }
          break;
        case 'vietnix_banner_clicks_column':
          $opt_array = get_option('vietnix_banner_click_count');

          if (isset($opt_array[$post_id])) {
            echo $opt_array[$post_id];
          } else {
            echo '0';
          }
          break;
      }
    }

    /**
     * update_vietnix_banner_view_count
     *
     * @return int
     */
    function update_vietnix_banner_view_count()
    {
      $ids = $_POST['ids'];

      if (!empty($ids)) {

        $ids = ltrim($ids, ',');

        $ids_arr = explode(',', $ids);

        $opt_arr = get_option('vietnix_banner_view_count');

        foreach ($ids_arr as $id) {

          if (isset($opt_arr[$id])) {

            $opt_arr[$id] = (int) $opt_arr[$id] + 1;
          } else {

            $opt_arr[$id] = 1;
          }
        }

        update_option('vietnix_banner_view_count', $opt_arr);
      }

      die();
    }

    /**
     * update_vietnix_banner_click_count
     *
     * @return void
     */
    function update_vietnix_banner_click_count()
    {
      $ad_id = trim($_POST['ad_id']);

      if (!empty($ad_id)) {
        $opt_arr = get_option('vietnix_banner_click_count');

        if (isset($opt_arr[$ad_id])) {
          $opt_arr[$ad_id] = (int) $opt_arr[$ad_id] + 1;
        } else {
          $opt_arr[$ad_id] = 1;
        }

        update_option('vietnix_banner_click_count', $opt_arr);
      }

      die();
    }
  }

  new Vietnix_Banner_Center();
}