<?php
require_once 'user-meta-box-lib.php';
require_once 'user-meta-box-class.php';
require_once 'user-meta-box-enqueue.php';

function sneeit_user_meta_box_init_setup_user_meta_box($declaration) {
	$declaration = sneeit_validate_user_meta_box_declaration($declaration);			
	foreach ($declaration as $user_meta_box_id => $user_meta_box_declaration) {
		new Sneeit_User_Meta_Box($user_meta_box_id, $user_meta_box_declaration);
	}
}
add_action('sneeit_setup_user_meta_box', 'sneeit_user_meta_box_init_setup_user_meta_box', 1, 1);

