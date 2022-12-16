<?php

add_action( 'admin_print_footer_scripts', 'sneeit_theme_options_admin_enqueue_scripts', 1);
function sneeit_theme_options_admin_enqueue_scripts() {

	wp_enqueue_style('sneeit-theme-options', SNEEIT_PLUGIN_URL_CSS . 'theme-options.css', array(), SNEEIT_PLUGIN_VERSION);
	wp_enqueue_script('sneeit-theme-options', SNEEIT_PLUGIN_URL_JS . 'theme-options.js', array(
		'jquery',
		'sneeit-controls'
	), SNEEIT_PLUGIN_VERSION);
	
	wp_localize_script('sneeit-theme-options', 'Sneeit_Theme_Options', array(
		'text' => array(
			'are_you_sure' => esc_html__('Are You Sure?', 'sneeit'),
			'type_reset_to_confirm' => esc_html__('Type [reset] to confirm!', 'sneeit'),
			'search_result_single' => esc_html__('Found %s control', 'sneeit'),
			'search_result_plural' => esc_html__('Found %s controls', 'sneeit'),
			'search_result_not_found' => esc_html__('Not found any control', 'sneeit'),
		)
	));
	wp_localize_script('sneeit-theme-options', 'STOEI', array(
		'export_url' => admin_url( 'customize.php' ) . '?'.SNEEIT_KEY_SNEEIT_EXPORT.'=' . wp_create_nonce( SNEEIT_KEY_SNEEIT_EXPORT ),
		'import_nonce' => esc_attr(wp_create_nonce( SNEEIT_KEY_SNEEIT_IMPORT)),		
		'text' => array(
			'empty_import' => esc_html__('Please choose a file to import', 'sneeit'),
		),
		'export_key' => SNEEIT_KEY_SNEEIT_EXPORT,
		'import_key' => SNEEIT_KEY_SNEEIT_IMPORT,
	));
}
