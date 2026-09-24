jQuery(function ($) {
  $(document).ready(function () {
    // Vue 2 khong thay el van tu tao div tam va chay mounted() (goi AJAX), nen chi khoi tao khi co giao dien cua tool tren trang.
    if (!document.querySelector("#vnx-internal-link-ldp-center")) return;

    new Vue({
      el: "#vnx-internal-link-ldp-center",
      data: {
        settings: {
          sheet_url: "",
          sheet_tab: "",
          site_domain: "",
          ldp_urls: "",
          date_from: "",
          date_to: "",
        },
        loading: false,
        exporting: false,
        exportProgress: "",
        alertMessage: "",
        alertType: "success",

        // Resume state
        resumeAvailable: false,
        resumeInfo: null,   // { page, total_pages, count, started_at }
      },
      computed: {
        today() {
          return new Date().toISOString().slice(0, 10);
        },
      },
      mounted() {
        this.getSettings();
        this.checkProgress();
      },
      methods: {
        // ===== Settings =====
        getSettings() {
          this.loading = true;
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: { action: "vnx_illdp_get_settings_center", nonce: vnxILLDPData.nonce },
            success: (response) => {
              this.loading = false;
              if (response.success && response.data) {
                this.settings = response.data;
              }
            },
            error: () => {
              this.loading = false;
              this.showAlert("Lấy cài đặt thất bại", "error");
            },
          });
        },

        saveSettings(e) {
          e.preventDefault();
          const form = document.getElementById("vnx-illdp-form");
          if (!form.checkValidity()) {
            form.reportValidity();
            return;
          }
          this.loading = true;
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "vnx_illdp_save_settings_center",
              nonce: vnxILLDPData.nonce,
              settings: this.settings,
            },
            success: (response) => {
              this.loading = false;
              if (response.success) {
                this.showAlert("Cài đặt đã được lưu thành công", "success");
              } else {
                this.showAlert(response.data || "Lưu thất bại", "error");
              }
            },
            error: (err) => {
              this.loading = false;
              this.showAlert("Lỗi kết nối: " + err.statusText, "error");
            },
          });
        },

        // ===== Progress / Resume =====

        /**
         * Gọi khi trang load — kiểm tra xem có export đang dở không.
         */
        checkProgress() {
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: { action: "vnx_illdp_get_progress_center", nonce: vnxILLDPData.nonce },
            success: (response) => {
              if (response.success && response.data) {
                const p = response.data;
                if (!p.done && p.page > 0) {
                  this.resumeAvailable = true;
                  this.resumeInfo = p;
                }
              }
            },
            error: () => {
              // Silent fail — không crash Vue nếu AJAX lỗi
            },
          });
        },

        /**
         * Tiếp tục export từ trang kế tiếp (không clear Sheet — data trước đã có sẵn).
         */
        resumeExport() {
          this.resumeAvailable = false;
          this.exporting = true;
          const nextPage = (this.resumeInfo.page || 0) + 1;
          const total    = this.resumeInfo.total_pages || "?";
          this.exportProgress =
            `Đang tiếp tục từ trang ${nextPage}/${total} (đã ghi ${this.resumeInfo.count || 0} link vào Sheet)...`;
          // fresh=0 → không clear Sheet, chỉ append
          this.scanChunk(nextPage, false);
        },

        /**
         * Xóa progress cũ và bắt đầu lại hoàn toàn (clear Sheet).
         */
        discardAndRestart() {
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: { action: "vnx_illdp_clear_progress_center", nonce: vnxILLDPData.nonce },
            complete: () => {
              this.resumeAvailable = false;
              this.resumeInfo      = null;
              this.startFreshExport();
            },
          });
        },

        // ===== Export Flow =====

        exportNow(e) {
          e.preventDefault();
          if (!this.settings.sheet_url || !this.settings.sheet_tab) {
            this.showAlert("Vui lòng điền đầy đủ Link Sheet và Tên Tab.", "error");
            return;
          }

          // Bật loading ngay để không có khoảng trống "im lặng" trong lúc chờ AJAX
          this.exporting      = true;
          this.exportProgress = "Đang lưu cài đặt...";

          // Lưu settings trước
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "vnx_illdp_save_settings_center",
              nonce: vnxILLDPData.nonce,
              settings: this.settings,
            },
            success: () => {
              this.exportProgress = "Đang kiểm tra tiến trình export...";
              // Kiểm tra có export dở không
              jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: { action: "vnx_illdp_get_progress_center", nonce: vnxILLDPData.nonce },
                success: (res) => {
                  if (res.success && res.data && !res.data.done && res.data.page > 0) {
                    this.exporting       = false;
                    this.exportProgress  = "";
                    this.resumeAvailable = true;
                    this.resumeInfo      = res.data;
                  } else {
                    this.startFreshExport();
                  }
                },
                error: () => this.startFreshExport(),
              });
            },
            error: (err) => {
              this.exporting      = false;
              this.exportProgress = "";
              this.showAlert("Lỗi lưu cài đặt: " + err.statusText, "error");
            },
          });
        },

        startFreshExport() {
          this.exporting      = true;
          this.exportProgress = "Đang khởi tạo... Xóa dữ liệu cũ trên Sheet.";
          // fresh=1 ở page 1 → PHP sẽ clear Sheet trước khi append
          this.scanChunk(1, true);
        },

        /**
         * Quét 1 chunk (100 bài) và ghi ngay vào Sheet (server-side).
         *
         * @param {number}  page   Trang cần quét
         * @param {boolean} fresh  true = page 1, clear Sheet trước khi append
         */
        scanChunk(page, fresh) {
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            timeout: 120000, // 2 phút / chunk
            data: {
              action: "vnx_illdp_scan_chunk_center",
              nonce: vnxILLDPData.nonce,
              page: page,
              fresh: fresh ? 1 : 0,
            },
            success: (response) => {
              if (!response.success) {
                this.exporting      = false;
                this.exportProgress = "";
                this.showAlert(response.data || "Lỗi quét bài viết", "error");
                return;
              }

              const { page: pg, total_pages, count, chunk_count, done } = response.data;

              this.exportProgress =
                `Trang ${pg}/${total_pages} — Đã ghi link vào Sheet`;

              if (done) {
                this.exporting      = false;
                this.exportProgress = "";
                this.showAlert(
                  `Hoàn tất! Đã ghi link vào Google Sheet.`,
                  "success"
                );
              } else {
                // Tiếp tục chunk kế tiếp (không bao giờ fresh sau page 1)
                this.scanChunk(pg + 1, false);
              }
            },
            error: (err) => {
              this.exporting      = false;
              this.exportProgress = "";
              this.showAlert(
                `Lỗi hoặc timeout trang ${page}: ${err.statusText}. Reload trang để Resume.`,
                "error"
              );
            },
          });
        },

        // ===== Alert =====
        showAlert(message, type = "success") {
          this.alertMessage = message;
          this.alertType    = type;
        },
      },
    });
  });
});
