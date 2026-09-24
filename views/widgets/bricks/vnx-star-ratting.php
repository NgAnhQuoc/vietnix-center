<?php

$data = isset($data) ? $data : new stdClass();

$settings = $data->settings;
$icon = !empty($settings['icon']) ? \Bricks\Element::render_icon($settings['icon']) : false;
$iconhalf = !empty($settings['iconhalf']) ? \Bricks\Element::render_icon($settings['iconhalf']) : false;
$iconempty = !empty($settings['iconempty']) ? \Bricks\Element::render_icon($settings['iconempty']) : false;

$rating = $data->render_dynamic_data($settings['rating']);
$star = $data->render_dynamic_data($settings['star']);

if (!$icon) {
  return $data->render_element_placeholder(
    [
      'title' => esc_html__('No icon selected.', 'bricks'),
    ]
  );
}
;
if ($rating) {
  if ($rating <= 0) {
    $average_stars = 0;
  } else {
    $average_stars = round($rating * 2) / 2;
  }

  $drawn = $star;

  echo "<div {$data->render_attributes('_root')}>";

  // full stars.
  for ($i = 0; $i < floor($average_stars); $i++) {
    $drawn--;
    echo $icon;
  }

  // half stars.
  if ($rating - floor($average_stars) === 0.5) {
    $drawn--;
    echo $iconhalf;
  }

  // empty stars.
  for ($i = 0; $i < $drawn; $i++) {
    echo $iconempty;
  }

  echo "</div>";
}