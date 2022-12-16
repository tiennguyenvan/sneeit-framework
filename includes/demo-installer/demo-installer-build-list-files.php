<?php

if (!class_exists('ZipArchive')) {
	sneeit_demo_installer_ajax_error(__('Build list media files: Your server has no ZIP application to compress a file', 'sneeit'));	
}

if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Build list media files: Can not init file system', 'sneeit'));	
}

// collect main and sub demo folder
$folder_name = sneeit_get_server_request('folder');
if (!$folder_name) {
	sneeit_demo_installer_ajax_error(__('Build list media files: Can not find provide folder name', 'sneeit'));	
}
$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER . '/'. $folder_name;

// list structure of files
$demo_media_tree = sneeit_list_file_tree(WP_CONTENT_DIR.'/uploads');
if (!$demo_media_tree) {
	sneeit_demo_installer_ajax_error(__('Build list media files: can not list structure of upload folders', 'sneeit'));	
}

// list array of files
$demo_media_array = sneeit_list_file_array(WP_CONTENT_DIR.'/uploads');
if (!$demo_media_array) { // not allow empty media
	sneeit_demo_installer_ajax_error(__('Build list media files: can not list array of upload files', 'sneeit'));	
}

// convert to a string of json
$demo_media_tree_str = json_encode($demo_media_tree);	
if (!$demo_media_tree_str) { // not allow empty media
	sneeit_demo_installer_ajax_error(__('Build list media files: media files are too many to get and build', 'sneeit'));
}

// write down database to txt
if (!sneeit_write_file($folder_path.'/'.SNEEIT_DEMO_INSTALLER_MEDIA_STRUCTURE_FILE_NAME.'.txt', $demo_media_tree_str)) {			
	sneeit_demo_installer_ajax_error(__('Build list media files: Can not create media structure file', 'sneeit'));
}

// zip file
if (!sneeit_zip_file($folder_path.'/'.SNEEIT_DEMO_INSTALLER_MEDIA_STRUCTURE_FILE_NAME.'-'.time().'.gz', 
					$folder_path.'/'.SNEEIT_DEMO_INSTALLER_MEDIA_STRUCTURE_FILE_NAME.'.txt', 
					SNEEIT_DEMO_INSTALLER_MEDIA_STRUCTURE_FILE_NAME.'.txt')) {
	sneeit_demo_installer_ajax_error(__('Build list media files: ZIP application can not zip media structure file', 'sneeit'));
}

// remove the text file
sneeit_delete_file($folder_path.'/'.SNEEIT_DEMO_INSTALLER_MEDIA_STRUCTURE_FILE_NAME.'.txt');

echo json_encode($demo_media_array);