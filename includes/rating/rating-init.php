<?php
include_once 'rating-lib.php';

add_action('sneeit_review_system', 'sneeit_review_system');
global $Sneeit_Rating_Declaration;
function sneeit_review_system($declaration) {
	
	// Validate data
	if (!isset($declaration['display'])) {
		return;
	}
	
	if (!isset($declaration['display']['hook'])) {
		$declaration['display']['hook'] = 'the_content';
	}
	if (!isset($declaration['id'])) {
		$declaration['id'] = 'post-review';
	}
	if (!isset($declaration['type'])) {
		$declaration['type'] = array('star', 'point', 'percent');
	}
	if (!$declaration['post_type']) {
		$declaration['post_type'] = array('post', 'page');
	}
	if (!isset($declaration['title'])) {
		$declaration['title'] = sneeit_slug_to_title($declaration['id']);
	}
	if (!isset($declaration['context'])) {
		$declaration['context'] = 'advanced';
	} else {
		$declaration['context'] = strtolower($declaration['context']);
		if ($declaration['context'] != 'advanced' &&
			$declaration['context'] != 'side' && 
			$declaration['context'] != 'normal') {
			$declaration['context'] = 'advanced';
		}
	}
	if (!isset($declaration['support'])) {
		$declaration['support'] = array(
			'summary', 'conclusion', 'visitor'
		);
	}
	
	// validate priority value
	if (!isset($declaration['priority'])) {
		$declaration['priority'] = 'default';
	}
	
	// take action
	global $Sneeit_Rating_Declaration;
	$Sneeit_Rating_Declaration = $declaration;
	include_once 'rating-ajax.php';
	if (is_admin()) {		
		include_once 'rating-meta-box.php';
	} elseif (isset($declaration['display']['callback'])) {
		include_once 'rating-display.php';
	}
}
