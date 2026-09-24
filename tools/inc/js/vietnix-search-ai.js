jQuery(function ($) {
  if (typeof ajaxurl === "undefined") {
    window.ajaxurl = window.ajaxurl || "/wp-admin/admin-ajax.php";
  }

  if ($("#vietnix-search-ai-settings-center").length) {
    new Vue({
      el: "#vietnix-search-ai-settings-center",
      data: {
        apiKey: "",
        showApiKey: false,
        syncTime: "",
        limitScore: 0,
        multiUrls: "",
        ignoreContent: "",
        isSaving: false,
        alertMessage: "",
        alertType: "", //success' | 'error'
        countdown: 0,
        activeTab: "settings", // 'settings' | 'import'
        importFile: null,
        importJson: null,
        isImporting: false,
        service: {
          token: "",
          models: "",
          modelsList: [],
          name: "",
          prompt_system: "",
          list_url: [],
          isSaving: false,
        },
      },
      mounted() {
        this.loadSettings();
        this.loadServiceSettings();
        this.getOpenAIModels();
      },
      created() {},

      methods: {
        // Hiện alert - dùng chung cho mọi thao tác AJAX trên trang này (cả 3 tab),
        // tránh lặp lại 2 dòng gán ở mỗi chỗ.
        showToast(message, type) {
          this.alertMessage = message;
          this.alertType = type;
        },
        /**
         * Lấy cấu hình AI hiện tại từ server.
         * @returns {void}
         * Output: Cập nhật các biến data (apiKey, syncTime, limitScore) với dữ liệu trả về từ server.
         */
        loadSettings() {
          // Sử dụng this thay vì self để đảm bảo reactivity của Vue
          $.post(ajaxurl, { action: "vnx_search_ai_get_settings_center" }, (res) => {
            if (res.success && res.data) {
              this.apiKey = res.data.apiKey || "";
              this.syncTime = res.data.syncTime || "";
              this.limitScore = res.data.limitScore || 0.75;
              this.multiUrls = res.data.multiUrls || "";
              this.ignoreContent = res.data.ignoreContent || "";
            }
          });
        },
        /**
         * Lưu cấu hình AI lên server.
         * @returns {void}
         * Input: apiKey (string), syncTime (string) (number), limitScore (number)
         * Output: Hiển thị thông báo thành công hoặc lỗi dựa trên phản hồi từ server.
         */
        saveSettings() {
          this.isSaving = true;
          this.alertMessage = "";
          this.alertType = "";
          $.post(
            ajaxurl,
            {
              action: "vnx_search_ai_save_settings_center",
              apiKey: this.apiKey,
              syncTime: this.syncTime,
              limitScore: parseFloat(this.limitScore) || 0.75,
              multiUrls: this.multiUrls,
              ignoreContent: this.ignoreContent,
            },
            (res) => {
              this.isSaving = false;
              if (res.success) {
                this.showToast("Đã lưu cấu hình AI!", "success");
              } else {
                this.showToast(
                  "Lỗi: " +
                    (res.data && res.data.message
                      ? res.data.message
                      : "Không xác định"),
                  "error"
                );
              }
            }
          );
        },
        /**
         * Gửi yêu cầu reset embeddings AI lên server.
         * @returns {void}
         * Output: Hiển thị thông báo thành công hoặc lỗi dựa trên phản hồi từ server.
         */
        resetSync() {
          // vnxConfirm do assets/js/admin/confirm.js gan len window (bundle admin.js).
          // Fallback ve window.confirm phong khi bundle chua kip nap.
          const ask = window.vnxConfirm
            ? window.vnxConfirm({
                title: "Xoá toàn bộ embeddings",
                message:
                  "Toàn bộ dữ liệu embedding của bài viết sẽ bị xoá và tạo lại từ đầu. Quá trình mất khoảng 40 - 50 phút, trong lúc đó tìm kiếm AI sẽ không ra kết quả.",
                confirmText: "Xoá & tạo lại",
                tone: "danger",
              })
            : Promise.resolve(window.confirm("Xoá và tạo lại toàn bộ embeddings?"));

          ask.then((ok) => {
            if (ok) this.doResetSync();
          });
        },

        doResetSync() {
          this.isSaving = true;
          this.alertMessage = "";
          this.alertType = "";
          this.countdown = 50 * 60;
          this.startCountdown();
          $.post(
            ajaxurl,
            {
              action: "vnx_search_ai_save_embeddings_center",
            },
            (res) => {
              this.isSaving = false;
              if (res.success) {
                this.showToast("Đã reset embeddings thành công!", "success");
              } else {
                this.showToast(
                  "Lỗi: " +
                    (res.data && res.data.message
                      ? res.data.message
                      : "Không xác định"),
                  "error"
                );
              }
            }
          );
        },
        startCountdown() {
          if (this._countdownInterval) clearInterval(this._countdownInterval);
          this._countdownInterval = setInterval(() => {
            if (this.countdown > 0) {
              this.countdown--;
            } else {
              clearInterval(this._countdownInterval);
            }
          }, 1000);
        },
        // Tab switch
        switchTab(tab) {
          this.activeTab = tab;
        },
        // Xử lý chọn file json
        handleFileChange(e) {
          const file = e.target.files[0];
          if (file && file.type === "application/json") {
            const reader = new FileReader();
            reader.onload = (evt) => {
              try {
                const json = JSON.parse(evt.target.result);
                if (!Array.isArray(json))
                  throw new Error("File json phải là một mảng");
                const requiredFields = [
                  "ID",
                  "title",
                  "link",
                  "excerpt",
                  "categories",
                  "thumbnail",
                ];
                const valid = json.every((item) => {
                  return requiredFields.every((f) => {
                    return Object.prototype.hasOwnProperty.call(item, f);
                  });
                });
                if (!valid)
                  throw new Error(
                    "Mỗi object phải có đủ các trường: " +
                      requiredFields.join(", ")
                  );
                this.importFile = file;
                this.importJson = json; // Lưu lại object json để gửi đi
              } catch (err) {
                this.importFile = null;
                this.importJson = null;
                this.showToast(
                  "File json không đúng định dạng: " + err.message,
                  "error",
                  4000
                );
              }
            };
            reader.readAsText(file);
          } else {
            this.importFile = null;
            this.importJson = null;
            this.showToast("Chỉ chấp nhận file .json!", "error");
          }
        },
        // Gửi file json lên server để import
        importHuongdan() {
          if (!this.importJson) {
            this.showToast("Vui lòng chọn file .json hợp lệ!", "error");
            return;
          }
          this.isImporting = true;
          $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "vnx_search_ai_import_huongdan_embeddings_center",
              posts: JSON.stringify(this.importJson),
            },
            success: (res) => {
              this.isImporting = false;
              if (res.success) {
                this.showToast("Import thành công!", "success");
              } else {
                this.showToast(
                  "Lỗi: " +
                    (res.data && res.data.message
                      ? res.data.message
                      : "Không xác định"),
                  "error"
                );
              }
            },
            error: () => {
              this.isImporting = false;
              this.showToast("Lỗi upload file!", "error");
            },
          });
        },
        // Service Price tab: load settings
        loadServiceSettings() {
          $.post(
            ajaxurl,
            { action: "vnx_get_csv_widgetbricks_get_settings_center" },
            (res) => {
              if (res.success && res.data) {
                this.service.token = res.data.token || "";
                this.service.models = res.data.models || "";
                this.service.prompt_system = res.data.prompt_system || "";
                this.service.listSelectedFiles = Array.isArray(res.data.listSelectedFiles) ? res.data.listSelectedFiles : [];
                if (Array.isArray(res.data.list_url)) {
                  this.service.list_url = res.data.list_url.map((item) => {
                    return {
                      url: item.url || "",
                      name: item.name || "",
                    };
                  });
                } else {
                  this.service.list_url = [];
                }
              }
            }
          );
        },
        // Service Price tab: save settings
        saveServiceSettings() {
          this.service.isSaving = true;
          // Gửi đúng cấu trúc object
          $.post(
            ajaxurl,
            {
              action: "vnx_csv_widgetbricks_save_settings_center",
              token: this.service.token,
              models: this.service.models,
              prompt_system: this.service.prompt_system,
              list_url: JSON.stringify(this.service.list_url),
            },
            (res) => {
              this.service.isSaving = false;
              if (res.success) {
                this.showToast("Đã lưu cấu hình Service Price!", "success");
              } else {
                this.showToast(
                  "Lỗi: " +
                    (res.data && res.data.message
                      ? res.data.message
                      : "Không xác định"),
                  "error"
                );
              }
            }
          );
        },
        // Service Price tab: get models
        getOpenAIModels() {
          $.post(ajaxurl, { action: "vnx_search_ai_list_models_center" }, (res) => {
            if (res.success && Array.isArray(res.data)) {
              this.service.modelsList = res.data;
            } else {
              this.service.modelsList = ["gpt-3.5-turbo", "gpt-4", "gpt-4.1"]; // fallback
            }
          });
        },
        // Service Price tab: add/remove url
        addServiceUrl() {
          this.service.list_url.push({
            url: "",
            name: "",
          });
        },
        removeServiceUrl(idx) {
          this.service.list_url.splice(idx, 1);
        },
        updateDataPostsToday() {
          const ask = window.vnxConfirm
            ? window.vnxConfirm({
                title: "Cập nhật bài hôm nay",
                message: "Tạo embedding cho các bài viết đăng trong hôm nay. Bài đã có embedding sẽ được ghi đè.",
                confirmText: "Cập nhật",
                tone: "primary",
              })
            : Promise.resolve(window.confirm("Chạy cập nhật embedding cho các bài viết mới hôm nay?"));

          ask.then((ok) => {
            if (ok) this.doUpdateDataPostsToday();
          });
        },

        doUpdateDataPostsToday() {
          this.isSaving = true;
          this.alertMessage = "";
          this.alertType = "";
          $.post(
            ajaxurl,
            {
              action: "vnx_save_post_embeddings_today_center",
            },
            (res) => {
              this.isSaving = false;
              if (res.success) {
                this.showToast(
                  "Đã cập nhật embedding cho bài viết mới hôm nay!",
                  "success"
                );
              } else {
                this.showToast(
                  "Lỗi: " +
                    (res.data && res.data.message
                      ? res.data.message
                      : "Không xác định"),
                  "error"
                );
              }
            }
          );
        },
      },
    });
  }
});
