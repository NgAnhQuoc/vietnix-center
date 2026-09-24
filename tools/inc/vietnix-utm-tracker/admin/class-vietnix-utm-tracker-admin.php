<?php
class VIETNIX_UTM_Tracker_Admin_Center
{
	public $settings_slug = 'vietnix-utm-tracker';

	/**
	 * __construct function
	 */
	public function __construct()
	{
		$this->init();
	}

	/**
	 * init function
	 *
	 * @return void
	 */
	public function init()
	{
		add_action('admin_menu', [$this, 'vietnix_utm_tracking_admin_menu']);
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_styles']);
		// Chay song song thi vietnix-plugin da nap script UTM ngoai site, nap them ban cua
		// center se ghi cookie/gan su kien 2 lan.
		if (!vnx_center_companion_mode()) {
			add_action('wp_enqueue_scripts', [$this, 'front_enqueue_scripts']);
		}
	}

	public function vietnix_utm_tracking_admin_menu()
	{
		add_menu_page(
			vnx_center_menu_title('Vietnix Utm Tracking'),
			vnx_center_menu_title('Vietnix Utm Tracking'),
			'manage_options',
			'vietnix_utm_tracking_center',
			[$this, 'admin_page_config'],
			'dashicons-visibility'
		);
	}

	public function admin_enqueue_styles()
	{
		$pagenow = get_current_screen();

		if ($pagenow->id == 'toplevel_page_vietnix_utm_tracking_center') {
			vietnix_plugin_enqueue_admin_style_Center();
		}
	}

	public function front_enqueue_scripts()
	{
		$uniqueVersion = Vietnix_Utm_Tracker_Center__VERSION . '-' . uniqid();
		wp_register_script( 'vietnix-utm-tracker-center', VNX_PLUGIN_URL_CENTER . 'build/js/frontend/vnx-utm-tracker.js', array( 'jquery' ), $uniqueVersion, true );
		wp_localize_script( 'vietnix-utm-tracker-center', 'utm_array', array(
			'cookie_domain'  => get_option( 'vnx_plugin_setting_options' )[ 'cookie_domain' ],
			'cookies_age'    => get_option( Vietnix_Utm_Tracker_Center__PREFIX . 'cookies_age', array() ),
			'seo_channel'    => get_option( Vietnix_Utm_Tracker_Center__PREFIX . 'seo_channel', array() ),
			'social_channel' => get_option( Vietnix_Utm_Tracker_Center__PREFIX . 'social_channel', array() ),
			)
		);
		wp_enqueue_script( 'vietnix-utm-tracker-center' );

		wp_register_script( 'vietnix-utm-front-center', VNX_PLUGIN_URL_CENTER . 'tools/inc/js/vnx-utm-front.js', array( 'jquery','vietnix-utm-tracker-center' ), $uniqueVersion, true );
		wp_localize_script( 'vietnix-utm-front-center', 'utm_array', array(
			'cookie_domain'  => get_option( 'vnx_plugin_setting_options' )[ 'cookie_domain' ],
			'cookies_age'    => get_option( Vietnix_Utm_Tracker_Center__PREFIX . 'cookies_age', array() ),
			'seo_channel'    => get_option( Vietnix_Utm_Tracker_Center__PREFIX . 'seo_channel', array() ),
			'social_channel' => get_option( Vietnix_Utm_Tracker_Center__PREFIX . 'social_channel', array() ),
		)
		);
		wp_enqueue_script( 'vietnix-utm-front-center' );
	}

	public function admin_page_config()
	{
		$prefix = Vietnix_Utm_Tracker_Center__PREFIX;

		if (isset($_POST['action']) && $_POST['action'] ==	'update_cookies_age' && isset($_POST['cookies_age']) && $_POST['cookies_age'] != '') {
			update_option($prefix . 'cookies_age', $_POST['cookies_age']);
		}

		if (isset($_POST['action']) && $_POST['action'] ==	'update_channel' && isset($_POST['seo_channel'])) {
			$seo_channel = $_POST['seo_channel'];
			$seo_channel = $this->br_bookmarks_tagify_json_to_array_Center($seo_channel);
			update_option($prefix . 'seo_channel', implode(',', $seo_channel));
		}

		if (isset($_POST['action']) && $_POST['action'] ==	'update_channel' && isset($_POST['social_channel'])) {
			$social_channel = $_POST['social_channel'];
			$social_channel = $this->br_bookmarks_tagify_json_to_array_Center($social_channel);
			update_option($prefix . 'social_channel', implode(',', $social_channel));
		}

		\HelperCenter\View::render('tools/partials/standalone_tool_page', [
			'key' => 'vietnix-utm-tracker',
			'view' => 'tools/vietnix_utm_tracker',
		]);
	}

	public function br_bookmarks_tagify_json_to_array_Center($value)
	{
		if (empty($value)) {
			return $output = array();
		} else {
			$value = str_replace(array('[', ']'), '', $value);

			$value = str_replace('\"', "\"", $value);

			$value = explode(',', $value);

			$value_array = array();

			if (is_array($value) && 0 !== count($value)) {
				foreach ($value as $value_inner) {
					$value_array[] = json_decode($value_inner);
				}

				$value_array = json_decode(json_encode($value_array), true);

				$output = array();

				foreach ($value_array as $value_array_inner) {
					foreach ($value_array_inner as $key => $val) {
						$output[] = $val;
					}
				}
			}

			return $output;
		}
	}
}

// ---------------------------------------------------------
$vietnix_utm_tracker_admin = new VIETNIX_UTM_Tracker_Admin_Center();
