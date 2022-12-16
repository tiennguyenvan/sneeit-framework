<?php
function sneeit_page_builder_admin_enqueue_scripts($hook) {
	
	if ('post.php' != $hook && 'post-new.php' != $hook) {	
		return;
	}
	global $Sneeit_ShortCodes;
	
	if (empty($Sneeit_ShortCodes)) {		
		return;
	}
	
	global $Sneeit_PageBuilder_Declaration;
	
	
	// register style
	wp_register_style( 'sneeit-page-builder', SNEEIT_PLUGIN_URL_CSS . 'page-builder.css', array(), SNEEIT_PLUGIN_VERSION );    
	
	// register script
	wp_register_script('sneeit-page-builder-lib', SNEEIT_PLUGIN_URL_JS . 'page-builder-lib.js', array(
		'jquery',
		'sneeit-shortcodes-box'
	), SNEEIT_PLUGIN_VERSION, true);
	wp_register_script('sneeit-page-builder', SNEEIT_PLUGIN_URL_JS . 'page-builder.js', array(
		'sneeit-page-builder-lib'
	), SNEEIT_PLUGIN_VERSION, true);
		
	// enqueue style
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'sneeit-font-awesome');  
	wp_enqueue_style('sneeit-font-awesome-shims');
	wp_enqueue_style( 'sneeit-shortcode');  	
	wp_enqueue_style( 'sneeit-page-builder'); 
	
	// enqueue script
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-tooltip');
	wp_enqueue_script('sneeit-lib');
	wp_enqueue_script('sneeit-shortcodes-box');
	wp_enqueue_script('sneeit-page-builder-lib');
	wp_enqueue_script('sneeit-page-builder');
	
	
	// localize script
	
	wp_localize_script('sneeit-lib', 'Sneeit_PageBuilder_Declaration', $Sneeit_PageBuilder_Declaration);
	wp_localize_script('sneeit-lib', 'Sneeit_PageBuilder_Options', array(
		'text' => array(
				'Page_builder' => __('Sneeit Page Builder', 'sneeit')
			,	'Columns' => __('Columns', 'sneeit')
			,	'Shortcodes' => __('Shortcodes', 'sneeit')
			,	'Edit' => __('Edit', 'sneeit')
			,	'Duplicate' => __('Duplicate', 'sneeit')
			,	'Exit' => __('Exit', 'sneeit')
			,	'Apply' => __('Apply', 'sneeit')
			,	'Applied_your_changes' => __('Applied your changes', 'sneeit')
			,	'Delete' => __('Delete', 'sneeit')
			,	'Increase_width' => __('Increase Width', 'sneeit')
			,	'Decrease_width' => __('Decrease Width', 'sneeit')
			,	'You_made_changes_Are_you_sure_to_exit' => 
				__('You made changes. Are you sure to exit?', 'sneeit')
		),
		'column_pattern' => array('1/6', '1/5', '1/4', '1/3', '2/5', '1/2', '3/5', '2/3', '3/4', '4/5', '5/6', '1/1'),
		'style' => array(
			'toolbar_tab_content_column_button_margin' => 1.4,
		),
		'max_nested_level' => SNEEIT_MAX_NESTED_COLUMN_LEVEL,
		'separator_nested_level' => SNEEIT_NESTED_COLUMN_SEPARATOR
	));	
}
add_action( 'admin_enqueue_scripts', 'sneeit_page_builder_admin_enqueue_scripts', 10, 1);

