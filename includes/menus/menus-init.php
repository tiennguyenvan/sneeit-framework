<?php
// local global
global $Sneeit_Menu_Fields_Declaration;$Sneeit_Menu_Fields_Declaration = array();
global $Sneeit_Menu_Locations_Declaration;$Sneeit_Menu_Locations_Declaration = array();

// local defines
// WE WILL FIND A MARK TO INSERT OUR FIELDS BEFORE THAT MARK
// THIS CAN BE CHANGED BY WORDPRESS UPDATE. PLEASE FIND ANOTHER WAY FOR MORE STABLE
define('SNEEIT_MENUS_OUTPUT_PREPEND_MARK', '<fieldset class="field-move hide-if-no-js description description-wide">');

// local modules
require_once 'menus-lib.php';
require_once 'menus-setup.php';
require_once 'menus-update.php';
require_once 'menus-compact.php';
require_once 'menus-responsive.php';
include_once 'menus-enqueue.php';

// INIT MENU LOCATIONS
add_action('sneeit_setup_menu_locations', 'sneeit_menus_init_setup_menu_locations', 1, 1);
function sneeit_menus_init_setup_menu_locations($declaration) {
	global $Sneeit_Menu_Locations_Declaration;
	$Sneeit_Menu_Locations_Declaration = sneeit_validate_menu_locations_declaration($declaration);	
	add_action( 'after_setup_theme', 'sneeit_menus_init_after_setup_theme');
}
function sneeit_menus_init_after_setup_theme() {
	global $Sneeit_Menu_Locations_Declaration;
	foreach ($Sneeit_Menu_Locations_Declaration as $location => $title) {
		register_nav_menu( $location, $title);
	}
}


// INIT MENU FIELDS AND WALKER
add_action('sneeit_setup_menu_fields', 'sneeit_menus_init_setup_menu_fields', 1, 1);
function sneeit_menus_init_setup_menu_fields($declaration = array()) {	
	global $Sneeit_Menu_Fields_Declaration;
	$Sneeit_Menu_Fields_Declaration = sneeit_validate_menu_fields_declaration($declaration);		
	
	add_filter( 'wp_edit_nav_menu_walker', 'sneeit_menus_init_wp_edit_nav_menu_walker');
	add_filter( 'wp_setup_nav_menu_item', 'sneeit_setup_menu_fields', 10, 1 );
	add_action( 'wp_update_nav_menu_item', 'sneeit_update_menu_fields', 10, 3 );
}

function sneeit_menus_init_wp_edit_nav_menu_walker() {
	
	include_once sneeit_framework_plugin_path('/includes/controls/controls.php');	
	include_once 'menus-fields.php';
	
	return 'Sneeit_Walker_Nav_Menu_Edit';
}

