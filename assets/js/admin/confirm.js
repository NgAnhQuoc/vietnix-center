/**
 * Hộp xác nhận dùng chung, thay cho window.confirm().
 *
 * window.confirm() bị trình duyệt vẽ, không theo được giao diện plugin, không nói
 * được mức độ nguy hiểm của thao tác, và ở Chrome còn kèm ô "chặn hộp thoại" khiến
 * lần bấm sau im lặng không hỏi gì nữa.
 *
 * Hai cách dùng:
 *
 *   1. Khai báo trên nút - không phải viết JS:
 *      <button data-vnx-confirm="Xoá hết?"
 *              data-vnx-confirm-title="Xoá cache"
 *              data-vnx-confirm-ok="Xoá"
 *              data-vnx-confirm-tone="danger">
 *
 *   2. Gọi tay từ JS khác (kể cả file ngoài bundle, qua window):
 *      window.vnxConfirm({ message: "..." }).then(function (ok) { ... });
 */

const TONES = ["primary", "warning", "danger"];

const VNXConfirm = {
  init() {
    // Capture: phải chặn được cú click trước mọi handler khác (jQuery của tool,
    // submit của form...) thì mới hoãn được thao tác lại để hỏi.
    document.addEventListener("click", (event) => this.intercept(event), true);
    document.addEventListener("keydown", (event) => this.onKeydown(event));

    window.vnxConfirm = (options) => this.ask(options);
  },

  intercept(event) {
    const trigger = event.target.closest && event.target.closest("[data-vnx-confirm]");
    if (!trigger) return;

    // Cú click do chính mình phát lại sau khi người dùng đã đồng ý: cho đi tiếp.
    if (trigger.dataset.vnxConfirmed === "1") {
      delete trigger.dataset.vnxConfirmed;
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    this.ask({
      title: trigger.dataset.vnxConfirmTitle,
      message: trigger.dataset.vnxConfirm,
      confirmText: trigger.dataset.vnxConfirmOk,
      cancelText: trigger.dataset.vnxConfirmCancel,
      tone: trigger.dataset.vnxConfirmTone,
    }).then((ok) => {
      if (!ok) return;
      trigger.dataset.vnxConfirmed = "1";
      trigger.click();
    });
  },

  /**
   * @returns {Promise<boolean>}
   */
  ask(options) {
    const opts = options || {};
    this.build();

    const tone = TONES.indexOf(opts.tone) !== -1 ? opts.tone : "warning";

    this.el.dialog.className = "vnx-modal__dialog vnx-modal__dialog--" + tone;
    this.el.title.textContent = opts.title || "Xác nhận thao tác";
    this.el.text.textContent = opts.message || "";
    this.el.ok.textContent = opts.confirmText || "Đồng ý";
    this.el.cancel.textContent = opts.cancelText || "Huỷ";
    this.el.ok.className =
      "vnx-btn " + (tone === "danger" ? "vnx-btn--danger" : tone === "warning" ? "vnx-btn--warning" : "vnx-btn--primary");

    this.lastFocused = document.activeElement;
    this.root.hidden = false;
    // Thao tác thường là loại phá huỷ, nên để con trỏ ở nút Huỷ: gõ Enter theo
    // quán tính thì không mất dữ liệu.
    this.el.cancel.focus();

    return new Promise((resolve) => {
      this.resolve = resolve;
    });
  },

  close(result) {
    if (!this.root || this.root.hidden) return;

    this.root.hidden = true;
    if (this.lastFocused && this.lastFocused.focus) this.lastFocused.focus();

    const resolve = this.resolve;
    this.resolve = null;
    if (resolve) resolve(result);
  },

  onKeydown(event) {
    if (!this.root || this.root.hidden) return;

    if (event.key === "Escape") {
      event.preventDefault();
      this.close(false);
      return;
    }

    // Giữ tiêu điểm quẩn giữa hai nút, không cho tab ra sau lớp phủ.
    if (event.key === "Tab") {
      event.preventDefault();
      const next = document.activeElement === this.el.ok ? this.el.cancel : this.el.ok;
      next.focus();
    }
  },

  /**
   * Dựng DOM một lần rồi dùng lại. Class đều là class của bộ giao diện
   * (assets/scss/admin/_tools.scss) chứ không phải utility Tailwind: Tailwind
   * không quét file JS nên utility viết ở đây sẽ không có CSS tương ứng.
   */
  build() {
    if (this.root) return;

    const root = document.createElement("div");
    root.className = "vnx-modal";
    root.hidden = true;
    root.innerHTML = [
      '<div class="vnx-modal__backdrop"></div>',
      '<div class="vnx-modal__dialog" role="alertdialog" aria-modal="true"',
      '     aria-labelledby="vnx-modal-title" aria-describedby="vnx-modal-text">',
      '  <span class="vnx-modal__icon" aria-hidden="true">',
      '    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">',
      '      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"',
      '            d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />',
      '    </svg>',
      "  </span>",
      '  <div class="vnx-modal__body">',
      '    <h2 class="vnx-modal__title" id="vnx-modal-title"></h2>',
      '    <p class="vnx-modal__text" id="vnx-modal-text"></p>',
      "  </div>",
      '  <div class="vnx-modal__actions">',
      '    <button type="button" class="vnx-btn vnx-btn--ghost" data-vnx-modal-cancel></button>',
      '    <button type="button" class="vnx-btn" data-vnx-modal-ok></button>',
      "  </div>",
      "</div>",
    ].join("");

    document.body.appendChild(root);

    this.root = root;
    this.el = {
      dialog: root.querySelector(".vnx-modal__dialog"),
      title: root.querySelector(".vnx-modal__title"),
      text: root.querySelector(".vnx-modal__text"),
      ok: root.querySelector("[data-vnx-modal-ok]"),
      cancel: root.querySelector("[data-vnx-modal-cancel]"),
    };

    this.el.ok.addEventListener("click", () => this.close(true));
    this.el.cancel.addEventListener("click", () => this.close(false));
    root.querySelector(".vnx-modal__backdrop").addEventListener("click", () => this.close(false));
  },
};

export default VNXConfirm;
