<?php


global $sneeit_demo_installer_num_files;
$sneeit_demo_installer_num_files = 0;
function sneeit_demo_installer_start_listing_media_file ($media_structure, $depth
) {	
	global $sneeit_demo_installer_num_files;
	
	if ($depth == SNEEIT_DEMO_INSTALLER_MAX_MOVING_LOOP) {
		sneeit_demo_installer_ajax_error(__('Install List: too depth level of media folder to list', 'sneeit'));
	}
	foreach ($media_structure as $item_id => $item_value) {
		$item_value_array = (array) $item_value;
		if (isset($item_value_array['name'])) { // this is a file. must moving it to the current upload dir
			$sneeit_demo_installer_num_files++;		
		} else { // this is folder			
			if (!empty($item_value) &&
				!sneeit_demo_installer_start_listing_media_file(					 
					$item_value, 					
					$depth+1)) {
				sneeit_demo_installer_ajax_error(__('Install List: can not list file in sub folder', 'sneeit'), $folder_path);
			}
		}
	}
	return true;
}

if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Install List: Can not init file system', 'sneeit'));	
}

global $wp_filesystem;
if (!$wp_filesystem) {
	sneeit_demo_installer_ajax_error(__('Install List: Can not access file system', 'sneeit'));	
}

// collect parameters
$folder = sneeit_get_server_request('folder');
$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER . '/'. $folder;
if (!$folder) {
	sneeit_demo_installer_ajax_error(__('Install List: Wrong submit parameters', 'sneeit'), $folder_path);
}

// read media structure file
$media_structure = $wp_filesystem->get_contents($folder_path.'/'.SNEEIT_DEMO_INSTALLER_MEDIA_STRUCTURE_FILE_NAME.'.txt');
if (!$media_structure) {
	sneeit_demo_installer_ajax_error(__('Install List: missing media structure file', 'sneeit'), $folder_path);	
}
$media_structure = json_decode($media_structure);
if (!$media_structure) {
	sneeit_demo_installer_ajax_error(__('Install List: can not decode media structure file', 'sneeit'), $folder_path);	
}

sneeit_demo_installer_start_listing_media_file($media_structure, 1);

echo json_encode(array('num'=> $sneeit_demo_installer_num_files));