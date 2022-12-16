<?php


if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Install Extract: Can not init file system', 'sneeit'));	
}

global $wp_filesystem;
if (!$wp_filesystem) {
	sneeit_demo_installer_ajax_error(__('Install Extract: Can not access file system', 'sneeit'));	
}

// collect parameters
$files = sneeit_get_server_request('files');
$folder = sneeit_get_server_request('folder');
$latest = sneeit_get_server_request('latest');
$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER . '/'. $folder;
if (!$files || !is_array($files) || !$folder || !is_numeric($latest)) {
	sneeit_demo_installer_ajax_error(__('Install Extract: Wrong submit parameters', 'sneeit'), $folder_path);
}

$latest = (int) $latest;


/* work around to fix the problem with some hostings which have 
 * ajax security to not allow access system folders
 * in this case is: /tmp/ folder,
 * this must be sync with demo-installer js file
 *  */
$sneeit_safe_forward_slash = 'sneeit-safe-forward-slash';
foreach ($files as $key => $file) {
	$files[$key] = str_replace($sneeit_safe_forward_slash, '/', $files[$key]);
}


if (!unzip_file($files[$latest], $folder_path)) {
	foreach ($files as $file_path) {
		sneeit_delete_file($file_path); // delete all temporary files
	}	
	sneeit_demo_installer_ajax_error(__('Install Extract: Can not extract demo file', 'sneeit'), $folder_path);	
}
if (!$wp_filesystem->is_dir($folder_path.'/'.$latest)) {
	$wp_filesystem->mkdir($folder_path.'/'.$latest, 0777);
}

if ($wp_filesystem->is_dir($folder_path.'/'.$latest) || $wp_filesystem->mkdir($folder_path.'/'.$latest, 0777)) {
	if (!unzip_file($files[$latest], $folder_path.'/'.$latest)) {
		sneeit_demo_installer_ajax_error(__('Install Extract: fail for sub folder', 'sneeit'), $folder_path);	
	}
}
sneeit_delete_file($files[$latest]); // delete temporary file


echo json_encode(array('latest'=> ($latest+1)));