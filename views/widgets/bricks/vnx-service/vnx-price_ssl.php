<?php

use HelperCenter\View;

try {
  $version_style = $data->settings['version'];

  $file_csv = $data->settings['import-csv'];

  // yêu cầu chọn version style
  if (!isset($version_style)) {
    echo '<div class="vnx_error no_data"><b>Yêu cầu chọn Version style</div>';
    return;
  }

  // yêu cầu import file csv
  if (!isset($file_csv)) {
    echo '<div class="vnx_error no_data"><b>Không có data tryền vào</div>';
    return;
  }
  $my_class = [$version_style, 'w-full'];
  $data->set_attribute('_root', 'class', $my_class);

} catch (Exception $e) {
  throw new Exception($e->getMessage());
  return;
}

echo "<div {$data->render_attributes('_root')}>";
?>
<?php
View::render("widgets/bricks/vnx-service/ssl/" . $version_style, $data);
?>
<?php
echo '</div>';