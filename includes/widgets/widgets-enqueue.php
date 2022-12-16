<?php
add_action( 'admin_enqueue_scripts',  'sneeit_widgets_enqueue', 10, 1);
function sneeit_widgets_enqueue($hook) {
	if ('widgets.php' != $hook) {
		return;
	}
	
	global $Sneeit_Support_Custom_Sidebars;
	global $Sneeit_Sidebars_Declaration;
	global $Sneeit_Widgets_Declaration;
	global $wp_registered_sidebars;
	
	$sneeit_custom_sidebars = get_option(SNEEIT_OPT_CUSTOM_SIDEBARS);
	
	if (!is_array($sneeit_custom_sidebars))  {
		$sneeit_custom_sidebars = array();
	}
	
	wp_enqueue_style( 'jquery' );
	wp_enqueue_style( 'jquery-ui-core' );
	wp_enqueue_style( 'jquery-ui-tooltip' );
	wp_enqueue_style( 'sneeit-font-awesome' );
	wp_enqueue_style( 'sneeit-font-awesome-shims' );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'sneeit-plugin-chosen');
	wp_enqueue_media();
	wp_enqueue_style( 'sneeit-widgets', SNEEIT_PLUGIN_URL_CSS . 'widgets.css', array(), SNEEIT_PLUGIN_VERSION );
	
	wp_enqueue_script( 'sneeit-plugin-chosen');
    wp_enqueue_script( 'sneeit-widgets', SNEEIT_PLUGIN_URL_JS .'widgets.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-core', 'jquery-ui-tooltip' ), SNEEIT_PLUGIN_VERSION, true );
	wp_localize_script('sneeit-widgets', 'sneeit_widgets', array(
		'text' => array(
			'+ Add New Sidebar' => __('+ Add New Sidebar', 'sneeit'),
			'Cancel' => __('Cancel', 'sneeit'), 
			'Input Your Sidebar Name' => __('Input Your Sidebar Name', 'sneeit'),
			'Your Side Name Is Not Valid' => __('Your Sidebar Name Is Not Valid', 'sneeit'),
			'Sever Responded an Error Message!' => __('Sever Responded an Error Message!', 'sneeit'),
			'Default' => __('Default', 'sneeit'),
			'Are You Sure?' => __('Are You Sure?', 'sneeit'),
			'Input Your Value' => __('Input Your Value', 'sneeit'),
			'Delete Sidebar' => __('Delete Sidebar', 'sneeit'),
			'Rename Sidebar' => __('Rename Sidebar', 'sneeit'),
			'Follow Format Of' => __('Follow Format Of', 'sneeit')
		),
		'support_sidebar' => $Sneeit_Support_Custom_Sidebars,
		'sidebar_declaration' => $Sneeit_Sidebars_Declaration,
		'widget_declaration' => $Sneeit_Widgets_Declaration,
		'custom_sidebars' => $sneeit_custom_sidebars
	));
}

