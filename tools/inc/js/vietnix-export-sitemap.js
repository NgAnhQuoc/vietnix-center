jQuery(function ($) {
  $(document).ready(function () {
    // Vue 2 khong thay el van tu tao div tam va chay mounted() (goi AJAX), nen chi khoi tao khi co giao dien cua tool tren trang.
    if (!document.querySelector("#vietnix-export-sitemap-center")) return;

    new Vue({
      el: "#vietnix-export-sitemap-center",
      data: {
        dataOption: {
          sheet_url: "",
          sheet_tab: "",
          site_domain: "",
        },
        loading: false,
        exporting: false,
        alertMessage: "",
        alertType: "success", // 'success' or 'error'
      },
      mounted() {
        this.getSettings();
      },

      methods: {
        saveSettings(e) {
          e.preventDefault();

          const form = document.getElementById("export-sitemap-form");
          if (!form.checkValidity()) {
            form.reportValidity();
            return;
          }

          this.loading = true;
          this.alertMessage = "";
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "save_export_sitemap_settings_center",
              nonce: vietnixExportSitemapData.nonce,
              settings: this.dataOption,
            },
            success: (response) => {
              this.loading = false;
              if (response.success) {
                this.showAlert("Cài đặt đã được lưu thành công", "success");
              } else {
                this.showAlert(
                  response.data || "Lưu cài đặt thất bại",
                  "error"
                );
              }
            },
            error: (error) => {
              this.loading = false;
              this.showAlert("Lỗi kết nối: " + error.statusText, "error");
            },
          });
        },

        getSettings() {
          this.loading = true;
          this.alertMessage = "";
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "get_export_sitemap_setting_center",
              nonce: vietnixExportSitemapData.nonce,
            },
            success: (response) => {
              this.loading = false;
              if (response.success && response.data) {
                this.dataOption = response.data;
              }
            },
            error: (error) => {
              this.loading = false;
              this.showAlert("Lấy dữ liệu thất bại", "error");
            },
          });
        },

        exportNow(e) {
          e.preventDefault();

          if (
            !this.dataOption.sheet_url ||
            !this.dataOption.sheet_tab
          ) {
            this.showAlert(
              "Vui lòng điền đầy đủ Link Sheet và Tên Tab trước khi export.",
              "error"
            );
            return;
          }

          this.exporting = true;
          this.alertMessage = "";

          // Save settings trước khi export để đảm bảo dùng đúng data trên form
          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "save_export_sitemap_settings_center",
              nonce: vietnixExportSitemapData.nonce,
              settings: this.dataOption,
            },
            success: () => {
              // Sau khi save thành công, tiến hành export
              jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                  action: "export_sitemap_now_center",
                  nonce: vietnixExportSitemapData.nonce,
                },
                timeout: 120000, // 2 phút timeout
                success: (response) => {
                  this.exporting = false;
                  if (response.success) {
                    this.showAlert(response.data || "Export thành công!", "success");
                  } else {
                    this.showAlert(
                      response.data || "Export thất bại",
                      "error"
                    );
                  }
                },
                error: (error) => {
                  this.exporting = false;
                  this.showAlert(
                    "Lỗi kết nối hoặc timeout: " + error.statusText,
                    "error"
                  );
                },
              });
            },
            error: (error) => {
              this.exporting = false;
              this.showAlert("Lỗi lưu cài đặt: " + error.statusText, "error");
            },
          });
        },

        showAlert(message, type = "success") {
          this.alertMessage = message;
          this.alertType = type;
        },
      },
    });
  });
});
