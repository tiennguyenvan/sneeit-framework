<?php

// https://stackoverflow.com/questions/13405572/sql-statement-to-get-column-type
// https://dev.mysql.com/doc/refman/5.7/en/create-table.html
// https://wordpress.stackexchange.com/questions/35012/matching-database-content-types-to-php-types

if (!class_exists('ZipArchive')) {
	sneeit_demo_installer_ajax_error(__('Building database: Your server has no ZIP application to compress a file', 'sneeit'));	
}

if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Building database: Can not init file system', 'sneeit'));	
}

global $wp_filesystem;

//$wp_filesystem = new WP_Filesystem_Base();

if (!$wp_filesystem) {
	sneeit_demo_installer_ajax_error(__('Building database: Can not access file system', 'sneeit'));
}

// change mode for content folder
//if (!$wp_filesystem->chmod(WP_CONTENT_DIR, 0777, true)) {
//	sneeit_demo_installer_ajax_error(__('Building database: Can not access site content folder', 'sneeit'));
//}

// create demo folder
if (!$wp_filesystem->is_dir(SNEEIT_DEMO_INSTALLER_FOLDER) && !$wp_filesystem->mkdir(SNEEIT_DEMO_INSTALLER_FOLDER, 0777)) {
	sneeit_demo_installer_ajax_error(__('Building database: Can not create main folder for saving built files', 'sneeit'));	
}

// create sub demo folder
$folder_name = time();
$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER . '/'. $folder_name;
if (!$wp_filesystem->mkdir($folder_path, 0777)) {
	sneeit_demo_installer_ajax_error(__('Building database: Can not create ID folder for saving built files', 'sneeit'), SNEEIT_DEMO_INSTALLER_FOLDER);
}


// read database
global $wpdb;

// list all table
$wpdb_tables = $wpdb->get_results("SHOW TABLES LIKE '%'");	
if (empty($wpdb_tables)) {
	sneeit_demo_installer_ajax_error(__('Building database: The database is empty or can not access', 'sneeit'), $folder_path);	
}

// init table data
global $table_prefix;
if (!$table_prefix) {
	$table_prefix = 'wp_';
}
$database = array();
$options = array();
foreach ($wpdb_tables as $table) {
	// read data for each table
	foreach ($table as $table_name) {
		// replace prefix place holder, so when we restore, we can use the restored site prefix
		$prefix_holder_table_name = str_replace(
			array(
				$table_prefix, 
				strtolower($table_prefix), 
				strtoupper($table_prefix), 
				ucfirst($table_prefix)
			),
			SNEEIT_DEMO_INSTALLER_TABLE_PREFIX_PLACE_HOLDER, 
			$table_name
		);
		
		
		// init database element
		$database[$prefix_holder_table_name] = array(
			'status' => array(),
			'query' => '',
			'column_status' => array(),
			'data' => ''
		);
		
		// get status of table and save to array
		$database[$prefix_holder_table_name]['status'] = $wpdb->get_results("SHOW TABLE STATUS LIKE '$table_name'");
		if (!$database[$prefix_holder_table_name]['status']) {
			sneeit_demo_installer_ajax_error(__('Building database: Can not get table status', 'sneeit'), $folder_path);	
		}
		
		// get create table query
		$table_query = $wpdb->get_results("SHOW CREATE TABLE ".sneeit_backquote(DB_NAME).".".sneeit_backquote($table_name));
		if (!$table_query) {
			sneeit_demo_installer_ajax_error(__('Building database: Can not get table query', 'sneeit'), $folder_path);	
		}
		$table_query = (array) $table_query[0];
		
		if (!isset($table_query["Create Table"])) {
			sneeit_demo_installer_ajax_error(__('Building database: Can not get table query string', 'sneeit'), $folder_path);	
		}
		$database[$prefix_holder_table_name]['query'] = $table_query["Create Table"];
		
		// replace query prefix with place holder
		$database[$prefix_holder_table_name]['query'] = str_replace(sneeit_backquote($table_name), sneeit_backquote($prefix_holder_table_name), $database[$prefix_holder_table_name]['query']);
		
		// get column status
		$table_column_names = $wpdb->get_results ("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' and TABLE_NAME = '$table_name'");
		if (!$table_column_names) {
			sneeit_demo_installer_ajax_error(__('Building database: Can not get table column names', 'sneeit'), $folder_path);	
		}
		foreach ($table_column_names as $table_column_name) {
			$table_col_name = (array) $table_column_name;
			$table_col_name = $table_col_name['COLUMN_NAME'];			
			$database[$prefix_holder_table_name]['column_status'][$table_col_name] = $wpdb->get_results ("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' and TABLE_NAME = '$table_name' AND COLUMN_NAME = '$table_col_name'");
			if (!$database[$prefix_holder_table_name]['column_status'][$table_col_name]) {
				sneeit_demo_installer_ajax_error(__('Building database: Can not get table column status', 'sneeit'), $folder_path);	
			}
		}
		
		// get data of table as array		
		$database[$prefix_holder_table_name]['data'] = $wpdb->get_results ("SELECT * FROM ".$table_name);
		if ($wpdb->last_error && !$database[$prefix_holder_table_name]['data']) {
			sneeit_demo_installer_ajax_error(__('Building database: Can not get table data array', 'sneeit'), $folder_path);
		}
		
		// remove admin account to prevent override the admin account of restored site
//		if ($table_name == $table_prefix.'users') {			
//			foreach ($database[$prefix_holder_table_name]['data'] as $key => $user) {				
//				if (property_exists($user, 'ID') && $user->ID == '1') {
//					unset($database[$prefix_holder_table_name]['data'][$key]);
//					break;
//				}				
//			}
//		}
	}		
}

$mods = get_theme_mods();
$alloptions = wp_load_alloptions();
$widgets = array();
foreach ($alloptions as $key => $value) {
	if (strpos($key, 'widget_') !== false && strpos($key, 'widget_') == 0) {
		$widgets[$key] = get_option($key);
	}
}

$data = array(
	'database' => $database,
	'mods' => $mods,
	'widgets' => $widgets
);

// convert to a string of json
$data_string = json_encode($data);
if (!$data_string) {
	sneeit_demo_installer_ajax_error(__('Building database: The database is too big to get and build', 'sneeit'), $folder_path);
}

// replace:
// - uploads url
// - themes url
// - plugins url
// - content url
// - includes url
// - admin url
// - site url
// - home url
// - admin email
$replace_strings = array();
$sneeit_wp_upload_dir = wp_upload_dir();
$replace_strings[SNEEIT_DEMO_INSTALLER_UPLOADS_DIR_URL_PLACE_HOLDER] = $sneeit_wp_upload_dir['baseurl'];
$replace_strings[SNEEIT_DEMO_INSTALLER_THEMES_DIR_URL_PLACE_HOLDER] = get_theme_root_uri();
$replace_strings[SNEEIT_DEMO_INSTALLER_PLUGINS_DIR_URL_PLACE_HOLDER] = plugins_url();
$replace_strings[SNEEIT_DEMO_INSTALLER_CONTENT_DIR_URL_PLACE_HOLDER] = content_url();
$replace_strings[SNEEIT_DEMO_INSTALLER_ADMIN_DIR_URL_PLACE_HOLDER] = admin_url();
$replace_strings[SNEEIT_DEMO_INSTALLER_SITE_DIR_URL_PLACE_HOLDER] = get_site_url();
$replace_strings[SNEEIT_DEMO_INSTALLER_HOME_DIR_URL_PLACE_HOLDER] = get_home_url();
$replace_strings[SNEEIT_DEMO_INSTALLER_ADMIN_EMAIL_PLACE_HOLDER] = get_option('admin_email');

foreach ($replace_strings as $key => $value) {
	if ($value) {
		$value = json_encode($value);
		if ($value) {
			$value = str_replace('"', '', $value);
			$data_string = str_replace($value, $key, $data_string);
		}
	}
}

// write down database to txt
if (!sneeit_write_file($folder_path.'/'.SNEEIT_DEMO_INSTALLER_DATA_FILE_NAME.'.txt', $data_string, $folder_path)) {
	sneeit_demo_installer_ajax_error(__('Building database: Can not create database file', 'sneeit'), $folder_path);
}

// zip file
if (!sneeit_zip_file($folder_path.'/'.SNEEIT_DEMO_INSTALLER_DATA_FILE_NAME.'-'.time().'.gz', 
					$folder_path.'/'.SNEEIT_DEMO_INSTALLER_DATA_FILE_NAME.'.txt', 
					SNEEIT_DEMO_INSTALLER_DATA_FILE_NAME.'.txt')) {
	sneeit_demo_installer_ajax_error(__('Building database: ZIP application can not zip database file', 'sneeit'), $folder_path);
}

// remove the text file
sneeit_delete_file($folder_path.'/'.SNEEIT_DEMO_INSTALLER_DATA_FILE_NAME.'.txt');

echo json_encode(array('folder' => $folder_name));
