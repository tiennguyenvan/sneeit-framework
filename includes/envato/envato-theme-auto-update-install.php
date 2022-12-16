<?php

// blank page for updating
add_action('admin_menu', 'sneeit_envato_theme_auto_update_add_page');
function sneeit_envato_theme_auto_update_add_page() {
	if (!is_admin() || !current_user_can('manage_options')) {
		return;
	}
	
    add_submenu_page(
        null,
        '',
        '',
        'manage_options',
        SNEEIT_ENVATO_THEME_AUTO_UPDATE,
        'sneeit_envato_theme_auto_update_ui'
	);
}

// UI manager for update process
function sneeit_envato_theme_auto_update_ui() {
	if ( ! isset( $_GET['theme'] ) || 
		 ! $_GET['theme'] || 
		 ! isset( $_GET['item_id'] ) || 
		 ! $_GET['item_id'] || 
		 ! isset( $_GET['version'] ) || 
		 ! $_GET['version'] ) {
		_e( 'Page arguments are wrong!', 'sneeit' );
		return;
	}
?>
<div class="wrap">
    <h1><?php _e( 'Preparing Update Envato Theme', 'sneeit' ); ?></h1>
    <div id="<?php echo esc_attr(SNEEIT_ENVATO_THEME_AUTO_UPDATE); ?>">
		<br class="clear">
		<div class="content"></div>		
        <br class="clear">
    </div>
</div>

<?php
}


// main purpose is for backing up language files of current update theme (any updating theme)
add_filter( 'upgrader_pre_install', 'sneeit_envato_theme_auto_update_upgrader_pre_install', 10, 2 );
function sneeit_envato_theme_auto_update_upgrader_pre_install( $res = true, $hook_extra = array() ) {
	
	global $wp_filesystem;
	
	if ( ! $wp_filesystem ) { 		
		return;
	}
	
	// check if theme has /languages/ folder 
	$copy_folder = get_template_directory() . '/languages/';
	if ( ! $wp_filesystem->is_dir( $copy_folder ) ) {		
		return;
	}
	
	$upload_dir = wp_upload_dir();
	$dest_folder = $upload_dir['basedir'] . '/' . SNEEIT_ENVATO_THEME_AUTO_UPDATE . '/';
	
	// create temp folder if not exist
	if ( ! $wp_filesystem->is_dir($dest_folder) && 
		 ! $wp_filesystem->mkdir($dest_folder, 0777 ) ) {
		return;
	}
	
	// list and copy files to temp folder
	$files = array_keys( $wp_filesystem->dirlist( $copy_folder ) );
	foreach ( $files as $file_name ) {
		$wp_filesystem->copy( $copy_folder . $file_name, $dest_folder . $file_name, true, FS_CHMOD_FILE );
	}
	
	return $res;
}


add_filter( 'upgrader_post_install', 'sneeit_envato_theme_auto_update_upgrader_post_install', 10, 3 );
function sneeit_envato_theme_auto_update_upgrader_post_install( $res = true, $hook_extra = array(), $result = array() ) {
	
	global $wp_filesystem;
	
	if ( ! $wp_filesystem ) { 
		return;
	}
	
	// check if theme has /languages/ folder 
	$upload_dir = wp_upload_dir();
	$dest_folder = get_template_directory() . '/languages/';
	if ( ! $wp_filesystem->is_dir( $dest_folder ) ) {
		return;
	}
	
	// check if temp folder already exit
	$copy_folder = $upload_dir['basedir'] . '/' . SNEEIT_ENVATO_THEME_AUTO_UPDATE . '/';
	if ( ! $wp_filesystem->is_dir( $copy_folder ) ) {
		return;
	}
	
	// delete theme file (if it's on local host)
	$wp_filesystem->delete($copy_folder . SNEEIT_ENVATO_THEME_AUTO_UPDATE . '.zip');
	
	// restore files
	$files = array_keys( $wp_filesystem->dirlist( $copy_folder ) );
	foreach ( $files as $file_name ) {
		if (strpos($file_name, SNEEIT_ENVATO_THEME_AUTO_UPDATE) !== false) {
			continue;
		}
		$wp_filesystem->copy( $copy_folder . $file_name, $dest_folder . $file_name, false, FS_CHMOD_FILE );
	}
		
	// delete temp folder files
	$wp_filesystem->delete( $copy_folder, true );

	return $res;
}