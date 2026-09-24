<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! defined( 'VNX_LOGIN_SLUG' ) ) {
    define( 'VNX_LOGIN_SLUG', 'vnxlogin7k2p9' );
}

class VNX_Hide_Login_Center {

    private static $instance = null;
    private $wp_login_php = false;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 9999 );
        add_action( 'wp_loaded',      array( $this, 'wp_loaded' ) );
        add_action( 'setup_theme',    array( $this, 'setup_theme' ), 1 );

        add_filter( 'site_url',         array( $this, 'site_url' ), 10, 4 );
        add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
        add_filter( 'wp_redirect',      array( $this, 'wp_redirect' ), 10, 2 ); 
        add_filter( 'login_url',        array( $this, 'login_url' ), 10, 3 );
        add_filter( 'logout_redirect',  array( $this, 'logout_redirect' ), 10, 3 );

        // Ngăn WordPress redirect /wp-admin tự động
        remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
    }


    /**
     * Kiểm tra permalink có trailing slash không
     */
    private function use_trailing_slashes() {
        return ( '/' === substr( get_option( 'permalink_structure' ), -1, 1 ) );
    }

    /**
     * Thêm/xóa trailing slash tùy theo cấu hình permalink
     */
    private function user_trailingslashit( $string ) {
        return $this->use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );
    }

    /**
     * Trả về slug login tùy chỉnh (hardcoded)
     */
    private function new_login_slug() {
        return VNX_LOGIN_SLUG;
    }

    /**
     * Trả về URL login tùy chỉnh đầy đủ
     */
    public function new_login_url( $scheme = null ) {
        $url = home_url( '/', $scheme );

        if ( get_option( 'permalink_structure' ) ) {
            return $this->user_trailingslashit( $url . $this->new_login_slug() );
        } else {
            return $url . '?' . $this->new_login_slug();
        }
    }


    /**
     * Hook: plugins_loaded 
     * Phát hiện request và gán lại $pagenow
     */
    public function plugins_loaded() {
        global $pagenow;

        $request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

        // Nếu request là wp-login.php hoặc wp-register.php => redirect về trang chủ
        if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
               || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) )
             && ! is_admin() ) {

            $this->wp_login_php = true;
            $pagenow = 'index.php';

        } elseif ( ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' ) )
                   || ( ! get_option( 'permalink_structure' )
                        && isset( $_GET[ $this->new_login_slug() ] )
                        && empty( $_GET[ $this->new_login_slug() ] ) ) ) {

            // Nếu request là custom login slug => cho phép hiển thị wp-login.php
            $_SERVER['SCRIPT_NAME'] = $this->new_login_slug();
            $pagenow = 'wp-login.php';

        } elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
                     || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) )
                   && ! is_admin() ) {

            $this->wp_login_php = true;
            $pagenow = 'index.php';
        }
    }

    /**
     * Hook: setup_theme
     * Chặn truy cập customizer khi chưa đăng nhập
     */
    public function setup_theme() {
        global $pagenow;

        if ( ! is_user_logged_in() && 'customize.php' === $pagenow ) {
            wp_die( __( 'You do not have permission to access this page.' ), 403, array( 'response' => 403 ) );
        }
    }

    /**
     * Hook: wp_loaded
     * Xử lý redirect & load wp-login.php cho custom slug
     */
    public function wp_loaded() {
        global $pagenow;

        $request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

        // Bỏ qua nếu là post password form
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'postpass' && isset( $_POST['post_password'] ) ) {
            return;
        }

        // Chặn wp-admin khi chưa đăng nhập
        if ( is_admin() && ! is_user_logged_in() && ! defined( 'WP_CLI' ) && ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_CRON' ) && $pagenow !== 'admin-post.php' && ( ! isset( $request['path'] ) || $request['path'] !== admin_url( 'options.php', 'relative' ) ) ) {
            wp_safe_redirect( home_url( '/' ) );
            exit;
        }

        // Chặn profile.php khi chưa đăng nhập
        if ( ! is_user_logged_in() && isset( $_GET['wc-ajax'] ) && $pagenow === 'profile.php' ) {
            wp_safe_redirect( home_url( '/' ) );
            exit;
        }

        // Chặn options.php khi chưa đăng nhập
        if ( ! is_user_logged_in() && isset( $request['path'] ) && $request['path'] === admin_url( 'options.php', 'relative' ) ) {
            wp_safe_redirect( home_url( '/' ) );
            exit;
        }

        // Xử lý trailing slash cho custom login URL
        if ( $pagenow === 'wp-login.php' && isset( $request['path'] ) && $request['path'] !== $this->user_trailingslashit( $request['path'] ) && get_option( 'permalink_structure' ) ) {
            wp_safe_redirect( $this->user_trailingslashit( $this->new_login_url() )
                              . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );
            exit;

        } elseif ( $this->wp_login_php ) {

            // wp-login.php bị chặn => redirect về trang chủ
            wp_safe_redirect( home_url( '/' ) );
            exit;

        } elseif ( $pagenow === 'wp-login.php' ) {

            // Custom slug hợp lệ => load wp-login.php
            $redirect_to = admin_url();

            if ( is_user_logged_in() && ! isset( $_REQUEST['action'] ) ) {
                wp_safe_redirect( $redirect_to );
                exit;
            }

            $login_file = ABSPATH . 'wp-login.php';
            if ( file_exists( $login_file ) ) {
                require_once $login_file;
            } else {
                wp_die( __( 'Login file not found.' ), 'Error', array( 'response' => 500 ) );
            }

            exit;
        }
    }

    /**
     * Filter: site_url
     * Thay thế wp-login.php bằng custom slug trong URL
     */
    public function site_url( $url, $path, $scheme, $blog_id ) {
        return $this->filter_wp_login_php( $url, $scheme );
    }

    /**
     * Filter: network_site_url
     */
    public function network_site_url( $url, $path, $scheme ) {
        return $this->filter_wp_login_php( $url, $scheme );
    }

    /**
     * Filter: wp_redirect
     */
    public function wp_redirect( $location, $status ) {
        if ( strpos( $location, 'https://wordpress.com/wp-login.php' ) !== false ) {
            return $location;
        }
        return $this->filter_wp_login_php( $location );
    }

    /**
     * Lọc và thay thế wp-login.php bằng custom login URL
     */
    private function filter_wp_login_php( $url, $scheme = null ) {
        // Không thay thế postpass URL
        if ( strpos( $url, 'wp-login.php?action=postpass' ) !== false ) {
            return $url;
        }

        // Thay thế wp-login.php bằng custom login URL
        if ( strpos( $url, 'wp-login.php' ) !== false && strpos( (string) wp_get_referer(), 'wp-login.php' ) === false ) {

            if ( is_ssl() ) {
                $scheme = 'https';
            }

            $args = explode( '?', $url );

            if ( isset( $args[1] ) ) {
                parse_str( $args[1], $args );

                if ( isset( $args['login'] ) ) {
                    $args['login'] = rawurlencode( $args['login'] );
                }

                $url = add_query_arg( $args, $this->new_login_url( $scheme ) );
            } else {
                $url = $this->new_login_url( $scheme );
            }
        }

        return $url;
    }

    /**
     * Filter: login_url
     * Ẩn login URL khi ở trang 404
     */
    public function login_url( $login_url, $redirect, $force_reauth ) {
        if ( is_404() ) {
            return '#';
        }

        if ( $force_reauth === false ) {
            return $login_url;
        }

        if ( empty( $redirect ) ) {
            return $login_url;
        }

        $redirect = explode( '?', $redirect );

        if ( $redirect[0] === admin_url( 'options.php' ) ) {
            $login_url = admin_url();
        }

        return $login_url;
    }

    /**
     * Filter: logout_redirect
     * Chuyển hướng về trang chủ sau khi logout
     */
    public function logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
        return home_url( '/' );
    }
}


add_action( 'plugins_loaded', function() {
    VNX_Hide_Login_Center::get_instance();
} );
