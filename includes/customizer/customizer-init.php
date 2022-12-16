<?php
/* TODO
 * - Add validator for customizer declaration
 */
// local global variables
global $Sneeit_Customize_Declarations;$Sneeit_Customize_Declarations = array();

// local defines
define('SNEEIT_DEFAULT_CUSTOMIZER_PRIORITY', 50);

// local requirements
require_once 'customizer-lib.php';
require_once 'customizer-ajax.php';


add_action('sneeit_setup_customizer', 'sneeit_customizer_init_setup_customizer',1,1);
function sneeit_customizer_init_setup_customizer($declarations) {
	global $Sneeit_Customize_Declarations;
	$Sneeit_Customize_Declarations = $declarations;
	if (sneeit_customize_has_fonts($declarations)) {	
		sneeit_get_uploaded_fonts();
	}
	require_once 'customizer-default.php';
}


add_action( 'customize_register', 'sneeit_customizer_init_customize_register');
function sneeit_customizer_init_customize_register($wp_customize) {
	
	/* check if export or import */	
	if ( current_user_can( 'edit_theme_options' )) {
		if (isset( $_REQUEST[SNEEIT_KEY_SNEEIT_EXPORT] )) {		
			sneeit_customizer_export_settings();
			return;
		}
		
		if (isset( $_REQUEST[SNEEIT_KEY_SNEEIT_IMPORT]) &&
			isset( $_FILES[SNEEIT_KEY_SNEEIT_IMPORT.'-file'])) {		
			sneeit_customizer_import_settings();			
		}
	}	
	
	global $Sneeit_Customize_Declarations;
	
	if (is_array($Sneeit_Customize_Declarations)) {
		/* add sections for import / export */
		$Sneeit_Customize_Declarations[SNEEIT_KEY_SNEEIT_EXPORT_IMPORT] = array(
			'title' => esc_html__('Export / Import', 'sneeit'),
			'icon' => 'image-filter',
			'settings' => array(
				SNEEIT_KEY_SNEEIT_EXPORT => array(
					'label' => esc_html__('Export', 'sneeit'), 
					'description' => esc_html__('Click the button below to export customizaton settings for this theme', 'sneeit'), 
					'type' => 'export', 
				),
				SNEEIT_KEY_SNEEIT_IMPORT => array(
					'label' => esc_html__('Import', 'sneeit'), 
					'description' => esc_html__('Upload a file to import customizaton settings for this theme.', 'sneeit'), 
					'type' => 'import', 
				),
			),
		);

		require_once 'customizer-control.php';
		require_once 'customizer-register.php';		
	}
}

require_once 'customizer-enqueue.php';
require_once 'customizer-out.php';
