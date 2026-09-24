<?php
use HelperCenter\View;

if ( !isset( $data->settings ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
    return;
}
$settings = $data->settings;
$my_class = [ 'vnx_element' ];
$form_style = isset( $settings[ 'form_style' ] ) ? $settings[ 'form_style' ] : '';
array_push( $my_class, $form_style );
$data->set_attribute( '_root', 'class', $my_class );
echo "<div {$data->render_attributes( '_root' )}>";
if ( !$form_style ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data text-center"><b>Chưa chọn style cho Form</div>';
} else {
    View::render( "widgets/bricks/domain/vnx-form-search/" . $form_style, $data );
}
echo '</div>';
?>