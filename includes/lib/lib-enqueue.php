<?php

add_action( 'admin_enqueue_scripts', 'sneeit_lib_admin_enqueue_scripts');
function sneeit_lib_admin_enqueue_scripts () {
	// register global styles
	if (is_rtl()) :
		wp_register_style('sneeit-font-awesome', SNEEIT_PLUGIN_URL_FONT_AWESOME_RTL, array(), SNEEIT_PLUGIN_VERSION );    	
	else:
		wp_register_style('sneeit-font-awesome', SNEEIT_PLUGIN_URL_FONT_AWESOME, array(), SNEEIT_PLUGIN_VERSION );    	
	endif;
	wp_register_style('sneeit-font-awesome-shims', SNEEIT_PLUGIN_URL_FONT_AWESOME_SHIMS, array(), SNEEIT_PLUGIN_VERSION );   
	
	
	wp_register_style('sneeit-plugin-chosen', SNEEIT_PLUGIN_URL_JS_PLUGINS . 'chosen/chosen.jquery.css', array(), SNEEIT_PLUGIN_VERSION);
	
	wp_enqueue_style('sneeit-admin', SNEEIT_PLUGIN_URL_CSS . 'admin.css', array(), SNEEIT_PLUGIN_VERSION);
	wp_enqueue_style('sneeit-font-awesome');
	wp_enqueue_style('sneeit-font-awesome-shims');
	
	// register global scripts
	wp_enqueue_script('iris');
	wp_enqueue_media();	
	
	wp_register_script('sneeit-lib', SNEEIT_PLUGIN_URL_JS . 'lib.js', array('jquery'), SNEEIT_PLUGIN_VERSION, false);
	wp_register_script('sneeit-plugin-chosen', SNEEIT_PLUGIN_URL_JS_PLUGINS .'chosen/chosen.jquery.min.js', array( 'jquery' ), SNEEIT_PLUGIN_VERSION, false);
	/*
	 * General setup for sneeit-lib
	 */
	
	wp_localize_script('sneeit-lib', 'Sneeit', array(
		'home_url' => SNEEIT_HOME_URL,
		'plugin_url' => SNEEIT_PLUGIN_URL,
		'plugin_img_url' => SNEEIT_PLUGIN_URL_IMAGES,
		'plugin_js_url' => SNEEIT_PLUGIN_URL_JS,
		'plugin_css_url' => SNEEIT_PLUGIN_URL_CSS,
		'is_localhost' => SNEEIT_IS_LOCALHOST,
		'is_rtl' => SNEEIT_IS_RLT
	));	
	
	wp_register_script('sneeit-web-fonts', 'https://ajax.googleapis.com/ajax/libs/webfont/1.5.18/webfont.js', array(), SNEEIT_PLUGIN_VERSION, true);
	
}


add_action( 'customize_controls_enqueue_scripts', 'sneeit_lib_enqueue_customize_controls_enqueue_scripts');
function sneeit_lib_enqueue_customize_controls_enqueue_scripts () {
	wp_register_style('sneeit-font-awesome', SNEEIT_PLUGIN_URL_FONT_AWESOME, array(), SNEEIT_PLUGIN_VERSION ); 
	wp_register_style('sneeit-font-awesome-shims', SNEEIT_PLUGIN_URL_FONT_AWESOME_SHIMS, array(), SNEEIT_PLUGIN_VERSION );
}

function sneeit_front_enqueue_url($file = '') {
	
	$id = explode('.', $file);
	$id = $id[0];
	
	$rtl = (is_rtl()? 'rtl-' : '');
	$rtl_ = ($rtl ? 'rtl/' : '');
	$min = (SNEEIT_IS_LOCALHOST? '' : '.min');
	$min_ = (SNEEIT_IS_LOCALHOST? '' : 'min/');
	
	/* enqueue css*/	
	if (strpos($file, '.css')) {
		return SNEEIT_PLUGIN_URL_CSS.$min_.$rtl_.$rtl.$id.$min.'.css';	
	}
	/* enqueue js */
	return SNEEIT_PLUGIN_URL_JS.$min_.$rtl_.$rtl.$id.$min.'.js';
}