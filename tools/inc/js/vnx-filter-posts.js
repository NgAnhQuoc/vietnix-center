/**
 * Vietnix Filter Posts — Vue.js logic
 * Lọc bài viết theo từ khoá, category, trạng thái, khoảng thời gian.
 * Hiển thị bảng kết quả phân trang + xuất CSV.
 */
(function () {
  document.addEventListener("DOMContentLoaded", function () {
    var el = document.getElementById("vnx-filter-posts-center");
    if (!el) return;

    // Guard: không crash nếu localize data chưa được inject
    if (typeof vnxFilterPostsData === "undefined") {
      console.warn("[VNX_FilterPosts] vnxFilterPostsData not found, skipping init.");
      return;
    }

    try {
    new Vue({
      el: "#vnx-filter-posts-center",
      data: {
        // Filters
        keyword: "",
        searchScope: ["all"],
        scopeDropdownOpen: false,
        scopeOptions: [
          { value: "all", label: "Tất cả" },
          { value: "h1", label: "H1" },
          { value: "seo_title", label: "Tiêu đề SEO" },
          { value: "h2", label: "H2" },
          { value: "content", label: "Nội dung" },
        ],
        category: 0,
        postStatus: "any",
        dateFrom: "",
        dateTo: "",

        // Results
        posts: [],
        totalPosts: 0,
        totalPages: 0,
        currentPage: 1,
        perPage: 20,

        // UI states
        loading: false,
        exporting: false,
        alertMessage: "",
        alertType: "success", // 'success' | 'error'

        // Categories from PHP
        categories: vnxFilterPostsData.categories || [],

        // Status options
        statusOptions: [
          { value: "any", label: "Tất cả" },
          { value: "publish", label: "Published" },
          { value: "draft", label: "Draft" },
          { value: "pending", label: "Pending" },
          { value: "private", label: "Private" },
          { value: "trash", label: "Trash" },
        ],
      },

      computed: {
        selectedScopeText: function () {
          var vm = this;
          if (vm.searchScope.length === 0) return "Chưa chọn";
          if (vm.searchScope.includes("all")) return "Tất cả";

          var labels = vm.scopeOptions
            .filter(function (opt) {
              return vm.searchScope.includes(opt.value) && opt.value !== "all";
            })
            .map(function (opt) {
              return opt.label;
            });
          return labels.join(", ");
        },

        /**
         * Tính toán danh sách page buttons cho phân trang.
         * Hiển thị tối đa 7 page buttons xung quanh current page.
         */
        pageNumbers: function () {
          var pages = [];
          var total = this.totalPages;
          var current = this.currentPage;

          if (total <= 7) {
            for (var i = 1; i <= total; i++) pages.push(i);
            return pages;
          }

          // Luôn hiện trang 1
          pages.push(1);

          var start = Math.max(2, current - 2);
          var end = Math.min(total - 1, current + 2);

          // Thêm "..." nếu start > 2
          if (start > 2) pages.push("...");

          for (var i = start; i <= end; i++) pages.push(i);

          // Thêm "..." nếu end < total - 1
          if (end < total - 1) pages.push("...");

          // Luôn hiện trang cuối
          pages.push(total);

          return pages;
        },
      },

      methods: {
        toggleScopeDropdown: function () {
          this.scopeDropdownOpen = !this.scopeDropdownOpen;
        },
        isScopeSelected: function (value) {
          return this.searchScope.includes(value);
        },
        toggleScopeOption: function (value) {
          var index = this.searchScope.indexOf(value);
          if (value === "all") {
            this.searchScope = ["all"];
          } else {
            var allIndex = this.searchScope.indexOf("all");
            if (allIndex > -1) {
              this.searchScope.splice(allIndex, 1);
            }

            if (index > -1) {
              this.searchScope.splice(index, 1);
              if (this.searchScope.length === 0) {
                this.searchScope = ["all"];
              }
            } else {
              this.searchScope.push(value);
            }
          }
        },

        /**
         * Gọi AJAX lọc bài viết và cập nhật bảng.
         */
        filterPosts: function (page) {
          var vm = this;
          page = page || 1;
          vm.currentPage = page;
          vm.loading = true;
          vm.alertMessage = "";

          try {
            jQuery.ajax({
              url: vnxFilterPostsData.ajaxUrl,
              type: "POST",
              data: {
                action: "vnx_filter_posts_search_center",
                nonce: vnxFilterPostsData.nonce,
                keyword: vm.keyword,
                search_scope: vm.searchScope.join(","),
                category: vm.category,
                post_status: vm.postStatus,
                date_from: vm.dateFrom,
                date_to: vm.dateTo,
                page: page,
                per_page: vm.perPage,
              },
              success: function (response) {
                vm.loading = false;
                if (response.success) {
                  vm.posts = response.data.posts;
                  vm.totalPosts = response.data.total;
                  vm.totalPages = response.data.total_pages;
                  vm.currentPage = response.data.current_page;
                } else {
                  vm.showAlert("Lỗi: " + (response.data || "Không rõ"), "error");
                }
              },
              error: function (xhr, status, error) {
                vm.loading = false;
                vm.showAlert("Lỗi kết nối server: " + (error || status), "error");
              },
            });
          } catch (e) {
            vm.loading = false;
            vm.showAlert("Lỗi không mong muốn: " + e.message, "error");
            console.error("[VNX_FilterPosts] filterPosts error:", e);
          }
        },

        /**
         * Xuất CSV toàn bộ kết quả lọc.
         */
        exportCSV: function () {
          var vm = this;
          vm.exporting = true;
          vm.alertMessage = "";

          try {
            jQuery.ajax({
              url: vnxFilterPostsData.ajaxUrl,
              type: "POST",
              data: {
                action: "vnx_filter_posts_export_center",
                nonce: vnxFilterPostsData.nonce,
                keyword: vm.keyword,
                search_scope: vm.searchScope.join(","),
                category: vm.category,
                post_status: vm.postStatus,
                date_from: vm.dateFrom,
                date_to: vm.dateTo,
              },
              success: function (response) {
                vm.exporting = false;
                if (response.success) {
                  vm.downloadCSV(response.data.csv_data);
                  vm.showAlert(
                    "Đã xuất " + response.data.total + " bài viết thành công!",
                    "success"
                  );
                } else {
                  vm.showAlert("Lỗi: " + (response.data || "Không rõ"), "error");
                }
              },
              error: function (xhr, status, error) {
                vm.exporting = false;
                vm.showAlert("Lỗi kết nối server: " + (error || status), "error");
              },
            });
          } catch (e) {
            vm.exporting = false;
            vm.showAlert("Lỗi không mong muốn: " + e.message, "error");
            console.error("[VNX_FilterPosts] exportCSV error:", e);
          }
        },

        /**
         * Tạo file CSV từ data và trigger download.
         */
        downloadCSV: function (csvData) {
          try {
            // BOM để Excel nhận đúng UTF-8
            var BOM = "\uFEFF";
            var csvContent = BOM;

            for (var i = 0; i < csvData.length; i++) {
              var row = csvData[i];
              var line = [];
              for (var j = 0; j < row.length; j++) {
                var cell = String(row[j]).replace(/"/g, '""');
                line.push('"' + cell + '"');
              }
              csvContent += line.join(",") + "\r\n";
            }

            var blob = new Blob([csvContent], {
              type: "text/csv;charset=utf-8;",
            });
            var url = URL.createObjectURL(blob);
            var a = document.createElement("a");
            var now = new Date();
            var filename =
              "filter-posts_" +
              now.getFullYear() +
              "-" +
              String(now.getMonth() + 1).padStart(2, "0") +
              "-" +
              String(now.getDate()).padStart(2, "0") +
              "_" +
              String(now.getHours()).padStart(2, "0") +
              String(now.getMinutes()).padStart(2, "0") +
              ".csv";

            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
          } catch (e) {
            this.showAlert("Lỗi khi tạo file CSV: " + e.message, "error");
            console.error("[VNX_FilterPosts] downloadCSV error:", e);
          }
        },

        /**
         * Chuyển trang phân trang.
         */
        goToPage: function (page) {
          if (page === "..." || page < 1 || page > this.totalPages) return;
          this.filterPosts(page);
        },

        /**
         * Reset toàn bộ bộ lọc.
         */
        resetFilters: function () {
          this.keyword = "";
          this.searchScope = ["all"];
          this.category = 0;
          this.postStatus = "any";
          this.dateFrom = "";
          this.dateTo = "";
          this.posts = [];
          this.totalPosts = 0;
          this.totalPages = 0;
          this.currentPage = 1;
        },

        /**
         * Hiển thị alert toast.
         */
        showAlert: function (message, type) {
          this.alertMessage = message;
          this.alertType = type || "success";
        },

        /**
         * Trả về class CSS cho badge trạng thái.
         */
        statusBadgeClass: function (status) {
          switch (status) {
            case "Published":
              return "vnx-badge--green";
            case "Draft":
              return "vnx-badge--amber";
            case "Pending":
              return "vnx-badge--orange";
            case "Private":
              return "vnx-badge--purple";
            case "Trash":
              return "vnx-badge--red";
            default:
              return "vnx-badge--gray";
          }
        },

        /**
         * Tính STT dựa vào trang hiện tại.
         */
        rowIndex: function (index) {
          return (this.currentPage - 1) * this.perPage + index + 1;
        },
      },
      mounted: function () {
        var vm = this;
        document.addEventListener("click", function (e) {
          var container = vm.$el.querySelector(".scope-dropdown-container");
          if (container && !container.contains(e.target)) {
            vm.scopeDropdownOpen = false;
          }
        });
      },
    });
    } catch (e) {
      console.error("[VNX_FilterPosts] Vue init error:", e);
      // Hiển thị lỗi trực tiếp trong container nếu Vue không khởi tạo được
      if (el) {
        el.innerHTML = '<div style="padding:20px;color:#dc2626;border:1px solid #dc2626;border-radius:8px;background:#fef2f2;">' +
          '<strong>⚠ Lỗi khởi tạo Filter Posts:</strong> ' + e.message +
          '</div>';
      }
    }
  });
})();
