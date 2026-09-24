<?php
require_once VNX_PLUGIN_PATH_CENTER . '/extentions/helper/gapi.php';

function sendDiscordCallmeMessage_Center($request)
{
  $data = $request->get_body_params();

  if (empty($data) || empty($data['phone'])) {
    return false;
  }

  if (!wp_verify_nonce($data['nonce_data'] ?? '', 'form-call-me-now')) {
    return wp_send_json(false, 400);
  }

  $msg = !empty($data['message']) ? $data['message'] : '';

  // Route van giu ten /vietnix/telegram vi frontend dang goi, nhung tin gui ve Discord.
  require_once VNX_PLUGIN_PATH_CENTER . '/extentions/helper/discord_notify.php';
  $sent = vnx_discord_notify_Center(
    vnx_discord_get_webhook_Center('discord_webhook_callme'),
    '☎ GỌI LẠI CHO TÔI',
    '✆ ' . vnx_discord_escape_Center($data['phone']) . ($msg !== '' ? "\n\n" . vnx_discord_escape_Center($msg) : ''),
    'f39c12'
  );

  if (!$sent) {
    wp_send_json(false, 400);
  } else {
    wp_send_json(true, 200);
  }
}

add_action('rest_api_init', function () {
  register_rest_route('vietnix', '/telegram', array(
    'methods' => 'POST',
    'callback' => 'sendDiscordCallmeMessage_Center',
  ));
});

/*
 *************************************
 * API News for Portal
 * URL: /wp-json/vietnix/news
 * Method: GET
 * @params: cat=xxxx
 * 
 * return array last 3 posts
 */

function getNewsForPortal_Center($request)
{

  $data = $request->get_query_params();

  if (empty($data) || empty($data['cat'])) {
    return false;
  }

  $cat = get_category_by_slug($request['cat']);

  $posts = get_posts(
    array(
      'post_type' => 'post',
      'posts_per_page' => 3,
      'category__in' => array($cat->term_id)
    )
  );

  if (empty($posts)) {
    return [];
  }
  $results = [];
  foreach ($posts as $post) {
    $item = new stdClass();
    $item->title = $post->post_title;
    $item->expert = $post->post_excerpt;
    $item->content = $post->content;
    $item->date = $post->post_date;
    $item->thumbnail = get_the_post_thumbnail_url($post->ID, 'blog_thumbnail') ?: get_template_directory_uri() . "/assets/images/common/default.jpg";
    array_push($results, $item);
  }

  return $results;
}

add_action('rest_api_init', function () {
  register_rest_route('vietnix', '/news', array(
    'methods' => 'GET',
    'callback' => 'getNewsForPortal_Center',
  ));
});

if ( !function_exists( 'domain_transfer_form_validate_Center' ) ) {
  function domain_transfer_form_validate_Center( $validation_errors, $form )
  {
    $form_field = $form->get_fields();

    if ( !isset( $form_field ) || !isset( $form_field[ 'domain_transfer' ] ) || !isset( $form_field[ 'transfer_auth' ] ) )
      return $validation_errors; // filter: phai tra lai mang loi, 'return;' se xoa loi cua callback khac

    if ( isset( $form_field[ 'domain_transfer' ] ) )
      if ( !preg_match( '/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}(?:\.[a-zA-Z]{2,})?$/', $form_field[ 'domain_transfer' ] ) )
        array_push( $validation_errors, '<span class="vnx_validate_domain">Tên miền không hợp lệ</span>' );

    // Ma EPP thuong co ky tu dac biet (nhieu registry bat buoc), chi chu + so se chan nham ma hop le.
    // Van loai khoang trang va < > " ' ` \ vi ma nay duoc luu vao cookie gio hang.
    if ( isset( $form_field[ 'transfer_auth' ] ) )
      if ( !preg_match( '/^[A-Za-z0-9!@#$%^&*()_+=\[\]{};:,.\/?|~-]{6,64}$/', $form_field[ 'transfer_auth' ] ) )
        array_push( $validation_errors, '<span class="vnx_validate_auth">Authorization Code không hợp lệ</span>' );

    // array_push( $validation_errors, json_encode( $form_field ) );
    return $validation_errors;
  }
  add_filter( 'bricks/form/validate', 'domain_transfer_form_validate_Center', -10, 2 );
}

if ( !function_exists( 'domain_transfer_form_action_Center' ) ) {
  function domain_transfer_form_action_Center( $form )
  {
    $form_field = $form->get_fields();

    if ( !isset( $form_field ) || !isset( $form_field[ 'domain_transfer' ] ) || !isset( $form_field[ 'transfer_auth' ] ) )
      return;

    $data_arr = array(
      array(
        'domain'      => $form_field[ 'domain_transfer' ],
        'authen_code' => $form_field[ 'transfer_auth' ],
      )
    );

    $data_json = json_encode( $data_arr );
    $data = base64_encode( $data_json );

    setcookie( 'vnx_domain_carts', $data, time() + 3600, '/', '.vietnix.vn' );
    sleep( 1 );
  }
  add_action( 'bricks/form/custom_action', 'domain_transfer_form_action_Center', -10, 1 );
}
