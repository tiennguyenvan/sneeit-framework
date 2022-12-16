<?php
/*
https://github.com/envato/envato-wordpress-toolkit/issues/82#issuecomment-118649827

 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action('sneeit_envato_theme_auto_update', 'sneeit_envato_theme_auto_update_action');
function sneeit_envato_theme_auto_update_action() {
	if (!is_admin() || !current_user_can('manage_options')) {
		return;
	}
	include_once 'envato-class-protected-api.php';// envato tool kit library
	include_once 'envato-theme-auto-update-ajax.php'; // ajax service
	
	include_once 'envato-theme-auto-update-check.php';
	include_once 'envato-theme-auto-update-enqueue.php';
	include_once 'envato-theme-auto-update-install.php';
}

