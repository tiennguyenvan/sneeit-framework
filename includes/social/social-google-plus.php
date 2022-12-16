<?php

// link format: https://plus.google.com/u/0/+TienNguyenPlus
add_filter('sneeit_number_google_plus_followers', 'sneeit_get_social_count_number_google_plus_followers', 1, 1);
function sneeit_get_social_count_number_google_plus_followers($args) {
	/* if page, the URL must be like: https://plus.google.com/102946791258279958344
	 * which has no /u/ /0/
	 * 
	 * The profile can be anything
	 */
	if (is_string($args)) {
		$args = array(
			'url' => $args
		);
	}
	if (!isset($args['name'])) {
		$args['name'] = 'google';
	}	
	
	$args['filter'] = array(
		array(
			'start_1' => 'id="contentPane',
			'start_2' => 'BOfSxb',
			'end_3' => '</span>'
		),
		array(
			'start_1' => '<div class="C98T8d',
			'start_2' => '">',
			'end_3' => '<'
		),
	);
	
	return sneeit_get_one_number_from_url($args);
}
