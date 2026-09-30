<?php

/** Vietnix Cache Scheduler: hẹn giờ xoá cache LiteSpeed rồi cào lại từng nhóm cache (desktop/mobile, guest/vary). */

use HelperCenter\DiscordBot;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

class VietnixCacheScheduler_Center
{
    private const OPTION_NAME = 'vnx_cache_scheduler_jobs_center';
    private const AUTO_OPTION = 'vnx_cache_scheduler_auto_enabled_center';
    private const NOTIFY_OPTION = 'vnx_cache_scheduler_notify_center';
    private const LS_PLUGIN_FILE = 'litespeed-cache/litespeed-cache.php';
    private const LOCK_KEY = 'vnx_cache_scheduler_lock_center';
    private const LOCK_OPTION = 'vnx_cs_lock_center';
    private const CRON_HOOK = 'vnx_cache_scheduler_tick_center';
    private const CRON_INTERVAL = 'vnx_five_minutes';

    /** Tiền tố transient giữ token của lượt "Chạy ngay" đang chờ tiến trình nền nhận. */
    private const RUN_TOKEN_PREFIX = 'vnx_cache_scheduler_run_';

    /** Tiền tố transient giữ token dùng một lần của mỗi request purge lẻ. */
    private const PURGE_TOKEN_PREFIX = 'vnx_cache_scheduler_purge_';

    /** Tiền tố transient giữ số URL đã xử lý, để trang quản trị đọc được từ request khác. */
    private const PROGRESS_PREFIX = 'vnx_cache_scheduler_progress_';

    /** Bản sao tiến độ nằm thẳng trong bảng options. */
    private const PROGRESS_OPTION_PREFIX = 'vnx_cs_progress_center_';

    /** Cờ huỷ do trang quản trị đặt, tiến trình đang chạy đọc rồi tự dừng. */
    private const CANCEL_PREFIX = 'vnx_cache_scheduler_cancel_';

    /** Bản sao cờ huỷ nằm thẳng trong bảng options. */
    private const CANCEL_OPTION_PREFIX = 'vnx_cs_cancel_center_';

    /** Thời gian chờ tiến trình tự dừng sau khi bấm Huỷ, quá hạn thì cho phép dừng cứng. */
    private const CANCEL_GRACE = 90;

    /** Nhịp đọc lại cờ huỷ từ cơ sở dữ liệu, để vòng cào không nện DB mỗi lần thử. */
    private const CANCEL_POLL_EVERY = 2;

    /** Tiến trình còn sống thì cứ vài giây lại chạm nhịp tim vào transient tiến độ. */
    private const RUN_HEARTBEAT_STALE_AFTER = 180;

    /** Nhịp chạm heartbeat trong lúc chờ mạng, đủ dày để không bị coi là chết, đủ thưa để không nện DB. */
    private const HEARTBEAT_TOUCH_EVERY = 5;

    /** Chặn tần suất bộ dò job treo chạy ké trên các trang admin khác. */
    private const WATCHDOG_THROTTLE_KEY = 'vnx_cache_scheduler_watchdog_center';
    private const WATCHDOG_THROTTLE_TTL = 30;
    private const SEGMENT_TIME_BUDGET = 150;
    private const RUN_MAX_AUTO_RESUMES = 20;
    private const RUN_MAX_STUCK_RESUMES = 2;

    /** Job kẹt ở trạng thái running quá lâu (tiến trình nền bị kill) thì coi như thất bại. Phải >= LOCK_TTL. */
    private const RUN_STALE_AFTER = 3600;

    /** Thời gian chờ tiến trình nền nhận token trước khi kết luận request loopback không tới nơi. */
    private const RUN_PICKUP_TIMEOUT = 5;

    /** Job "running" mà không có tiến trình nào giữ lock quá ngần này giây là job mồ côi.
     *  Giữ dưới trần 60s mà proxy/LSAPI hay dùng, để watchdog kết luận trước khi hạ tầng cắt request. */
    private const RUN_ORPHAN_AFTER = 55;

    /** Ngân sách thời gian phần warm, dùng chung cho mọi cách chạy (CLI, web/loopback). */
    private const WARM_TIME_BUDGET = 3300;

    /** Giới hạn cứng set_time_limit() của phần warm, chừa thêm chỗ so với WARM_TIME_BUDGET để kịp gửi thông báo. */
    private const WARM_HARD_LIMIT = 3480;

    /** TTL của lock, dài hơn WARM_HARD_LIMIT để lock không hết hạn giữa lượt chạy. */
    private const LOCK_TTL = 3600;

    /** Nhịp làm tươi khoá theo nhịp tim; TTL cả giờ nên không cần ghi lại mỗi vài giây. */
    private const LOCK_TOUCH_EVERY = 60;

    /** Số lần cào lại 1 nhóm nếu verify chưa thấy hit. */
    private const WARM_MAX_ATTEMPTS = 3;

    /** Số URL cào đồng thời trong 1 lô (qua curl_multi), chỉnh theo tải server chịu được. */
    private const WARM_CONCURRENCY = 4;

    /** Timeout mỗi request warm/verify. Trang render lạnh mất ~20s nên 30s quá sát trần. */
    private const WARM_REQUEST_TIMEOUT = 90;

    /** Tran thoi gian cho MOT URL, tinh ca moi nhom cache va moi lan thu lai. */
    private const WARM_URL_TIME_LIMIT = 90;

    /** Thời gian tối thiểu còn lại mới dám xoá cache một URL. */
    private const WARM_MIN_BUDGET_PER_URL = 90;

    /** Timeout curl của LiteSpeed Crawler; mặc định 30s khiến trang chậm bị xếp vào blacklist. */
    private const CRAWLER_TIMEOUT = 90;

    /** Nghỉ giữa 2 URL khi cào tuần tự (job "toàn bộ site", concurrency = 1). */
    private const WARM_SEQUENTIAL_GAP_USEC = 200000;

    /** Số URL tối đa liệt kê trong một thông báo, phần dư gộp thành một dòng "… và N URL khác". */
    private const NOTIFY_URL_LIMIT = 10;

    /** Độ dài tối đa của một tin Discord do plugin tự cắt. */
    private const NOTIFY_CHUNK_LIMIT = 3500;

    private const NOTIFY_TITLES = [
        'purge_start' => 'Bắt đầu xoá cache: ',
        'warm_start' => 'Bắt đầu cào lại cache: ',
        'done' => 'Đã xoá cache và cào cache xong: ',
    ];

    /** Màu embed Discord theo từng loại thông báo: xanh dương = đang chạy, xanh lá = xong, vàng = cảnh báo, đỏ = lỗi. */
    private const NOTIFY_COLORS = [
        'purge_start' => '38a7ff',
        'warm_start' => '38a7ff',
        'done' => '2ecc71',
        'warn' => 'f39c12',
        'error' => 'e74c3c',
        'cancelled' => '95a5a6',
    ];

    /** Giả lập request của browser thật (UA + header) thay vì UA bot riêng, để response được cache. */
    private const WARM_UA_PROFILES = [
        'desktop' => [
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36',
            'sec_ch_ua' => '"Not=A?Brand";v="99", "Google Chrome";v="151", "Chromium";v="151"',
            'sec_ch_ua_mobile' => '?0',
            'sec_ch_ua_platform' => '"macOS"',
        ],
        'mobile' => [
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1',
            'sec_ch_ua' => null,
            'sec_ch_ua_mobile' => null,
            'sec_ch_ua_platform' => null,
        ],
    ];

    private static $instance = null;

    /** Đích resolve cho request đang gửi: ['ip' => ..., 'port' => 443|80] hoặc null (đi qua DNS bình thường). */
    private $resolveTarget = null;

    /** Memo giá trị vary theo từng profile UA, mỗi profile chỉ hỏi server 1 lần. */
    private $guestVaryCache = [];

    /** Số URL đã xử lý của lượt chạy trong request này; null khi không có lượt nào đang chạy. */
    private $progress = null;

    /** Bật khi tiến trình đọc được cờ huỷ, để lượt chạy kết thúc ở trạng thái "đã huỷ". */
    private $cancelled = false;

    /** Bật khi khoá toàn cục biến mất giữa lượt chạy: dừng chặng này rồi bàn giao, KHÔNG huỷ lượt. */
    private $lockLost = false;

    /** Mã của lượt chạy mà tiến trình này đang phục vụ. */
    private $runId = '';

    /** microtime lần cuối hỏi cơ sở dữ liệu về cờ huỷ, để giãn nhịp đọc. */
    private $cancelCheckedAt = null;

    /** Bật khi tiến trình này đang giữ khoá toàn cục, để nhịp tim làm tươi luôn khoá. */
    private $lockOwned = false;

    /** Mã ngẫu nhiên của lượt giữ khoá này, để chỉ nhả đúng khoá của mình. */
    private $lockToken = '';

    /** Lần cuối làm tươi khoá, để nhịp tim không ghi lại khoá mỗi vài giây. */
    private $lockTouchedAt = null;

    /** Mọi trục trặc của lượt chạy hiện tại, gom theo nhóm để cuối lượt báo cáo một lần. */
    private $runIssues = ['purge' => [], 'warm' => [], 'skipped' => []];

    /** Tổng số URL của lượt chạy hiện tại, để báo cáo nói được "lỗi mấy trên mấy". */
    private $runTotalUrls = 0;

    /** Số URL đã xử lý tại lúc chặng hiện tại CHỦ ĐỘNG dừng để bàn giao. */
    private $handoffAt = null;

    /** Số trang lỗi của riêng chặng này, để cộng dồn sang bản ghi lịch khi bàn giao. */
    private $segmentFailed = 0;

    /** Số trang lỗi mà các chặng trước đã cộng dồn lại, để chặng cuối báo cáo đủ. */
    private $runCarriedFailures = 0;

    // --- Bootstrap ---

    /** Lấy instance đang chạy, tạo mới nếu chưa có. */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        // Gọi new lần hai (vd: file cron bản cũ) thì thoát sớm, không đăng ký hook lại.
        if (self::$instance !== null) {
            return;
        }
        self::$instance = $this;

        // LSCWP đọc hằng này lúc crawler chạy (Crawler::_get_curl_options).
        if (!defined('LITESPEED_CRAWLER_TIMEOUT')) {
            define('LITESPEED_CRAWLER_TIMEOUT', self::CRAWLER_TIMEOUT);
        }

        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_ajax_vnx_cache_scheduler_list', [$this, 'ajaxList']);
        add_action('wp_ajax_vnx_cache_scheduler_save', [$this, 'ajaxSave']);
        add_action('wp_ajax_vnx_cache_scheduler_delete', [$this, 'ajaxDelete']);
        add_action('wp_ajax_vnx_cache_scheduler_toggle', [$this, 'ajaxToggle']);
        add_action('wp_ajax_vnx_cache_scheduler_toggle_auto', [$this, 'ajaxToggleAuto']);
        add_action('wp_ajax_vnx_cache_scheduler_run_now', [$this, 'ajaxRunNow']);
        add_action('wp_ajax_vnx_cache_scheduler_cancel', [$this, 'ajaxCancel']);
        add_action('wp_ajax_vnx_cache_scheduler_save_notify', [$this, 'ajaxSaveNotify']);
        add_action('wp_ajax_vnx_cache_scheduler_test_notify', [$this, 'ajaxTestNotify']);
        add_action('wp_ajax_vnx_cache_scheduler_public_urls', [$this, 'ajaxPublicUrls']);
        // Request loopback không có cookie đăng nhập nên phải đăng ký cả nopriv.
        add_action('wp_ajax_vnx_cache_scheduler_execute', [$this, 'ajaxExecuteQueued']);
        add_action('wp_ajax_nopriv_vnx_cache_scheduler_execute', [$this, 'ajaxExecuteQueued']);
        add_action('wp_ajax_vnx_cache_scheduler_purge_one', [$this, 'ajaxPurgeOne']);
        add_action('wp_ajax_nopriv_vnx_cache_scheduler_purge_one', [$this, 'ajaxPurgeOne']);
        add_filter('cron_schedules', [$this, 'registerCronInterval']);
        add_action(self::CRON_HOOK, [$this, 'runDueJobs']);
        add_action('admin_init', [$this, 'watchStaleRuns']);

        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), self::CRON_INTERVAL, self::CRON_HOOK);
        }
    }

    /** Dọn job treo ké trên mọi trang quản trị, không chỉ trang lịch hẹn. */
    public function watchStaleRuns()
    {
        if (wp_doing_ajax() || wp_doing_cron() || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return;
        }

        $jobs = $this->getJobs();

        // Khoá còn kẹt mà không lịch nào đang chạy cũng là một dạng treo.
        if ((!$this->hasRunningJob($jobs) && $this->lockHolder() === null)
            || get_transient(self::WATCHDOG_THROTTLE_KEY)
        ) {
            return;
        }

        set_transient(self::WATCHDOG_THROTTLE_KEY, time(), self::WATCHDOG_THROTTLE_TTL);

        $this->releaseStaleRuns($jobs);
    }

    private function hasRunningJob(array $jobs)
    {
        foreach ($jobs as $job) {
            if (is_array($job) && ($job['last_status'] ?? '') === 'running') {
                return true;
            }
        }

        return false;
    }

    public function registerCronInterval($schedules)
    {
        $schedules = is_array($schedules) ? $schedules : [];

        $schedules[self::CRON_INTERVAL] = [
            'interval' => 300,
            'display' => 'Mỗi 5 phút (Vietnix Cache Scheduler)',
        ];

        return $schedules;
    }

    // --- LiteSpeed detection ---

    public function getLiteSpeedStatus()
    {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (!file_exists(WP_PLUGIN_DIR . '/' . self::LS_PLUGIN_FILE)) {
            return 'missing';
        }

        if (!is_plugin_active(self::LS_PLUGIN_FILE)) {
            return 'inactive';
        }

        return 'active';
    }

    private function isMobileCacheEnabled()
    {
        try {
            return class_exists('\LiteSpeed\Conf')
                && (bool) \LiteSpeed\Conf::cls()->conf(\LiteSpeed\Base::O_CACHE_MOBILE);
        } catch (\Throwable $e) {
            return false;
        }
    }

    // --- Admin scripts ---

    public function enqueueScripts()
    {
        if (!$this->isToolsPage()) {
            return;
        }

        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        $jsRelPath = 'tools/inc/js/vietnix-cache-scheduler.js';
        $jsVersion = file_exists(VNX_PLUGIN_PATH_CENTER . $jsRelPath) ? (string) filemtime(VNX_PLUGIN_PATH_CENTER . $jsRelPath) : '1.0';
        wp_enqueue_script('vietnix-cache-scheduler', VNX_PLUGIN_URL_CENTER . $jsRelPath, ['jquery', 'vuejs-library-center'], $jsVersion, true);

        wp_localize_script('vietnix-cache-scheduler', 'vnxCacheSchedulerData', [
            'nonce' => wp_create_nonce('vnx_cache_scheduler_nonce'),
        ]);
    }

    private function isToolsPage()
    {
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';

        return $page !== '' && strpos($page, VNX_PLUGIN_SLUG_CENTER) !== false;
    }

    // --- AJAX handlers ---

    public function ajaxList()
    {
        $this->authorize();

        wp_send_json_success([
            'jobs' => $this->attachProgress($this->releaseStaleRuns($this->getJobs())),
            'ls_status' => $this->getLiteSpeedStatus(),
            'auto_enabled' => $this->isAutoEnabled(),
            'notify' => $this->getNotifySettings(),
        ]);
    }

    /** Trả danh sách URL đang public (chỉ để xem trước bên UI, không tự đổ vào ô "URL cụ thể"). */
    public function ajaxPublicUrls()
    {
        $this->authorize();

        $urls = $this->getPublicPageUrls();

        wp_send_json_success([
            'urls' => $urls,
            'total' => count($urls),
        ]);
    }

    public function ajaxSave()
    {
        $this->authorize();

        $raw = isset($_POST['job']) && is_array($_POST['job']) ? wp_unslash($_POST['job']) : [];
        $job = $this->sanitizeJobInput($raw);

        if (is_string($job)) {
            wp_send_json_error($job);
        }

        $jobs = $this->releaseStaleRuns($this->getJobs());
        $id = sanitize_text_field($this->scalarString($raw['id'] ?? ''));
        $index = $this->findJobIndex($jobs, $id);

        if ($index !== null) {
            if (($jobs[$index]['last_status'] ?? '') === 'running') {
                wp_send_json_error('Lịch đang có một lượt chạy, không thể sửa lúc này.');
            }

            // Bản ghi tạo từ phiên bản cũ có thể thiếu các key này.
            $job['id'] = $id;
            $job['created_at'] = $jobs[$index]['created_at'] ?? time();
            $job['last_run_at'] = $jobs[$index]['last_run_at'] ?? null;
            $job['last_status'] = $jobs[$index]['last_status'] ?? null;
            $job['last_error'] = $jobs[$index]['last_error'] ?? '';
            $job['last_done'] = (int) ($jobs[$index]['last_done'] ?? 0);
            $job['last_total'] = (int) ($jobs[$index]['last_total'] ?? 0);
            $job['run_started_at'] = $jobs[$index]['run_started_at'] ?? null;
            $jobs[$index] = $job;
        } else {
            $job['id'] = wp_generate_uuid4();
            $job['created_at'] = time();
            $jobs[] = $job;
        }

        $this->saveJobs($jobs);

        wp_send_json_success(['jobs' => $this->attachProgress($this->getJobs())]);
    }

    public function ajaxDelete()
    {
        $this->authorize();

        $id = sanitize_text_field($this->scalarString(wp_unslash($_POST['id'] ?? '')));
        $existing = $this->releaseStaleRuns($this->getJobs());
        $index = $this->findJobIndex($existing, $id);

        if ($index !== null && ($existing[$index]['last_status'] ?? '') === 'running') {
            wp_send_json_error('Lịch đang có một lượt chạy, không thể xoá lúc này.');
        }

        $jobs = array_values(array_filter($existing, function ($job) use ($id) {
            return !is_array($job) || ($job['id'] ?? null) !== $id;
        }));

        $this->saveJobs($jobs);

        $this->forgetProgress($id);
        $this->forgetCancel($id);

        wp_send_json_success(['jobs' => $this->attachProgress($jobs)]);
    }

    public function ajaxToggle()
    {
        $this->authorize();

        $id = sanitize_text_field($this->scalarString(wp_unslash($_POST['id'] ?? '')));
        $jobs = $this->getJobs();
        $index = $this->findJobIndex($jobs, $id);

        if ($index === null) {
            wp_send_json_error('Không tìm thấy lịch hẹn.');
        }

        $turningOn = empty($jobs[$index]['enabled']);
        if ($turningOn && $this->getLiteSpeedStatus() !== 'active') {
            wp_send_json_error('LiteSpeed Cache chưa sẵn sàng, không thể bật lịch.');
        }

        $jobs[$index]['enabled'] = $turningOn;
        $jobs[$index]['next_run_at'] = $turningOn ? $this->computeNextRun($jobs[$index], time()) : null;

        $this->saveJobs($jobs);

        wp_send_json_success(['jobs' => $this->attachProgress($jobs)]);
    }

    public function ajaxToggleAuto()
    {
        $this->authorize();

        $enable = !empty($_POST['enabled']);
        if ($enable && $this->getLiteSpeedStatus() !== 'active') {
            wp_send_json_error('LiteSpeed Cache chưa sẵn sàng, không thể bật tự động.');
        }

        update_option(self::AUTO_OPTION, $enable);

        wp_send_json_success(['auto_enabled' => $enable]);
    }

    public function ajaxSaveNotify()
    {
        $this->authorize();

        $webhook = esc_url_raw($this->scalarString(wp_unslash($_POST['webhook_url'] ?? '')));
        $enabled = !empty($_POST['enabled']);

        if ($enabled && $webhook === '') {
            wp_send_json_error('Cần nhập Webhook URL trước khi bật thông báo.');
        }

        update_option(self::NOTIFY_OPTION, [
            'enabled' => $enabled,
            'webhook_url' => $webhook,
        ]);

        wp_send_json_success($this->getNotifySettings());
    }

    public function ajaxTestNotify()
    {
        $this->authorize();

        $webhook = esc_url_raw($this->scalarString(wp_unslash($_POST['webhook_url'] ?? '')));
        if ($webhook === '') {
            wp_send_json_error('Chưa nhập Webhook URL.');
        }

        $sent = DiscordBot::sendMessageByWebhook(
            $webhook,
            'Vietnix Cache Scheduler',
            "✅ Webhook hoạt động, đây là tin nhắn thử.\n\n" . $this->noticeFooter($this->currentActorLabel()),
            self::NOTIFY_COLORS['purge_start']
        );

        if (!$sent) {
            wp_send_json_error('Gửi thất bại, kiểm tra lại Webhook URL.');
        }

        wp_send_json_success('Đã gửi tin nhắn thử, kiểm tra kênh Discord.');
    }

    /** "Chạy ngay": chỉ xếp việc rồi trả lời liền. */
    public function ajaxRunNow()
    {
        $this->authorize();

        if ($this->getLiteSpeedStatus() !== 'active') {
            wp_send_json_error('LiteSpeed Cache chưa sẵn sàng.');
        }

        $id = sanitize_text_field($this->scalarString(wp_unslash($_POST['id'] ?? '')));
        $jobs = $this->releaseStaleRuns($this->getJobs());
        $index = $this->findJobIndex($jobs, $id);

        if ($index === null) {
            wp_send_json_error('Không tìm thấy lịch hẹn.');
        }

        if (($jobs[$index]['last_status'] ?? '') === 'running') {
            wp_send_json_error('Lịch này đang có một lượt chạy khác, thử lại sau ít phút.');
        }

        if ($this->lockHolder() !== null) {
            wp_send_json_error('Đang có một lượt xoá cache khác chạy, thử lại sau ít phút.');
        }

        $actor = $this->currentActorLabel();

        $jobs[$index]['last_status'] = 'running';
        $jobs[$index]['run_started_at'] = time();
        $jobs[$index]['run_id'] = $this->newRunId();
        $jobs[$index]['last_error'] = '';
        $jobs[$index] = $this->clearResumeState($jobs[$index]);
        $this->saveJobs($jobs);

        $this->forgetCancel($id);

        if ($this->dispatchBackgroundRun($id, $actor)) {
            wp_send_json_success([
                'jobs' => $this->attachProgress($this->getJobs()),
                'queued' => true,
            ]);
        }

        // Host chặn loopback: chạy thẳng trong request này để không mất tính năng.
        error_log('VNX Cache Scheduler: không gửi được request chạy nền, chạy đồng bộ lịch "' . ($jobs[$index]['label'] ?? '') . '".');

        ignore_user_abort(true);

        if (function_exists('fastcgi_finish_request')) {
            $this->respondThenContinueInBackground($jobs);
            $this->runJobById($id, $actor);

            return;
        }

        $this->runJobById($id, $actor);

        wp_send_json_success([
            'jobs' => $this->attachProgress($this->getJobs()),
            'queued' => false,
        ]);
    }

    /** Trả JSON "đã xếp việc" cho trình duyệt rồi đóng kết nối, không chờ job chạy xong. */
    private function respondThenContinueInBackground(array $jobs)
    {
        if (!headers_sent() && wp_doing_ajax()) {
            header('Content-Type: application/json; charset=' . get_option('blog_charset'));
        }

        echo wp_json_encode([
            'success' => true,
            'data' => [
                'jobs' => $this->attachProgress($jobs),
                'queued' => true,
            ],
        ]);

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        fastcgi_finish_request();
    }

    /** Huỷ lượt chạy đang diễn ra của một lịch. */
    public function ajaxCancel()
    {
        $this->authorize();

        $id = sanitize_text_field($this->scalarString(wp_unslash($_POST['id'] ?? '')));
        $force = !empty($_POST['force']) && $_POST['force'] !== 'false';
        $jobs = $this->getJobs();
        $index = $this->findJobIndex($jobs, $id);

        if ($index === null) {
            wp_send_json_error('Không tìm thấy lịch hẹn.');
        }

        $actor = $this->currentActorLabel();

        if (($jobs[$index]['last_status'] ?? '') !== 'running') {
            // Lịch không chạy nhưng khoá toàn cục còn kẹt thì mọi lượt sau đều bị từ chối.
            if ($this->sweepStaleLock($jobs)) {
                $this->forgetCancel($id);

                wp_send_json_success([
                    'jobs' => $this->attachProgress($this->getJobs()),
                    'stopped' => true,
                    'lock_cleared' => true,
                ]);
            }

            wp_send_json_error('Lịch này không có lượt chạy nào đang diễn ra.');
        }

        $runId = (string) ($jobs[$index]['run_id'] ?? '');
        $pending = $this->readCancel($id);

        // Cờ của một lượt chạy đã kết thúc không được tính là "đã xin huỷ lần trước".
        if ($pending !== null && !$this->cancelTargetsRun($pending, $runId)) {
            $this->forgetCancel($id);
            $pending = null;
        }

        // Tiến trình đã chết hẳn: dọn thẳng, không có gì để chờ.
        if (!$this->runLooksAlive($jobs[$index])) {
            $this->forceReleaseRun($id, $jobs, $index, $actor, 'Lượt chạy bị huỷ: tiến trình nền đã chết, quản trị viên dọn thủ công.');

            wp_send_json_success([
                'jobs' => $this->attachProgress($this->getJobs()),
                'stopped' => true,
            ]);
        }

        $waited = $pending !== null ? max(0, time() - (int) ($pending['at'] ?? time())) : 0;
        $graceLeft = $pending !== null ? max(0, self::CANCEL_GRACE - $waited) : self::CANCEL_GRACE;

        // Quá ân hạn mà tiến trình vẫn chạy: nó đang kẹt trong một request không trả lời.
        if ($pending !== null && ($graceLeft === 0 || $force)) {
            $this->requestCancel($id, $runId, $actor, true);

            $live = $this->readProgress($id);
            $reason = 'Lượt chạy bị dừng khẩn cấp: đã xin huỷ ' . $waited
                . ' giây trước mà tiến trình nền không dừng'
                . ($live !== null ? ' (đang ở URL thứ ' . ((int) ($live['done'] ?? 0) + 1) . ')' : '')
                . '. Khoá đã được nhả, tiến trình cũ sẽ tự thoát ở chốt kiểm tra kế tiếp.';

            $this->forceReleaseRun($id, $jobs, $index, $actor, $reason, true);

            wp_send_json_success([
                'jobs' => $this->attachProgress($this->getJobs()),
                'stopped' => true,
                'forced' => true,
            ]);
        }

        $this->requestCancel($id, $runId, $actor, false);

        $live = $this->readProgress($id);

        wp_send_json_success([
            'jobs' => $this->attachProgress($this->getJobs()),
            'stopped' => false,
            'already' => $pending !== null,
            'done' => $live !== null ? (int) ($live['done'] ?? 0) : null,
            'total' => $live !== null ? (int) ($live['total'] ?? 0) : null,
            'force_available_in' => $graceLeft,
        ]);
    }

    /** Điểm nhận request chạy nền. */
    public function ajaxExecuteQueued()
    {
        $id = sanitize_text_field($this->scalarString(wp_unslash($_POST['id'] ?? '')));
        $token = sanitize_text_field($this->scalarString(wp_unslash($_POST['token'] ?? '')));
        $queued = $id !== '' ? get_transient(self::RUN_TOKEN_PREFIX . $id) : false;

        if ($token === '' || !is_array($queued) || !hash_equals((string) ($queued['token'] ?? ''), $token)) {
            wp_die('', '', ['response' => 403]);
        }

        // Token chỉ dùng được 1 lần.
        delete_transient(self::RUN_TOKEN_PREFIX . $id);

        // Phía gửi đã ngắt kết nối ngay (blocking = false), phải chạy cho tới cùng.
        ignore_user_abort(true);

        $this->runJobById($id, (string) ($queued['actor'] ?? 'Không xác định'));

        wp_die('', '', ['response' => 200]);
    }

    /** Xoá cache đúng 1 URL rồi kết thúc. */
    public function ajaxPurgeOne()
    {
        $token = sanitize_text_field($this->scalarString(wp_unslash($_POST['token'] ?? '')));
        $stored = $token !== '' ? get_transient(self::PURGE_TOKEN_PREFIX . $token) : false;

        if (!is_string($stored) || !hash_equals($stored, $token)) {
            wp_die('', '', ['response' => 403]);
        }

        // Token chỉ dùng được 1 lần.
        delete_transient(self::PURGE_TOKEN_PREFIX . $token);

        $url = esc_url_raw($this->scalarString(wp_unslash($_POST['url'] ?? '')));
        if ($url === '') {
            wp_die('', '', ['response' => 400]);
        }

        // Không dồn thông báo admin cho một request chạy nền.
        if (!defined('LITESPEED_PURGE_SILENT')) {
            define('LITESPEED_PURGE_SILENT', true);
        }

        try {
            do_action('litespeed_purge_url', $url);
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: xoá cache "' . $url . '" thất bại: ' . $e->getMessage());
            wp_die('', '', ['response' => 500]);
        }

        // LSCWP đã gọi header() ở đây; wp_die đẩy header đó ra cho LSWS xử lý.
        wp_die('', '', ['response' => 200]);
    }

    /** Gửi request loopback không chờ phản hồi để lượt chạy diễn ra ở tiến trình khác. */
    private function dispatchBackgroundRun($jobId, $actor)
    {
        $token = wp_generate_password(32, false);

        set_transient(self::RUN_TOKEN_PREFIX . $jobId, [
            'token' => $token,
            'actor' => $actor,
        ], 5 * MINUTE_IN_SECONDS);

        $response = wp_remote_post(admin_url('admin-ajax.php'), [
            'timeout' => 1,
            // Không chờ job chạy xong, chỉ cần request được nhận.
            'blocking' => false,
            // Loopback thường đi qua cert nội bộ/self-signed.
            'sslverify' => false,
            'cookies' => [],
            'body' => [
                'action' => 'vnx_cache_scheduler_execute',
                'id' => $jobId,
                'token' => $token,
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('VNX Cache Scheduler: gửi request chạy nền thất bại: ' . $response->get_error_message());
            delete_transient(self::RUN_TOKEN_PREFIX . $jobId);

            return false;
        }

        if (!$this->waitForRunPickup($jobId)) {
            error_log('VNX Cache Scheduler: request chạy nền không tới được ' . admin_url('admin-ajax.php')
                . ' sau ' . self::RUN_PICKUP_TIMEOUT . 's (không ai nhận token), chuyển sang chạy đồng bộ.');
            // Xoá token trước khi chạy đồng bộ: request tới trễ sẽ bị 403 thay vì chạy chồng.
            delete_transient(self::RUN_TOKEN_PREFIX . $jobId);

            return false;
        }

        return true;
    }

    /** Chờ tới khi tiến trình nền xoá token (dấu hiệu nó đã nhận việc). */
    private function waitForRunPickup($jobId)
    {
        $key = self::RUN_TOKEN_PREFIX . $jobId;
        $deadline = microtime(true) + self::RUN_PICKUP_TIMEOUT;

        do {
            usleep(250000);

            // Phải xoá cache cục bộ mới đọc được thay đổi do tiến trình kia ghi.
            $this->bustTransientCache($key);

            if (!get_transient($key)) {
                return true;
            }
        } while (microtime(true) < $deadline);

        return false;
    }

    /** Chạy 1 lịch theo id rồi ghi lại trạng thái. Dùng cho cả chạy nền lẫn chạy đồng bộ dự phòng. */
    private function runJobById($id, $actor)
    {
        $lockHolder = $this->lockHolder();

        if ($lockHolder !== null && $lockHolder === (string) $id) {
            error_log('VNX Cache Scheduler: bỏ qua chặng trùng của lịch ' . $id . ', chặng trước vẫn đang giữ khoá.');

            return;
        }

        if ($lockHolder !== null) {
            $this->markJobFinished($id, 'error', $actor, 'Đang có một lượt xoá cache khác chạy, lượt này bị bỏ qua.');

            return;
        }

        $bootJobs = $this->getJobs();
        $bootIndex = $this->findJobIndex($bootJobs, $id);

        if ($bootIndex === null) {
            return;
        }

        $this->runId = (string) ($bootJobs[$bootIndex]['run_id'] ?? '');

        $pendingCancel = $this->readCancel($id);

        if ($this->cancelTargetsRun($pendingCancel, $this->runId)) {
            $this->markJobFinished(
                $id,
                'cancelled',
                $actor,
                'Lượt chạy bị huỷ từ trang quản trị trước khi chặng kế tiếp kịp khởi động.'
            );

            return;
        }

        // Cờ sót của một lượt chạy đã kết thúc thì mới được phép xoá.
        if ($pendingCancel !== null) {
            $this->forgetCancel($id);
        }

        // Lock mang id của lịch để releaseStaleRuns phân biệt job đang chạy thật với job kẹt.
        $this->acquireLock($id);

        // Nhịp tim phải có ngay từ giây đầu, trước cả khi biết tổng số trang.
        $this->startProgress($id, 0);

        $status = 'success';
        $reason = '';
        $finished = false;

        // Fatal error, hết memory_limit hay hết max_execution_time đều không chạy finally.
        register_shutdown_function(function () use ($id, $actor, &$finished) {
            if ($finished) {
                return;
            }

            $error = error_get_last();
            $fatalTypes = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
            $detail = ($error !== null && ((int) $error['type'] & $fatalTypes))
                ? $error['message'] . ' (' . basename($error['file']) . ':' . $error['line'] . ')'
                : 'không rõ nguyên nhân (không có lỗi PHP fatal nào được ghi nhận - nghi do hạ tầng '
                . 'giết tiến trình: PHP-FPM/LSAPI request timeout, OOM killer...)';

            $this->releaseLock();
            $this->markJobFinished($id, 'error', $actor, 'PHP dừng đột ngột giữa lượt chạy: ' . $detail);
        });

        try {
            $jobs = $this->getJobs();
            $index = $this->findJobIndex($jobs, $id);

            if ($index === null) {
                // Lịch bị xoá giữa chừng: vẫn phải vô hiệu hoá shutdown handler.
                $finished = true;

                return;
            }

            $reason = $this->purgeJob($jobs[$index], $actor, microtime(true) + $this->warmTimeBudget());
            $status = $reason === '' ? 'success' : 'error';

            if ($this->cancelled) {
                $status = 'cancelled';
                $reason = 'Lượt chạy bị huỷ từ trang quản trị.';
            }
        } catch (\Throwable $e) {
            $status = 'error';
            $reason = $this->describeThrowable($e);
        } finally {
            $this->releaseLock();
        }

        if ($this->handoffAt !== null && $status === 'success') {
            $finished = true;

            if ($this->handOffToNextSegment($id, $actor)) {
                return;
            }

            $finished = false;

            if ($this->cancelled) {
                $status = 'cancelled';
                $reason = 'Lượt chạy bị huỷ từ trang quản trị.';
            } else {
                $status = 'error';
                $reason = 'Chặng cào dừng đúng hạn nhưng không giao được việc cho chặng kế tiếp '
                    . '(request chạy nền không tới nơi, hoặc đã chạm trần ' . self::RUN_MAX_AUTO_RESUMES . ' chặng).';
            }
        }

        $this->markJobFinished($id, $status, $actor, $reason);
        $finished = true;
    }

    /** Lưu con trỏ rồi giao phần việc còn lại cho một tiến trình mới. */
    private function handOffToNextSegment($id, $actor)
    {
        $jobs = $this->getJobs();
        $index = $this->findJobIndex($jobs, $id);

        if ($index === null) {
            return false;
        }
        if ($this->cancelled || ($this->runId !== '' && (string) ($jobs[$index]['run_id'] ?? '') !== $this->runId)) {
            error_log('VNX Cache Scheduler: lịch "' . ($jobs[$index]['label'] ?? '')
                . '" đã dừng hoặc bị thay lượt chạy, không bàn giao chặng kế tiếp.');

            return false;
        }

        // Yêu cầu huỷ tới ngay trước lúc bàn giao thì không được đẻ thêm chặng mới.
        $pendingCancel = $this->readCancel((string) $id);

        if ($this->cancelTargetsRun($pendingCancel, $this->runId)) {
            $this->cancelled = true;

            error_log('VNX Cache Scheduler: lịch "' . ($jobs[$index]['label'] ?? '') . '" có yêu cầu huỷ, không bàn giao chặng kế tiếp.');

            return false;
        }

        $resumeCount = (int) ($jobs[$index]['resume_count'] ?? 0);

        if ($resumeCount >= self::RUN_MAX_AUTO_RESUMES) {
            error_log('VNX Cache Scheduler: lịch "' . ($jobs[$index]['label'] ?? '') . '" chạm trần '
                . self::RUN_MAX_AUTO_RESUMES . ' chặng, dừng chuỗi cào tiếp.');

            return false;
        }

        $jobs[$index]['resume_skip'] = (int) $this->handoffAt;
        $jobs[$index]['resume_count'] = $resumeCount + 1;
        $jobs[$index]['resume_stuck'] = 0;
        $jobs[$index]['resume_failed'] = (int) ($jobs[$index]['resume_failed'] ?? 0) + $this->segmentFailed;
        $jobs[$index]['last_status'] = 'running';
        $jobs[$index]['run_started_at'] = time();

        $this->saveJobs($jobs);

        error_log('VNX Cache Scheduler: lịch "' . ($jobs[$index]['label'] ?? '') . '" bàn giao sang chặng '
            . ($resumeCount + 2) . ', đã cào ' . $this->handoffAt . ' URL.');

        return $this->dispatchBackgroundRun($id, $actor);
    }

    /** Đọc lại option rồi mới ghi: lượt chạy kéo dài vài phút, danh sách có thể đã đổi. */
    private function markJobFinished($id, $status, $actor = '', $reason = '')
    {
        $jobs = $this->getJobs();
        $index = $this->findJobIndex($jobs, $id);

        if ($index === null) {
            return;
        }

        // Tiến trình của lượt cũ không được ghi đè trạng thái lên lượt mới.
        $currentRunId = (string) ($jobs[$index]['run_id'] ?? '');
        if ($this->runId !== '' && $currentRunId !== $this->runId) {
            error_log('VNX Cache Scheduler: bỏ qua ghi trạng thái "' . $status . '" cho lịch ' . $id
                . ': lượt chạy đã bị thay thế hoặc dừng cứng.');

            return;
        }

        $jobs[$index]['last_status'] = $status;
        $jobs[$index]['last_run_at'] = time();
        $jobs[$index]['run_started_at'] = null;
        $jobs[$index]['run_id'] = null;
        $jobs[$index]['last_error'] = in_array($status, ['error', 'cancelled'], true) ? $reason : '';

        $jobs[$index] = $this->clearResumeState($jobs[$index]);

        $snapshot = $this->progressSnapshot();
        if ($snapshot !== null) {
            $jobs[$index]['last_done'] = $snapshot['done'];
            $jobs[$index]['last_total'] = $snapshot['total'];
        }

        if (($jobs[$index]['schedule_type'] ?? '') === 'once') {
            $jobs[$index]['enabled'] = false;
            $jobs[$index]['next_run_at'] = null;
        } elseif (!empty($jobs[$index]['enabled']) && (int) ($jobs[$index]['next_run_at'] ?? 0) <= time()) {
            try {
                $jobs[$index]['next_run_at'] = $this->computeNextRun($jobs[$index], time());
            } catch (\Throwable $e) {
                error_log('VNX Cache Scheduler: tính mốc chạy kế tiếp sau khi kết thúc thất bại: '
                    . $e->getMessage() . ' - hoãn 1 giờ.');
                $jobs[$index]['next_run_at'] = time() + HOUR_IN_SECONDS;
            }
        }

        $this->saveJobs($jobs);
        $this->clearProgress((string) $id);
        $this->forgetCancel((string) $id);

        if ($status === 'error') {
            $this->notifyJobFailure($jobs[$index], $actor, $reason);
        } elseif ($status === 'cancelled') {
            $this->notifyJobCancelled($jobs[$index], $actor, $reason);
        }
    }

    /** Xoá mọi dấu vết "cào tiếp" trên một bản ghi lịch. */
    private function clearResumeState(array $job)
    {
        $job['resume_skip'] = 0;
        $job['resume_count'] = 0;
        $job['resume_stuck'] = 0;
        $job['resume_failed'] = 0;
        unset($job['auto_retry_used']);

        return $job;
    }

    /** Kiểm tra nonce và quyền trước mọi thao tác AJAX. Tự trả lỗi và dừng nếu không đạt. */
    private function authorize()
    {
        check_ajax_referer('vnx_cache_scheduler_nonce', 'nonce');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error('Không có quyền truy cập.');
        }
    }

    // --- Public URL discovery (viết riêng, không gọi sang VietnixExportSitemap_Center) ---

    /** Danh sách URL trang (post_type "page") đang public: publish, không noindex, không bị redirect. */
    private function getPublicPageUrls()
    {
        $urls = [];
        $redirectSources = $this->getActiveRedirectSources();

        $postIds = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        foreach ($postIds as $postId) {
            if ($this->isPostNoindex($postId)) {
                continue;
            }

            $url = get_permalink($postId);
            if (!$url || $this->isUrlRedirected($url, $redirectSources)) {
                continue;
            }

            $urls[$url] = true;
        }

        $urls = array_keys($urls);
        sort($urls);

        return $urls;
    }

    /** Bài viết có bị đánh noindex (RankMath) không. */
    private function isPostNoindex($postId)
    {
        $robots = get_post_meta($postId, 'rank_math_robots', true);
        if (empty($robots)) {
            return false;
        }

        return is_array($robots)
            ? in_array('noindex', $robots, true)
            : (strpos((string) $robots, 'noindex') !== false);
    }

    /** "Sources" của các redirect đang active trong RankMath, để loại URL đã bị redirect. */
    private function getActiveRedirectSources()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'rank_math_redirections';
        if (!$wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table))) {
            return [];
        }

        $rows = $wpdb->get_col("SELECT sources FROM {$table} WHERE status = 'active'");

        $sources = [];
        foreach ($rows as $row) {
            $decoded = maybe_unserialize($row);
            if (is_array($decoded)) {
                $sources = array_merge($sources, $decoded);
            }
        }

        foreach ($sources as $key => $source) {
            if (($source['comparison'] ?? 'exact') !== 'regex' || !isset($source['pattern'])) {
                continue;
            }

            $pattern = rtrim($source['pattern'], '/');

            $compileError = null;
            set_error_handler(function ($no, $str) use (&$compileError) {
                $compileError = $str;
            }, E_WARNING);
            $isValid = preg_match('/' . $pattern . '/', '') !== false;
            restore_error_handler();

            if (!$isValid) {
                error_log('VNX Cache Scheduler: bỏ qua redirect "Regex" không hợp lệ trong RankMath (pattern: "'
                    . $source['pattern'] . '"): ' . ($compileError ?: 'lỗi biên dịch regex không rõ.'));
                unset($sources[$key]);
            }
        }

        return array_values($sources);
    }

    /** URL có khớp bất kỳ redirect source nào đang active không. */
    private function isUrlRedirected($url, array $sources)
    {
        if (empty($sources)) {
            return false;
        }

        $path = rtrim((string) parse_url($url, PHP_URL_PATH), '/');

        foreach ($sources as $source) {
            if (!isset($source['pattern'])) {
                continue;
            }

            $pattern = rtrim($source['pattern'], '/');
            $comparison = $source['comparison'] ?? 'exact';

            switch ($comparison) {
                case 'contains':
                    if (strpos($path, $pattern) !== false) {
                        return true;
                    }
                    break;

                case 'start':
                    if (strpos($path, $pattern) === 0 || strpos($path, '/' . $pattern) === 0) {
                        return true;
                    }
                    break;

                case 'end':
                    if (substr($path, -strlen($pattern)) === $pattern) {
                        return true;
                    }
                    break;

                case 'regex':
                    if (@preg_match('/' . $pattern . '/', $path)) {
                        return true;
                    }
                    break;

                default:
                    if ($path === $pattern || $path === '/' . $pattern) {
                        return true;
                    }
            }
        }

        return false;
    }

    // --- Storage ---

    private function getJobs()
    {
        $jobs = get_option(self::OPTION_NAME, []);

        // Option hỏng thì bỏ qua thay vì để fatal về sau.
        return is_array($jobs) ? array_values(array_filter($jobs, 'is_array')) : [];
    }

    /** Ghi lại toàn bộ lịch hẹn, đánh lại index cho liên tục. */
    private function saveJobs(array $jobs)
    {
        update_option(self::OPTION_NAME, array_values($jobs));
    }

    private function findJobIndex(array $jobs, $id)
    {
        if ($id === '') {
            return null;
        }

        foreach ($jobs as $index => $job) {
            // Bản ghi cũ/hỏng có thể thiếu key id, không được để bắn warning.
            if (is_array($job) && ($job['id'] ?? null) === $id) {
                return $index;
            }
        }

        return null;
    }

    /** Tiến trình nền chết giữa chừng để lại job treo ở "running", quá hạn thì đánh dấu lỗi. */
    private function releaseStaleRuns(array $jobs)
    {
        $changed = false;
        $staleJobs = [];
        $retryJobs = [];
        $cancelledJobs = [];

        // Đọc khoá một lần: mỗi lần đọc đều phải né object cache, để trong vòng lặp là mỗi
        // lịch lại thêm một truy vấn.
        $lockHolder = $this->lockHolder();

        foreach ($jobs as $index => $job) {
            if (($job['last_status'] ?? '') !== 'running') {
                continue;
            }

            $startedAt = (int) ($job['run_started_at'] ?? 0);
            $age = $startedAt > 0 ? time() - $startedAt : PHP_INT_MAX;

            // Còn tiến trình giữ lock cho đúng job này thì nó vẫn đang chạy thật.
            $ownsLock = $lockHolder !== null && $lockHolder === (string) ($job['id'] ?? '');

            $live = $this->readProgress((string) ($job['id'] ?? ''));
            $heartbeat = $live !== null && !empty($live['updated_at']) ? (int) $live['updated_at'] : null;
            $silentFor = $heartbeat !== null ? time() - $heartbeat : null;
            $heartbeatDead = $silentFor !== null && $silentFor >= self::RUN_HEARTBEAT_STALE_AFTER;

            $heartbeatMissing = $heartbeat === null && $age >= self::RUN_ORPHAN_AFTER;

            if ($ownsLock && !$heartbeatDead && !$heartbeatMissing && $age < self::RUN_STALE_AFTER) {
                continue;
            }

            // Không ai giữ lock: chừa một khoảng ngắn cho tiến trình nền kịp khởi động.
            if (!$ownsLock && $age < self::RUN_ORPHAN_AFTER) {
                continue;
            }

            if ($heartbeatDead) {
                $reason = 'Tiến trình chạy nền im lặng ' . $silentFor . ' giây, tiến độ không nhúc nhích: '
                    . 'PHP bị giết ngang (LSAPI_MAX_PROCESS_TIME, request_terminate_timeout hoặc hết memory_limit).';
            } elseif ($heartbeatMissing) {
                $reason = 'Tiến trình chạy nền không để lại nhịp tim nào sau ' . $age . ' giây (cả transient '
                    . 'lẫn bản sao tiến độ trong options đều trống): PHP chết ngay đầu lượt chạy.';
            } elseif ($ownsLock) {
                $reason = 'Tiến trình chạy nền dừng giữa chừng: quá ' . round(self::RUN_STALE_AFTER / 60)
                    . ' phút không báo kết quả (thường do PHP bị kill vì hết max_execution_time/memory_limit).';
            } else {
                $reason = 'Tiến trình chạy nền không còn tồn tại sau ' . $age . ' giây (không giữ lock): '
                    . 'request chạy nền không tới nơi, hoặc PHP đã chết mà không kịp báo lỗi.';
            }

            // Khoá là toàn cục nên một lượt chạy chết chặn mọi lịch khác; nhả luôn tại đây.
            if ($ownsLock) {
                $this->discardLock();
                $lockHolder = null;
            }

            $doneSoFar = $live !== null ? (int) ($live['done'] ?? 0) : 0;

            // Giữ lại số đã cào để cột "Đã xử lý" cho biết lượt chạy chết ở đâu.
            if ($live !== null && !empty($live['total'])) {
                $jobs[$index]['last_done'] = $doneSoFar;
                $jobs[$index]['last_total'] = (int) $live['total'];
            }

            $this->forgetProgress((string) ($job['id'] ?? ''));

            $cancelWanted = $this->cancelTargetsRun(
                $this->readCancel((string) ($job['id'] ?? '')),
                (string) ($job['run_id'] ?? '')
            );

            $this->forgetCancel((string) ($job['id'] ?? ''));

            $purgeType = $job['purge_type'] ?? 'all';
            $lastResumeAt = (int) ($job['resume_skip'] ?? 0);
            $resumeCount = (int) ($job['resume_count'] ?? 0);
            $stuck = (int) ($job['resume_stuck'] ?? 0);

            if ($doneSoFar > $lastResumeAt) {
                $nextSkip = $doneSoFar;
                $nextStuck = 0;
            } elseif ($stuck + 1 >= self::RUN_MAX_STUCK_RESUMES) {
                $nextSkip = $lastResumeAt + 1;
                $nextStuck = 0;

                error_log('VNX Cache Scheduler: lịch "' . ($job['label'] ?? '') . '" chết ' . self::RUN_MAX_STUCK_RESUMES
                    . ' lần liên tiếp tại URL thứ ' . ($lastResumeAt + 1) . ', bỏ qua URL này và cào tiếp.');
            } else {
                $nextSkip = $lastResumeAt;
                $nextStuck = $stuck + 1;
            }

            $canAutoRetry = !$cancelWanted && $resumeCount < self::RUN_MAX_AUTO_RESUMES;

            if (!$canAutoRetry && !$cancelWanted && $resumeCount >= self::RUN_MAX_AUTO_RESUMES) {
                $reason .= ' Đã tự cào tiếp ' . $resumeCount . ' lần, chạm trần '
                    . self::RUN_MAX_AUTO_RESUMES . ' nên dừng hẳn.';
            }

            if ($cancelWanted) {
                $cancelReason = 'Lượt chạy đã được yêu cầu huỷ, tiến trình nền dừng trước khi kịp báo cáo. '
                    . $reason;

                $jobs[$index]['last_status'] = 'cancelled';
                $jobs[$index]['run_started_at'] = null;
                $jobs[$index]['run_id'] = null;
                $jobs[$index]['last_error'] = $cancelReason;
                $jobs[$index] = $this->clearResumeState($jobs[$index]);

                $cancelledJobs[] = [$jobs[$index], $cancelReason];
            } elseif ($canAutoRetry) {
                $jobs[$index]['resume_skip'] = $nextSkip;
                $jobs[$index]['resume_count'] = $resumeCount + 1;
                $jobs[$index]['resume_stuck'] = $nextStuck;
                $jobs[$index]['run_started_at'] = time();
                $retryJobs[] = [$jobs[$index], $reason, $nextSkip];
            } else {
                $jobs[$index]['last_status'] = 'error';
                $jobs[$index]['run_started_at'] = null;
                $jobs[$index]['run_id'] = null;
                $jobs[$index]['last_error'] = $reason;

                // Không đi qua markJobFinished() nên phải tự dọn suất cào tiếp.
                $jobs[$index] = $this->clearResumeState($jobs[$index]);

                $staleJobs[] = [$jobs[$index], $reason];
            }

            $changed = true;
        }

        if ($changed) {
            $this->saveJobs($jobs);
        }

        // Khoá mồ côi không thuộc lịch nào cũng phải được dọn ở đây.
        $this->sweepStaleLock($jobs);

        // Gửi sau khi ghi option, kẻo request Discord chết thì job kẹt "running".
        foreach ($staleJobs as $stale) {
            $this->notifyJobFailure($stale[0], 'Hệ thống (phát hiện job treo)', $stale[1]);
        }

        foreach ($cancelledJobs as $cancelled) {
            error_log('VNX Cache Scheduler: lịch "' . ($cancelled[0]['label'] ?? '') . '" đã có yêu cầu huỷ, '
                . 'không cào tiếp nữa.');

            $this->notifyJobCancelled($cancelled[0], 'Hệ thống (phát hiện job treo)', $cancelled[1]);
        }

        foreach ($retryJobs as [$job, $reason, $nextSkip]) {
            $id = (string) ($job['id'] ?? '');

            error_log('VNX Cache Scheduler: lịch "' . ($job['label'] ?? '') . '" (' . $id . ') chết giữa chừng ('
                . $reason . '), đã cào ' . ($job['last_done'] ?? 0) . '/' . ($job['last_total'] ?? 0)
                . ' - tự động cào tiếp sau 2 giây.');

            $this->notifyJobResumed($job, $reason, $nextSkip);

            sleep(2);

            if (!$this->dispatchBackgroundRun($id, 'Hệ thống (tự động cào tiếp)')) {
                $this->markJobFinished(
                    $id,
                    'error',
                    'Hệ thống (tự động cào tiếp)',
                    $reason . ' Đã thử tự động cào tiếp nhưng không gửi được request chạy nền.'
                );
            }
        }

        return $jobs;
    }

    private function isAutoEnabled()
    {
        return (bool) get_option(self::AUTO_OPTION, false);
    }

    // --- Validation ---

    /** Kiểm tra và chuẩn hoá dữ liệu lịch hẹn gửi lên từ form. */
    private function sanitizeJobInput(array $raw)
    {
        $purgeType = 'urls';
        $scheduleType = in_array($raw['schedule_type'] ?? '', ['once', 'daily', 'weekly'], true)
            ? $raw['schedule_type']
            : '';

        if ($scheduleType === '') {
            return 'Loại lịch không hợp lệ.';
        }

        $urls = [];
        $lines = is_array($raw['urls'] ?? null) ? $raw['urls'] : explode("\n", $this->scalarString($raw['urls'] ?? ''));
        foreach ($lines as $line) {
            $line = trim($this->scalarString($line));
            if ($line === '') {
                continue;
            }
            if (strpos($line, '//') === 0) {
                $line = (is_ssl() ? 'https:' : 'http:') . $line;
            } elseif (strpos($line, '://') === false) {
                $line = home_url('/' . ltrim($line, '/'));
            }
            $line = esc_url_raw($line);
            if ($line !== '' && !in_array($line, $urls, true)) {
                $urls[] = $line;
            }
        }
        if (empty($urls)) {
            return 'Cần ít nhất 1 URL hợp lệ.';
        }

        $runAt = '';
        if ($scheduleType === 'once') {
            $runAt = str_replace('T', ' ', $this->scalarString($raw['run_at'] ?? ''));
            if (!$this->parseLocalDateTime($runAt)) {
                return 'Ngày giờ chạy không hợp lệ.';
            }
        }

        $weekdays = array_values(array_unique(array_intersect(
            array_map('intval', array_filter((array) ($raw['weekdays'] ?? []), 'is_scalar')),
            range(1, 7)
        )));
        if ($scheduleType === 'weekly' && empty($weekdays)) {
            return 'Chọn ít nhất 1 ngày trong tuần.';
        }

        $timeOfDay = $this->scalarString($raw['time_of_day'] ?? '');

        $job = [
            'label' => sanitize_text_field($raw['label'] ?? '') ?: 'Lịch xoá cache',
            'purge_type' => $purgeType,
            'urls' => $urls,
            'schedule_type' => $scheduleType,
            'run_at' => $runAt,
            'time_of_day' => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $timeOfDay) ? $timeOfDay : '00:00',
            'weekdays' => $weekdays,
            'enabled' => filter_var($raw['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN) && $this->getLiteSpeedStatus() === 'active',
            'last_run_at' => null,
            'last_status' => null,
            'last_error' => '',
            'last_done' => 0,
            'last_total' => 0,
            'run_started_at' => null,
            'next_run_at' => null,
        ];

        if ($job['enabled']) {
            $job['next_run_at'] = $this->computeNextRun($job, time());
        }

        return $job;
    }

    /** Ép về chuỗi an toàn: mảng/object trong payload AJAX trả về '' thay vì fatal. */
    private function scalarString($value, $default = '')
    {
        return is_scalar($value) ? (string) $value : $default;
    }

    private function parseLocalDateTime($value)
    {
        $value = trim($this->scalarString($value));
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            return false;
        }

        try {
            return (new \DateTime($value, wp_timezone()))->getTimestamp();
        } catch (\Exception $e) {
            return false;
        }
    }

    // --- Progress ---

    /** Mở bộ đếm cho lượt chạy của một lịch. */
    private function startProgress($jobId, $total, $doneOffset = 0)
    {
        if ($jobId === '') {
            return;
        }

        $total = max(0, (int) $total);

        $this->progress = [
            'job_id' => $jobId,
            'done' => max(0, min($total, (int) $doneOffset)),
            'total' => $total,
            'current_url' => '',
        ];

        $this->saveProgress();
    }

    /** Đánh dấu vừa xử lý xong một URL. */
    private function bumpProgress($url = null)
    {
        if ($this->progress === null) {
            return;
        }

        $this->progress['done'] = min($this->progress['total'], $this->progress['done'] + 1);

        if ($url !== null) {
            $this->progress['current_url'] = $url;
        }

        $this->saveProgress();
    }

    /** Chạm nhịp tim mà không tăng số đã xử lý. */
    private function touchProgress()
    {
        $this->saveProgress();
    }

    private function saveProgress()
    {
        if ($this->progress === null) {
            return;
        }

        // Mốc này là bằng chứng duy nhất cho biết tiến trình còn sống.
        $this->progress['updated_at'] = time();

        set_transient(self::PROGRESS_PREFIX . $this->progress['job_id'], $this->progress, self::LOCK_TTL);

        // Ghi kèm xuống options: object cache ngoài bị flush thì transient bay, option còn.
        update_option(self::PROGRESS_OPTION_PREFIX . $this->progress['job_id'], $this->progress, false);

        // Khoá cũng phải được làm tươi theo nhịp tim, kẻo bị dọn oan.
        $this->touchLock();
    }

    /** Transient tiến độ của một lịch, né object cache để đọc được số do tiến trình khác ghi. */
    private function readProgress($jobId)
    {
        if ($jobId === '') {
            return null;
        }

        $key = self::PROGRESS_PREFIX . $jobId;
        $this->bustTransientCache($key);
        $live = get_transient($key);

        if (is_array($live)) {
            return $live;
        }

        // Transient mất không có nghĩa là tiến trình chết: rơi về bản sao trong options.
        $optionKey = self::PROGRESS_OPTION_PREFIX . $jobId;
        wp_cache_delete($optionKey, 'options');
        wp_cache_delete('notoptions', 'options');
        $stored = get_option($optionKey);

        if (!is_array($stored) || empty($stored['updated_at'])) {
            return null;
        }

        // Bản sao không tự hết hạn như transient nên phải tự bỏ khi đã quá cũ.
        if (time() - (int) $stored['updated_at'] > self::LOCK_TTL) {
            delete_option($optionKey);

            return null;
        }

        return $stored;
    }

    /** Lần chạm nhịp tim gần nhất của một lịch, null nếu chưa có tiến độ nào được ghi. */
    private function progressHeartbeat($jobId)
    {
        $live = $this->readProgress($jobId);

        return $live !== null && !empty($live['updated_at']) ? (int) $live['updated_at'] : null;
    }

    /** Xoá bản sao transient trong object cache của tiến trình hiện tại. */
    private function bustTransientCache($key)
    {
        wp_cache_delete($key, 'transient');
        wp_cache_delete('_transient_' . $key, 'options');
        wp_cache_delete('_transient_timeout_' . $key, 'options');
        wp_cache_delete('notoptions', 'options');
    }

    /** Ghi yêu cầu huỷ cho một lượt chạy, vào cả transient lẫn options. */
    private function requestCancel($jobId, $runId, $actor, $hard = false)
    {
        if ($jobId === '') {
            return;
        }

        $existing = $this->readCancel($jobId);

        $record = [
            'run_id' => (string) $runId,
            // Ân hạn tính từ lần bấm Huỷ đầu tiên nên giữ nguyên mốc cũ.
            'at' => $existing !== null ? (int) ($existing['at'] ?? time()) : time(),
            'actor' => (string) $actor,
            'hard' => (bool) $hard || !empty($existing['hard']),
        ];

        set_transient(self::CANCEL_PREFIX . $jobId, $record, self::LOCK_TTL);
        update_option(self::CANCEL_OPTION_PREFIX . $jobId, $record, false);
    }

    /** Yêu cầu huỷ đang treo của một lịch, null nếu không có. */
    private function readCancel($jobId)
    {
        if ($jobId === '') {
            return null;
        }

        $key = self::CANCEL_PREFIX . $jobId;
        $this->bustTransientCache($key);
        $live = get_transient($key);

        if (!is_array($live)) {
            $optionKey = self::CANCEL_OPTION_PREFIX . $jobId;
            wp_cache_delete($optionKey, 'options');
            wp_cache_delete('notoptions', 'options');
            $stored = get_option($optionKey);

            if (is_array($stored)) {
                // Bản sao trong options mới là bản đầy đủ, luôn ưu tiên hơn cờ kiểu cũ.
                $live = $stored;
            } elseif (is_int($live) || (is_string($live) && $live !== '')) {
                // Bản ghi của phiên bản trước chỉ là timestamp trần, không mang mã lượt chạy.
                $live = ['run_id' => '', 'at' => (int) $live, 'actor' => '', 'hard' => false];
            } else {
                return null;
            }
        }

        $at = (int) ($live['at'] ?? 0);

        // Bản sao trong options không tự hết hạn, cờ cũ sót lại sẽ giết oan lượt mới.
        if ($at <= 0 || time() - $at > self::LOCK_TTL) {
            $this->forgetCancel($jobId);

            return null;
        }

        return $live;
    }

    /** Xoá cả transient lẫn bản sao trong options của một yêu cầu huỷ. */
    private function forgetCancel($jobId)
    {
        if ($jobId === '') {
            return;
        }

        delete_transient(self::CANCEL_PREFIX . $jobId);
        delete_option(self::CANCEL_OPTION_PREFIX . $jobId);
    }

    /** Yêu cầu huỷ này có nhắm vào lượt chạy đang xét không. */
    private function cancelTargetsRun($record, $runId)
    {
        if (!is_array($record)) {
            return false;
        }

        // So khớp chặt: cờ không mang mã lượt chạy (bản ghi kiểu cũ) chỉ ứng với lịch cũng
        // chưa có mã, chứ không được vơ luôn mọi lượt chạy mới.
        return (string) ($record['run_id'] ?? '') === (string) $runId;
    }

    /** Mã nhận diện cho một lượt chạy mới. */
    private function newRunId()
    {
        return wp_generate_password(12, false);
    }

    // --- Khoá toàn cục ---

    /** Giành khoá toàn cục cho một chủ sở hữu. */
    private function acquireLock($holder)
    {
        // Token là bằng chứng "khoá này của tôi": nhả khoá phải so token, kẻo tiến trình cũ
        // thoát muộn lại xoá đúng khoá mà lượt chạy mới vừa giành được.
        $this->lockToken = wp_generate_password(12, false);
        $this->lockOwned = true;
        $this->lockTouchedAt = time();

        $this->writeLock([
            'holder' => (string) $holder,
            'token' => $this->lockToken,
            'at' => time(),
        ]);
    }

    /** Ghi khoá vào cả transient lẫn options. */
    private function writeLock(array $record)
    {
        set_transient(self::LOCK_KEY, $record, self::LOCK_TTL);
        update_option(self::LOCK_OPTION, $record, false);
    }

    /** Làm tươi mốc thời gian của khoá, chỉ khi tiến trình này đang giữ nó. */
    private function touchLock()
    {
        if (!$this->lockOwned) {
            return;
        }

        $now = time();

        // TTL của khoá tính bằng giờ: ghi lại theo mọi nhịp tim chỉ tổ nện DB.
        if ($this->lockTouchedAt !== null && ($now - $this->lockTouchedAt) < self::LOCK_TOUCH_EVERY) {
            return;
        }

        $this->lockTouchedAt = $now;

        $current = $this->rawLock();

        // Khoá đã bị dọn hoặc bị lượt khác giành mất: thôi nhận là của mình, đừng làm tươi hộ.
        if (!is_array($current) || (string) ($current['token'] ?? '') !== $this->lockToken) {
            $this->lockOwned = false;

            // Mất khoá nghĩa là lượt chạy này đã bị dừng cứng hoặc bị thay. Cứ cào tiếp thì
            // hai tiến trình cùng ghi một bộ đếm tiến độ, con số nhảy loạn. Dừng chặng này
            // thôi, phần còn lại giao cho chặng mới - mất khoá không phải là lệnh huỷ.
            if (!$this->cancelled && !$this->lockLost) {
                $this->lockLost = true;

                error_log('VNX Cache Scheduler: lượt chạy hiện tại đã mất khoá toàn cục (bị dừng cứng, '
                    . 'bị lượt khác thay, hoặc object cache bị xoá sạch), dừng chặng này và bàn giao '
                    . 'phần còn lại cho một chặng mới.');
            }

            return;
        }

        $current['at'] = $now;

        $this->writeLock($current);
    }

    /** Giá trị thô của khoá, đã né object cache của tiến trình hiện tại. */
    private function rawLock()
    {
        $this->bustTransientCache(self::LOCK_KEY);
        $live = get_transient(self::LOCK_KEY);

        if (is_array($live) || (is_string($live) && $live !== '')) {
            return $live;
        }

        // Transient trống chưa chắc là hết khoá: Purge All vừa thổi bay object cache thì bản
        // sao trong options mới là bản còn thật.
        wp_cache_delete(self::LOCK_OPTION, 'options');
        wp_cache_delete('notoptions', 'options');
        $stored = get_option(self::LOCK_OPTION);

        if (!is_array($stored)) {
            return $live;
        }

        $at = (int) ($stored['at'] ?? 0);

        // Option không tự hết hạn nên phải tự tính tuổi, kẻo khoá chết chặn mọi lượt sau.
        if ($at <= 0 || time() - $at > self::LOCK_TTL) {
            delete_option(self::LOCK_OPTION);

            return $live;
        }

        // Dựng lại transient để các lần đọc sau không phải chạm bảng options nữa.
        set_transient(self::LOCK_KEY, $stored, self::LOCK_TTL);

        return $stored;
    }

    /** Ai đang giữ khoá toàn cục. */
    private function lockHolder()
    {
        $lock = $this->rawLock();

        if (is_array($lock)) {
            $holder = (string) ($lock['holder'] ?? '');

            return $holder === '' ? null : $holder;
        }

        // Khoá do phiên bản cũ đặt chỉ là chuỗi id trần.
        if (is_string($lock) && $lock !== '') {
            return $lock;
        }

        return null;
    }

    /** Khoá đã nằm đó bao lâu, null nếu khoá cũ không mang mốc thời gian. */
    private function lockAge()
    {
        $lock = $this->rawLock();

        if (is_array($lock) && !empty($lock['at'])) {
            return time() - (int) $lock['at'];
        }

        return null;
    }

    /** Nhả khoá của chính tiến trình này; khoá đã sang tay lượt khác thì để nguyên. */
    private function releaseLock()
    {
        $owned = $this->lockOwned;
        $token = $this->lockToken;

        $this->lockOwned = false;
        $this->lockToken = '';
        $this->lockTouchedAt = null;

        if (!$owned) {
            return;
        }

        $current = $this->rawLock();

        // Dừng khẩn cấp đã nhả khoá và lượt mới có thể đã giành được nó: xoá thẳng ở đây là
        // cướp khoá của lượt đang chạy, hai lượt sẽ chồng lên nhau.
        if (is_array($current) && (string) ($current['token'] ?? '') !== $token) {
            return;
        }

        $this->discardLock();
    }

    /** Xoá thẳng khoá của kẻ khác. Chỉ dùng khi đã xác định khoá đó mồ côi. */
    private function discardLock()
    {
        delete_transient(self::LOCK_KEY);
        delete_option(self::LOCK_OPTION);
    }

    /** Dọn khoá toàn cục mà không lịch nào còn nhận. */
    private function sweepStaleLock(array $jobs)
    {
        $holder = $this->lockHolder();

        if ($holder === null) {
            return false;
        }

        foreach ($jobs as $job) {
            if (!is_array($job) || ($job['last_status'] ?? '') !== 'running') {
                continue;
            }

            // Có lịch đang nhận khoá này: chuyện treo hay không để releaseStaleRuns lo.
            if ((string) ($job['id'] ?? '') === $holder) {
                return false;
            }
        }

        $age = $this->lockAge();

        // Khoá vừa đặt có thể của tiến trình chưa kịp đánh dấu lịch là đang chạy.
        if ($age !== null && $age < self::RUN_HEARTBEAT_STALE_AFTER) {
            return false;
        }

        error_log('VNX Cache Scheduler: dọn khoá toàn cục mồ côi của "' . $holder . '" ('
            . ($age === null ? 'khoá kiểu cũ, không có mốc thời gian' : $age . ' giây, không lịch nào nhận') . ').');

        $this->discardLock();

        return true;
    }

    /** Trang quản trị có yêu cầu huỷ lượt chạy này không. */
    private function cancelRequested()
    {
        // Mất khoá cũng là "dừng ngay", chỉ khác ở chỗ kết lượt: xem nhánh lockLost trong purgeJob().
        if ($this->cancelled || $this->lockLost) {
            return true;
        }

        if ($this->progress === null) {
            return false;
        }

        // Hàm này được gọi cho mỗi URL và mỗi lần thử lại nên phải giãn nhịp đọc DB.
        $now = microtime(true);
        if ($this->cancelCheckedAt !== null && ($now - $this->cancelCheckedAt) < self::CANCEL_POLL_EVERY) {
            return false;
        }
        $this->cancelCheckedAt = $now;

        $record = $this->readCancel($this->progress['job_id']);

        if (!$this->cancelTargetsRun($record, $this->runId)) {
            return false;
        }

        $this->cancelled = true;

        return true;
    }

    /** Lượt chạy của lịch này có tiến trình thật đứng sau hay không. */
    private function runLooksAlive(array $job, $lockHolder = false)
    {
        $startedAt = (int) ($job['run_started_at'] ?? 0);

        // Vừa bấm chạy: tiến trình nền có thể chưa kịp giữ khoá, chưa vội kết luận nó chết.
        if ($startedAt > 0 && (time() - $startedAt) < self::RUN_ORPHAN_AFTER) {
            return true;
        }

        // $lockHolder === false nghĩa là chưa ai đọc hộ; null là "đọc rồi, không ai giữ khoá".
        $holder = $lockHolder === false ? $this->lockHolder() : $lockHolder;

        if ($holder !== (string) ($job['id'] ?? '')) {
            return false;
        }

        $heartbeat = $this->progressHeartbeat((string) ($job['id'] ?? ''));

        return $heartbeat !== null && (time() - $heartbeat) < self::RUN_HEARTBEAT_STALE_AFTER;
    }

    /** Dọn sạch dấu vết của một lượt chạy đã chết. Chỉ gọi khi runLooksAlive() đã nói là chết. */
    private function forceReleaseRun($id, array $jobs, $index, $actor, $reason, $keepCancel = false)
    {
        // Đọc trước khi xoá transient: notify bên dưới cần số đã cào được lúc dọn.
        $live = $this->readProgress($id);

        // Khoá toàn cục: chỉ nhả khi nó mang đúng id này, kẻo cướp khoá của lượt khác.
        if ($this->lockHolder() === (string) $id) {
            $this->discardLock();
        }

        $this->forgetProgress((string) $id);
        delete_transient(self::RUN_TOKEN_PREFIX . $id);

        // Dừng cứng thì giữ lại cờ huỷ: tiến trình xác sống còn phải đọc được nó mà tự thoát.
        if (!$keepCancel) {
            $this->forgetCancel((string) $id);
        }

        $jobs[$index]['last_status'] = 'cancelled';
        $jobs[$index]['last_run_at'] = time();
        $jobs[$index]['run_started_at'] = null;
        $jobs[$index]['run_id'] = null;
        $jobs[$index]['last_error'] = $reason;
        $jobs[$index] = $this->clearResumeState($jobs[$index]);

        if ($live !== null && !empty($live['total'])) {
            $jobs[$index]['last_done'] = (int) $live['done'];
            $jobs[$index]['last_total'] = (int) $live['total'];
        }

        $this->saveJobs($jobs);

        $this->notifyJobCancelled($jobs[$index], $actor, $reason);
    }

    /** Số đếm hiện tại của tiến trình này, để ghi lại lên job lúc kết thúc. */
    private function progressSnapshot()
    {
        if ($this->progress === null || $this->progress['total'] <= 0) {
            return null;
        }

        return ['done' => $this->progress['done'], 'total' => $this->progress['total']];
    }

    private function clearProgress($jobId)
    {
        $this->progress = null;

        if ($jobId !== '') {
            $this->forgetProgress($jobId);
        }
    }

    /** Xoá cả transient lẫn bản sao trong options của một tiến độ. */
    private function forgetProgress($jobId)
    {
        if ($jobId === '') {
            return;
        }

        delete_transient(self::PROGRESS_PREFIX . $jobId);
        delete_option(self::PROGRESS_OPTION_PREFIX . $jobId);
    }

    /** Gắn số "đã xử lý / tổng" vào từng lịch trước khi trả về cho trang quản trị. */
    private function attachProgress(array $jobs)
    {
        $lockHolder = $this->lockHolder();

        foreach ($jobs as $index => $job) {
            $jobs[$index]['progress'] = is_array($job) ? $this->describeJobProgress($job) : null;

            // Giao diện phải phân biệt "đang chạy" với "đã xin huỷ, chờ tiến trình dừng".
            $running = is_array($job) && ($job['last_status'] ?? '') === 'running';
            $cancel = $running ? $this->readCancel((string) ($job['id'] ?? '')) : null;
            $wanted = $running && $this->cancelTargetsRun($cancel, (string) ($job['run_id'] ?? ''));

            $jobs[$index]['cancel_requested'] = $wanted;
            // Giao diện cần biết khi nào được mời người dùng dừng cứng.
            $jobs[$index]['cancel_force_in'] = $wanted
                ? max(0, self::CANCEL_GRACE - (time() - (int) ($cancel['at'] ?? time())))
                : null;
            $jobs[$index]['run_alive'] = $running && $this->runLooksAlive($job, $lockHolder);
        }

        return $jobs;
    }

    /** Số URL đã xử lý / tổng của một lịch, null nếu không có gì để hiện. */
    private function describeJobProgress(array $job)
    {
        $purgeType = $job['purge_type'] ?? 'all';

        // "Toàn bộ site" không lưu sẵn tổng số trang, chỉ biết qua transient tiến trình lúc chạy.
        $listTotal = $purgeType === 'all'
            ? 0
            : count(array_filter((array) ($job['urls'] ?? []), 'is_string'));

        $lastDone = (int) ($job['last_done'] ?? 0);
        $lastTotal = (int) ($job['last_total'] ?? 0);

        if (($job['last_status'] ?? '') === 'running') {
            // Transient do request khác ghi: phải né cache cục bộ mới đọc được số mới nhất.
            $live = $this->readProgress((string) ($job['id'] ?? ''));

            if (is_array($live) && !empty($live['total'])) {
                return [
                    'done' => (int) $live['done'],
                    'total' => (int) $live['total'],
                    'current_url' => (string) ($live['current_url'] ?? ''),
                    'source' => 'live',
                ];
            }

            // Chưa có nhịp nào: chặng vừa chết, hoặc chặng kế tiếp chưa kịp ghi số đầu.
            if ($lastTotal > 0) {
                return ['done' => $lastDone, 'total' => $lastTotal, 'source' => 'last'];
            }

            if ($listTotal > 0) {
                return ['done' => 0, 'total' => $listTotal, 'source' => 'list'];
            }

            return null;
        }

        if ($lastTotal > 0) {
            return ['done' => $lastDone, 'total' => $lastTotal, 'source' => 'last'];
        }

        // Lịch "URL cụ thể" chưa chạy lần nào vẫn nói được tổng số trang sẽ cào.
        if ($listTotal > 0) {
            return ['done' => 0, 'total' => $listTotal, 'source' => 'list'];
        }

        return null;
    }

    /** Summary crawler của LSCWP, [] nếu không đọc được. */
    private function crawlerSummary()
    {
        try {
            if (!class_exists('\LiteSpeed\Crawler')) {
                return [];
            }

            return (array) \LiteSpeed\Crawler::get_summary();
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: đọc summary crawler thất bại: ' . $e->getMessage());

            return [];
        }
    }

    // --- Notifications ---

    private function getNotifySettings()
    {
        $settings = (array) get_option(self::NOTIFY_OPTION, []);

        return [
            'enabled' => !empty($settings['enabled']),
            'webhook_url' => isset($settings['webhook_url']) ? (string) $settings['webhook_url'] : '',
        ];
    }

    private function currentActorLabel()
    {
        $user = wp_get_current_user();

        return $user && $user->exists() ? $user->display_name : 'Không xác định';
    }

    /** Khối "Phạm vi" của thông báo: số URL ở dòng đầu, từng URL một dòng có đánh số. */
    private function describeJobScope(array $job)
    {
        if (($job['purge_type'] ?? 'all') === 'all') {
            return '**Phạm vi:** Toàn bộ site';
        }

        $urls = array_values(array_filter((array) ($job['urls'] ?? []), 'is_string'));
        $shown = array_slice($urls, 0, self::NOTIFY_URL_LIMIT);

        $lines = ['**Phạm vi:** ' . count($urls) . ' URL'];
        foreach ($shown as $index => $url) {
            $lines[] = ($index + 1) . '. ' . $url;
        }
        if (count($urls) > count($shown)) {
            $lines[] = '… và ' . (count($urls) - count($shown)) . ' URL khác';
        }

        return implode("\n", $lines);
    }

    /** Chân trang chung của mọi thông báo: ai chạy, site nào, lúc nào. */
    private function noticeFooter($actor)
    {
        $actor = trim($this->scalarString($actor)) ?: 'Không xác định';

        return '**Thực hiện bởi:** ' . $actor
            . "\n**Website:** " . home_url()
            . "\n**Thời gian:** " . (new \DateTime('now', new \DateTimeZone('Asia/Ho_Chi_Minh')))->format('d/m/Y H:i');
    }

    /** Gửi 1 tin Discord riêng ngay khi vừa xử lý xong 1 URL (cào thành công hoặc lỗi). */
    private function notifyUrlDone(array $job, $actor, $url, $ok, $done, $total)
    {
        try {
            $settings = $this->getNotifySettings();
            if (!$settings['enabled'] || $settings['webhook_url'] === '') {
                return;
            }

            $title = ($ok ? 'Đã crawl xong' : 'Crawl lỗi') . ' (' . $done . '/' . $total . '): ' . ($job['label'] ?? '');

            // Khong blocking: cho Discord phan hoi tung URL se lam vong lap cao vuot deadline.
            $sent = DiscordBot::sendMessageByWebhook(
                $settings['webhook_url'],
                $title,
                $url . "\n\n" . $this->noticeFooter($actor),
                $ok ? self::NOTIFY_COLORS['done'] : self::NOTIFY_COLORS['warn']
            );

            if (!$sent) {
                error_log('VNX Cache Scheduler: gửi thông báo Discord cho URL "' . $url
                    . '" thất bại (Discord từ chối request), xem log "Discord webhook send failed" ở trên.');
            }
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: gửi thông báo Discord theo URL thất bại: ' . $e->getMessage());
        }
    }

    /** Gửi thông báo một chặng của lượt chạy, nuốt mọi lỗi phát sinh. */
    private function notifyDiscord(array $job, $actor, $stage = 'done')
    {
        try {
            $this->sendJobNotice($job, $actor, $stage);
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: gửi thông báo Discord (' . $stage . ') thất bại: ' . $e->getMessage());
        }
    }

    private function sendJobNotice(array $job, $actor, $stage)
    {
        $settings = $this->getNotifySettings();
        if (!$settings['enabled'] || $settings['webhook_url'] === '') {
            return;
        }

        $scope = $this->describeJobScope($job);

        $title = (self::NOTIFY_TITLES[$stage] ?? self::NOTIFY_TITLES['done']) . ($job['label'] ?? '');

        $sent = DiscordBot::sendMessageByWebhook(
            $settings['webhook_url'],
            $title,
            $scope . "\n\n" . $this->noticeFooter($actor),
            self::NOTIFY_COLORS[$stage] ?? self::NOTIFY_COLORS['purge_start']
        );

        if (!$sent) {
            error_log('VNX Cache Scheduler: gửi thông báo Discord (' . $stage . ') cho lịch "' . ($job['label'] ?? '')
                . '" thất bại (Discord từ chối request), xem log "Discord webhook send failed" ở trên.');
        }
    }

    /** Báo lịch chạy thất bại kèm lý do, nuốt mọi lỗi phát sinh. */
    private function notifyJobFailure(array $job, $actor, $reason)
    {
        $reason = trim($this->scalarString($reason)) ?: 'Không rõ nguyên nhân.';

        $detail = $this->detailRunIssues();

        error_log('VNX Cache Scheduler: lịch "' . ($job['label'] ?? '') . '" ('
            . ($job['id'] ?? '') . ') chạy thất bại: ' . $reason
            . ($detail !== '' ? "\n" . strip_tags(str_replace(['**', '> '], '', $detail)) : ''));

        try {
            $this->sendJobFailureNotice($job, $actor, $reason);
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: gửi thông báo lỗi Discord thất bại: ' . $e->getMessage());
        }
    }

    private function sendJobFailureNotice(array $job, $actor, $reason)
    {
        $settings = $this->getNotifySettings();
        if (!$settings['enabled'] || $settings['webhook_url'] === '') {
            return;
        }

        if (mb_strlen($reason) > 800) {
            $reason = mb_substr($reason, 0, 800) . '... (đã cắt bớt, xem đầy đủ trong error_log)';
        }

        // Danh sách trang lỗi thường vượt 4096 ký tự nên tách mỗi nhóm nguyên nhân một tin.
        $parts = ["**Lý do:** {$reason}\n\n"
            . $this->describeJobScope($job) . "\n\n"
            . $this->noticeFooter($actor)];

        foreach ($this->runIssueBlocks() as $block) {
            $parts[] = $block;
        }

        if (!empty($this->runIssues['warm'])) {
            $parts[] = "**Vì sao không hit:**\n"
                . "1. `x-litespeed-cache` rỗng → request không tới LiteSpeed (sai Server IP/vhost)\n"
                . "2. `cache-control=no-cache` → trang bị loại khỏi cache\n"
                . "3. `miss` → cache chưa được lưu";
        }

        // Một nhóm nguyên nhân vẫn có thể dài hơn giới hạn, nên cắt tiếp từng phần.
        $messages = [];
        foreach ($parts as $part) {
            foreach ($this->splitForDiscord($part) as $chunk) {
                $messages[] = $chunk;
            }
        }

        $label = $job['label'] ?? '';
        $total = count($messages);

        foreach ($messages as $index => $body) {
            $title = '❌ Lịch xoá cache CHẠY THẤT BẠI'
                . ($total > 1 ? ' (' . ($index + 1) . '/' . $total . ')' : '')
                . ': ' . $label;

            $sent = DiscordBot::sendMessageByWebhook(
                $settings['webhook_url'],
                $title,
                $body,
                self::NOTIFY_COLORS['error']
            );

            if (!$sent) {
                error_log('VNX Cache Scheduler: gửi thông báo lỗi Discord (phần ' . ($index + 1) . '/' . $total
                    . ') cho lịch "' . $label
                    . '" thất bại (Discord từ chối request), xem log "Discord webhook send failed" ở trên.');
            }
        }
    }

    /** Cắt một khối văn bản thành nhiều mảnh vừa giới hạn description của Discord. */
    private function splitForDiscord($text, $limit = self::NOTIFY_CHUNK_LIMIT)
    {
        $chunks = [];
        $current = '';

        foreach (explode("\n", $text) as $line) {
            while (mb_strlen($line) > $limit) {
                if ($current !== '') {
                    $chunks[] = $current;
                    $current = '';
                }

                $chunks[] = mb_substr($line, 0, $limit);
                $line = mb_substr($line, $limit);
            }

            $candidate = $current === '' ? $line : $current . "\n" . $line;
            if (mb_strlen($candidate) > $limit) {
                $chunks[] = $current;
                $current = $line;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /** Báo lượt chạy vừa chết và đang được cào tiếp từ chỗ dừng, nuốt mọi lỗi phát sinh. */
    private function notifyJobResumed(array $job, $reason, $nextSkip = 0)
    {
        try {
            $settings = $this->getNotifySettings();
            if (!$settings['enabled'] || $settings['webhook_url'] === '') {
                return;
            }

            $done = (int) ($job['last_done'] ?? 0);
            $total = (int) ($job['last_total'] ?? 0);
            $nextSkip = max(0, (int) $nextSkip);
            $progressLine = $total > 0 ? "**Đã cào được:** {$done}/{$total} URL\n\n" : '';

            $planLine = $nextSkip > 0
                ? 'Hệ thống cào tiếp từ URL thứ ' . ($nextSkip + 1) . ', không cào lại từ đầu.'
                : 'Không xác định được lượt chạy dừng ở URL nào, hệ thống chạy lại từ URL đầu tiên.';

            DiscordBot::sendMessageByWebhook(
                $settings['webhook_url'],
                '🔁 Lượt chạy chết giữa chừng, đang cào tiếp: ' . ($job['label'] ?? ''),
                "**Lý do:** {$reason}\n\n" . $progressLine
                    . $planLine . "\n\n"
                    . $this->noticeFooter('Hệ thống (tự động cào tiếp)'),
                self::NOTIFY_COLORS['warn']
            );
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: gửi thông báo cào tiếp Discord thất bại: ' . $e->getMessage());
        }
    }

    /** Báo lịch chạy bị huỷ giữa chừng, nuốt mọi lỗi phát sinh. */
    private function notifyJobCancelled(array $job, $actor, $reason)
    {
        try {
            $this->sendJobCancelledNotice($job, $actor, $reason);
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: gửi thông báo huỷ Discord thất bại: ' . $e->getMessage());
        }
    }

    private function sendJobCancelledNotice(array $job, $actor, $reason)
    {
        $settings = $this->getNotifySettings();
        if (!$settings['enabled'] || $settings['webhook_url'] === '') {
            return;
        }

        $reason = trim($this->scalarString($reason)) ?: 'Không rõ lý do.';

        $done = (int) ($job['last_done'] ?? 0);
        $total = (int) ($job['last_total'] ?? 0);
        $progressLine = $total > 0 ? "**Tiến độ khi huỷ:** {$done}/{$total} URL\n\n" : '';

        $sent = DiscordBot::sendMessageByWebhook(
            $settings['webhook_url'],
            '⏹️ Lịch xoá cache ĐÃ HUỶ: ' . ($job['label'] ?? ''),
            "**Lý do:** {$reason}\n\n" . $progressLine
                . $this->describeJobScope($job) . "\n\n"
                . $this->noticeFooter($actor),
            self::NOTIFY_COLORS['cancelled']
        );

        if (!$sent) {
            error_log('VNX Cache Scheduler: gửi thông báo huỷ Discord cho lịch "' . ($job['label'] ?? '')
                . '" thất bại (Discord từ chối request), xem log "Discord webhook send failed" ở trên.');
        }
    }

    /** Mô tả ngắn gọn ngoại lệ để đưa vào thông báo: message + nơi ném. */
    private function describeThrowable(\Throwable $e)
    {
        return get_class($e) . ': ' . $e->getMessage()
            . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')';
    }



    // --- Cron engine ---

    /** Điểm vào của cron: chạy mọi lịch đã tới hạn rồi tính mốc kế tiếp. */
    public function runDueJobs()
    {
        $this->releaseStaleRuns($this->getJobs());

        if ($this->getLiteSpeedStatus() !== 'active' || !$this->isAutoEnabled()) {
            return;
        }

        if ($this->lockHolder() !== null) {
            return;
        }

        $this->acquireLock('cron');

        $handoffId = null;

        try {
            $jobs = $this->getJobs();
            $now = time();
            $changed = false;

            $tickDeadline = microtime(true) + $this->warmTimeBudget();

            foreach ($jobs as &$job) {
                if (empty($job['enabled']) || empty($job['next_run_at']) || $job['next_run_at'] > $now) {
                    continue;
                }

                // Lượt trước còn dang dở: mở lượt mới sẽ ghi đè con trỏ và cào lại từ đầu.
                if (($job['last_status'] ?? '') === 'running') {
                    continue;
                }

                if ($this->pastDeadline($tickDeadline)) {
                    // Chưa đụng next_run_at nên các lịch còn lại vẫn tới hạn ở tick sau.
                    error_log('VNX Cache Scheduler: hết ngân sách thời gian của lượt cron, hoãn các lịch còn lại sang tick sau.');
                    break;
                }

                $job['last_status'] = 'running';
                $job['run_started_at'] = $now;
                $job['run_id'] = $this->newRunId();
                $this->runId = $job['run_id'];
                $this->saveJobs($jobs);

                // Cờ huỷ sót của lượt trước không được phép giết lượt mới do cron mở.
                $this->forgetCancel((string) ($job['id'] ?? ''));

                $this->acquireLock((string) ($job['id'] ?? ''));

                try {
                    $reason = $this->purgeJob($job, 'Hệ thống (tự động)', $tickDeadline);

                    // Chặng dừng đúng hạn để bàn giao: lượt chạy chưa khép lại.
                    if ($reason === '' && $this->handoffAt !== null) {
                        $job['last_status'] = 'running';
                        $handoffId = (string) ($job['id'] ?? '');
                        $changed = true;

                        break;
                    }

                    if ($reason === '' && $this->cancelled) {
                        $job['last_status'] = 'cancelled';
                        $job['last_error'] = 'Lượt chạy bị huỷ từ trang quản trị.';
                        $this->notifyJobCancelled($job, 'Hệ thống (tự động)', $job['last_error']);
                    } else {
                        $job['last_status'] = $reason === '' ? 'success' : 'error';
                        $job['last_error'] = $reason;

                        if ($reason !== '') {
                            $this->notifyJobFailure($job, 'Hệ thống (tự động)', $reason);
                        }
                    }
                } catch (\Throwable $e) {
                    $reason = $this->describeThrowable($e);
                    $job['last_status'] = 'error';
                    $job['last_error'] = $reason;
                    $this->notifyJobFailure($job, 'Hệ thống (tự động)', $reason);
                }

                $job['last_run_at'] = $now;
                $job['run_started_at'] = null;
                $job['run_id'] = null;
                $this->runId = '';
                $this->forgetCancel((string) ($job['id'] ?? ''));

                $snapshot = $this->progressSnapshot();
                if ($snapshot !== null) {
                    $job['last_done'] = $snapshot['done'];
                    $job['last_total'] = $snapshot['total'];
                }
                $this->clearProgress((string) ($job['id'] ?? ''));

                $changed = true;

                if (($job['schedule_type'] ?? '') === 'once') {
                    $job['enabled'] = false;
                    $job['next_run_at'] = null;
                } else {
                    // Không hoãn thì next_run_at kẹt ở mốc quá hạn, job chạy lại mỗi tick.
                    try {
                        $job['next_run_at'] = $this->computeNextRun($job, $now);
                    } catch (\Throwable $e) {
                        error_log('VNX Cache Scheduler: tính mốc chạy kế tiếp cho "' . ($job['label'] ?? '') . '" thất bại: ' . $e->getMessage() . ' - hoãn 1 giờ.');
                        $job['next_run_at'] = $now + HOUR_IN_SECONDS;
                    }
                }
            }
            unset($job);

            if ($changed) {
                $this->saveJobs($jobs);
            }
        } finally {
            $this->releaseLock();
        }

        // Phải bàn giao sau finally: còn giữ khoá thì chặng kế tiếp tưởng mình trùng và thoát.
        if ($handoffId !== null && !$this->handOffToNextSegment($handoffId, 'Hệ thống (tự động)')) {
            $this->markJobFinished(
                $handoffId,
                'error',
                'Hệ thống (tự động)',
                'Chặng cào dừng đúng hạn nhưng không giao được việc cho chặng kế tiếp.'
            );
        }
    }

    // --- Purge ---

    /** Chạy trọn một lịch hẹn: xoá cache rồi cào lại, kèm thông báo từng chặng. */
    private function purgeJob(array $job, $actor, $deadline = null)
    {
        $this->runIssues = ['purge' => [], 'warm' => [], 'skipped' => []];
        $this->runTotalUrls = 0;
        $this->handoffAt = null;
        $this->segmentFailed = 0;
        $this->runCarriedFailures = (int) ($job['resume_failed'] ?? 0);

        // Chặng nối tiếp không phải lượt chạy mới nên không báo "bắt đầu" lần nữa.
        $isFirstSegment = (int) ($job['resume_skip'] ?? 0) === 0;

        if ($isFirstSegment) {
            $this->notifyDiscord($job, $actor, 'purge_start');
        }

        // Mot tien trinh cron chay nhieu lich: khong reset thi lich sau bi coi la da huy.
        $this->cancelled = false;
        $this->cancelCheckedAt = null;

        if ($this->lockOwned) {
            $this->lockLost = false;
        }

        $this->runId = (string) ($job['run_id'] ?? '');

        $purgeType = $job['purge_type'] ?? 'all';

        // "Toàn bộ site" tự lấy danh sách trang public rồi cào lại y như nhánh URL cụ thể.
        if ($purgeType === 'all') {
            // Mở tiến độ TRƯỚC khi purge, kẻo cột "Đã xử lý" trơ ra "—" suốt lúc purge.
            $jobUrls = $this->getPublicPageUrls();

            // Danh sach rong thi bao loi ngay, thay vi roi xuong duoi bao "done" gia.
            if (empty($jobUrls)) {
                return 'Không tìm thấy trang public nào để cào lại (kiểm tra lại có "page" nào publish, không bị noindex, không bị redirect hay không).';
            }

            $resumeSkip = max(0, min((int) ($job['resume_skip'] ?? 0), count($jobUrls)));

            $this->startProgress((string) ($job['id'] ?? ''), count($jobUrls), $resumeSkip);

            // Đếm TRƯỚC khi cắt, kẻo tổng chỉ còn là phần chưa cào của chặng này.
            $totalUrls = count($jobUrls);

            if ($resumeSkip > 0) {
                $jobUrls = array_slice($jobUrls, $resumeSkip);
            } else {
                do_action('litespeed_purge_all', 'vnx_cache_scheduler');

                $this->flushPurgeHeader();
                $this->flushLiteSpeedPurgeQueue();
            }

            $readyUrls = $jobUrls; // đã purge toàn site ở trên (hoặc ở lượt trước), khỏi purge lại từng URL.
        } else {
            $jobUrls = array_values(array_filter((array) ($job['urls'] ?? []), 'is_string'));
            $readyUrls = null; // xác định bên dưới, cần xoá cache từng URL trước.
            $totalUrls = count($jobUrls);

            $resumeSkip = max(0, min((int) ($job['resume_skip'] ?? 0), $totalUrls));

            $this->startProgress((string) ($job['id'] ?? ''), $totalUrls, $resumeSkip);

            if ($resumeSkip > 0) {
                $jobUrls = array_slice($jobUrls, $resumeSkip);
            }
        }

        $this->touchProgress();

        $target = $this->loadResolveTarget();
        $variants = $this->buildWarmVariants($target);

        $this->notifyDiscord($job, $actor, 'warm_start');
        $this->touchProgress();

        if (function_exists('set_time_limit')) {
            @set_time_limit($this->warmHardLimit());
        }

        $deadline = $deadline !== null ? (float) $deadline : microtime(true) + $this->warmTimeBudget();

        // Chia chặng cho cả hai kiểu lịch: danh sách dài cũng vượt max_execution_time.
        $chunked = true;
        $segmentDeadline = min($deadline, microtime(true) + self::SEGMENT_TIME_BUDGET);

        // Hết giờ chặng: phần chưa đụng tới là việc của chặng sau, không phải trang lỗi.
        $purgeTruncated = false;

        if ($readyUrls === null) {
            // Xoá cache từng URL trước (xem purgeUrlRemotely() để biết vì sao không gộp lô được).
            $readyUrls = [];

            // Chừa ngân sách cho warm, kẻo URL vừa purge nằm trần và khách phải chờ render lạnh.
            $purgeDeadline = min($segmentDeadline, microtime(true) + (self::SEGMENT_TIME_BUDGET / 2));

            foreach ($jobUrls as $url) {
                $this->touchProgress();

                if ($this->cancelRequested()) {
                    break;
                }

                if ($this->pastDeadline($purgeDeadline)) {
                    $purgeTruncated = true;
                    break;
                }

                if ($this->pastDeadline($deadline - self::WARM_MIN_BUDGET_PER_URL)) {
                    $this->runIssues['skipped'][] = $url;
                    continue;
                }

                $purgeError = $this->purgeUrlRemotely($url);
                if ($purgeError !== null) {
                    $this->runIssues['purge'][$url] = $purgeError;
                    $this->bumpProgress($url);
                    $this->notifyUrlDone($job, $actor, $url, false, $this->progress['done'] ?? 0, $this->progress['total'] ?? 0);
                    continue;
                }

                $readyUrls[] = $url;
            }
        }

        // "Toàn bộ site" cào tuần tự từng URL một theo đúng thứ tự danh sách: cào đồng thời
        $concurrency = $purgeType === 'all' ? 1 : self::WARM_CONCURRENCY;

        // Lô nào chưa hit ngay lượt đồng thời tự rơi về cào tuần tự có retry/backoff.
        $this->runIssues['warm'] = $this->warmUrlsConcurrently($readyUrls, $variants, $target, $segmentDeadline, $job, $actor, $concurrency, $chunked);
        $this->runTotalUrls = $totalUrls;

        // Còn URL chưa đụng tới thì phải bàn giao, kẻo lượt chạy tự tuyên bố xong.
        if (
            $purgeTruncated && $this->handoffAt === null && !$this->cancelled
            && (int) ($this->progress['done'] ?? 0) < $totalUrls
        ) {
            $this->handoffAt = (int) ($this->progress['done'] ?? 0);
        }

        // Mất khoá giữa chừng KHÔNG phải lệnh huỷ: chốt con trỏ rồi để chặng mới cào tiếp.
        if ($this->lockLost && !$this->cancelled) {
            // Dừng cứng từ trang quản trị cũng nhả khoá, phải đọc lại cờ huỷ mới phân biệt được.
            if ($this->cancelTargetsRun($this->readCancel((string) ($job['id'] ?? '')), $this->runId)) {
                $this->cancelled = true;
            } else {
                $done = (int) ($this->progress['done'] ?? 0);

                error_log('VNX Cache Scheduler: lịch "' . ($job['label'] ?? '') . '" mất khoá sau ' . $done
                    . ' URL, bàn giao phần còn lại cho chặng mới.');

                $this->runIssues = ['purge' => [], 'warm' => [], 'skipped' => []];
                $this->handoffAt = $done;

                return '';
            }
        }

        // Huỷ giữa chừng thì số liệu dở dang, báo cáo lúc này chỉ gây hiểu nhầm.
        if ($this->cancelRequested()) {
            error_log('VNX Cache Scheduler: lịch "' . ($job['label'] ?? '') . '" bị huỷ giữa chừng theo yêu cầu.');
            $this->runIssues = ['purge' => [], 'warm' => [], 'skipped' => []];
            $this->handoffAt = null;

            return '';
        }

        if ($this->handoffAt !== null) {
            $this->segmentFailed = count($this->runIssues['purge'])
                + count($this->runIssues['warm'])
                + count($this->runIssues['skipped']);

            if ($this->segmentFailed > 0) {
                error_log('VNX Cache Scheduler: chặng vừa rồi của lịch "' . ($job['label'] ?? '') . '" có '
                    . $this->segmentFailed . ' trang lỗi:' . "\n"
                    . strip_tags(str_replace(['**', '> '], '', $this->detailRunIssues())));
            }

            return '';
        }

        if ($this->hasRunIssues() || $this->runCarriedFailures > 0) {
            return $this->summariseRunIssues();
        }

        $this->notifyDiscord($job, $actor, 'done');

        return '';
    }

    /** Lượt chạy vừa rồi có trang nào trục trặc không. */
    private function hasRunIssues()
    {
        foreach ($this->runIssues as $group) {
            if (!empty($group)) {
                return true;
            }
        }

        return false;
    }

    /** Một dòng tổng kết dùng cho trạng thái lịch và tiêu đề thông báo. */
    private function summariseRunIssues()
    {
        $purge = count($this->runIssues['purge']);
        $warm = count($this->runIssues['warm']);
        $skipped = count($this->runIssues['skipped']);
        $carried = $this->runCarriedFailures;
        $failed = $purge + $warm + $skipped + $carried;
        $total = max($this->runTotalUrls, $failed);

        $parts = [];
        if ($purge > 0) {
            $parts[] = $purge . ' trang không xoá được cache';
        }
        if ($warm > 0) {
            $parts[] = $warm . ' trang cào xong nhưng không hit';
        }
        if ($skipped > 0) {
            $parts[] = $skipped . ' trang chưa kịp crawl (hết ngân sách ' . $this->warmTimeBudget() . ' giây)';
        }
        if ($carried > 0) {
            $parts[] = $carried . ' trang lỗi ở các chặng cào trước';
        }

        $summary = 'Lỗi ' . $failed . '/' . $total . ' trang: ' . implode(', ', $parts) . '.';

        if ($skipped > 0) {
            $summary .= ' Chia lịch này thành nhiều lịch nhỏ chạy lệch giờ.';
        }

        return $summary;
    }

    /** Danh sách trang lỗi, gom theo nhóm nguyên nhân, dùng cho phần chi tiết của thông báo. */
    private function detailRunIssues()
    {
        return implode("\n\n", $this->runIssueBlocks());
    }

    /** Từng nhóm nguyên nhân lỗi là một khối văn bản riêng, để thông báo Discord gửi mỗi nhóm một tin thay vì dồn hết vào một tin rồi bị cắt cụt. */
    private function runIssueBlocks()
    {
        $groups = [
            'purge' => '🚫 Không xoá được cache',
            'warm' => '⚠️ Cào xong nhưng verify không thấy hit',
            'skipped' => '⏱️ Chưa kịp cào vì hết thời gian',
        ];

        $blocks = [];

        foreach ($groups as $key => $title) {
            $items = $this->runIssues[$key];
            if (empty($items)) {
                continue;
            }

            $lines = ['**' . $title . ' (' . count($items) . ')**'];
            $shown = 0;

            foreach ($items as $url => $reason) {
                if ($shown >= self::NOTIFY_URL_LIMIT) {
                    break;
                }

                // Nhóm skipped là danh sách tuần tự nên khoá chính là số thứ tự, không phải URL.
                $line = ($shown + 1) . '. ' . (is_int($url) ? $reason : $url);
                if (!is_int($url)) {
                    $line .= "\n> " . implode("\n> ", (array) $reason);
                }

                $lines[] = $line;
                $shown++;
            }

            if (count($items) > $shown) {
                $lines[] = '… và ' . (count($items) - $shown) . ' trang khác';
            }

            $blocks[] = implode("\n", $lines);
        }

        return $blocks;
    }



    /** Ngân sách thời gian cho phần warm của lượt chạy này. */
    private function warmTimeBudget()
    {
        return self::WARM_TIME_BUDGET;
    }

    /** Mốc set_time_limit() tương ứng với ngân sách, chừa chỗ để kịp ghi trạng thái và gửi thông báo. */
    private function warmHardLimit()
    {
        return self::WARM_HARD_LIMIT;
    }

    /** Timeout cho một request warm/verify, co lại theo thời gian còn lại của URL. */
    private function warmRequestTimeout($deadline = null)
    {
        if ($deadline === null) {
            return self::WARM_REQUEST_TIMEOUT;
        }

        $remaining = (int) floor((float) $deadline - microtime(true));

        // Vẫn phải là số dương, cURL hiểu 0 là "chờ vô hạn".
        return max(5, min(self::WARM_REQUEST_TIMEOUT, $remaining));
    }

    private function pastDeadline($deadline)
    {
        return microtime(true) >= (float) $deadline;
    }

    /** Đẩy header X-LiteSpeed-Purge ra khỏi mọi lớp output buffer trước khi warm. */
    private function flushPurgeHeader()
    {
        try {
            if (headers_sent()) {
                return;
            }

            // Sau flush() thì header() bị bỏ qua, phải set Content-Type trước.
            if (wp_doing_ajax()) {
                header('Content-Type: application/json; charset=' . get_option('blog_charset'));
            }

            // Xả hết mọi lớp buffer, không chỉ lớp kế tiếp.
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();

            // Chờ server xử lý xong X-LiteSpeed-Purge rồi mới warm.
            usleep(300000);
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: flush purge header thất bại: ' . $e->getMessage());
        }
    }

    /** Xả hàng đợi purge của LiteSpeed trước khi warm, nuốt mọi lỗi phát sinh. */
    private function flushLiteSpeedPurgeQueue()
    {
        try {
            $this->doFlushLiteSpeedPurgeQueue();
        } catch (\Throwable $e) {
            // Không xả được thì cache warm có thể bị purge ngay, nhưng vẫn hơn chết cả job.
            error_log('VNX Cache Scheduler: xả hàng đợi purge LiteSpeed ném exception: ' . $e->getMessage());
        }
    }

    /** Gửi request bỏ đi tới admin-ajax.php để LSCWP phát nốt header purge đang xếp hàng. */
    private function doFlushLiteSpeedPurgeQueue()
    {
        $queue = get_option('litespeed.purge.queue');
        if (!$queue || $queue === '-1') {
            return;
        }

        $target = $this->loadResolveTarget();
        $url = $this->buildWarmRequestUrl(admin_url('admin-ajax.php'), $target);
        $args = [
            'timeout' => 15,
            'redirection' => 0,
            'headers' => $target && $target['port'] === 80 ? ['X-Forwarded-Proto' => 'https'] : [],
        ];
        if ($target) {
            $args['sslverify'] = false;
        }

        $response = $this->fetchWarm($url, $args, $target);
        if (is_wp_error($response)) {
            error_log('VNX Cache Scheduler: xả hàng đợi purge LiteSpeed thất bại: ' . $response->get_error_message());
            return;
        }

        error_log('VNX Cache Scheduler: đã xả hàng đợi purge LiteSpeed (' . $queue . ') qua ' . $url . ', status ' . wp_remote_retrieve_response_code($response));

        // Chờ LSWS xử lý purge từ response trên xong rồi mới warm.
        usleep(300000);
    }

    /** Xoá cache 1 URL qua endpoint riêng, đi cùng đường mạng với request warm. */
    private function purgeUrlRemotely($url)
    {
        $token = wp_generate_password(32, false);
        set_transient(self::PURGE_TOKEN_PREFIX . $token, $token, 5 * MINUTE_IN_SECONDS);

        $target = $this->loadResolveTarget();
        $endpoint = $this->buildWarmRequestUrl(admin_url('admin-ajax.php'), $target);

        $args = [
            'method' => 'POST',
            'timeout' => 15,
            'redirection' => 0,
            'cookies' => [],
            'headers' => $target && $target['port'] === 80 ? ['X-Forwarded-Proto' => 'https'] : [],
            'body' => [
                'action' => 'vnx_cache_scheduler_purge_one',
                'token' => $token,
                'url' => $url,
            ],
        ];
        if ($target) {
            $args['sslverify'] = false;
        }

        $response = $this->fetchWarm($endpoint, $args, $target);

        if (is_wp_error($response)) {
            delete_transient(self::PURGE_TOKEN_PREFIX . $token);
            $reason = 'lỗi mạng - ' . $response->get_error_message();
            error_log('VNX Cache Scheduler: purge "' . $url . '" thất bại: ' . $reason);

            return $reason;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            delete_transient(self::PURGE_TOKEN_PREFIX . $token);
            $reason = 'endpoint purge trả về status ' . $code;
            error_log('VNX Cache Scheduler: purge "' . $url . '" thất bại: ' . $reason);

            return $reason;
        }

        // Không có header x-litespeed-*: request không qua LSWS, purge rơi vào hư không.
        if (!$this->describeCacheResult($response, $endpoint)['is_litespeed']) {
            $reason = 'response của endpoint purge không có header x-litespeed-*, request không tới LiteSpeed';
            error_log('VNX Cache Scheduler: purge "' . $url . '" thất bại: ' . $reason);

            return $reason;
        }

        // Chờ LSWS xử lý xong header purge trước khi warm.
        usleep(300000);

        return null;
    }

    // --- Warm - HTTP transport ---

    /** Đích resolve cho các request warm của lượt chạy này. */
    private function loadResolveTarget()
    {
        try {
            if (!class_exists('\LiteSpeed\Conf') || !class_exists('\LiteSpeed\Base')) {
                return null;
            }

            $serverIp = (string) \LiteSpeed\Conf::cls()->conf(\LiteSpeed\Base::O_SERVER_IP);
            if ($serverIp === '' || !filter_var($serverIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return null;
            }

            // Crawler lưu port test được vào summary; chưa test thì mặc định 443.
            $summary = $this->crawlerSummary();
            $port = !empty($summary['test_port']) && (int) $summary['test_port'] === 80 ? 80 : 443;

            return ['ip' => $serverIp, 'port' => $port];
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: đọc server IP LiteSpeed thất bại: ' . $e->getMessage());
            return null;
        }
    }

    /** Callback http_api_curl: ép curl resolve thẳng vào Server IP, né DNS/CDN phía trước. */
    public function resolveDirectToServerIp($handle, $parsedArgs, $requestUrl)
    {
        if (!$this->resolveTarget) {
            return;
        }

        try {
            $parsed = wp_parse_url($requestUrl);
            if (empty($parsed['host'])) {
                return;
            }

            curl_setopt($handle, CURLOPT_RESOLVE, [$parsed['host'] . ':' . $this->resolveTarget['port'] . ':' . $this->resolveTarget['ip']]);
            curl_setopt($handle, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: resolve server IP thất bại: ' . $e->getMessage());
        }
    }

    private function fetchWarm($url, array $args, $target)
    {
        $this->resolveTarget = $target;
        add_action('http_api_curl', [$this, 'resolveDirectToServerIp'], 10, 3);

        try {
            // wp_remote_request để chỗ gọi truyền được 'method'; mặc định vẫn là GET.
            return wp_remote_request($url, $args);
        } catch (\Throwable $e) {
            // wp_remote_request() trả WP_Error cho lỗi mạng, nhưng hook của plugin khác thì ném thẳng.
            error_log('VNX Cache Scheduler: request "' . $url . '" ném exception: ' . $e->getMessage());

            return new \WP_Error('vnx_cache_scheduler_http_exception', $e->getMessage());
        } finally {
            remove_action('http_api_curl', [$this, 'resolveDirectToServerIp'], 10);
            $this->resolveTarget = null;
        }
    }

    private function buildWarmRequestUrl($url, $target)
    {
        if ($target && $target['port'] === 80) {
            return preg_replace('#^https://#i', 'http://', $url);
        }

        return $url;
    }

    private function buildWarmArgs($url, array $variant, $target, $isWarmRequest, $deadline = null)
    {
        $headers = $this->buildWarmHeaders($url, $variant['profile']);

        // Verify không gửi max-age=0, nếu không server bypass cache và luôn báo miss.
        if ($isWarmRequest) {
            $headers['Cache-Control'] = 'max-age=0';
        }

        if ($target && $target['port'] === 80) {
            $headers['X-Forwarded-Proto'] = 'https';
        }

        $args = [
            'timeout' => $this->warmRequestTimeout($deadline),
            'redirection' => 3,
            'user-agent' => $variant['profile']['user_agent'],
            'headers' => $headers,
        ];

        if (!empty($variant['cookies'])) {
            $args['cookies'] = $variant['cookies'];
        }

        // Ép IP thì cert origin không khớp domain, phải tắt verify SSL.
        if ($target) {
            $args['sslverify'] = false;
        }

        return $args;
    }

    private function buildWarmHeaders($url, array $profile)
    {
        $headers = [
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5',
            'Upgrade-Insecure-Requests' => '1',
            'Referer' => $url,
            'Priority' => 'u=0, i',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'same-origin',
            'Sec-Fetch-User' => '?1',
        ];

        if ($profile['sec_ch_ua']) {
            $headers['Sec-Ch-Ua'] = $profile['sec_ch_ua'];
            $headers['Sec-Ch-Ua-Mobile'] = $profile['sec_ch_ua_mobile'];
            $headers['Sec-Ch-Ua-Platform'] = $profile['sec_ch_ua_platform'];
        }

        return $headers;
    }

    // --- Warm - cache variants ---

    /** Tên cookie vary của LiteSpeed (mặc định _lscache_vary, đổi được qua setting Login Cookie). */
    private function litespeedVaryName()
    {
        try {
            if (class_exists('\LiteSpeed\Vary')) {
                $name = (string) \LiteSpeed\Vary::cls()->get_vary_name();
                if ($name !== '') {
                    return $name;
                }
            }
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: đọc tên cookie vary thất bại: ' . $e->getMessage());
        }

        return '_lscache_vary';
    }

    /** Giá trị vary tự tính theo guest.vary.php, chỉ dùng dự phòng khi không hỏi được server. */
    private function guestModeVaryValue()
    {
        try {
            if (!class_exists('\LiteSpeed\Conf') || !class_exists('\LiteSpeed\Base')) {
                return null;
            }

            $conf = \LiteSpeed\Conf::cls();
            if (!$conf->conf(\LiteSpeed\Base::O_GUEST)) {
                return null;
            }

            $value = 'guest_mode:1';
            if (empty($conf->conf(\LiteSpeed\Base::O_DEBUG))) {
                $value = md5($conf->conf(\LiteSpeed\Base::HASH) . $value);
            }

            return $value;
        } catch (\Throwable $e) {
            error_log('VNX Cache Scheduler: dựng giá trị vary Guest Mode thất bại: ' . $e->getMessage());
            return null;
        }
    }

    /** Lấy cookie vary thật của khách: hỏi thẳng guest.vary.php, lệch thì mới lùi về giá trị tự tính. */
    private function resolveGuestVary($profileName, array $profile, $fallback, $target)
    {
        if (array_key_exists($profileName, $this->guestVaryCache)) {
            return $this->guestVaryCache[$profileName];
        }

        $value = $this->fetchGuestVaryFromServer($profile, $target);

        if ($value === null) {
            error_log('VNX Cache Scheduler: không lấy được cookie vary từ guest.vary.php (' . $profileName . '), tạm dùng giá trị tự tính.');
            $value = $fallback;
        } elseif ($value !== $fallback) {
            // Không phải lỗi, nhưng công thức tự tính đã lệch với thực tế.
            error_log('VNX Cache Scheduler: cookie vary server cấp (' . $profileName . ') khác giá trị tự tính, dùng giá trị của server.');
        }

        $this->guestVaryCache[$profileName] = $value;

        return $value;
    }

    /** Gọi guest.vary.php đúng như trình duyệt của khách gọi, rồi đọc Set-Cookie. */
    private function fetchGuestVaryFromServer(array $profile, $target)
    {
        if (!defined('WP_PLUGIN_DIR')) {
            return null;
        }

        try {
            return $this->requestGuestVary($profile, $target);
        } catch (\Throwable $e) {
            // Bước phụ trợ: lỗi thì lùi về giá trị tự tính, không đánh hỏng cả URL.
            error_log('VNX Cache Scheduler: lấy cookie vary ném exception: ' . $e->getMessage());

            return null;
        }
    }

    private function requestGuestVary(array $profile, $target)
    {
        $endpoint = plugins_url('guest.vary.php', WP_PLUGIN_DIR . '/' . self::LS_PLUGIN_FILE);
        $headers = ['Accept' => 'application/json'];

        if ($target && $target['port'] === 80) {
            $headers['X-Forwarded-Proto'] = 'https';
        }

        $args = [
            'timeout' => 10,
            // Port 80 gọi bằng http:// nên LSWS hay 301 sang https, chặn redirect là mất cookie.
            'redirection' => 3,
            'user-agent' => $profile['user_agent'],
            'headers' => $headers,
        ];

        if ($target) {
            $args['sslverify'] = false;
        }

        // Đi cùng đường với request warm để lấy cookie của đúng vhost.
        $response = $this->fetchWarm($this->buildWarmRequestUrl($endpoint, $target), $args, $target);

        if (is_wp_error($response)) {
            error_log('VNX Cache Scheduler: gọi guest.vary.php thất bại: ' . $response->get_error_message());
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            error_log('VNX Cache Scheduler: guest.vary.php trả về status ' . $code . '.');
            return null;
        }

        $value = (string) wp_remote_retrieve_cookie_value($response, $this->litespeedVaryName());

        return $value !== '' ? $value : null;
    }

    /** Liệt kê mọi nhóm cache cần cào cho một URL. */
    private function buildWarmVariants($target)
    {
        $profiles = ['desktop' => self::WARM_UA_PROFILES['desktop']];
        if ($this->isMobileCacheEnabled()) {
            $profiles['mobile'] = self::WARM_UA_PROFILES['mobile'];
        }

        // Guest Mode tắt thì khách vào thẳng bucket không cookie.
        $fallbackVary = $this->guestModeVaryValue();
        $varyName = $this->litespeedVaryName();

        $variants = [];
        foreach ($profiles as $profileName => $profileData) {
            $cookieSets = ['' => []];
            if ($fallbackVary !== null) {
                // Plugin khác có thể hook litespeed_vary để tách bucket theo thiết bị.
                $cookieSets = [
                    'guest' => [],
                    'vary' => [$varyName => $this->resolveGuestVary($profileName, $profileData, $fallbackVary, $target)],
                ];
            }

            foreach ($cookieSets as $cookieName => $cookies) {
                $label = $profileName . ($cookieName !== '' ? ' + ' . $cookieName : '');
                $variants[] = [
                    'label' => $label,
                    'profile' => $profileData,
                    'cookies' => $cookies,
                ];
            }
        }

        return $variants;
    }

    // --- Warm - concurrent batch ---

    /** Cào nhiều URL cùng lúc theo lô $concurrency (mặc định WARM_CONCURRENCY); lô nào chưa hit rơi về warmVariant() cào tuần tự có retry. */
    private function warmUrlsConcurrently(array $urls, array $variants, $target, $deadline, array $job = [], $actor = null, $concurrency = self::WARM_CONCURRENCY, $chunked = false)
    {
        $missed = [];
        $concurrency = max(1, (int) $concurrency);

        foreach (array_chunk($urls, $concurrency) as $batch) {
            // Mỗi lô kiểm tra một lần: đủ dày để nút Huỷ có tác dụng, đủ thưa để nhẹ DB.
            if ($this->cancelRequested()) {
                break;
            }

            // Chế độ chia chặng: hết giờ thì phần còn lại là việc BÀN GIAO, không phải trang lỗi.
            if ($chunked && $this->pastDeadline($deadline)) {
                $this->handoffAt = (int) ($this->progress['done'] ?? 0);

                break;
            }

            if (!$chunked && $this->pastDeadline($deadline - self::WARM_MIN_BUDGET_PER_URL)) {
                // "Chưa kịp cào" khác hẳn "cào xong mà không hit", không được gộp chung.
                foreach ($batch as $url) {
                    $this->runIssues['skipped'][] = $url;
                }
                continue;
            }

            // Lo song song chay warm roi verify nen co the ngon toi 2 x WARM_REQUEST_TIMEOUT.
            $batchDeadline = min((float) $deadline, microtime(true) + self::WARM_URL_TIME_LIMIT);

            $hits = $this->warmBatchOnce($batch, $variants, $target, $batchDeadline);
            $requestUrls = [];
            $route = $target ? 'qua IP ' . $target['ip'] . ':' . $target['port'] : 'qua DNS';

            foreach ($batch as $url) {
                if ($this->cancelRequested()) {
                    break;
                }

                // Han chung phai kiem tra o day chu khong chi o dau moi lo.
                if ($chunked && $this->pastDeadline($deadline)) {
                    $this->handoffAt = (int) ($this->progress['done'] ?? 0);

                    break 2;
                }

                if ($this->pastDeadline($deadline)) {
                    $this->runIssues['skipped'][] = $url;
                    continue;
                }

                // Han rieng cua URL nay, khong bao gio vuot han chung cua ca luot.
                $urlDeadline = min((float) $deadline, microtime(true) + self::WARM_URL_TIME_LIMIT);

                $failures = [];

                foreach ($variants as $vi => $variant) {
                    if (!empty($hits[$url][$vi])) {
                        continue;
                    }

                    if (!isset($requestUrls[$url])) {
                        $requestUrls[$url] = $this->buildWarmRequestUrl($url, $target);
                    }

                    $failure = $this->warmVariant($url, $requestUrls[$url], $variant, $target, $route, $urlDeadline);
                    if ($failure !== null) {
                        $failures[] = $failure;
                    }
                }

                if (!empty($failures)) {
                    $missed[$url] = $failures;
                }

                $this->bumpProgress($url);
                $this->notifyUrlDone($job, $actor, $url, empty($failures), $this->progress['done'] ?? 0, $this->progress['total'] ?? 0);

                // Chi nghi khi cao tuan tu; mode dong thoi da bi gioi han bang WARM_CONCURRENCY.
                if ($concurrency === 1 && !$this->pastDeadline($deadline)) {
                    usleep(self::WARM_SEQUENTIAL_GAP_USEC);
                }
            }
        }

        return $missed;
    }

    /** Warm rồi verify đồng thời cho mọi (URL, nhóm cache) trong 1 lô, không retry. Trả về $result[$url][$variantIndex] = hit hay không. */
    private function warmBatchOnce(array $urls, array $variants, $target, $deadline = null)
    {
        if (!function_exists('curl_multi_init')) {
            return [];
        }

        $tasks = [];
        foreach ($urls as $url) {
            $requestUrl = $this->buildWarmRequestUrl($url, $target);
            foreach ($variants as $vi => $variant) {
                $tasks[] = [
                    'url' => $url,
                    'request_url' => $requestUrl,
                    'variant_index' => $vi,
                    'variant' => $variant,
                ];
            }
        }

        if (empty($tasks)) {
            return [];
        }

        // Lần 1: warm - ép render lại.
        $warmArgs = [];
        foreach ($tasks as $i => $task) {
            $warmArgs[$i] = $this->buildWarmArgs($task['url'], $task['variant'], $target, true, $deadline);
        }
        $this->runCurlMultiBatch($tasks, $warmArgs, $target);

        usleep(300000); // cho LiteSpeed kịp ghi cache mới trước khi verify.

        // Lần 2: verify - không gửi Cache-Control để chắc chắn đọc từ cache.
        $verifyArgs = [];
        foreach ($tasks as $i => $task) {
            $verifyArgs[$i] = $this->buildWarmArgs($task['url'], $task['variant'], $target, false, $deadline);
        }
        $responses = $this->runCurlMultiBatch($tasks, $verifyArgs, $target);

        $hits = [];
        foreach ($tasks as $i => $task) {
            $response = $responses[$i] ?? null;
            $cache = $response !== null ? strtolower($response['headers']['x-litespeed-cache'] ?? '') : '';

            error_log('VNX Cache Scheduler: verify warm đồng thời (' . $task['variant']['label'] . ') "'
                . $task['url'] . '": ' . ($response !== null
                    ? 'status ' . $response['status'] . ', x-litespeed-cache=' . ($cache !== '' ? $cache : '(rỗng)')
                    : 'không có phản hồi (lỗi curl)'));

            $hits[$task['url']][$task['variant_index']] = ($cache === 'hit');
        }

        return $hits;
    }

    /** Chạy nhiều request GET song song bằng curl_multi (wp_remote_request chỉ chạy tuần tự). */
    private function runCurlMultiBatch(array $tasks, array $args, $target)
    {
        $mh = curl_multi_init();
        $handles = [];

        foreach ($tasks as $i => $task) {
            $taskArgs = $args[$i];
            $headers = [];
            foreach ((array) $taskArgs['headers'] as $name => $value) {
                $headers[] = $name . ': ' . $value;
            }

            $verifySsl = array_key_exists('sslverify', $taskArgs) ? (bool) $taskArgs['sslverify'] : true;

            $ch = curl_init($task['request_url']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_TIMEOUT => (int) ($taskArgs['timeout'] ?? self::WARM_REQUEST_TIMEOUT),
                CURLOPT_USERAGENT => $taskArgs['user-agent'],
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => $verifySsl,
                CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            ]);

            if (!empty($taskArgs['cookies'])) {
                $cookiePairs = [];
                foreach ($taskArgs['cookies'] as $name => $value) {
                    $cookiePairs[] = $name . '=' . rawurlencode($value);
                }
                curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookiePairs));
            }

            if ($target) {
                $host = wp_parse_url($task['request_url'], PHP_URL_HOST);
                if ($host) {
                    curl_setopt($ch, CURLOPT_RESOLVE, [$host . ':' . $target['port'] . ':' . $target['ip']]);
                    curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
                }
            }

            curl_multi_add_handle($mh, $ch);
            $handles[$i] = $ch;
        }

        $running = null;
        $lastTouch = microtime(true);
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) {
                curl_multi_select($mh, 1);

                // Lô đồng thời có thể chờ mạng hàng chục giây liền.
                if (microtime(true) - $lastTouch >= self::HEARTBEAT_TOUCH_EVERY) {
                    $this->touchProgress();
                    $lastTouch = microtime(true);
                }
            }
        } while ($running && $status === CURLM_OK);

        $results = [];
        foreach ($handles as $i => $ch) {
            $raw = curl_multi_getcontent($ch);
            $errno = curl_errno($ch);

            if ($errno !== 0 || $raw === null) {
                error_log('VNX Cache Scheduler: request đồng thời "' . $tasks[$i]['request_url'] . '" lỗi curl: ' . curl_error($ch));
                $results[$i] = null;
            } else {
                $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $results[$i] = [
                    'status' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
                    'headers' => $this->parseCurlHeaders(substr($raw, 0, $headerSize)),
                ];
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);

        return $results;
    }

    /** Header của response cuối cùng (sau mọi redirect) dạng tên đã lowercase => giá trị. */
    private function parseCurlHeaders($rawHeaders)
    {
        // FOLLOWLOCATION gộp header mọi chặng redirect vào 1 chuỗi; chỉ khối cuối là response thật.
        $blocks = preg_split('/\r?\n\r?\n/', trim($rawHeaders));
        $lastBlock = end($blocks);

        $headers = [];
        foreach (explode("\n", (string) $lastBlock) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, ':') === false) {
                continue;
            }

            [$name, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }

        return $headers;
    }

    // --- Warm - execution & verify ---

    /** Cào 1 nhóm cache cho tới khi verify thấy hit. */
    private function warmVariant($url, $requestUrl, array $variant, $target, $route, $deadline = null)
    {
        $profile = $variant['label'];
        $lastDetail = null;

        for ($attempt = 1; $attempt <= self::WARM_MAX_ATTEMPTS; $attempt++) {
            // Một lần thử có thể ngốn tới WARM_REQUEST_TIMEOUT giây.
            $this->touchProgress();

            // Một URL có thể ngốn tới ba lần thử nên phải hỏi cờ huỷ ở đây.
            if ($this->cancelRequested()) {
                return ($lastDetail !== null ? $lastDetail . ' ' : '') . '(dừng theo yêu cầu huỷ)';
            }

            // Kiem tra truoc MOI lan thu: het han tu nhom truoc thi nhom sau khong duoc chay.
            if ($deadline !== null && $this->pastDeadline($deadline)) {
                return ($lastDetail !== null ? $lastDetail . ' ' : '')
                    . '(hết trần ' . self::WARM_URL_TIME_LIMIT . 's của URL này sau '
                    . ($attempt - 1) . ' lần thử)';
            }

            $suffix = $attempt > 1 ? ', lần thử ' . $attempt : '';

            // Lần 1: warm - request khiến PHP render lại và LiteSpeed lưu cache mới.
            $response = $this->fetchWarm($requestUrl, $this->buildWarmArgs($url, $variant, $target, true, $deadline), $target);

            if (is_wp_error($response)) {
                error_log('VNX Cache Scheduler: warm url thất bại (' . $profile . ', ' . $route . $suffix . ') "' . $url . '": ' . $response->get_error_message());
                $lastDetail = $profile . ' (' . $route . '): lỗi mạng - ' . $response->get_error_message();
                $this->warmBackoff($attempt);
                continue;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code < 200 || $code >= 400) {
                error_log('VNX Cache Scheduler: warm url (' . $profile . ', ' . $route . $suffix . ') "' . $url . '" trả về status ' . $code . ' (có thể bị chặn bởi WAF/security plugin, cache không được tạo đúng).');
                $lastDetail = $profile . ' (' . $route . '): status ' . $code;

                // 4xx là lỗi cố định, thử lại vô ích; 5xx có thể chỉ quá tải nhất thời.
                if ($code < 500) {
                    return $lastDetail;
                }
                $this->warmBackoff($attempt);
                continue;
            }

            // Request warm đầu tiên của một nhóm bắt buộc phải là miss.
            if ($attempt === 1) {
                $warmResult = $this->describeCacheResult($response, $requestUrl);
                if ($warmResult['cache'] === 'hit') {
                    error_log('VNX Cache Scheduler: request warm (' . $profile . ', ' . $route . ') "' . $url . '" đã trả hit, cache cũ chưa bị xoá.');

                    return $profile . ' (' . $route . '): ' . $warmResult['summary']
                        . ' → cache cũ chưa bị xoá, kiểm tra bước purge (Server IP/vhost hoặc header X-LiteSpeed-Purge không tới LSWS)';
                }
            }

            usleep(300000);

            $this->touchProgress();

            // Lần 2: verify, KHÔNG gửi Cache-Control để chắc chắn đọc từ cache.
            $verify = $this->fetchWarm($requestUrl, $this->buildWarmArgs($url, $variant, $target, false, $deadline), $target);

            if (is_wp_error($verify)) {
                error_log('VNX Cache Scheduler: verify warm thất bại (' . $profile . ', ' . $route . $suffix . ') "' . $url . '": ' . $verify->get_error_message());
                $lastDetail = $profile . ' (' . $route . '): verify lỗi mạng - ' . $verify->get_error_message();
                $this->warmBackoff($attempt);
                continue;
            }

            $result = $this->describeCacheResult($verify, $requestUrl);
            error_log('VNX Cache Scheduler: verify warm (' . $profile . ', ' . $route . $suffix . ') "' . $url . '": ' . $result['summary']);

            if ($result['cache'] === 'hit') {
                return null;
            }

            $lastDetail = $profile . ' (' . $route . '): ' . $result['summary'];

            // Server nói rõ trang không được phép cache thì cào lại bao nhiêu lần cũng vô ích.
            if (stripos($result['control'], 'no-cache') !== false) {
                return $lastDetail . ' → trang bị loại khỏi cache, kiểm tra mục Excludes của LiteSpeed hoặc plugin đang set no-cache';
            }

            $this->warmBackoff($attempt);
        }

        // Verify lại qua DNS để phân biệt lỗi cấu hình Server IP với lỗi cache thật.
        if ($target) {
            $dnsVerify = $this->fetchWarm($url, $this->buildWarmArgs($url, $variant, null, false, $deadline), null);
            $dnsSummary = is_wp_error($dnsVerify)
                ? 'lỗi mạng - ' . $dnsVerify->get_error_message()
                : $this->describeCacheResult($dnsVerify, $url)['summary'];
            error_log('VNX Cache Scheduler: verify lại qua DNS (' . $profile . ') "' . $url . '": ' . $dnsSummary);
            $lastDetail .= ' | qua DNS: ' . $dnsSummary . ' → kiểm tra lại Server IP trong LiteSpeed Cache > General';
        }

        return $lastDetail . ' (đã thử ' . self::WARM_MAX_ATTEMPTS . ' lần)';
    }

    /** Nghỉ giãn dần giữa các lần cào lại: 0,5s rồi 1,5s. */
    private function warmBackoff($attempt)
    {
        // Giãn dần: 0,5s rồi 1,5s. Cho LiteSpeed kịp ghi cache trước khi verify lại.
        usleep($attempt * 500000 + 500000);
    }

    private function retrieveHeaderString($response, $name)
    {
        $value = wp_remote_retrieve_header($response, $name);

        return is_array($value) ? implode(', ', $value) : (string) $value;
    }

    private function responseFinalUrl($response)
    {
        try {
            if (!empty($response['http_response']) && is_object($response['http_response']) && method_exists($response['http_response'], 'get_response_object')) {
                return (string) $response['http_response']->get_response_object()->url;
            }
        } catch (\Throwable $e) {
            // Không lấy được URL cuối thì bỏ qua, chỉ dùng để hiển thị.
        }

        return '';
    }

    private function describeCacheResult($response, $requestedUrl)
    {
        $cache = strtolower($this->retrieveHeaderString($response, 'x-litespeed-cache'));
        $control = $this->retrieveHeaderString($response, 'x-litespeed-cache-control');
        $tag = $this->retrieveHeaderString($response, 'x-litespeed-tag');
        $finalUrl = $this->responseFinalUrl($response);

        $parts = [
            'status ' . wp_remote_retrieve_response_code($response),
            'x-litespeed-cache=' . ($cache !== '' ? $cache : '(rỗng)'),
        ];
        if ($control !== '') {
            $parts[] = 'cache-control=' . $control;
        }
        if ($finalUrl !== '' && untrailingslashit($finalUrl) !== untrailingslashit($requestedUrl)) {
            $parts[] = 'redirect tới ' . $finalUrl;
        }

        return [
            'cache' => $cache,
            'control' => $control,
            // Có header x-litespeed-* = đã tới đúng LSWS; không có = nhầm vhost.
            'is_litespeed' => $cache !== '' || $control !== '' || $tag !== '',
            'summary' => implode(', ', $parts),
        ];
    }

    // --- Schedule math ---

    private function computeNextRun(array $job, $from)
    {
        switch ($job['schedule_type']) {
            case 'once':
                return $this->parseLocalDateTime($job['run_at']) ?: null;
            case 'interval':
                return $from + max(1, (int) $job['interval_minutes']) * 60;
            case 'daily':
                return $this->nextDailyTime($job['time_of_day'], $from);
            case 'weekly':
                return $this->nextWeeklyTime($job['time_of_day'], $job['weekdays'], $from);
        }

        return null;
    }

    /** Mốc 'HH:MM' gần nhất còn ở phía trước, tính theo timezone của site. */
    private function nextDailyTime($hhmm, $from)
    {
        [$h, $m] = array_map('intval', explode(':', $hhmm));

        $dt = new \DateTime('@' . $from);
        $dt->setTimezone(wp_timezone());
        $dt->setTime($h, $m, 0);

        if ($dt->getTimestamp() <= $from) {
            $dt->modify('+1 day');
        }

        return $dt->getTimestamp();
    }

    /** Mốc 'HH:MM' gần nhất rơi vào một trong các thứ đã chọn. */
    private function nextWeeklyTime($hhmm, array $weekdays, $from)
    {
        if (empty($weekdays)) {
            return null;
        }

        [$h, $m] = array_map('intval', explode(':', $hhmm));

        for ($i = 0; $i <= 7; $i++) {
            $dt = new \DateTime('@' . $from);
            $dt->setTimezone(wp_timezone());
            $dt->modify("+{$i} day");
            $dt->setTime($h, $m, 0);

            if (in_array((int) $dt->format('N'), $weekdays, true) && $dt->getTimestamp() > $from) {
                return $dt->getTimestamp();
            }
        }

        return null;
    }
}

VietnixCacheScheduler_Center::instance();
