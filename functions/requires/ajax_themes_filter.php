<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

if (!function_exists('loadThemespost_init_Center')) {

    function loadThemespost_init_Center()
    {
        try {
            // check_ajax_referer( 'ajax_load_post_nonce', '_ajax_nonce' );
            $paged = isset($_POST['ajax_paged']) ? intval($_POST['ajax_paged']) : 1;
            if ($paged <= 0 || !$paged || !is_numeric($paged))
                View::render('widgets/bricks/theme_post/theme_post_not_found');

            $tax_id = isset($_POST['tax_id']) ? $_POST['tax_id'] : '';
            $class = 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8';
            $taxonomy = isset($_POST['taxonomy']) ? $_POST['taxonomy'] : '';
            $loop_card = isset($_POST['loop_card']) ? $_POST['loop_card'] : '';
            $posts_type = isset($_POST['posts_type']) ? $_POST['posts_type'] : 'vnx-theme-wp';
            $posts_per_page = $_POST['posts_per_page'];
            $current_page = $_POST['current_page'];
            $query_args = array(
                'post_type'      => $posts_type,
                'post_status'    => 'publish',
                'posts_per_page' => $posts_per_page,
                'paged'          => $paged,
            );
            $tax_query = array(
                array(
                    'taxonomy' => 'loai-theme',
                    'fields'   => 'id',
                    'terms'    => array($tax_id),
                )
            );
            if ($taxonomy) {
                $tax_query[0]['taxonomy'] = $taxonomy;
            }
            if ($tax_id != '' && $tax_id != 'all')
                $query_args['tax_query'] = $tax_query;
            ob_start();

            $post_new = new WP_Query($query_args);

            if ($post_new->have_posts()) :
                if ($loop_card)
                    $class = "vnx_loop_" . $posts_type;
                echo '<div class ="vnx_loop_section grid ' . $class . '">';
                while ($post_new->have_posts()) :
                    $post_new->the_post();
                    if ($loop_card) {
                        echo do_shortcode("[bricks_template id=\"$loop_card\" ]");
                    } else {
                        View::render('widgets/bricks/theme_post/post-theme-wp');
                    }
                endwhile;
                echo '</div>';
                echo '<div class="vnx-pagination-theme-post w-full flex items-center justify-center mt-8">';
                vnx_pagi_ajax_Center($post_new, $paged, $current_page);
                echo '</div>';
            else :
                View::render('widgets/bricks/theme_post/theme_post_not_found');
            endif;
            wp_reset_query();
            $result = ob_get_clean();
            wp_send_json_success($result);
            die();
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }
    add_action('wp_ajax_loadpost_center', 'loadThemespost_init_Center');
    add_action('wp_ajax_nopriv_loadpost_center', 'loadThemespost_init_Center');
}

if (!function_exists('vnx_load_theme_posts_init_Center')) {
    function vnx_load_theme_posts_init_Center()
    {
        try {
            $paged = isset($_POST['ajax_paged']) ? intval($_POST['ajax_paged']) : 1;
            if ($paged <= 0 || !$paged || !is_numeric($paged)) {
                $paged = 1;
            }

            $tax_id = isset($_POST['tax_id']) ? $_POST['tax_id'] : 'all';
            $posts_type = isset($_POST['posts_type']) ? $_POST['posts_type'] : 'post';
            $posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 6;
            $loop_card = isset($_POST['loop_card']) ? intval($_POST['loop_card']) : '';
            $loop_not_card = isset($_POST['loop_not_card']) ? intval($_POST['loop_not_card']) : '';
            $current_page = isset($_POST['current_page']) ? $_POST['current_page'] : '';
            $root_div = isset($_POST['root_div']) ? $_POST['root_div'] : '';

            $query_args = array(
                'post_type'      => $posts_type,
                'post_status'    => 'publish',
                'posts_per_page' => $posts_per_page,
                'paged'          => $paged,
            );

            if ($tax_id != '' && $tax_id != 'all') {
                $tax_ids = explode(',', $tax_id);
                $tax_ids = array_map('trim', $tax_ids);
                $tax_ids = array_filter($tax_ids, 'is_numeric');

                if (!empty($tax_ids)) {
                    $taxonomies = get_object_taxonomies($posts_type, 'objects');
                    if (!empty($taxonomies)) {
                        $tax_query = array('relation' => 'OR');
                        foreach ($taxonomies as $taxonomy) {
                            $tax_query[] = array(
                                'taxonomy' => $taxonomy->name,
                                'field'    => 'term_id',
                                'terms'    => $tax_ids,
                            );
                        }
                        $query_args['tax_query'] = $tax_query;
                    }
                }
            }

            ob_start();
            $post_new = new WP_Query($query_args);

            if ($post_new->have_posts()) {
                $current_post_index = 0;
                while ($post_new->have_posts()) {
                    $post_new->the_post();
                    $is_highlighted = ($current_post_index === 1);
                    $current_post_index++;
                    
                    echo '<div class="vnx-theme-posts-card">';
                    if ($loop_card && $root_div === 'bricks') {
                        echo do_shortcode("[bricks_template id=\"$loop_card\" ]");
                    } else {
                        echo '<div class="vnx-theme-posts-card-preview">';
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('medium_large', array('style' => 'width: 100%; height: 100%; object-fit: cover;'));
                        } 
                        echo '</div>';
                        echo '<div class="vnx-theme-posts-card-title">';
                        $highlight_class = $is_highlighted ? 'highlighted' : '';
                        $post_title = get_the_title();
                        if (empty($post_title)) {
                            $post_title = get_the_title();
                        }
                        echo '<h3 class="' . esc_attr($highlight_class) . '">';
                        echo esc_html($post_title);
                        echo '</h3>';
                        echo '</div>';
                    }
                    echo '</div>';
                }

                if ($post_new->max_num_pages > 1) {
                    vnx_pagi_ajax_theme_posts_Center($post_new, $paged, $current_page);
                }
            } else {
                echo '<div class="vnx-theme-posts-no-results" style="grid-column: 1 / -1; text-align: center; padding: 40px;">';
                if ($loop_not_card && $root_div === 'bricks') {
                    echo do_shortcode("[bricks_template id=\"$loop_not_card\" ]");
                } else {
                    echo '<p>Không tìm thấy bài viết nào.</p>';
                }
                echo '</div>';
            }

            wp_reset_postdata();
            $result = ob_get_clean();
            wp_send_json_success($result);
            die();
        } catch (Exception $e) {
            error_log('vnx_load_theme_posts_init_Center error: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Đã có lỗi xảy ra. Vui lòng thử lại sau!'));
            die();
        }
    }
    add_action('wp_ajax_vnx_load_theme_posts_center', 'vnx_load_theme_posts_init_Center');
    add_action('wp_ajax_nopriv_vnx_load_theme_posts_center', 'vnx_load_theme_posts_init_Center');
}

if (!function_exists('vnx_pagi_ajax_theme_posts_Center')) {
    function vnx_pagi_ajax_theme_posts_Center($custom_query = null, $paged = 1, $current_url = null)
    {
        global $wp_query, $wp_rewrite;
        if ($current_url == null) {
            $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }
        
        $url_parts = parse_url($current_url);
        $path = isset($url_parts['path']) ? $url_parts['path'] : '';
        $pagination_base = $wp_rewrite->pagination_base;
        
        $pattern = '/' . preg_quote($pagination_base, '/') . '\/(\d+)\/?/';
        $prev_path = '';
        while ($path !== $prev_path) {
            $prev_path = $path;
            $path = preg_replace($pattern, '', $path);
        }
        $path = rtrim($path, '/');
        
        $clean_url = $url_parts['scheme'] . '://' . $url_parts['host'] . $path;
        if (isset($url_parts['query'])) {
            $query_args = array();
            parse_str($url_parts['query'], $query_args);
            unset($query_args['paged']);
            if (!empty($query_args)) {
                $clean_url .= '?' . http_build_query($query_args);
            }
        }
        
        if ($custom_query) {
            $main_query = $custom_query;
        } else {
            $main_query = $wp_query;
        }
        $total = isset($main_query->max_num_pages) ? $main_query->max_num_pages : '';
        if ($total > 1) {
            echo '<div class="paginate_links">';
            echo paginate_links(
                array(
                    'base'      => trailingslashit($clean_url) . "{$pagination_base}/%#%/",
                    'format'    => '',
                    'current'   => max(1, $paged),
                    'total'     => $total,
                    'mid_size'  => 1,
                    'end_size'  => 2,
                    'prev_text' => __('<i class="fas fa-chevron-left"></i>', 'devvn'),
                    'next_text' => __('<i class="fas fa-chevron-right"></i></i>', 'devvn'),
                )
            );
            echo '</div>';
        }
    }
}

if (!function_exists('vnx_pagi_ajax_Center')) {
    function vnx_pagi_ajax_Center($custom_query = null, $paged = 1, $current_url = null)
    {
        global $wp_query, $wp_rewrite;
        if ($current_url == null) {
            $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }
        if ($custom_query) {
            $main_query = $custom_query;
        } else {
            $main_query = $wp_query;
        }
        $total = isset($main_query->max_num_pages) ? $main_query->max_num_pages : '';
        if ($total > 1) {
            echo '<div class="paginate_links">';
            echo paginate_links(
                array(
                    'base'      => trailingslashit($current_url) . "{$wp_rewrite->pagination_base}/%#%/",
                    'format'    => '',
                    'current'   => max(1, $paged),
                    'total'     => $total,
                    'mid_size'  => '2',
                    'prev_text' => __('<i class="fas fa-chevron-left"></i>', 'devvn'),
                    'next_text' => __('<i class="fas fa-chevron-right"></i></i>', 'devvn'),
                )
            );
            echo '</div>';
        }
    }
}
