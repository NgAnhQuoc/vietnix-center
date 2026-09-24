<?php

/**
 * Tool: Đồng bộ tác giả ACF cho bài viết theo danh mục
 *
 * Xử lý theo từng chunk (50 bài/lần) để tránh timeout khi danh mục có nhiều bài.
 * Frontend gọi AJAX lặp lại cho đến khi done = true.
 */
class VNX_SyncPostAuthorsByCategory_Center
{
    private const PER_PAGE = 50;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_ajax_vnx_sync_post_authors_by_category_center', [$this, 'ajaxSyncChunk']);
    }

    // =========================================================================
    // Enqueue Scripts
    // =========================================================================

    public function enqueueScripts()
    {
        // Chi nap tren trang cua center: nap o moi trang admin (ke ca trang cua vietnix-plugin) thi Vue/CSS
        // cua center chay tren giao dien cua plugin kia va lam nang admin.
        if (!vnx_center_is_own_admin_page()) {
            return;
        }

        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        wp_enqueue_script(
            'vnx-sync-post-authors-center',
            VNX_PLUGIN_URL_CENTER . 'tools/inc/js/vnx-sync-post-authors.js',
            ['jquery', 'vuejs-library-center'],
            '1.0',
            true
        );
    }
    /**
     * Xử lý AJAX theo từng chunk (page).
     *
     * POST params:
     *   - nonce
     *   - category_id  (int)
     *   - seo_author   (int, optional)
     *   - writer       (int, optional)
     *   - technical_author (int, optional)
     *   - page         (int, default 1)
     */
    public function ajaxSyncChunk()
    {
        // Kiểm tra nonce
        if (!check_ajax_referer('vnx_sync_post_authors_nonce', 'nonce', false)) {
            wp_send_json_error('Nonce không hợp lệ.');
            return;
        }

        // Kiểm tra quyền
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Bạn không có quyền thực hiện thao tác này.');
            return;
        }

        // Tăng time limit cho chunk này
        set_time_limit(120);

        // Lấy và validate dữ liệu POST
        $category_id = intval($_POST['category_id'] ?? 0);
        $seo_author = intval($_POST['seo_author'] ?? 0);
        $writer = intval($_POST['writer'] ?? 0);
        $technical_author = intval($_POST['technical_author'] ?? 0);
        $page = max(1, intval($_POST['page'] ?? 1));

        if (empty($category_id)) {
            wp_send_json_error('Vui lòng chọn danh mục.');
            return;
        }

        if (!$seo_author && !$writer && !$technical_author) {
            wp_send_json_error('Vui lòng chọn ít nhất một tác giả để cập nhật.');
            return;
        }

        // Kiểm tra danh mục tồn tại
        $category = get_term($category_id, 'category');
        if (!$category || is_wp_error($category)) {
            wp_send_json_error('Danh mục không tồn tại.');
            return;
        }

        // Lấy chunk bài viết của page hiện tại
        $query = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'any',
            'posts_per_page' => self::PER_PAGE,
            'paged' => $page,
            'cat' => $category_id,
            'fields' => 'ids',
            'no_found_rows' => false,
        ]);

        $total_posts = (int) $query->found_posts;
        $total_pages = (int) $query->max_num_pages;
        $post_ids = $query->posts;

        // Không có bài nào (page 1 và empty)
        if ($page === 1 && empty($post_ids)) {
            wp_send_json_success([
                'page' => 1,
                'total_pages' => 0,
                'total_posts' => 0,
                'updated' => 0,
                'done' => true,
                'message' => 'Không có bài viết nào thuộc danh mục "' . esc_html($category->name) . '".',
            ]);
            return;
        }

        // Validate users và chuẩn bị fields cần update
        $fields_to_update = [];
        if ($seo_author && get_userdata($seo_author)) {
            $fields_to_update['seo_author'] = $seo_author;
        }
        if ($writer && get_userdata($writer)) {
            $fields_to_update['writer'] = $writer;
        }
        if ($technical_author && get_userdata($technical_author)) {
            $fields_to_update['technical_author'] = $technical_author;
        }

        if (empty($fields_to_update)) {
            wp_send_json_error('User không hợp lệ hoặc không tồn tại.');
            return;
        }

        // Cập nhật từng bài trong chunk
        $updated = 0;
        foreach ($post_ids as $post_id) {
            $post_updated = false;

            foreach ($fields_to_update as $field_name => $user_id) {
                if (function_exists('update_field')) {
                    // Dùng ACF nếu có
                    update_field($field_name, $user_id, $post_id);
                    $post_updated = true;
                } else {
                    // Fallback: post_meta
                    update_post_meta($post_id, $field_name, $user_id);
                    $post_updated = true;
                }
            }

            if ($post_updated) {
                $updated++;
            }
        }

        $done = ($page >= $total_pages) || empty($post_ids);

        wp_send_json_success([
            'page' => $page,
            'total_pages' => $total_pages,
            'total_posts' => $total_posts,
            'updated' => $updated,   // số bài update trong chunk này
            'done' => $done,
        ]);
    }
}

new VNX_SyncPostAuthorsByCategory_Center();
