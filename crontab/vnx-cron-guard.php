<?php

// Cron hệ thống gọi thẳng file PHP nên không đi qua active_plugins: không tự kiểm tra
// thì tắt plugin trong wp-admin vẫn không dừng được lịch, mà job lại chạy hỏng vì
// request loopback rơi vào ngữ cảnh không còn plugin nên hook ajax không được đăng ký.

if (!function_exists('vnx_cron_active_vietnix_plugin_Center')) {
    // Chỉ chấp nhận khi CHÍNH vietnix-center (thư mục chứa file cron này) đang active: job
    // dùng class riêng của center (hậu tố Center), vietnix-plugin không có các class đó.
    // Nếu cho qua khi chỉ vietnix-plugin active thì cron sẽ tự require và chạy code của
    // center dù center đã tắt (xoá cache, cập nhật embeddings...), chồng lên cron của plugin kia.
    // Muốn job chạy khi chỉ dùng vietnix-plugin thì trỏ crontab vào thư mục crontab của nó.
    function vnx_cron_active_vietnix_plugin_Center()
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $dir = basename(dirname(__DIR__));

        return is_plugin_active($dir . '/vietnix-center.php') ? $dir : null;
    }
}

if (!function_exists('vnx_cron_require_active_plugin_Center')) {
    function vnx_cron_require_active_plugin_Center($cronLabel)
    {
        if (!defined('ABSPATH')) {
            error_log('VNX cron (' . $cronLabel . '): WordPress chưa được load, bỏ qua lượt chạy.');
            exit();
        }

        if (vnx_cron_active_vietnix_plugin_Center() === null) {
            error_log('VNX cron (' . $cronLabel . '): bỏ qua lượt chạy, plugin Vietnix Center đang không active.');
            exit();
        }
    }
}
