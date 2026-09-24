<?php

use HelperCenter\View;

if (!function_exists('get_api_whmcs_Center')) {
  function get_api_whmcs_Center()
  {
    try {
      if (!isset($_POST['data']['data']['security']) || !wp_verify_nonce($_POST['data']['data']['security'], 'domain_checking')) {
        die('Permission Denied.');
      }
      $whmcs_link = (isset($_POST['whmcs_api_link']) && $_POST['whmcs_api_link']) ? $_POST['whmcs_api_link'] : VNX_WHMCS_API_LINK;
      $data = ($_POST['data']['data'] == "") ? array() : $_POST['data']['data'];
      $url = $whmcs_link;
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        http_build_query(
          array_merge(
            array(
              'action' => $_POST['data']['action'],
              'username' => '51bYFLhdmtgx5E6AdIdPggIWWynCbRqe',
              'password' => 'zzEDyVQgciVvwbSAt4CL8P9l1LVn02vn',
              'responsetype' => 'json',
            ),
            $data
          )
        )
      );
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $response = curl_exec($ch);
      curl_close($ch);
      $response = json_decode($response, true);
      if (isset($response['result']) && $response['result'] == "success" && isset($response['status']) && $response['status'] == "available") {
        $parts = explode(".", $data['domain'], 2);
        $domain_name = $parts[0];
        $tld = $parts[1];
        $premium = check_premium_domain_Center($domain_name, $tld);
        $premium = array("premium" => $premium);
        $response = array_merge($response, $premium);
        echo json_encode($response);
      } else {
        echo json_encode($response);
      }
      exit;
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }
  add_action('wp_ajax_get_api_whmcs_center', 'get_api_whmcs_Center');
  add_action('wp_ajax_nopriv_get_api_whmcs_center', 'get_api_whmcs_Center');
}

if (!function_exists('domain_vailid_checking_Center')) {
  function domain_vailid_checking_Center($domain = '')
  {
    try {
      $whmcs_link = VNX_DOMAIN_CHECKING;
      $api = true;
      if (isset($_POST['data']) && $domain == '') {
        if (isset($_POST['data']['data']['security']) && wp_verify_nonce($_POST['data']['data']['security'], 'domain_checking')) {
          $api = true;
          $data = ($_POST['data']['data'] == "") ? array() : $_POST['data']['data'];
          $domain = $data['domain'];
        } else {
          die('Permission Denied.');
        }
      } else {
        $api = false;
        $domain = $domain;
      }
      $dotPosition = strpos($domain, '.');
      $afterDot = ($dotPosition !== false) ? substr($domain, $dotPosition + 1) : '';
      $beforeDot = ($dotPosition !== false) ? substr($domain, 0, $dotPosition) : '';
      $url = $whmcs_link . '?sld=' . $beforeDot . '&tld=' . $afterDot;
      $curl = curl_init();
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        )
      );
      $response = curl_exec($curl);
      $response = json_decode($response, true);
      curl_close($curl);

      if (!empty($response)) {
        $available = $response[0]['isAvailable'] ? 'available' : 'unavailable';
        $response = array('result' => 'success', 'status' => $available);
        $premium = check_premium_domain_Center($beforeDot, $afterDot);
        $premium = array("premium" => $premium);
        $response = array_merge($response, $premium);
      } else {
        $response = array('result' => 'error', 'message' => $response);
      }
      if ($api == true) {
        echo json_encode($response);
      } else {
        return $response;
      }
      exit;
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }
  add_action('wp_ajax_domain_vailid_checking_center', 'domain_vailid_checking_Center');
  add_action('wp_ajax_nopriv_domain_vailid_checking_center', 'domain_vailid_checking_Center');
}

if (!function_exists('suggest_domain_Center')) {
  function suggest_domain_Center()
  {
    if (isset($_POST['data'][0]['domain_search']) && isset($_POST['data'][0]['template'])) {
      View::render('widgets/bricks/domain/' . $_POST['data'][0]['template'], ['domain_search' => $_POST['data'][0]['domain_search']]);
    }
    exit;
  }
  add_action('wp_ajax_suggest_domain_center', 'suggest_domain_Center');
  add_action('wp_ajax_nopriv_suggest_domain_center', 'suggest_domain_Center');
}

if (!function_exists('get_api_whmcs_checkAllDomain_Center')) {
  function get_api_whmcs_checkAllDomain_Center()
  {
    try {
      if (!isset($_POST['data']['data']['security']) || !wp_verify_nonce($_POST['data']['data']['security'], 'domain_suggest_checking')) {
        die('Permission Denied.');
      }
      $whmcs_link = (isset($_POST['whmcs_api_link']) && $_POST['whmcs_api_link']) ? $_POST['whmcs_api_link'] : VNX_WHMCS_API_LINK;
      $data = ($_POST['data']['data'] == "") ? array() : $_POST['data']['data'];
      $arr = [];
      foreach ($data["domain"] as $key => $value) {
        $domain_explode = explode('.', $value, 2);
        $domain_name = $domain_explode[0];
        $tld_checking = isset($domain_explode[1]) ? $domain_explode[1] : '';
        if ($tld_checking == 'vn' && strlen($domain_name) <= 2) {
          $response = array(
            'result' => 'success',
            'status' => '1',
          );
        } else {
          $response = domain_vailid_checking_Center($value);
          if ($response['result'] == 'success' && strtolower($response['status']) == "available") {
            $response = array('result' => 'success', 'status' => 'available');
            $premium = check_premium_domain_Center($domain_name, $tld_checking);
            $premium = array("premium" => $premium);
            $response = array_merge($response, $premium);
          }
        }
        array_push($arr, json_encode(["domain" => $value, "reponse" => $response]));
      }
      echo json_encode($arr);
      exit();
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }
  add_action('wp_ajax_get_api_whmcs_checkAllDomain_center', 'get_api_whmcs_checkAllDomain_Center');
  add_action('wp_ajax_nopriv_get_api_whmcs_checkAllDomain_center', 'get_api_whmcs_checkAllDomain_Center');
}

if (!function_exists('get_api_listDomain_whmcs_Center')) {
  function get_api_listDomain_whmcs_Center()
  {
    try {
      if (!isset($_POST['data']['data']['security']) || !wp_verify_nonce($_POST['data']['data']['security'], 'domain_suggest_checking')) {
        die('Permission Denied.');
      }
      $domain = $_POST['data']['data']['domain'];
      $res = [];
      foreach ($domain as $key => $value) {
        $res[substr($value, strpos($value, '.') + 1)] = domain_vailid_checking_Center($value);
      }
      $orderArray = ['vn', 'com', 'com.vn', 'net'];
      $orderedArray = [];
      foreach ($orderArray as $itemName) {
        if (isset($res[$itemName])) {
          $orderedArray[$itemName] = $res[$itemName];
          unset($res[$itemName]);
        }
      }
      $orderedArray = $orderedArray + $res;
      $random = ($_POST['data']['data']['random_string']) ?? '';
      wp_send_json_success(array($orderedArray, $random));
      exit;
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Some thing wrong!'));
    }
  }
  add_action('wp_ajax_get_api_listDomain_whmcs_center', 'get_api_listDomain_whmcs_Center');
  add_action('wp_ajax_nopriv_get_api_listDomain_whmcs_center', 'get_api_listDomain_whmcs_Center');
}

if (!function_exists('check_premium_domain_Center')) {
  function check_premium_domain_Center($domain_name, $tld)
  {

    $curl = curl_init();

    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => "https://domaincheck.httpapi.com/api/domains/available.json?auth-userid=439343&api-key=JkKvuRUzZBbt4us0omdF1N9ruXyWAYvg&domain-name={$domain_name}&tlds={$tld}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
      )
    );

    $response = curl_exec($curl);

    curl_close($curl);
    $response = json_decode($response, true);
    $checking = $response[$domain_name . '.' . $tld];
    return $checking;
  }
}
