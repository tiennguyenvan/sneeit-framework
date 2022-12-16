<?php
if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Explore: Can not init file system', 'sneeit'));	
}

$home_path = get_home_path();
$home_url = get_home_url(null, '/');
$home_path = str_replace('\\', '/', $home_path); // in case win
$result_folders = sneeit_list_file_tree(SNEEIT_DEMO_INSTALLER_FOLDER);
$result_urls = array();
//var_dump(time());
//var_dump(current_time( 'timestamp' ));
// replace path to URL
foreach ($result_folders as $folder => $file_lists) {
	$name = $folder;
	if (is_numeric($folder) && strlen($folder) == 10) {	
		$name = sprintf(__('%s ago', 'sneeit'), human_time_diff((int) $folder, current_time('timestamp')));
	}
	$item_list = array(
		'name' => $name,
		'folder' => $folder,
		'files' => array()
	);
	if (is_array($file_lists)) {
		foreach ($file_lists as $file) {
			if (isset($file['path'])) {
				$item_link = str_replace('\\', '/',$file['path']);
				$item_link = str_replace($home_path, $home_url, $item_link);
				array_push($item_list['files'], array(
					'name' => $file['name'],
					'link' => $item_link
				));
			}			
		}
	}
	array_push($result_urls, $item_list);
}

echo json_encode($result_urls);