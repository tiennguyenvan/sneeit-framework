<?php

function sneeit_customize_cssout($setting_id, $setting_property, &$google_font_url, &$upload_font_url) {
	if (!isset($setting_property['cssout'])) {
		return '';
	}
	if (!isset($setting_property['type'])) {
		$setting_property['type'] = 'text';
	}
	$setting_value = get_theme_mod($setting_id, isset($setting_property['default']) ? $setting_property['default']: '');
	
	
	
	switch ($setting_property['type']) {
		case 'font-family':	
			$setting_value = sneeit_get_font_family_css($setting_value, $google_font_url, $upload_font_url);
			break;
		
		case 'font':
			$font_style = '';
			$font_weight = '';
			$font_size = '';
			$font_name = '';
			$setting_value = explode(' ', $setting_value);
			foreach ($setting_value as $key => $value) {
				if ($key == 0) {
					$font_style = $value;
				} else if ($key == 1) {
					$font_weight = $value;
				} else if ($key == 2) {
					$font_size = $value;
					if (strpos($font_size, 'px') === false) {
						$font_size = $font_size.'px';
					}
				} else {
					$font_name .= $value . ' ';
				}
			}
			$font_name = sneeit_get_font_family_css($font_name, $google_font_url, $upload_font_url);
			$setting_value = $font_style . ' ' . $font_weight . ' ' . $font_size . ' ' . $font_name;
			
			break;
			
		case 'box-padding-px':
			if (is_rtl()) {
				$setting_value = explode(' ', $setting_value);
				
				if (count($setting_value) < 4) {
					break;
				}
				$setting_value = 
					$setting_value[0] .' '. 
					$setting_value[3] .' '. 
					$setting_value[2] .' '. 
					$setting_value[1];				
			}			
			break;
			
		default:
			break;
	}
	
	if ($setting_value) {
		return str_replace('%s', $setting_value, $setting_property['cssout']);	
	}
	return '';
}
if (sneeit_is_gpsi()) {
	global $google_font_url_enqueue;
	$google_font_url_enqueue = '';
}
add_action('wp_head', 'sneeit_customizer_out_wp_enqueue_scripts', 1000);
function sneeit_customizer_out_wp_enqueue_scripts() {
	global $Sneeit_Safe_Fonts;
	global $Sneeit_Google_Fonts;
	global $Sneeit_Upload_Fonts;
	global $Sneeit_Customize_Declarations;
	if (sneeit_is_gpsi()) {
		global $google_font_url_enqueue;
	}
	$customizer_css_out = '';
	$google_font_url = array();
	$upload_font_url = array();
	
	if (!is_array($Sneeit_Customize_Declarations)) {
		return;
	}
	
	
	if ($Sneeit_Upload_Fonts == null && sneeit_customize_has_fonts($Sneeit_Customize_Declarations)) {	
		sneeit_get_uploaded_fonts();
	}	
	
	foreach ($Sneeit_Customize_Declarations as $level_1_id => $level_1_value) :
		// check if this is setting and it has cssout property
		if (isset($level_1_value['cssout'])) {
			$customizer_css_out .= sneeit_customize_cssout($level_1_id, $level_1_value, $google_font_url, $upload_font_url);			
			continue;
		}

		$level_1_next = array();		
		if (isset($level_1_value['sections'])) {		
			// this is a panel
			$level_1_next = $level_1_value['sections'];			
		} else if (isset($level_1_value['settings'])) {
			// this is a section		
			$level_1_next = $level_1_value['settings'];			
		}

		// next level 1
		foreach ($level_1_next as $level_2_id => $level_2_value) :
			if (isset($level_2_value['cssout'])) {
				$customizer_css_out .= sneeit_customize_cssout($level_2_id, $level_2_value, $google_font_url, $upload_font_url);
				continue;
			}
			
			// level 2 is a section (we have no panel here)
			if (isset($level_2_value['settings'])) {
				// scan for last level of declaration
				foreach ($level_2_value['settings'] as $level_3_id => $level_3_value) {

					
					if (isset($level_3_value['cssout'])) {
						$customizer_css_out .= sneeit_customize_cssout($level_3_id, $level_3_value, $google_font_url, $upload_font_url);
						continue;
					}
				}
			}
		endforeach;
	endforeach;
	
	
	
	// enqueue to load fonts
	// enqueue for google fonts
	$google_font_url_enqueue = '';
	foreach ($google_font_url as $font_url) {
		if ($google_font_url_enqueue) {
			$google_font_url_enqueue .= '|';
		}
		$google_font_url_enqueue .= $font_url;
	}
	
//	if (current_user_can('manage_options')) {
//		echo $google_font_url_enqueue;
//		$google_font_url_enqueue = 'Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic|Oswald:400,300,700';
//	}
	
	if ($google_font_url_enqueue && !sneeit_is_gpsi()) {
		// we can load a lot of families with 1 style request
		wp_enqueue_style( 'sneeit-google-fonts', '//fonts.googleapis.com/css?family='.$google_font_url_enqueue, array(), SNEEIT_PLUGIN_VERSION );
	}
	
	// enqueue for upload fonts, with inline css
	$upload_font_url_enqueue = '';
	foreach ($upload_font_url as $font_name => $font_src) {
		$upload_font_url_enqueue .= '@font-face {font-family: "'.$font_name.'";'.$font_src.'}';
	}
	
	$customizer_css_out = $upload_font_url_enqueue . $customizer_css_out;
	
	echo '<style type="text/css">'. $customizer_css_out .'</style>';
}


if (sneeit_is_gpsi()) {
	function customizer_out_wp_footer() {
		wp_enqueue_style( 'sneeit-sneeit');	
		global $google_font_url_enqueue;
		if ($google_font_url_enqueue && !sneeit_is_gpsi()) {
		// we can load a lot of families with 1 style request
			wp_enqueue_style( 'sneeit-google-fonts', '//fonts.googleapis.com/css?family='.$google_font_url_enqueue, array(), SNEEIT_PLUGIN_VERSION );
		}
	}
	add_action( 'wp_footer', 'customizer_out_wp_footer');
}