<?php
add_action( 'admin_print_footer_scripts', 'sneeit_post_meta_box_admin_enqueue_scripts', 2);
function sneeit_post_meta_box_admin_enqueue_scripts($hook) {
	wp_enqueue_style( 'sneeit-post-meta-box', SNEEIT_PLUGIN_URL_CSS . 'post-meta-box.css', array(), SNEEIT_PLUGIN_VERSION );
	wp_enqueue_script( 'sneeit-post-meta-box', SNEEIT_PLUGIN_URL_JS .'post-meta-box.js', array(), SNEEIT_PLUGIN_VERSION, true );	
}


