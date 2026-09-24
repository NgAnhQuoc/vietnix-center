<?php

get_header();

$post_id = get_the_ID();

// $is_built_with_elementor = \Elementor\Plugin::$instance->db->is_built_with_elementor($post_id);

while (have_posts()) : the_post();
    the_content();
endwhile;

get_footer();
