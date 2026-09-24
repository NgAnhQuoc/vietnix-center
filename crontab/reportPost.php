<?php

// Giới hạn thời gian chạy tối đa 60s cho mỗi lượt cron.
ini_set('max_execution_time', '60');

// Xác định đường dẫn WordPress
$wp_path = dirname(__FILE__, 5);
$wp_load = $wp_path . '/wp-load.php';
if (file_exists($wp_load)) {
    require_once($wp_load);
} else {
    error_log('Không tìm thấy file wp-load.php tại: ' . $wp_load);
    exit();
}

require_once __DIR__ . '/vnx-cron-guard.php';
vnx_cron_require_active_plugin_Center('report post');

if (!class_exists('VietnixRequiesCenter\\VietnixReportPosts')) {
    error_log('Không tìm thấy class VietnixRequiesCenter\\VietnixReportPosts');
    exit();
}

use VietnixRequiesCenter\VietnixReportPosts;
use HelperCenter\DiscordBot;

class VietnixReportPostsCronjob_Center
{
    public $dataOption = [];
    private $vnx_report_posts;

    public function __construct()
    {
        $this->vnx_report_posts = new VietnixReportPosts();
        $option = $this->vnx_report_posts->getSetting();
        $this->dataOption = json_decode($option, true);

        $this->checkTimeCron();
    }
    /**
     * Lấy danh sách bài viết
     * 
     * @param string $startTime Thời gian bắt đầu
     * @param string $endTime Thời gian kết thúc
     * @return WP_Query|false
     */
    private function getListPost($startTime, $endTime)
    {
        try {
            if (empty($startTime) || empty($endTime)) {
                error_log('Thời gian không hợp lệ');
                return false;
            }

            // lấy những bài có date_first_public thoả mãn 
            $args = [
                'post_type'      => 'post',
                // 'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_query'     => [
                    [
                        'key'     => 'date_first_public',
                        'value'   => [$startTime, $endTime],
                        'compare' => 'BETWEEN',
                        'type'    => 'DATETIME',
                    ],
                ],
            ];
            return new \WP_Query($args);
        } catch (Exception $ex) {
            error_log("Lỗi getListPost: " . $ex->getMessage());
            return false;
        }
    }

    /**
     * Lấy thống kê bài viết theo tác giả
     * 
     * @param WP_Query $query Query bài viết
     * @return array
     */
    private function getWriterStats($query)
    {
        try {
            if (!$query || !$query->have_posts()) {
                return ['total' => 0, 'writers' => []];
            }

            $total_posts = $query->post_count;
            $writer_count = [];

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                if (get_field('writer', $post_id, false, false)) {
                    $user_id = get_field('writer', $post_id, false, false);
                    if ($user_id != '') {
                        $writer_name = get_userdata($user_id)->display_name;
                        if (!isset($writer_count[$writer_name])) {
                            $writer_count[$writer_name] = 0;
                        }
                        $writer_count[$writer_name]++;
                    }
                }
            }

            wp_reset_postdata();

            return [
                'total' => $total_posts,
                'writers' => $writer_count,
            ];
        } catch (Exception $ex) {
            error_log("Lỗi getWriterStats: " . $ex->getMessage());
            return ['total' => 0, 'writers' => []];
        }
    }



    /**
     * Gửi báo cáo ngày
     */
    public function sendDailyPostReport()
    {
        try {

            if (empty($this->dataOption['linkhooks'])) {
                throw new Exception('Webhook Discord chưa được cấu hình');
            }

            $linkhooks = $this->dataOption['linkhooks'];
            $today = date('Y-m-d');
            $query = $this->getListPost($today . ' 00:00:00', $today . ' 23:59:59');

            if (!$query) {
                throw new Exception('Không thể lấy danh sách bài viết');
            }

            $stats = $this->getWriterStats($query);

            $title = "📢 Báo cáo bài viết ngày " . date('d/m/Y H:i') . "\n\n";
            $message = "Tổng số bài publish: {$stats['total']}\n\n";

            foreach ($stats['writers'] as $writer => $count) {
                $message .= "✍️ $writer: $count\n";
            }


            // Gửi thông báo đến Discord
            DiscordBot::sendMessageByWebhook(
                $linkhooks,
                $title,
                $message,
                '#00b0f4'
            );
        } catch (Exception $ex) {
            error_log("Lỗi báo cáo hàng ngày: " . $ex->getMessage());
        }
    }

    /**
     * Gửi báo cáo tuần
     */
    public function sendWeeklyPostReport($date = 'saturday')
    {
        try {

            if (empty($this->dataOption['linkhooks'])) {
                throw new Exception('Webhook Discord chưa được cấu hình');
            }

            $linkhooks = $this->dataOption['linkhooks'];
            $start_of_week = date('Y-m-d 00:00:00', strtotime('last ' . $date));
            $end_of_week = date('Y-m-d 23:59:59', strtotime('this ' . $date));

            $query = $this->getListPost($start_of_week, $end_of_week);

            if (!$query) {
                throw new Exception('Không thể lấy danh sách bài viết');
            }

            $stats = $this->getWriterStats($query);

            $title = "📢 Báo cáo bài viết tuần (" . date('d/m/Y', strtotime($start_of_week)) . " - " . date('d/m/Y', strtotime($end_of_week)) . ")\n\n";
            $message = "Tổng số bài publish: {$stats['total']}\n\n";

            foreach ($stats['writers'] as $writer => $count) {
                $message .= "✍️ $writer: $count\n";
            }

            // Gửi thông báo đến Discord
            DiscordBot::sendMessageByWebhook(
                $linkhooks,
                $title,
                $message,
                '#00b0f4'
            );
        } catch (Exception $ex) {
            error_log("Lỗi báo cáo hàng tuần: " . $ex->getMessage());
        }
    }

    /**
     * Kiểm tra thời gian chạy cron
     */
    public function checkTimeCron()
    {
        try {
            if (!$this->dataOption) {
                throw new Exception('Không tìm thấy cấu hình báo cáo');
            }

            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $current_time = date('H:i');


            // Nếu thời gian bằng với thời gian cấu hình và ngày bằng với ngày hiện tại thì gửi báo cáo
            if (isset($this->dataOption['daily']['time']) && $current_time === $this->dataOption['daily']['time']) {
                $this->sendDailyPostReport();
                echo "send daily";
            }


            if (
                $current_time === $this->dataOption['weekly']['time'] &&
                $this->dataOption['weekly']['day'] === strtolower(date('l'))
            ) {
                $this->sendWeeklyPostReport($this->dataOption['weekly']['day']);
                echo "send weekly";
            }
        } catch (Exception $ex) {
            error_log("Lỗi kiểm tra thời gian: " . $ex->getMessage());
        }
    }
}

new VietnixReportPostsCronjob_Center();

