<?php
if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Delete: Can not init file system', 'sneeit'));	
}
global $wp_filesystem;
if (!$wp_filesystem) {
	sneeit_demo_installer_ajax_error(__('Delete: Can not access file system', 'sneeit'));	
}

$folder = sneeit_get_server_request('folder');
if (!$folder) {
	sneeit_demo_installer_ajax_error(__('Delete: wrong request folder name', 'sneeit'));
}

$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER.'/'.$folder;
if (!$wp_filesystem->is_dir($folder_path)) {
	sneeit_demo_installer_ajax_error(__('Delete: not found the request folder', 'sneeit'));
}

if (!($folder_path)) {
	sneeit_demo_installer_ajax_error(__('Delete: can not delete the request folder', 'sneeit'));
}

if (!sneeit_delete_file($folder_path)) {
	sneeit_demo_installer_ajax_error(__('Delete: can not delete the request folder', 'sneeit'));
}

echo json_encode(array('status' => 'done'));