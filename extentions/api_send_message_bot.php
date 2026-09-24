<?php
require_once VNX_PLUGIN_PATH_CENTER . '/extentions/helper/gapi.php';
require_once VNX_PLUGIN_PATH_CENTER . '/extentions/helper/DataSyncDiscordSheet_Center.php';
require_once VNX_PLUGIN_PATH_CENTER . '/extentions/helper/discord_notify.php';



// 'bricks/form/validate' la filter: tham so 1 la mang loi validate, phai tra lai nguyen ven,
// neu khong loi cua cac callback truoc (vd domain_transfer_form_validate_Center) se bi xoa.
function bricksSendDiscordMessage_Center($validation_errors, $handler)
{
  // Form se bi Bricks tu choi thi khong gui, tranh gui tin cho ban ghi khong hop le.
  if (!vnx_bricks_form_passes_validation_Center($validation_errors, $handler)) {
    return $validation_errors;
  }

  try {

    $dataSyncTelegramSheet = new DataSyncDiscordSheet_Center();

    $record = $handler->get_fields();

    // form field
    $formFieldId = !empty($record['form_field_id']) ? $record['form_field_id'] : '';
    $referrer = !empty($record['referrer']) ? $record['referrer'] : '';

    if ($formFieldId) {
      $info_sync_telegram_sheet = $dataSyncTelegramSheet->getDataSyncDiscordSheet($formFieldId, $referrer, $record);

      //processed data
      $dataSendDiscord = $info_sync_telegram_sheet['dataSendDiscord'];
      $dataSendGGSheet = $info_sync_telegram_sheet['dataSendGGSheet'];

      //array
      $discordWebhook = $info_sync_telegram_sheet['discordWebhook'];
      $sheetID = $info_sync_telegram_sheet['sheetID'];
      $pageName = $info_sync_telegram_sheet['pageName'];

      // hande send Discord (form chua dien webhook thi bo qua)
      if (!empty($discordWebhook) && !empty($dataSendDiscord['content'])) {
        vnx_discord_notify_Center($discordWebhook, $dataSendDiscord['title'], $dataSendDiscord['content']);
      }

      // hande send api GG sheet
      if (is_array($sheetID) && $dataSendGGSheet && $pageName) {
        foreach ($sheetID as $index => $value) {
          vnxSyncDataSpreadsheets_Center($value, $pageName[$index], $dataSendGGSheet);
        }
      }
    } elseif (!empty($record['form_field_name']) || !empty($record['form_field_email']) || !empty($record['form_field_phone'])) {
      // Form dung thu kieu cu (khong co form_field_id). Form khac (tim viec, danh gia, chuyen ten mien...)
      // khong vao day - truoc day van gui tin "DANG KY DUNG THU" rong.
      vnx_discord_notify_Center(
        vnx_discord_get_webhook_Center('discord_webhook_trial'),
        '🆕 ĐĂNG KÝ DÙNG THỬ MIỄN PHÍ',
        vnx_discord_format_fields_Center([
          'Tên' => $record['form_field_name'] ?? '',
          'Email' => $record['form_field_email'] ?? '',
          'SĐT' => $record['form_field_phone'] ?? '',
          'Gói' => $record['form_field_package'] ?? '',
        ]),
        'e74c3c'
      );
    }
  } catch (\Throwable $e) {

    require_once VNX_PLUGIN_PATH_CENTER . '/functions/requires/push_logs_error.php';
    $push_log = new Vnx_Push_Logger_Center();
    $push_log->vnxPushLogger($e->getMessage(), ['File' => __FILE__, 'Line' => $e->getLine()]);
    throw $e;
  }

  return $validation_errors;
}

// Priority cuoi cung: cho cac validator khac (vd domain_transfer_form_validate_Center) chay truoc.
add_filter('bricks/form/validate', 'bricksSendDiscordMessage_Center', PHP_INT_MAX, 2);
