<?php
function sneeit_rating_user_rating_callback() {
	$target_value = sneeit_get_server_request('value');
	$post_id = sneeit_get_server_request('id');
	
	if (!$post_id) {die();}
	if (!is_numeric($target_value)) {die();}
	
	global $Sneeit_Rating_Declaration;
	$id = $Sneeit_Rating_Declaration['id'];
	$display = $Sneeit_Rating_Declaration['display'];
	
	if (!isset($display['text_n_user_vote_x_score'])) {
		$display['text_n_user_vote_x_score'] = esc_html__('%1$s user %2$s x %3$s', 'sneeit');
	}
	if (!isset($display['text_vote'])) {
		$display['text_vote'] = esc_html__('vote', 'sneeit');
	}
	if (!isset($display['text_votes'])) {
		$display['text_votes'] = esc_html__('votes', 'sneeit');
	}
		
	$target_value = (int) $target_value;
	$post_review = get_post_meta($post_id, $id, true);
	if (is_array($post_review) && !empty($post_review['type']) && is_array($post_review[$post_review['type']])) {
		$post_review_allow_visitor = (!empty($post_review['support-visitor-review']) && 
										in_array('visitor', $Sneeit_Rating_Declaration['support']));
		if (!$post_review_allow_visitor) {die();}
		
		$post_review_type = $post_review['type'];
		$post_review_items = $post_review[$post_review_type];
		$post_review_author_total_score = 0;
		$post_review_author_total_item = 0;
		
		// valid target value
		if ('star' == $post_review_type && $target_value > 5) {			
			$target_value = 5;
		} elseif ('star' != $post_review_type && $target_value > 10) {
			$target_value = 10;		
		}
		
		if ($target_value < 0) {
			$target_value = 0;
		}
			
		

		// calculate average score of author rating
		foreach ($post_review_items as $item) {		
			if (isset($item['value']) && is_numeric($item['value'])) {
				$post_review_author_total_item++;
				$post_review_author_total_score += (int) $item['value'];
			}
		}
		$post_review_author_average_score = 0;
		if ($post_review_author_total_item) {
			$post_review_author_average_score = $post_review_author_total_score / $post_review_author_total_item;
		}


		// calculate total average score
		$post_review_user = get_post_meta($post_id, $id.'-user-'.$post_review_type, true);
		if (!$post_review_user) {
			$post_review_user = array('count' => 0, 'total' => 0);
		}

		$post_review_user['count']++;
		$post_review_user['total'] += $target_value;

		$post_review_total_item = $post_review_user['count'] + 1;
		$post_review_total_score = $post_review_user['total'] + $post_review_author_average_score;
		$post_review_average_score = $post_review_total_score / $post_review_total_item;
		$post_review_average_scale_score = $post_review_average_score;
		$post_review_user_average_score = 0;
		if ($post_review_user['count']) {
			$post_review_user_average_score = $post_review_user['total'] / $post_review_user['count'];
		}

		// change to 0-100 ratio
		$post_review_author_average_scale_score = $post_review_author_average_score;
		if ('star' == $post_review_type ) {
			$post_review_average_scale_score = $post_review_average_score * 100 / 5;
			$post_review_author_average_scale_score = $post_review_author_average_score * 100 / 5;
			$post_review_user_average_scale_score = $post_review_user_average_scale_score * 100 / 5;
		} elseif ('point' == $post_review_type) {
			$post_review_average_scale_score = $post_review_average_score * 100 / 10;
			$post_review_author_average_scale_score = $post_review_author_average_score * 100 / 10;
			$post_review_user_average_scale_score = $post_review_user_average_scale_score * 100 / 01;
		}

		// update review average field for index query post
		update_post_meta($post_id, $id.'-average', $post_review_average_scale_score);	
		update_post_meta($post_id, $id.'-user-'.$post_review_type, $post_review_user);
		
		// output rating box: https://circle.firchow.net/
		echo sprintf(
			$display['text_n_user_vote_x_score']
			, $post_review_user['count']
			, $post_review_user['count'] > 1 ? $display['text_vote']:$display['text_votes']
			, number_format($post_review_user_average_score, 1)
		) . '**********'.$post_review_average_score;
	}
	die();
}
if (is_admin()) :	
	add_action( 'wp_ajax_nopriv_sneeit_post_review_user_rating', 'sneeit_rating_user_rating_callback' );
	add_action( 'wp_ajax_sneeit_post_review_user_rating', 'sneeit_rating_user_rating_callback' );
endif;// is_admin for ajax