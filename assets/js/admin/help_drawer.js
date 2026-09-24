/**
 * Drawer "Hướng dẫn sử dụng" - trượt từ phải, dùng chung cho các trang admin
 * (views/tools/partials/help_drawer.php). Chỉ một drawer tồn tại trên mỗi trang
 * nên xử lý đơn giản như một singleton, không cần build lại DOM như confirm.js.
 *
 * Tab con bên trong drawer là Preline HSTabs (data-hs-tabs-vertical), tự init
 * cùng lúc với "import preline" ở assets/js/admin.js nên không cần đụng tới ở đây.
 */

const VNXHelpDrawer = {
  init() {
    this.root = document.getElementById("vnx-help-drawer");
    if (!this.root) return;

    this.panel = this.root.querySelector(".vnx-drawer__panel");

    document.addEventListener("click", (event) => {
      if (event.target.closest("[data-vnx-help-open]")) {
        event.preventDefault();
        this.open();
        return;
      }
      if (event.target.closest("[data-vnx-help-close]")) {
        event.preventDefault();
        this.close();
      }
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && !this.root.hidden) this.close();
    });
  },

  open() {
    if (this.openRaf) cancelAnimationFrame(this.openRaf);
    this.lastFocused = document.activeElement;
    this.root.hidden = false;

    this.openRaf = requestAnimationFrame(() => {
      this.openRaf = null;
      this.root.classList.add("is-open");
    });
    document.body.classList.add("vnx-drawer-lock");
    this.panel.focus();
  },

  close() {
    if (this.root.hidden) return;

    let hadRaf = false;
    if (this.openRaf) {
      cancelAnimationFrame(this.openRaf);
      this.openRaf = null;
      hadRaf = true;
    }

    const wasOpen = this.root.classList.contains("is-open");
    this.root.classList.remove("is-open");
    document.body.classList.remove("vnx-drawer-lock");
    if (this.lastFocused && this.lastFocused.focus) this.lastFocused.focus();

    if (hadRaf && !wasOpen) {
      this.root.hidden = true;
      return;
    }

    const panel = this.panel;
    const onEnd = (event) => {
      if (event.target !== panel) return;
      panel.removeEventListener("transitionend", onEnd);
      this.root.hidden = true;
    };
    panel.addEventListener("transitionend", onEnd);
  },
};

export default VNXHelpDrawer;
