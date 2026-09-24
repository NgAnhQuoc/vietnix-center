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
vnx_cron_require_active_plugin_Center('export sitemap');

if (!class_exists('VietnixExportSitemap_Center')) {
    error_log('Không tìm thấy class VietnixExportSitemap_Center');
    exit();
}

class VietnixExportSitemapCronjob_Center
{
    private $vnx_export_sitemap;

    public function __construct()
    {
        $this->vnx_export_sitemap = new VietnixExportSitemap_Center();
        $this->runExport();
    }

    /**
     * Chạy export sitemap lên Google Sheet
     * Được gọi trực tiếp từ system crontab (mỗi 30 phút)
     */
    public function runExport()
    {
        try {
            date_default_timezone_set('Asia/Ho_Chi_Minh');

            $result = $this->vnx_export_sitemap->exportToGoogleSheet();

            if ($result === true) {
                error_log('Cron export sitemap thành công lúc ' . date('Y-m-d H:i:s'));
            } else {
                error_log('Cron export sitemap thất bại: ' . $result);
            }
        } catch (Exception $ex) {
            error_log("Lỗi cron export sitemap: " . $ex->getMessage());
        }
    }
}

new VietnixExportSitemapCronjob_Center();
