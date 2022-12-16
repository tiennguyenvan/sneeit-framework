<?php
global $Sneeit_Envato_Theme_Activation;
add_filter('sneeit_envato_theme_activation_check', 'sneeit_envato_theme_activation_check', 10, 0);
function sneeit_envato_theme_activation_check() {
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
	
	$user_name = get_option(SNEEIT_ENVATO_OPT_USER_NAME.'-'.$theme_slug, '');
	if ( ! $user_name ) {
		return false;
	}
	
	$api_key = get_option(SNEEIT_ENVATO_OPT_API_KEY.'-'.$theme_slug, '');

	if ( ! $api_key ) {
		return false;
	}
	
	require_once 'envato-class-protected-api.php';	
	$envato_api = new Envato_Protected_API( $user_name, $api_key );

	if ($envato_api) {		
		$list_themes = $envato_api->private_user_data( 'wp-list-themes', '', '',  true );
		
		// raise error
		if ( ! $list_themes || ! empty( $list_themes['api_error'] ) ) {
			return false;
		} 
		else {
			// check if this theme is in purchase list and the 			
			foreach ($list_themes as $theme) {				
				if (	is_object($theme) && 
						property_exists($theme, 'theme_name') && 
						(
							strtolower($theme->theme_name) == strtolower($current_theme->stylesheet) ||
							strtolower($theme->theme_name) == strtolower($current_theme->get( 'Name' )) ||
							strtolower($theme->theme_name) == strtolower($current_theme->get('TextDomain'))
						) 
					) {
					return true;
				}
			}
		}
	} /*check envato API*/

	return false;
}

function sneeit_envato_theme_activation_admin_menu() {
	global $Sneeit_Envato_Theme_Activation;
	
	if (!isset($Sneeit_Envato_Theme_Activation['menu-title'])) {
		$Sneeit_Envato_Theme_Activation['menu-title'] = esc_html__('Theme Activation', 'sneeit');
	}
	
	if (!isset($Sneeit_Envato_Theme_Activation['page-title'])) {
		$Sneeit_Envato_Theme_Activation['page-title'] = esc_html__('Theme Activation', 'sneeit');
	}
	
	
	add_theme_page( 
		$Sneeit_Envato_Theme_Activation['page-title'],
		$Sneeit_Envato_Theme_Activation['menu-title'], 
		'manage_options',
		'sneeit-theme-activation', 
		'sneeit_envato_theme_activation_html'
	);
}
function sneeit_envato_theme_activation_html() {
	global $Sneeit_Envato_Theme_Activation;
	if (!isset($Sneeit_Envato_Theme_Activation['page-title'])) {
		$Sneeit_Envato_Theme_Activation['page-title'] = esc_html__('Theme Options', 'sneeit');
	}
	
	echo '<div class="wrap">'.
		'<h1>'.$Sneeit_Envato_Theme_Activation['page-title'].'</h1>';
		if (isset($Sneeit_Envato_Theme_Activation['html-before'])) {
			echo $Sneeit_Envato_Theme_Activation['html-before'];
		}
		
		include_once 'envato-theme-activation-html.php';
		
		if (isset($Sneeit_Envato_Theme_Activation['html-after'])) {
			echo $Sneeit_Envato_Theme_Activation['html-after'];
		}		
	echo '</div>';
}

add_action('sneeit_envato_theme_activation', 'sneeit_envato_theme_activation');
function sneeit_envato_theme_activation($args) {
	if (!is_admin() || !current_user_can('manage_options')) {
		return;
	}	
	
	global $Sneeit_Envato_Theme_Activation;
	$Sneeit_Envato_Theme_Activation = $args;
	
	add_action( 'admin_menu', 'sneeit_envato_theme_activation_admin_menu');
}