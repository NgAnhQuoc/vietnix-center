<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
?>
<!-- id la diem mount cua Vue (tools/inc/js/vnx-internal-link-ldp.js), khong doi. -->
<div id="vnx-internal-link-ldp-center" class="vnx-panel">

  <?php View::render('tools/partials/alert_vue'); ?>

  <form id="vnx-illdp-form" class="vnx-panel" @submit.prevent>
    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z" />
          <path d="M7 12h2v5H7zm4-3h2v8h-2zm4-3h2v11h-2z" />
        </svg>
        Cấu hình Google Sheet
      </h3>

      <div class="vnx-fields">
        <div class="vnx-field vnx-field--full">
          <label class="vnx-label" for="illdp_sheet_url">Link Google Sheet <span class="vnx-label__req">*</span></label>
          <input type="url" id="illdp_sheet_url" v-model="settings.sheet_url" required
            placeholder="https://docs.google.com/spreadsheets/d/...">
        </div>

        <div class="vnx-field vnx-field--md">
          <label class="vnx-label" for="illdp_sheet_tab">Tên tab sheet <span class="vnx-label__req">*</span></label>
          <input type="text" id="illdp_sheet_tab" v-model="settings.sheet_tab" required placeholder="Internal link 12">
        </div>

        <div class="vnx-field vnx-field--md">
          <label class="vnx-label" for="illdp_site_domain">Site domain <span class="vnx-label__req">*</span></label>
          <input type="text" id="illdp_site_domain" v-model="settings.site_domain" required placeholder="vietnix.vn">
          <p class="vnx-help">Domain production. Không khớp với site hiện tại thì export bị huỷ.</p>
        </div>
      </div>
    </div>

    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path
            d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z" />
        </svg>
        Danh sách LDP URL
      </h3>
      <p class="vnx-card__desc">Mỗi dòng một URL. Hệ thống quét toàn bộ bài viết để tìm link trỏ đến các URL này.</p>

      <div class="vnx-field vnx-field--full">
        <textarea id="illdp_ldp_urls" v-model="settings.ldp_urls" rows="10"
          placeholder="https://vietnix.vn/web-hosting/&#10;https://vietnix.vn/vps/"></textarea>
      </div>
    </div>

    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 10h18M3 6h18M3 14h18M3 18h18" />
        </svg>
        Cấu trúc dữ liệu xuất ra
      </h3>

      <div class="vnx-table-wrap">
        <table class="vnx-table">
          <thead>
            <tr>
              <th>Cột A — URL</th>
              <th>Cột B — Link</th>
              <th>Cột C — Anchor text</th>
              <th>Cột D — HTML type</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>URL bài viết chứa link</td>
              <td>Link LDP được tìm thấy</td>
              <td>Nội dung anchor text</td>
              <td><code>&lt;a&gt;</code></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="vnx-actions">
      <button type="button" class="vnx-btn vnx-btn--primary" @click="saveSettings" :disabled="loading">
        <?php View::render('tools/partials/spinner', ['show' => 'loading']); ?>
        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span v-if="loading">Đang lưu...</span>
        <span v-else>Lưu cài đặt</span>
      </button>

      <button type="button" class="vnx-btn vnx-btn--success" @click="exportNow" :disabled="exporting || resumeAvailable">
        <?php View::render('tools/partials/spinner', ['show' => 'exporting']); ?>
        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
        </svg>
        <span v-if="exporting">Đang export...</span>
        <span v-else>Export ngay</span>
      </button>
    </div>

    <div v-if="resumeAvailable" class="vnx-alert vnx-alert--warning">
      <p>
        Phát hiện lượt export trước chưa hoàn tất (đang ở trang {{ resumeInfo.page }}/{{ resumeInfo.total_pages || '?' }},
        đã ghi {{ resumeInfo.count || 0 }} link). Bạn muốn tiếp tục hay bắt đầu lại từ đầu?
      </p>
      <div class="vnx-actions">
        <button type="button" class="vnx-btn vnx-btn--primary" @click="resumeExport">Tiếp tục export</button>
        <button type="button" class="vnx-btn vnx-btn--danger" @click="discardAndRestart">Bắt đầu lại (xóa Sheet)</button>
      </div>
    </div>

    <div v-if="exporting" class="vnx-alert vnx-alert--warning">
      <?php View::render('tools/partials/spinner', ['show' => '']); ?>
      <p>{{ exportProgress || 'Đang quét bài viết và tìm internal link đến LDP...' }}</p>
    </div>
  </form>
</div>
