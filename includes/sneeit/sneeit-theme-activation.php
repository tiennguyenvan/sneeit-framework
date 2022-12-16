<?php
global $Sneeit_Sneeit_Theme_Activation;
add_filter('sneeit_sneeit_theme_activation_check', 'sneeit_sneeit_theme_activation_check', 10, 0);
function sneeit_sneeit_theme_activation_check() {
	
	if (!is_admin() || !current_user_can('manage_options')) {
		return true;
	}
	$current_theme = wp_get_theme();
	if (is_object($current_theme->parent())) {
		$current_theme = $current_theme->parent();
	}
	
	if (!isset($current_theme->stylesheet)) {
		$current_theme->stylesheet = 'global';
	}
	$theme_slug = $current_theme->stylesheet;
	
	$user_name = get_option(SNEEIT_SNEEIT_OPT_USER_NAME.'-'.$theme_slug, '');	
	
	if ( ! $user_name ) {
		return false;
	}
		
	$license_key = get_option(SNEEIT_SNEEIT_OPT_LICENSE_KEY.'-'.$theme_slug, '');
	if ( ! $license_key ) {
		return false;
	}	
	
	require_once 'sneeit-theme-api.php';	
	$theme_update = sneeit_sneeit_theme_api($user_name, $license_key, $theme_slug);
	
	if (is_string($theme_update)) {
		return false;
	}
	
	return true;
}

function sneeit_sneeit_theme_activation_admin_menu() {
	global $Sneeit_Sneeit_Theme_Activation;
	
	if (!isset($Sneeit_Sneeit_Theme_Activation['menu-title'])) {
		$Sneeit_Sneeit_Theme_Activation['menu-title'] = esc_html__('Theme Activation', 'sneeit');
	}
	
	if (!isset($Sneeit_Sneeit_Theme_Activation['page-title'])) {
		$Sneeit_Sneeit_Theme_Activation['page-title'] = esc_html__('Theme Activation', 'sneeit');
	}
	
	add_theme_page( 
		$Sneeit_Sneeit_Theme_Activation['page-title'],
		$Sneeit_Sneeit_Theme_Activation['menu-title'], 
		'manage_options',
		SNEEIT_THEME_ACTIVATION_PAGE_SLUG, 
		'sneeit_sneeit_theme_activation_html'
	);
}
function sneeit_sneeit_theme_activation_html() {
	global $Sneeit_Sneeit_Theme_Activation;
	if (!isset($Sneeit_Sneeit_Theme_Activation['page-title'])) {
		$Sneeit_Sneeit_Theme_Activation['page-title'] = esc_html__('Theme Options', 'sneeit');
	}
	
	echo '<div class="wrap">'.
		'<h1>'.$Sneeit_Sneeit_Theme_Activation['page-title'].'</h1>';
		if (isset($Sneeit_Sneeit_Theme_Activation['html-before'])) {
			echo $Sneeit_Sneeit_Theme_Activation['html-before'];
		}
		
		include_once 'sneeit-theme-activation-html.php';
		
		if (isset($Sneeit_Sneeit_Theme_Activation['html-after'])) {
			echo $Sneeit_Sneeit_Theme_Activation['html-after'];
		}		
	echo '</div>';
}

add_action('sneeit_sneeit_theme_activation', 'sneeit_sneeit_theme_activation');
function sneeit_sneeit_theme_activation($args) {
	if (!is_admin() || !current_user_can('manage_options')) {
		return;
	}	
	
	global $Sneeit_Sneeit_Theme_Activation;
	$Sneeit_Sneeit_Theme_Activation = $args;
	
	add_action( 'admin_menu', 'sneeit_sneeit_theme_activation_admin_menu');
}