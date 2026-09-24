<?php

use  Bricks\Breakpoints;
use HelperCenter\View;

if (!isset($data->settings)) {
    if (current_user_can('update_core'))
        echo '<div class="vnx_error no_data"><b>Không có data tryền vào: ' . esc_html(__FILE__) . '</div>';
    return;
}
//css class table
$settings = $data->settings;
$my_class = ['vnx_element'];
$table_style = isset($settings['table_style']) ? $settings['table_style'] : '';
$group_1 = array('hosting_price_v1','list_hosting_price_v1');
if (in_array($table_style, $group_1))
    array_push($my_class, 'vnx_tab_script', 'group_1');
array_push($my_class, $table_style);
$data->set_attribute('_root', 'class', $my_class);
$loop_slide = isset($settings['loop_slide']) ? $settings['loop_slide'] : 'loop';
$item_show = isset($settings['item_show']) ? $settings['item_show'] : '3';
$item_show_scroll = isset($settings['item_show_scroll']) ? $settings['item_show_scroll'] : '1';
$spacing_item_show = isset($settings['spacing_item_show']) ? $settings['spacing_item_show'] : '0px';
$show_arrow = isset($settings['show_arrow']) ? true : false;
$show_dot = isset($settings['show_dot']) ? true : false;
$item_start = !empty($settings['item_start']) ? $settings['item_start'] : 0;
$item_focus = !empty($settings['item_focus']) ? $settings['item_focus'] : 'center';
$splide_options = [
    'type' => $loop_slide,
    'direction' => 'ltr',
    'keyboard' => 'global',
    'perPage' => $item_show,
    'perMove' => $item_show_scroll,
    'gap' => $spacing_item_show,
    'start'        => $item_start,
    'focus'        => $item_focus,
    'arrows' => $show_arrow,
    'classes' => [
        'arrows' => 'splide__arrows',
        'arrow' => 'splide__arrow',
        'prev'  => 'splide__arrow--prev',
        'next'  => 'splide__arrow--next',
    ],
    'speed' => 300,
    'useTransform'=> true,
    'pagination' => $show_dot,
    'breakpoints' => [],
];
$breakpoints = [];
$arr_breakpoints = [];
foreach (Breakpoints::$breakpoints as $breakpoint) {
    $item_start_bk = !empty($settings['item_start' . ':' . $breakpoint['key']]) ? $settings['item_start' . ':' . $breakpoint['key']] : $item_start;
    $item_focus_bk = !empty($settings['item_focus' . ':' . $breakpoint['key']]) ? $settings['item_focus' . ':' . $breakpoint['key']] : $item_focus;
    $item_spacing_bk = !empty($settings['spacing_item_show' . ':' . $breakpoint['key']]) ? $settings['spacing_item_show' . ':' . $breakpoint['key']] : $spacing_item_show;
    if (!empty($settings['item_show' . ':' . $breakpoint['key']]) && $settings['item_show' . ':' . $breakpoint['key']] != null) {
        $arr_breakpoints[$breakpoint['width']] = [
            'perPage' => $settings['item_show' . ':' . $breakpoint['key']],
            'start'        => $item_start_bk,
            'focus'        => $item_focus_bk,
            'gap' => $item_spacing_bk,
            'speed' => 200,
            'lazyLoad'=> 'nearby',
        ];
    }
}
if (count($arr_breakpoints)) {
    $splide_options['breakpoints'] = $arr_breakpoints;
}
$id_ele = $data->element['id'];
echo "<div {$data->render_attributes('_root')} data-id-splide=" . $id_ele . " data-splide=" . json_encode($splide_options) . ">";
if (!$table_style) {
    if (current_user_can('update_core'))
        echo '<div class="vnx_error no_data text-center"><b>Chưa chọn style cho table</div>';
} else {
    if (in_array($table_style, $group_1)) {
        View::render("widgets/bricks/vnx-service/hosting/" . $table_style, $data);
    }
}
echo '</div>';
