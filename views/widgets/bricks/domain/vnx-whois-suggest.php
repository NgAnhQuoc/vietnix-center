<?php
try {
    $data = isset( $data ) ? $data : new stdClass();
    $settings = isset( $data->settings ) ? $data->settings : [];
    $pos = isset( $settings[ 'result_style' ] ) ? $settings[ 'result_style' ] : '';
    $wrapper_class = 'suggest-landingpage ';
    if($settings[ 'result_style' ] == 'whois_result_suggest'){
        $wrapper_class = '';
    }
    echo '<div id="vnx-whois-suggest" class="' . $wrapper_class . 'vnx-suggest-result">';
    VNX_WHOIS_CHECK_Center::showNotFound();
    echo '</div>';
}
catch ( Exception $e ) {
    echo "<p><b>Something WRONG: </b>" . $e->getMessage() . "</p>";
    error_log( $e->getMessage() );
}