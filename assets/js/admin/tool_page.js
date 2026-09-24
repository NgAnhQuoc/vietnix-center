
const HASH_PREFIX = "#tool=";
const QUERY_KEY = "tool";
const PANEL_ID_PREFIX = "vnx-tool-";

/**
 * Gắn slug vào URL mà server sẽ quay về sau khi lưu. Trả về đường dẫn tương đối
 * vì handler bên PHP bọc giá trị này trong site_url().
 */
function withToolParam(url, slug) {
  const parsed = new URL(url, window.location.href);
  parsed.searchParams.set(QUERY_KEY, slug);
  parsed.hash = "";
  return parsed.pathname + parsed.search;
}

const VNXToolPage = {
  init() {
    this.root = document.getElementById("vnx-tools");
    if (!this.root) return;

    this.tabs = Array.from(this.root.querySelectorAll("[data-tool-tab]"));
    // Vượt qua cả tool chỉ có link ra trang khác (không có panel/data-tool-tab)
    // để ô tìm kiếm vẫn lọc được chúng - xem bindSearch().
    this.searchItems = Array.from(this.root.querySelectorAll("[data-tool-slug]"));
    if (!this.tabs.length && !this.searchItems.length) return;

    this.restoreActiveTab();
    this.bindPersist();
    this.bindFormReturn();
    this.bindSearch();
  },

  findTab(slug) {
    if (!slug) return null;
    return this.tabs.find((tab) => tab.dataset.toolSlug === slug) || null;
  },

  restoreActiveTab() {
    const fromHash = window.location.hash.startsWith(HASH_PREFIX)
      ? window.location.hash.slice(HASH_PREFIX.length)
      : "";
    const fromQuery = new URLSearchParams(window.location.search).get(QUERY_KEY);
    const tab = this.findTab(fromHash) || this.findTab(fromQuery);
    if (tab && !tab.classList.contains("active")) {
      tab.click();
    }
    if (tab) this.scrollTabIntoView(tab);
  },

  /**
   * Màn hình nhỏ: danh sách tool xếp thành hàng ngang cuộn được,
   * nên tool đang mở phải được kéo vào giữa tầm nhìn.
   */
  scrollTabIntoView(tab) {
    const list = tab.parentElement;
    if (!list || list.scrollWidth <= list.clientWidth) return;
    const left = tab.offsetLeft - (list.clientWidth - tab.offsetWidth) / 2;
    list.scrollTo({ left: Math.max(left, 0), behavior: "smooth" });
  },

  bindPersist() {
    this.tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const slug = tab.dataset.toolSlug;
        // Preline chỉ toggle class .active, aria-selected phải tự cập nhật.
        this.tabs.forEach((other) => {
          other.setAttribute("aria-selected", other === tab ? "true" : "false");
        });
        this.scrollTabIntoView(tab);

        // ?tool= chỉ là phương tiện chở slug qua một lần submit, ghi được hash
        // rồi thì dọn đi cho URL khỏi mang hai dấu vết cùng nghĩa.
        const params = new URLSearchParams(window.location.search);
        params.delete(QUERY_KEY);
        const search = params.toString();

        window.history.replaceState(
          null,
          "",
          window.location.pathname + (search ? "?" + search : "") + HASH_PREFIX + slug
        );
      });
    });
  },

  /**
   * Form không chạy AJAX (post thẳng về trang này, hoặc qua admin-post.php rồi
   * redirect) sẽ làm trình duyệt tải lại trang. Cả hai đường đều không mang theo
   * hash, còn _wp_http_referer thì PHP đã ghi sẵn từ lúc render nên không biết
   * người dùng đã chuyển sang tool nào. Đóng dấu slug ngay trước khi gửi để lưu
   * xong quay lại đúng tool, không phụ thuộc vào localStorage.
   */
  bindFormReturn() {
    this.root.addEventListener("submit", (event) => {
      // Form AJAX (Vue) đã gọi preventDefault, trang không tải lại.
      if (event.defaultPrevented) return;

      const form = event.target;
      if (!form || form.tagName !== "FORM") return;

      const panel = form.closest('[id^="' + PANEL_ID_PREFIX + '"]');
      if (!panel) return;
      const slug = panel.id.slice(PANEL_ID_PREFIX.length);

      // wp_nonce_field() mặc định chèn _wp_http_referer cho MỌI form, nên không
      // thể lấy sự có mặt của nó để đoán form đi đường nào - phải nhìn action.
      const action = new URL(form.getAttribute("action") || window.location.href, window.location.href);
      const isAdminPost = /\/admin-post\.php$/.test(action.pathname);

      // Handler bên admin-post.php redirect về đúng giá trị này.
      const referer = form.querySelector('input[name="_wp_http_referer"]');
      if (referer) referer.value = withToolParam(referer.value, slug);

      // Form post thẳng về trang này thì không có bước redirect nào đọc
      // _wp_http_referer cả, phải gắn slug vào chính action.
      if (!isAdminPost) {
        form.setAttribute("action", withToolParam(action.href, slug));
      }
    });
  },

  bindSearch() {
    const input = document.getElementById("vnx-tools-search");
    const noResult = document.getElementById("vnx-tools-no-result");
    if (!input) return;

    const headings = Array.from(this.root.querySelectorAll("[data-tool-group]"));

    const filter = () => {
      const keyword = input.value.trim().toLowerCase();
      const visibleGroups = new Set();

      this.searchItems.forEach((tab) => {
        const hit = !keyword || tab.dataset.toolKeyword.indexOf(keyword) !== -1;
        tab.classList.toggle("hidden", !hit);
        if (hit) visibleGroups.add(tab.dataset.toolGroupItem);
      });

      headings.forEach((heading) => {
        heading.classList.toggle("hidden", !visibleGroups.has(heading.dataset.toolGroup));
      });

      if (noResult) noResult.classList.toggle("hidden", visibleGroups.size > 0);

      // Lọc xong thì kéo hàng tab về đầu để thấy ngay kết quả đầu tiên.
      const list = this.searchItems[0] && this.searchItems[0].parentElement;
      if (list) list.scrollLeft = 0;
    };

    input.addEventListener("input", filter);
    input.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        input.value = "";
        filter();
      }
    });
  },
};

export default VNXToolPage;
