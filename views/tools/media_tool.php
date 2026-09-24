<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

wp_register_script('media_tool_func-center', VNX_PLUGIN_URL_CENTER . 'tools/inc/js/media_tool.js');
wp_enqueue_script('media_tool_func-center');

// Count total attachments across all statuses
global $wpdb;
$total_attachments = $wpdb->get_var($wpdb->prepare(
  "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s",
  'attachment'
));

wp_localize_script('media_tool_func-center', 'media_tool_array', array(
  'ajax_url' => admin_url('admin-ajax.php'),
  'total_media' => $total_attachments,
  'ajax_nonce' => wp_create_nonce('media-tool-ajax-nonce')
));
?>
<div class="vnx-panel">

  <div class="vnx-alert vnx-alert--warning">
    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
      <path fill-rule="evenodd" clip-rule="evenodd"
        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
    </svg>
    <p>
      Khi tool đang bật, mọi file upload mới sẽ tự nhận slug là chuỗi ngẫu nhiên 32 ký tự.
      Hai nút bên dưới đổi slug của <strong>toàn bộ</strong> file đã có và không thể hoàn tác.
    </p>
  </div>

  <div class="vnx-card">
    <h3 class="vnx-card__title">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      Thư viện media
    </h3>

    <p class="text-[13px] text-gray-700">
      Tổng số file đính kèm: <strong class="text-gray-900"><?= (int) $total_attachments ?></strong>
    </p>

    <!-- media_tool.js ghi tiến độ vào <p> và bật/tắt spinner bằng class .hidden.
         Dùng spinner SVG chung thay cho GIF cũ: GIF 50px chỉ có hình xoay 12px ở giữa nên thu
         về 20px chỉ còn một chấm, lại phải tải từ vietnix.vn. -->
    <div class="mt-3 vnx-media-tool-message text-[13px] text-emerald-700">
      <?php View::render('tools/partials/spinner', ['show' => '', 'class' => 'hidden']); ?>
      <p class="m-0"></p>
    </div>

    <div class="vnx-card__footer vnx_media_tool_bnt">
      <button type="button" class="vnx-btn vnx-btn--primary tool_media_btn" value="regenerate_attachment_slugs_center"
        data-vnx-confirm-title="Đổi slug toàn bộ media"
        data-vnx-confirm="Slug của mọi file đính kèm sẽ đổi sang chuỗi ngẫu nhiên 32 ký tự. Link ảnh cũ đang được dùng ở nơi khác sẽ hỏng, và không hoàn tác được."
        data-vnx-confirm-ok="Đổi toàn bộ" data-vnx-confirm-tone="danger">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Đổi sang chuỗi ngẫu nhiên
      </button>

      <button type="button" class="vnx-btn vnx-btn--ghost tool_media_btn" value="reset_attachment_slugs_to_default_center"
        data-vnx-confirm-title="Trả slug về mặc định"
        data-vnx-confirm="Slug của mọi file đính kèm sẽ được tính lại theo tên file gốc. Link ảnh hiện tại sẽ đổi theo."
        data-vnx-confirm-ok="Trả về mặc định" data-vnx-confirm-tone="warning">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
        </svg>
        Trả về slug mặc định
      </button>
    </div>
  </div>
</div>
