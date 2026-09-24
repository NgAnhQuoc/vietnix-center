jQuery(function ($) {
  $(document).ready(function () {
    // Vue 2 khong thay el van tu tao div tam va chay mounted() (goi AJAX), nen chi khoi tao khi co giao dien cua tool tren trang.
    if (!document.querySelector("#vnx-import-docs-center")) return;

    new Vue({
      el: "#vnx-import-docs-center",
      data: {
        idImportDocs: "",
        nonce: "",
        infoImportDocs: {
          thumbnailId: "",
          metaTitle: "",
          slug: "",
          metaDescription: "",
          keyword: "",
          category: "",
        },
        contentPreview: "",
        contentBlocksWP: "",
        isSyncImage: false,
        idNewPost: null,
        isLoading: false,
        alertMessage: "",
        alertType: "",
        idFolder: "",
        listDocsInFolder: [],
        activeTab:
          localStorage.getItem("vnxImportDocsActiveTab") || "single",
        settings: {
          listIdCss: "",
        },
      },
      created() {
        this.nonce = window.vnxImportDocsNonce;
      },
      mounted() {
        this.getSettings();
      },
      watch: {
        activeTab(value) {
          localStorage.setItem("vnxImportDocsActiveTab", value);
        },
      },
      methods: {
        async clickImportDocs(e) {
          e.preventDefault();

          this.isSyncImage = false;
          this.listDocsInFolder = [];
          await this.vnxImportDocs();
        },

        async clickSyncImage(e) {
          e.preventDefault();
          this.isLoading = true;
          await asyncImageToWordpressMediaLibrary();

          this.isSyncImage = true;
          this.isLoading = false;
          this.showToast(
            "Đã đồng bộ ảnh vào thư viện media WordPress.",
            "success"
          );
        },

        async clickCreatePost(e) {
          this.isLoading = true;
          e.preventDefault();

          const content = $("#result-import-docs").clone();

          const thumbnailEl = content.find("#thumbnail");
          const thumbnailId = thumbnailEl.data("id-thumbnail");
          const thumbnailDes = thumbnailEl.data("description_");
          const thumbnailAlt = thumbnailEl.data("alt_");

          content.find("img").removeAttr("name");
          content.find("#thumbnail").remove();

          const payload = {
            action: "vnx_create_post_from_content_center",
            thumbnailId: thumbnailId,
            thumbnailDes: thumbnailDes,
            thumbnailAlt: thumbnailAlt,
            metaTitle: this.infoImportDocs.metaTitle,
            slug: this.infoImportDocs.slug,
            metaDescription: this.infoImportDocs.metaDescription,
            keyword: this.infoImportDocs.keyword,
            nonce: this.nonce,
            category: this.infoImportDocs.category,
            content: content.html(),
          };

          jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: payload,
            success: function (response) {
              this.idNewPost = response.data.post_id;

              window.open(
                `/wp-admin/post.php?post=${this.idNewPost}&action=edit`,
                "_blank"
              );
            },
          });
          this.isLoading = false;

          this.listDocsInFolder = this.listDocsInFolder.map((doc) => {
            if (doc.id === this.idImportDocs) {
              return { ...doc, status: "success" };
            }

            return doc;
          });
        },

        async vnxImportDocs() {
          this.isLoading = true;
          await jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "vnx_import_docs_center",
              documentId: extractGoogleDocId(this.idImportDocs),
              nonce: this.nonce,
            },
            success: (response) => {
              console.log(response);
              if (response.success) {
                this.infoImportDocs = response.data.infoFirstTable;
                this.contentPreview = response.data.contentPreview;
                this.contentBlocksWP = response.data.contentBlocksWP;
                this.showToast("Đã import nội dung thành công.", "success");
              } else {
                this.showToast(response.data, "error");
                this.isLoading = false;
              }
            },
            complete: () => {
              this.isLoading = false;
            },
          });
        },

        async clickGetListDocsInFolder(e) {
          e.preventDefault();

          this.isLoading = true;
          await jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
              action: "vnx_get_list_docs_in_folder_center",
              folderId: extractGoogleDriveFolderId(this.idFolder),
              nonce: this.nonce,
            },
            success: (response) => {
              if (response.success) {
                // type response = {
                //   id: string;
                //   name: string;
                // };
                this.listDocsInFolder = response.data.files.map((file) => {
                  return {
                    ...file,
                    status: "ready",
                  };
                });
              } else {
                this.showToast(response.data, "error");
              }
            },
            complete: () => {
              this.isLoading = false;
            },
          });
        },

        async clickImportDoc(id) {
          this.idImportDocs = id;
          await this.vnxImportDocs();
          this.isSyncImage = false;

          this.listDocsInFolder = this.listDocsInFolder.map((doc) => {
            if (doc.id === id) {
              return { ...doc, status: "inprogress" };
            }

            if (doc.status === "inprogress") {
              return { ...doc, status: "ready" };
            }

            return doc;
          });
        },

        async clickSaveSettings() {
          await jQuery
            .ajax({
              url: ajaxurl,
              type: "POST",
              data: {
                action: "vnx_save_settings_center",
                listIdCss: this.settings.listIdCss,
                nonce: this.nonce,
              },
            })
            .done((response) => {
              if (response && response.success) {
                this.showToast("Đã lưu cài đặt.", "success");
              } else {
                this.showToast("Lưu cài đặt thất bại.", "error");
              }
            })
            .fail((response) => {
              this.showToast("Lưu cài đặt thất bại.", "error");
            });
        },

        async getSettings() {
          await jQuery
            .ajax({
              url: ajaxurl,
              type: "POST",
              data: { action: "vnx_get_settings_center", nonce: this.nonce },
            })
            .done((response) => {
              this.settings = response.data;
            })
            .fail((response) => {
              this.showToast("Lấy cài đặt thất bại.", "error");
            });
        },

        clearImportDocs() {
          this.idImportDocs = "";
          this.infoImportDocs = {
            thumbnailId: "",
            metaTitle: "",
            slug: "",
            metaDescription: "",
            keyword: "",
            content: "",
            category: "",
          };
          this.contentPreview = "";
          this.isSyncImage = false;
        },

        showToast(message, type) {
          // Cập nhật nội dung alert (đọc bởi views/tools/partials/alert_vue.php).
          // Nằm trong luồng nội dung (không phải toast nổi tự ẩn) nên giữ nguyên
          // đến khi người dùng thao tác tiếp, không tự biến mất.
          this.alertMessage = message;
          this.alertType = type;
        },
      },
    });

    function extractGoogleDocId(input) {
      const match = input.match(/\/document\/d\/([a-zA-Z0-9_-]+)/);
      if (match) {
        return match[1];
      }

      if (/^[a-zA-Z0-9_-]{20,}$/.test(input)) {
        return input;
      }

      return input;
    }

    function extractGoogleDriveFolderId(input) {
      const match = input.match(/\/drive\/folders\/([a-zA-Z0-9_-]+)/);
      if (match) {
        return match[1];
      }

      if (/^[a-zA-Z0-9_-]{20,}$/.test(input)) {
        return input;
      }

      return input;
    }

    async function asyncImageToWordpressMediaLibrary() {
      try {
        var content = $("#result-import-docs");
        var images = content.find("img");

        // Tạo một mảng các Promise để xử lý tất cả các ảnh
        const imagePromises = [];

        images.each((index, img) => {
          let src = $(img).attr("src");
          var name = $(img).attr("name");
          $(img).removeAttr("name");

          // Tạo một Promise cho mỗi ảnh
          const imagePromise = new Promise((resolve) => {
            jQuery.ajax({
              url: ajaxurl,
              type: "POST",
              data: {
                action: "vnx_async_image_to_wordpress_media_library_center",
                url: src,
                name: name,
                nonce: window.vnxImportDocsNonce,
              },
              success: function (response) {
                if (index === 0) {
                  $(img).attr("data-id-thumbnail", response.data.id);
                }

                $(img).attr("src", response.data.url);
                $(img).removeAttr("name");
                resolve();
              },
              error: function () {
                resolve(); // Vẫn resolve để không block các ảnh khác
              },
            });
          });

          imagePromises.push(imagePromise);
        });

        // Đợi tất cả các Promise hoàn thành
        await Promise.all(imagePromises);
      } catch (error) {
        console.error("Error:", error);
      }
    }
  });
});
