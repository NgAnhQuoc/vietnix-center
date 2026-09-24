<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
?>
<script>
  window.vnxImportDocsNonce = "<?php echo wp_create_nonce('vnx_import_docs_nonce'); ?>";
</script>

<!-- id la diem mount cua Vue (tools/inc/js/vietnix-import-docs.js), khong doi. -->
<!-- Tool nay khong co bang du lieu nao can be ngang; form va ban xem truoc bai viet
     deu de doc hon o kho hep. -->
<div id="vnx-import-docs-center" class="vnx-panel">

  <?php View::render('tools/partials/alert_vue'); ?>

  <!-- Hai tab dau KHONG phai hai kieu import khac nhau, chung chi khac cach chon tai lieu:
       ca hai deu nap noi dung vao khoi "Xem truoc & tao bai viet" ben duoi roi moi tao bai.
       Vi vay ten tab dat theo nguon ("Tu ...") thay vi theo hanh dong ("Import ..."), de
       khong ai hieu nham tab folder la import ca folder mot luot. -->
  <div class="vnx-subtabs" role="tablist">
    <button type="button" class="vnx-subtab" role="tab" :class="{ 'is-active': activeTab === 'single' }"
      :aria-selected="activeTab === 'single'" @click="activeTab = 'single'">
      <i class="fa-solid fa-link" aria-hidden="true"></i> Import từ link tài liệu
    </button>
    <button type="button" class="vnx-subtab" role="tab" :class="{ 'is-active': activeTab === 'folder' }"
      :aria-selected="activeTab === 'folder'" @click="activeTab = 'folder'">
      <i class="fa-solid fa-folder-open" aria-hidden="true"></i> Import từ folder Drive
    </button>
    <button type="button" class="vnx-subtab" role="tab" :class="{ 'is-active': activeTab === 'settings' }"
      :aria-selected="activeTab === 'settings'" @click="activeTab = 'settings'">
      <i class="fa-solid fa-gear" aria-hidden="true"></i> Cài đặt
    </button>
  </div>

  <div class="vnx-card">
    <div v-if="activeTab === 'single'">
      <label class="vnx-label" for="vnx-idoc-single">Link Google Docs</label>
      <div class="vnx-fields">
        <div class="vnx-field vnx-field--lg">
          <input type="url" id="vnx-idoc-single" v-model="idImportDocs"
            placeholder="https://docs.google.com/document/d/..." @keydown.enter.prevent="clickImportDocs">
        </div>
        <button type="button" class="vnx-btn vnx-btn--primary" @click="clickImportDocs"
          :disabled="isLoading || !idImportDocs">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
          </svg>
          Tải nội dung
        </button>
      </div>
      <p class="vnx-help">Tải nội dung về khối <strong>Xem trước &amp; tạo bài viết</strong> bên dưới. Bước này chưa
        ghi gì vào WordPress — kiểm tra xong mới bấm Tạo bài viết.</p>
    </div>

    <div v-if="activeTab === 'folder'">
      <label class="vnx-label" for="vnx-idoc-folder">Link folder Google Drive</label>
      <div class="vnx-fields">
        <div class="vnx-field vnx-field--lg">
          <input type="url" id="vnx-idoc-folder" v-model="idFolder"
            placeholder="https://drive.google.com/drive/folders/..."
            @keydown.enter.prevent="clickGetListDocsInFolder">
        </div>
        <button type="button" class="vnx-btn vnx-btn--primary" @click="clickGetListDocsInFolder"
          :disabled="isLoading || !idFolder">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Liệt kê tài liệu
        </button>
      </div>
      <p class="vnx-help">Chỉ liệt kê các Google Docs có trong folder, <strong>không</strong> import cả folder một
        lượt. Vẫn làm từng bài một: bấm Xem trước ở tài liệu nào thì nội dung bài đó hiện ở khối bên dưới.</p>
    </div>

    <div v-if="activeTab === 'settings'">
      <label class="vnx-label" for="listIdCss">List ID template Bricks</label>
      <div class="vnx-fields">
        <div class="vnx-field vnx-field--md">
          <input type="text" id="listIdCss" v-model="settings.listIdCss" placeholder="VD: 1234, 5678">
        </div>
        <button type="button" class="vnx-btn vnx-btn--primary" @click="clickSaveSettings"
          :disabled="isLoading || !settings.listIdCss">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Lưu cài đặt
        </button>
      </div>
      <p class="vnx-help">Khối xem trước mượn CSS của các template Bricks này để hiển thị giống bài viết thật.
        Nhập ID template, cách nhau bằng dấu phẩy. Không ảnh hưởng tới nội dung bài được tạo ra.</p>
    </div>
  </div>

  <!-- Danh sách doc trong folder -->
  <div class="vnx-card" v-if="activeTab === 'folder' && listDocsInFolder.length > 0">
    <h3 class="vnx-card__title">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
      </svg>
      Tìm thấy {{ listDocsInFolder.length }} tài liệu
    </h3>
    <p class="vnx-card__desc">Bấm <strong>Xem trước</strong> để nạp một tài liệu xuống khối bên dưới, rồi tạo bài
      viết ở đó. Trạng thái bên dưới theo dõi từng tài liệu trong lần làm việc này.</p>

    <ul class="flex flex-col gap-2 m-0 list-none">
      <li class="flex items-center gap-3 px-3 py-2 border border-gray-200 rounded" v-for="doc in listDocsInFolder"
        :key="doc.id">
        <a class="flex-1 min-w-0 truncate text-[13px] text-[#1170BE] hover:text-[#0074CF]" target="_blank"
          rel="noopener" :href="`https://docs.google.com/document/d/${doc.id}/edit?tab=t.0`">{{ doc.name }}</a>

        <span class="shrink-0 vnx-badge vnx-badge--green" v-if="doc.status === 'success'">Đã tạo bài</span>
        <span class="shrink-0 vnx-badge vnx-badge--amber" v-else-if="doc.status === 'inprogress'">Đang mở bên
          dưới</span>

        <button type="button" class="shrink-0 vnx-btn vnx-btn--sm vnx-btn--ghost" @click="clickImportDoc(doc.id)">
          {{ doc.status === 'ready' ? 'Xem trước' : 'Xem lại' }}
        </button>
      </li>
    </ul>
  </div>

  <!-- Xem trước bài viết -->
  <div class="vnx-card">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
      <h3 class="vnx-card__title vnx-card__title--flush">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2.5 12S5.8 5.5 12 5.5 21.5 12 21.5 12 18.2 18.5 12 18.5 2.5 12 2.5 12z" />
        </svg>
        Xem trước &amp; tạo bài viết
      </h3>

      <div class="vnx-actions" v-if="!!contentPreview">
        <button type="button" class="vnx-btn vnx-btn--ghost" @click="clearImportDocs">Huỷ</button>

        <button type="button" class="vnx-btn vnx-btn--ghost" @click="clickSyncImage" v-if="!isSyncImage">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Đồng bộ ảnh vào Media
        </button>

        <button type="button" class="vnx-btn vnx-btn--primary" @click="clickCreatePost">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tạo bài viết
        </button>
      </div>
    </div>

    <div v-if="!contentPreview" class="vnx-empty">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <p>Chọn một tài liệu ở trên để xem trước nội dung tại đây.</p>
    </div>

    <div v-else class="flex flex-col gap-5">
      <dl class="grid grid-cols-1 gap-px overflow-hidden bg-gray-200 border border-gray-200 rounded sm:grid-cols-2">
        <div class="p-3 bg-white">
          <dt class="m-0 text-[11px] font-semibold uppercase tracking-[0.04em] text-gray-400">Meta title</dt>
          <dd class="m-0 mt-1 text-[13px] text-gray-900">{{ infoImportDocs.metaTitle || '—' }}</dd>
        </div>
        <div class="p-3 bg-white">
          <dt class="m-0 text-[11px] font-semibold uppercase tracking-[0.04em] text-gray-400">Slug</dt>
          <dd class="m-0 mt-1 text-[13px] text-gray-900 break-all">{{ infoImportDocs.slug || '—' }}</dd>
        </div>
        <div class="p-3 bg-white">
          <dt class="m-0 text-[11px] font-semibold uppercase tracking-[0.04em] text-gray-400">Keyword</dt>
          <dd class="m-0 mt-1 text-[13px] text-gray-900">{{ infoImportDocs.keyword || '—' }}</dd>
        </div>
        <div class="p-3 bg-white">
          <dt class="m-0 text-[11px] font-semibold uppercase tracking-[0.04em] text-gray-400">Category</dt>
          <dd class="m-0 mt-1 text-[13px] text-gray-900">{{ infoImportDocs.category || '—' }}</dd>
        </div>
        <div class="p-3 bg-white sm:col-span-2">
          <dt class="m-0 text-[11px] font-semibold uppercase tracking-[0.04em] text-gray-400">Description</dt>
          <dd class="m-0 mt-1 text-[13px] text-gray-900 whitespace-pre-line">
            {{ infoImportDocs.metaDescription || '—' }}
          </dd>
        </div>
      </dl>

      <!-- Nội dung render bằng CSS của Bricks nên phải giữ đúng cấu trúc class .brxe-* -->
      <div class="p-4 border border-gray-200 rounded">
        <div class="brxe-block">
          <div id="vnx_post_content" class="brxe-post-content">
            <div id="ftwp-postcontent" class="brxe-post-content" v-html="contentPreview"></div>
          </div>
        </div>

        <!-- Blocks Gutenberg tương ứng: chỉ dùng để copy sang editor, không cần hiện. -->
        <div class="hidden brxe-post-content">
          <div id="result-import-docs" v-html="contentBlocksWP"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="vnx-overlay" v-if="isLoading">
    <?php View::render('tools/partials/spinner', ['show' => '', 'class' => 'text-white vnx-spinner--lg']); ?>
  </div>
</div>