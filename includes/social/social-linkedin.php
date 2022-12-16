<?php

// link format: https://vn.linkedin.com/in/tien-nguyen-van-4982736b
// ******* REQUIRE PHP 5.5+ **********
add_filter('sneeit_number_linkedin_connections', 'sneeit_get_social_count_number_linkedin_connections', 1);
function sneeit_get_social_count_number_linkedin_connections($args) {
	if (is_string($args)) {		
		$args = array(
			'url' => $args
		);
	}
	if (!isset($args['name'])) {
		$args['name'] = 'linkedin';
	}
	if (!isset($args['filter'])) {
		$args['filter'] = array(
			array(
				'start_1' => 'member-connections',
				'start_2' => '<strong>',
				'end_3' => '</strong>'
			)
		);
	}
	
	return sneeit_get_one_number_from_url($args);
}
