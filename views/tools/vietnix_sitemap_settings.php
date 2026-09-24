<?php

use HelperCenter\View;

if (!class_exists('VNX_Sitemap_Settings_Center')) {
  require_once __DIR__ . '/../../extentions/vnx-sitemap/class-vnx-sitemap-settings.php';
}

$settings_handler = VNX_Sitemap_Settings_Center::instance();
$settings = $settings_handler->get_settings();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vnx_sitemap_nonce'])) {
  if (wp_verify_nonce($_POST['vnx_sitemap_nonce'], 'vnx_sitemap_settings')) {

    $action = $_POST['action'] ?? '';

    if ($action === 'save_settings') {
      $result = $settings_handler->save_from_post($_POST);
      $message = $result['message'];
      $message_type = $result['success'] ? 'success' : 'error';
      $settings = $result['settings'] ?? $settings;
    }

    if ($action === 'clear_cache') {
      $result = $settings_handler->clear_and_rebuild_cache();
      $message = $result['message'];
      $message_type = $result['success'] ? 'success' : 'error';
    }

  } else {
    $message = 'Lỗi bảo mật! Vui lòng thử lại.';
    $message_type = 'error';
  }
}
?>

<div class="vnx-panel">
  <?php View::render('tools/partials/alert', array('message' => $message, 'type' => $message_type)); ?>

  <form method="post" class="vnx-panel">
    <?php wp_nonce_field('vnx_sitemap_settings', 'vnx_sitemap_nonce'); ?>
    <input type="hidden" name="action" value="save_settings">

    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Cấu hình chung
      </h3>

      <div class="vnx-field vnx-field--lg">
        <label class="vnx-label" for="sitemap_accept_domain">Domain được chấp nhận (mỗi domain một dòng)</label>
        <textarea id="sitemap_accept_domain" name="sitemap_accept_domain" rows="4"
          placeholder="cdn.example.com&#10;images.example.com"><?php echo esc_textarea($settings_handler->get_accept_domains_string()); ?></textarea>
        <p class="vnx-help">Domain chính của site luôn được chấp nhận. Chỉ thêm domain CDN hoặc domain phụ.</p>
      </div>

      <!-- Ba o duoi day deu chi chua vai chu so, khong o nao can rong hon o nao. -->
      <div class="mt-4 vnx-fields">
        <div class="vnx-field vnx-field--xs">
          <label class="vnx-label" for="ttl_cache">TTL cache (giây)</label>
          <input type="number" id="ttl_cache" name="ttl_cache" min="60" max="86400" required
            value="<?php echo esc_attr($settings['ttl_cache']); ?>">
          <p class="vnx-help">60 – 86400. Mặc định 1800 (30 phút).</p>
        </div>

        <div class="vnx-field vnx-field--xs">
          <label class="vnx-label" for="posts_per_sitemap">Số URL mỗi sitemap</label>
          <input type="number" id="posts_per_sitemap" name="posts_per_sitemap" min="10" max="1000" required
            value="<?php echo esc_attr($settings['posts_per_sitemap']); ?>">
          <p class="vnx-help">10 – 1000. Mặc định 200.</p>
        </div>

        <div class="vnx-field vnx-field--xs">
          <label class="vnx-label" for="images_per_post">Số ảnh tối đa mỗi URL</label>
          <input type="number" id="images_per_post" name="images_per_post" min="0" max="1000" required
            value="<?php echo esc_attr($settings['images_per_post']); ?>">
          <p class="vnx-help">0 – 1000. Đặt 0 để tắt image sitemap.</p>
        </div>
      </div>
    </div>

    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 6h18M7 12h10m-6 6h2M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
        </svg>
        Priority theo loại nội dung
      </h3>
      <p class="vnx-card__desc">Độ ưu tiên 0.0 – 1.0 cho từng loại nội dung trong sitemap.</p>

      <div class="vnx-alert vnx-alert--info mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
          <path fill-rule="evenodd" clip-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" />
        </svg>
        <p>
          Hỗ trợ pattern: <code>post</code> = tất cả sitemap post &middot; <code>post-1</code> = chỉ trang 1 &middot;
          <code>post-*</code> = các trang 2, 3, 4…
        </p>
      </div>

      <!-- Repeater: JS chỉ thêm/bớt .priority-row, id giữ nguyên để addPriorityRow() còn tìm được. -->
      <div id="priority-rows" class="flex flex-col gap-2">
        <?php foreach ($settings['base_priorities'] as $type => $priority): ?>
          <div class="priority-row vnx-fields vnx-fields--tight">
            <div class="vnx-field vnx-field--md">
              <input type="text" name="priority_type[]" placeholder="post, post-1, post-*" class="vnx-input--sm"
                value="<?php echo esc_attr($type); ?>">
            </div>
            <div class="vnx-field vnx-field--num">
              <input type="number" name="priority_value[]" step="0.1" min="0" max="1" placeholder="0.0 - 1.0"
                class="vnx-input--sm" value="<?php echo esc_attr($priority); ?>">
            </div>
            <button type="button" class="vnx-btn vnx-btn--remove" aria-label="Xoá dòng"
              onclick="this.parentElement.remove()">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="button" class="mt-3 vnx-btn vnx-btn--ghost vnx-btn--sm" onclick="vnxAddPriorityRow()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Thêm post type
      </button>
    </div>

    <div class="vnx-actions">
      <button type="submit" class="vnx-btn vnx-btn--primary vnx-button-submit">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Lưu cài đặt
      </button>
    </div>
  </form>

  <!-- Form riêng cho Clear Cache: cùng nonce nhưng khác action, không nằm chung form Lưu. -->
  <form method="post" class="vnx-card">
    <?php wp_nonce_field('vnx_sitemap_settings', 'vnx_sitemap_nonce'); ?>
    <input type="hidden" name="action" value="clear_cache">

    <h3 class="vnx-card__title">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      Cache
    </h3>
    <p class="vnx-card__desc">Xoá toàn bộ transient <code>vnx_sitemap</code> rồi dựng lại từ đầu.</p>

    <button type="submit" class="vnx-btn vnx-btn--ghost"
      data-vnx-confirm-title="Xoá và dựng lại cache"
      data-vnx-confirm="Toàn bộ transient vnx_sitemap sẽ bị xoá rồi dựng lại từ đầu. Trong lúc dựng lại, sitemap có thể trả về chậm hơn bình thường."
      data-vnx-confirm-ok="Xoá &amp; dựng lại" data-vnx-confirm-tone="warning">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      Clear &amp; rebuild cache
    </button>
  </form>
</div>

<script>
  // Đặt trên window vì nút "Thêm post type" gọi qua thuộc tính onclick inline.
  function vnxAddPriorityRow() {
    var container = document.getElementById('priority-rows');
    if (!container) return;

    var row = document.createElement('div');
    row.className = 'priority-row vnx-fields vnx-fields--tight';
    row.innerHTML = [
      '<div class="vnx-field vnx-field--md"><input type="text" name="priority_type[]" value="" placeholder="post, post-1, post-*" class="vnx-input--sm"></div>',
      '<div class="vnx-field vnx-field--num"><input type="number" name="priority_value[]" value="0.5" step="0.1" min="0" max="1" placeholder="0.0 - 1.0" class="vnx-input--sm"></div>',
      '<button type="button" class="vnx-btn vnx-btn--remove" aria-label="Xoá dòng" onclick="this.parentElement.remove()">',
      '  <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">',
      '    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />',
      '  </svg>',
      '</button>'
    ].join('');

    container.appendChild(row);
  }
</script>
