<?php
require_once __DIR__ . "/../components/static_api.php";
class Domain_dispatch_Center
{
  private $baseUrl = "https://whois.vietnix.vn/";
  private $staticApi;

  public function __construct($baseUrl = null)
  {
    $this->staticApi = new vnx_StaticApi_Center($baseUrl ?? $this->baseUrl);
  }

  public function get_domain_info($sld, $tld, $withprice = 0)
  {
    try {
      $response = $this->staticApi->get("available", [
        "sld" => $sld,
        "tld" => $tld,
        "withprice" => $withprice
      ]);
      return json_encode($response);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }

  public function get_whois_domain_Center($domain)
  {
    try {
      /*
       * ĐÃ TẮT (2026-08-20): trước đó tên miền .vn được tra whois trực tiếp
       * qua api.inet.vn (VNNIC) thay vì whois.vietnix.vn, xem đoạn dưới.
       * Theo yêu cầu, mọi tên miền (kể cả .vn) quay lại dùng whois.vietnix.vn
       * như cũ. Giữ lại đoạn code này (không xoá) để dễ bật lại nếu cần.
       *
       * // Tên miền .vn dùng API whois trực tiếp của api.inet.vn (VNNIC),
       * // tên miền quốc tế vẫn lấy như cũ qua whois.vietnix.vn
       * if (preg_match('/\.vn$/i', $domain)) {
       *   return json_encode($this->get_vn_whois_domain($domain));
       * }
       */

      $response = $this->staticApi->get("whois", [
        "domain" => $domain,
        "responsetype" => "json"
      ]);

      return json_encode($response);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }

  /*
   * ĐÃ TẮT (2026-08-20): hàm này lấy whois cho domain .vn từ api.inet.vn và
   * chuẩn hóa về dạng {data: [{label, value}]} để tương thích với các nơi
   * đang parse response của get_whois_domain_Center(). Không còn được gọi vì
   * get_whois_domain_Center() ở trên đã comment lại đoạn rẽ nhánh sang .vn.
   * Giữ nguyên nội dung để tiện bật lại nếu sau này cần dùng lại api.inet.vn.
   *
   * private function get_vn_whois_domain($domain)
   * {
   *   $response = wp_remote_post('https://api.inet.vn/api/public/whois/v1/whois/directly', [
   *     'timeout' => 30,
   *     'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
   *     'body' => ['domainName' => $domain],
   *   ]);
   *
   *   if (is_wp_error($response)) {
   *     return ['data' => []];
   *   }
   *
   *   $data = json_decode(wp_remote_retrieve_body($response), true);
   *
   *   if (empty($data) || ($data['code'] ?? '') !== '0') {
   *     return ['data' => []];
   *   }
   *
   *   $items = [];
   *   if (!empty($data['registrantName'])) {
   *     $items[] = ['label' => 'domainName', 'value' => $data['registrantName']];
   *   }
   *   if (!empty($data['registrar'])) {
   *     $items[] = ['label' => 'registrar', 'value' => $data['registrar']];
   *   }
   *   if (!empty($data['creationDate'])) {
   *     $items[] = ['label' => 'creationDate', 'value' => $data['creationDate']];
   *   }
   *   if (!empty($data['expirationDate'])) {
   *     $items[] = ['label' => 'registrarExpirationDate', 'value' => $data['expirationDate']];
   *   }
   *   if (!empty($data['nameServer']) && is_array($data['nameServer'])) {
   *     $items[] = ['label' => 'nameServers', 'value' => implode(', ', $data['nameServer'])];
   *   }
   *   if (!empty($data['status']) && is_array($data['status'])) {
   *     $items[] = ['label' => 'domainStatus', 'value' => implode(', ', $data['status'])];
   *   }
   *   if (!empty($data['DNSSEC'])) {
   *     $items[] = ['label' => 'dnssec', 'value' => $data['DNSSEC']];
   *   }
   *
   *   return ['data' => $items];
   * }
   */

  public function get_list_renew()
  {
    // url api: https://whois.vietnix.vn/price/list
    $response = $this->staticApi->get("price/list");
    return json_encode($response);
  }
}

if (!function_exists('check_domain_data_Center')) {
  function check_domain_data_Center($domain)
  {
    try {
      $domain_dispatch = new Domain_dispatch_Center();
      $data = ($_POST['data']['data'] == "") ? array() : $_POST['data']['data'];
      $domain = $domain != null ? $domain : $data['domain'];
      $dotPosition = strpos($domain, '.');
      $sld = ($dotPosition !== false) ? substr($domain, 0, $dotPosition) : '';
      $tld = ($dotPosition !== false) ? substr($domain, $dotPosition + 1) : '';

      $withprice = 1;
      $response = $domain_dispatch->get_domain_info($sld, $tld, $withprice);
      $response = json_decode($response, true);
      if (!empty($response) && isset($response[0]['isAvailable'])) {
        $available = $response[0]['isAvailable'] == true ? 'available' : 'unavailable';
        $premium = $response[0]['isPremium'];
        $tld = $response[0]['tld'];
        $sld = $response[0]['sld'];
        $price_domain = $response[0]['pricing']['register'][1];
        $formatted_price = number_format((float) $price_domain, 0, '', '.');
        $domainVn = 'available';
        if ($response[0]['tld'] == 'vn' && strlen($response[0]['sld']) <= 2) {
          $domainVn = 'unavailable';
        }
        if ($premium == true) {
          $response = array('result' => 'success', 'status' => $available, "sld" => $sld, "tld" => $tld, "premium" => $premium, "domainVn" => $domainVn);
        } else {
          $response = array('result' => 'success', 'status' => $available, "sld" => $sld, "tld" => $tld, "premium" => null, "domainVn" => $domainVn, 'pricedomain' => $formatted_price);
        }
      } else {
        $response = array('result' => 'error', 'message' => $response);
      }
      wp_send_json_success($response);
      exit;
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }

  add_action('wp_ajax_check_domain_data_center', 'check_domain_data_Center');
  add_action('wp_ajax_nopriv_check_domain_data_center', 'check_domain_data_Center');
}

if (!function_exists('get_listpriceDomain_Center')) {
  function get_listpriceDomain_Center()
  {
    try {
      $domain_dispatch = new Domain_dispatch_Center();
      $sld = 'vietnix';
      $string_tdl = $_POST['data'];
      $withprice = 1;
      $response = $domain_dispatch->get_domain_info($sld, $string_tdl, $withprice);
      wp_send_json_success(json_decode($response));
      exit;
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }
  }
  add_action('wp_ajax_get_listpriceDomain_center', 'get_listpriceDomain_Center');
  add_action('wp_ajax_nopriv_get_listpriceDomain_center', 'get_listpriceDomain_Center');
}

if (!function_exists('get_listpriceRenewDomain_Center')) {
  function get_listpriceRenewDomain_Center()
  {
    try {
      $domain_dispatch = new Domain_dispatch_Center();
      $response = $domain_dispatch->get_list_renew();
      wp_send_json_success(json_decode($response));
      exit;
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }
  }
  add_action('wp_ajax_get_listpriceRenewDomain_center', 'get_listpriceRenewDomain_Center');
  add_action('wp_ajax_nopriv_get_listpriceRenewDomain_center', 'get_listpriceRenewDomain_Center');
}

if (!function_exists('get_listDomian_suggest_Center')) {
  function get_listDomian_suggest_Center()
  {
    try {
      $domain_dispatch = new Domain_dispatch_Center();
      $domain = $_POST['domain'];
      $dotPosition = strpos($domain, '.');
      $sld = ($dotPosition !== false) ? substr($domain, 0, $dotPosition) : '';
      $tld = ($dotPosition !== false) ? substr($domain, $dotPosition + 1) : '';
      $string_tdl = $_POST['data'];
      $tlds = explode(',', $string_tdl);
      $tlds = array_map('trim', $tlds);
      if (($key = array_search($tld, $tlds)) !== false) {
        unset($tlds[$key]);
      }
      $string_tdl = implode(',', $tlds);
      $withprice = 1;
      $response = $domain_dispatch->get_domain_info($sld, $string_tdl, $withprice);

      $response = json_decode($response, true);
      if (isset($response['result']) && $response['result'] == 'error') {
        wp_send_json_error(array('success' => false));
      } else {
        wp_send_json_success($response);
      }

      wp_send_json_success($response);
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }

    exit;
  }
  add_action('wp_ajax_get_listDomian_suggest_center', 'get_listDomian_suggest_Center');
  add_action('wp_ajax_nopriv_get_listDomian_suggest_center', 'get_listDomian_suggest_Center');
}

//get_listDomian_Tab_Center
if (!function_exists('get_listDomian_Tab_Center')) {
  function get_listDomian_Tab_Center()
  {
    try {
      $domain_dispatch = new Domain_dispatch_Center();
      $domain = $_POST['domain'];
      $dotPosition = strpos($domain, '.');
      if ($dotPosition !== false) {
        $sld = substr($domain, 0, $dotPosition);
        $tld = substr($domain, $dotPosition + 1);
      } else {
        $sld = $domain;
        $tld = '';
      }
      $string_tdl = $_POST['data'];
      $tlds = explode(',', $string_tdl);
      $tlds = array_map('trim', $tlds);
      $string_tdl = implode(',', $tlds);
      $withprice = 1;
      $response = $domain_dispatch->get_domain_info($sld, $string_tdl, $withprice);

      $response = json_decode($response, true);
      if (isset($response['result']) && $response['result'] == 'error') {
        wp_send_json_error(array('success' => false));
      } else {
        wp_send_json_success($response);
      }

      wp_send_json_success($response);
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }

    exit;
  }
  add_action('wp_ajax_get_listDomian_Tab_center', 'get_listDomian_Tab_Center');
  add_action('wp_ajax_nopriv_get_listDomian_Tab_center', 'get_listDomian_Tab_Center');
}
// hàm lấy danh sách trạng thái domain trang muti whois
if (!function_exists('get_listDomian_whois_Center')) {
  function get_listDomian_whois_Center()
  {
    try {
      $domain_dispatch = new Domain_dispatch_Center();
      $sld = $_POST['domain'];
      $string_tdl = $_POST['data'];
      $withprice = 1;
      $response = $domain_dispatch->get_domain_info($sld, $string_tdl, $withprice);


      wp_send_json_success(json_decode($response));
      exit;
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }
  }
  add_action('wp_ajax_get_listDomian_whois_center', 'get_listDomian_whois_Center');
  add_action('wp_ajax_nopriv_get_listDomian_whois_center', 'get_listDomian_whois_Center');
}

if (!function_exists('get_whois_domain_Center')) {
  function get_whois_domain_Center()
  {
    try {
      $domain_dispatch = new Domain_dispatch_Center();
      $domain = $_POST['domain'];
      $response = $domain_dispatch->get_whois_domain_Center($domain);
      wp_send_json_success(json_decode($response));
      exit;
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }
  }
  add_action('wp_ajax_get_whois_domain_center', 'get_whois_domain_Center');
  add_action('wp_ajax_nopriv_get_whois_domain_center', 'get_whois_domain_Center');
}


if (!function_exists('validate_single_domain_Center')) {
  function validate_single_domain_Center()
  {
    try {
      $domain = isset($_POST['domain']) ? $_POST['domain'] : '';

      if (empty($domain)) {
        wp_send_json_error(array('message' => 'Domain is required'));
        exit;
      }

      if (!class_exists('VNX_Validate_Domain_Center')) {
        wp_send_json_success(array('is_valid' => true));
        exit;
      }

      $validator = VNX_Validate_Domain_Center::instance();
      $black_list = $validator->get_black_list();
      $blocked_prefixes = $validator->get_blocked_prefixes();

      $sld = strtolower(explode('.', $domain)[0]);

      foreach ($black_list as $keyword) {
        if (stripos($sld, $keyword) !== false) {
          wp_send_json_error(array(
            'message' => 'Tên miền chứa từ khóa không được phép: ' . $keyword,
            'is_valid' => false
          ));
          exit;
        }
      }

      foreach ($blocked_prefixes as $prefix) {
        if (strpos($sld, $prefix) === 0) {
          wp_send_json_error(array(
            'message' => 'Tên miền có tiền tố không được phép: ' . $prefix,
            'is_valid' => false
          ));
          exit;
        }
      }

      wp_send_json_success(array('is_valid' => true));
      exit;
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }
  }
  add_action('wp_ajax_validate_single_domain_center', 'validate_single_domain_Center');
  add_action('wp_ajax_nopriv_validate_single_domain_center', 'validate_single_domain_Center');
}
//get_api_listDomain_whmcs_Center