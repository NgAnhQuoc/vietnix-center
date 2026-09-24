<?php
// This code getted from Elementor Full Width template
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

get_header();
global $post;
// Lưu lại giá trị của biến toàn cục $post
$original_post = $post;
$post_id = $GLOBALS[ 'whois_result_page_id' ] ?? 0;
$post = get_post( $post_id );
$bricks_data = \Bricks\Database::get_data( $post_id, 'content' );

$uploads = wp_upload_dir();
$css_url = $uploads['baseurl'].'/bricks/css/';
$css_link = sprintf( '<link rel="stylesheet" id="bricks-post-%d-css" href="%spost-%d.min.css" media="all" />', $post_id, esc_url( $css_url ), $post_id );
echo $css_link;
Bricks\Frontend::render_content( $bricks_data );

// Khôi phục giá trị của biến toàn cục $post
$post = $original_post;


get_footer();