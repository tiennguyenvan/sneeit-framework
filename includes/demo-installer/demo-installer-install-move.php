<?php
/*
 * @param WP_Filesystem_Base $wp_filesystem
 */
global $sneeit_demo_installer_move_index;
global $sneeit_demo_installer_move_counter;
global $sneeit_demo_installer_move_file_length;
global $sneeit_demo_installer_move_fail_count;
$sneeit_demo_installer_move_counter = 0;

function sneeit_demo_installer_start_move_media_file (
	$wp_filesystem, 
	$folder_path, 
	$media_structure, 
	$current_upload_dir, 
	$depth
) {
	
	global $sneeit_demo_installer_move_index;
	global $sneeit_demo_installer_move_counter;
	global $sneeit_demo_installer_move_file_length;
	global $sneeit_demo_installer_move_fail_count;
	
	if ($depth == SNEEIT_DEMO_INSTALLER_MAX_MOVING_LOOP) {
		sneeit_demo_installer_ajax_error(__('Install Move: too depth level of media folder to create', 'sneeit'), $folder_path);
	}
	foreach ($media_structure as $item_id => $item_value) {
		$item_value_array = (array) $item_value;
		if (isset($item_value_array['name'])) { // this is a file. must moving it to the current upload dir
			// skip processed indexes
			if ($sneeit_demo_installer_move_counter < $sneeit_demo_installer_move_index) {
				$sneeit_demo_installer_move_counter++;
				continue;
			}
			
			$name = $item_value_array['name'];
			
			// start moving file
			if (!$wp_filesystem->move($folder_path.'/'.$name, $current_upload_dir.'/'.$name, true)) {
				
				// if fail, may be not found file, so we must search in sub folder
				for ($i = 0; $i < $sneeit_demo_installer_move_file_length; $i++){
					if ($wp_filesystem->move($folder_path.'/'.$i.'/'.$name, $current_upload_dir.'/'.$name, true)) {
						break;
					}
				}
				
				// if also not found in sub folder, increase fail count
				if ($i == $sneeit_demo_installer_move_file_length) {
					$sneeit_demo_installer_move_fail_count++;
					
					// if too much fail, we must raise error
					if ($sneeit_demo_installer_move_counter >= SNEEIT_DEMO_INSTALLER_MAX_MOVING_FILE_NUMBER && 
						$sneeit_demo_installer_move_counter / 10 <= $sneeit_demo_installer_move_fail_count) {
						sneeit_demo_installer_ajax_error(
							__('Install Move: Can not move media file', 'sneeit'), $folder_path);
					}					
				}
			}
			
			// couting number of processed file to split steps
			$sneeit_demo_installer_move_counter++;
			if ($sneeit_demo_installer_move_counter == SNEEIT_DEMO_INSTALLER_MAX_MOVING_FILE_NUMBER + $sneeit_demo_installer_move_index) {
				$sneeit_demo_installer_move_index += SNEEIT_DEMO_INSTALLER_MAX_MOVING_FILE_NUMBER;
				echo json_encode(array(
						'latest'=> $sneeit_demo_installer_move_index, 
						'fail' => $sneeit_demo_installer_move_fail_count
					)
				);
				die();
			}
		} else { // this is folder
			// create it if not exist
			if (!$wp_filesystem->is_dir($current_upload_dir.'/'.$item_id) && 
				!$wp_filesystem->mkdir($current_upload_dir.'/'.$item_id, 0777)) {
					sneeit_demo_installer_ajax_error(__('Install Move: can not create media folder', 'sneeit'), $folder_path);					
			}
			if (!empty($item_value) &&
				!sneeit_demo_installer_start_move_media_file(
					$wp_filesystem,
					$folder_path, 
					$item_value, 
					$current_upload_dir.'/'.$item_id, 
					$depth+1)) {
				sneeit_demo_installer_ajax_error(__('Install Move: can not move file to sub folder', 'sneeit'), $folder_path);
			}
		}
	}
	return true;
}

if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Install Move: Can not init file system', 'sneeit'));	
}

global $wp_filesystem;
if (!$wp_filesystem) {
	sneeit_demo_installer_ajax_error(__('Install Move: Can not access file system', 'sneeit'));	
}

// collect parameters
$sneeit_demo_installer_move_index = sneeit_get_server_request('latest');
$sneeit_demo_installer_move_fail_count = sneeit_get_server_request('fail');
$files = sneeit_get_server_request('files');
$folder = sneeit_get_server_request('folder');
$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER . '/'. $folder;
if (!$files || !is_array($files) || !$folder || !is_numeric($sneeit_demo_installer_move_index) || !is_numeric($sneeit_demo_installer_move_fail_count)) {
	sneeit_demo_installer_ajax_error(__('Install Move: Wrong submit parameters', 'sneeit'), $folder_path);
}
$sneeit_demo_installer_move_index = (int) $sneeit_demo_installer_move_index;
$sneeit_demo_installer_move_counter = 0;
$sneeit_demo_installer_move_file_length = count($files);

// read media structure file
$media_structure = $wp_filesystem->get_contents($folder_path.'/'.SNEEIT_DEMO_INSTALLER_MEDIA_STRUCTURE_FILE_NAME.'.txt');
if (!$media_structure) {
	sneeit_demo_installer_ajax_error(__('Install Move: missing media structure file', 'sneeit'), $folder_path);	
}
$media_structure = json_decode($media_structure);
if (!$media_structure) {
	sneeit_demo_installer_ajax_error(__('Install Move: can not decode media structure file', 'sneeit'), $folder_path);	
}

// moving files to upload folder
$current_upload_dir = wp_upload_dir();
$current_upload_dir = $current_upload_dir['basedir'];

sneeit_demo_installer_start_move_media_file($wp_filesystem, $folder_path, $media_structure, $current_upload_dir, 1);

echo json_encode(array('latest'=> 'done', 'fail'=> $sneeit_demo_installer_move_fail_count));