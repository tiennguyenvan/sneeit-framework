<?php
function sneeit_customizer_import_callback() {	
	
	if ( !current_user_can( 'edit_theme_options' ) ) {
		_e('You have no permission for this action', 'sneeit');
		die();
	}
		
	$file_name = wp_unslash(sneeit_get_server_request('file_name'));
	
	$nonce = sneeit_get_server_request('nonce');
	if ( ! wp_verify_nonce( $nonce, 'sneeit-customizer-importing' ) ) {
		_e('This file has wrong nonce', 'sneeit');
		die();
	}
	
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	$template	 = get_template();
	$overrides   = array( 'test_form' => FALSE, 'mimes' => array('dat' => 'text/dat') );
	$file        = wp_handle_upload( $file_name, $overrides );
	// Make sure we have an uploaded file.
	if ( isset( $file['error'] ) ) {
		echo $file['error'] . $file_name;
		die();
	}
	if ( ! file_exists( $file['file'] ) ) {
		_e( 'Can not write file to server. Please check your server permission', 'sneeit' );
		die();
	}
	// Get the upload data.
	$data = json_decode( trim( wp_unslash(  file_get_contents( $file['file'] ) ) ), true );
	
	// Remove the uploaded file.
	unlink( $file['file'] );
	
	// Data checks.
	if ( !is_object($data) || !isset( $data['template'] ) || !isset( $data['mods'] )) {
		_e( 'The file has wrong data format', 'sneeit' );
		die();
	}	
	if ( $data['template'] != $template ) {
		$cei_error = __( 'The data in the file is not for this current theme', 'sneeit' );
		return;
	}
	
	// Loop through the mods.
	foreach ( $data['mods'] as $key => $val ) {
		// Save the mod.
		set_theme_mod( $key, $val );
	}

	die();
}
if (is_admin()) :
	add_action( 'wp_ajax_nopriv_sneeit_customizer_import', 'sneeit_customizer_import_callback' );
	add_action( 'wp_ajax_sneeit_customizer_import', 'sneeit_customizer_import_callback' );
endif;// is_admin for ajax
