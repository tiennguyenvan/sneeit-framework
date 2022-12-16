<?php
global $Sneeit_View_Counter_Declaration;
$Sneeit_View_Counter_Declaration = array();
add_action('sneeit_support_view_counter', 'sneeit_utilities_support_view_counter', 1);
function sneeit_utilities_support_view_counter($declaration = array()) {
	
	if (empty($_SERVER['REMOTE_ADDR'])) {
		return;
	}
	if (empty($_SERVER['REQUEST_TIME_FLOAT'])) {
		return;
	}
	
	global $Sneeit_View_Counter_Declaration;
	$Sneeit_View_Counter_Declaration = wp_parse_args($declaration, array(
		/* the post meta key will be used to save the view count 
		 * default: post-view-count
		 */
		'article_view_count_meta_key' => SNEEIT_KEY_POST_VIEW_COUNT,
	));
	
	add_filter('the_content', 'sneeit_utilities_view_save');
}

function sneeit_utilities_view_save($content) {
	
	if (is_single()) {		
		// get user data
		$post_id = get_the_ID();
		$server_time = $_SERVER['REQUEST_TIME_FLOAT'];
				
		// check if user already read this post
		$views = get_post_meta($post_id, SNEEIT_KEY_POST_VIEWS, true);
		
		if (!is_array($views)) {
			$views = array();
		}
		if (!in_array($_SERVER['REMOTE_ADDR'], $views)) {			
			// if not, we need to update view objects
			$views[$server_time] = $_SERVER['REMOTE_ADDR'];
			update_post_meta($post_id, SNEEIT_KEY_POST_VIEWS, $views);
			
			// and also the post view count
			global $Sneeit_View_Counter_Declaration;
			update_post_meta($post_id, $Sneeit_View_Counter_Declaration['article_view_count_meta_key'], count($views));
		}
	}
	
	
	return $content;
}
