<?php
// Giới hạn thời gian chạy tối đa 60s cho mỗi lượt cron.
ini_set('max_execution_time', '60');

$path = dirname(__DIR__) . '/tools/vietnix-search-ai.php';
if (!file_exists($path)) {
    die("File not found: $path");
}

try {
    $wp_path = dirname(__FILE__, 5);
    $wp_load = $wp_path . '/wp-load.php';
    if (file_exists($wp_load)) {
        require_once($wp_load);
    } else {
        error_log('Không tìm thấy file wp-load.php tại: ' . $wp_load);
    }

    // Kiểm tra xem WordPress đã được load chưa
    if (!defined('ABSPATH')) {
        error_log('WordPress load failed');
    }

    require_once __DIR__ . '/vnx-cron-guard.php';
    vnx_cron_require_active_plugin_Center('update search AI');

    // Chỉ require vietnix-search-ai.php sau khi đã load WordPress
    require_once $path;

    class VNXSearchAICronjob_Center
    {
        public function __construct()
        {
            try {
                $option = get_option('vnx_search_ai');
                $syncTime = isset($option['syncTime']) ? $option['syncTime'] : '';
                if (!$syncTime) {
                    error_log('CRON VNXSearchAI_Center:: Không tìm thấy syncTime trong option vnx_search_ai');
                    return;
                }
                date_default_timezone_set('Asia/Ho_Chi_Minh');
                $current_time = date('H:i');
                if ($current_time === $syncTime) {
                    error_log('VNX_CRON: Tới giờ đồng bộ syncTime=' . $syncTime . ', chạy save_post_embeddings_today()');
                    try {
                        $search_ai = new VNXSearchAI_Center();
                        $search_ai->save_post_embeddings_today();
                    } catch (\Throwable $e) {
                        error_log('CRON VNXSearchAI_Center:: Lỗi khi gọi save_post_embeddings_today: ' . $e->getMessage());
                    }
                } else {
                    error_log('CRON VNXSearchAI_Center:: Chưa tới giờ đồng bộ. Hiện tại: ' . $current_time . ', syncTime=' . $syncTime);
                }
            } catch (\Throwable $ex) {
                error_log('CRON VNXSearchAI_Center:: Lỗi hệ thống: ' . $ex->getMessage());
            }
        }
    }

    new VNXSearchAICronjob_Center();
} catch (\Throwable $fatal) {
    error_log('VNX_CRON: Lỗi ngoài class: ' . $fatal->getMessage());
}