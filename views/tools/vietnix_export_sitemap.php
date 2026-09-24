<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
?>
<!-- id la diem mount cua Vue (tools/inc/js/vietnix-export-sitemap.js), khong doi. -->
<div id="vietnix-export-sitemap-center" class="vnx-panel">

  <?php View::render('tools/partials/alert_vue'); ?>

  <form id="export-sitemap-form" class="vnx-panel" @submit.prevent>
    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z" />
          <path d="M7 12h2v5H7zm4-3h2v8h-2zm4-3h2v11h-2z" />
        </svg>
        Cấu hình Google Sheet
      </h3>

      <div class="vnx-fields">
        <!-- URL Google Sheet rat dai, cho han mot hang rieng. -->
        <div class="vnx-field vnx-field--full">
          <label class="vnx-label" for="sheet_url">Link Google Sheet <span class="vnx-label__req">*</span></label>
          <input type="url" id="sheet_url" v-model="dataOption.sheet_url" required
            placeholder="https://docs.google.com/spreadsheets/d/...">
        </div>

        <div class="vnx-field vnx-field--md">
          <label class="vnx-label" for="sheet_tab">Tên tab sheet <span class="vnx-label__req">*</span></label>
          <input type="text" id="sheet_tab" v-model="dataOption.sheet_tab" required placeholder="Sheet1">
        </div>

        <div class="vnx-field vnx-field--md">
          <label class="vnx-label" for="site_domain">Site domain <span class="vnx-label__req">*</span></label>
          <input type="text" id="site_domain" v-model="dataOption.site_domain" required placeholder="vietnix.vn">
          <p class="vnx-help">Domain production. Hệ thống so sánh với site hiện tại, không khớp thì huỷ export để
            staging không đẩy nhầm dữ liệu.</p>
        </div>
      </div>
    </div>

    <div class="vnx-alert vnx-alert--info">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path
          d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
      </svg>
      <p>Cron chạy tự động 30 phút một lần. Nút <strong>Export ngay</strong> chỉ dùng khi cần đẩy dữ liệu gấp.</p>
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

      <button type="button" class="vnx-btn vnx-btn--success" @click="exportNow" :disabled="exporting">
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

    <div v-if="exporting" class="vnx-alert vnx-alert--warning">
      <?php View::render('tools/partials/spinner', ['show' => '']); ?>
      <p>Đang export dữ liệu lên Google Sheet, vui lòng không đóng tab.</p>
    </div>
  </form>
</div>
