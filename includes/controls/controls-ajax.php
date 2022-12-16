<?php

function sneeit_controls_ajax() {
	
	if (!current_user_can('moderate_comments')) {
		die();
	}
	$sub_action = sneeit_get_server_request('sub_action');
	
	if (!$sub_action) {
		die();
	}
	
		
	switch ($sub_action) {
		// build actions
		case 'tags':
			
			echo json_encode(get_terms( array(
				'taxonomy' => 'post_tag',
				'hide_empty' => false,
				'fields' => 'id=>name',	
				'number' => 5000,
			) ));
			break;
		
		case 'categories':			
			
			echo json_encode(get_terms( array(
				'taxonomy' => 'category',
				'hide_empty' => false,
				'fields' => 'id=>name',	
				'number' => 5000,
			) ));
			break;
		
		case 'users':	
			$users = get_users( array(
//				'fields' => array('id', 'display_name')
			) );
			$users_for_json = array();
			foreach ($users as $user) {
				$users_for_json[$user->data->ID] = $user->data->display_name;
			}
			echo json_encode($users_for_json);
			break;
		
		default:
			break;
	}
	
	die();
}
if (is_admin()) :
	add_action( 'wp_ajax_nopriv_sneeit_controls', 'sneeit_controls_ajax');
	add_action( 'wp_ajax_sneeit_controls', 'sneeit_controls_ajax');
endif;// is_admin for ajax
