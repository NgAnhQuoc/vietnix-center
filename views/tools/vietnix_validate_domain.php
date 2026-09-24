<?php

use HelperCenter\View;

if (!class_exists('VNX_Validate_Domain_Center')) {
  require_once __DIR__ . '/../../tools/vietnix-validate-domain.php';
}

$validate_domain_handler = VNX_Validate_Domain_Center::instance();

$message = '';
$message_type = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vnx_validate_domain_nonce'])) {
  if (wp_verify_nonce($_POST['vnx_validate_domain_nonce'], 'vnx_validate_domain_action')) {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'reset') {
      $result = $validate_domain_handler->reset_to_defaults();
    } else {
      $result = $validate_domain_handler->save_from_post($_POST);
    }

    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
  } else {
    $message = 'Lỗi bảo mật! Vui lòng thử lại.';
    $message_type = 'error';
  }
}

// Get current data (after save/reset if applicable)
$black_list = $validate_domain_handler->get_black_list();
$blocked_prefixes = $validate_domain_handler->get_blocked_prefixes();
?>

<div class="vnx-panel">
  <?php View::render('tools/partials/alert', array('message' => $message, 'type' => $message_type)); ?>

  <form method="post" class="vnx-card">
    <?php wp_nonce_field('vnx_validate_domain_action', 'vnx_validate_domain_nonce'); ?>
    <input type="hidden" name="action" value="save">

    <h3 class="vnx-card__title">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
      </svg>
      Danh sách chặn
    </h3>

    <!-- Hai o cung vai tro (danh sach keyword) nen chia deu hang. -->
    <div class="vnx-fields">
      <div class="vnx-field">
        <label class="vnx-label" for="domain_list">Chặn tên miền có chứa keyword</label>
        <textarea id="domain_list" name="domain_list" rows="6"
          placeholder="jx, clmm, cltx, kubet"><?php echo esc_textarea(implode(", ", $black_list)); ?></textarea>
        <p class="vnx-help">Các keyword cách nhau bằng dấu phẩy. Tên miền chứa bất kỳ keyword nào sẽ bị loại khỏi kết
          quả tìm kiếm.</p>
      </div>

      <div class="vnx-field">
        <label class="vnx-label" for="blocked_prefixes">Chặn tên miền có tiền tố</label>
        <textarea id="blocked_prefixes" name="blocked_prefixes" rows="6"
          placeholder="nso, nro, bet, gun"><?php echo esc_textarea(implode(", ", $blocked_prefixes)); ?></textarea>
        <p class="vnx-help">Chỉ so khớp phần đầu của tên miền, cách nhau bằng dấu phẩy.</p>
      </div>
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

  <!-- Form riêng: reset dùng chung nonce nhưng khác action, tách ra cho khỏi lẫn với nút Lưu. -->
  <form method="post" class="vnx-card">
    <?php wp_nonce_field('vnx_validate_domain_action', 'vnx_validate_domain_nonce'); ?>
    <input type="hidden" name="action" value="reset">

    <h3 class="vnx-card__title">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      Khôi phục mặc định
    </h3>
    <p class="vnx-card__desc">Ghi đè cả hai danh sách bên trên bằng bộ giá trị mặc định của plugin.</p>

    <button type="submit" class="vnx-btn vnx-btn--warning"
      data-vnx-confirm-title="Khôi phục mặc định"
      data-vnx-confirm="Cả hai danh sách chặn hiện tại sẽ bị ghi đè bằng bộ giá trị mặc định của plugin. Thao tác này không hoàn tác được."
      data-vnx-confirm-ok="Khôi phục" data-vnx-confirm-tone="danger">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      Reset về mặc định
    </button>
  </form>
</div>
