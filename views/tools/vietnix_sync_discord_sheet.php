<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

$vnx_notice = vnx_tool_notice_consume_Center('vietnix-sync-discord-sheet');
$vnx_sync_telegram_sheet_setting = null;

try {
  $vnx_sync_telegram_sheet_setting = json_decode(get_option('vnx_sync_telegram_sheet_setting'));
} catch (\Throwable $e) {
  require_once VNX_PLUGIN_PATH_CENTER . '/functions/requires/push_logs_error.php';
  $push_log = new Vnx_Push_Logger_Center();
  $push_log->vnxPushLogger($e->getMessage(), ['File' => __FILE__, 'Line' => $e->getLine()]);
  throw $e;
}

// Ep ve mang: json_decode('{}') la object rong, empty() cua object luon false.
if (empty((array) $vnx_sync_telegram_sheet_setting)) {
?>
  <div class="vnx-panel">
    <?php View::render('tools/partials/alert', array('message' => $vnx_notice['message'], 'type' => $vnx_notice['type'])); ?>

    <div class="vnx-empty">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <p>Chưa có form nào được ghi nhận. Gửi thử một form trên site để nó xuất hiện ở đây.</p>
    </div>
  </div>
<?php
  return;
}

$vnx_sync_form_slugs = array();
$vnx_sync_form_index = 0;
foreach ($vnx_sync_telegram_sheet_setting as $formName => $value) {
  $vnx_sync_form_slugs[$formName] = sanitize_title($formName) . '-' . $vnx_sync_form_index;
  $vnx_sync_form_index++;
}

// Ten hien thi: "Form name" (hoac tieu de template/trang) cua form Bricks, kem slug de van tra cuu duoc.
$vnx_sync_form_info = function_exists('vnx_sync_form_info_Center') ? vnx_sync_form_info_Center() : array();
$vnx_sync_form_name = function ($formName) use ($vnx_sync_form_info) {
  return ($vnx_sync_form_info[(string) $formName]['name'] ?? '') !== '' ? $vnx_sync_form_info[(string) $formName]['name'] : (string) $formName;
};
$vnx_sync_form_label = function ($formName) use ($vnx_sync_form_name) {
  $name = $vnx_sync_form_name($formName);
  return $name !== (string) $formName ? $name . ' (' . $formName . ')' : $name;
};
// Tieu de card: "Ten (ID: ...)" voi ID element form cua Bricks (cot Form ID o Bricks > Form Submissions).
// Form co nhieu ban sao thi chi hien ID cua ban cho ra ten (phan tu dau).
$vnx_sync_form_title = function ($formName) use ($vnx_sync_form_info, $vnx_sync_form_name) {
  $element_id = $vnx_sync_form_info[(string) $formName]['element_ids'][0] ?? '';
  return $vnx_sync_form_name($formName) . ($element_id !== '' ? ' (ID: ' . $element_id . ')' : '');
};

$vnx_sync_requested_form = isset($_GET['vnx_sync_form']) ? sanitize_text_field(wp_unslash($_GET['vnx_sync_form'])) : '';
$vnx_sync_active_form = isset($vnx_sync_form_slugs[$vnx_sync_requested_form])
  ? $vnx_sync_requested_form
  : array_key_first($vnx_sync_form_slugs);
?>

<div class="vnx-panel">
  <?php View::render('tools/partials/alert', array('message' => $vnx_notice['message'], 'type' => $vnx_notice['type'])); ?>

  <?php if (count((array) $vnx_sync_telegram_sheet_setting) > 1):
    // Whitelist cho Tagify (mode: 'select'): mỗi item mang thêm formSlug để JS biết
    // panel nào cần hiện khi chọn, vì "value" hiển thị là tên form chứ không phải slug.
    $vnx_sync_form_whitelist = array();
    foreach ($vnx_sync_form_slugs as $formName => $formSlug) {
      $vnx_sync_form_whitelist[] = array('value' => $vnx_sync_form_label($formName), 'formSlug' => $formSlug);
    }
    $vnx_sync_form_initial = array(array('value' => $vnx_sync_form_label($vnx_sync_active_form), 'formSlug' => $vnx_sync_form_slugs[$vnx_sync_active_form])); ?>
    <div class="vnx-form-switcher">
      <span class="vnx-form-switcher__icon">
        <i class="fa-solid fa-list-check" aria-hidden="true"></i>
      </span>
      <div class="vnx-form-switcher__body">
        <label class="vnx-form-switcher__label" for="vnx-sync-form-select">Chọn form để cấu hình</label>
        <p class="vnx-form-switcher__desc"><?= count($vnx_sync_form_slugs) ?> form đã được ghi nhận</p>
      </div>
      <div class="vnx-field vnx-form-switcher__select">
        <input type="text" id="vnx-sync-form-select" data-vnx-whitelist="<?= esc_attr(wp_json_encode($vnx_sync_form_whitelist)) ?>"
          value="<?= esc_attr(wp_json_encode($vnx_sync_form_initial)) ?>">
      </div>
    </div>
  <?php endif; ?>

  <?php foreach ($vnx_sync_telegram_sheet_setting as $formName => $value):
    $form_slug = $vnx_sync_form_slugs[$formName]; ?>
    <form id="vnx-sync-form-<?= esc_attr($form_slug) ?>" data-form-slug="<?= esc_attr($form_slug) ?>"
      class="vnx-sync-form vnx-card <?= (string) $formName === (string) $vnx_sync_active_form ? '' : 'hidden' ?>"
      action="<?php echo esc_attr('admin-post.php'); ?>" method="post">
      <?php wp_nonce_field('vnx_sync_discord_sheet_security', 'vnx-sync-discord-sheet-nonce'); ?>
      <input type="hidden" name="action" value="vnx_sync_discord_sheet_submit_Center" />
      <input type="hidden" name="formName" value="<?= esc_attr($formName) ?>">
      <input type="hidden" name="referrer" value="<?= esc_attr($value->referrer) ?>">

      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="normal-case tracking-normal text-[15px] font-semibold text-gray-900"><?= esc_html($vnx_sync_form_title($formName)) ?></span>
      </h3>

      <!-- Discord: Tagify biến các ô này thành tag, JS gắn theo class (tên class còn hậu tố _telegram) nên phải giữ nguyên. -->
      <div class="space-y-4">
      <section class="el_group_form vnx-card" style="background-color:#f9fafb">
        <h4 class="vnx-card__title">
          <i class="fa-brands fa-discord" aria-hidden="true"></i>
          Discord
        </h4>
        <p class="vnx-card__desc">Gửi thông báo tới kênh Discord mỗi khi form được submit.</p>
        <div class="vnx-fields">
          <div class="vnx-field vnx-field--full">
            <label class="vnx-label">Discord Webhook</label>
            <input type="text" name="discordWebhook" class="input_sync_tagify"
              value="<?= esc_attr($value->discordWebhook ?? '') ?>">
            <p class="vnx-help">Nhấn Enter hoặc dấu phẩy sau mỗi webhook để thêm tag (tối đa 5) - tin sẽ gửi đến tất cả.</p>
          </div>

          <div class="vnx-field vnx-field--full">
            <label class="vnx-label">Title</label>
            <input type="text" name="title" class="input_sync_elements_title_telegram"
              onchange="vnxSyncCheckPair(event)" value="<?= esc_attr($value->title) ?>">
          </div>

          <div class="vnx-field vnx-field--full">
            <label class="vnx-label">Content</label>
            <input type="text" name="content" class="input_sync_elements_content_telegram"
              onchange="vnxSyncCheckPair(event)" value="<?= esc_attr($value->content) ?>">
            <p class="vnx-help">Input dạng checkbox thì thêm hậu tố <code>-checkbox</code>.</p>
          </div>
        </div>
        <p class="vnx-error error_sync_input"></p>
      </section>

      <section class="el_group_form vnx-card" style="background-color:#f9fafb">
        <h4 class="vnx-card__title">
          <i class="fa-solid fa-table" aria-hidden="true"></i>
          Google Sheet
        </h4>
        <p class="vnx-card__desc">Ghi mỗi lượt submit thành một dòng mới trong Google Sheet.</p>
        <div class="vnx-fields">
          <div class="vnx-field">
            <label class="vnx-label">ID Sheet</label>
            <input type="text" name="sheetID" class="input_sync_tagify" onchange="vnxSyncCheckPair(event)"
              value="<?= esc_attr($value->sheetID) ?>">
          </div>

          <div class="vnx-field">
            <label class="vnx-label">Page name</label>
            <input type="text" name="pageName" class="input_sync_tagify" onchange="vnxSyncCheckPair(event)"
              value="<?= esc_attr($value->pageName) ?>">
          </div>

          <div class="vnx-field vnx-field--full">
            <label class="vnx-label">Data</label>
            <input type="text" name="dataSheet" class="input_sync_elements_data_GGSheet"
              onchange="vnxSyncCheckPair(event)" value="<?= esc_attr($value->dataSheet) ?>">
            <p class="vnx-help">Input dạng checkbox thì thêm hậu tố <code>-checkbox</code>.</p>
          </div>
        </div>
        <p class="vnx-error error_sync_input"></p>
      </section>
      </div>

      <div class="pt-4 mt-5 border-t border-gray-100 vnx-actions">
        <button type="submit" class="vnx-btn vnx-btn--ghost" name="submit_vnx_sync" value="delete" formnovalidate
          data-vnx-confirm="<?= esc_attr('Xoá cấu hình của form "' . $formName . '"? Nếu form này vẫn còn trên site, lần gửi tiếp theo nó sẽ xuất hiện lại với cấu hình trống.') ?>"
          data-vnx-confirm-title="Xoá form" data-vnx-confirm-ok="Xoá" data-vnx-confirm-tone="danger">
          <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
          Xoá form
        </button>
        <button type="submit" class="vnx-btn vnx-btn--primary vnx-button-submit" name="submit_vnx_sync" value="save"
          data-action="vnx_sync_discord_sheet_submit_Center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Cập nhật
        </button>
      </div>
    </form>
  <?php endforeach; ?>
</div>

<script>
  function vnxSyncCheckPair(event) {
    var group = event.target.closest('.el_group_form');
    if (!group) return;

    // Chi Sheet ID <-> Page name moi ghep theo thu tu (sheet thu i ghi vao page thu i).
    // Nhom Discord khong co cap nao: 1 Title dung chung cho moi webhook.
    var sheetInput = group.querySelector('input[name="sheetID"]');
    var pageInput = group.querySelector('input[name="pageName"]');
    var error = group.querySelector('.error_sync_input');
    if (!error || !sheetInput || !pageInput) return;

    var count = function(input) {
      return input.value.split('value').length - 1;
    };

    error.textContent = count(sheetInput) === count(pageInput) ? '' : 'Số ID Sheet và Page name chưa khớp!';
  }
</script>