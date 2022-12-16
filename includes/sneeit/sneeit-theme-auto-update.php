<?php
/*
https://github.com/sneeit/sneeit-wordpress-toolkit/issues/82#issuecomment-118649827

 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action('sneeit_sneeit_theme_auto_update', 'sneeit_sneeit_theme_auto_update_action');
function sneeit_sneeit_theme_auto_update_action() {
	if (!current_user_can('manage_options')) {
		return;
	}
	include_once 'sneeit-theme-api.php';// sneeit tool kit library
	include_once 'sneeit-theme-auto-update-check.php';
}

