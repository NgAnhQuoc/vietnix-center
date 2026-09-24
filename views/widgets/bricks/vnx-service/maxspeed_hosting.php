<?php

use HelperCenter\View;

if (!isset($data->settings['upload'])) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào</div>';
  return;
}
$settings = $data->settings;
$table_style = $settings['table_style'];
$data->set_attribute('_root', 'class', 'vnx_element_maxspeed w-full');
echo "<div {$data->render_attributes('_root')}>";
if (!$table_style) {
  if (current_user_can('update_core'))
    echo '<div class="vnx_error no_data text-center"><b>Chưa chọn style cho table</div>';
} else {
  ?>
  <!-- Desktop -->
  <div class="w-full hidden lg:block  <?php echo $table_style; ?>">
    <?php
    View::render("widgets/bricks/vnx-service/hosting/" . $table_style, $data);
    ?>
  </div>
  <!-- /Desktop -->
  <!-- Mobile -->
  <div class="w-full lg:hidden mobile  <?php echo $table_style; ?>">
    <?php
    View::render("widgets/bricks/vnx-service/hosting/" . $table_style . '_mobile', $data);
    ?>
  </div>
  <!-- /Mobile -->
  <?php
}
echo '</div>';
