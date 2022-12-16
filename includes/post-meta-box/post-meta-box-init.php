<?php

global $Sneeit_Post_Meta_Box_Declaration; $Sneeit_Post_Meta_Box_Declaration = array();

require_once 'post-meta-box-lib.php';
require_once 'post-meta-box-class.php';


function sneeit_post_meta_box_init_setup_post_meta_box($declaration = array()) {
	global $Sneeit_Post_Meta_Box_Declaration;
	$Sneeit_Post_Meta_Box_Declaration = sneeit_validate_post_meta_box_declaration($declaration);
}
add_action('sneeit_setup_post_meta_box', 'sneeit_post_meta_box_init_setup_post_meta_box', 1, 1);

/**
 * Calls the class on the post edit screen.
 */
function sneeit_load_meta_box() {
	global $Sneeit_Post_Meta_Box_Declaration;
	require_once 'post-meta-box-enqueue.php';
	
	foreach ($Sneeit_Post_Meta_Box_Declaration as $post_meta_box_id => $post_meta_box_declaration) {
		new Sneeit_Post_Meta_Box($post_meta_box_id, $post_meta_box_declaration);
	}    
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'sneeit_load_meta_box');
    add_action( 'load-post-new.php', 'sneeit_load_meta_box');
}
