<?php

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use HelperCenter\View;

$cookies_age = get_option(Vietnix_Utm_Tracker_Center__PREFIX . 'cookies_age', array());
$seo_channel = get_option(Vietnix_Utm_Tracker_Center__PREFIX . 'seo_channel', array());
$social_channel = get_option(Vietnix_Utm_Tracker_Center__PREFIX . 'social_channel', array());

// Các nguồn được nhận diện tự động, người dùng không cấu hình được - chỉ mô tả.
$fixed_channels = array(
  'ADS' => 'Nguồn từ quảng cáo Facebook, Google, Zalo, Email... đã được team Marketing gắn UTM tracking.',
  'DIRECT' => 'Người dùng gõ thẳng vietnix.vn trên trình duyệt.',
  'PORTAL' => 'Người dùng gõ thẳng portal.vietnix.vn trên trình duyệt.',
  'REFERRAL' => 'Nguồn đến từ các website giới thiệu.',
);

$vnx_notice = vnx_tool_notice_consume_Center('vietnix-utm-tracker');
?>

<div id="vnx-utm-tracker" class="vnx-panel">

  <?php View::render('tools/partials/alert', array('message' => $vnx_notice['message'], 'type' => $vnx_notice['type'])); ?>

  <div class="vnx-subtabs" role="tablist">
    <button type="button" class="vnx-subtab is-active" role="tab" aria-selected="true"
      data-vnx-utm-tab="vnx-utm-channel">
      <i class="fa-solid fa-diagram-project" aria-hidden="true"></i> Channel
    </button>
    <button type="button" class="vnx-subtab" role="tab" aria-selected="false" data-vnx-utm-tab="vnx-utm-general">
      <i class="fa-solid fa-sliders" aria-hidden="true"></i> General
    </button>
  </div>

  <div id="vnx-utm-channel">
    <form action="<?php echo esc_attr('admin-post.php'); ?>" method="POST" class="vnx-card">
      <?php wp_nonce_field('vnx_utm_tracker_security', 'vnx-utm_tracker-nonce'); ?>
      <input type="hidden" name="action" value="vnx_utm_tracker_submit_Center">
      <input type="hidden" name="form_action" value="update_channel">

      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z" />
        </svg>
        Nguồn tự cấu hình
      </h3>

      <div class="vnx-fields">
        <div class="vnx-field vnx-field--md">
          <label class="vnx-label" for="seo_channel">SEO</label>
          <input type="text" id="seo_channel" name="seo_channel" placeholder="Nhập các nguồn SEO..."
            value="<?php echo esc_attr(is_scalar($seo_channel) ? $seo_channel : ''); ?>">
        </div>

        <div class="vnx-field vnx-field--md">
          <label class="vnx-label" for="social_channel">Social</label>
          <input type="text" id="social_channel" name="social_channel" placeholder="Nhập các nguồn Social..."
            value="<?php echo esc_attr(is_scalar($social_channel) ? $social_channel : ''); ?>">
        </div>
      </div>

      <h3 class="vnx-card__title vnx-card__title--sub">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Nguồn nhận diện tự động
      </h3>

      <div class="flex flex-col gap-2">
        <?php foreach ($fixed_channels as $label => $desc): ?>
          <div class="flex items-start gap-3 px-3 py-2.5 border border-gray-200 rounded bg-gray-50">
            <span class="vnx-badge vnx-badge--amber shrink-0"><?= esc_html($label) ?></span>
            <p class="m-0 text-[13px] leading-5 text-gray-600"><?= esc_html($desc) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="vnx-card__footer">
        <button type="submit" class="vnx-btn vnx-btn--primary vnx-button-submit">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Lưu cài đặt
        </button>
      </div>
    </form>
  </div>

  <div id="vnx-utm-general" class="hidden">
    <form action="<?php echo esc_attr('admin-post.php'); ?>" method="POST" class="vnx-card">
      <?php wp_nonce_field('vnx_utm_tracker_security', 'vnx-utm_tracker-nonce'); ?>
      <input type="hidden" name="action" value="vnx_utm_tracker_submit_Center">
      <input type="hidden" name="form_action" value="update_cookies_age">

      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Thời gian lưu cookie
      </h3>

      <div class="vnx-field vnx-field--xs">
        <label class="vnx-label" for="cookies_age">Cookies age (ngày)</label>
        <input type="number" id="cookies_age" name="cookies_age" min="1" placeholder="VD: 30"
          value="<?php echo esc_attr(is_scalar($cookies_age) ? $cookies_age : ''); ?>">
        <p class="vnx-help">Số ngày giữ lại tham số UTM của lần truy cập đầu tiên.</p>
      </div>

      <div class="vnx-card__footer">
        <button type="submit" class="vnx-btn vnx-btn--primary vnx-button-submit">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Lưu cài đặt
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // Tab con của riêng tool này. Không dùng Preline vì trang Tool đã dùng data-hs-tab
  // cho tab ngoài, lồng thêm một tầng nữa dễ đụng nhau khi Preline dò cùng thuộc tính.
  (function() {
    var root = document.getElementById('vnx-utm-tracker');
    if (!root) return;

    var tabs = root.querySelectorAll('[data-vnx-utm-tab]');

    tabs.forEach(function(tab) {
      tab.addEventListener('click', function() {
        tabs.forEach(function(other) {
          var panel = document.getElementById(other.dataset.vnxUtmTab);
          var active = other === tab;

          other.classList.toggle('is-active', active);
          other.setAttribute('aria-selected', active ? 'true' : 'false');
          if (panel) panel.classList.toggle('hidden', !active);
        });
      });
    });
  })();
</script>