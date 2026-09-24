<?php

/**
 * Toast dùng chung cho các tool chạy bằng Vue.
 *
 * Yêu cầu app Vue của tool có sẵn hai data property:
 *   alertMessage (string) - nội dung, rỗng thì toast ẩn
 *   alertType    (string) - 'error' thì hiện đỏ, còn lại hiện xanh
 *
 * Gọi: View::render('tools/partials/toast');
 */

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
?>
<div v-cloak v-if="alertMessage" role="alert"
  :class="['vnx-toast', alertType === 'error' ? 'vnx-toast--error' : 'vnx-toast--success']">
  <span class="vnx-toast__icon">
    <svg v-if="alertType !== 'error'" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"
      aria-hidden="true">
      <path
        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
    </svg>
    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
      <path
        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
    </svg>
  </span>
  <div class="vnx-toast__body">{{ alertMessage }}</div>
  <button type="button" class="vnx-toast__close" @click="alertMessage = ''" aria-label="Đóng thông báo">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14" aria-hidden="true">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
    </svg>
  </button>
</div>
