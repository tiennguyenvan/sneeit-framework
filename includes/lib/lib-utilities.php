<?php
function sneeit_get_server_request($key) {
	$value = '';
	if ($key) {
		if (isset($_GET[$key])) {
			$value = $_GET[$key];
		} else if (isset($_POST[$key])) {
			$value = $_POST[$key];
		}
	}
	return $value;
}

function sneeit_are_you_admin() {
	return current_user_can( 'manage_options' );
}

function sneeit_get_client_ip() {
	$ip_keys = array(
		'HTTP_CLIENT_IP', 
		'HTTP_X_FORWARDED_FOR', 
		'HTTP_X_FORWARDED', 
		'HTTP_FORWARDED_FOR', 
		'HTTP_FORWARDED', 
		'REMOTE_ADDR'
	);	
	foreach ($ip_keys as $key) {
		$ip = getenv($key);
		if( $ip && !strcasecmp( $ip, 'unknown')) {
			return $ip;
		}
		if (isset($_SERVER[$key])) {
			$ip = $_SERVER[$key];
			if( $ip && !strcasecmp( $ip, 'unknown')) {
				return $ip;
			}
		}
	}
	return '';
}

function sneeit_is_gpsi() {
	return (isset($_SERVER['HTTP_USER_AGENT']) && 
			strpos($_SERVER['HTTP_USER_AGENT'], 'Google Page Speed Insights') !== false);
}

function sneeit_var_dump($expression, $echo = true) {
	if (current_user_can( 'manage_options') && !empty($_GET['debug']) && $_GET['debug'] == 'sneeit') {
		if (!is_string($expression)) {
			echo '<pre style="border: none; width: 100%;">';
			var_dump($expression);
			echo '</pre>';
		} else {
			echo '<textarea style="border: none; width: 100%;">';
			echo esc_textarea($expression);
			echo '</textarea>';
		}
	}
}

function sneeit_get_one_number_from_url_with_filter( $url, $filter) {
	$count = -1;
	$remote_args = array(
		'timeout'    => SNEEIT_REMOTE_TIMEOUT,
		'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) ' . 
		                'AppleWebKit/537.36 (KHTML, like Gecko) ' . 
		                'Chrome/76.0.3809.132 '.
		                'Safari/537.36',
		'sslverify'  => false
	);
	
	if (SNEEIT_SOCIAL_DEBUG && 
		!empty($_GET['debug']) && 
		current_user_can( 'manage_options')
	) :			
		// $url = esc_url($url);		
		$url = 'https://instagram.com/mensdayoutindia';
	endif;
	
	$response = wp_remote_get( $url, array(	'timeout' => SNEEIT_REMOTE_TIMEOUT	) );	
	if (SNEEIT_SOCIAL_DEBUG && 
		!empty($_GET['debug']) && 
		current_user_can( 'manage_options')
	) :		
		var_dump('SNEEIT_GET_ONE_NUMBER_FROM_URL_WITH_FILTER 1:', $url, $filter, $response, wp_remote_retrieve_body($response));	
		$response = @file_get_contents( "https://www.instagram.com/mensdayoutindia/?__a=1");
		var_dump('SNEEIT_GET_ONE_NUMBER_FROM_URL_WITH_FILTER 1:', $url, $filter, $response, wp_remote_retrieve_body($response));
		$count = false;
	endif;
	
	if ( ! is_wp_error( $response ) ) {
		
		$count = sneeit_get_one_number_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
	}	
	
	if ( -1 == $count ) {
		$response = wp_remote_get( $url, $remote_args );	
		if (SNEEIT_SOCIAL_DEBUG && 
			!empty($_GET['debug']) && 
			current_user_can( 'manage_options')
		) :		
			var_dump('SNEEIT_GET_ONE_NUMBER_FROM_URL_WITH_FILTER 2:', $url, $filter, $response, wp_remote_retrieve_body($response));			
			$count = false;
		endif;
		if ( ! is_wp_error( $response ) ) {			
			$count = sneeit_get_one_number_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( -1 == $count ) {
		$remote_args['sslverify'] = true;
		$response = wp_remote_get( $url, $remote_args );
		if (SNEEIT_SOCIAL_DEBUG && 
			!empty($_GET['debug']) && 
			current_user_can( 'manage_options')
		) :		
			var_dump('SNEEIT_GET_ONE_NUMBER_FROM_URL_WITH_FILTER 3:', $url, $filter, $response, wp_remote_retrieve_body($response));			
			$count = false;
		endif;
		if ( ! is_wp_error( $response ) ) {
			$count = sneeit_get_one_number_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( -1 == $count ) {
		$remote_args['sslverify'] = false;
		$remote_args['user-agent'] = '';
		$response = wp_remote_get( $url, $remote_args );
		if (SNEEIT_SOCIAL_DEBUG && 
			!empty($_GET['debug']) && 
			current_user_can( 'manage_options')
		) :		
			var_dump('SNEEIT_GET_ONE_NUMBER_FROM_URL_WITH_FILTER 4:', $url, $filter, $response, wp_remote_retrieve_body($response));			
			$count = false;
		endif;
		if ( ! is_wp_error( $response ) ) {
			$count = sneeit_get_one_number_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( -1 == $count ) {
		$remote_args['sslverify'] = true;
		$remote_args['user-agent'] = '';
		$response = wp_remote_get( $url, $remote_args );
		if (SNEEIT_SOCIAL_DEBUG && 
			!empty($_GET['debug']) && 
			current_user_can( 'manage_options')
		) :		
			var_dump('SNEEIT_GET_ONE_NUMBER_FROM_URL_WITH_FILTER 5:', $url, $filter, $response, wp_remote_retrieve_body($response));			
			$count = false;
		endif;
		if ( ! is_wp_error( $response ) ) {
			$count = sneeit_get_one_number_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( -1 == $count ) {
		$remote_args['sslverify'] = false;
		unset($remote_args['user-agent']);
		$response = wp_remote_get( $url, $remote_args );
		if (SNEEIT_SOCIAL_DEBUG && 
			!empty($_GET['debug']) && 
			current_user_can( 'manage_options')
		) :		
			var_dump('SNEEIT_GET_ONE_NUMBER_FROM_URL_WITH_FILTER 6:', $url, $filter, $response, wp_remote_retrieve_body($response));			
			$count = false;
		endif;
		if ( ! is_wp_error( $response ) ) {
			$count = sneeit_get_one_number_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( -1 == $count ) {
		$remote_args['sslverify'] = true;
		unset($remote_args['user-agent']);
		$response = wp_remote_get( $url, $remote_args );
		if (SNEEIT_SOCIAL_DEBUG && 
			!empty($_GET['debug']) && 
			current_user_can( 'manage_options')
		) :		
			var_dump('SNEEIT_GET_ONE_NUMBER_FROM_URL_WITH_FILTER 7:', $url, $filter, $response, wp_remote_retrieve_body($response));			
			$count = false;
		endif;
		if ( ! is_wp_error( $response ) ) {
			$count = sneeit_get_one_number_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	return $count;
}

/*
 * @param $args array()
 *		name			string		will use to check url
 *		url				string		social url
 *		cache_time		int			only re-fetch after end of cache time (seconds)
 *		remote_timeout	int			number of seconds to wait when fetch a site
 *		filter			array		the filter to cut the response html until find the count
 *							key			key will begin with prefix 'start' or 'end', 'open' or 'close', true or false, '' or ' ', 0 or 1
 *										will cut off the 'head' or the 'tail', example: 'start_1', or 'end_2'
 *										only use _ (underscore) to split key parts
 *										key must be specifi
 *							value		the string will be searched
 *		user-agent		header user-agent request
 * 
 *	@return -1 for error and an int for result
 */
function sneeit_get_one_number_from_url( $args = array() ) {
	
	if (!isset($args['url']) || 
		!isset($args['filter']) || 
		!is_array($args['filter']) ||
		(isset($args['name']) && strpos($args['url'], $args['name']) === false)) {		
		return -1;
	}
		
	$count = false;
	$response_html = false;
	
	// generate key just to use in case using cache
	$count_key = sneeit_url_to_slug($args['url'], true, SNEEIT_PLUGIN_VERSION);	
	$count = get_transient($count_key);
	
if (SNEEIT_SOCIAL_DEBUG && 
	!empty($_GET['debug']) && 
	current_user_can( 'manage_options')
) :
	var_dump('sneeit_get_one_number_from_url 1:', $count_key);
	var_dump('sneeit_get_one_number_from_url 2:', $count);
	var_dump('sneeit_get_one_number_from_url 3:', get_transient( SNEEIT_ADMIN_CACHE_REFRESH_TIME_KEY ));
	$count = false;
endif;
	
	if ( $count == false || $count == '' || ( sneeit_are_you_admin() && get_transient( SNEEIT_ADMIN_CACHE_REFRESH_TIME_KEY ) == false ) ) {
		
		if (SNEEIT_SOCIAL_DEBUG && 
			!empty($_GET['debug']) && 
			current_user_can( 'manage_options')
		) :
			var_dump('START GETTING NUMBER');			
			$count = false;
		endif;
		
		$count = sneeit_get_one_number_from_url_with_filter( $args['url'], $args['filter'] );		
		
		if ( -1 == $count ) {
			$count = get_option( $count_key );
			
			if ( false == $count ) {
				return -1;
			}
		}
		
		set_transient($count_key, $count, SNEEIT_CACHE_TIME);
		update_option($count_key, $count);
		set_transient(SNEEIT_ADMIN_CACHE_REFRESH_TIME_KEY, 'cached', SNEEIT_ADMIN_CACHE_REFRESH_TIME);
	}	
	return $count;
}

/*filter is the list of index (key) in multiple levels chain in json, don't include "body"
 * example: $args['filter'] = array('items', 0, 'statistics', 'subscriberCount')
 * will get data from json[items][0][statistics][subscriberCount]
 * 
 * the name paramete is just for checking the result
 *  */
function sneeit_get_data_from_json_url( $args = array() ) {
	$data = '';
	if (!isset($args['url']) || 
		!isset($args['chain']) || 
		!is_array($args['chain']) ||
		(isset($args['name']) && strpos($args['url'], $args['name']) === false)) {		
		return $data;
	}
			
	$count = false;
	$response_html = false;
	
	$data_key = sneeit_url_to_slug($args['url'], true, SNEEIT_PLUGIN_VERSION);
	
	$data = get_transient($data_key);	
	
	if ( $data == false || $data == '' || ( sneeit_are_you_admin() && get_transient( SNEEIT_ADMIN_CACHE_REFRESH_TIME_KEY ) == false ) ) {		
		
		if (empty($args['filter'])) {
			$response = wp_remote_get( $args['url'], array(	'timeout' => SNEEIT_REMOTE_TIMEOUT	) );	
			if ( is_wp_error( $response ) ) {			
				return $data;
			}
			$data = json_decode(wp_remote_retrieve_body($response), true);	
		} else {
			$data = sneeit_get_text_from_url_with_filter( $args['url'], $args['filter']);
			if ( is_wp_error( $data ) ) {			
				return $data;
			}
			$data = json_decode($data, true);				
		}				
						
		if ( is_wp_error( $data ) ) {			
			return $data;
		}
		
		foreach ($args['chain'] as $json_key) {			
			if ( !isset($data[$json_key])) {
				$data = get_option( $data_key );
				if (false == $data || '' == $data) {
					$data = '';
				}
				return $data;
			}
			$data = $data[$json_key];
		}
		
		
		set_transient($data_key, $data, SNEEIT_CACHE_TIME);
		update_option($data_key, $data);
		set_transient(SNEEIT_ADMIN_CACHE_REFRESH_TIME_KEY, 'cached', SNEEIT_ADMIN_CACHE_REFRESH_TIME);
	}
	return $data;
}

/**
 * Merge user defined arguments into defaults array.
 *
 * This function is used throughout WordPress to allow for array
 * to be merged into another array.
 * We support only 3 levels
 *
 * @param array $args  Value to merge with $defaults
 * @param array        $defaults Optional. Array that serves as the defaults. Default empty.
 * @return array Merged user defined values with defaults.
 */
function sneeit_parse_args( $args, $default = array() ) {
	$args = (array) $args;
	$default = (array) $default;
	
	foreach ($default as $key_1st => $default_1st) {
		if (!isset($args[$key_1st])) {
			$args[$key_1st] = $default[$key_1st];
		} else if (is_array($default_1st) && isset($args[$key_1st])) {
			foreach ($default_1st as $key_2nd => $default_2nd) {
				
				if (!isset($args[$key_1st][$key_2nd])) {
					$args[$key_1st][$key_2nd] = $default[$key_1st][$key_2nd];
				} else if (is_array($default_2nd) && isset($args[$key_1st][$key_2nd])) {
					
					foreach ($default_2nd as $key_3rd => $default_3rd) {
						if (!isset($args[$key_1st][$key_2nd][$key_3rd])) {
							$args[$key_1st][$key_2nd][$key_3rd] = $default[$key_1st][$key_2nd][$key_3rd];
						}
					}
				}
				
			}
		}
	}
	
	return $args;
}



/**
 * 
 * @param type $url
 * @param type $filter
 * @return type
 */
function sneeit_get_text_from_url_with_filter( $url, $filter) {
	$text = '';
	$remote_args = array(
		'timeout'    => SNEEIT_REMOTE_TIMEOUT,
		'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) ' . 
		                'AppleWebKit/537.36 (KHTML, like Gecko) ' . 
		                'Chrome/49.0.2623.87 '.
		                'Safari/537.36',
		'sslverify'  => false
	);
	
	$response = wp_remote_get( $url, array(	'timeout' => SNEEIT_REMOTE_TIMEOUT	) );	
	
	if ( ! is_wp_error( $response ) ) {
		/* 
		 * active this if you want to debug to save the time
		 * /
		 /*
		set_transient('test20170210', wp_remote_retrieve_body($response));
		*/
		/*
		* end debug 
		*/		 
		$text = sneeit_text_in_string_with_filter( wp_remote_retrieve_body($response), $filter );			
	}	
	
	if ( empty($text) ) {
		$response = wp_remote_get( $url, $remote_args );	
		
		if ( ! is_wp_error( $response ) ) {			
			$text = sneeit_text_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( empty($text) ) {
		$remote_args['sslverify'] = true;
		$response = wp_remote_get( $url, $remote_args );
		
		if ( ! is_wp_error( $response ) ) {
			$text = sneeit_text_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( empty($text) ) {
		$remote_args['sslverify'] = false;
		$remote_args['user-agent'] = '';
		$response = wp_remote_get( $url, $remote_args );
		
		if ( ! is_wp_error( $response ) ) {
			$text = sneeit_text_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( empty($text) ) {
		$remote_args['sslverify'] = true;
		$remote_args['user-agent'] = '';
		$response = wp_remote_get( $url, $remote_args );
		
		if ( ! is_wp_error( $response ) ) {
			$text = sneeit_text_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( empty($text) ) {
		$remote_args['sslverify'] = false;
		unset($remote_args['user-agent']);
		$response = wp_remote_get( $url, $remote_args );
		
		if ( ! is_wp_error( $response ) ) {
			$text = sneeit_text_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	if ( empty($text) ) {
		$remote_args['sslverify'] = true;
		unset($remote_args['user-agent']);
		$response = wp_remote_get( $url, $remote_args );
		
		if ( ! is_wp_error( $response ) ) {
			$text = sneeit_text_in_string_with_filter( wp_remote_retrieve_body($response), $filter );
		}
	}
	
	return $text;
}