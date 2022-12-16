<?php
/* BUILD =========================================================================================================
 * client: send "build_database"
 * server: create demo folder, get database, replace domain by place HOLDER, write down as zip file
 *         success: return 'folder: name of demo folder'
 *         fail: return 'error: error detail here'
 * 
 * client: send "build_list_files"
 * server: list all folder and file in wp-content/uploads/
 *         success: return 'array_of_file_list'
 *         fail: return 'error: error detail here'
 * 
 * client: send "build_files" attach array_of_file_list and latest processed file id
 * server: continue read the files in the list, check file size, add to compress list if total size not over than 4M
 *         success: return 'latest: the latest processed in file list'
 *         fail: return 'error: error detail here'
 * 
 * client: repeat until done the file list, then send: "explore" attach the folder id (name of demo folder)
 * server: read the demo folder, list all the file and send back the list of .zip file
 * 
 * client: show up the list of the files and recommend integrate code. Allow reload or build again
 */

/* INSTALL =========================================================================================================
 * client: send "install_folder", attach folder name
 * server: create a demo folder
 *         success: return {status: 'done'}
 *
 * client: send "install_download" attach folder, a list file and latest id, repeat until download all files
 * server: download file, copy to demo folder, remove the temporary file
 *         success: return {latest: 'latest id', file: 'downloaded_file_path'}
 *
 * client: send "install_move" attach array_of_downloaded_file + Index
 * server: extra all files. Depend media structure, create folder and copy all files
 *         success: return {status:'done'}
 *
 * client: send "install_start" attach database file
 * server: Write database. 
 *         success: return {status:'done'}
 *
 * client: send "detele" attach folder name
 * server: delete the folder
 *         success: return {status:'done'}
 * 
 * client: show done
 */

// https://wordpress.org/support/topic/display-mysql-table-data-in-a-wp-page?replies=26
// https://stackoverflow.com/questions/5439165/wordpress-retrieve-database-table-names-and-print-them-out
// https://ottopress.com/2011/tutorial-using-the-wp_filesystem/
// https://codex.wordpress.org/Filesystem_API
// https://codex.wordpress.org/Function_Reference/download_url
function sneeit_demo_installer_callback() {	
	if (!current_user_can('manage_options')) {
		die();
	}
	$sub_action = sneeit_get_server_request('sub_action');	
	if (!$sub_action) {
		die();
	}
	
	require_once 'demo-installer-lib.php';
	
	switch ($sub_action) {
		// build actions
		case 'build_database':
			require_once 'demo-installer-build-database.php';
			break;
		
		case 'build_list_files':
			require_once 'demo-installer-build-list-files.php';
			break;
		
		case 'build_files':
			require_once 'demo-installer-build-files.php';
			break;
		
		// explore file
		case 'explore':
			require_once 'demo-installer-explore.php';
			break;
		
		case 'delete':
			require_once 'demo-installer-delete.php';
			break;
		
		// install
		case 'install_folder':
			require_once 'demo-installer-install-folder.php';
			break;
		
		case 'install_download':
			require_once 'demo-installer-install-download.php';
			break;
		
		case 'install_extract':
			require_once 'demo-installer-install-extract.php';
			break;
		
		case 'install_list':
			require_once 'demo-installer-install-list.php';
			break;
			
		case 'install_move':
			require_once 'demo-installer-install-move.php';
			break;
				
		case 'install_start':
			require_once 'demo-installer-install-start.php';
			break;
	}
	die();
}
if (is_admin()) :
	add_action( 'wp_ajax_nopriv_sneeit_demo_installer', 'sneeit_demo_installer_callback' );
	add_action( 'wp_ajax_sneeit_demo_installer', 'sneeit_demo_installer_callback' );
endif;// is_admin for ajax
