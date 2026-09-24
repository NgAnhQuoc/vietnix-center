<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
?>
<!-- id la diem mount cua Vue (tools/inc/js/vietnix-search-ai.js), khong doi. -->
<div id="vietnix-search-ai-settings-center" class="relative vnx-panel">

  <?php View::render('tools/partials/alert_vue'); ?>

  <div class="vnx-subtabs" role="tablist">
    <button type="button" class="vnx-subtab" role="tab" :class="{ 'is-active': activeTab === 'settings' }"
      :aria-selected="activeTab === 'settings'" @click="switchTab('settings')">
      <i class="fa-solid fa-gear" aria-hidden="true"></i> Cấu hình
    </button>
    <button type="button" class="vnx-subtab" role="tab" :class="{ 'is-active': activeTab === 'import' }"
      :aria-selected="activeTab === 'import'" @click="switchTab('import')">
      <i class="fa-solid fa-upload" aria-hidden="true"></i> Import site hướng dẫn
    </button>
    <button type="button" class="vnx-subtab" role="tab" :class="{ 'is-active': activeTab === 'service' }"
      :aria-selected="activeTab === 'service'" @click="switchTab('service')">
      <i class="fa-solid fa-database" aria-hidden="true"></i> Service Price
    </button>
  </div>

  <!-- Tab: cấu hình OpenAI + đồng bộ -->
  <div v-if="activeTab === 'settings'" class="vnx-panel">
    <form class="vnx-panel" @submit.prevent="saveSettings">
      <div class="vnx-card">
        <h3 class="vnx-card__title">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.7 5.7l-1.8 1.8H10v2H8v2H5a1 1 0 01-1-1v-2.6a1 1 0 01.3-.7l5-5A6 6 0 1121 9z" />
          </svg>
          Kết nối OpenAI
        </h3>

        <div class="vnx-field vnx-field--full">
          <label class="vnx-label" for="apiKey">API key OpenAI <span class="vnx-label__req">*</span></label>
          <!-- relative: chứa nút hiện/ẩn đặt tuyệt đối bên phải ô nhập -->
          <div class="relative">
            <input id="apiKey" name="apiKey" :type="showApiKey ? 'text' : 'password'" v-model="apiKey" required
              autocomplete="new-password" class="vnx-input--with-action" placeholder="sk-...">
            <button type="button" title="Hiện/Ẩn API Key" aria-label="Hiện hoặc ẩn API Key"
              class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 bg-transparent border-0 cursor-pointer hover:text-gray-700"
              @click="showApiKey = !showApiKey">
              <svg v-if="!showApiKey" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                viewBox="0 0 16 16" aria-hidden="true">
                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z" />
                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                viewBox="0 0 16 16" aria-hidden="true">
                <path
                  d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z" />
                <path
                  d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z" />
                <path
                  d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z" />
              </svg>
            </button>
          </div>
        </div>

        <div class="mt-4 vnx-fields">
          <div class="vnx-field vnx-field--xs">
            <label class="vnx-label" for="syncTime">Giờ đồng bộ <span class="vnx-label__req">*</span></label>
            <input type="time" id="syncTime" name="syncTime" v-model="syncTime" required>
            <p class="vnx-help">Cron chạy mỗi ngày một lần.</p>
          </div>

          <div class="vnx-field vnx-field--num">
            <label class="vnx-label" for="limitScore">Ngưỡng điểm trả về</label>
            <input type="number" id="limitScore" name="limitScore" min="0" max="1" step="0.1" v-model="limitScore"
              placeholder="VD: 0.75">
            <p class="vnx-help whitespace-nowrap">Độ tương đồng tối thiểu, từ 0 đến 1.</p>
          </div>
        </div>
      </div>

      <div class="vnx-card">
        <h3 class="vnx-card__title">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
          Phạm vi lập chỉ mục
        </h3>

        <!-- Hai o cung vai tro (danh sach, moi dong mot muc) nen chia deu hang,
             khong khai modifier do rong. -->
        <div class="vnx-fields">
          <div class="vnx-field">
            <label class="vnx-label" for="multiUrls">Danh sách URL (mỗi dòng một URL)</label>
            <textarea id="multiUrls" name="multiUrls" v-model="multiUrls" rows="6"
              placeholder="https://vietnix.vn/..."></textarea>
          </div>

          <div class="vnx-field">
            <label class="vnx-label" for="ignoreContent">Từ khoá bỏ qua (mỗi dòng một từ)</label>
            <textarea id="ignoreContent" name="ignoreContent" v-model="ignoreContent" rows="6"
              placeholder="Nhập mỗi dòng một từ khoá cần bỏ qua..."></textarea>
            <p class="vnx-help">Đoạn nội dung chứa từ khoá này sẽ không được đưa vào embeddings.</p>
          </div>
        </div>
      </div>

      <div class="vnx-actions">
        <button type="submit" class="vnx-btn vnx-btn--primary" :disabled="isSaving">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          {{ isSaving ? 'Đang lưu...' : 'Lưu cài đặt' }}
        </button>

        <button type="button" class="vnx-btn vnx-btn--success" @click="updateDataPostsToday" :disabled="isSaving">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Cập nhật bài hôm nay
        </button>

        <button type="button" id="resetSyncButton" class="vnx-btn vnx-btn--danger" @click="resetSync">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Xoá toàn bộ embeddings
        </button>

        <span v-if="countdown > 0" class="vnx-badge vnx-badge--blue">
          Còn {{ Math.floor(countdown / 60) }}:{{ (countdown % 60).toString().padStart(2, '0') }} phút
        </span>
      </div>
    </form>

    <!-- Lập chỉ mục chạy lâu, khoá luôn cả tab để không ai bấm chồng lệnh. -->
    <div v-if="isSaving" class="vnx-overlay">
      <?php View::render('tools/partials/spinner', ['show' => '', 'class' => 'text-white vnx-spinner--lg']); ?>
    </div>
  </div>

  <!-- Tab: import file JSON -->
  <div v-if="activeTab === 'import'" class="vnx-panel">
    <div class="vnx-card">
      <h3 class="vnx-card__title">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 15V3m0 0L8 7m4-4l4 4" />
        </svg>
        Import từ file JSON
      </h3>

      <div class="vnx-field vnx-field--lg">
        <label class="vnx-label" for="file_input">Chọn file .json</label>
        <input id="file_input" type="file" accept=".json,application/json" @change="handleFileChange"
          aria-describedby="file_input_help">
        <p class="vnx-help" id="file_input_help">Chỉ nhận file .json đúng định dạng bên dưới.</p>
      </div>

      <p class="mt-4 mb-2 text-[11px] font-semibold uppercase tracking-[0.04em] text-gray-400">Định dạng mẫu</p>
      <pre class="vnx-code">[{
  "ID": "string",
  "title": "string",
  "link": "string",
  "content": "string",
  "excerpt": "string",
  "categories": "string",
  "thumbnail": "string"
}, ...]</pre>

      <div class="vnx-card__footer">
        <button type="button" class="vnx-btn vnx-btn--primary" :disabled="isImporting || !importFile"
          @click="importHuongdan">
          {{ isImporting ? 'Đang import...' : 'Import' }}
        </button>
      </div>
    </div>
  </div>

  <!-- Tab: cấu hình trợ lý bảng giá dịch vụ -->
  <div v-if="activeTab === 'service'" class="vnx-panel">
    <form class="vnx-panel" @submit.prevent="saveServiceSettings">
      <div class="vnx-grid vnx-grid--2">
        <div class="vnx-card">
          <h3 class="vnx-card__title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Cấu hình model
          </h3>

          <div class="vnx-field vnx-field--full">
            <label class="vnx-label" for="vnx-ai-service-token">Token</label>
            <input id="vnx-ai-service-token" type="password" v-model="service.token" autocomplete="new-password"
              placeholder="Nhập token...">
          </div>

          <div class="mt-4 vnx-field vnx-field--md">
            <label class="vnx-label" for="vnx-ai-service-model">Model</label>
            <select id="vnx-ai-service-model" v-model="service.models">
              <option v-for="m in service.modelsList" :key="m" :value="m">{{ m }}</option>
            </select>
          </div>

          <div class="mt-4 vnx-field vnx-field--full">
            <label class="vnx-label" for="vnx-ai-service-prompt">Prompt system</label>
            <textarea id="vnx-ai-service-prompt" v-model="service.prompt_system" rows="8"
              placeholder="prompt_system..."></textarea>
          </div>
        </div>

        <div class="vnx-card">
          <h3 class="vnx-card__title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            Danh sách URL
          </h3>

          <div class="vnx-table-wrap">
            <table class="vnx-table">
              <thead>
                <tr>
                  <th>URL</th>
                  <th class="w-40">Loại</th>
                  <th class="w-12"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in service.list_url" :key="idx">
                  <td>
                    <input type="url" v-model="item.url" class="vnx-input--sm" placeholder="https://...">
                  </td>
                  <td>
                    <select v-model="item.name" class="vnx-input--sm">
                      <option v-if="!service.listSelectedFiles || service.listSelectedFiles.length === 0" value="">
                        Không có lựa chọn
                      </option>
                      <option v-for="w in service.listSelectedFiles" :key="w" :value="w">{{ w }}</option>
                    </select>
                  </td>
                  <td class="text-center">
                    <button type="button" class="vnx-btn vnx-btn--remove" aria-label="Xoá URL"
                      @click="removeServiceUrl(idx)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="!service.list_url || service.list_url.length === 0">
                  <td colspan="3" class="text-gray-400">Chưa có URL nào.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" class="mt-3 vnx-btn vnx-btn--ghost vnx-btn--sm" @click="addServiceUrl">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Thêm URL
          </button>
        </div>
      </div>

      <div class="vnx-actions">
        <button type="submit" class="vnx-btn vnx-btn--primary" :disabled="service.isSaving">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          {{ service.isSaving ? 'Đang lưu...' : 'Lưu cấu hình' }}
        </button>
      </div>
    </form>
  </div>
</div>