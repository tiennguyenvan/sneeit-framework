<?php


global $wpdb;
global $table_prefix;
if (!$table_prefix) {
	$table_prefix = 'wp_';
}


if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Install Start: Can not init file system', 'sneeit'));	
}

global $wp_filesystem;
if (!$wp_filesystem) {
	sneeit_demo_installer_ajax_error(__('Install Start: Can not access file system', 'sneeit'));	
}


// collect parameters
$folder = sneeit_get_server_request('folder');
$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER . '/'. $folder;
if (!$folder) {
	sneeit_demo_installer_ajax_error(__('Install Start: Wrong submit parameters', 'sneeit'), $folder_path);
}


// get data string
$data_demo_string = $wp_filesystem->get_contents($folder_path.'/'.SNEEIT_DEMO_INSTALLER_DATA_FILE_NAME.'.txt');
if (!$data_demo_string) {
	sneeit_demo_installer_ajax_error(__('Install Start: missing data file', 'sneeit'), $folder_path);	
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
			$data_demo_string = str_replace($key, $value, $data_demo_string);
		}
	}
}




// check if still contains any place holder
//var_dump($data_demo_string);
foreach ($replace_strings as $key => $value) {
//	var_dump($key);
//	var_dump($value);
	if (strpos($data_demo_string, $key) !== false) {
		sneeit_demo_installer_ajax_error(__('Install Start: Your site was set up with wrong options and settings', 'sneeit'), $folder_path);
	}
}

// check memory available before decode
if (function_exists('memory_get_peak_usage') &&
	function_exists('ini_get') &&
	function_exists('intval')) {
	
	$sneeit_demo_installer_memory_peak = memory_get_peak_usage();
	$sneeit_demo_installer_memory_max = intval(ini_get('memory_limit')) * 1024 * 1024;
	$sneeit_demo_installer_memory_add = strlen($data_demo_string) * 10;

	if ($sneeit_demo_installer_memory_max <= $sneeit_demo_installer_memory_peak + $sneeit_demo_installer_memory_add) {
		sneeit_demo_installer_ajax_error(__('Install Start: hosting memory (RAM) is too low, please increase to at least memory_limit = ' . ((int) (2 * ($sneeit_demo_installer_memory_peak + $sneeit_demo_installer_memory_add) / 1014 / 1024)) . 'MB to install', 'sneeit'), $folder_path);
	}
}	

$data_demo_array = json_decode($data_demo_string);

if (!$data_demo_array) {
	sneeit_demo_installer_ajax_error(__('Install Start: can not decode demo data', 'sneeit'), $folder_path);
}


$data_demo_array = (array) $data_demo_array;
if (!isset($data_demo_array['database']) || 
	!isset($data_demo_array['mods']) ||
	!isset($data_demo_array['widgets'])){	
	sneeit_demo_installer_ajax_error(__('Install Start: extracted data structure is wrong', 'sneeit'), $folder_path);	
}


// extract database
$database_demo_array_with_prefix = $data_demo_array['database'];
if (!$database_demo_array_with_prefix) {
	sneeit_demo_installer_ajax_error(__('Install Start: can not decode demo database', 'sneeit'), $folder_path);	
}


// replace prefix database
$database_demo_array = array();
foreach ($database_demo_array_with_prefix as $table_name_with_prefix => $table_value) {
	$table_name = str_replace(SNEEIT_DEMO_INSTALLER_TABLE_PREFIX_PLACE_HOLDER, $table_prefix, $table_name_with_prefix);
	$table_value->query = str_replace(sneeit_backquote($table_name_with_prefix), sneeit_backquote($table_name), $table_value->query);
	$database_demo_array[$table_name] = $table_value;
}

// get current database
$database_current_array = array();
$database_current_tables = $wpdb->get_results("SHOW TABLES LIKE '%'");	
if (empty($database_current_tables)) {
	sneeit_demo_installer_ajax_error(__('Install Start: The database is empty or can not access', 'sneeit'), $folder_path);	
}

foreach ($database_current_tables as $table) {
	// read data for each table
	foreach ($table as $table_name) {
		// get data as array (we will use in future if have chance
		$database_current_array[$table_name] = true;		
	}		
}



/*
 * BACKUP IMPORTANT DATA FROM CURRENT DATABASE
 * 
 */
// backup current admin user
if (!current_user_can('manage_options')) {
	sneeit_demo_installer_ajax_error(__('Install Start: Current user has no permission to install demo', 'sneeit'), $folder_path);	
}
$admin_user_id = get_current_user_id();
$admin_user = get_user_by('ID', $admin_user_id);
$admin_user_login = $admin_user->user_login;
$admin_user_pass = $admin_user->user_pass;
$admin_user_email = $admin_user->user_email;

// current user_roles option
$wp_user_roles_option_name = $table_prefix.'user_roles';
$wp_user_roles_option_value = $wpdb->get_row(
		"SELECT option_value FROM $wpdb->options WHERE option_name = '$wp_user_roles_option_name'",
		ARRAY_N
	);
if (!empty($wp_user_roles_option_value) && $wp_user_roles_option_value[0]) {
	$wp_user_roles_option_value = $wp_user_roles_option_value[0];
} else {
	$wp_user_roles_option_value = '';
}

// current login session
$session_token_meta_value = $wpdb->get_row( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'session_tokens'", ARRAY_N );
if (!empty($session_token_meta_value) && $session_token_meta_value[0]) {
	$session_token_meta_value = $session_token_meta_value[0];
} else {
	$session_token_meta_value = '';
}

// current wp_capabilities
$wp_capabilities_meta_key = $table_prefix.'capabilities';
$wp_capabilities_meta_value = $wpdb->get_row( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = '$wp_capabilities_meta_key'", ARRAY_N );
if (!empty($wp_capabilities_meta_value) && $wp_capabilities_meta_value[0]) {
	$wp_capabilities_meta_value = $wp_capabilities_meta_value[0];
} else {
	$wp_capabilities_meta_value = '';
}

// rewrite rules
$rewrite_rules_option_value = $wpdb->get_row(
	"SELECT option_value FROM $wpdb->options WHERE option_name = 'rewrite_rules'",
	ARRAY_N
);
if (!empty($rewrite_rules_option_value) && $rewrite_rules_option_value[0]) {
	$rewrite_rules_option_value = $rewrite_rules_option_value[0];
} else {
	$rewrite_rules_option_value = '';
}

// write out database
foreach ($database_demo_array as $table_name => $table_value) {	
	if (!isset($database_current_array[$table_name]) && !isset($database_current_array[strtolower($table_name)])) {	
		// at this time, we will not need to create table
		// if some table is missing by not installing recommend plugins,
		// the user must install the plugins first to make full demo		
		continue;
		
		/*
		// replace collation, engine and charset of query before calling
		
		// now we can create new table		
		if (!$wpdb->query($table_value->query)) {			
			// do something when create table fail					
		}
		 */
	}
	
	// set up format string
	$format = array();
	foreach ($table_value->column_status as $column_id => $column_status_array) {	
	
		$column_status = $column_status_array[0];
		$column_data_type = strtolower($column_status->DATA_TYPE);
		
		switch ($column_data_type) {
			case 'decimal':
			case 'float':
			case 'double':
			case 'real':
				array_push($format, '%f');	
				break;
			
			case 'bit':
			case 'boolean':
			case 'serial':
				array_push($format, '%d');
				break;

			default:
				if (strpos($column_data_type, 'int') !== false) {
					array_push($format, '%d');
				} else {
					array_push($format, '%s');
				}
				break;
		}
	}
		
	// replace current database
	foreach ($table_value->data as $key => $value) {
		/*
		 * PREVENT OVERRIDE SOME IMPORTANT OPTIONS
		 */
		// prevent override theme mods
		if ($table_name == $table_prefix.'options' && 
			isset($value->option_name) && 
			strpos($value->option_name, 'theme_mods_') !== false && 
			strpos($value->option_name, 'theme_mods_') == 0) {
			continue;
		}
		
		// prevent override site widgets
		if ($table_name == $table_prefix.'options' && 
			isset($value->option_name) && 
			strpos($value->option_name, 'widget_') !== false && 
			strpos($value->option_name, 'widget_') == 0) {
			continue;
		}
	
		// Not need to write feed, just continue to prevent unexpect errors
		if ($table_name == $table_prefix.'options' && 
			isset($value->option_name) && 
			strpos($value->option_name, '_transient_feed_') !== false && 
			strpos($value->option_name, '_transient_feed_') == 0) {
			continue;
		}
		
		// prevent override user information
		if ($table_name == $table_prefix.'users' && 
			isset($value->ID)) {
			$current_id = (int) $value->ID;
			if (get_user_by('id', $current_id)) {
				// if found an already exist user,
				// just next, prevent override to avoid unexpected error
				continue;
			}			
		}
		
		
		
		// prevent override user capability
		if ($table_name == $table_prefix.'usermeta' && 
			isset($value->meta_key) && 
			isset($value->user_id) &&
			strpos($value->meta_key, '_capabilities') !== false && 
			strpos($value->meta_key, '_capabilities') != 0) {
			$value->meta_key = $table_prefix.'capabilities';
		
			if ($wp_capabilities_meta_value) {
				$value->meta_value = $wp_capabilities_meta_value;
			} else {
				$value->meta_value = 'a:1:{s:13:"administrator";b:1;}';
			}					
		}
		
		/*
		 * PRE-PROCESS SOME IMPORTANT OPTIONS
		 */
		// to prevent log out after write into database, don't replace session_tokens
		if ($table_name == $table_prefix.'usermeta' && 
			isset($value->meta_key) && 
			$value->meta_key == 'session_tokens') {
			if ($session_token_meta_value) {
				$value->meta_value = $session_token_meta_value;
			} else {
				continue;
			}	
		}
		
		// prevent override _user_roles data and prefix
		if ($table_name == $table_prefix.'options' && 
			isset($value->option_name) && 
			strpos($value->option_name, '_user_roles') !== false && 
			strpos($value->option_name, '_user_roles') != 0) {
			$value->option_name = $table_prefix.'user_roles';
			if ($wp_user_roles_option_value) {
				$value->option_value = $wp_user_roles_option_value;
			} else {
				$value->option_value = 'a:5:{s:13:"administrator";a:2:{s:4:"name";s:13:"Administrator";s:12:"capabilities";a:61:{s:13:"switch_themes";b:1;s:11:"edit_themes";b:1;s:16:"activate_plugins";b:1;s:12:"edit_plugins";b:1;s:10:"edit_users";b:1;s:10:"edit_files";b:1;s:14:"manage_options";b:1;s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:6:"import";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:8:"level_10";b:1;s:7:"level_9";b:1;s:7:"level_8";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;s:12:"delete_users";b:1;s:12:"create_users";b:1;s:17:"unfiltered_upload";b:1;s:14:"edit_dashboard";b:1;s:14:"update_plugins";b:1;s:14:"delete_plugins";b:1;s:15:"install_plugins";b:1;s:13:"update_themes";b:1;s:14:"install_themes";b:1;s:11:"update_core";b:1;s:10:"list_users";b:1;s:12:"remove_users";b:1;s:13:"promote_users";b:1;s:18:"edit_theme_options";b:1;s:13:"delete_themes";b:1;s:6:"export";b:1;}}s:6:"editor";a:2:{s:4:"name";s:6:"Editor";s:12:"capabilities";a:34:{s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;}}s:6:"author";a:2:{s:4:"name";s:6:"Author";s:12:"capabilities";a:10:{s:12:"upload_files";b:1;s:10:"edit_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:4:"read";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:12:"delete_posts";b:1;s:22:"delete_published_posts";b:1;}}s:11:"contributor";a:2:{s:4:"name";s:11:"Contributor";s:12:"capabilities";a:5:{s:10:"edit_posts";b:1;s:4:"read";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:12:"delete_posts";b:1;}}s:10:"subscriber";a:2:{s:4:"name";s:10:"Subscriber";s:12:"capabilities";a:2:{s:4:"read";b:1;s:7:"level_0";b:1;}}}';
			}	
		} // wp_user_roles
		

		// prevent override rewrite_rules data
		if (0 && $table_name == $table_prefix.'options' && 
			isset($value->option_name) && 
			'rewrite_rules' == $value->option_name) {
			if ($rewrite_rules_option_value) {
				$value->option_value = $rewrite_rules_option_value;
			} else {
				$value->option_value = 'a:86:{s:11:"^wp-json/?$";s:22:"index.php?rest_route=/";s:14:"^wp-json/(.*)?";s:33:"index.php?rest_route=/$matches[1]";s:47:"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$";s:52:"index.php?category_name=$matches[1]&feed=$matches[2]";s:42:"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$";s:52:"index.php?category_name=$matches[1]&feed=$matches[2]";s:23:"category/(.+?)/embed/?$";s:46:"index.php?category_name=$matches[1]&embed=true";s:35:"category/(.+?)/page/?([0-9]{1,})/?$";s:53:"index.php?category_name=$matches[1]&paged=$matches[2]";s:17:"category/(.+?)/?$";s:35:"index.php?category_name=$matches[1]";s:44:"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?tag=$matches[1]&feed=$matches[2]";s:39:"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?tag=$matches[1]&feed=$matches[2]";s:20:"tag/([^/]+)/embed/?$";s:36:"index.php?tag=$matches[1]&embed=true";s:32:"tag/([^/]+)/page/?([0-9]{1,})/?$";s:43:"index.php?tag=$matches[1]&paged=$matches[2]";s:14:"tag/([^/]+)/?$";s:25:"index.php?tag=$matches[1]";s:45:"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?post_format=$matches[1]&feed=$matches[2]";s:40:"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?post_format=$matches[1]&feed=$matches[2]";s:21:"type/([^/]+)/embed/?$";s:44:"index.php?post_format=$matches[1]&embed=true";s:33:"type/([^/]+)/page/?([0-9]{1,})/?$";s:51:"index.php?post_format=$matches[1]&paged=$matches[2]";s:15:"type/([^/]+)/?$";s:33:"index.php?post_format=$matches[1]";s:12:"robots\.txt$";s:18:"index.php?robots=1";s:48:".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\.php$";s:18:"index.php?feed=old";s:20:".*wp-app\.php(/.*)?$";s:19:"index.php?error=403";s:18:".*wp-register.php$";s:23:"index.php?register=true";s:32:"feed/(feed|rdf|rss|rss2|atom)/?$";s:27:"index.php?&feed=$matches[1]";s:27:"(feed|rdf|rss|rss2|atom)/?$";s:27:"index.php?&feed=$matches[1]";s:8:"embed/?$";s:21:"index.php?&embed=true";s:20:"page/?([0-9]{1,})/?$";s:28:"index.php?&paged=$matches[1]";s:27:"comment-page-([0-9]{1,})/?$";s:40:"index.php?&page_id=926&cpage=$matches[1]";s:41:"comments/feed/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?&feed=$matches[1]&withcomments=1";s:36:"comments/(feed|rdf|rss|rss2|atom)/?$";s:42:"index.php?&feed=$matches[1]&withcomments=1";s:17:"comments/embed/?$";s:21:"index.php?&embed=true";s:44:"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:40:"index.php?s=$matches[1]&feed=$matches[2]";s:39:"search/(.+)/(feed|rdf|rss|rss2|atom)/?$";s:40:"index.php?s=$matches[1]&feed=$matches[2]";s:20:"search/(.+)/embed/?$";s:34:"index.php?s=$matches[1]&embed=true";s:32:"search/(.+)/page/?([0-9]{1,})/?$";s:41:"index.php?s=$matches[1]&paged=$matches[2]";s:14:"search/(.+)/?$";s:23:"index.php?s=$matches[1]";s:47:"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?author_name=$matches[1]&feed=$matches[2]";s:42:"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:50:"index.php?author_name=$matches[1]&feed=$matches[2]";s:23:"author/([^/]+)/embed/?$";s:44:"index.php?author_name=$matches[1]&embed=true";s:35:"author/([^/]+)/page/?([0-9]{1,})/?$";s:51:"index.php?author_name=$matches[1]&paged=$matches[2]";s:17:"author/([^/]+)/?$";s:33:"index.php?author_name=$matches[1]";s:69:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$";s:80:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]";s:64:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$";s:80:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]";s:45:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$";s:74:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true";s:57:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$";s:81:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]";s:39:"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$";s:63:"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]";s:56:"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$";s:64:"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]";s:51:"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$";s:64:"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]";s:32:"([0-9]{4})/([0-9]{1,2})/embed/?$";s:58:"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true";s:44:"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$";s:65:"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]";s:26:"([0-9]{4})/([0-9]{1,2})/?$";s:47:"index.php?year=$matches[1]&monthnum=$matches[2]";s:43:"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?year=$matches[1]&feed=$matches[2]";s:38:"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?year=$matches[1]&feed=$matches[2]";s:19:"([0-9]{4})/embed/?$";s:37:"index.php?year=$matches[1]&embed=true";s:31:"([0-9]{4})/page/?([0-9]{1,})/?$";s:44:"index.php?year=$matches[1]&paged=$matches[2]";s:13:"([0-9]{4})/?$";s:26:"index.php?year=$matches[1]";s:27:".?.+?/attachment/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:37:".?.+?/attachment/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:57:".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:33:".?.+?/attachment/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:16:"(.?.+?)/embed/?$";s:41:"index.php?pagename=$matches[1]&embed=true";s:20:"(.?.+?)/trackback/?$";s:35:"index.php?pagename=$matches[1]&tb=1";s:40:"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$";s:47:"index.php?pagename=$matches[1]&feed=$matches[2]";s:35:"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$";s:47:"index.php?pagename=$matches[1]&feed=$matches[2]";s:28:"(.?.+?)/page/?([0-9]{1,})/?$";s:48:"index.php?pagename=$matches[1]&paged=$matches[2]";s:35:"(.?.+?)/comment-page-([0-9]{1,})/?$";s:48:"index.php?pagename=$matches[1]&cpage=$matches[2]";s:24:"(.?.+?)(?:/([0-9]+))?/?$";s:47:"index.php?pagename=$matches[1]&page=$matches[2]";s:27:"[^/]+/attachment/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:37:"[^/]+/attachment/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:57:"[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:"[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:52:"[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:33:"[^/]+/attachment/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";s:16:"([^/]+)/embed/?$";s:37:"index.php?name=$matches[1]&embed=true";s:20:"([^/]+)/trackback/?$";s:31:"index.php?name=$matches[1]&tb=1";s:40:"([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?name=$matches[1]&feed=$matches[2]";s:35:"([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:43:"index.php?name=$matches[1]&feed=$matches[2]";s:28:"([^/]+)/page/?([0-9]{1,})/?$";s:44:"index.php?name=$matches[1]&paged=$matches[2]";s:35:"([^/]+)/comment-page-([0-9]{1,})/?$";s:44:"index.php?name=$matches[1]&cpage=$matches[2]";s:24:"([^/]+)(?:/([0-9]+))?/?$";s:43:"index.php?name=$matches[1]&page=$matches[2]";s:16:"[^/]+/([^/]+)/?$";s:32:"index.php?attachment=$matches[1]";s:26:"[^/]+/([^/]+)/trackback/?$";s:37:"index.php?attachment=$matches[1]&tb=1";s:46:"[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:41:"[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$";s:49:"index.php?attachment=$matches[1]&feed=$matches[2]";s:41:"[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$";s:50:"index.php?attachment=$matches[1]&cpage=$matches[2]";s:22:"[^/]+/([^/]+)/embed/?$";s:43:"index.php?attachment=$matches[1]&embed=true";}';
			}
		} // rewrite_rules
		
		
		// other normal values		
		$value = (array) $value;		
		if (!($ret = $wpdb->replace($table_name, $value, $format))) {
			sneeit_demo_installer_ajax_error(sprintf(__('Install Start: Can not insert data into table: %s', 'sneeit'), $table_name), $folder_path);
		}		
	}
}

// override theme mods (must place after database write out to prevent override
remove_theme_mods();
if (is_array($data_demo_array['mods']) || is_object($data_demo_array['mods'])) {	
	foreach ($data_demo_array['mods'] as $key => $value) {
		// process and valid key before write
		if (is_numeric($key)) {
			$key = (int) $key;
		}

		if (is_object($value)) {
			$value = (array) $value;
		}

		// write mods
		set_theme_mod($key, $value);
	}
}


// override widget data
if (is_array($data_demo_array['widgets']) || is_object($data_demo_array['widgets'])) {
	foreach ($data_demo_array['widgets'] as $key => $value) {
		// process and valid key before write
		if (is_numeric($key)) {
			$key = (int) $key;
		}

		if (is_object($value)) {
			$value = (array) $value;		
		}	
		if (is_array($value)) {
			foreach ($value as $sub_key => $sub_value) {
				if (is_numeric($sub_key)) {
					unset($value[$sub_key]);
					$sub_key = (int) $sub_key;
					$value[$sub_key] = $sub_value;
				}

				if (is_object($sub_value)) {
					$value[$sub_key] = (array) $sub_value;
				}
			}
		}

		// write widgets
		delete_option($key);	
		update_option($key, $value);	
	}
}

// delete temporary DEMO folder
sneeit_delete_file($folder_path);

// reset admin account
wp_update_user(array(
	'ID' => $admin_user_id,
	'user_pass' => $admin_user_pass,
	'user_login' => $admin_user_login,
	'user_email' => $admin_user_email,
	'role' => 'administrator'
));
$admin_user->add_role('administrator');

echo json_encode(array('status'=>'done'));
