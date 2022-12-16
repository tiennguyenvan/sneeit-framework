<?php
function sneeit_theme_options_save_callback() {	
	include_once sneeit_framework_plugin_path('/includes/lib/lib-ultilities.php');
	
	if (!sneeit_are_you_admin()) {		
		die();
	}
	
	$data = sneeit_get_server_request('data');
	
	if (is_array($data)) {
		foreach ($data as $option_id => $option_declaration) {
			if (SNEEIT_KEY_SNEEIT_EXPORT == $option_id ||
				SNEEIT_KEY_SNEEIT_IMPORT == $option_id) {
				continue;
			}
			
			if (is_array($option_declaration['value'])) {
				$option_declaration['value'] = implode(',', $option_declaration['value']);
			}
			$option_declaration['value'] = stripslashes($option_declaration['value']);
			echo $option_id.'='.$option_declaration['value'].'
';
			
			set_theme_mod($option_id, $option_declaration['value']);
		}
	}
	
	die();
}
if (is_admin()) :
	add_action( 'wp_ajax_nopriv_sneeit_theme_options_save', 'sneeit_theme_options_save_callback' );
	add_action( 'wp_ajax_sneeit_theme_options_save', 'sneeit_theme_options_save_callback' );
endif;// is_admin for ajax