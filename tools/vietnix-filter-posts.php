<?php

/**
 * Tool: Filter Posts
 *
 * Lọc bài viết WordPress theo từ khoá, category, trạng thái, khoảng thời gian.
 * Hiển thị kết quả dạng bảng phân trang. Hỗ trợ xuất CSV.
 */
class VNX_FilterPosts_Center
{
    private $search_keywords = [];
    private $search_scope    = 'all';

    public function __construct()
    {
        try {
            add_action('admin_enqueue_scripts',              [$this, 'enqueueScripts']);
            add_action('wp_ajax_vnx_filter_posts_search_center',    [$this, 'ajaxFilterPosts']);
            add_action('wp_ajax_vnx_filter_posts_export_center',    [$this, 'ajaxExportCSV']);
        } catch (\Throwable $e) {
            error_log('[VNX_FilterPosts_Center] Constructor error: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Admin Scripts
    // =========================================================================

    public function enqueueScripts()
    {
        // Chi nap tren trang cua center: nap o moi trang admin (ke ca trang cua vietnix-plugin) thi Vue/CSS
        // cua center chay tren giao dien cua plugin kia va lam nang admin.
        if (!vnx_center_is_own_admin_page()) {
            return;
        }

        try {

            wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
            wp_enqueue_script('vuejs-library-center');

            wp_enqueue_script(
                'vnx-filter-posts-center',
                VNX_PLUGIN_URL_CENTER . 'tools/inc/js/vnx-filter-posts.js',
                ['jquery', 'vuejs-library-center'],
                '1.0',
                true
            );

            // Lấy danh sách categories
            $categories = get_categories([
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]);

            $catList = [];
            foreach ($categories as $cat) {
                $catList[] = [
                    'id'   => $cat->term_id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ];
            }

            wp_localize_script('vnx-filter-posts-center', 'vnxFilterPostsData', [
                'nonce'      => wp_create_nonce('vnx_filter_posts_nonce'),
                'ajaxUrl'    => admin_url('admin-ajax.php'),
                'categories' => $catList,
            ]);
        } catch (\Throwable $e) {
            error_log('[VNX_FilterPosts_Center] enqueueScripts error: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // AJAX: Filter Posts (phân trang)
    // =========================================================================

    public function ajaxFilterPosts()
    {
        try {
            check_ajax_referer('vnx_filter_posts_nonce', 'nonce');

            if (!current_user_can('edit_posts')) {
                wp_send_json_error('Bạn không có quyền sử dụng chức năng này.');
                return;
            }
            $keyword     = sanitize_textarea_field($_POST['keyword'] ?? '');
            $searchScope = sanitize_text_field($_POST['search_scope'] ?? 'all');
            $category    = intval($_POST['category'] ?? 0);
            $postStatus  = sanitize_text_field($_POST['post_status'] ?? 'any');
            $dateFrom    = sanitize_text_field($_POST['date_from'] ?? '');
            $dateTo      = sanitize_text_field($_POST['date_to'] ?? '');
            $page        = max(1, intval($_POST['page'] ?? 1));
            $perPage     = max(1, min(100, intval($_POST['per_page'] ?? 20)));

            $args = $this->buildQueryArgs($keyword, $searchScope, $category, $postStatus, $dateFrom, $dateTo, $page, $perPage);

            add_filter('posts_join', [$this, 'customPostsJoin'], 10, 2);
            add_filter('posts_search', [$this, 'customPostsSearch'], 10, 2);
            $query = new WP_Query($args);
            remove_filter('posts_search', [$this, 'customPostsSearch'], 10);
            remove_filter('posts_join', [$this, 'customPostsJoin'], 10);

            $posts = $this->formatPosts($query->posts);

            wp_send_json_success([
                'posts'        => $posts,
                'total'        => (int) $query->found_posts,
                'total_pages'  => (int) $query->max_num_pages,
                'current_page' => $page,
                'per_page'     => $perPage,
            ]);
        } catch (\Throwable $e) {
            error_log('[VNX_FilterPosts_Center] ajaxFilterPosts error: ' . $e->getMessage());
            wp_send_json_error('Đã xảy ra lỗi khi lọc bài viết: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // AJAX: Export CSV (toàn bộ kết quả)
    // =========================================================================

    public function ajaxExportCSV()
    {
        try {
            check_ajax_referer('vnx_filter_posts_nonce', 'nonce');

            if (!current_user_can('edit_posts')) {
                wp_send_json_error('Bạn không có quyền sử dụng chức năng này.');
                return;
            }

            set_time_limit(120);

            $keyword     = sanitize_textarea_field($_POST['keyword'] ?? '');
            $searchScope = sanitize_text_field($_POST['search_scope'] ?? 'all');
            $category    = intval($_POST['category'] ?? 0);
            $postStatus  = sanitize_text_field($_POST['post_status'] ?? 'any');
            $dateFrom    = sanitize_text_field($_POST['date_from'] ?? '');
            $dateTo      = sanitize_text_field($_POST['date_to'] ?? '');

            // Lấy toàn bộ kết quả (không phân trang)
            $args = $this->buildQueryArgs($keyword, $searchScope, $category, $postStatus, $dateFrom, $dateTo, 1, -1);

            add_filter('posts_join', [$this, 'customPostsJoin'], 10, 2);
            add_filter('posts_search', [$this, 'customPostsSearch'], 10, 2);
            $query = new WP_Query($args);
            remove_filter('posts_search', [$this, 'customPostsSearch'], 10);
            remove_filter('posts_join', [$this, 'customPostsJoin'], 10);

            $posts = $this->formatPosts($query->posts);

            // Tạo CSV data
            $csvRows = [];
            $csvRows[] = ['ID', 'Tiêu đề', 'Slug', 'URL', 'Category', 'Tác giả', 'Ngày đăng', 'Ngày sửa', 'Trạng thái'];

            foreach ($posts as $post) {
                $csvRows[] = [
                    $post['id'],
                    $post['title'],
                    $post['slug'],
                    $post['permalink'],
                    $post['categories'],
                    $post['author'],
                    $post['date'],
                    $post['modified_date'],
                    $post['status'],
                ];
            }

            wp_send_json_success([
                'csv_data' => $csvRows,
                'total'    => count($posts),
            ]);
        } catch (\Throwable $e) {
            error_log('[VNX_FilterPosts_Center] ajaxExportCSV error: ' . $e->getMessage());
            wp_send_json_error('Đã xảy ra lỗi khi xuất CSV: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Build WP_Query args từ các filter params.
     */
    private function buildQueryArgs(
        string $keyword,
        string $searchScope,
        int $category,
        string $postStatus,
        string $dateFrom,
        string $dateTo,
        int $page,
        int $perPage
    ): array {
        $validStatuses = ['publish', 'draft', 'pending', 'private', 'trash', 'any'];
        if (!in_array($postStatus, $validStatuses)) {
            $postStatus = 'any';
        }

        $args = [
            'post_type'      => 'post',
            'post_status'    => $postStatus,
            'posts_per_page' => $perPage,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if (is_array($searchScope)) {
            $this->search_scope = $searchScope;
        } else {
            $this->search_scope = array_filter(array_map('trim', explode(',', $searchScope)));
        }
        if (empty($this->search_scope)) {
            $this->search_scope = ['all'];
        }

        // Search keyword
        if (!empty($keyword)) {
            $keyword_normalized = str_replace(array("\r\n", "\r", "\n"), ',', $keyword);
            $this->search_keywords = array_filter(array_map('trim', explode(',', $keyword_normalized)));
            if (!empty($this->search_keywords)) {
                $args['s'] = implode(' ', $this->search_keywords);
            }
        } else {
            $this->search_keywords = [];
        }

        // Filter by category
        if ($category > 0) {
            $args['cat'] = $category;
        }

        // Date range filter
        $dateQuery = [];
        if (!empty($dateFrom) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            [$y, $m, $d] = explode('-', $dateFrom);
            $dateQuery['after'] = [
                'year'  => (int) $y,
                'month' => (int) $m,
                'day'   => (int) $d,
            ];
        }
        if (!empty($dateTo) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            [$y, $m, $d] = explode('-', $dateTo);
            $dateQuery['before'] = [
                'year'  => (int) $y,
                'month' => (int) $m,
                'day'   => (int) $d,
            ];
        }
        if (!empty($dateQuery)) {
            $dateQuery['inclusive'] = true;
            $dateQuery['column']   = 'post_date';
            $args['date_query']    = [$dateQuery];
        }

        return $args;
    }

    /**
     * Custom search SQL for multiple exact keywords with OR logic.
     */
    public function customPostsSearch(string $search, $wp_query): string
    {
        global $wpdb;

        if (empty($this->search_keywords) || !$wp_query->is_search()) {
            return $search;
        }

        $search_clauses = [];
        foreach ($this->search_keywords as $keyword) {
            if (empty($keyword)) {
                continue;
            }
            $like = '%' . $wpdb->esc_like($keyword) . '%';
            $escaped_regex = $this->mysqlRegexEscape($keyword);

            $keyword_clauses = [];
            foreach ($this->search_scope as $scope) {
                switch ($scope) {
                    case 'h1':
                        $keyword_clauses[] = $wpdb->prepare(
                            "(({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_content REGEXP %s))",
                            $like,
                            '<h1[^>]*>[^<]*' . $escaped_regex . '[^<]*</h1>'
                        );
                        break;
                    case 'h2':
                        $keyword_clauses[] = $wpdb->prepare(
                            "({$wpdb->posts}.post_content REGEXP %s)",
                            '<h2[^>]*>[^<]*' . $escaped_regex . '[^<]*</h2>'
                        );
                        break;
                    case 'seo_title':
                        $keyword_clauses[] = $wpdb->prepare(
                            "((pm_seo.meta_value LIKE %s) OR (pm_seo.meta_value IS NULL AND {$wpdb->posts}.post_title LIKE %s))",
                            $like,
                            $like
                        );
                        break;
                    case 'content':
                        $keyword_clauses[] = $wpdb->prepare(
                            "({$wpdb->posts}.post_content LIKE %s)",
                            $like
                        );
                        break;
                    case 'all':
                    default:
                        $keyword_clauses[] = $wpdb->prepare(
                            "(({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_content LIKE %s) OR ({$wpdb->posts}.post_excerpt LIKE %s) OR (pm_seo.meta_value LIKE %s))",
                            $like,
                            $like,
                            $like,
                            $like
                        );
                        break;
                }
            }

            if (!empty($keyword_clauses)) {
                $search_clauses[] = "(" . implode(' OR ', $keyword_clauses) . ")";
            }
        }

        if (!empty($search_clauses)) {
            $search = " AND (" . implode(' OR ', $search_clauses) . ") ";
        }

        return $search;
    }

    /**
     * Custom join for SEO metadata tables when searching within SEO titles.
     */
    public function customPostsJoin($join, $wp_query)
    {
        global $wpdb;

        if (!$wp_query->is_search()) {
            return $join;
        }

        $needs_seo = false;
        foreach ($this->search_scope as $scope) {
            if (in_array($scope, ['seo_title', 'all'], true)) {
                $needs_seo = true;
                break;
            }
        }

        if ($needs_seo) {
            $join .= " LEFT JOIN {$wpdb->postmeta} AS pm_seo ON ({$wpdb->posts}.ID = pm_seo.post_id AND (pm_seo.meta_key = 'rank_math_title' OR pm_seo.meta_key = '_yoast_wpseo_title')) ";
        }

        return $join;
    }

    /**
     * Escape regular expression special characters for MySQL REGEXP compatibility.
     */
    private function mysqlRegexEscape(string $str): string
    {
        $chars = ['\\', '^', '$', '.', '|', '?', '*', '+', '(', ')', '[', ']', '{', '}'];
        $escaped = $str;
        foreach ($chars as $char) {
            $escaped = str_replace($char, '\\\\' . $char, $escaped);
        }
        return $escaped;
    }

    /**
     * Format WP_Post objects thành mảng data cho frontend.
     */
    private function formatPosts(array $wpPosts): array
    {
        $posts = [];

        foreach ($wpPosts as $post) {
            $categories = get_the_category($post->ID);
            $catNames   = array_map(function ($cat) {
                return $cat->name;
            }, $categories);

            $author = get_the_author_meta('display_name', $post->post_author);

            $statusLabels = [
                'publish' => 'Published',
                'draft'   => 'Draft',
                'pending' => 'Pending',
                'private' => 'Private',
                'trash'   => 'Trash',
            ];

            $posts[] = [
                'id'            => $post->ID,
                'title'         => $post->post_title,
                'slug'          => $post->post_name,
                'permalink'     => get_permalink($post->ID),
                'categories'    => implode(', ', $catNames),
                'author'        => $author ?: '—',
                'date'          => get_the_date('Y-m-d H:i', $post->ID),
                'modified_date' => get_the_modified_date('Y-m-d H:i', $post->ID),
                'status'        => $statusLabels[$post->post_status] ?? $post->post_status,
            ];
        }

        return $posts;
    }
}

try {
    new VNX_FilterPosts_Center();
} catch (\Throwable $e) {
    error_log('[VNX_FilterPosts_Center] Fatal init error: ' . $e->getMessage());
}