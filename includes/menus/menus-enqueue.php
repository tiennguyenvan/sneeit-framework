<?php
//add_action( 'admin_enqueue_scripts', 'sneeit_menus_admin_enqueue_scripts', 2);
//add_action( 'admin_enqueue_scripts', 'sneeit_menus_admin_enqueue_scripts', 2);
add_action( "admin_print_scripts-nav-menus.php",  'sneeit_menus_admin_enqueue_scripts');
function sneeit_menus_admin_enqueue_scripts () {
	include_once sneeit_framework_plugin_path('/includes/controls/controls.php');	
	wp_enqueue_style( 'sneeit-menus', SNEEIT_PLUGIN_URL_CSS .'menus.css', array(), SNEEIT_PLUGIN_VERSION );
    wp_enqueue_script( 'sneeit-menus', SNEEIT_PLUGIN_URL_JS .'menus.js', array(), SNEEIT_PLUGIN_VERSION, true );
}
