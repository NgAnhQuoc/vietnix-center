/**
 * Sync Post Authors By Category
 * Xử lý đồng bộ theo từng chunk để tránh timeout với danh mục nhiều bài.
 */
jQuery(function ($) {
    $(document).ready(function () {
        // Vue 2 khong thay el van tu tao div tam va chay mounted() (goi AJAX), nen chi khoi tao khi co giao dien cua tool tren trang.
        if (!document.querySelector("#vnx-sync-post-authors-center")) return;

        new Vue({
            el: '#vnx-sync-post-authors-center',

            data: {
                // Data từ PHP
                categories: window.vnxSyncAuthorsCategories || [],
                users: window.vnxSyncAuthorsUsers || [],
                nonce: window.vnxSyncAuthorsNonce || '',

                // Form values
                selectedCategory: '',
                seoAuthor: '',
                writer: '',
                technicalAuthor: '',

                // UI state
                syncing: false,
                alertMessage: '',
                alertType: 'success',
                syncResult: null,

                // Progress tracking
                progress: {
                    current: 0,   // tổng số bài đã update
                    totalPosts: 0,   // tổng số bài trong danh mục
                    totalPages: 0,   // tổng số chunk
                    currentPage: 0,   // đang xử lý chunk nào
                    percent: 0,
                },
            },

            methods: {

                // ===== Validate =====

                validate() {
                    if (!this.selectedCategory) {
                        this.showAlert('Vui lòng chọn danh mục!', 'error');
                        return false;
                    }
                    if (!this.seoAuthor && !this.writer && !this.technicalAuthor) {
                        this.showAlert('Vui lòng chọn ít nhất một tác giả để cập nhật!', 'error');
                        return false;
                    }
                    return true;
                },

                // ===== Entry point =====

                syncAuthors(e) {
                    e.preventDefault();
                    if (!this.validate()) return;

                    // Reset state
                    this.syncing = true;
                    this.syncResult = null;
                    this.progress = { current: 0, totalPosts: 0, totalPages: 0, currentPage: 0, percent: 0 };

                    // Bắt đầu từ page 1
                    this.processChunk(1);
                },

                // ===== Chunk loop =====

                /**
                 * Gọi AJAX xử lý 1 chunk, sau đó tự động gọi chunk tiếp theo
                 * cho đến khi done = true.
                 *
                 * @param {number} page - chunk cần xử lý (bắt đầu từ 1)
                 */
                processChunk(page) {
                    var self = this;
                    self.progress.currentPage = page;

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        timeout: 120000, // 2 phút / chunk
                        data: {
                            action: 'vnx_sync_post_authors_by_category_center',
                            nonce: self.nonce,
                            category_id: self.selectedCategory,
                            seo_author: self.seoAuthor,
                            writer: self.writer,
                            technical_author: self.technicalAuthor,
                            page: page,
                        },

                        success(response) {
                            if (!response.success) {
                                self.syncing = false;
                                self.showAlert(response.data || 'Có lỗi xảy ra!', 'error');
                                self.syncResult = { success: false, message: (response.data || 'Lỗi không xác định') };
                                return;
                            }

                            var data = response.data;

                            // Cập nhật tổng số bài (lần đầu)
                            if (page === 1) {
                                self.progress.totalPosts = data.total_posts;
                                self.progress.totalPages = data.total_pages;
                            }

                            // Cộng dồn số bài đã update
                            self.progress.current += data.updated;

                            // Tính phần trăm
                            if (self.progress.totalPosts > 0) {
                                self.progress.percent = Math.min(
                                    100,
                                    Math.round((self.progress.current / self.progress.totalPosts) * 100)
                                );
                            }

                            if (data.done) {
                                // Hoàn tất
                                self.syncing = false;
                                self.progress.percent = 100;
                                self.syncResult = {
                                    success: true,
                                    message: 'Hoàn tất! Đã cập nhật ' + self.progress.current + '/' + self.progress.totalPosts + ' bài viết.',
                                };
                                self.showAlert('Đồng bộ tác giả thành công!', 'success');
                            } else {
                                // Tiếp tục chunk kế tiếp
                                self.processChunk(page + 1);
                            }
                        },

                        error(xhr, status, error) {
                            self.syncing = false;
                            var msg = status === 'timeout'
                                ? 'Chunk ' + page + ' bị timeout. Thử giảm kích thước batch hoặc kiểm tra server.'
                                : 'Lỗi kết nối chunk ' + page + ': ' + error;
                            self.showAlert(msg, 'error');
                            self.syncResult = { success: false, message: msg };
                        },
                    });
                },

                // ===== Alert =====

                showAlert(message, type) {
                    this.alertMessage = message;
                    this.alertType = type || 'success';
                },
            },
        });
    });
});
