<?php

use HelperCenter\View;

$data = isset($data) ? $data : new stdClass();
$data->set_attribute('_root', 'class', 'vnx_search_to_popup');
$settings = isset($data->settings) ? $data->settings : array();
$style = '1';
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';
$btn_txt = isset($settings['button_text']) ? $settings['button_text'] : '';
$btn_icon = isset($settings['button_icon']) ? $settings['button_icon'] : '';
$post_type = isset($settings['post_type']) ? $settings['post_type'] : 'post';
$orderby = isset($settings['orderby']) ? $settings['orderby'] : 'date';
$meta_key = isset($settings['meta_key']) ? $settings['meta_key'] : '';
$order = isset($settings['order']) ? $settings['order'] : 'DESC';
$posts_per_page = isset($settings['posts_per_page']) ? $settings['posts_per_page'] : '0';
$query_args = array(
    'post_type'      => $post_type,
    'orderby'        => $orderby,
    'order'          => $order,
    'posts_per_page' => $posts_per_page,
);
if ($meta_key)
    $query_args['meta_key'] = $meta_key;
$query_data = json_encode($query_args);
if (isset($settings['popup_template']) || $settings['popup_template'])
    $style = $settings['popup_template'];
echo "<div {$data->render_attributes('_root')}>";
?>
<form class="relative form_search_to_popup" data-query="<?php echo esc_attr($query_data); ?>">
    <div class="nvx_notice absolute w-full top-full bg-white z-[2] shadow-md p-2 text-red-600 text-sm hidden"></div>
    <div class="vnx_wrapper relative flex justify-center items-stretch overflow-hidden">
        <input type="text" class="relative w-full text-sm px-5 py-2.5 outline-none border" placeholder="<?php echo esc_attr($placeholder); ?>">
        <button type="submit" class="form_search_to_popup_btn button absolute top-0 right-0 z-[1] h-full px-4 flex items-center justify-center font-medium  outline-none">
            <span class="button_icon mx-0.5">
                <?php
                if ($btn_icon)
                    echo '<i class="' . $btn_icon['icon'] . '"></i>';
                ?>
            </span>
            <span class="button_text mx-0.5">
                <?php echo esc_html($btn_txt); ?>
            </span>
        </button>
    </div>
</form>
<?php
View::render('widgets/bricks/search_to_popup/vnx_search_to_popup_' . $style, ['settings' => $settings]);
echo '</div>';
?>