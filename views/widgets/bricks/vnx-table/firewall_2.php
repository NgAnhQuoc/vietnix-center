<?php

use HelperCenter\View;

if ( !isset( $data->settings ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
    return;
}
$settings = $data->settings;
$get_csv = $data->get_upload_file_data();

if ( $get_csv[ 'status' ] == 'error' ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>' . $get_csv[ 'message' ] . '</div>';
    return;
}
if ( $get_csv[ 'status' ] == 'success' && !empty( $get_csv[ 'data' ] ) )
    $csv_data = isset( $get_csv[ 'data' ] ) ? $get_csv[ 'data' ] : array();
if ( empty( $csv_data ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>CSV không có nội dung: ' . esc_html( __FILE__ ) . '</div>';
    return;
}
$array_data = [ 'info' => $csv_data, 'settings' => $settings ];
View::render( 'widgets/bricks/vnx-table/' . $settings[ 'table_style' ] . '_desktop', $array_data );
View::render( 'widgets/bricks/vnx-table/' . $settings[ 'table_style' ] . '_mobile', $array_data );
?>