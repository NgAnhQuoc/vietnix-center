document.addEventListener("DOMContentLoaded", function () {
  // Vue 2 khong thay el van tu tao div tam va chay mounted() (goi AJAX), nen chi khoi tao khi co giao dien cua tool tren trang.
  if (!document.querySelector("#vietnix-report-posts-center")) return;

  new Vue({
    el: "#vietnix-report-posts-center",
    data: {
      dataOption: {
        linkhooks: "",
        daily: {
          title: "",
          time: "",
        },
        weekly: {
          title: "",
          time: "",
          day: "",
        },
      },

      optionDay: [
        {
          value: "monday",
          label: "Monday",
        },
        {
          value: "tuesday",
          label: "Tuesday",
        },
        {
          value: "wednesday",
          label: "Wednesday",
        },
        {
          value: "thursday",
          label: "Thursday",
        },
        {
          value: "friday",
          label: "Friday",
        },
        {
          value: "saturday",
          label: "Saturday",
        },
        {
          value: "sunday",
          label: "Sunday",
        },
      ],

      loading: false,
      alertMessage: "",
      alertType: "success",
    },
    created() {
      this.dataOption = window.vnxReportPostsData;
      this.nonce = window.vnxReportPostsNonce;
    },

    methods: {
      saveSettings(e) {
        e.preventDefault();

        // lấy sự kiện check mặc định
        const form = e.target.closest("form");
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
            action: "save_vietnix_settings_center",
            settings: this.dataOption,
            nonce: this.nonce,
          },
          success: (response) => {
            this.loading = false;
            // wp_send_json_error mac dinh van tra HTTP 200, phai xem response.success.
            if (response && response.success) {
              this.showAlert("Cài đặt đã được lưu thành công", "success");
            } else {
              this.showAlert((response && response.data) || "Lưu cài đặt thất bại", "error");
            }
          },
          error: (error) => {
            this.loading = false;
            const message = error.responseJSON && error.responseJSON.data;
            this.showAlert(message || error.statusText || error.message, "error");
          },
        });
      },

      showAlert(message, type) {
        this.alertMessage = message;
        this.alertType = type || "success";
      },
    },
  });
});
