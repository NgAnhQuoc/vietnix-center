<?php

$data = isset($data) ? $data : new stdClass();

$settings = $data->settings;
$icon = !empty($settings['icon']) ? \Bricks\Element::render_icon($settings['icon']) : false;

$label = $data->render_dynamic_data($settings['label']);
$data->set_attribute( '_root', 'class', 'vnx-post-view-counter' );

if (!$icon) {
  return $data->render_element_placeholder(
    [
      'title' => esc_html__('No icon selected.', 'vnx'),
    ]
  );
}
;


  echo "<div {$data->render_attributes('_root')}>";
  if (function_exists('pvc_get_post_views')) :
?>
  <div class="vnx-view-icon"><?=$icon?></div>
  <div class="vnx-view-counter"><?=esc_html_e(pvc_get_post_views()); ?></div>
  <div class="vnx-view-label"><?=$label?></div>
<?php
  echo "</div>";
  endif;