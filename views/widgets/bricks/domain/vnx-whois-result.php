<?php
try {
    wp_enqueue_script( 'whois_check-center' );
    wp_localize_script( 'whois_check-center', 'whois_array', array(
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      // 'nonce'    => wp_create_nonce( 'whois_nonce' ),
   ) );
    $data = isset( $data ) ? $data : new stdClass();
    $settings = isset( $data->settings ) ? $data->settings : [];
    $pos = isset( $settings[ 'result_style' ] ) ? $settings[ 'result_style' ] : '';
    $wrapper_id = 'vnx-whois-result-landingpage';
    if($settings[ 'result_style' ] == 'whois_result_page'){
        $wrapper_id = 'vnx-whois-result';
    }
    echo '<div id="' . esc_attr( $wrapper_id ) . '" class="vnx-whois-result">';
    if ( $pos == 'whois_landing_page' ) {
        VNX_WHOIS_CHECK_Center::showLoadingLPage();
        echo '<div class="result_wrapper mb-5 md:mb-0"></div>';
    } else {
        VNX_WHOIS_CHECK_Center::showResultTable();
    }
    echo '</div>';
}
catch ( Exception $e ) {
    echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
    error_log( $e->getMessage() );
}
?>