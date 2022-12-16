<?php
// link format: https://twitter.com/tiennguyentweet
add_filter('sneeit_number_twitter_followers', 'sneeit_get_social_count_number_twitter_followers' , 1, 1);
function sneeit_get_social_count_number_twitter_followers ($args) {
	if (is_string($args)) {		
		$args = array(
			'url' => $args
		);
	}
	if (!isset($args['name'])) {
		$args['name'] = 'twitter';
	}
	if (!isset($args['filter'])) {
		$args['filter'] = array(
			array(
				'start_1' => 'ProfileNav-item--followers',
				'start_2' => 'title',
				'end_3' => '>'
			)			
		);
	}
	
	return sneeit_get_one_number_from_url($args);
}
