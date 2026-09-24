<?php
$data = isset($data) ? $data : new stdClass();
$settings = isset($data->settings) ? $data->settings : array();
$btn_icon = isset($settings['button_icon']) ? $settings['button_icon'] : '';
$placeholder = isset($settings['placeholder']) ? $settings['placeholder'] : '';

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
$style = '1';
if (isset($settings['popup_template']) || $settings['popup_template'])
    $style = $settings['popup_template'];
// echo '<pre>';
// print_r( $settings );
// echo '</pre>';
?>
<div class="vnx_popup style_1 flex items-center justify-center min-h-screen w-full min-w-screen fixed top-0 left-0" id="bricks">
    <div class="vnx_popup_wrapper relative self-center flex items-center justify-center min-h-screen w-full">
        <div class="vnx_popup_bgr min-w-full min-h-full absolute z-0 cursor-pointer"></div>
        <div class="vnx_popup_content_wrapper relative z-1 self-center flex flex-col items-stretch">
            <div class="popup_top flex flex-col sm:flex-row items-center sm:items-stretch justify-between py-0 pb-2.5 sm:py-6 mb-8 border-b">
                <form class="relative form_search_to_popup w-full sm:w-64 rounded-lg" data-query="<?php echo esc_attr($query_data); ?>" data-style="<?php echo esc_attr($style); ?>">
                    <div class="nvx_notice absolute w-full top-full bg-white z-[2] shadow-md p-2 text-red-600 text-sm hidden">
                    </div>
                    <div class="vnx_wrapper relative flex justify-center items-stretch overflow-hidden">
                        <input type="text" class="relative w-full px-2.5 py-1 pl-10 outline-none bg-[#38A7FF26] rounded-lg" placeholder="<?php echo esc_attr($placeholder); ?>">
                        <button type="submit" class="form_search_to_popup_btn popup_btn button absolute top-0 left-0 z-[1] h-full px-2.5 flex items-center justify-center font-medium outline-none">
                            <span class="button_icon mx-0.5 text-lg">
                                <?php
                                if ($btn_icon)
                                    echo '<i class="' . $btn_icon['icon'] . '"></i>';
                                ?>
                            </span>
                        </button>
                    </div>
                </form>
                <div class="vnx_order_by flex items-center justify-between sm:justify-end min-w-full sm:min-w-min mt-2.5 sm:mt-0">
                    <div class="vnx_count_result flex flex-nowrap"><span class="vnx_loop_count mr-0.5" class=" w-max">0</span><span class="w-max">kết quả</span></div>
                    <select class="vnx_select_orderby py-1 pl-3 pr-8 ml-2.5 bg-[#38A7FF26] rounded-lg">
                        <?php
                        $orderby = isset($settings['orderby']) && $settings['orderby'] ? $settings['orderby'] : '';
                        if ($orderby) {
                            switch ($orderby) {
                                case 'name':
                                    echo '<option value="name">Theo tên</option>';
                                    break;

                                default:
                                    $label = (isset($settings['meta_key_label']) && $settings['meta_key_label']) ? $settings['meta_key_label'] : 'Mặc định';
                                    echo '<option value="' . esc_attr($orderby) . '">' . esc_html($label) . '</option>';
                                    echo '<option value="name">Theo tên</option>';
                                    break;
                            }
                        }
                        ?>
                        <option value="latest">Mới nhất</option>
                        <option value="oldest">Cũ nhất</option>
                    </select>
                </div>
            </div>
            <div class="vnx_popup_content"></div>
        </div>
    </div>
</div>