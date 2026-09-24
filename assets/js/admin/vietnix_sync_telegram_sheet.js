import Tagify from "@yaireo/tagify";

(function ($) {
  "use strict";

  window.addEventListener("DOMContentLoaded", (event) => {
    // Chọn form để cấu hình: Tagify mode "select" - danh sách form có thể dài
    // nên cần gõ tìm được, thay vì cuộn native <select>.
    const formSelectInput = document.getElementById("vnx-sync-form-select");
    if (formSelectInput) {
      const whitelist = JSON.parse(formSelectInput.dataset.vnxWhitelist || "[]");

      const formSelectTagify = new Tagify(formSelectInput, {
        mode: "select",
        whitelist,
        enforceWhitelist: true,
        dropdown: {
          enabled: 0,
          closeOnSelect: true,
          maxItems: Infinity,
          // Cac form co the trung ten chi khac hoa/thuong (vd. "dang-ky-x" vs "DANG-KY-X"),
          // mac dinh Tagify to dau check khong phan biet hoa/thuong nen ca 2 deu bi tinh la dang chon.
          caseSensitive: true,
        },
      });

      const showForm = (slug) => {
        document.querySelectorAll(".vnx-sync-form").forEach((form) => {
          form.classList.toggle("hidden", form.dataset.formSlug !== slug);
        });
      };

      formSelectTagify.on("change", (event) => {
        let tags = [];
        try {
          tags = JSON.parse(event.detail.value || "[]");
        } catch (e) {
          tags = [];
        }
        if (tags[0] && tags[0].formSlug) showForm(tags[0].formSlug);
      });
    }

    const input_sync_elements = document.querySelectorAll(".input_sync_tagify");
    input_sync_elements.forEach((item) => {
      new Tagify(item, {
        maxTags: 5,
        duplicates: true,
      });
    });

    const input_sync_elements_title_telegram = document.querySelectorAll(
      ".input_sync_elements_title_telegram"
    );
    input_sync_elements_title_telegram.forEach((item) => {
      new Tagify(item, {
        maxTags: 1,
        duplicates: true,
      });
    });

    const input_sync_elements_content_telegram = document.querySelectorAll(
      ".input_sync_elements_content_telegram"
    );
    input_sync_elements_content_telegram.forEach((item) => {
      new Tagify(item, {
        whitelist: [
          "Họ tên: name",
          "SĐT: phone",
          "Email: email",
          "Tác giả: author",
          "Date: date",
          "Referrer: referrer",
          "Tiêu đề: title",
          "Website: website",
          "Dịch vụ: service",
          "Gói: package",
        ],
        enforceWhitelist: false,
        dropdown: {
          classname: "tags-look",
          enabled: 0,
          closeOnSelect: false,
        },
      });
    });

    const input_sync_elements_data_GGSheet = document.querySelectorAll(
      ".input_sync_elements_data_GGSheet"
    );
    input_sync_elements_data_GGSheet.forEach((item) => {
      new Tagify(item, {
        whitelist: [
          "name",
          "phone",
          "email",
          "date",
          "referrer",
          "title",
          "website",
          "package",
          "service",
        ],
        enforceWhitelist: false,
        dropdown: {
          classname: "tags-look",
          enabled: 0,
          closeOnSelect: false,
        },
      });
    });
  });
})(jQuery);
