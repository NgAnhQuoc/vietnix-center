<?php

use HelperCenter\DiscordBot;

require_once VNX_PLUGIN_PATH_CENTER . '/extentions/helper/gapi.php';

// 'bricks/form/validate' la filter: tham so 1 la mang loi validate, phai tra lai nguyen ven,
// neu khong loi cua cac callback truoc (vd domain_transfer_form_validate_Center) se bi xoa.
function vnx_FormSendMessageDiscord_Center($validation_errors, $handler)
{
  $record = $handler->get_fields();

  // Chi form dung thu hosting moi gui Discord; form khac bo qua (truoc day van gui voi title/message rong -> Discord 400)
  if (($record['form_field_id'] ?? '') !== 'dang-ky-dung-thu-hosting-mp') {
    return $validation_errors;
  }

  // Form se bi Bricks tu choi thi khong gui, tranh gui tin cho ban ghi khong hop le.
  if (!vnx_bricks_form_passes_validation_Center($validation_errors, $handler)) {
    return $validation_errors;
  }

  // Webhook Discord
  $linkHooks = "https://discord.com/api/webhooks/1385187864408494252/foU-hWT7wNtl4g6k_sWImAT9dSA14WH9Tby_G6JloiM3ItQTO90u1mLMPjmmbGtTTNbM";
  try {
    switch ($record['form_field_id']) {
      case "dang-ky-dung-thu-hosting-mp":
        $title = 'ĐĂNG KÝ DÙNG THỬ HOSTING' . "\n";
        $name = !empty($record['form_field_name']) ? $record['form_field_name'] : '';
        $email = !empty($record['form_field_email']) ? $record['form_field_email'] : '';
        $phone = !empty($record['form_field_phone']) ? $record['form_field_phone'] : '';
        $package = !empty($record['form_field_package']) ? $record['form_field_package'] : '';
        $service = !empty($record['form_field_service']) ? $record['form_field_service'] : '';
        $date = date("d/m/Y");
        $idform = !empty($record['formId']) ? $record['formId'] : '';
        $referrer = !empty($record['referrer']) ? $record['referrer'] : '';
        $message = "Họ và tên: " . $name . "\nEmail: " . $email . "\nSĐT: " . $phone . "\nDịch vụ: " . $service . "\nGói: " . $package . "\n";

        $pageName = 'Data-Trial-Hosting';
        $sheetID = '18FOaOGGHBDA8gcvQ3bVzwBPb6GjDmBVGfxF5qq4qyHg';
        $dataSendGGSheet = [
          "majorDimension" => "ROWS",
          "values" => [
            [
              "=row()-1",
              $name,
              $phone,
              $email,
              $service,
              $package,
              $date,
              $idform,
              $referrer,
            ]
          ]
        ];
        vnxSyncDataSpreadsheets_Center($sheetID, $pageName, $dataSendGGSheet);
        break;
    }
    DiscordBot::sendMessageByWebhook(
      $linkHooks,
      $title,
      $message,
      '#F68E13'
    );
  } catch (Exception $ex) {
    error_log('Lỗi gửi discord action form: ' . $ex->getMessage());
  }

  return $validation_errors;
}
// Chay song song thi vietnix-plugin da gui Discord cho form nay, dang ky lai se gui 2 lan.
if (!vnx_center_companion_mode()) {
  // Priority cuoi cung: cho cac validator khac chay truoc.
  add_filter('bricks/form/validate', 'vnx_FormSendMessageDiscord_Center', PHP_INT_MAX, 2);
}
