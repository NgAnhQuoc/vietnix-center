<?php
require_once VNX_PLUGIN_PATH_CENTER . '/extentions/helper/discord_notify.php';

/**
 * Thong tin bai viet de gui Discord.
 * post va lap-trinh chi khac taxonomy va ten ACF field SEO/Writer.
 */
function vnx_new_post_info_Center($post)
{
  $config = array(
    'post' => array('taxonomy' => 'category', 'seo' => 'seo_author', 'writer' => 'writer'),
    'lap-trinh' => array('taxonomy' => 'tax_lap-trinh', 'seo' => 'seo_author_dev', 'writer' => 'writer_dev'),
  );
  if (!isset($config[$post->post_type])) {
    return null;
  }
  $config = $config[$post->post_type];

  $terms = get_the_terms($post->ID, $config['taxonomy']);
  $user_name = function ($field) use ($post) {
    $user_id = get_field($field, $post->ID, false, false);
    $user = $user_id ? get_userdata($user_id) : false;
    return $user ? $user->display_name : '';
  };

  return array(
    'link' => get_permalink($post->ID),
    'author' => get_the_author_meta('display_name', $post->post_author),
    'seo' => $user_name($config['seo']),
    'writer' => $user_name($config['writer']),
    'category' => is_array($terms) ? implode(', ', wp_list_pluck($terms, 'name')) : '',
  );
}

function vnx_my_check_new_post_Center($new_status, $old_status, $post)
{
  try {
    if ($new_status !== 'publish' || $new_status === $old_status) {
      return;
    }

    $info = vnx_new_post_info_Center($post);
    if (!$info) {
      return;
    }

    // Link de nguyen (khong escape) de Discord tu nhan dang URL.
    $content = $info['link'] . "\n\n" . vnx_discord_format_fields_Center(array(
      'Tác giả' => $info['author'],
      'SEO' => $info['seo'],
      'Writer' => $info['writer'],
      'Category' => $info['category'],
    ));
    // Hook chi dang ky tren production nen khong can guard moi truong o day.
    vnx_discord_notify_Center(
      vnx_discord_get_webhook_Center('discord_webhook_post'),
      '📝 Bài viết mới Publish trên website Vietnix',
      $content,
      '2ecc71'
    );
  } catch (\Throwable $ex) {
    error_log('[vietnix-center] New post notify failed: ' . $ex->getMessage());
  }
}
if (get_home_url() === "https://vietnix.vn") {
  add_action('transition_post_status', 'vnx_my_check_new_post_Center', 10, 3);
}
