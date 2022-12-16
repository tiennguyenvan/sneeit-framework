<?php

function sneeit_customize_controls_enqueue_scripts() {
	wp_enqueue_style('sneeit-font-awesome');
	wp_enqueue_style('sneeit-font-awesome-shims');
	wp_enqueue_style('sneeit-customizer', SNEEIT_PLUGIN_URL_CSS . 'customizer.css', array(), SNEEIT_PLUGIN_VERSION );
	wp_enqueue_script('jquery');
	wp_enqueue_script('sneeit-lib', SNEEIT_PLUGIN_URL_JS .'lib.js', array('jquery'), SNEEIT_PLUGIN_VERSION, false);
	wp_enqueue_script('sneeit-customizer', SNEEIT_PLUGIN_URL_JS .'customizer.js', array('jquery', 'sneeit-lib'), SNEEIT_PLUGIN_VERSION, true);

	global $Sneeit_Customize_Declarations;	
	
	wp_localize_script('sneeit-customizer', 'Sneeit_Customize_Options', $Sneeit_Customize_Declarations);
}
add_action( 'customize_controls_enqueue_scripts', 'sneeit_customize_controls_enqueue_scripts');