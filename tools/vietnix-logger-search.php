<?php

/**
 * Vietnix Logger Search
 *
 * Ghi log từng lượt tìm kiếm của người dùng trên site.
 * File này chỉ được nạp khi option "Vietnix Logger Search" ở tab Tool đang bật,
 * nên tắt option là dừng ghi log — bảng dữ liệu vẫn giữ nguyên, không xoá.
 *
 * Đọc lại qua REST: /wp-json/vnx_api/v1/search-keywords
 */

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

define_if_not_defined_Center('VNX_SEARCH_KEYWORDS_TABLE', 'vnx_search_keywords');

/**
 * 1. Tạo bảng khi option được bật.
 *
 * Chạy ở admin_init vì bật option là một request admin, nên bảng được tạo ngay
 * lúc bấm Update và front-end không phải gánh thêm query nào.
 * Đã có bảng thì thoát luôn — không có nhánh nào xoá hay tạo lại bảng.
 */
function vnx_search_keywords_install_v2_Center(): void
{
  global $wpdb;

  $table = $wpdb->prefix . VNX_SEARCH_KEYWORDS_TABLE;

  if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table) {
    return;
  }

  // Mỗi lượt search là một dòng nên bảng không có ràng buộc UNIQUE nào.
  $wpdb->query("CREATE TABLE IF NOT EXISTS `{$table}` (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    keyword varchar(255) NOT NULL,
    url_search varchar(255) NOT NULL DEFAULT '',
    created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    KEY keyword (keyword(191)),
    KEY created_at (created_at)
  ) " . $wpdb->get_charset_collate() . ";");
}
add_action('admin_init', 'vnx_search_keywords_install_v2_Center');

/**
 * 2. Endpoint AJAX nhận từ khoá do snippet JS gửi lên rồi ghi vào DB.
 *
 * Search element render form HTML thuần, không có hook submit nào — nên bắt sự
 * kiện ở phía JS rồi bắn về đây. Chạy trên admin-ajax nên không phải chờ
 * template load như template_redirect.
 *
 * JS gửi: action, keyword, url. Có từ khoá là ghi, không lọc bot hay IP.
 */
function vnx_search_keywords_capture_v2_Center(): void
{
  global $wpdb;

  $keyword = sanitize_text_field(wp_unslash($_POST['keyword'] ?? ''));
  $keyword = preg_replace('/\s+/u', ' ', trim($keyword));

  if ($keyword === '') {
    wp_send_json_error(array('code' => 'empty_keyword'), 400);
  }

  // Chỉ giữ đường dẫn trang, bỏ query string và anchor. Ép (string) vì strtok
  // trả false khi chuỗi chỉ toàn ký tự phân cách (ví dụ url = "#").
  $url = esc_url_raw(wp_unslash($_POST['url'] ?? ''));
  $url = $url !== '' ? (string) strtok(strtok($url, '#'), '?') : '';

  // Cắt cho vừa cột varchar(255), tránh insert lỗi khi MySQL ở strict mode.
  $keyword = mb_substr($keyword, 0, 255, 'UTF-8');
  $url     = mb_substr($url, 0, 255, 'UTF-8');

  $inserted = $wpdb->insert(
    $wpdb->prefix . VNX_SEARCH_KEYWORDS_TABLE,
    array(
      'keyword'    => $keyword,
      'url_search' => $url,
      'created_at' => current_time('mysql'),
    ),
    array('%s', '%s', '%s')
  );

  if ($inserted === false) {
    error_log('[VNX_SEARCH_KEYWORDS] insert failed: ' . $wpdb->last_error);
    wp_send_json_error(array('code' => 'db_error'), 500);
  }

  wp_send_json_success(array('code' => 'logged'));
}
// Snippet JS ghi log nam ngoai plugin (Bricks/GTM) va goi dung ten action cua vietnix-plugin,
// nen center phai nghe ca ten chuan thi doi plugin moi khong mat log. Khi ca 2 cung active,
// callback chay truoc da wp_send_json (die) nen moi luot tim kiem van chi ghi 1 dong.
add_action('wp_ajax_vnx_log_search_keyword', 'vnx_search_keywords_capture_v2_Center');
add_action('wp_ajax_nopriv_vnx_log_search_keyword', 'vnx_search_keywords_capture_v2_Center');
add_action('wp_ajax_vnx_log_search_keyword_center', 'vnx_search_keywords_capture_v2_Center');
add_action('wp_ajax_nopriv_vnx_log_search_keyword_center', 'vnx_search_keywords_capture_v2_Center');

/**
 * 3. Đổi datetime trong DB sang ISO 8601 UTC: 2026-09-07T04:15:33.000Z
 *
 * Cột created_at ghi bằng current_time('mysql') nên đang là giờ local của site,
 * phải quy về UTC trước khi gắn hậu tố Z — nếu không sẽ lệch đúng bằng offset.
 * Giữ đúng định dạng của vietnix-plugin để API trả cùng một kiểu dữ liệu.
 */
function vnx_search_keywords_to_iso8601_Center(?string $mysql_datetime): string
{
  $mysql_datetime = trim((string) $mysql_datetime);

  // Dòng cũ có thể mang giá trị mặc định '0000-00-00 00:00:00', không parse được.
  if ($mysql_datetime === '' || strpos($mysql_datetime, '0000-00-00') === 0) {
    return '';
  }

  try {
    $date = new DateTimeImmutable($mysql_datetime, wp_timezone());
  } catch (Exception $e) {
    return '';
  }

  return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.v\Z');
}

/**
 * 4. Đọc log cho API dùng lại (api/vietnixApi_SearchKeywords.php).
 *
 * @param array $args search, dateFrom, dateTo, page, itemsPerPage, orderBy, sortDir
 * @return array{items: array, total: int, page: int, itemsPerPage: int}
 */
function vnx_search_keywords_get_logs_Center(array $args = array()): array
{
  global $wpdb;

  $args = array_merge(array(
    'search'       => '',
    'dateFrom'     => '',
    'dateTo'       => '',
    'page'         => 1,
    'itemsPerPage' => 20,
    'orderBy'      => 'created_at',
    'sortDir'      => 'desc',
  ), $args);

  $table = $wpdb->prefix . VNX_SEARCH_KEYWORDS_TABLE;
  $page  = max(1, (int) $args['page']);
  $per   = max(1, min(100, (int) $args['itemsPerPage']));

  if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
    return array('items' => array(), 'total' => 0, 'page' => $page, 'itemsPerPage' => $per);
  }

  // Whitelist để tránh SQL injection qua ORDER BY.
  $order_by = strtolower(trim((string) $args['orderBy']));
  $column   = in_array($order_by, array('created_at', 'keyword', 'id'), true) ? $order_by : 'created_at';
  $dir      = strtolower(trim((string) $args['sortDir'])) === 'asc' ? 'ASC' : 'DESC';

  $where  = array('1=1');
  $values = array();

  $search = trim((string) $args['search']);
  if ($search !== '') {
    $where[]  = 'keyword LIKE %s';
    $values[] = '%' . $wpdb->esc_like($search) . '%';
  }

  // Chỉ có ngày thì gắn thêm giờ, nếu không mốc cuối sẽ mất trọn ngày cuối.
  $date_from = trim((string) $args['dateFrom']);
  if ($date_from !== '') {
    $where[]  = 'created_at >= %s';
    $values[] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) ? $date_from . ' 00:00:00' : $date_from;
  }

  $date_to = trim((string) $args['dateTo']);
  if ($date_to !== '') {
    $where[]  = 'created_at <= %s';
    $values[] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to) ? $date_to . ' 23:59:59' : $date_to;
  }

  $where_sql = implode(' AND ', $where);

  $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
  $total     = (int) $wpdb->get_var($values ? $wpdb->prepare($count_sql, $values) : $count_sql);

  $rows = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT keyword, url_search, created_at FROM {$table}
       WHERE {$where_sql}
       ORDER BY {$column} {$dir}, id DESC
       LIMIT %d OFFSET %d",
      array_merge($values, array($per, ($page - 1) * $per))
    ),
    ARRAY_A
  );

  $items = array_map(function ($row) {
    return array(
      'keyword'   => $row['keyword'],
      'createdAt' => vnx_search_keywords_to_iso8601_Center($row['created_at']),
      'urlSearch' => $row['url_search'],
    );
  }, $rows ?: array());

  return array(
    'items'        => $items,
    'total'        => $total,
    'page'         => $page,
    'itemsPerPage' => $per,
  );
}
