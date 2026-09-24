<?php
try {
    $note = get_field( 'note' ) ?? '';
    $icon = get_field( 'icon' ) ?? '';
    $bgr_select = get_field( 'bgr_select' ) ?? '';
    $custom_background = get_field( 'custom_background' ) ?? '#fff';
    $bgr = $bgr_select != '1' ? "#$bgr_select" : $custom_background;
    echo '<div class="vnx-note-icon-block" style="background-color:' . esc_attr( $bgr ) . '">';
    echo '<div class="vnx_wrapper">';
    if ( $icon )
        echo wp_get_attachment_image( $icon, 'thumbnail', true, [ "class" => 'icon_before' ] );
    echo $note;
    echo '</div>';
    echo '</div>';
}
catch ( Exception $e ) {
    echo '<p><b>Something wrong:</b>' . $e->getMessage() . '</p>';
}
?>