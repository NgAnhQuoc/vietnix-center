<?php

use HelperCenter\View;

$vnx_api_price_table_urls = get_option('vnx_api_price_table_urls', '');
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vnx_api_price_table_nonce'])) {
  if (wp_verify_nonce($_POST['vnx_api_price_table_nonce'], 'vnx_api_price_table_save')) {
    $urls = isset($_POST['vnx_api_price_table_urls']) ? sanitize_textarea_field($_POST['vnx_api_price_table_urls']) : '';
    update_option('vnx_api_price_table_urls', $urls);
    $vnx_api_price_table_urls = $urls;
    $message = 'Lưu danh sách URL thành công!';
    $message_type = 'success';
  } else {
    $message = 'Lỗi bảo mật! Vui lòng thử lại.';
    $message_type = 'error';
  }
}
?>

<div class="vnx-panel">
  <?php View::render('tools/partials/alert', array('message' => $message, 'type' => $message_type)); ?>

  <form method="post" class="vnx-card">
    <?php wp_nonce_field('vnx_api_price_table_save', 'vnx_api_price_table_nonce'); ?>

    <h3 class="vnx-card__title">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
      </svg>
      Danh sách URL nguồn
    </h3>

    <div class="vnx-field vnx-field--full">
      <label class="vnx-label" for="vnx_api_price_table_urls">List URL (mỗi URL một dòng)</label>
      <textarea id="vnx_api_price_table_urls" name="vnx_api_price_table_urls" rows="10"
        placeholder="https://vietnix.vn/wp-json/vnx_api/v1/thong-tin-khuyen-mai"><?php echo esc_textarea($vnx_api_price_table_urls); ?></textarea>
      <p class="vnx-help">Mỗi URL trên một dòng, dòng trống sẽ được bỏ qua.</p>
      <p class="vnx-help">Dạng endpoint: <code>{{domain}}/wp-json/vnx_api/v1/thong-tin-khuyen-mai</code></p>
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
