<?php

use HelperCenter\View;

if (!isset($data->settings)) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html(__FILE__) . '</div>';
  return;
}
$settings = $data->settings;
$my_class = ['vnx_element'];
$table_style = isset($settings['table_style']) ? $settings['table_style'] : '';
$group_1 = array('compare_hosting_v2');
$layout = isset($settings['mode_table']) && $settings['mode_table'] != null ? 'vnx_mode_dark' : 'vnx_mode_light';
if (in_array($table_style, $group_1))
  array_push($my_class, 'vnx_service_price', 'group_1', $layout);
$data->set_attribute('_root', 'class', $my_class);
echo "<div {$data->render_attributes('_root')}>";

if (!$table_style) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data text-center"><b>Chưa chọn style cho table</div>';
} else {
?>
  <div class="w-full hidden lg:block  <?php echo $table_style; ?>">
    <?php
    View::render("widgets/bricks/vnx-service/hosting/" . $table_style, $data);
    ?>
  </div>
  <!-- /Desktop -->
  <!-- Mobile -->
  <div class="w-full lg:hidden  <?php echo $table_style; ?>">
    <?php
    View::render("widgets/bricks/vnx-service/hosting/" . $table_style . '_mobile', $data);
    ?>
  </div>
<?php
}
echo '</div>';
