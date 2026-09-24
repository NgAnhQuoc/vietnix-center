<?php

/**
 * vietnix_banner_register_post_type_Center function
 *
 * @return void
 */
function vietnix_banner_register_post_type_Center()
{
  $labels = array(
    'name'               => esc_html__('Vietnix Banner', 'vietnix_banner'),
    'singular_name'      => esc_html__('Vietnix Banner', 'vietnix_banner'),
    'add_new'            => esc_html_x('Add New Banner', 'vietnix_banner'),
    'add_new_item'       => esc_html__('Add New Banner', 'vietnix_banner'),
    'edit_item'          => esc_html__('Edit Banner', 'vietnix_banner'),
    'new_item'           => esc_html__('New Banner', 'vietnix_banner'),
    'all_items'          => esc_html__('All Banner', 'vietnix_banner'),
    'view_item'          => esc_html__('View Banner', 'vietnix_banner'),
    'not_found'          => esc_html__('No Banner found', 'vietnix_banner'),
    'not_found_in_trash' => esc_html__('No Banner found in Trash', 'vietnix_banner'),
    'parent_item_colon'  => esc_html__('Parent Team:', 'vietnix_banner'),
    'menu_name'          => esc_html__('Vietnix Banner', 'vietnix_banner'),
  );

  $args = array(
    'labels'             => $labels,
    'public'             => false,
    'show_ui'            => true,
    'capability_type'    => 'post',
    'hierarchical'       => false,
    'rewrite'            => false,
    'publicly_queryable' => false,
    'show_in_menu'       => true,
    'show_in_admin_bar'  => true,
    'can_export'         => true,
    'has_archive'        => false,
    'menu_position'      => 100,
    'menu_icon'          => 'dashicons-images-alt',
    'supports'           => array('title', 'editor')
  );
  register_post_type('vietnix_banner', $args);
}
add_action('init', 'vietnix_banner_register_post_type_Center');

/**
 * vietnix_banner_add_meta_box_Center function
 *
 * @return void
 */
function vietnix_banner_add_meta_box_Center()
{
  add_meta_box('vietnix-banner-shortcode-box', 'Vietnix Banner', 'vietnix_banner_shortcode_box_Center', 'vietnix_banner', 'side', 'high');
}
add_action("add_meta_boxes", "vietnix_banner_add_meta_box_Center");

/**
 * vietnix_banner_shortcode_box_Center function
 *
 * @param [type] $post
 * @return void
 */
function vietnix_banner_shortcode_box_Center($post)
{
?>
  <h4><?php echo esc_html('Shortcode', 'vietnix_banner'); ?></h4>
  <input type='text' class='widefat' value='[VIETNIX_BANNER id="<?php echo $post->ID; ?>"]' readonly="">

  <h4><?php echo esc_html('PHP Code', 'vietnix_banner'); ?></h4>
  <input type='text' class='widefat' value="&lt;?php echo do_shortcode('[VIETNIX_BANNER id=&quot;<?php echo $post->ID; ?>&quot;]'); ?&gt;" readonly="">
<?php
}
