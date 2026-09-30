<?php

use HelperCenter\View;

try {
    $version_style = $data->settings['version'];

    // yêu cầu chọn version style
    if (!isset($version_style)) {
        echo '<div class="vnx_error no_data"><b>Yêu cầu chọn Version style</div>';
        return;
    }

    // Layout đang ở dạng tĩnh nên chưa cần check file csv
    $my_class = [$version_style, 'w-full'];
    $data->set_attribute('_root', 'class', $my_class);
} catch (Exception $e) {
    throw new Exception($e->getMessage());
    return;
}

echo "<div {$data->render_attributes('_root')}>";
?>
<?php
View::render("widgets/bricks/vnx-service/server/" . $version_style, $data);
?>
<?php
echo '</div>';
