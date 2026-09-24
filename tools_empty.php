<?php

/**
 * Trang Vietnix › Tool khi chưa bật tool nào. Dùng chung khung với tools_layout.php
 * (viền, bo góc, header có logo) để hai trạng thái không nhìn như hai trang khác nhau.
 */

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
?>
<div id="vnx-tools" class="w-full mt-5 mr-5 bg-white border border-gray-200 rounded shadow overflow-clip">

  <?php View::render('tools/partials/tool_page_header'); ?>

  <div class="p-6 vnx-tool-panels">
    <div class="vnx-empty">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
      <h2 class="m-0 mb-1 text-base font-semibold text-gray-900">Chưa có tool nào được bật</h2>
      <p class="max-w-md">
        Bật tool ở tab <strong>Tool</strong> trong trang Settings. Những tool có trang cấu hình riêng sẽ hiện ở đây.
      </p>
      <a class="mt-4 vnx-btn vnx-btn--primary" href="<?= esc_url(admin_url('admin.php?page=vnx-setting')) ?>">
        Mở trang Settings
      </a>
    </div>
  </div>
</div>
