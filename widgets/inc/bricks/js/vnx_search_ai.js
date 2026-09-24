Vue.prototype.$eventBus = new Vue();

jQuery(function ($) {
  if (typeof ajaxurl === "undefined") {
    window.ajaxurl = window.ajaxurl || "/wp-admin/admin-ajax.php";
  }
  $("#vnx-search-ai").each(function () {
    new Vue({
      el: `#${this.id}`,
      data: {
        searchValue: "",
        isLoading: false,
      },
      methods: {
        handleSearch(e) {
          e.preventDefault();
          this.isLoading = true;
          this.$eventBus.$emit("search-ai", this.searchValue);
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        },
        // Khi user xóa hết text → emit ngay với chuỗi rỗng để hiển thị lại tất cả
        handleInput() {
          if (!this.searchValue || !this.searchValue.trim()) {
            this.$eventBus.$emit("search-ai", "");
          }
        },
      },
    });
  });

  $("#vnx-result-search-ai").each(function () {
    new Vue({
      el: `#${this.id}`,
      data: {
        searchValue: "",
        result: "",
        isLoading: false,
        isLoadingAll: false,
        error: "",
        allPosts: [],        // Cache toàn bộ bài viết để restore khi search rỗng
        searchResults: [],
        currentPage: 1,
        pageSize: 10,
        selectedCategories: [],
        uniqueCategories: [],
        downloadType: "txt",
        isDownloadingAll: false,
      },
      computed: {
        totalPages() {
          return Math.ceil(this.filteredResults.length / this.pageSize) || 1;
        },
        paginatedResults() {
          const start = (this.currentPage - 1) * this.pageSize;
          return this.filteredResults.slice(start, start + this.pageSize);
        },
        filteredResults() {
          if (!this.selectedCategories.length) return this.searchResults;
          return this.searchResults.filter((item) =>
            this.selectedCategories.includes(item.categories)
          );
        },
      },
      watch: {
        searchResults() {
          this.currentPage = 1;
          this.uniqueCategories = getUniqueCategories(this.searchResults);
          this.selectedCategories = this.uniqueCategories.map((c) => c.name);
        },
        activeTab(val) {
          if (val === "ai-price") {
            this.loadAiPrice();
          }
        },
      },
      created() {
        this.$eventBus.$on("search-ai", (data) => {
          this.handleSearchAI(data);
        });
        this.$nextTick(() => {
          if (this.uniqueCategories && this.uniqueCategories.length) {
            this.selectedCategories = this.uniqueCategories.map((c) => c.name);
          }
        });
        // Load tất cả bài viết mặc định khi trang khởi tạo
        this.loadAllPosts();
      },
      methods: {
        // Load toàn bộ bài viết từ file embedding để hiển thị mặc định
        async loadAllPosts() {
          this.isLoadingAll = true;
          this.error = "";
          try {
            const response = await fetch(ajaxurl + '?action=vnx_search_ai_download_embeddings_json_center', {
              method: 'POST',
            });
            if (!response.ok) throw new Error('Lỗi server: ' + response.status);
            const data = await response.json();
            if (Array.isArray(data)) {
              const mapped = data.map(item => ({
                title:      item.title      || '',
                link:       item.link       || '',
                excerpt:    item.excerpt    || '',
                content:    item.content    || '',
                categories: item.categories || '',
                score:      null,
              }));
              this.allPosts = mapped;       // lưu cache
              this.searchResults = mapped;  // hiển thị ngay
            }
          } catch (err) {
            this.error = 'Không thể tải danh sách bài viết: ' + err.message;
          } finally {
            this.isLoadingAll = false;
          }
        },
        async handleSearchAI(value) {
          this.searchValue = value;
          this.error = "";
          this.result = "";

          // Nếu search rỗng → restore từ cache, không gọi API
          if (!value || !value.trim()) {
            this.searchResults = this.allPosts.slice();
            return;
          }

          this.isLoading = true;
          this.searchResults = [];
          this.selectedCategories = [];
          const self = this;
          try {
            await $.ajax({
              url: ajaxurl,
              method: "POST",
              data: {
                action: "vnx_search_ai_search_posts_center",
                keyword: self.searchValue,
              },
              success: function (response) {
                if (response.success && Array.isArray(response.data)) {
                  self.searchResults = response.data.map(function (item) {
                    if (item.excerpt) {
                      item.excerpt = item.excerpt.replace(
                        /\[&hellip;\]/g,
                        "..."
                      );
                    }
                    return item;
                  });
                  self.selectedCategories = self.uniqueCategories.slice();
                } else if (response.data) {
                  self.result = response.data;
                } else {
                  self.error = "Không có kết quả.";
                }
              },
              error: function (xhr) {
                self.error =
                  "Lỗi khi tìm kiếm: " +
                  (xhr.responseJSON?.message || xhr.statusText);
              },
              complete: function () {
                self.isLoading = false;
              },
            });
          } catch (err) {
            this.error = "Lỗi không xác định: " + err.message;
            this.isLoading = false;
          }
        },
        changePage(page) {
          if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
          }
        },
        handleDownload() {
          const slug = this.slugify(this.searchValue || 'search-results');
          if (this.downloadType === "txt") this.downloadTxt(slug);
          else if (this.downloadType === "csv") this.downloadCsv(slug);
          else if (this.downloadType === "json") this.downloadJson(slug);
        },
        downloadTxt(slug) {
          let txt = this.filteredResults
            .map((item) => {
              return `${item.title}\n${item.link}\n${item.excerpt || ""}\n`;
            })
            .join("\n");
          const blob = new Blob([txt], { type: "text/plain" });
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = `${slug}.txt`;
          document.body.appendChild(a);
          a.click();
          setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
          }, 100);
        },
        async downloadCsv(slug) {
          const data = this.filteredResults.map((item) => ({
            "Tiêu đề": item.title || "",
            Link: item.link || "",
            "Tóm tắt": item.excerpt || "",
            "Chuyên mục": item.categories || "",
          }));
          const ws = XLSX.utils.json_to_sheet(data);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, ws, "Kết quả tìm kiếm");
          XLSX.writeFile(wb, `${slug}.csv`, { bookType: "csv" });
        },
        async downloadJson(slug) {
          const data = this.filteredResults.map((item) => ({
            title: item.title || "",
            link: item.link || "",
            excerpt: item.excerpt || "",
            categories: item.categories || "",
          }));
          const jsonStr = JSON.stringify(data, null, 2);
          const blob = new Blob([jsonStr], { type: "application/json" });
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = `${slug}.json`;
          document.body.appendChild(a);
          a.click();
          setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
          }, 100);
        },
        slugify(text) {
          return (text || "")
            .toString()
            .normalize('NFD')
            .replace(/\p{Diacritic}/gu, '')
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .toLowerCase();
        },
        toggleCategory(cat) {
          // Nếu chỉ có 1 category đang sáng và bấm vào nó thì mở all
          if (
            this.selectedCategories.length === 1 &&
            this.selectedCategories[0] === cat
          ) {
            this.selectedCategories = this.uniqueCategories.map((c) => c.name);
          } else if (
            this.selectedCategories.length === this.uniqueCategories.length
          ) {
            this.selectedCategories = [cat];
          } else if (this.selectedCategories.includes(cat)) {
            this.selectedCategories = this.selectedCategories.filter(
              (c) => c !== cat
            );
          } else {
            this.selectedCategories.push(cat);
          }
          this.currentPage = 1; // Luôn về trang 1 khi filter cate
        },
        decodeHtmlEntities(str) {
          if (!str) return "";
          const txt = document.createElement("textarea");
          txt.innerHTML = str;
          return txt.value;
        },
        async downloadAllPosts() {
          if (this.isDownloadingAll) return;
          this.isDownloadingAll = true;
          try {
            // 1. Fetch toàn bộ dữ liệu JSON từ API
            const response = await fetch(ajaxurl + '?action=vnx_search_ai_download_embeddings_json_center', {
              method: 'POST',
            });
            if (!response.ok) throw new Error('Lỗi kết nối server: ' + response.status);
            const data = await response.json();
            if (!Array.isArray(data)) throw new Error('Dữ liệu trả về không hợp lệ.');

            const slug = 'all-posts_' + new Date().toISOString().slice(0, 10);

            // 2. Convert và download theo định dạng đang chọn
            if (this.downloadType === 'txt') {
              const txt = data
                .map(item => `${item.title}\n${item.link}\n${item.excerpt || ''}\n`)
                .join('\n');
              this._triggerDownload(new Blob([txt], { type: 'text/plain' }), slug + '.txt');

            } else if (this.downloadType === 'csv') {
              const rows = data.map(item => ({
                'Tiêu đề': item.title || '',
                'Link': item.link || '',
                'Tóm tắt': item.excerpt || '',
                'Nội dung': item.content || '',
                'Chuyên mục': item.categories || '',
              }));
              const ws = XLSX.utils.json_to_sheet(rows);
              const wb = XLSX.utils.book_new();
              XLSX.utils.book_append_sheet(wb, ws, 'Tất cả bài viết');
              XLSX.writeFile(wb, slug + '.csv', { bookType: 'csv' });

            } else {
              // json (mặc định)
              const jsonStr = JSON.stringify(data, null, 2);
              this._triggerDownload(new Blob([jsonStr], { type: 'application/json' }), slug + '.json');
            }

          } catch (err) {
            alert('Không thể tải xuống: ' + err.message);
          } finally {
            this.isDownloadingAll = false;
          }
        },
        // Helper: tạo link ảo và trigger download
        _triggerDownload(blob, filename) {
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = filename;
          document.body.appendChild(a);
          a.click();
          setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
          }, 100);
        },
      },
    });
  });

  $("#vnx-result-dowload").each(function () {
    new Vue({
      el: `#${this.id}`,
      data: {
        data: null,
        downloadType: "txt",
        isLoadingPrice: false,
        aiPriceError: "",
      },
      created() {
        this.$eventBus.$on("search-ai", (data) => {
          this.fetchInfoByUrl(data);
        });
      },
      methods: {
        fetchInfoByUrl(url) {
          this.data = null;
          this.isLoadingPrice = true;
          this.aiPriceError = "";
          $.ajax({
            url: ajaxurl,
            method: "POST",
            data: {
              action: "vnx_search_ai_get_info_by_url_center",
              url: url,
            },
            success: (res) => {
              this.isLoadingPrice = false;
              if (res.success && res.data) {
                this.data = res.data;
              } else {
                this.aiPriceError = res.data?.message || res.message || "Không có dữ liệu.";
              }
            },
            error: (xhr) => {
              this.isLoadingPrice = false;
              let msg = "Lỗi không xác định";
              if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
              else if (xhr.statusText) msg = xhr.statusText;
              else if (xhr.responseText) msg = xhr.responseText;
              this.aiPriceError = msg;
            },
          });
        },
        formatJson(data) {
          try {
            if (typeof data === 'string') {
              data = JSON.parse(data);
            }
            return JSON.stringify(data, null, 2);
          } catch (e) {
            return typeof data === 'string' ? data : '';
          }
        },
        handleDownload() {
          if (this.downloadType === 'json') {
            this.downloadJson();
          } else {
            this.downloadTxt();
          }
        },
        downloadTxt() {
          let txt = '';
          if (typeof this.data === 'object') {
            txt = JSON.stringify(this.data, null, 2);
          } else {
            txt = this.data;
          }
          const blob = new Blob([txt], { type: "text/plain" });
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = "result.txt";
          document.body.appendChild(a);
          a.click();
          setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
          }, 100);
        },
        downloadJson() {
          let jsonStr = '';
          if (typeof this.data === 'object') {
            jsonStr = JSON.stringify(this.data, null, 2);
          } else {
            try {
              jsonStr = JSON.stringify(JSON.parse(this.data), null, 2);
            } catch (e) {
              jsonStr = this.data;
            }
          }
          const blob = new Blob([jsonStr], { type: "application/json" });
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = "result.json";
          document.body.appendChild(a);
          a.click();
          setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
          }, 100);
        },
        handleClose() {
          this.data = null;
        },
      },
    });
  });
});

// Hàm tiện ích lấy uniqueCategories dạng {name, count}
function getUniqueCategories(searchResults) {
  const catMap = {};
  searchResults.forEach((item) => {
    if (Array.isArray(item.categories)) {
      item.categories.forEach((catName) => {
        if (!catMap[catName]) catMap[catName] = 0;
        catMap[catName]++;
      });
    } else if (item.categories) {
      const catName = item.categories;
      if (!catMap[catName]) catMap[catName] = 0;
      catMap[catName]++;
    }
  });
  return Object.keys(catMap).map((name) => ({ name, count: catMap[name] }));
}
