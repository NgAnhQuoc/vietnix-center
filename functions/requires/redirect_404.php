<?php
if ( !function_exists( 'author_pagination_404_Center' ) ) {
    function author_pagination_404_Center()
    {
        if ( !is_author() )
            return;
        // 5 is hung nguyen author
        if ( get_the_author_meta( 'ID' ) != 5 )
            return;
        $current = add_query_arg( '', '' );
        if ( strpos( $current, '/page/' ) !== false ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
        }
    }
    // Chay song song thi vietnix-plugin da dang ky ban giong het (xem vnx_center_companion_mode()).
    if ( !vnx_center_companion_mode() ) {
        add_action( 'wp', 'author_pagination_404_Center' );
    }
}
?>