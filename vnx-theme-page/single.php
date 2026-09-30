<?php

get_header();

$post_id = get_the_ID();

while (have_posts()) : the_post();
    the_content();
endwhile;

get_footer();
