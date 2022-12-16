<?php
function sneeit_init_file_system($actionurl = '', $action = -1, $name = '_wpnonce') {
	if (!$actionurl) {
		$actionurl = $_SERVER['REQUEST_URI'];
	}
	// okay, let's see about getting credentials
	$url = wp_nonce_url($actionurl,$action, $name);
	$method = '';
	if (false === ($creds = request_filesystem_credentials($url, $method, false, false, null) ) ) {
		$method = 'ftp';
		if (false === ($creds = request_filesystem_credentials($url, $method, false, false, null) ) ) {
			// if we get here, then we don't have credentials yet,
			// but have just produced a form for the user to fill in,
			// so stop processing for now
			return false; // stop the normal page form from displaying
		}
	}
	
	// now we have some credentials, try to get the wp_filesystem running
	if ( ! WP_Filesystem($creds) ) {
		// our credentials were no good, ask the user for them again
		request_filesystem_credentials($url, $method, true, false, null);
		return false;
	}
	
	return true;
}

function sneeit_write_file($full_file_path, $content) {
	global $wp_filesystem;
	if (!$wp_filesystem) {
		return false;
	}
	return $wp_filesystem->put_contents($full_file_path,	$content, FS_CHMOD_FILE);
}

function sneeit_zip_file($zip_file_full_path, $orignal_file_full_path, $inside_zip_file_path = null) {
	if (!class_exists('ZipArchive')) {
		return false;
	}
	
	$zip = new ZipArchive();
	
	if ($zip->open($zip_file_full_path, ZipArchive::CREATE) === TRUE) {
		if (!$zip->addFile($orignal_file_full_path, $inside_zip_file_path)) {
			echo 'CAN NOT ADD FILE';
			$zip->close();
			return false;
		}
		if (!$zip->close()) {
			echo 'CAN NOT CREATE';
			return false;
		}
	} else {
		echo 'CAN NOT OPEN';
		return false;
	}
	
	return true;
}


function sneeit_delete_file($full_file_path) {
	global $wp_filesystem;
	if (!$wp_filesystem) {
		return false;
	}
	if ($wp_filesystem->is_dir($full_file_path)) {
		return $wp_filesystem->delete($full_file_path, true);
	} else {
		return $wp_filesystem->delete($full_file_path);
	}
	
	
}
function sneeit_list_file_tree($folder_path, $max_depth = 3, $current_tree = array(), $current_depth = 0) {
	if ($current_depth == $max_depth || $current_depth == 10) {
		return $current_tree;
	}
	
	global $wp_filesystem;
	
	$dirlist = $wp_filesystem->dirlist($folder_path);
	if (!$dirlist) {
		return $current_tree;
	}
	foreach ($dirlist as $item) {
		if ($item['type'] == 'd') {
			$current_tree[$item['name']] = sneeit_list_file_tree(
											$folder_path.'/'.$item['name'], 
											$max_depth, 
											array(), 
											$current_depth+1);
		} else {
			array_push($current_tree, array(
				'name' => $item['name'],
				'path' => $folder_path . '/' .$item['name'], 
				'size' => $item['size'])
			);
		}
	}
	return $current_tree;
}

function sneeit_list_file_array($folder_path, $max_depth = 3, $current_array = array(), $current_depth = 0) {
	if ($current_depth == $max_depth || $current_depth == 10) {
		return $current_array;
	}
	
	global $wp_filesystem;
	
	$dirlist = $wp_filesystem->dirlist($folder_path);
	if (!$dirlist) {
		return $current_array;
	}
	foreach ($dirlist as $item) {
		if ($item['type'] == 'd') {				
			$folder_file_array = sneeit_list_file_array(
							$folder_path.'/'.$item['name'], 
							$max_depth, 
							array(), 
							$current_depth+1);
			$current_array = wp_parse_args($folder_file_array, $current_array);			
		} else {		
			array_push($current_array, array(
				'name' => $item['name'],
				'path' => $folder_path . '/' .$item['name'], 
				'size' => $item['size'])
			);			
		}
	}
	return $current_array;
}

