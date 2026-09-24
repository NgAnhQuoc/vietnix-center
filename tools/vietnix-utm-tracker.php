<?php

if ( is_plugin_active( 'vietnix-utm-tracker/vietnix-utm-tracker.php' ) ) {
  deactivate_plugins( array( 'vietnix-utm-tracker/vietnix-utm-tracker.php' ) );
}

if ( !is_plugin_active('vietnix-utm-tracker/vietnix-utm-tracker.php') ) {
  define_if_not_defined_Center('Vietnix_Utm_Tracker', 'Vietnix_Utm_Tracker');
  define_if_not_defined_Center('Vietnix_Utm_Tracker_Center__FILE__', __FILE__);
  define_if_not_defined_Center('Vietnix_Utm_Tracker_Center__URL', plugins_url('/', __FILE__));
  define_if_not_defined_Center('Vietnix_Utm_Tracker_Center__PATH', plugin_dir_path(__FILE__));
  define_if_not_defined_Center('Vietnix_Utm_Tracker_Center__VERSION', '1.0.2');
  // Prefix option dung chung voi vietnix-plugin (vnx_utm_tracker_cookies_age, ...).
  define_if_not_defined_Center('Vietnix_Utm_Tracker_Center__PREFIX', 'vnx_utm_tracker_');


  require_once( Vietnix_Utm_Tracker_Center__PATH . 'inc/vietnix-utm-tracker/admin/class-vietnix-utm-tracker-admin.php' );

}