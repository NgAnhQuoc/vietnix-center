const LOADING_CLASS = "is-loading";
const TAB_NAME_TO_ID = { widget: "1", extensions: "2", tool: "3", options: "options" };
const TAB_ID_TO_NAME = Object.fromEntries(Object.entries(TAB_NAME_TO_ID).map(([name, id]) => [id, name]));

const VNXGeneral = {
  init() {
    this.bindSubmitLoading();
    this.bindWidgetGroupField();
    this.bindTabHashSync();
    this.restoreActiveTabFromHash();
  },

  bindSubmitLoading() {
    document.addEventListener("submit", (event) => {
      // Form AJAX (Vue) tự gọi preventDefault và tự quản lý trạng thái của nó.
      if (event.defaultPrevented) return;

      const form = event.target;
      const button = form.querySelector(".vnx-button-submit");
      if (!button) return;

      // Chặn bấm lần hai trong lúc trình duyệt còn đang gửi request đầu.
      if (form.dataset.vnxSubmitting === "1") {
        event.preventDefault();
        return;
      }
      form.dataset.vnxSubmitting = "1";

      button.classList.add(LOADING_CLASS);
      button.setAttribute("aria-busy", "true");
    });

    // Bấm Back: trang lấy lại từ bfcache nên nút vẫn đang quay, phải trả về bình thường.
    window.addEventListener("pageshow", (event) => {
      if (!event.persisted) return;
      document.querySelectorAll("." + LOADING_CLASS + ".vnx-button-submit").forEach((button) => {
        button.classList.remove(LOADING_CLASS);
        button.removeAttribute("aria-busy");
        if (button.form) delete button.form.dataset.vnxSubmitting;
      });
    });
  },

  bindWidgetGroupField() {
    document.addEventListener(
      "submit",
      (event) => {
        const field = event.target.querySelector(".vnx-active-widget-group");
        if (!field) return;

        const activeSubtab = document.querySelector("#vnx-widget-subtab-gutenberg.active, #vnx-widget-subtab-gutenberg.is-active");
        field.value = activeSubtab ? "gutenberg" : "bricks";
      },
      true
    );
  },

  bindTabHashSync() {
    const state = {};
    const params = new URLSearchParams((window.location.hash || "").slice(1));
    state.tab = params.get("tab");
    state.subtab = params.get("subtab");

    const writeHash = () => {
      if (!state.tab) return;
      let hash = "#tab=" + state.tab;
      if (state.subtab) hash += "&subtab=" + state.subtab;
      if (window.location.hash !== hash) history.replaceState(null, "", hash);
    };

    document.addEventListener("click", (event) => {
      const mainTab = event.target.closest('[id^="hs-tab-to-select-item-"]');
      if (mainTab) {
        const match = mainTab.id.match(/^hs-tab-to-select-item-(.+)$/);
        if (!match) return;
        state.tab = TAB_ID_TO_NAME[match[1]] || match[1];
        // Đổi tab cha thì bỏ subtab cũ - nó thuộc tab khác, giữ lại sẽ lệch.
        state.subtab = null;
        writeHash();
        return;
      }

      const subtab = event.target.closest("#hs-tab-to-select-1 .vnx-subtab, #hs-tab-to-select-3 .vnx-subtab");
      if (subtab) {
        state.subtab = subtab.id;
        writeHash();
      }
    });

    const tabSelect = document.getElementById("tab-select");
    if (tabSelect) {
      tabSelect.addEventListener("change", () => {
        const match = tabSelect.value.match(/^#hs-tab-to-select-(.+)$/);
        if (!match) return;
        state.tab = TAB_ID_TO_NAME[match[1]] || match[1];
        state.subtab = null;
        writeHash();
      });
    }
  },

  restoreActiveTabFromHash() {
    const hash = window.location.hash;
    if (!hash || hash.indexOf("tab=") === -1) return;

    const params = new URLSearchParams(hash.slice(1));
    const tab = params.get("tab");
    const subtab = params.get("subtab");

    window.addEventListener("load", () => {
      const tabId = tab ? TAB_NAME_TO_ID[tab] || tab : null;
      if (tabId) {
        const tabButton = document.querySelector('[data-hs-tab="#hs-tab-to-select-' + tabId + '"]');
        if (tabButton && !tabButton.classList.contains("active")) tabButton.click();
      }
      if (subtab) {
        const subtabButton = document.getElementById(subtab);
        if (subtabButton && !subtabButton.classList.contains("active")) subtabButton.click();
      }
    });
  },
};

export default VNXGeneral;
