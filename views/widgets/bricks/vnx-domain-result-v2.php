<?php
use HelperCenter\View;

if ( !isset( $data->settings ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
    return;
}
$settings = $data->settings;
$my_class = [ 'vnx_element' ];
$result_style = isset( $settings[ 'result_style' ] ) ? $settings[ 'result_style' ] : '';
array_push( $my_class, $result_style );
$data->set_attribute( '_root', 'class', $my_class );
echo "<div {$data->render_attributes( '_root' )}>";
if ( !$result_style ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data text-center"><b>Chưa chọn style cho Form</div>';
} else {
    View::render( "widgets/bricks/domain/vnx-result/" . $result_style, $data );
}
echo '</div>';
?>