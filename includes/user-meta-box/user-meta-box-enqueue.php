<?php
function sneeit_user_meta_box_admin_enqueue_scripts ($hook) {
	if ('user-new.php' != $hook && 'user-edit.php' != $hook && 'profile.php' != $hook) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'sneeit-font-awesome' );
	wp_enqueue_style('sneeit-font-awesome-shims');
	wp_enqueue_style( 'sneeit-user-meta-box', SNEEIT_PLUGIN_URL_CSS . 'user-meta-box.css', array(), SNEEIT_PLUGIN_VERSION );
    wp_enqueue_script( 'sneeit-user-meta-box', SNEEIT_PLUGIN_URL_JS .'user-meta-box.js', array( 'wp-color-picker' ), SNEEIT_PLUGIN_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'sneeit_user_meta_box_admin_enqueue_scripts', 10, 1);

