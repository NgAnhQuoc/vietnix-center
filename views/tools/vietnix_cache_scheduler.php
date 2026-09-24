<?php

use HelperCenter\View;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}
?>
<div id="vietnix-cache-scheduler" class="vnx-panel">

  <?php View::render('tools/partials/alert_vue'); ?>

  <div v-cloak v-if="lsStatus === 'missing'" class="vnx-alert vnx-alert--error">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
    </svg>
    <p>Chưa cài plugin <strong>LiteSpeed Cache</strong> nên các lịch hẹn sẽ không chạy.
      <a href="<?= esc_url(admin_url('plugin-install.php?s=litespeed-cache&tab=search&type=term')) ?>">Cài plugin ngay</a>.
    </p>
  </div>

  <div v-cloak v-else-if="lsStatus === 'inactive'" class="vnx-alert vnx-alert--warning">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
    </svg>
    <p>Plugin <strong>LiteSpeed Cache</strong> đang tắt nên các lịch hẹn sẽ không chạy.
      <a href="<?= esc_url(admin_url('plugins.php')) ?>">Kích hoạt plugin</a>.
    </p>
  </div>

  <div class="vnx-actions">
    <button type="button" class="vnx-btn vnx-btn--primary" @click="openCreateForm">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Thêm lịch mới
    </button>
    <label class="inline-flex items-center gap-2 ml-1 cursor-pointer select-none"
      :class="{ 'opacity-40 cursor-not-allowed': !autoEnabled && lsStatus !== 'active' }"
      title="Bật/tắt toàn bộ tự động hoá - không đổi trạng thái bật/tắt của từng lịch">
      <span class="relative inline-flex items-center shrink-0">
        <input type="checkbox" class="sr-only peer" :checked="autoEnabled"
          :disabled="!autoEnabled && lsStatus !== 'active'" @change="toggleAuto($event.target.checked, $event.target)">
        <span
          class="block w-9 h-5 rounded-full bg-gray-300 peer-checked:bg-[#38a7ff] peer-disabled:cursor-not-allowed peer-disabled:opacity-40 transition-colors"></span>
        <span
          class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></span>
      </span>
      <span class="text-[13px] text-gray-700">Tự động chạy</span>
    </label>
    <button type="button" class="ml-auto vnx-btn vnx-btn--icon"
      :class="notify.enabled ? 'vnx-btn--primary' : 'vnx-btn--ghost'"
      title="Cài đặt thông báo Discord" aria-label="Cài đặt thông báo Discord"
      @click="showNotify = true">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
      </svg>
    </button>
  </div>

  <div v-cloak v-if="showPublicUrls" class="vnx-modal" @click.self="showPublicUrls = false">
    <div class="vnx-modal__backdrop" @click="showPublicUrls = false"></div>
    <div class="vnx-modal__dialog vnx-modal__dialog--primary vnx-modal__dialog--lg" role="dialog" aria-modal="true"
      aria-labelledby="cs-public-urls-title">
      <span class="vnx-modal__icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </span>
      <div class="vnx-modal__body">
        <h2 id="cs-public-urls-title" class="flex items-center gap-2 vnx-modal__title">
          <span>Danh sách trang crawl cache</span>
          <span v-if="!loadingPublicUrls" class="vnx-badge vnx-badge--gray">{{ publicUrls.length }} trang</span>
        </h2>
        <p class="vnx-help">Các trang này sẽ được crawl lại cache khi chạy lịch xóa cache "Toàn bộ site".</p>

        <div v-if="loadingPublicUrls" class="mt-3 vnx-loading">
          <?php View::render('tools/partials/spinner', ['show' => '']); ?>
          Đang tải danh sách...
        </div>

        <div v-else-if="publicUrls.length === 0" class="mt-3 vnx-empty">
          <p>Không tìm thấy trang nào.</p>
        </div>

        <ul v-else class="mt-3 overflow-y-auto border border-gray-200 divide-y divide-gray-100 rounded max-h-80">
          <li v-for="(url, index) in publicUrls" :key="url">
            <a :href="url" :title="url" target="_blank" rel="noopener"
              class="flex items-center gap-2.5 px-3 py-1.5 text-xs leading-5 text-gray-600 no-underline hover:bg-gray-50 hover:text-[#38a7ff]">
              <span class="w-5 shrink-0 text-right font-mono text-[11px] tabular-nums text-gray-400">{{ index + 1 }}</span>
              <span class="flex-1 min-w-0 truncate">{{ url }}</span>
              <svg class="h-3.5 w-3.5 shrink-0 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none"
                stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
            </a>
          </li>
        </ul>

        <div class="vnx-modal__actions">
          <button type="button" class="vnx-btn vnx-btn--ghost vnx-btn--sm" @click="showPublicUrls = false">Đóng</button>
        </div>
      </div>
    </div>
  </div>

  <div v-cloak v-if="showNotify" class="vnx-modal" @click.self="showNotify = false">
    <div class="vnx-modal__backdrop" @click="showNotify = false"></div>
    <div class="vnx-modal__dialog vnx-modal__dialog--primary vnx-modal__dialog--lg" role="dialog" aria-modal="true"
      aria-labelledby="cs-notify-title">
      <span class="vnx-modal__icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
        </svg>
      </span>
      <div class="vnx-modal__body">
        <h2 id="cs-notify-title" class="vnx-modal__title">Thông báo qua Discord</h2>

        <div class="mt-4 vnx-fields">
          <div class="vnx-field vnx-field--full">
            <label class="vnx-label" for="cs-webhook">Webhook URL</label>
            <input type="url" id="cs-webhook" v-model="notify.webhook_url"
              placeholder="https://discord.com/api/webhooks/...">
          </div>

          <div class="vnx-field vnx-field--full">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
              <span class="relative inline-flex items-center shrink-0">
                <input type="checkbox" class="sr-only peer" v-model="notify.enabled">
                <span
                  class="block w-9 h-5 rounded-full bg-gray-300 peer-checked:bg-[#38a7ff] transition-colors"></span>
                <span
                  class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></span>
              </span>
              <span class="text-[13px] text-gray-700">Bật thông báo</span>
            </label>
          </div>
        </div>

        <div class="vnx-modal__actions">
          <button type="button" class="vnx-btn vnx-btn--ghost vnx-btn--sm" @click="showNotify = false">Đóng</button>
          <button type="button" class="vnx-btn vnx-btn--ghost vnx-btn--sm" :disabled="!notify.webhook_url || testingNotify"
            @click="testNotify">
            {{ testingNotify ? 'Đang gửi...' : 'Gửi thử' }}
          </button>
          <button type="button" class="vnx-btn vnx-btn--primary vnx-btn--sm" :disabled="savingNotify" @click="saveNotify">
            {{ savingNotify ? 'Đang lưu...' : 'Lưu cài đặt' }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div v-cloak v-if="showForm" class="vnx-modal" @click.self="closeForm">
    <div class="vnx-modal__backdrop" @click="closeForm"></div>
    <div class="vnx-modal__dialog vnx-modal__dialog--primary vnx-modal__dialog--xl" role="dialog" aria-modal="true"
      aria-labelledby="cs-form-title">
      <span class="vnx-modal__icon" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" />
        </svg>
      </span>
      <div class="vnx-modal__body">
        <h2 id="cs-form-title" class="vnx-modal__title">{{ form.id ? 'Sửa lịch hẹn' : 'Thêm lịch hẹn mới' }}</h2>

        <div v-if="formError" class="mt-4 vnx-alert vnx-alert--error" role="alert">
          <p>{{ formError }}</p>
        </div>

        <form @submit.prevent="submitForm">
          <div class="mt-4 vnx-fields">
            <div class="vnx-field vnx-field--full">
              <label class="vnx-label" for="cs-label">Nhãn</label>
              <input type="text" id="cs-label" v-model="form.label" placeholder="Xoá cache trang chủ mỗi sáng">
            </div>

            <div class="vnx-field vnx-field--full">
              <label class="vnx-label">Phạm vi xoá</label>
              <div class="flex flex-wrap gap-4">
                <label class="vnx-check"><input type="radio" value="all" v-model="form.purge_type"> Toàn bộ site</label>
                <label class="vnx-check"><input type="radio" value="urls" v-model="form.purge_type"> URL cụ thể</label>
              </div>
              <p class="vnx-help" v-if="form.purge_type === 'all'">Chỉ chọn "Toàn bộ site" khi thật sự cần thiết: lịch này xoá và crawl lại cache các trang đã public, tốn nhiều thời gian và tài nguyên server hơn hẳn so với xoá theo URL cụ thể.</p>
            </div>

            <div class="vnx-field vnx-field--full" v-if="form.purge_type === 'urls'">
              <label class="vnx-label" for="cs-urls">Danh sách URL <span class="vnx-label__req">*</span></label>
              <textarea id="cs-urls" rows="4" v-model="form.urls"
                placeholder="https://vietnix.vn/a&#10;https://vietnix.vn/b"></textarea>
              <p class="vnx-help">Mỗi dòng 1 link. Có thể nhập đường dẫn tương đối, ví dụ /blog/bai-viet.</p>
            </div>

            <div class="vnx-field vnx-field--md">
              <label class="vnx-label" for="cs-schedule-type">Loại lịch</label>
              <select id="cs-schedule-type" v-model="form.schedule_type">
                <option value="once">Chạy 1 lần</option>
                <option value="daily">Hằng ngày</option>
                <option value="weekly">Hằng tuần</option>
                <option value="interval">Theo chu kỳ (phút)</option>
              </select>
            </div>

            <div class="vnx-field vnx-field--md" v-if="form.schedule_type === 'once'">
              <label class="vnx-label" for="cs-run-at">Thời điểm chạy</label>
              <input type="datetime-local" id="cs-run-at" v-model="form.run_at">
            </div>

            <div class="vnx-field vnx-field--md" v-if="form.schedule_type === 'daily' || form.schedule_type === 'weekly'">
              <label class="vnx-label" for="cs-time">Giờ chạy</label>
              <input type="time" id="cs-time" v-model="form.time_of_day">
            </div>

            <div class="vnx-field vnx-field--md" v-if="form.schedule_type === 'interval'">
              <label class="vnx-label" for="cs-interval">Chạy lại mỗi (phút)</label>
              <input type="number" id="cs-interval" min="1" v-model.number="form.interval_minutes">
            </div>

            <div class="vnx-field vnx-field--full" v-if="form.schedule_type === 'weekly'">
              <label class="vnx-label">Ngày trong tuần</label>
              <div class="flex flex-wrap gap-4">
                <label v-for="day in weekdayOptions" :key="day.value" class="vnx-check">
                  <input type="checkbox" :value="day.value" v-model="form.weekdays"> {{ day.label }}
                </label>
              </div>
            </div>

            <div class="vnx-field vnx-field--full">
              <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                <span class="relative inline-flex items-center shrink-0">
                  <input type="checkbox" class="sr-only peer" v-model="form.enabled"
                    :disabled="!form.enabled && lsStatus !== 'active'">
                  <span
                    class="block w-9 h-5 rounded-full bg-gray-300 peer-checked:bg-[#38a7ff] peer-disabled:cursor-not-allowed peer-disabled:opacity-40 transition-colors"></span>
                  <span
                    class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></span>
                </span>
                <span class="text-[13px] text-gray-700">Bật lịch này ngay sau khi lưu</span>
              </label>
            </div>
          </div>

          <div class="vnx-modal__actions">
            <button type="button" class="vnx-btn vnx-btn--ghost" @click="closeForm">Huỷ</button>
            <button type="submit" class="vnx-btn vnx-btn--primary" :disabled="saving">
              <?php View::render('tools/partials/spinner', ['show' => 'saving']); ?>
              <span>{{ saving ? 'Đang lưu...' : 'Lưu lịch' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div v-cloak v-if="loading" class="vnx-loading">
    <?php View::render('tools/partials/spinner', ['show' => '']); ?>
    Đang tải danh sách lịch...
  </div>

  <div v-cloak v-else-if="jobs.length === 0" class="vnx-empty">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <p>Chưa có lịch hẹn xoá cache nào.</p>
  </div>

  <div v-cloak v-else class="vnx-table-wrap">
    <table class="vnx-table">
      <thead>
        <tr>
          <th class="w-10 text-center">#</th>
          <th class="w-full min-w-[180px]">Nhãn</th>
          <th class="w-px">Phạm vi</th>
          <th class="w-px">Lịch chạy</th>
          <th class="w-px text-center">Đã xử lý</th>
          <th class="w-px">Lần gần nhất</th>
          <th class="w-px text-center">Bật/Tắt</th>
          <th class="w-px text-right">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(job, index) in jobs" :key="job.id">
          <td class="text-xs text-center text-gray-400">{{ index + 1 }}</td>
          <td class="font-medium align-middle">{{ job.label }}</td>
          <td class="w-px whitespace-nowrap">
            <span class="inline-flex items-center gap-1.5">
              <template v-if="job.purge_type === 'all'">
                <span class="vnx-badge vnx-badge--red">Toàn site</span>
                <button type="button" class="vnx-icon-link" data-tooltip="Danh sách trang crawl cache"
                  aria-label="Danh sách trang crawl cache" @click="openPublicUrls">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </button>
              </template>
              <span v-else class="vnx-badge vnx-badge--blue">{{ job.urls.length }} URL</span>
            </span>
          </td>
          <td class="w-px text-xs whitespace-nowrap">{{ scheduleLabel(job) }}</td>
          <td class="w-px text-xs text-center whitespace-nowrap">
            <template v-if="job.progress && job.progress.total">
              <span class="font-mono tabular-nums"
                :class="job.last_status === 'running' ? 'font-semibold text-[#38a7ff]' : 'text-gray-600'">
                {{ job.progress.done }}/{{ job.progress.total }}
              </span>
            </template>
            <span v-else class="text-gray-300">—</span>
          </td>
          <td class="w-px text-xs text-gray-500 whitespace-nowrap">
            <span class="inline-flex items-center gap-1.5">
              <span v-if="job.last_run_at" class="font-mono tabular-nums">{{ formatTime(job.last_run_at) }}</span>
              <span v-else class="text-gray-300">—</span>
              <span v-if="job.last_status" class="vnx-badge" :title="job.last_error || null"
                :class="statusBadgeClass(rowStatus(job))">{{ statusLabel(rowStatus(job)) }}</span>
            </span>
          </td>
          <td class="w-px text-center">
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" class="sr-only peer" :checked="job.enabled"
                :disabled="!job.enabled && lsStatus !== 'active'" @change="toggleJob(job, $event.target)">
              <div
                class="w-9 h-5 rounded-full bg-gray-300 peer-checked:bg-[#38a7ff] peer-disabled:cursor-not-allowed peer-disabled:opacity-40 transition-colors">
              </div>
              <div
                class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4">
              </div>
            </label>
          </td>
          <td class="w-px text-right whitespace-nowrap">
            <div class="inline-flex items-center justify-end gap-2">
              <button v-if="job.last_status === 'running'" type="button"
                class="vnx-btn vnx-btn--sm min-w-[104px]"
                :class="canForceCancel(job) ? 'vnx-btn--danger' : 'vnx-btn--warning'" :disabled="job.cancelling"
                :data-vnx-confirm-title="canForceCancel(job) ? 'Dừng khẩn cấp' : 'Huỷ lượt chạy'"
                :data-vnx-confirm="cancelConfirmText(job)"
                :data-vnx-confirm-ok="canForceCancel(job) ? 'Dừng khẩn cấp' : 'Huỷ lượt chạy'"
                data-vnx-confirm-tone="danger" @click="cancelJob(job, canForceCancel(job))">
                <?php View::render('tools/partials/spinner', ['show' => 'job.cancelling || (job.cancel_requested && !canForceCancel(job))']); ?>
                <span>{{ cancelButtonLabel(job) }}</span>
              </button>
              <button v-else type="button" class="vnx-btn vnx-btn--ghost vnx-btn--sm min-w-[104px]" :disabled="lsStatus !== 'active' || isRunning(job)"
                data-vnx-confirm-title="Chạy lịch ngay" :data-vnx-confirm="'Xoá cache theo lịch &quot;' + job.label + '&quot; ngay bây giờ?'"
                data-vnx-confirm-ok="Chạy ngay" @click="runNow(job)">
                <?php View::render('tools/partials/spinner', ['show' => 'isRunning(job)']); ?>
                <span>{{ isRunning(job) ? 'Đang chạy...' : 'Chạy ngay' }}</span>
              </button>
              <button type="button" class="vnx-btn vnx-btn--ghost vnx-btn--sm" :disabled="isRunning(job)"
                :title="isRunning(job) ? 'Lịch đang chạy, không thể sửa' : null" @click="openEditForm(job)">Sửa</button>
              <button type="button" class="vnx-btn vnx-btn--danger vnx-btn--sm" :disabled="isRunning(job)"
                :title="isRunning(job) ? 'Lịch đang chạy, không thể xoá' : null" data-vnx-confirm-title="Xoá lịch hẹn"
                :data-vnx-confirm="'Xoá lịch &quot;' + job.label + '&quot;? Thao tác này không hoàn tác được.'"
                data-vnx-confirm-ok="Xoá" data-vnx-confirm-tone="danger" @click="deleteJob(job)">Xoá</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>