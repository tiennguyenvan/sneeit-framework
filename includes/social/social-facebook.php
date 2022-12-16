<?php


////
// PRE DEFINES FOR GETTING VALUE OF LIKES
////
// link format: https://www.facebook.com/Sneeit-622691404530609/
add_filter('sneeit_number_facebook_likes', 'sneeit_get_social_count_number_facebook_likes', 1, 1);
function sneeit_get_social_count_number_facebook_likes ($args) {
	if ( is_string( $args ) ) {	
		$args = array(
			'url' => $args
		);
	}
	
	// process URL:	
	if ( strpos( $args['url'], '/community') === false ) {
		if (strrpos( $args['url'], '/' ) < strlen( $args['url'] ) - 1 ) {
			$args['url'] .= '/';
		}
		$args['url'] .= 'community';
	}
		
	
	// addition data for facebook			
	$args['name'] = 'facebook';
	$args['filter'] = array(
		array(
			'start_1' => 'id="content_container"',
			'start_2' => 'class="clearfix"',
			'start_3' => 'class="clearfix',
			'start_4' => '<div class="',
			'start_5' => '<div class="',
			'start_6' => '">',
			'end_1' => '</div>',
		),	
		array(
			'start_1' => '<meta name="description" content="',
			'start_2' => '. ',
			'end_1' => ' ',
		),			
	);	
	
if (SNEEIT_SOCIAL_DEBUG && 
	!empty($_GET['debug']) && 
	current_user_can( 'manage_options')
) :
	var_dump($args);
endif;
	
	return sneeit_get_one_number_from_url( $args );
}

if (SNEEIT_SOCIAL_DEBUG && 
	!empty($_GET['debug']) && 
	strpos($_GET['debug'], 'facebook') !== false	
) :
	add_action('plugins_loaded', 'sneeit_debug_social_facebook');
	function sneeit_debug_social_facebook() {	
		if (!current_user_can( 'manage_options')) {
			return;
		}
		var_dump(sneeit_get_social_count_number_facebook_likes(array('url' => $_GET['debug'])));	
		die();		
	}
endif;

