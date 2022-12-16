<?php

add_action( 'admin_head', 'sneeit_sneeit_theme_auto_update_check');
function sneeit_sneeit_theme_auto_update_check() {
	if (!is_admin() || !current_user_can('manage_options') ||
		(!empty($_GET['page']) && SNEEIT_THEME_ACTIVATION_PAGE_SLUG == $_GET['page'])) {
		return;
	}
	
	$current_theme = wp_get_theme();
	if (is_object($current_theme->parent())) {
		$current_theme = $current_theme->parent();
	}
	$current_screen = get_current_screen();
			
	// our ugrader is working, we don't need to check anything
	if ( !is_object($current_screen) || 
		 'update' == $current_screen->id ||
		 'update-core' == $current_screen->id ) {
		return;
	}
	
	if ( !isset( $current_theme->stylesheet ) ) {
		$current_theme->stylesheet = 'global';
	}
	$theme_slug = $current_theme->stylesheet;
	
	$user_name = get_option( SNEEIT_SNEEIT_OPT_USER_NAME . '-' . $current_theme->stylesheet, '' );
	$license_key = get_option( SNEEIT_SNEEIT_OPT_LICENSE_KEY . '-' . $current_theme->stylesheet, '' );

	if ( $user_name && $license_key ) {
		$theme_update = sneeit_sneeit_theme_api($user_name, $license_key, $theme_slug);		
		if (is_string($theme_update)) {			
			return;
		}
	
		// check if this theme needs to be updated
		if (version_compare($theme_update['version'], $current_theme->get( 'Version' ), '>')) {
			$current_update = get_site_transient( 'update_themes' );
			if ( ! is_object( $current_update ) ) {
				$current_update = new stdClass();
			}
			if ( ! isset( $current_update->response ) ) {
				$current_update->response = array();
			}

			$current_update->last_checked = time();
			$current_update->response[ $theme_slug ] = array(
				'theme' => $theme_slug,
				'new_version' => $theme_update['version'],
				'url' => $current_theme->get( 'ThemeURI' ),
				'package' => $theme_update['download_url']
			);
			set_site_transient( 'update_themes', $current_update );

			$update_url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-theme&theme=' ) . $theme_slug, 'upgrade-theme_' . $theme_slug );			
			$message = sprintf(
				__('"%1$s" theme was out of date. <a href="%2$s">Please click here to update</a>', 'sneeit'), 
				$current_theme->get( 'Name' ), 
				esc_url($update_url)
			);
			add_settings_error(SNEEIT_SNEEIT_THEME_AUTO_UPDATE, 'update_needed', $message, 'notice-warning');
			settings_errors(SNEEIT_SNEEIT_THEME_AUTO_UPDATE);
		}
	}/*check user name and api key*/
}
