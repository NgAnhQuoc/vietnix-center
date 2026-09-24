<?php
$id = get_the_ID();
$link = esc_url(get_the_permalink());
$title = get_the_title();
$excerpt = get_the_excerpt($id);
$icon_uri = VNX_PLUGIN_URL_CENTER . 'assets/images/icons/';
$note_icon = '<img class="vnx_icon note_icon mr-1" src="' . $icon_uri . 'note-icon.svg" alt="Note icon">';
$user_icon = '<img class="vnx_icon user_icon mr-1" src="' . $icon_uri . 'user-icon.svg" alt="User icon">';
$calendar_icon = '<img class="vnx_icon calendar_icon mr-1" src="' . $icon_uri . 'calendar-icon.svg" alt="calendar icon">';
$clock_icon = '<img class="vnx_icon clock_icon mr-1" src="' . $icon_uri . 'clock-icon.svg" alt="clock icon">';
if (str_word_count($excerpt, 0) > 38) {
    $words = str_word_count($excerpt, 2);
    $pos = array_keys($words);
    $excerpt = substr($excerpt, 0, $pos[38]) . '...';
}
$get_terms = get_the_terms($id, 'tax_lap-trinh');
$term_name = '';
$term_url = '';
// print_r($get_terms);
if (!empty($get_terms) && isset($get_terms[0]->name))
    $term_name = $get_terms[0]->name;
if (!empty($get_terms) && isset($get_terms[0]->term_id))
    $term_url = get_term_link($get_terms[0]->term_id);
$author = get_the_author();
$date = get_the_date('d/m/Y', $id);
$reading_time = '';
if (function_exists('vnx_reading_time_by_words'))
    $reading_time = vnx_reading_time_by_words(array('rule' => 220), 'phút đọc');
echo '<li class="vnx_card_item">';
echo '<div class="vnx_wrapper p-5 pb-1.5 sm:pb-5 border rounded-lg">';
echo '<div class="vnx_title"><a href="' . esc_attr($link) . '" rel="nofollow" class="text-lg font-bold">' . esc_html($title) . '</a></div>';
echo '<div class="vnx_excerpt my-5">' . esc_html($excerpt) . '<a class="vnx_view_more text-brand ml-1" href="' . esc_attr($link) . '" rel="nofollow">Đọc tiếp</a></div>';
echo '<div class="vnx_card_bottom flex flex-wrap items-center text-[#525666B2]">';
echo '<a class="vnx_category flex items-center mr-3.5 mb-3.5 sm:mr-4 sm:mb-0" href="' . esc_attr($term_url) . '" rel="nofollow">' . $note_icon . esc_html($term_name) . '</a>';
echo '<span class="vnx_author flex items-center mr-3.5 mb-3.5 sm:mr-4 sm:mb-0">' . $user_icon . esc_html($author) . '</span>';
echo '<span class="vnx_date flex items-center mr-3.5 mb-3.5 sm:mr-4 sm:mb-0">' . $calendar_icon . esc_html($date) . '</span>';
if ($reading_time)
    echo '<span class="vnx_reading_time flex items-center mb-3.5 sm:mb-0">' . $clock_icon . esc_html($reading_time) . '</span>';
echo '</div>';
echo '</div>';
echo '</li>';
