<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
?>
<!-- id la diem mount cua Vue (tools/inc/js/vnx-filter-posts.js), khong doi. -->
<div id="vnx-filter-posts-center" class="vnx-panel vnx-panel--wide">

  <?php View::render('tools/partials/alert_vue'); ?>

  <div class="vnx-panel">
    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z" />
        </svg>
        Bộ lọc
      </h3>

      <div class="vnx-fields">
        <div class="vnx-field vnx-field--lg">
          <label class="vnx-label" for="vnx-fp-keyword">Từ khoá</label>
          <textarea id="vnx-fp-keyword" v-model="keyword" rows="4"
            placeholder="Mỗi dòng một từ khoá hoặc cách nhau bằng dấu phẩy..." @keydown.ctrl.enter="filterPosts(1)"
            @keydown.meta.enter="filterPosts(1)"></textarea>
        </div>

        <div class="vnx-field vnx-field--md">
          <div class="relative scope-dropdown-container">
            <label class="vnx-label" for="vnx-fp-scope">Tìm trong</label>
            <button type="button" id="vnx-fp-scope" @click="toggleScopeDropdown" :aria-expanded="scopeDropdownOpen"
              class="flex items-center justify-between w-full gap-2 px-3 py-2 text-left bg-white border border-gray-300 rounded text-[13px] leading-5 text-gray-900 hover:border-gray-400 focus:border-[#38A7FF] focus:outline-none">
              <span class="truncate">{{ selectedScopeText }}</span>
              <svg class="w-4 h-4 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-if="scopeDropdownOpen"
              class="absolute left-0 z-50 w-full p-1.5 mt-1 overflow-y-auto bg-white border border-gray-200 rounded shadow-lg max-h-60">
              <div v-for="opt in scopeOptions" :key="opt.value" @click="toggleScopeOption(opt.value)"
                class="flex items-center gap-2.5 p-2 rounded cursor-pointer select-none hover:bg-gray-50">
                <input type="checkbox" :checked="isScopeSelected(opt.value)" class="pointer-events-none">
                <span class="text-[13px] text-gray-700 pointer-events-none">{{ opt.label }}</span>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <label class="vnx-label" for="vnx-fp-category">Category</label>
            <select id="vnx-fp-category" v-model="category">
              <option :value="0">Tất cả category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
        </div>
      </div>

      <div class="mt-5 vnx-fields">
        <div class="vnx-field vnx-field--sm">
          <label class="vnx-label" for="vnx-fp-status">Trạng thái</label>
          <select id="vnx-fp-status" v-model="postStatus">
            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>

        <div class="vnx-field vnx-field--xs">
          <label class="vnx-label" for="vnx-fp-perpage">Số bài/trang</label>
          <select id="vnx-fp-perpage" v-model="perPage">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>

        <!-- Hai dau cua mot khoang, gom duoi mot nhan thay vi hai nhan roi. -->
        <div class="vnx-field vnx-field--auto">
          <span class="vnx-label">Ngày đăng</span>
          <div class="vnx-fields vnx-fields--tight">
            <div class="vnx-field vnx-field--sm">
              <input type="date" id="vnx-fp-from" v-model="dateFrom" aria-label="Từ ngày">
            </div>
            <span class="text-gray-400" aria-hidden="true">&rarr;</span>
            <div class="vnx-field vnx-field--sm">
              <input type="date" id="vnx-fp-to" v-model="dateTo" aria-label="Đến ngày">
            </div>
          </div>
        </div>
      </div>

      <div class="vnx-card__footer">
        <button type="button" class="vnx-btn vnx-btn--primary" @click="filterPosts(1)" :disabled="loading">
          <?php View::render('tools/partials/spinner', ['show' => 'loading']); ?>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <span v-if="loading">Đang tìm...</span>
          <span v-else>Tìm kiếm</span>
        </button>

        <button type="button" class="vnx-btn vnx-btn--success" @click="exportCSV"
          :disabled="exporting || totalPosts === 0">
          <?php View::render('tools/partials/spinner', ['show' => 'exporting']); ?>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span v-if="exporting">Đang xuất CSV...</span>
          <span v-else>Xuất CSV</span>
        </button>

        <button type="button" class="vnx-btn vnx-btn--ghost" @click="resetFilters">Reset</button>

        <span v-if="totalPosts > 0" class="text-[13px] text-gray-500">
          Tìm thấy <strong class="text-gray-900">{{ totalPosts }}</strong> bài viết
        </span>
      </div>
    </div>

    <div v-if="!loading && posts.length === 0 && totalPosts === 0 && currentPage === 1" class="vnx-empty">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
      </svg>
      <p>Nhập bộ lọc rồi nhấn <strong>Tìm kiếm</strong> để bắt đầu.</p>
    </div>

    <div v-if="loading && posts.length === 0" class="vnx-loading">
      <?php View::render('tools/partials/spinner', ['show' => '', 'class' => 'text-[#38A7FF]']); ?>
      Đang tìm kiếm bài viết...
    </div>
  </div>

  <!-- Bang 8 cot: de ngoai wrapper de an het chieu ngang panel. -->
  <div v-if="posts.length > 0">
    <div class="vnx-table-wrap">
      <table class="vnx-table">
        <thead>
          <tr>
            <th class="w-12 text-center">#</th>
            <th class="w-16 text-center">ID</th>
            <th>Tiêu đề</th>
            <th class="w-48">Slug</th>
            <th class="w-40">Category</th>
            <th class="w-32">Tác giả</th>
            <th class="w-36">Ngày đăng</th>
            <th class="w-24 text-center">Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(post, index) in posts" :key="post.id">
            <td class="text-xs text-center text-gray-400">{{ rowIndex(index) }}</td>
            <td class="font-mono text-xs text-center">{{ post.id }}</td>
            <td>
              <a :href="post.permalink" target="_blank" rel="noopener" :title="post.title" class="font-medium">
                {{ post.title }}
              </a>
            </td>
            <td class="text-xs text-gray-500 break-all">{{ post.slug }}</td>
            <td class="text-xs">{{ post.categories || '—' }}</td>
            <td class="text-xs">{{ post.author }}</td>
            <td class="text-xs text-gray-500">{{ post.date }}</td>
            <td class="text-center">
              <span class="vnx-badge" :class="statusBadgeClass(post.status)">{{ post.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="totalPages > 1" class="flex flex-wrap items-center justify-between gap-3 mt-4">
      <div class="text-[13px] text-gray-500">
        Trang {{ currentPage }} / {{ totalPages }} ({{ totalPosts }} bài viết)
      </div>
      <div class="flex gap-1">
        <button type="button" class="vnx-btn vnx-btn--ghost vnx-btn--sm" @click="goToPage(currentPage - 1)"
          :disabled="currentPage <= 1" aria-label="Trang trước">‹</button>

        <template v-for="(page, index) in pageNumbers">
          <button v-if="page !== '...'" :key="'p-' + index" type="button" class="vnx-btn vnx-btn--sm"
            :class="page === currentPage ? 'vnx-btn--primary' : 'vnx-btn--ghost'" @click="goToPage(page)">
            {{ page }}
          </button>
          <span v-else :key="'e-' + index" class="px-1 py-1.5 text-gray-400">…</span>
        </template>

        <button type="button" class="vnx-btn vnx-btn--ghost vnx-btn--sm" @click="goToPage(currentPage + 1)"
          :disabled="currentPage >= totalPages" aria-label="Trang sau">›</button>
      </div>
    </div>
  </div>
</div>