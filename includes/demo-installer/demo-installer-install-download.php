<?php


if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Install Download: Can not init file system', 'sneeit'));	
}

global $wp_filesystem;
if (!$wp_filesystem) {
	sneeit_demo_installer_ajax_error(__('Install Download: Can not access file system', 'sneeit'));	
}

// collect parameters
$links = sneeit_get_server_request('links');
$latest = sneeit_get_server_request('latest');
$folder = sneeit_get_server_request('folder');

if (!$links || !is_array($links) || !$folder || !is_numeric($latest)) {
	sneeit_demo_installer_ajax_error(__('Install Download: Wrong submit parameters', 'sneeit'), $folder_path);	
}
$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER . '/'. $folder;

$latest = (int) $latest;
// download file to temp
$file_path = download_url($links[$latest]);
if (is_wp_error($file_path)) {
	sneeit_demo_installer_ajax_error(__('Install Download: Can not download your demo files', 'sneeit'), $folder_path);	
}
echo json_encode(array('latest'=> ($latest+1), 'file'=> $file_path));