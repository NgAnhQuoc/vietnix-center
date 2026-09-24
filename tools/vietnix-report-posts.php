<?php

namespace VietnixRequiesCenter;

use HelperCenter\View;
use HelperCenter\DiscordBot;

class VietnixReportPosts
{

    // Data option default
    // {
    //     "linkhooks": "string",
    //     "daily": {
    //       "title": "string",
    //       "time": "string"
    //     },
    //     "weekly": {
    //       "title": "string",
    //       "time": "string",
    //       "day": "string"
    //     }
    // }

    public $nameOption = 'vnx_report_posts_setting';
    public function __construct()
    {
        $this->init();
    }

    function init()
    {

        add_action('wp_ajax_save_vietnix_settings_center', [$this, 'handleSaveSettings']);

        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'loadJs']);
        add_action('admin_enqueue_scripts', [$this, 'loadCss']);


        // Hook vào sự kiện khi bài viết được xuất bản.
        // Chay song song thi vietnix-plugin da ghi date_first_public, khong dang ky lai.
        if (vnx_center_companion_mode()) {
            return;
        }
        add_action('transition_post_status', function ($new_status, $old_status, $post) {
            if ($post->post_type !== 'post') {
                return;
            }

            if ($old_status !== 'publish' && $new_status === 'publish') {
                if (!get_post_meta($post->ID, 'date_first_public', true)) {
                    update_post_meta($post->ID, 'date_first_public', current_time('mysql'));
                }
            }
        }, 10, 3);
    }



    public function loadJs()
    {
        // Chi nap tren trang cua center: nap o moi trang admin (ke ca trang cua vietnix-plugin) thi Vue/CSS
        // cua center chay tren giao dien cua plugin kia va lam nang admin.
        if (!vnx_center_is_own_admin_page()) {
            return;
        }

        wp_register_script('vuejs-library-center', VNX_PLUGIN_URL_CENTER . 'assets/js/libs/vue.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('vuejs-library-center');

        wp_register_script('vietnix_report_posts-center', VNX_PLUGIN_URL_CENTER . 'tools/inc/js/vietnix_report_posts.js', ['jquery', 'vuejs-library-center'], vnx_asset_version_Center('tools/inc/js/vietnix_report_posts.js'), true);
        wp_enqueue_script('vietnix_report_posts-center');
    }
    public function loadCss()
    {
        $pagenow = get_current_screen();

        if ($pagenow->id == 'toplevel_page_vnx_report_post_center') {
            vietnix_plugin_enqueue_admin_style_Center();
        }
    }

    public function add_menu_page()
    {
        add_menu_page(
            vnx_center_menu_title('Report post'),                         // Page title
            vnx_center_menu_title('Report post'),                         // Menu title
            'edit_pages',                          // Capability (Editor trở lên)
            'vnx_report_post_center',              // Menu slug (khac vietnix-plugin de khong render chong)
            [$this, 'render_view'],                // Callback function
            'dashicons-clock',                     // Icon
            100                                    // Position
        );
    }

    public function render_view()
    {

        View::render('tools/partials/standalone_tool_page', [
            'key' => 'vietnix-report-posts',
            'view' => 'tools/vietnix_report_posts',
        ]);
    }

    function getSetting()
    {
        try {
            $dataOption = get_option($this->nameOption);
            if (!$dataOption) {
                $dataOption = [
                    "linkhooks" => "",
                    "daily" => [
                        "title" => "",
                        "time" => ""
                    ],
                    "weekly" => [
                        "title" => "",
                        "time" => "",
                        "day" => ""
                    ]
                ];
                add_option($this->nameOption, json_encode($dataOption), "", "yes");
            }

            return $dataOption;
        } catch (Exception $ex) {
            error_log("Lỗi: " . $ex->getMessage());
        }
    }

    function updateSettings($data)
    {
        try {
            $convertedJson = json_encode($data);
            update_option($this->nameOption, $convertedJson, "");
            $this->notiSetupSuccess();

            return wp_send_json_success('Cài đặt đã được lưu thành công');
        } catch (Exception $ex) {
            error_log("Lỗi: " . $ex->getMessage());
        }
    }

    function notiSetupSuccess()
    {
        try {
            $dataOption = $this->getSetting();
            $dataOption = json_decode($dataOption, true);

            $title = "🔨Cập nhật thông báo report!\n\n";
            $message = '';
            $message .= "Hàng ngày: " . $dataOption['daily']['title'] . " lúc " . $dataOption['daily']['time'] . "\n";
            $message .= "Hàng tuần: " . $dataOption['weekly']['title'] . " lúc " . $dataOption['weekly']['time'] . " " . $dataOption['weekly']['day'] . "\n";


            DiscordBot::sendMessageByWebhook(
                $dataOption['linkhooks'],
                $title,
                $message,
                '#00b0f4'
            );
            return true;
        } catch (Exception $ex) {
            error_log("Lỗi: " . $ex->getMessage());
            return false;
        }
    }



    public function handleSaveSettings()
    {
        // Cung capability voi menu (edit_pages): truoc day menu mo cho Editor nhung luu lai doi
        // manage_options -> Editor vao duoc trang ma bam Luu bi 403.
        if (!current_user_can('edit_pages') || !check_ajax_referer('vietnix_report_posts_nonce', 'nonce', false)) {
            wp_send_json_error('Bạn không có quyền thực hiện thao tác này', 403);
        }

        try {

            if (!isset($_POST['settings']) || !is_array($_POST['settings'])) {
                wp_send_json_error('Dữ liệu settings không hợp lệ');
            }



            $settings = array(
                'linkhooks' => sanitize_text_field($_POST['settings']['linkhooks']),
                'daily' => array(
                    'title' => sanitize_text_field($_POST['settings']['daily']['title']),
                    'time' => sanitize_text_field($_POST['settings']['daily']['time'])
                ),
                'weekly' => array(
                    'title' => sanitize_text_field($_POST['settings']['weekly']['title']),
                    'time' => sanitize_text_field($_POST['settings']['weekly']['time']),
                    'day' => sanitize_text_field($_POST['settings']['weekly']['day'])
                )
            );

            // Khởi tạo class và lưu settings

            $this->updateSettings($settings);

            wp_send_json_success('Cài đặt đã được lưu thành công');
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
}

new VietnixReportPosts();
