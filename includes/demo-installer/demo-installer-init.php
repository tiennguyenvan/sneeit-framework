<?php
global $Sneeit_Demo_Installer;
include_once 'demo-installer-defines.php';
include_once 'demo-installer-ajax.php';

function sneeit_demo_installer_admin_menu() {
	global $Sneeit_Demo_Installer;
	
	if (!isset($Sneeit_Demo_Installer['menu-title'])) {
		$Sneeit_Demo_Installer['menu-title'] = esc_html__('Demo Installation', 'sneeit');
	}
	
	if (!isset($Sneeit_Demo_Installer['page-title'])) {
		$Sneeit_Demo_Installer['page-title'] = esc_html__('Demo Installation', 'sneeit');
	}
	
	add_theme_page( 
		$Sneeit_Demo_Installer['page-title'],
		$Sneeit_Demo_Installer['menu-title'], 
		'manage_options',
		'sneeit-demo-installer', 
		'sneeit_demo_installer_html'
	);
}
function sneeit_demo_installer_html() {
	global $Sneeit_Demo_Installer;
	if (!isset($Sneeit_Demo_Installer['page-title'])) {
		$Sneeit_Demo_Installer['page-title'] = esc_html__('Demo Installation', 'sneeit');
	}
	
	echo '<div class="wrap">'.
		'<h1>'.$Sneeit_Demo_Installer['page-title'].'</h1>';
		if (isset($Sneeit_Demo_Installer['html-before'])) {
			echo $Sneeit_Demo_Installer['html-before'];
		}
		
		
		include_once 'demo-installer-html.php';
		
		if (isset($Sneeit_Demo_Installer['html-after'])) {
			echo $Sneeit_Demo_Installer['html-after'];
		}
		
		require_once 'demo-installer-enqueue.php';
	echo '</div>';
}

add_action('sneeit_demo_installer', 'sneeit_demo_installer',  10, 1); // end of filter
function sneeit_demo_installer($args) {
	// validate args
	if (!isset($args['declarations']) || !is_admin()) {
		return;
	}
	
	// save it
	global $Sneeit_Demo_Installer;	
	$Sneeit_Demo_Installer = $args;
	
	add_action( 'admin_menu', 'sneeit_demo_installer_admin_menu');
}
