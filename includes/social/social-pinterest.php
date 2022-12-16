<?php

// link format: https://www.pinterest.com/tvnguyen/
add_filter('sneeit_number_pinterest_followers', 'sneeit_get_social_count_number_pinterest_followers', 1, 1);
function sneeit_get_social_count_number_pinterest_followers($args) {
	if (is_string($args)) {		
		$args = array(
			'url' => $args
		);
	}
	
	if (!isset($args['name'])) {
		$args['name'] = 'pinterest';
	}
	if (!isset($args['filter'])) {
		$args['filter'] = array(
			array(
				'start_1' => '/followers/" data-reactid="',
				'start_2' => '<span data-reactid="',
				'start_3' => '"><span ',
				'start_4' => '>',
				'end_5' => '</span>'
			),
			array(
				'start_1' => '"follower_count": ',
				'end_2' => ', "'
			),
			array(
				'start_1' => '"pinterestapp:followers": "',
				'end_2' => '",'
			),
			array(
				'start_1' => '<meta property="pinterestapp:followers" name="pinterestapp:followers" content="',
				'end_2' => '"',
			),			
			array(
				'start_1' => 'FollowerCount',
				'start_2' => '<span',
				'start_3' => '>',
				'end_4' => '</span>'
			)
		);
	}	
	
	return sneeit_get_one_number_from_url($args);
}

