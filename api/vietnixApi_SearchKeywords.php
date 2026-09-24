<?php

namespace VNX_API_Center;

class VietnixAPI_SearchKeywords
{
  public function __construct()
  {
    add_action('rest_api_init', array($this, 'register_route'));
  }

  public function register_route()
  {
    register_rest_route(VNX_Api_Prefix_V1, '/search-keywords', array(
      'methods' => 'GET',
      'permission_callback' => array('\VNX_API_Center\VietnixPluginAPI', 'authenticate'),
      'callback' => array($this, 'get_search_keywords'),
      'args' => array(
        'page' => array(
          'default' => 1,
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return is_numeric($param) && $param > 0;
          }
        ),
        'itemsPerPage' => array(
          'default' => 20,
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            if (!is_numeric($param) || $param <= 0 || $param > 100) {
              return new \WP_Error('invalid_items_per_page', 'The itemsPerPage parameter must be a number between 1 and 100.');
            }
            return true;
          }
        ),
        'search' => array(
          'default' => '',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return is_string($param);
          }
        ),
        'period' => array(
          'default' => '',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return in_array(strtolower($param), array('', 'today', 'yesterday', 'this_week', 'this_month', 'last_month', 'this_year'));
          }
        ),
        'dateFrom' => array(
          'default' => '',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return is_string($param);
          }
        ),
        'dateTo' => array(
          'default' => '',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return is_string($param);
          }
        ),
        'orderBy' => array(
          'default' => 'created_at',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return in_array(strtolower($param), array('', 'created_at', 'keyword', 'id'));
          }
        ),
        'sortDir' => array(
          'default' => 'desc',
          'required' => false,
          'validate_callback' => function ($param, $request, $key) {
            return in_array(strtolower($param), array('', 'asc', 'desc'));
          }
        ),
      ),
    ));
  }

  static function get_search_keywords(\WP_REST_Request $request)
  {
    // Không để cache che mất dữ liệu mới.
    nocache_headers();
    if (!headers_sent()) {
      header('X-LiteSpeed-Cache-Control: no-cache');
    }

    $page           = max(1, intval($request->get_param('page')));
    $items_per_page = max(1, min(100, intval($request->get_param('itemsPerPage'))));

    // Hàm đọc log nằm trong tool "Vietnix Logger Search"; tool tắt thì chưa có dữ liệu để trả.
    if (!function_exists('vnx_search_keywords_get_logs_Center')) {
      return self::empty_response($page, $items_per_page);
    }

    try {
      $date_from = trim((string) $request->get_param('dateFrom'));
      $date_to   = trim((string) $request->get_param('dateTo'));
      $period    = strtolower(trim((string) $request->get_param('period')));

      // dateFrom/dateTo truyền tay được ưu tiên hơn period.
      if ($period !== '') {
        $range     = self::resolve_period($period);
        $date_from = $date_from !== '' ? $date_from : $range['from'];
        $date_to   = $date_to !== '' ? $date_to : $range['to'];
      }

      $result = vnx_search_keywords_get_logs_Center(array(
        'search'       => trim((string) $request->get_param('search')),
        'dateFrom'     => $date_from,
        'dateTo'       => $date_to,
        'page'         => $page,
        'itemsPerPage' => $items_per_page,
        'orderBy'      => (string) $request->get_param('orderBy'),
        'sortDir'      => (string) $request->get_param('sortDir'),
      ));

      return new \WP_REST_Response(array(
        'success' => true,
        'message' => 'Search keywords fetched successfully',
        'items'   => $result['items'],
        'meta'    => array(
          'totalItems'   => $result['total'],
          'totalPages'   => (int) ceil($result['total'] / $result['itemsPerPage']),
          'page'         => $result['page'],
          'itemsPerPage' => $result['itemsPerPage'],
        ),
      ), 200);
    } catch (\Exception $e) {
      error_log('Error in get_search_keywords: ' . $e->getMessage());
      return new \WP_REST_Response(array(
        'success' => false,
        'message' => 'An error occurred while fetching search keywords',
        'error'   => $e->getMessage(),
      ), 500);
    }
  }

  private static function resolve_period(string $period): array
  {
    $today = current_time('Y-m-d');

    switch ($period) {
      case 'today':
        return array('from' => $today, 'to' => $today);
      case 'yesterday':
        $d = date('Y-m-d', strtotime($today . ' -1 day'));
        return array('from' => $d, 'to' => $d);
      case 'this_week':
        return array(
          'from' => date('Y-m-d', strtotime('monday this week', strtotime($today))),
          'to'   => $today,
        );
      case 'this_month':
        return array('from' => date('Y-m-01', strtotime($today)), 'to' => $today);
      case 'last_month':
        $first_last_month = date('Y-m-01', strtotime($today . ' first day of last month'));
        return array(
          'from' => $first_last_month,
          'to'   => date('Y-m-t', strtotime($first_last_month)),
        );
      case 'this_year':
        return array('from' => date('Y-01-01', strtotime($today)), 'to' => $today);
    }

    return array('from' => '', 'to' => '');
  }

  private static function empty_response($page, $items_per_page)
  {
    return new \WP_REST_Response(array(
      'success' => true,
      'message' => 'Search keywords fetched successfully',
      'items'   => array(),
      'meta'    => array(
        'totalItems'   => 0,
        'totalPages'   => 0,
        'page'         => $page,
        'itemsPerPage' => $items_per_page,
      ),
    ), 200);
  }
}
