<?php

add_action( 'admin_enqueue_scripts', 'sneeit_envato_theme_auto_update_enqueue_scripts');
function sneeit_envato_theme_auto_update_enqueue_scripts ($hook) {
	if (!is_admin() || !current_user_can('manage_options')) {
		return;
	}
	
	if ( 'admin_page_' . SNEEIT_ENVATO_THEME_AUTO_UPDATE == $hook ) {
		if ( ! isset( $_GET['theme'] ) || 
			 ! $_GET['theme'] || 
			 ! isset( $_GET['item_id'] ) || 
			 ! $_GET['item_id'] || 
			 ! isset( $_GET['version'] ) || 
			 ! $_GET['version'] ) {
			return;
		}

		$theme = $_GET['theme'];
		$item_id = $_GET['item_id'];
		$version = $_GET['version'];

		wp_enqueue_script(
			SNEEIT_ENVATO_THEME_AUTO_UPDATE, 
			SNEEIT_PLUGIN_URL_JS . 'envato-theme-auto-update.js', 
			array( 'jquery' ), 
			SNEEIT_PLUGIN_VERSION, 
			true
		);
		
		
		wp_localize_script(SNEEIT_ENVATO_THEME_AUTO_UPDATE, 'sneeit_envato_theme_auto_update', array(
			'theme'    => $theme,
			'item_id'  => $item_id,
			'version'  => $version,
			'app_name' => SNEEIT_ENVATO_THEME_AUTO_UPDATE,
			'text'     => array(
				'get_download_link' 
					=> __( 'Getting download link ...', 'sneeit' ),
				'download_file' 
					=> __( 'Downloading theme files to local hosting (may take 15 mins) ...', 'sneeit' ),
				'core_queue'
					=> __( 'Adding theme files to WordPress update core ...', 'sneeit' ),
				'redirect_link'
					=> __( 'Redirecting to Wordpress update core ...', 'sneeit' ),
			),
		));
	}
}
