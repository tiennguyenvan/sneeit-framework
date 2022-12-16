<?php
/*apply: https://make.wordpress.org/core/2014/07/08/customizer-improvements-in-4-0/*/
/*customizer lib, only use in customizer extension*/
function sneeit_add_customize_setting($wp_customize, $section_id, $setting_id, $setting_declarations) {	
	// add setting to its section
	if (!class_exists( 'WP_Customize_Control' ) ) {
		return;
	}
	if (!isset($setting_declarations['type'])) {
		$setting_declarations['type'] = 'text';
	}
	$default_value = (isset($setting_declarations['default']) ? 
						$setting_declarations['default'] : 
						(($setting_declarations['type'] == 'number') ? 0 : ''));
	$wp_customize->add_setting($setting_id , array(
		'default' => $default_value
		)
	);
	$control_id = $setting_id . '_'.$setting_declarations['type'].'_control';
	$setting_declarations['attr'] = array(
		'data-customize-setting-link' => $setting_id
	);
	
	
	$control_options = array(				
		'label'			=> (/*you can input both title or label are also ok*/
				isset($setting_declarations['label']) ? 
					$setting_declarations['label'] : 
					(
						isset($setting_declarations['title']) ? 
							$setting_declarations['title'] : 
							sneeit_slug_to_title($setting_id)
					)
			),
		'priority'		=> (isset($setting_declarations['priority'])? $setting_declarations['priority'] : SNEEIT_DEFAULT_CUSTOMIZER_PRIORITY),
		'section'		=> $section_id,
		'settings'		=> $setting_id,
		'type'			=> $setting_declarations['type'],
		'setting_id'	=> $setting_id,
		'description'	=> (isset($setting_declarations['description'])? $setting_declarations['description'] : ''),
		'declarations'	=> $setting_declarations,
	);
	
	$input_attrs = array();
	if (isset($setting_declarations['min'])) {
		$input_attrs['min'] = $setting_declarations['min'];
	} else {
		$input_attrs['min'] = 0;
	}

	if (isset($setting_declarations['max'])) {
		$input_attrs['max'] = $setting_declarations['max'];
	} else {
		$input_attrs['max'] = 1000;
	}

	if (isset($setting_declarations['step'])) {
		$input_attrs['step'] = $setting_declarations['step'];
	} else {
		$input_attrs['step'] = 1;
	}

	if (isset($setting_declarations['class'])) {
		$input_attrs['class'] = $setting_declarations['class'];
	} else {
		$input_attrs['class'] = '';
	}

	if (isset($setting_declarations['style'])) {
		$input_attrs['style'] = $setting_declarations['style'];
	} else {
		$input_attrs['style'] = '';
	}
	
	if (count($input_attrs)) {
		$control_options['input_attrs'] = $input_attrs;
	}
	
	if (isset($setting_declarations['none'])) {
		$control_options['none'] = $setting_declarations['none'];
	}
	if (isset($setting_declarations['prefix'])) {
		$control_options['prefix'] = $setting_declarations['prefix'];
	}
	
	
	// modify the declaration
	if (isset($setting_declarations['choices'])) {
		$control_options['choices'] = $setting_declarations['choices'];			
		
		// check if choices contain special HTML tags, we will force it as visual picker
		$c_choice = current($control_options['choices']);
		if (strpos($c_choice, '<') !== false && strpos($c_choice, '>') != false	&& $control_options['type'] == 'select') {
			$control_options['type'] = 'visual';
		}
	}
	
	/* "content" type does not work at this time 
	 * because customizer can not detect after 
	 * replacing textarea with wp_editor by javascript
	 */
	if ('content' == $control_options['type']) {
		$control_options['type'] = 'textarea';
		$control_options['declarations']['type'] = 'textarea';
	}
	
	
	// add control via Customize API	
	switch ($control_options['type']) :
		case 'color':
			$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize,$control_id,$control_options));
			break;
		
		case 'media':
			$wp_customize->add_control(new WP_Customize_Media_Control($wp_customize,$control_id,$control_options));
			break;
		
		case 'upload':
		case 'file':
			$control_options['type'] = 'upload';			
			$wp_customize->add_control(new WP_Customize_Upload_Control($wp_customize,$control_id,$control_options));
			break;

		case 'image':			
			$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize,$control_id,$control_options));
			break;							
		
		default:
			$wp_customize->add_control(new WP_Customize_Sneeit_Control($wp_customize,$control_id,$control_options));
			break;
	endswitch;	
}

function sneeit_customize_has_fonts($declarations) {	
	if (!is_array($declarations)) {
		return false;
	}
	global $Sneeit_Customize_Declarations;
	foreach ($Sneeit_Customize_Declarations as $level_1_id => $level_1_value) :
		if (isset($level_1_value['type']) && ($level_1_value['type'] == 'font' || $level_1_value['type'] == 'font-family')) {
			return true;
		}
		
		$level_1_next = array();
		if (isset($level_1_value['sections'])) {		
			$level_1_next = $level_1_value['sections'];
		} else if (isset($level_1_value['settings'])) {
			$level_1_next = $level_1_value['settings'];
		}


		// next level 1
		foreach ($level_1_next as $level_2_id => $level_2_value) :
			if (isset($level_2_value['type']) && ($level_2_value['type'] == 'font' || $level_2_value['type'] == 'font-family')) {
				return true;
			}
			
			if (isset($level_2_value['settings'])) {			
				// scan for last level of declaration
				foreach ($level_2_value['settings'] as $level_3_id => $level_3_value) {
					if (isset($level_3_value['type']) && ($level_3_value['type'] == 'font' || $level_3_value['type'] == 'font-family')) {
						return true;
					}		
				}

			}
		endforeach;
	endforeach;

	return false;	
}


function sneeit_customizer_export_settings_disable($wp_customize) {
	if ( ! wp_verify_nonce( $_REQUEST['sneeit-customizer-export'], 'sneeit-customizer-exporting' ) ) {
		return;
	}

	$theme		= get_stylesheet();
	$template	= get_template();
	$charset	= get_option( 'blog_charset' );
	$mods		= get_theme_mods();
	$data		= array(
		'template'  => $template,
		'mods'	  => $mods ? $mods : array(),
		'options'	  => array()
	);

	// Get options from the Customizer API.
	$settings = $wp_customize->settings();

	foreach ( $settings as $key => $setting ) {

		if ( 'option' == $setting->type ) {

			// Don't save widget data.
			if ( stristr( $key, 'widget_' ) ) {
				continue;
			}

			// Don't save sidebar data.
			if ( stristr( $key, 'sidebars_' ) ) {
				continue;
			}

			// Don't save core options.
			if ( in_array( $key, array(
					'blogname',
					'blogdescription',
					'show_on_front',
					'page_on_front',
					'page_for_posts'
			)) ) {
				continue;
			}

			$data['options'][ $key ] = $setting->value();
		}
	}

	// Plugin developers can specify additional option keys to export.
	$option_keys = apply_filters( 'sneeit_customizer_export_option_keys', array() );

	foreach ( $option_keys as $option_key ) {

		$option_value = get_option( $option_key );

		if ( $option_value ) {
			$data['options'][ $option_key ] = $option_value;
		}
	}

	// Set the download headers.
	header( 'Content-disposition: attachment; filename=' . $theme . 'settings-options.dat' );
	header( 'Content-Type: application/octet-stream; charset=' . $charset );

	// Serialize the export data.
	echo serialize( $data );

	// Start the download.
	die();
}


function sneeit_customizer_export_settings() {
	if ( ! wp_verify_nonce( $_REQUEST[SNEEIT_KEY_SNEEIT_EXPORT], SNEEIT_KEY_SNEEIT_EXPORT ) ) {
		return;
	}
	
	$data = array(
		'template'  => get_template(),
		'mods'		=> get_theme_mods(),	
	);
	
	// Set the download headers.
	header( 'Content-disposition: attachment; filename=' . get_stylesheet() . '-settings-options.dat' );
	header( 'Content-Type: application/octet-stream; charset=' . get_option( 'blog_charset' ) );

	// Serialize the export data.
	echo json_encode($data);

	// Start the download.
	die();
}

function sneeit_customizer_import_error($error = '') {
	set_transient(SNEEIT_KEY_SNEEIT_IMPORT, $error, 3600);
	
	if (!empty($_REQUEST[SNEEIT_KEY_SNEEIT_IMPORT.'-refer'])) {
		wp_redirect($_REQUEST[SNEEIT_KEY_SNEEIT_IMPORT.'-refer']);		
		die();
	}
}
function sneeit_customizer_import_settings() {	
	// Make sure we have a valid nonce.
	if ( ! wp_verify_nonce( $_REQUEST[SNEEIT_KEY_SNEEIT_IMPORT], SNEEIT_KEY_SNEEIT_IMPORT ) ) {		
		return;
	}

	// Make sure WordPress upload support is loaded.
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}
	
	// Setup internal vars.	
	$template	 = get_template();
	$overrides   = array( 'test_form' => FALSE, 'mimes' => array('dat' => 'text/dat') );
	$file        = wp_handle_upload( $_FILES[SNEEIT_KEY_SNEEIT_IMPORT.'-file'], $overrides );


	// Make sure we have an uploaded file.
	if ( isset( $file['error'] ) ) {		
		return sneeit_customizer_import_error($file['error']);		
	}
	if ( ! file_exists( $file['file'] ) ) {
		return sneeit_customizer_import_error(__( 'The file is not exist', 'sneeit' ));
	}

	// Get the upload data.
	$raw  = file_get_contents( $file['file'] );
	$data = json_decode( $raw , true );
	
	// Remove the uploaded file.
	unlink( $file['file'] );

	// Data checks.
	if ( !is_array( $data ) ) {
		return sneeit_customizer_import_error(__( 'The file has wrong data format', 'sneeit' ));		
	}
	if ( ! isset( $data['template'] ) || ! isset( $data['mods'] ) ) {
		return sneeit_customizer_import_error(__( 'The file has no required data', 'sneeit' ));		
	}
	if ( $data['template'] != $template ) {
		return sneeit_customizer_import_error(__( 'The file data is not for the current template', 'sneeit' ));		
	}	
	
	// Loop through the mods.
	foreach ( $data['mods'] as $key => $val ) {
		// Save the mod.
		set_theme_mod( $key, $val );
	}
	
	if (!empty($_REQUEST[SNEEIT_KEY_SNEEIT_IMPORT.'-refer'])) {
		wp_redirect($_REQUEST[SNEEIT_KEY_SNEEIT_IMPORT.'-refer']);		
		die();
	}
}
