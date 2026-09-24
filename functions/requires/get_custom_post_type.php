<?php
use HelperCenter\View;

if ( !defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}
if ( !function_exists( 'vnx_ajax_get_custom_post_type_Center' ) ) {
    function vnx_ajax_get_custom_post_type_Center()
    {
        if ( !check_ajax_referer( 'search_to_popup', '_wpnonce', false ) ) {
            wp_send_json_error( 'Nonce is invalid' );
            wp_die(); // All ajax handlers should die when finished
        }
        $count = 0;
        $keyword = isset( $_POST[ 'keyword' ] ) ? $_POST[ 'keyword' ] : '';
        $popup_style = isset( $_POST[ 'popup_style' ] ) ? $_POST[ 'popup_style' ] : '1';
        $current_url = isset( $_POST[ 'current_url' ] ) ? $_POST[ 'current_url' ] : get_home_url();
        $query_data = isset( $_POST[ 'query_data' ] ) ? $_POST[ 'query_data' ] : '';
        $post_type = isset( $query_data[ 'post_type' ] ) ? $query_data[ 'post_type' ] : 'post';
        $orderby = isset( $query_data[ 'orderby' ] ) ? $query_data[ 'orderby' ] : 'date';
        $meta_key = isset( $query_data[ 'meta_key' ] ) ? $query_data[ 'meta_key' ] : '';
        $order = isset( $query_data[ 'order' ] ) ? $query_data[ 'order' ] : 'DESC';
        $posts_per_page = isset( $query_data[ 'posts_per_page' ] ) ? $query_data[ 'posts_per_page' ] : '0';
        $paged = 1;
        $tax_query = array();
        if ( isset( $_GET[ 'paged' ] ) ) {
            $paged = $_GET[ 'paged' ] ?? $paged;
        } else if ( isset( $_POST[ 'paged' ] ) ) {
            $paged = $_POST[ 'paged' ] ?? $paged;
        }
        ob_start(); //bắt đầu bộ nhớ đệm

        global $wp_query;
        $loop_args = array(
            'paged'       => $paged,
            'post_type'   => $post_type,
            'order'       => $order,
            'orderby'     => $orderby,
            'post_status' => 'publish',
            // 'posts_per_page' => 2,
        );
        if ( !empty( $tax_query ) )
            $loop_args[ 'tax_query' ] = $tax_query;
        if ( $meta_key ) {
            $loop_args[ 'meta_key' ] = $meta_key;
        }
        if ( $keyword ) {
            $loop_args[ 's' ] = $keyword;
        }
        if ( $posts_per_page != '0' ) {
            $loop_args[ 'posts_per_page' ] = $posts_per_page;
        }
        $loop = new WP_Query( $loop_args );
        $wp_query = $loop;
        if ( have_posts() ) {
            $count = $loop->found_posts;
            ?>
            <ul class="vnx_<?php echo esc_attr( $post_type ); ?>_list vnx_list">
                <?php
                while ( have_posts() ) {
                    the_post();
                    View::render( 'widgets/bricks/search_to_popup/cards/custom_post_card_style_' . $popup_style );
                }
                ?>
            </ul>
            <?php
            $paginate_data = $loop_args;
            $paginate_data[ 'popup_style' ] = $popup_style;
            $paginate_data[ 'current_url' ] = $current_url;
            unset( $paginate_data[ 'paged' ] );
            echo '<div class="vnx_ajax_paginate_popup mt-8" data-query="' . esc_attr( json_encode( $paginate_data ) ) . '">';
            $args = array(
                'base'      => $current_url . '?paged=%#%',
                'type'      => 'list',
                'next_text' => __( '<i class="fas fa-chevron-right"></i>', 'vietnix' ),
                'prev_text' => __( '<i class="fas fa-chevron-left"></i>', 'vietnix' ),
            );
            the_posts_pagination( $args );
            echo '</div>';
        } else {
            $notice = 'Không có bài viết hiển thị';
            if ( $keyword )
                $notice = 'Không có bài viết hiển thị cho "' . esc_html( $keyword ) . '"';
            echo '<div class="not_found_notice text-center py-10 px-5">';
            echo $notice;
            echo '</div>';
        }
        wp_reset_query();
        wp_reset_postdata();

        $result = ob_get_clean(); //cho hết bộ nhớ đệm vào biến $result
        $return = array(
            'html'  => $result,
            'count' => $count,
        );
        // wp_send_json_error( null, 401, 1 );
        wp_send_json_success( $return ); // trả về giá trị dạng json
        wp_die(); // All ajax handlers should die when finished
    }
    add_action( 'wp_ajax_vnx_get_custom_post_type_center', 'vnx_ajax_get_custom_post_type_Center' );
    add_action( 'wp_ajax_nopriv_vnx_get_custom_post_type_center', 'vnx_ajax_get_custom_post_type_Center' );
}
?>