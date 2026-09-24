<?php
/*
 ***********
 * Handle Gapi
 *
 */

function vnxSyncDataSpreadsheets_Center($spreadsheetId, $range, $data)
{
  // Set up credentials
  $authConfig = vnx_center_gbot_credentials();
  // Nguoi dung hay dan ca link Sheet vao o "ID Sheet": lay ID trong .../spreadsheets/d/{ID}/...
  $spreadsheetId = trim((string) $spreadsheetId);
  if (preg_match('#/spreadsheets/d/([a-zA-Z0-9_-]+)#', $spreadsheetId, $matches)) {
    $spreadsheetId = $matches[1];
  }

  $accessToken = vnxGetAccessToken_Center($authConfig);

  if ($accessToken) {
    $headers = array(
      "Authorization: Bearer " . $accessToken,
      "Content-Type: application/json"
    );
    $curl = curl_init();
    // Ten sheet co the co dau cach/tieng Viet -> phai encode trong URL.
    curl_setopt($curl, CURLOPT_URL, 'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($spreadsheetId) . '/values/' . rawurlencode(trim((string) $range)) . ':append?valueInputOption=USER_ENTERED');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($curl);
    $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    // Sai ID, sai ten sheet, service account chua duoc share quyen... truoc day deu im lang.
    if ($code < 200 || $code >= 300) {
      error_log('[vietnix-center] Google Sheet append failed (HTTP ' . $code . ', sheet ' . $spreadsheetId . ', range ' . $range . '): ' . ($curlError ?: substr((string) $response, 0, 500)));
      return false;
    }
    return true;
  } else {
    error_log('[vietnix-center] Google Sheet append skipped: khong lay duoc access token');
    return false;
  }
}

function vnxGetAccessToken_Center($authConfig)
{
  $client_email = $authConfig['client_email'];
  $private_key = $authConfig['private_key'];

  try {
    $scopes = ["https://www.googleapis.com/auth/spreadsheets"];
    $url = "https://www.googleapis.com/oauth2/v4/token";
    $header = array("alg" => "RS256", "typ" => "JWT");
    $now = floor(time());
    $claim = array(
      "iss" => $client_email,
      "sub" => $client_email,
      "scope" => implode(" ", $scopes),
      "aud" => $url,
      "exp" => (string)($now + 3600),
      "iat" => (string)$now,
    );
    $signature = base64_encode(json_encode($header, JSON_UNESCAPED_SLASHES)) . "." . base64_encode(json_encode($claim, JSON_UNESCAPED_SLASHES));
    $b = "";
    openssl_sign($signature, $b, $private_key, "SHA256");
    $jwt = $signature . "." . base64_encode($b);
    $curl_handle = curl_init();
    curl_setopt_array($curl_handle, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => array(
        "assertion" => $jwt,
        "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer"
      ),
    ]);
    $res = curl_exec($curl_handle);

    curl_close($curl_handle);
    $obj = json_decode($res);

    $accessToken = $obj->{'access_token'};
    return $accessToken ? $accessToken : -1;
  } catch (Exception $e) {
    //throw $th;
  }
}

function vnxGetWorksheetId_Center($accessToken, $spreadsheetId, $worksheetName)
{
  //  $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$worksheetName?fields=values";
  $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId?fields=sheets(properties(title,sheetId))";
  $headers = [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json',
  ];
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);

  $response = curl_exec($ch);
  curl_close($ch);
  $response = json_decode($response, true);
  foreach ($response['sheets'] as $sheet) {
    if ($sheet['properties']['title'] == $worksheetName) {
      return $sheet['properties']['sheetId'];
    }
  }
  return null;
}
