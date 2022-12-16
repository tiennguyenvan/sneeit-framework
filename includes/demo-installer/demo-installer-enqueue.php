<?php

add_action( 'admin_print_footer_scripts', 'sneeit_demo_installer_admin_enqueue_scripts', 1);
function sneeit_demo_installer_admin_enqueue_scripts() {
	global $Sneeit_Demo_Installer;
	$demo_list = $Sneeit_Demo_Installer['declarations'];
	wp_enqueue_script('sneeit-lib');
	wp_enqueue_style('sneeit-demo-installer', SNEEIT_PLUGIN_URL_CSS . 'demo-installer.css', array(), SNEEIT_PLUGIN_VERSION);
	wp_enqueue_script('sneeit-demo-installer', SNEEIT_PLUGIN_URL_JS .'demo-installer.js', array( 'jquery' ), SNEEIT_PLUGIN_VERSION, false);
	
	wp_localize_script('sneeit-demo-installer', 'sneeit_demo_installer', array(
		'text' => array(
			'Unknown error' => __('Unknown error', 'sneeit'),
			'Building database file' => __('Building database file', 'sneeit'),
			'Listing files' => __('Listing files', 'sneeit'),
			'Building Files' => __('Building Files', 'sneeit'),
			'Built successfully' => __('Built successfully', 'sneeit'),
			'Done' => __('Done', 'sneeit'),
			'Delete' => __('Delete', 'sneeit'),
			'Get Code' => __('Get Code', 'sneeit'),
			'Restore Demo' => __('Restore Demo', 'sneeit'),
			'Your demo name' => __('Your Demo Name', 'sneeit'),
			'Not found any built demo files' => __('Not found any built demo files', 'sneeit'),
			'This function is for DEVELOPERS only to help them building demo data to integrate to their themes. Are you sure to BUILD yours?' => __('This function is for DEVELOPERS only to help them building demo data to integrate to their themes. Are you sure to BUILD yours?', 'sneeit'),
			'Can not explore the demo data' => __('Can not explore the demo data', 'sneeit'),
			'Can not delete the demo data' => __('Can not delete the demo data', 'sneeit'),
			'Download those below files and upload to some where (example: Google drive, Drop Box).' => __('Download those below files and upload to some where (example: Google drive, Drop Box).', 'sneeit'),
			'copy_code' => __('Then copy the DIRECT download link of them and put into your "sneeit_demo_installer" declaration hook in your theme as below:', 'sneeit'),
			'Creating download folder' => __('Creating download folder', 'sneeit'),
			'Installing files'  => __('Installing files', 'sneeit'),
			'Downloading files'  => __('Downloading files', 'sneeit'),
			'Extracting files'  => __('Extracting files', 'sneeit'),
			'Listing files'  => __('Moving files', 'sneeit'),
			'Moving files'  => __('Moving files', 'sneeit'),			
			'Installed successfully'  => __('Installed successfully', 'sneeit'),
			'A process is running!'  => __('A process is running!', 'sneeit'),
			'Please make sure your site is very NEW or just a TEST site, because demo data will ERASE all your database. Are you sure to install demo?'  => __('Please make sure your site is very NEW or just a TEST site, because demo data will ERASE all your database. Are you sure to install demo?', 'sneeit'),
		),
		'demo_list' => $demo_list,
		'max_moving_loop' => SNEEIT_DEMO_INSTALLER_MAX_MOVING_LOOP,
		'max_moving_file_number' => SNEEIT_DEMO_INSTALLER_MAX_MOVING_FILE_NUMBER,
	));
}
