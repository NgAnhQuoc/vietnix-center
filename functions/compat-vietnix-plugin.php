<?php
if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

/**
 * Lop tuong thich khi thay the han vietnix-plugin bang vietnix-center.
 *
 * Code nam NGOAI plugin (snippet WPCode, Code element cua Bricks...) van goi ten cu cua
 * vietnix-plugin: class Helper\DiscordBot, ham vnxSyncDataSpreadsheets()... Center dat ten
 * rieng (HelperCenter\*, hau to _Center) nen khi go vietnix-plugin, cac cho do se fatal
 * "Class/Call to undefined function". File nay khai bao lai ten cu, tro sang ban cua center.
 *
 * CHI nap khi vietnix-plugin KHONG active (xem vietnix-center.php): center load truoc
 * vietnix-plugin theo ten thu muc, khai bao ten cu luc do se lam vietnix-plugin fatal
 * "Cannot redeclare".
 *
 * Danh sach ham duoc sinh tu dong: moi ham global cua vietnix-plugin co ban <ten>_Center.
 */

if (!function_exists('vnx_center_compat_call')) {
  function vnx_center_compat_call($center_fn, array $args)
  {
    if (function_exists($center_fn)) {
      return $center_fn(...$args);
    }
    // Ham cua center chua duoc nap (tool/extension tuong ung dang tat).
    error_log('[vietnix-center compat] ' . $center_fn . '() chua duoc nap - kiem tra tool/extension tuong ung da bat chua.');
    return null;
  }
}

// Class Helper\* cua vietnix-plugin -> HelperCenter\*
foreach (array(
  'DiscordBot',
  'GoogleAuth',
  'UploadImage',
  'VNXAutoLoader',
  'View',
  'VnxApiKeyCrypt',
) as $vnx_compat_class) {
  if (!class_exists('Helper\\' . $vnx_compat_class, false) && class_exists('HelperCenter\\' . $vnx_compat_class)) {
    class_alias('HelperCenter\\' . $vnx_compat_class, 'Helper\\' . $vnx_compat_class);
  }
}
unset($vnx_compat_class);

// Ham global cua vietnix-plugin -> <ten>_Center
if (!function_exists('add_noindex_to_whois_result_pages')) {
  function add_noindex_to_whois_result_pages(...$args) { return vnx_center_compat_call('add_noindex_to_whois_result_pages_Center', $args); }
}
if (!function_exists('add_title_faqs_in_post')) {
  function add_title_faqs_in_post(...$args) { return vnx_center_compat_call('add_title_faqs_in_post_Center', $args); }
}
if (!function_exists('apply_custom_posts_filter')) {
  function apply_custom_posts_filter(...$args) { return vnx_center_compat_call('apply_custom_posts_filter_Center', $args); }
}
if (!function_exists('apply_custom_posts_filter_dev')) {
  function apply_custom_posts_filter_dev(...$args) { return vnx_center_compat_call('apply_custom_posts_filter_dev_Center', $args); }
}
if (!function_exists('author_pagination_404')) {
  function author_pagination_404(...$args) { return vnx_center_compat_call('author_pagination_404_Center', $args); }
}
if (!function_exists('br_bookmarks_tagify_json_to_array')) {
  function br_bookmarks_tagify_json_to_array(...$args) { return vnx_center_compat_call('br_bookmarks_tagify_json_to_array_Center', $args); }
}
if (!function_exists('bricksSendTelegramMessage')) {
  function bricksSendTelegramMessage(...$args) { return vnx_center_compat_call('bricksSendDiscordMessage_Center', $args); }
}
if (!function_exists('button_widget_block_assets')) {
  function button_widget_block_assets(...$args) { return vnx_center_compat_call('button_widget_block_assets_Center', $args); }
}
if (!function_exists('by_manage_dev_posts_custom_column_dev')) {
  function by_manage_dev_posts_custom_column_dev(...$args) { return vnx_center_compat_call('by_manage_dev_posts_custom_column_dev_Center', $args); }
}
if (!function_exists('by_manage_post_posts_custom_column')) {
  function by_manage_post_posts_custom_column(...$args) { return vnx_center_compat_call('by_manage_post_posts_custom_column_Center', $args); }
}
if (!function_exists('by_manage_posts_columns')) {
  function by_manage_posts_columns(...$args) { return vnx_center_compat_call('by_manage_posts_columns_Center', $args); }
}
if (!function_exists('by_manage_posts_columns_dev')) {
  function by_manage_posts_columns_dev(...$args) { return vnx_center_compat_call('by_manage_posts_columns_dev_Center', $args); }
}
if (!function_exists('check_domain_data')) {
  function check_domain_data(...$args) { return vnx_center_compat_call('check_domain_data_Center', $args); }
}
if (!function_exists('check_premium_domain')) {
  function check_premium_domain(...$args) { return vnx_center_compat_call('check_premium_domain_Center', $args); }
}
if (!function_exists('conditionally_category_icon')) {
  function conditionally_category_icon(...$args) { return vnx_center_compat_call('conditionally_category_icon_Center', $args); }
}
if (!function_exists('convertDataInputTagify')) {
  function convertDataInputTagify(...$args) { return vnx_center_compat_call('convertDataInputTagify_Center', $args); }
}
if (!function_exists('convertStringToArray')) {
  function convertStringToArray(...$args) { return vnx_center_compat_call('convertStringToArray_Center', $args); }
}
if (!function_exists('count_attachment_paged')) {
  function count_attachment_paged(...$args) { return vnx_center_compat_call('count_attachment_paged_Center', $args); }
}
if (!function_exists('custom_posts_filter')) {
  function custom_posts_filter(...$args) { return vnx_center_compat_call('custom_posts_filter_Center', $args); }
}
if (!function_exists('custom_posts_filter_dev')) {
  function custom_posts_filter_dev(...$args) { return vnx_center_compat_call('custom_posts_filter_dev_Center', $args); }
}
if (!function_exists('define_if_not_defined')) {
  function define_if_not_defined(...$args) { return vnx_center_compat_call('define_if_not_defined_Center', $args); }
}
if (!function_exists('disable_admin_caching')) {
  function disable_admin_caching(...$args) { return vnx_center_compat_call('disable_admin_caching_Center', $args); }
}
if (!function_exists('domain_transfer_form_action')) {
  function domain_transfer_form_action(...$args) { return vnx_center_compat_call('domain_transfer_form_action_Center', $args); }
}
if (!function_exists('domain_transfer_form_validate')) {
  function domain_transfer_form_validate(...$args) { return vnx_center_compat_call('domain_transfer_form_validate_Center', $args); }
}
if (!function_exists('domain_vailid_checking')) {
  function domain_vailid_checking(...$args) { return vnx_center_compat_call('domain_vailid_checking_Center', $args); }
}
if (!function_exists('filter_lap_trinh_posts')) {
  function filter_lap_trinh_posts(...$args) { return vnx_center_compat_call('filter_lap_trinh_posts_Center', $args); }
}
if (!function_exists('findIndexInObject')) {
  function findIndexInObject(...$args) { return vnx_center_compat_call('findIndexInObject_Center', $args); }
}
if (!function_exists('findValueIndex')) {
  function findValueIndex(...$args) { return vnx_center_compat_call('findValueIndex_Center', $args); }
}
if (!function_exists('generate_random_string')) {
  function generate_random_string(...$args) { return vnx_center_compat_call('generate_random_string_Center', $args); }
}
if (!function_exists('generateRandomString')) {
  function generateRandomString(...$args) { return vnx_center_compat_call('generateRandomString_Center', $args); }
}
if (!function_exists('get_api_listDomain_whmcs')) {
  function get_api_listDomain_whmcs(...$args) { return vnx_center_compat_call('get_api_listDomain_whmcs_Center', $args); }
}
if (!function_exists('get_api_whmcs')) {
  function get_api_whmcs(...$args) { return vnx_center_compat_call('get_api_whmcs_Center', $args); }
}
if (!function_exists('get_api_whmcs_checkAllDomain')) {
  function get_api_whmcs_checkAllDomain(...$args) { return vnx_center_compat_call('get_api_whmcs_checkAllDomain_Center', $args); }
}
if (!function_exists('get_domain_data_whois_ldpage')) {
  function get_domain_data_whois_ldpage(...$args) { return vnx_center_compat_call('get_domain_data_whois_ldpage_Center', $args); }
}
if (!function_exists('get_listDomian_suggest')) {
  function get_listDomian_suggest(...$args) { return vnx_center_compat_call('get_listDomian_suggest_Center', $args); }
}
if (!function_exists('get_listDomian_Tab')) {
  function get_listDomian_Tab(...$args) { return vnx_center_compat_call('get_listDomian_Tab_Center', $args); }
}
if (!function_exists('get_listDomian_whois')) {
  function get_listDomian_whois(...$args) { return vnx_center_compat_call('get_listDomian_whois_Center', $args); }
}
if (!function_exists('get_listpriceDomain')) {
  function get_listpriceDomain(...$args) { return vnx_center_compat_call('get_listpriceDomain_Center', $args); }
}
if (!function_exists('get_listpriceRenewDomain')) {
  function get_listpriceRenewDomain(...$args) { return vnx_center_compat_call('get_listpriceRenewDomain_Center', $args); }
}
if (!function_exists('get_suggest_item_chunk')) {
  function get_suggest_item_chunk(...$args) { return vnx_center_compat_call('get_suggest_item_chunk_Center', $args); }
}
if (!function_exists('get_tld_pricing_from_api')) {
  function get_tld_pricing_from_api(...$args) { return vnx_center_compat_call('get_tld_pricing_from_api_Center', $args); }
}
if (!function_exists('get_whois_data')) {
  function get_whois_data(...$args) { return vnx_center_compat_call('get_whois_data_Center', $args); }
}
if (!function_exists('get_whois_data_by_domain')) {
  function get_whois_data_by_domain(...$args) { return vnx_center_compat_call('get_whois_data_by_domain_Center', $args); }
}
if (!function_exists('get_whois_domain')) {
  function get_whois_domain(...$args) { return vnx_center_compat_call('get_whois_domain_Center', $args); }
}
if (!function_exists('getNewsForPortal')) {
  function getNewsForPortal(...$args) { return vnx_center_compat_call('getNewsForPortal_Center', $args); }
}
if (!function_exists('is_use_bricks')) {
  function is_use_bricks(...$args) { return vnx_center_compat_call('is_use_bricks_Center', $args); }
}
if (!function_exists('iwp_breadcrumbs')) {
  function iwp_breadcrumbs(...$args) { return vnx_center_compat_call('iwp_breadcrumbs_Center', $args); }
}
if (!function_exists('litespeed_cusstom_purge_cache')) {
  function litespeed_cusstom_purge_cache(...$args) { return vnx_center_compat_call('litespeed_cusstom_purge_cache_Center', $args); }
}
if (!function_exists('loadThemespost_init')) {
  function loadThemespost_init(...$args) { return vnx_center_compat_call('loadThemespost_init_Center', $args); }
}
if (!function_exists('move_variable_and_following_elements_to_first_index')) {
  function move_variable_and_following_elements_to_first_index(...$args) { return vnx_center_compat_call('move_variable_and_following_elements_to_first_index_Center', $args); }
}
if (!function_exists('my_sortable_cake_column')) {
  function my_sortable_cake_column(...$args) { return vnx_center_compat_call('my_sortable_cake_column_Center', $args); }
}
if (!function_exists('my_sortable_cake_column_dev')) {
  function my_sortable_cake_column_dev(...$args) { return vnx_center_compat_call('my_sortable_cake_column_dev_Center', $args); }
}
if (!function_exists('post_content_filter_by_paragraph')) {
  function post_content_filter_by_paragraph(...$args) { return vnx_center_compat_call('post_content_filter_by_paragraph_Center', $args); }
}
if (!function_exists('prefix_debug_redirection')) {
  function prefix_debug_redirection(...$args) { return vnx_center_compat_call('prefix_debug_redirection_Center', $args); }
}
if (!function_exists('prevent_parent_post_access')) {
  function prevent_parent_post_access(...$args) { return vnx_center_compat_call('prevent_parent_post_access_Center', $args); }
}
if (!function_exists('regenerate_attachment_slugs')) {
  function regenerate_attachment_slugs(...$args) { return vnx_center_compat_call('regenerate_attachment_slugs_Center', $args); }
}
if (!function_exists('register_button_widget_block')) {
  function register_button_widget_block(...$args) { return vnx_center_compat_call('register_button_widget_block_Center', $args); }
}
if (!function_exists('register_shortcode_widget_block')) {
  function register_shortcode_widget_block(...$args) { return vnx_center_compat_call('register_shortcode_widget_block_Center', $args); }
}
if (!function_exists('register_vnx_block_table_coupon')) {
  function register_vnx_block_table_coupon(...$args) { return vnx_center_compat_call('register_vnx_block_table_coupon_Center', $args); }
}
if (!function_exists('register_vnx_blockquote_block')) {
  function register_vnx_blockquote_block(...$args) { return vnx_center_compat_call('register_vnx_blockquote_block_Center', $args); }
}
if (!function_exists('register_vnx_compare_block')) {
  function register_vnx_compare_block(...$args) { return vnx_center_compat_call('register_vnx_compare_block_Center', $args); }
}
if (!function_exists('register_vnx_featured_snippet')) {
  function register_vnx_featured_snippet(...$args) { return vnx_center_compat_call('register_vnx_featured_snippet_Center', $args); }
}
if (!function_exists('register_vnx_note_block')) {
  function register_vnx_note_block(...$args) { return vnx_center_compat_call('register_vnx_note_block_Center', $args); }
}
if (!function_exists('register_vnx_note_icon_block')) {
  function register_vnx_note_icon_block(...$args) { return vnx_center_compat_call('register_vnx_note_icon_block_Center', $args); }
}
if (!function_exists('register_vnx_view_more_block')) {
  function register_vnx_view_more_block(...$args) { return vnx_center_compat_call('register_vnx_view_more_block_Center', $args); }
}
if (!function_exists('reset_attachment_slugs_to_default')) {
  function reset_attachment_slugs_to_default(...$args) { return vnx_center_compat_call('reset_attachment_slugs_to_default_Center', $args); }
}
if (!function_exists('sendTelegramMessage')) {
  function sendTelegramMessage(...$args) { return vnx_center_compat_call('sendDiscordCallmeMessage_Center', $args); }
}
if (!function_exists('showIcon')) {
  function showIcon(...$args) { return vnx_center_compat_call('showIcon_Center', $args); }
}
if (!function_exists('smashing_posts_orderby')) {
  function smashing_posts_orderby(...$args) { return vnx_center_compat_call('smashing_posts_orderby_Center', $args); }
}
if (!function_exists('suggest_domain')) {
  function suggest_domain(...$args) { return vnx_center_compat_call('suggest_domain_Center', $args); }
}
if (!function_exists('svg_is_displayable_image')) {
  function svg_is_displayable_image(...$args) { return vnx_center_compat_call('svg_is_displayable_image_Center', $args); }
}
if (!function_exists('validate_single_domain')) {
  function validate_single_domain(...$args) { return vnx_center_compat_call('validate_single_domain_Center', $args); }
}
if (!function_exists('vietnix_banner_add_meta_box')) {
  function vietnix_banner_add_meta_box(...$args) { return vnx_center_compat_call('vietnix_banner_add_meta_box_Center', $args); }
}
if (!function_exists('vietnix_banner_register_post_type')) {
  function vietnix_banner_register_post_type(...$args) { return vnx_center_compat_call('vietnix_banner_register_post_type_Center', $args); }
}
if (!function_exists('vietnix_banner_shortcode')) {
  function vietnix_banner_shortcode(...$args) { return vnx_center_compat_call('vietnix_banner_shortcode_Center', $args); }
}
if (!function_exists('vietnix_banner_shortcode_box')) {
  function vietnix_banner_shortcode_box(...$args) { return vnx_center_compat_call('vietnix_banner_shortcode_box_Center', $args); }
}
if (!function_exists('vietnix_banner_single_metabox')) {
  function vietnix_banner_single_metabox(...$args) { return vnx_center_compat_call('vietnix_banner_single_metabox_Center', $args); }
}
if (!function_exists('vietnix_banner_single_metabox_content')) {
  function vietnix_banner_single_metabox_content(...$args) { return vnx_center_compat_call('vietnix_banner_single_metabox_content_Center', $args); }
}
if (!function_exists('vietnix_banner_single_metabox_save')) {
  function vietnix_banner_single_metabox_save(...$args) { return vnx_center_compat_call('vietnix_banner_single_metabox_save_Center', $args); }
}
if (!function_exists('vietnix_banner_single_metabox_style')) {
  function vietnix_banner_single_metabox_style(...$args) { return vnx_center_compat_call('vietnix_banner_single_metabox_style_Center', $args); }
}
if (!function_exists('vietnix_plugin_enqueue_admin_style')) {
  function vietnix_plugin_enqueue_admin_style(...$args) { return vnx_center_compat_call('vietnix_plugin_enqueue_admin_style_Center', $args); }
}
if (!function_exists('vnx_add_page_template_to_dropdown')) {
  function vnx_add_page_template_to_dropdown(...$args) { return vnx_center_compat_call('vnx_add_page_template_to_dropdown_Center', $args); }
}
if (!function_exists('vnx_ajax_get_custom_post_type')) {
  function vnx_ajax_get_custom_post_type(...$args) { return vnx_center_compat_call('vnx_ajax_get_custom_post_type_Center', $args); }
}
// vnx_api_send_Center (Telegram) da bi xoa; giu wrapper de snippet cu con goi chi ghi log, khong fatal.
if (!function_exists('vnx_api_send')) {
  function vnx_api_send(...$args) { return vnx_center_compat_call('vnx_api_send_Center', $args); }
}
if (!function_exists('vnx_block_table_coupon_style')) {
  function vnx_block_table_coupon_style(...$args) { return vnx_center_compat_call('vnx_block_table_coupon_style_Center', $args); }
}
if (!function_exists('vnx_blockquote_block_style')) {
  function vnx_blockquote_block_style(...$args) { return vnx_center_compat_call('vnx_blockquote_block_style_Center', $args); }
}
if (!function_exists('vnx_build_categories_tree')) {
  function vnx_build_categories_tree(...$args) { return vnx_center_compat_call('vnx_build_categories_tree_Center', $args); }
}
if (!function_exists('vnx_change_page_template')) {
  function vnx_change_page_template(...$args) { return vnx_center_compat_call('vnx_change_page_template_Center', $args); }
}
if (!function_exists('vnx_compare_block_get_content')) {
  function vnx_compare_block_get_content(...$args) { return vnx_center_compat_call('vnx_compare_block_get_content_Center', $args); }
}
if (!function_exists('vnx_compare_block_style')) {
  function vnx_compare_block_style(...$args) { return vnx_center_compat_call('vnx_compare_block_style_Center', $args); }
}
if (!function_exists('vnx_FormSendMessageDiscord')) {
  function vnx_FormSendMessageDiscord(...$args) { return vnx_center_compat_call('vnx_FormSendMessageDiscord_Center', $args); }
}
if (!function_exists('vnx_get_custom_posts_list')) {
  function vnx_get_custom_posts_list(...$args) { return vnx_center_compat_call('vnx_get_custom_posts_list_Center', $args); }
}
if (!function_exists('vnx_get_custom_posts_template')) {
  function vnx_get_custom_posts_template(...$args) { return vnx_center_compat_call('vnx_get_custom_posts_template_Center', $args); }
}
if (!function_exists('vnx_get_listpost')) {
  function vnx_get_listpost(...$args) { return vnx_center_compat_call('vnx_get_listpost_Center', $args); }
}
if (!function_exists('vnx_get_posts_template')) {
  function vnx_get_posts_template(...$args) { return vnx_center_compat_call('vnx_get_posts_template_Center', $args); }
}
if (!function_exists('vnx_get_template_list')) {
  function vnx_get_template_list(...$args) { return vnx_center_compat_call('vnx_get_template_list_Center', $args); }
}
if (!function_exists('vnx_get_view_more_link_items')) {
  function vnx_get_view_more_link_items(...$args) { return vnx_center_compat_call('vnx_get_view_more_link_items_Center', $args); }
}
if (!function_exists('vnx_load_more')) {
  function vnx_load_more(...$args) { return vnx_center_compat_call('vnx_load_more_Center', $args); }
}
if (!function_exists('vnx_load_more_posts')) {
  function vnx_load_more_posts(...$args) { return vnx_center_compat_call('vnx_load_more_posts_Center', $args); }
}
if (!function_exists('vnx_load_theme_posts_init')) {
  function vnx_load_theme_posts_init(...$args) { return vnx_center_compat_call('vnx_load_theme_posts_init_Center', $args); }
}
if (!function_exists('vnx_my_check_new_post')) {
  function vnx_my_check_new_post(...$args) { return vnx_center_compat_call('vnx_my_check_new_post_Center', $args); }
}
if (!function_exists('vnx_note_icon_style')) {
  function vnx_note_icon_style(...$args) { return vnx_center_compat_call('vnx_note_icon_style_Center', $args); }
}
if (!function_exists('vnx_onload_list_post')) {
  function vnx_onload_list_post(...$args) { return vnx_center_compat_call('vnx_onload_list_post_Center', $args); }
}
if (!function_exists('vnx_pagi_ajax')) {
  function vnx_pagi_ajax(...$args) { return vnx_center_compat_call('vnx_pagi_ajax_Center', $args); }
}
if (!function_exists('vnx_pagi_ajax_theme_posts')) {
  function vnx_pagi_ajax_theme_posts(...$args) { return vnx_center_compat_call('vnx_pagi_ajax_theme_posts_Center', $args); }
}
if (!function_exists('vnx_paging_nav')) {
  function vnx_paging_nav(...$args) { return vnx_center_compat_call('vnx_paging_nav_Center', $args); }
}
if (!function_exists('vnx_paging_nav_custom_icon')) {
  function vnx_paging_nav_custom_icon(...$args) { return vnx_center_compat_call('vnx_paging_nav_custom_icon_Center', $args); }
}
if (!function_exists('vnx_plugin_general_submit')) {
  function vnx_plugin_general_submit(...$args) { return vnx_center_compat_call('vnx_plugin_general_submit_Center', $args); }
}
if (!function_exists('vnx_search_custom_posts_list')) {
  function vnx_search_custom_posts_list(...$args) { return vnx_center_compat_call('vnx_search_custom_posts_list_Center', $args); }
}
if (!function_exists('vnx_search_keywords_get_logs')) {
  function vnx_search_keywords_get_logs(...$args) { return vnx_center_compat_call('vnx_search_keywords_get_logs_Center', $args); }
}
if (!function_exists('vnx_search_keywords_to_iso8601')) {
  function vnx_search_keywords_to_iso8601(...$args) { return vnx_center_compat_call('vnx_search_keywords_to_iso8601_Center', $args); }
}
if (!function_exists('vnx_style')) {
  function vnx_style(...$args) { return vnx_center_compat_call('vnx_style_Center', $args); }
}
if (!function_exists('vnx_sync_telegram_sheet_submit')) {
  function vnx_sync_telegram_sheet_submit(...$args) { return vnx_center_compat_call('vnx_sync_discord_sheet_submit_Center', $args); }
}
if (!function_exists('vnx_utm_tracker_submit')) {
  function vnx_utm_tracker_submit(...$args) { return vnx_center_compat_call('vnx_utm_tracker_submit_Center', $args); }
}
if (!function_exists('vnx_view_more_style')) {
  function vnx_view_more_style(...$args) { return vnx_center_compat_call('vnx_view_more_style_Center', $args); }
}
if (!function_exists('vnxCutString')) {
  function vnxCutString(...$args) { return vnx_center_compat_call('vnxCutString_Center', $args); }
}
if (!function_exists('vnxGetAccessToken')) {
  function vnxGetAccessToken(...$args) { return vnx_center_compat_call('vnxGetAccessToken_Center', $args); }
}
if (!function_exists('vnxGetWorksheetId')) {
  function vnxGetWorksheetId(...$args) { return vnx_center_compat_call('vnxGetWorksheetId_Center', $args); }
}
if (!function_exists('vnxSyncDataSpreadsheets')) {
  function vnxSyncDataSpreadsheets(...$args) { return vnx_center_compat_call('vnxSyncDataSpreadsheets_Center', $args); }
}
if (!function_exists('webp_is_displayable')) {
  function webp_is_displayable(...$args) { return vnx_center_compat_call('webp_is_displayable_Center', $args); }
}
if (!function_exists('webp_upload_mimes')) {
  function webp_upload_mimes(...$args) { return vnx_center_compat_call('webp_upload_mimes_Center', $args); }
}
if (!function_exists('whois_get_domain_status')) {
  function whois_get_domain_status(...$args) { return vnx_center_compat_call('whois_get_domain_status_Center', $args); }
}
if (!function_exists('whois_get_suggest_item')) {
  function whois_get_suggest_item(...$args) { return vnx_center_compat_call('whois_get_suggest_item_Center', $args); }
}
if (!function_exists('whois_result_template_include')) {
  function whois_result_template_include(...$args) { return vnx_center_compat_call('whois_result_template_include_Center', $args); }
}
if (!function_exists('wpse_force_template')) {
  function wpse_force_template(...$args) { return vnx_center_compat_call('wpse_force_template_Center', $args); }
}

// AJAX action cu cua vietnix-plugin -> <ten>_center
// Center doi ten moi action wp_ajax_* thanh <ten>_center, nhung JS nam ngoai plugin (Code element
// cua Bricks, snippet WPCode...) van POST action cu -> admin-ajax tra "0" (400). Vd nut "Dang ky ngay"
// trang /enterprise-cloud-server/ goi action=vietnix_order_product. Chay tre tren admin_init (admin-ajax
// doc $_REQUEST['action'] sau hook nay): chi noi khi ten cu chua co handler va ban _center co handler.
add_action('admin_init', function () {
  if (!wp_doing_ajax() || empty($_REQUEST['action']) || !is_scalar($_REQUEST['action'])) {
    return;
  }
  $action = (string) $_REQUEST['action'];
  $hook = (is_user_logged_in() ? 'wp_ajax_' : 'wp_ajax_nopriv_') . $action;
  if (has_action($hook) || !has_action($hook . '_center')) {
    return;
  }
  add_action($hook, function () use ($hook) {
    do_action($hook . '_center');
  });
}, PHP_INT_MAX);
