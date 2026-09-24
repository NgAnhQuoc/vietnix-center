<?php
if ( !isset( $data->settings ) ) {
    if ( current_user_can( 'update_core' ) )
        echo '<div class="vnx_error no_data"><b>Không có $data tryền vào: ' . esc_html( __FILE__ ) . '</div>';
    return;
}
$settings = $data->settings;
$placeholder = isset( $settings[ 'placeholder' ] ) ? $settings[ 'placeholder' ] : '';
$button_text = isset( $settings[ 'button_text' ] ) ? $settings[ 'button_text' ] : '';
$button_icon = isset( $settings[ 'button_icon' ] ) ? $settings[ 'button_icon' ] : array();
$redirect_url = isset( $settings[ 'redirect_url' ] ) ? $settings[ 'redirect_url' ] : '';
?>
<form method="GET" action="<?php echo esc_attr( $redirect_url ); ?>" class="relative">
    <div class="vnx_wrapper rounded overflow-hidden relative">
        <input type="text" class="relative z-0 py-1.5 border-none outline-none" name="domain"
            placeholder="<?php echo esc_attr( $placeholder ); ?>">
        <button type="submit" class="absolute z-[1] right-0 top-0 h-full flex items-center">
            <?php
            if ( !empty( $button_icon ) )
                echo Bricks\Element::render_icon( $button_icon, [ 'vnx_icon' ] );
            echo '<span class="button_text">' . esc_html( $button_text ) . '</span>';
            ?>
        </button>
    </div>
    <div class="vnx_notice absolute hidden">
        <div class="vnx_wrapper relative rounded bg-white">
            <span class="vnx_notice_text block px-4 py-2 relative z-[2]"></span>
        </div>
    </div>
</form>
<?php
// echo '<pre>';
// print_r( $settings );
// echo '</pre>';
?>