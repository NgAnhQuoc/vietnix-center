<?php 
use HelperCenter\View;

if (!isset($data) || !isset($data->settings)) {
  if (current_user_can('update_core')) {
    echo '<div class="vnx_error no_data"><b>Không có data truyền vào: ' . esc_html(__FILE__) . '</b></div>';
  }
  return;
}

$settings = $data->settings;
$widget_style = isset($settings['widget_style']) ? $settings['widget_style'] : 'theme_posts';

$data->set_attribute('_root', 'class', $widget_style);
echo "<div {$data->render_attributes('_root')}>";
if($widget_style == 'theme_posts') {
  View::render("widgets/bricks/theme_post/vnx-theme-posts", $data);
}
echo '</div>';