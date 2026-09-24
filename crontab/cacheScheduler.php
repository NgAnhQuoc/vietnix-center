<?php

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
vnx_cron_require_active_plugin_Center('cache scheduler');

// Plugin dang active co the da nap class nay roi; require de nua la fatal redeclare.
if (!class_exists('VietnixCacheScheduler_Center')) {
    $vnx_cache_scheduler_path = dirname(__DIR__) . '/tools/vietnix-cache-scheduler.php';
    if (!file_exists($vnx_cache_scheduler_path)) {
        error_log('Không tìm thấy file ' . $vnx_cache_scheduler_path);
        exit();
    }

    require_once $vnx_cache_scheduler_path;
}

if (!class_exists('VietnixCacheScheduler_Center')) {
    error_log('Không tìm thấy class VietnixCacheScheduler_Center');
    exit();
}

class VietnixCacheSchedulerCronjob_Center
{
    /** @var VietnixCacheScheduler_Center */
    private $vnx_cache_scheduler;

    public function __construct()
    {
        $this->vnx_cache_scheduler = VietnixCacheScheduler_Center::instance();
        $this->runDueJobs();
    }

    public function runDueJobs()
    {
        try {
            $this->vnx_cache_scheduler->runDueJobs();
        } catch (\Throwable $ex) {
            error_log('Lỗi cron cache scheduler: ' . $ex->getMessage());
        }
    }
}

new VietnixCacheSchedulerCronjob_Center();
