<?php
function sneeit_add_custom_sidebar_callback_deprecated() {	
	$sneeit_sidebars_declaration = get_option(SNEEIT_OPT_SIDEBARS_DECLARATION);
	$name = sneeit_get_server_request('name');
	$format = sneeit_get_server_request('format');
	$sidebar = array();
	if (!$format || !isset($sneeit_sidebars_declaration[$format])) {
		$format = 'sneeit';		
	} else {
		$sidebar = $sneeit_sidebars_declaration[$format];
	}
	$id = sneeit_title_to_slug($format.'-'.$name);
	
	$new_sidebar =	array(
		'name' => $name,
		'id' => $id
	);
	
	if (isset($sidebar['class'])) {
		$new_sidebar['class'] = $sidebar['class'];
	}
	if (isset($sidebar['description'])) {
		$new_sidebar['description'] = $sidebar['description'];
	}
	if (isset($sidebar['before_widget'])) {
		$new_sidebar['before_widget'] = $sidebar['before_widget'];
	}
	if (isset($sidebar['after_widget'])) {
		$new_sidebar['after_widget'] = $sidebar['after_widget'];
	}
	if (isset($sidebar['before_title'])) {
		$new_sidebar['before_title'] = $sidebar['before_title'];
	}
	if (isset($sidebar['after_title'])) {
		$new_sidebar['after_title'] = $sidebar['after_title'];
	}
	
	
	$sneeit_custom_sidebars = get_option(SNEEIT_OPT_CUSTOM_SIDEBARS);
	if (!is_array($sneeit_custom_sidebars)) {
		$sneeit_custom_sidebars = array();		
	}
	$sneeit_custom_sidebars[$id] = $new_sidebar;
	update_option(SNEEIT_OPT_CUSTOM_SIDEBARS, $sneeit_custom_sidebars);
	
	echo 'DONE';
	
	die();
}
function sneeit_add_custom_sidebar_callback() {	
	$name = sneeit_get_server_request('name');
	$format = sneeit_get_server_request('format');
	
	if (!$format) {		
		$format = 'sneeit';		
	}
	$id = sneeit_title_to_slug($format.'-'.$name);
	$sneeit_custom_sidebars = get_option(SNEEIT_OPT_CUSTOM_SIDEBARS);
	if (!is_array($sneeit_custom_sidebars)) {
		$sneeit_custom_sidebars = array();		
	}
	$sneeit_custom_sidebars[$id] = $name;
	update_option(SNEEIT_OPT_CUSTOM_SIDEBARS, $sneeit_custom_sidebars);
	
	echo 'DONE';
	
	die();
}
if (is_admin()) :
	add_action( 'wp_ajax_nopriv_sneeit_add_custom_sidebar', 'sneeit_add_custom_sidebar_callback' );
	add_action( 'wp_ajax_sneeit_add_custom_sidebar', 'sneeit_add_custom_sidebar_callback' );
endif;// is_admin for ajax


function sneeit_delete_custom_sidebar_callback() {	
	$id = sneeit_get_server_request('id');
	$sneeit_custom_sidebars = get_option(SNEEIT_OPT_CUSTOM_SIDEBARS);
	if (is_array($sneeit_custom_sidebars) && $id && isset($sneeit_custom_sidebars[$id])) {
		unset($sneeit_custom_sidebars[$id]);
		update_option(SNEEIT_OPT_CUSTOM_SIDEBARS, $sneeit_custom_sidebars);
	}
	echo 'DONE';
	
	die();
}
if (is_admin()) :
	add_action( 'wp_ajax_nopriv_sneeit_delete_custom_sidebar', 'sneeit_delete_custom_sidebar_callback' );
	add_action( 'wp_ajax_sneeit_delete_custom_sidebar', 'sneeit_delete_custom_sidebar_callback' );
endif;// is_admin for ajax


function sneeit_rename_custom_sidebar_callback() {	
	$name = sneeit_get_server_request('name');
	$id = sneeit_get_server_request('id');
	$sneeit_custom_sidebars = get_option(SNEEIT_OPT_CUSTOM_SIDEBARS);
	if (is_array($sneeit_custom_sidebars) && $id && isset($sneeit_custom_sidebars[$id]) && $name) {
		$sneeit_custom_sidebars[$id]['name'] = $name;
		update_option(SNEEIT_OPT_CUSTOM_SIDEBARS, $sneeit_custom_sidebars);
	}
	
	echo 'DONE';
	
	die();
}
if (is_admin()) :
	add_action( 'wp_ajax_nopriv_sneeit_rename_custom_sidebar', 'sneeit_rename_custom_sidebar_callback' );
	add_action( 'wp_ajax_sneeit_rename_custom_sidebar', 'sneeit_rename_custom_sidebar_callback' );
endif;// is_admin for ajax