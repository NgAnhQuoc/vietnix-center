<?php
if (!function_exists('conditionally_category_icon_Center')) {
  function conditionally_category_icon_Center($post_id)
  {
    try {

      $field_slug = get_the_category($post_id)[0]->slug;
      $icon = '';
      if ($field_slug == 'khuyen-mai') {
        $icon = '<i class="fas fa-gift"></i>';
      } else if ($field_slug == 'tai-lieu-ky-thuat') {
        $icon = '<i class="fas fa-book"></i>';
      } else if ($field_slug == 'su-kien') {
        $icon = '<i class="far fa-calendar-alt"></i>';
      } else if ($field_slug == 'thong-bao') {
        $icon = '<i class="far fa-bell"></i>';
      } else {
        $icon = '<i class="far fa-folder"></i>';
      }
      return $icon;
    } catch (Exception $e) {
    }
  }
}
if (!function_exists('is_use_bricks_Center')) {
  function is_use_bricks_Center()
  {
    if (is_archive() || is_singular('lap-trinh'))
      return true;
    $post_id = get_the_ID();
    $bricks_meta = get_post_meta($post_id, '_bricks_editor_mode', true);
    $use_bricks = $bricks_meta == 'bricks' ? true : false;
    return $use_bricks;
  }
}
