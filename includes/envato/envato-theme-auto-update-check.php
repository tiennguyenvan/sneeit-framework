<?php

add_action( 'admin_head', 'sneeit_envato_theme_auto_update_check' );
function sneeit_envato_theme_auto_update_check() {
	
	
	if (!is_admin() || !current_user_can('manage_options')) {
		return;
	}
	$current_theme = wp_get_theme();
	if (is_object($current_theme->parent())) {
		$current_theme = $current_theme->parent();
	}
	$current_screen = get_current_screen();
	
	
	
	// our ugrader is working, we don't need to check anything
	if ( 'admin_page_' . SNEEIT_ENVATO_THEME_AUTO_UPDATE == $current_screen->id || 
		 'update' == $current_screen->id ||
		 'update-core' == $current_screen->id ) {
		return;
	}
	
	if ( !isset( $current_theme->stylesheet ) ) {
		$current_theme->stylesheet = 'global';
	}
	
	$user_name = get_option( SNEEIT_ENVATO_OPT_USER_NAME . '-' . $current_theme->stylesheet, '' );
	$api_key = get_option( SNEEIT_ENVATO_OPT_API_KEY . '-' . $current_theme->stylesheet, '' );

	if ( $user_name && $api_key ) {
		$envato_api = new Envato_Protected_API( $user_name, $api_key );
		
		
		
		if ($envato_api) {			
			$list_themes = $envato_api->private_user_data( 'wp-list-themes', '', '',  true );	
			

			if ( ! $list_themes || ! empty( $list_themes['api_error'] ) ) {
				if ( empty( $list_themes['api_error'] ) || !is_array($list_themes)) {
					$error_message = esc_html__('Operation timed out with 0 bytes received.', 'sneeit');
					if (is_string($list_themes)) {
						$error_message = $list_themes;
					}
					$list_themes = array(
						'api_error' => array($error_message),
					);
				}				
				
				$message = sprintf(
					__('Error when check for update "%1$s" theme:<br /> %2$s', 'sneeit'), 
					$current_theme->get( 'Name' ), 
					implode('<br \>', (array) $list_themes['api_error'])
				);				
				add_settings_error(SNEEIT_ENVATO_THEME_AUTO_UPDATE, 'update_errors', $message, 'error');
				settings_errors(SNEEIT_ENVATO_THEME_AUTO_UPDATE);		
			} else {
				$theme_new_version = '';				
				$theme_item_id = '';
				
				// check if this theme is in purchase list and the 
				foreach ($list_themes as $theme) {
					if (	is_object($theme) && 
							property_exists($theme, 'theme_name') && 
							property_exists($theme, 'version') && 
							property_exists($theme, 'item_id') && 
							(								
								strtolower($theme->theme_name) == strtolower($current_theme->stylesheet) ||
								strtolower($theme->theme_name) == strtolower($current_theme->get( 'Name' )) ||
								strtolower($theme->theme_name) == strtolower($current_theme->get('TextDomain'))
							)
							&& $theme->version != $current_theme->get( 'Version' ) ) {
						$theme_new_version = $theme->version;
						$theme_item_id = $theme->item_id;
						break;
					}
				}
				
				// show update link
				if ( $theme_new_version) {					
					$update_url = admin_url( 'admin.php' );															
					$update_url = add_query_arg( 'page', SNEEIT_ENVATO_THEME_AUTO_UPDATE, $update_url );
					$update_url = add_query_arg( 'theme', urlencode( $current_theme->stylesheet ), $update_url );
					$update_url = add_query_arg( 'item_id', $theme_item_id, $update_url );
					$update_url = add_query_arg( 'version', $theme_new_version, $update_url );					
					$update_url = wp_nonce_url( $update_url );			
					$message = sprintf(
						__('"%1$s" theme was out of date. <a href="%2$s">Please click here to update</a>', 'sneeit'), 
						$current_theme->get( 'Name' ), 
						esc_url($update_url)
					);
					add_settings_error(SNEEIT_ENVATO_THEME_AUTO_UPDATE, 'update_needed', $message, 'notice-warning');
					settings_errors(SNEEIT_ENVATO_THEME_AUTO_UPDATE);
				}								
			}
		}/*check $envato_api*/
	}/*check user name and api key*/
}
