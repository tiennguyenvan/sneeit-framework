<?php

define('SNEEIT_OPT_CUSTOM_SIDEBARS', 'sneeit_custom_sidebars');
define('SNEEIT_OPT_SIDEBARS_DECLARATION', 'sneeit_sidebars_declaration');

global $Sneeit_Sidebars_Declaration; $Sneeit_Sidebars_Declaration = array();
global $Sneeit_Support_Custom_Sidebars; $Sneeit_Support_Custom_Sidebars = false;
global $Sneeit_Custom_Sidebars_Declaration; $Sneeit_Custom_Sidebars_Declaration = array();
global $Sneeit_Widgets_Declaration; $Sneeit_Widgets_Declaration = array();

require_once 'widgets-lib.php';
require_once 'widgets-enqueue.php';
require_once 'widgets-ajax.php';

add_action('sneeit_setup_sidebars', 'sneeit_widgets_init_setup_sidebars' , 1, 1);
function sneeit_widgets_init_setup_sidebars($declaration) {
	global $Sneeit_Sidebars_Declaration;
	global $Sneeit_Custom_Sidebars_Declaration;
	$Sneeit_Sidebars_Declaration = sneeit_validate_sidebars_declaration($declaration);
	update_option(SNEEIT_OPT_SIDEBARS_DECLARATION, $Sneeit_Sidebars_Declaration);
}

add_action('sneeit_support_custom_sidebars', 'sneeit_widgets_init_support_custom_sidebars' , 1, 1);
function sneeit_widgets_init_support_custom_sidebars($declaration) {
	global $Sneeit_Support_Custom_Sidebars;
	global $Sneeit_Custom_Sidebars_Declaration;
	$Sneeit_Support_Custom_Sidebars = true;
	$Sneeit_Custom_Sidebars_Declaration = $declaration;
}


add_action('sneeit_setup_widgets', 'sneeit_widgets_init_setup_widgets' , 1, 1);
function sneeit_widgets_init_setup_widgets($declaration) {
	global $Sneeit_Widgets_Declaration;
	$Sneeit_Widgets_Declaration = sneeit_validate_widgets_declaration($declaration);
}

add_action( 'widgets_init', 'sneeit_widgets_init_widgets_init' , 100);
function sneeit_widgets_init_widgets_init(){ 
	
	global $Sneeit_Sidebars_Declaration;
	global $Sneeit_Support_Custom_Sidebars;
	global $Sneeit_Custom_Sidebars_Declaration;
	
	foreach ($Sneeit_Sidebars_Declaration as $sidebar_id => $sidebar_declaration) {		
		register_sidebar($sidebar_declaration);
	}
	
	// add custom sidebar
	$sneeit_custom_sidebars = get_option(SNEEIT_OPT_CUSTOM_SIDEBARS);	
	
	if ($Sneeit_Support_Custom_Sidebars && is_array($sneeit_custom_sidebars) && count($sneeit_custom_sidebars)) {
		foreach ($sneeit_custom_sidebars as $sidebar_id => $sidebar_data) {
			$sidebar_declaration = array();
			$sidebar_format = explode('-', $sidebar_id);
			$sidebar_format = $sidebar_format[0];
			if (is_array($sidebar_data)) {
				$sidebar_declaration = $sidebar_data;
			} else if (is_string($sidebar_format) && $sidebar_format != 'sneeit' && isset($Sneeit_Sidebars_Declaration[$sidebar_format])) {
				$sidebar_declaration = $Sneeit_Sidebars_Declaration[$sidebar_format];
			} else if (is_array($Sneeit_Custom_Sidebars_Declaration)) {
				$sidebar_declaration = $Sneeit_Custom_Sidebars_Declaration;
			}			
			$sidebar_declaration['id'] = $sidebar_id;
			if (is_string($sidebar_data)) {
				$sidebar_declaration['name'] = $sidebar_data;
			}
			$sneeit_custom_sidebars[$sidebar_id] = $sidebar_declaration;
			
			register_sidebar($sidebar_declaration);
		}
	}
	update_option(SNEEIT_OPT_CUSTOM_SIDEBARS, $sneeit_custom_sidebars);
	
	require_once 'widgets-class.php';
	require_once 'widgets-register.php';
}


// https://wordpress.org/ideas/topic/passing-arguments-to-widget?replies=4#post-28752
// https://wordpress.org/ideas/topic/allow-ability-to-pass-parameters-when-registering-widgets
add_action('sneeit_display_sidebar', 'sneeit_widgets_init_display_sidebar', 10, 1 );
function sneeit_widgets_init_display_sidebar($args = array()) {	
	$args = wp_parse_args($args, array(
		'id' => '',
		'class' => '',
		'before_sidebar' => '<aside id="%1$s" class="%2$s">',
		'after_sidebar' => '<div class="clear"></div></aside>'
	));
	
	if (!$args['id']) {
		return;
	}
	if (!$args['class']) {
		$args['class'] = 'sidebar';
	}
	
	$args['before_sidebar'] = sprintf($args['before_sidebar'], $args['id'], $args['class']);
	$args['after_sidebar'] = sprintf($args['after_sidebar'], $args['id'], $args['class']);
		
	if ( is_active_sidebar( $args['id'] )) :
		echo $args['before_sidebar'];
		dynamic_sidebar( $args['id'] );
		echo $args['after_sidebar'];
	endif;
}