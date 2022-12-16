<?php
function sneeit_envato_theme_auto_update_redirect_link( $theme ) {
	echo wp_nonce_url( self_admin_url( 'update.php?action=upgrade-theme&theme=' ) . $theme, 'upgrade-theme_' . $theme );
}
function sneeit_envato_theme_auto_update_core_queue( $theme, $item_id, $version, $url ) {
	if ( ! $theme || !$item_id || ! $version || ! $url ) {		
		_e( '*** Error: Invalid Theme Information Provided!', 'sneeit' );
		return;
	}
	
	// clean up $url
	$url = str_replace( '\\\\', '\\', $url);
	
	$current_theme = wp_get_theme();
	if (is_object($current_theme->parent())) {
		$current_theme = $current_theme->parent();
	}
	
	$current_update = get_site_transient( 'update_themes' );
	
	if ( ! is_object( $current_update ) ) {
		$current_update = new stdClass();
	}
	if ( ! isset( $current_update->response ) ) {
		$current_update->response = array();
	}
		
	$current_update->last_checked = time();
	$current_update->response[ $theme ] = array(
		'theme' => $theme,
		'new_version' => $version,
		'url' => $current_theme->get( 'ThemeURI' ),
		'package' => $url
	);
	set_site_transient( 'update_themes', $current_update );
	
	sneeit_envato_theme_auto_update_redirect_link( $theme );
}

function sneeit_envato_theme_auto_update_download_file( $url, $timeout = 3000 ) {
	echo $url;
	return;
	
	if ( ! sneeit_init_file_system() ) {
		_e( '*** Error: Can not init file system!', 'sneeit' );
		return;
	}
	/**
	 * @param WP_Filesystem_Base $wp_filesytem Description
	 */	
	global $wp_filesystem;
	if ( ! $wp_filesystem ) {		
		_e( '*** Error: Can not access file system!', 'sneeit' );
		return;
	}
	
	$upload_dir = wp_upload_dir();
	$dest_folder = $upload_dir['basedir'] . '/' . SNEEIT_ENVATO_THEME_AUTO_UPDATE;
	
	// create temp folder
	if ( ! $wp_filesystem->is_dir($dest_folder) && 
		 ! $wp_filesystem->mkdir($dest_folder, 0777 ) ) {		
		_e( '*** Error: Can create temporary folder', 'sneeit' );
		return;
	}
	

	//WARNING: The file is not automatically deleted, The script must unlink() the file.
	if ( ! $url ) {		
		_e( '*** Error: Invalid URL Provided!', 'sneeit' );
		return;
	}
	
	$tmpfname = $dest_folder . '/' . SNEEIT_ENVATO_THEME_AUTO_UPDATE . '.zip';
	if ( ! $tmpfname ) {
		_e( '*** Error: Could not create Temporary file!', 'sneeit' );
		return;
	}
	
	/*
	$args = array(
		'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0',
		'headers'    => array( 'Accept-Encoding' => '' ), 
		'timeout'    => $timeout,
		'sslverify'  => true,
		'filename'   => $tmpfname,
	);
	
	$response = wp_safe_remote_get( $url, $args );
	*/
	$response = file_put_contents($tmpfname, fopen($url, 'r'));
	
	if ( 0 == $response ) {
		unlink( $tmpfname );				
		_e( '*** Error: Can not download file!', 'sneeit' );	
		return;
	}

	$content_md5 = wp_remote_retrieve_header( $response, 'content-md5' );
	if ( $content_md5 ) {
		$md5_check = verify_file_md5( $tmpfname, $content_md5 );
		if ( is_wp_error( $md5_check ) ) {
			unlink( $tmpfname );
			echo sprintf( __( '*** Error: Wrong MD5 check [%s]!', 'sneeit' ), $md5_check );
			return;
		}
	}
	
	echo $tmpfname;
}
function sneeit_envato_theme_auto_update_get_download_link( $theme, $item_id ) {
	if ( ! $theme || !$item_id ) {		
		_e( '*** Error: Invalid Theme Information Provided!', 'sneeit' );
		return;
	}
	
	$user_name = get_option( SNEEIT_ENVATO_OPT_USER_NAME . '-' . $theme, '' );
	$api_key = get_option( SNEEIT_ENVATO_OPT_API_KEY . '-' . $theme, '' );

	if ( $user_name && $api_key ) {
		$envato_api = new Envato_Protected_API( $user_name, $api_key );
		if ($envato_api) {
			// get upload url
			$theme_download_url = $envato_api->private_user_data( 'wp-download', '', $item_id, true );
			if ( !is_object( $theme_download_url ) || ! isset( $theme_download_url->url ) ) {								
				echo sprintf( __( '*** Error: Can not get download link for "%1$s" theme', 'sneeit' ), $theme );
				return;
			} else {
				echo $theme_download_url->url;
				return;
			}
		} else {
			_e( '*** Error: Can not connect to Envato API service!', 'sneeit' );
			return;
		}
	}
	
	_e( '*** Error: Wrong user name or API key!', 'sneeit' );
}

function sneeit_envato_theme_auto_update_callback() {	
	if ( ! current_user_can( 'manage_options' ) ) {
		die();
	}
	$sub_action = sneeit_get_server_request( 'sub_action' );
	
	switch ( $sub_action ) {
		// build actions
		case 'get_download_link':
			$theme = sneeit_get_server_request( 'theme' );
			$item_id = sneeit_get_server_request( 'item_id' );
			sneeit_envato_theme_auto_update_get_download_link( $theme, $item_id );
			break;
		
		case 'backup_languages':
			break;
		
		case 'download_file':
			$url = sneeit_get_server_request( 'url' );
			sneeit_envato_theme_auto_update_download_file( $url );
			break;
		
		case 'core_queue':
			$theme = sneeit_get_server_request( 'theme' );
			$item_id = sneeit_get_server_request( 'item_id' );	
			$version = sneeit_get_server_request( 'version' );
			$url = sneeit_get_server_request( 'url' );
			sneeit_envato_theme_auto_update_core_queue( $theme, $item_id, $version, $url );
			
			break;
	}
	die();
}

if ( is_admin() ) :
	add_action( 'wp_ajax_nopriv_sneeit_envato_theme_auto_update', 'sneeit_envato_theme_auto_update_callback' );
	add_action( 'wp_ajax_sneeit_envato_theme_auto_update', 'sneeit_envato_theme_auto_update_callback' );
endif;// is_admin for ajax