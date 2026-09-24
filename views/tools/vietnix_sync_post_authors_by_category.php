<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

// Lấy tất cả categories (flat list)
$all_categories = get_categories([
  'orderby' => 'name',
  'order' => 'ASC',
  'hide_empty' => false,
  'number' => 0,
]);

// Build danh sách phân cấp (có depth) theo thứ tự cha → con
if (!function_exists('vnx_build_categories_tree_Center')) {
  function vnx_build_categories_tree_Center($all_cats, $parent_id = 0, $depth = 0)
  {
    $result = [];
    foreach ($all_cats as $cat) {
      if ((int) $cat->parent === $parent_id) {
        $result[] = [
          'id' => $cat->term_id,
          'name' => $cat->name,
          'count' => $cat->count,
          'depth' => $depth,
        ];
        // Đệ quy lấy danh mục con
        $children = vnx_build_categories_tree_Center($all_cats, $cat->term_id, $depth + 1);
        $result = array_merge($result, $children);
      }
    }
    return $result;
  }
}
$categories_tree = vnx_build_categories_tree_Center($all_categories);

// Lấy danh sách tất cả users (role editor trở lên)
$users = get_users([
  'orderby' => 'display_name',
  'order' => 'ASC',
  'role__in' => ['administrator', 'editor', 'author', 'contributor'],
  'fields' => ['ID', 'display_name', 'user_login'],
]);
?>

<script>
  window.vnxSyncAuthorsNonce = "<?php echo wp_create_nonce('vnx_sync_post_authors_nonce'); ?>";
  window.vnxSyncAuthorsCategories = <?php echo json_encode(array_values($categories_tree)); ?>;
  window.vnxSyncAuthorsUsers = <?php echo json_encode(array_values(array_map(function ($user) {
    return ['id' => $user->ID, 'name' => $user->display_name, 'login' => $user->user_login];
  }, $users))); ?>;
</script>

<!-- id la diem mount cua Vue (tools/inc/js/vnx-sync-post-authors.js), khong doi. -->
<div id="vnx-sync-post-authors-center" class="vnx-panel">

  <?php View::render('tools/partials/alert_vue'); ?>

  <form id="vnx-sync-authors-form" class="vnx-panel" @submit.prevent>
    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z" />
        </svg>
        Danh mục
      </h3>

      <div class="vnx-field vnx-field--lg">
        <label class="vnx-label" for="vnx-category-select">
          Chọn danh mục bài viết <span class="vnx-label__req">*</span>
        </label>
        <select id="vnx-category-select" v-model="selectedCategory">
          <option value="">-- Chọn danh mục --</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ '— '.repeat(cat.depth) }}{{ cat.name }} ({{ cat.count }} bài)
          </option>
        </select>
        <p class="vnx-help">Mọi bài viết thuộc danh mục này sẽ được cập nhật, kể cả bài đã xuất bản.</p>
      </div>
    </div>

    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
        </svg>
        Tác giả
      </h3>
      <p class="vnx-card__desc">Chỉ những field có chọn user mới được ghi đè. Field để trống sẽ giữ nguyên giá trị cũ.
      </p>

      <div class="vnx-fields">
        <div class="vnx-field vnx-field--md">
          <label class="vnx-label" for="vnx-seo-author">
            <span class="vnx-badge vnx-badge--red">SEO</span>
          </label>
          <select id="vnx-seo-author" v-model="seoAuthor">
            <option value="">-- Bỏ trống (không cập nhật) --</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} (@{{ user.login }})
            </option>
          </select>
        </div>

        <div class="vnx-field vnx-field--md">
          <label class="vnx-label" for="vnx-writer">
            <span class="vnx-badge vnx-badge--green">Writer</span>
          </label>
          <select id="vnx-writer" v-model="writer">
            <option value="">-- Bỏ trống (không cập nhật) --</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} (@{{ user.login }})
            </option>
          </select>
        </div>

        <div class="vnx-field vnx-field--md">
          <label class="vnx-label" for="vnx-technical-author">
            <span class="vnx-badge vnx-badge--blue">Technical</span>
          </label>
          <select id="vnx-technical-author" v-model="technicalAuthor">
            <option value="">-- Bỏ trống (không cập nhật) --</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} (@{{ user.login }})
            </option>
          </select>
        </div>
      </div>
    </div>

    <div class="vnx-actions">
      <button type="button" class="vnx-btn vnx-btn--primary" @click="syncAuthors"
        :disabled="syncing || !selectedCategory">
        <?php View::render('tools/partials/spinner', ['show' => 'syncing']); ?>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path
            d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z" />
        </svg>
        <span v-if="syncing">Đang đồng bộ...</span>
        <span v-else>Đồng bộ tác giả</span>
      </button>
    </div>

    <!-- Chạy theo chunk nên hiện luôn số chunk, người dùng biết còn bao lâu nữa. -->
    <div v-if="syncing" class="vnx-card">
      <div class="flex flex-wrap items-center justify-between gap-2 mb-2 text-xs text-gray-500">
        <span>
          Chunk <strong class="text-gray-900">{{ progress.currentPage }}</strong> /
          {{ progress.totalPages > 0 ? progress.totalPages : '...' }}
        </span>
        <span>
          {{ progress.current }} / {{ progress.totalPosts > 0 ? progress.totalPosts : '...' }} bài —
          {{ progress.percent }}%
        </span>
      </div>
      <div class="vnx-progress">
        <div class="vnx-progress__bar" :style="{ width: progress.percent + '%' }"></div>
      </div>
    </div>

    <div v-if="syncResult" class="vnx-alert"
      :class="syncResult.success ? 'vnx-alert--success' : 'vnx-alert--error'">
      <svg v-if="syncResult.success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
        aria-hidden="true">
        <path fill-rule="evenodd" clip-rule="evenodd"
          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
      </svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" clip-rule="evenodd"
          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
      </svg>
      <p>{{ syncResult.message }}</p>
    </div>
  </form>
</div>
