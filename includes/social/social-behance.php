<?php

// link format: https://www.behance.net/tiennguyenvan
add_filter('sneeit_number_behance_followers', 'sneeit_get_social_count_number_behance_followers', 1, 1);
function sneeit_get_social_count_number_behance_followers($args) {
	if (is_string($args)) {		
		$args = array(
			'url' => $args
		);
	}
	if (!isset($args['name'])) {
		$args['name'] = 'behance';
	}
	if (!isset($args['filter'])) {
		$args['filter'] = array(
			array(
				'start_1' => 'js-followers-count',
				'end_2' => '</a>'
			)			
		);
	}
	// Behance require empty user agent at beginning to response right HTML
	// if not, it still response but with a wasted HTML	
	return sneeit_get_one_number_from_url($args);
}

