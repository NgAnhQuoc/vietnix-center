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
$group_1 = array('vps_price_3', 'hosting_v2', 'compare_hosting_v2', 'hosting_v3', 'hosting_v5', 'price_server', 'package_hosting', 'compare_wp_hosting', 'layout_carousel_price', 'wordpress_hosting_price_table');
$button_not_loading = array( 'compare_hosting_v2', 'compare_wp_hosting' );
if (in_array($table_style, $group_1))
    array_push($my_class, 'vnx_tab_script', 'group_1');
if (in_array($table_style, $button_not_loading))
    array_push($my_class, 'vnx_not_loading_btn');
array_push($my_class, $table_style);
$data->set_attribute('_root', 'class', $my_class);
echo "<div {$data->render_attributes('_root')}>";



if (!$table_style) {
    if (current_user_can('update_core'))
        echo '<div class="vnx_error no_data text-center"><b>Chưa chọn style cho table</div>';
} else {
    View::render("widgets/bricks/vnx-table/" . $table_style, $data);
}
echo '</div>';
