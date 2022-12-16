<?php
// delay seconds to prevent similar file name
sleep(1);

// check if ZIP application is available or not
if (!class_exists('ZipArchive')) {
	sneeit_demo_installer_ajax_error(__('Build media files: Your server has no ZIP application to compress a file', 'sneeit'));	
}

if (!sneeit_init_file_system()) {
	sneeit_demo_installer_ajax_error(__('Build media files: Can not init file system', 'sneeit'));	
}

global $wp_filesystem;
if (!$wp_filesystem) {
	sneeit_demo_installer_ajax_error(__('Build media files: Can not access file system', 'sneeit'));	
}

$files = sneeit_get_server_request('files');
$latest = sneeit_get_server_request('latest');
$folder = sneeit_get_server_request('folder');

if (!$files || !$folder || !is_numeric($latest)) {
	sneeit_demo_installer_ajax_error(__('Build media files: Wrong submit parameters', 'sneeit'));	
}

// extract file list from json
$files = json_decode(stripslashes($files));
if (!$files) {
	sneeit_demo_installer_ajax_error(__('Build media files: Wrong file list format', 'sneeit'));	
}

// init zip file
$zip = new ZipArchive();
$folder_path = SNEEIT_DEMO_INSTALLER_FOLDER.'/'.$folder;
$zip_file_full_path = $folder_path.'/'.SNEEIT_DEMO_INSTALLER_MEDIA_FILES_FILE_NAME.'-'.time().'.gz';
if ($zip->open($zip_file_full_path, ZipArchive::CREATE) !== TRUE) {
	sneeit_demo_installer_ajax_error(__('Build media files: Can not open zip file', 'sneeit'));	
}

// scan and add file to zip file
$latest = (int) $latest;
$total = 0;
for ($i = $latest; $i < count($files); $i++ ) {
//	$size = (int) $files[$i]->size;
	$name = $files[$i]->name;
	$path = $files[$i]->path;
	$size = $wp_filesystem->size($path);
//	var_dump('EACH SCAN');
//	var_dump($total);
//	var_dump((int) $files[$i]->size);
//	var_dump($size);
	
	if ($total + $size > SNEEIT_DEMO_INSTALLER_MAX_FILE_SIZE) {
		if ($i == $latest) { // only single file can larger than the limitation
			if (!$zip->addFile($path, $name)) {
				sneeit_demo_installer_ajax_error(__('Build media files: Can not add files to zip file', 'sneeit'));	
			}
			$i++;
		}
		break;
	}
	
	if (!$zip->addFile($path, $name)) {
		sneeit_demo_installer_ajax_error(__('Build media files: Can not add files to zip file', 'sneeit'));	
	}
	$total += $size;
}
if (!$zip->close()) {
	sneeit_demo_installer_ajax_error(__('Build media files: Can not create zip file', 'sneeit'));	
}

echo json_encode(array('latest' => $i));

