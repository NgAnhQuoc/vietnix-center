<?php
try {
    wp_enqueue_style( 'vnx-bricks-whois-domain-center' );
    $data = isset( $data ) ? $data : new stdClass();
    $settings = isset( $data->settings ) ? $data->settings : [];
    $placeholder = isset( $settings[ 'placeholder' ] ) ? $settings[ 'placeholder' ] : '';
    $btn_txt = isset( $settings[ 'button_text' ] ) ? $settings[ 'button_text' ] : '';
    $pos = isset( $settings[ 'form_style' ] ) ? $settings[ 'form_style' ] : '';
    $form_class = $pos != '' ? 'whois_check_form_landingpage' : 'whois_check_form';
    $whois_page_id = get_field( 'select_whois_page', 'option' ) ?? '';
    $form_settings = '';
    $input_name = '';
    if ( ( $form_class == 'whois_check_form_landingpage' && get_the_ID() != $whois_page_id ) || $form_class == 'whois_check_form' ) {
        $form_settings = ' name="vnx-check-whois-form" method="POST" action="' . get_the_permalink( $whois_page_id ) . '"';
        $input_name = ' name="whois-input-domain"';
    }
    $form_value = isset( $_POST[ 'whois-input-domain' ] ) ? $_POST[ 'whois-input-domain' ] : '';
    echo "<div {$data->render_attributes( '_root', )}>";
    ?>
    <div class="vnx-check-whois-form w-full">
    <?php wp_nonce_field( 'domain_checking', 'vnx_domain_security' ); ?>
        <form class="relative <?php echo esc_attr( $form_class ); ?>" <?php echo $form_settings; ?>>
            <div class="nvx_notice absolute w-full top-full bg-white z-[2] shadow-md p-2 text-red-600 text-sm hidden">Vui
                lòng nhập tên miền đúng!</div>
            <div class="vnx_wrapper relative flex justify-center items-stretch">
                <input type="text" <?php echo $input_name; ?> class="relative w-full text-sm px-5 py-2.5 outline-none"
                    placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( $form_value ); ?>">
                <button type="submit"
                    class="whois_submit_btn button absolute top-0 right-0 z-[1] h-full w-12 sm:w-32 flex items-center justify-center font-medium text-white">
                    <span class="hidden sm:block">
                        <?php echo esc_html( $btn_txt ); ?>
                    </span>
                    <span class="block sm:hidden"><i class="fa fa-search" aria-hidden="true"></i></span>
                </button>
            </div>
        </form>
    </div>
    <?php
    echo "</div>";
}
catch ( Exception $e ) {
    echo '<p><b>Something wrong:</b>' . $e->getMessage() . '</p>';
}
?>