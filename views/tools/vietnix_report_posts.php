<?php

use HelperCenter\View;
use VietnixRequiesCenter\VietnixReportPosts;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

$vnx_report_posts = new VietnixReportPosts();
$dataOption = $vnx_report_posts->getSetting();
?>

<script>
  window.vnxReportPostsData = <?php echo $dataOption; ?>;
  window.vnxReportPostsNonce = "<?php echo wp_create_nonce('vietnix_report_posts_nonce'); ?>";
</script>

<!-- id la diem mount cua Vue (tools/inc/js/vietnix_report_posts.js), khong doi. -->
<div id="vietnix-report-posts-center" class="vnx-panel">

  <?php View::render('tools/partials/alert_vue'); ?>

  <form id="settings-form" class="vnx-panel" @submit.prevent>
    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
        </svg>
        Kênh nhận thông báo
      </h3>

      <div class="vnx-field vnx-field--full">
        <label class="vnx-label" for="linkhooks">Discord webhook URL <span class="vnx-label__req">*</span></label>
        <input type="url" id="linkhooks" v-model="dataOption.linkhooks" required
          placeholder="https://discord.com/api/webhooks/...">
        <p class="vnx-help">Báo cáo ngày và báo cáo tuần đều gửi về webhook này.</p>
      </div>
    </div>

    <div class="vnx-grid vnx-grid--2">
      <div class="vnx-card">
        <h3 class="vnx-card__title">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
          </svg>
          Báo cáo ngày
        </h3>

        <div class="vnx-fields">
          <div class="vnx-field vnx-field--full">
            <label class="vnx-label" for="daily_title">Tiêu đề <span class="vnx-label__req">*</span></label>
            <input type="text" id="daily_title" name="daily_title" v-model="dataOption.daily.title" required
              placeholder="Báo cáo bài viết hôm nay">
          </div>

          <div class="vnx-field vnx-field--xs">
            <label class="vnx-label" for="daily_time">Giờ gửi <span class="vnx-label__req">*</span></label>
            <input type="time" id="daily_time" name="daily_time" v-model="dataOption.daily.time" required>
          </div>
        </div>
      </div>

      <div class="vnx-card">
        <h3 class="vnx-card__title">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" />
          </svg>
          Báo cáo tuần
        </h3>

        <div class="vnx-fields">
          <div class="vnx-field vnx-field--full">
            <label class="vnx-label" for="weekly_title">Tiêu đề <span class="vnx-label__req">*</span></label>
            <input type="text" id="weekly_title" name="weekly_title" v-model="dataOption.weekly.title" required
              placeholder="Báo cáo bài viết tuần này">
          </div>

          <div class="vnx-field vnx-field--xs">
            <label class="vnx-label" for="weekly_time">Giờ gửi <span class="vnx-label__req">*</span></label>
            <input type="time" id="weekly_time" name="weekly_time" v-model="dataOption.weekly.time" required>
          </div>
        </div>

        <div class="mt-4 vnx-field vnx-field--full">
          <span class="vnx-label">Ngày gửi trong tuần</span>
          <div class="grid grid-cols-2 gap-x-4 gap-y-2 sm:grid-cols-3">
            <label class="vnx-check" v-for="item in optionDay" :key="item.value">
              <input type="radio" name="weekly_day" :value="item.value" v-model="dataOption.weekly.day">
              <span>{{ item.label }}</span>
            </label>
          </div>
        </div>
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
    </div>
  </form>
</div>
