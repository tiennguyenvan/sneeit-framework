<?php
function sneeit_shortcodes_admin_enqueue_scripts($hook) {
	if ('post.php' != $hook && 'post-new.php' != $hook) {
		return;
	}
	
	include_once( sneeit_framework_plugin_path('/includes/controls/controls.php') );
	
	
	global $Sneeit_ShortCodes;
	if (empty($Sneeit_ShortCodes)) {
		return;
	}
	
	if (!isset($Sneeit_ShortCodes['title'])) {
		$Sneeit_ShortCodes['title'] = '';
	}
	if (!isset($Sneeit_ShortCodes['icon'])) {
		$Sneeit_ShortCodes['icon'] = '';
	}
	
	
	
	// register style
	wp_register_style( 'sneeit-shortcodes', SNEEIT_PLUGIN_URL_CSS . 'shortcodes.css', array(), SNEEIT_PLUGIN_VERSION );
	
	// register script
	wp_register_script('sneeit-shortcodes-box', SNEEIT_PLUGIN_URL_JS . 'shortcodes-box.js', array(
		'sneeit-lib',
		'jquery', 
		'jquery-ui-sortable',
		'jquery-ui-accordion'
	), SNEEIT_PLUGIN_VERSION, false);
	wp_register_script('sneeit-shortcodes', SNEEIT_PLUGIN_URL_JS . 'shortcodes.js', array(
		'sneeit-shortcodes-box'
	), SNEEIT_PLUGIN_VERSION, false);
	
	// enqueue style
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'sneeit-plugin-chosen' );
	wp_enqueue_style( 'sneeit-font-awesome');
	wp_enqueue_style('sneeit-font-awesome-shims');
	wp_enqueue_style( 'sneeit-shortcodes' ); 
	
	// enqueue script
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('sneeit-plugin-chosen');
	wp_enqueue_media();
	wp_enqueue_script('sneeit-lib');		
	wp_enqueue_script('sneeit-shortcodes-box');
	wp_enqueue_script('sneeit-shortcodes');
	
	global $wp_registered_sidebars;
	
	// localize script	
	
	wp_localize_script('sneeit-shortcodes-box', 'Sneeit_Shortcodes', array(
		'title' => $Sneeit_ShortCodes['title'],
		'icon' => $Sneeit_ShortCodes['icon'],		
		'declaration' => $Sneeit_ShortCodes['declarations'],
		'text' => array(
			'insert_shortcode' => __('Insert Shortcode', 'sneeit'),
			'cancel' => __('Cancel', 'sneeit'),
			'remove' => __('Remove', 'sneeit'),
			'add_new' => __('Add New', 'sneeit'),
			'are_you_sure' => __('Something was changed, are you sure to close the shortcode dialog?', 'sneeit'),
			'None' => __('None', 'sneeit'),
			'Browse' => __('Browse', 'sneeit'),
			'Input Your Value' => __('Input Your Value', 'sneeit'),
			'Type Field Name and Enter' => esc_attr(__('Type Field Name and Enter', 'sneeit')),
			'Oops, nothing found!' => esc_attr(__('Oops, nothing found!', 'sneeit')),
			'Top' => esc_attr(__('Top', 'sneeit')),
		)
	));
}
add_action( 'admin_enqueue_scripts', 'sneeit_shortcodes_admin_enqueue_scripts', 10, 1 );
