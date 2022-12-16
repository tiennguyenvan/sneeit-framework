<?php
define('SNEEIT_INSTAGRAM_API_KEY', 'sneeit-instagram-api-key');

// link format: https://www.instagram.com/username
add_filter('sneeit_number_instagram_followers', 'sneeit_get_social_count_number_instagram_followers', 1, 1);
function sneeit_get_social_count_number_instagram_followers($args) {
	if (is_string($args)) {		
		$args = array(
			'url' => $args
		);
	}
	if (!isset($args['name'])) {
		$args['name'] = 'instagram';
	}
	if (!isset($args['filter'])) {
		$args['filter'] = array(
			array(
				'start_1' => 'followed_by":{"count":',
				'end_2' => '},"'
			)
		);
	}
	
	// if user inputted youtube API KEY
	$instagram_api_key = get_option(SNEEIT_INSTAGRAM_API_KEY);
	if ($instagram_api_key) {
		// we will check cache first for fast response
		$count_key = sneeit_url_to_slug($args['url'], true, SNEEIT_PLUGIN_VERSION);	
		$count = get_transient($count_key);
		if ($count) {
			if (is_numeric($count)) {
				$count = number_format($count);
			}		
			return $count;
		}
		
		// if we found no, we will get APIs		
		$count = sneeit_get_data_from_json_url(array(
			'url' => 'https://api.instagram.com/v1/users/self/?access_token='.$instagram_api_key,
			'chain' => array('data', 'counts', 'followed_by'),
			'name'  => 'instagram',
		));

		// we found one
		if ($count) {
			if (is_numeric($count)) {
				$count = number_format($count);
			}

			// we need to save to cached
			set_transient($count_key, $count, SNEEIT_CACHE_TIME);
			update_option($count_key, $count);

			// and return the value
			return $count;
		}
		
	}
	
	// Instagram require empty user agent at beginning to response right HTML
	// if not, it still response but with a wasted HTML	
	return sneeit_get_one_number_from_url($args);
}

if (SNEEIT_SOCIAL_DEBUG && 
	!empty($_GET['debug']) && 
	strpos($_GET['debug'], 'instagram') !== false
) :
	add_action('plugins_loaded', 'sneeit_debug_social_instagram');
	function sneeit_debug_social_instagram() {	
		if (!current_user_can( 'manage_options')) {
			return;
		}
		var_dump('sneeit_debug_social_instagram :', sneeit_get_social_count_number_instagram_followers(array('url' => $_GET['debug'])));
		die();		
	}
endif;



add_filter('sneeit_instagram_media_nodes', 'sneeit_get_social_instagram_media_nodes', 1, 1);
function sneeit_get_social_instagram_media_nodes($args) {
	if (is_string($args)) {	
		$args = array(
			'url' => $args
		);
	}
	if (!isset($args['filter'])) {
		$args['filter'] = array(
			array(
				'start_1' => '<script type="text/javascript">window._sharedData = ',
				'end_2' => ';</script>'
			)			
		);
	}
	
	$args = wp_parse_args($args, array(		
		'filter' => array(
			'start_1' => '<script type="text/javascript">window._sharedData = ',
			'end_2' => ';</script>'
		),
		'chain' => array(
			'entry_data',
				'ProfilePage',
					0,
						'user',
							'media',
								'nodes'
		),		
	));
	
	$nodes = sneeit_get_data_from_json_url( $args );
	if (is_wp_error($nodes)) {
		return array();
	}
	
	return $nodes;
}


/*we will save this id in site option with name: sneeit_instagram_api_key*/
add_action( 'sneeit_instagram_api_key_collector', 'sneeit_instagram_api_key_collector_action' );
function sneeit_instagram_api_key_collector_action( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'label' => esc_html__( 'Instagram Access Token', 'sneeit' ),
		'description' => wp_kses( __(
			'To get Instagram Access Token: <ol>'.
				'<li>' . 
					'Access <a href="https://instagram.pixelunion.net/" target="_blank">https://instagram.pixelunion.net/</a>, click <b>Generate Access Token</b> button'.
				'</li>'.
				'<li>' .
					'Copy and paste Access Token here</b>'.
				'</li>'.
			'</ol>'
				
		, 'sneeit' ), array(
			'a' => array('href' => array(), 'target' => array(),) , 
			'img' => array('src' => array(), 'alt' => array(),) ,
			'ol' => array() ,
			'li' => array() ,
			'b' => array() ,
		) ),
		'before' => '<tr>',
		'after' => '</tr>',
		'before_label' => '<th scope="row"><label for="sneeit-instagram-api-key">',
		'after_label' => '</label></th>',
		'before_description' => '<p class="description" id="sneeit-instagram-api-key">',
		'after_description' => '</p>',
		'before_input' => '<td>',
		'after_input' => '</td>',
		'nonce' => true, /* false, collector will not check submit value*/
	) );
	
	// process save
	if ( $args['nonce']) {
		if ( ! empty( $_GET[ SNEEIT_INSTAGRAM_API_KEY ] ) ) {
			update_option( SNEEIT_INSTAGRAM_API_KEY, esc_attr( $_GET[ SNEEIT_INSTAGRAM_API_KEY ] ) );
		}
		elseif ( ! empty( $_POST[ SNEEIT_INSTAGRAM_API_KEY ] ) ) {
			update_option( SNEEIT_INSTAGRAM_API_KEY, esc_attr( $_POST[ SNEEIT_INSTAGRAM_API_KEY ] ) );
		}
	}
	
	$sneeit_instagram_api_key = get_option( SNEEIT_INSTAGRAM_API_KEY );
	
	echo 
	$args['before'] .
		$args['before_label'] . $args['label'] . $args['after_label'] .
		$args['before_input'] .
			'<input name="' .
					esc_attr( SNEEIT_INSTAGRAM_API_KEY ) . 
					'" type="text" id="' . 
					esc_attr( SNEEIT_INSTAGRAM_API_KEY ) . 
					'" value="' . 
					esc_attr( $sneeit_instagram_api_key ) .
					'" class="regular-text"/>'.
			$args['before_description'] . $args['description'] . $args['after_description'] .
		$args['after_input'] .
	$args['after'] ;
}