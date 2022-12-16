<?php
if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Install Folder: Can not init file system', 'sneeit'));	
}

global $wp_filesystem;
if (!$wp_filesystem) {
	sneeit_demo_installer_ajax_error(__('Install Folder: Can not access file system', 'sneeit'));	
}

// create demo folder
if (!$wp_filesystem->is_dir(SNEEIT_DEMO_INSTALLER_FOLDER) && !$wp_filesystem->mkdir(SNEEIT_DEMO_INSTALLER_FOLDER, 0777)) {
	sneeit_demo_installer_ajax_error(__('Install Folder: Can not create main folder for saving built files', 'sneeit'));	
}

// collect main and sub demo folder
$folder_name = sneeit_get_server_request('folder');
if (!$folder_name) {
	sneeit_demo_installer_ajax_error(__('Install Folder: Can not find provide folder name', 'sneeit'));	
}
$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER . '/'. $folder_name;

// delete folder if it was already created before
if (!$wp_filesystem->is_dir($folder_path) && !$wp_filesystem->mkdir($folder_path, 0777)) {
	sneeit_demo_installer_ajax_error(__('Install Folder: Can not create ID folder for saving built files', 'sneeit'), SNEEIT_DEMO_INSTALLER_FOLDER);
}

echo json_encode(array('status'=>'done'));