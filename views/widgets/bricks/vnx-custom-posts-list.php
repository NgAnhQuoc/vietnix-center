<?php

use HelperCenter\View;

$settings = $data->settings;
$my_class = ['vnx_element'];
$data->set_attribute('_root', 'class', $my_class);
echo "<div {$data->render_attributes('_root')}>";
$widget_syle = isset($settings['widget_style']) ? $settings['widget_style'] : '1';

$template_id = !empty($settings['loop_item_template']) ? intval($settings['loop_item_template']) : false;
$template_nodata_id = !empty($settings['template_nodata_id']) ? intval($settings['template_nodata_id']) : false;
if ($template_id && get_post_status($template_id) !== 'publish') {
  return $data->render_element_placeholder(
    [
      'title' => esc_html__('Template has not been published.', 'vietnix'),
    ]
  );
}
if (!$template_id) {
  return $data->render_element_placeholder(
    [
      'title' => esc_html__('No template selected.', 'vietnix'),
    ]
  );
} else {
  switch ($widget_syle) {
    case '1':
      View::render("widgets/bricks/post/vietnix-posts-list", $data);
      wp_register_style('css_post_template-center', content_url() . '/uploads/bricks/css/post-' . $template_id . '.min.css');
      wp_enqueue_style('css_post_template-center');
      wp_register_style('css_post_notemplate-center', content_url() . '/uploads/bricks/css/post-' . $template_nodata_id . '.min.css');
      wp_enqueue_style('css_post_notemplate-center');
      break;
    case '2':
      View::render("widgets/bricks/post/vietnix-posts-list-style-2", $data);
      wp_register_style('css_post_template-center', content_url() . '/uploads/bricks/css/post-' . $template_id . '.min.css');
      wp_enqueue_style('css_post_template-center');
      wp_register_style('css_post_notemplate-center', content_url() . '/uploads/bricks/css/post-' . $template_nodata_id . '.min.css');
      wp_enqueue_style('css_post_notemplate-center');
      break;
    default:
      View::render("widgets/bricks/post/vietnix-posts-list", $data);
      break;
  }
}

echo '</div>';
