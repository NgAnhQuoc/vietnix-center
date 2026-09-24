<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class Vietnix_Banner_Activator_Center
{
	/**
	 * Activate function
	 *
	 * @return void
	 */
	public static function activate()
	{
		if (false == get_option('vietnix_banner_view_count')) {
			add_option('vietnix_banner_view_count', array());
		}
		if (false == get_option('vietnix_banner_click_count')) {
			add_option('vietnix_banner_click_count', array());
		}
	}
}
