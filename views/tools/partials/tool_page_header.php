<?php

/**
 * Header dùng chung cho trang Vietnix › Tool: logo + trạng thái + link Settings.
 * Dùng ở cả tools_layout.php (có tool đang bật) và tools_empty.php (chưa bật tool nào),
 * để hai trạng thái không nhìn như hai trang khác nhau.
 *
 * Gọi: View::render('tools/partials/tool_page_header', ['count' => $tools_total]);
 * $count: số tool đang bật. Bỏ trống (null) để hiện thông báo "chưa có tool nào".
 */

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use HelperCenter\View;

$count = isset($data->count) ? (int) $data->count : null;
?>
<div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-gray-200">
  <div class="flex items-center gap-3">
    <img class="w-auto h-8" src="<?= esc_url(VNX_PLUGIN_URL_CENTER . 'assets/logo.png') ?>" alt="Vietnix">
    <?php if ($count === null): ?>
      <p class="m-0 text-sm font-medium text-gray-500">Chưa có tool nào đang bật</p>
    <?php else: ?>
      <div>
        <p class="m-0 text-sm font-medium text-green-600"><?= $count ?> tool đang bật</p>
      </div>
    <?php endif; ?>
  </div>
  <div class="flex items-center gap-2 ml-auto">
    <a class="inline-flex items-center gap-2 rounded border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 no-underline hover:border-[#38A7FF] hover:text-[#38A7FF]"
      href="<?= esc_url(admin_url('admin.php?page=vnx-setting')) ?>">
      <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" aria-hidden="true">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
          d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
          d="m16.1 12.2.9.5a.9.9 0 0 1 .3 1.3l-.9 1.5a.9.9 0 0 1-1.2.4l-1-.5a6.4 6.4 0 0 1-1.7 1v1a.9.9 0 0 1-1 .9h-1.7a.9.9 0 0 1-1-.9v-1a6.4 6.4 0 0 1-1.6-1l-1 .5a.9.9 0 0 1-1.2-.4l-.9-1.5a.9.9 0 0 1 .3-1.3l.9-.5a6.5 6.5 0 0 1 0-2l-.9-.4a.9.9 0 0 1-.3-1.3l.9-1.5a.9.9 0 0 1 1.2-.4l1 .5a6.4 6.4 0 0 1 1.6-1v-1a.9.9 0 0 1 1-.9h1.8a.9.9 0 0 1 .9.9v1a6.4 6.4 0 0 1 1.7 1l1-.5a.9.9 0 0 1 1.2.4l.9 1.5a.9.9 0 0 1-.3 1.3l-.9.4a6.5 6.5 0 0 1 0 2Z" />
      </svg>
      Settings
    </a>
    <?php View::render('tools/partials/help_drawer', ['context' => 'tools']); ?>
  </div>
</div>
<?php // Danh diem cho WP biet dat admin notice (vd. "Action Scheduler...") ngay
// duoi header nay, thay vi tu mo h1/h2 dau tien no vo tinh vo trong DOM (co
// the la tieu de an cua mot tool khac). Xem wp-admin/js/common.js dong ~1084. ?>
<hr class="wp-header-end">