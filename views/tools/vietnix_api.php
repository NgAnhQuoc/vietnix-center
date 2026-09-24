<?php

use HelperCenter\VnxApiKeyCrypt;
use HelperCenter\View;

$api_allow_ip = get_option('vnx_api_allow_ip');

// Đọc số ký tự của API key (không decrypt — bảo mật)
$api_key_count = class_exists(VnxApiKeyCrypt::class) ? VnxApiKeyCrypt::stored_length() : 0;
$api_key_placeholder = $api_key_count > 0
  ? str_repeat('*', min($api_key_count, 40))
  : 'Nhập API Key mới...';

$vnx_notice = vnx_tool_notice_consume_Center('vietnix-api');
?>
<form class="vnx-panel" action="<?php echo esc_attr('admin-post.php'); ?>" method="post">
  <?php View::render('tools/partials/alert', array('message' => $vnx_notice['message'], 'type' => $vnx_notice['type'])); ?>

  <input type="hidden" name="action" value="vnx_api_submit_Center" />
  <input type="hidden" name="vnx_api_submit" value="save" />
  <?php wp_nonce_field('vnx_api_security', 'vnx-api-nonce'); ?>

  <div class="vnx-card">
    <h3 class="vnx-card__title">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" aria-hidden="true">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
          d="M12.9 4.6 15.4 7m-8.3 8.3-2.2 2.2m3-9.4-2.1 2.1a3 3 0 0 0 0 4.3l1.2 1.2a3 3 0 0 0 4.3 0l2.1-2.1m0-6.3 2.1-2.1a3 3 0 0 1 4.3 0l-4.3-4.3" />
      </svg>
      Thông tin kết nối
    </h3>

    <div class="vnx-fields">
      <!-- full: Allow IP hay chua nhieu IP, de rieng mot hang cho rong thoai mai
           thay vi chia doi voi API Key. -->
      <div class="vnx-field vnx-field--full">
        <label class="vnx-label" for="allow_api">Allow IP</label>
        <input id="allow_api" name="allow_api" type="text" placeholder="Gõ IP rồi nhấn Enter"
          value="<?php echo esc_attr(is_scalar($api_allow_ip) ? $api_allow_ip : ''); ?>">
        <p class="vnx-help">IP được phép gọi API. Dán được cả danh sách ngăn bằng dấu phẩy, khoảng trắng hoặc xuống
          dòng. Để trống là chặn mọi IP (API không nhận request nào).</p>
      </div>

      <div class="vnx-field vnx-field--full">
        <label class="vnx-label" for="api_key">API Key</label>
        <!-- relative: chứa nút hiện/ẩn đặt tuyệt đối bên phải ô nhập -->
        <div class="relative">
          <input id="api_key" name="api_key" type="password" value="" autocomplete="new-password"
            class="vnx-input--with-action"
            placeholder="<?php echo esc_attr($api_key_placeholder); ?>"
            oninput="document.getElementById('api_key_toggle').hidden = !this.value">
          <button id="api_key_toggle" type="button" hidden title="Hiện/Ẩn API Key"
            aria-label="Hiện hoặc ẩn API Key"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 bg-transparent border-0 cursor-pointer hover:text-gray-700"
            onclick="(function(btn){var i=document.getElementById('api_key');var show=i.type==='password';i.type=show?'text':'password';var svgs=btn.querySelectorAll('svg');if(show){svgs[0].setAttribute('hidden','');svgs[1].removeAttribute('hidden');}else{svgs[0].removeAttribute('hidden');svgs[1].setAttribute('hidden','');}})(this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"
              aria-hidden="true">
              <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z" />
              <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"
              hidden aria-hidden="true">
              <path
                d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z" />
              <path
                d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z" />
              <path
                d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z" />
            </svg>
          </button>
        </div>
        <?php if ($api_key_count === 0): ?>
          <p class="vnx-help vnx-help--warning">Chưa cấu hình API Key (tối thiểu 32 ký tự).</p>
        <?php else: ?>
          <p class="vnx-help">Đã lưu một API Key (<?php echo (int) $api_key_count; ?> ký tự). Để trống nếu không đổi.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="vnx-card__footer">
      <button class="vnx-btn vnx-btn--primary vnx-button-submit" type="submit">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Lưu cài đặt
      </button>
    </div>
  </form>
</div>
