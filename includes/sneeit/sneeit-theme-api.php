<?php
/* 
 */
/**
 * https://sneeit.com/api/$user_name/$license_key/action_name:$theme_slug;
 * 
 * We must use api/wp-theme because sneeit has many items with same name
 * So providing wp-theme will help it has more information to search the 
 * purchased item with key word "WordPress Theme"
 * 
 * @param type $user_name
 * @param type $license_key
 * @param type $theme_slug
 * @return type
 */
define('SNEEIT_API_WP_THEME_ERROR_MISSING_AGENT', 0);
define('SNEEIT_API_WP_THEME_ERROR_WRONG_AGENT', 1);
define('SNEEIT_API_WP_THEME_ERROR_BAD_AGENT', 2);
define('SNEEIT_API_WP_THEME_ERROR_NOT_MEMBER', 3);
define('SNEEIT_API_WP_THEME_ERROR_NOT_BUYER', 4);
define('SNEEIT_API_WP_THEME_ERROR_NOT_PURCHASE', 5);
define('SNEEIT_API_WP_THEME_ERROR_NOT_LICENSE', 6);
define('SNEEIT_API_WP_THEME_ERROR_WRONG_LICENSE', 7);
function sneeit_sneeit_theme_api($user_name, $license_key, $theme_slug) {
	$url = "https://sneeit.com/api/wp-theme/$user_name/$license_key/$theme_slug";
	$cache_id = 'wp-check-update' . $user_name . $license_key . $theme_slug;
	
	// return cache in case not in activation page	
	if (empty($_POST['sneeit-username']) && empty($_POST['sneeit-key'])) {
		$cache = get_transient($cache_id);

		if ($cache && !is_wp_error($cache)) {
			return $cache;
		}
	} else {
		delete_transient($cache_id);
	}
	
	$response = wp_remote_request( $url );	
	/* Request  Error checkup */
	if ( is_wp_error( $response ) ) {
    	return esc_html_x('Unknown error from your server. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit');		
    }
	if ( $response['response']['code'] == 403 ) {
		return esc_html_x('Sneeit server is in downtime. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit');
	}
	if ( $response['response']['code'] == 404 ) {
		return esc_html_x('Not found your information from Sneeit server. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit');
	}
	if ($response['response']['code'] != 200 &&
		$response['response']['code'] != 301 && 
		$response['response']['code'] != 302) {
		return sprintf(esc_html_x('Unknown error response code (%s) from Sneeit server. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit'), $response['response']['code']);
	}
	
	/* Return data */		
	$data = json_decode( $response['body'] );
	
	/* Data error checkup */
	if (is_wp_error( $data )) {		
		return esc_html_x('Wrong response data format. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit');
	}
	if (!is_object($data)) {				
		return esc_html_x('Unexpected response data. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit');
	}
	$data = (array) $data;

	if (isset($data['error'])) {				
		$purchase_link = "https://sneeit.com/?s=$theme_slug+wordpress+theme";
		$register_link = "https://sneeit.com/wp-login.php?action=register";
		$home_link = "https://sneeit.com";
		$license_link = "https://sneeit.com/author/$user_name/?tab=licenses";
		$error_message = array(
			SNEEIT_API_WP_THEME_ERROR_MISSING_AGENT => esc_html_x('Missing some packages when sending data. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit'),
			
			SNEEIT_API_WP_THEME_ERROR_WRONG_AGENT => esc_html_x('Wrong data in some packages when sending to Sneeit. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit'),
			
			SNEEIT_API_WP_THEME_ERROR_BAD_AGENT => esc_html_x('Bad data in some packages when sending to Sneeit. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit'),
			
			SNEEIT_API_WP_THEME_ERROR_NOT_MEMBER => sprintf(wp_kses(_x('Not found your username from Sneeit. Please make sure you <a href="%s" target="_blank">created an account at Sneeit</a>. Or you can send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit'), array(
				'a' => array(
					'href' => array(),
					'target' => array()
				)
			)), $register_link),
			
			SNEEIT_API_WP_THEME_ERROR_NOT_BUYER => sprintf(wp_kses(_x('You did not purchase any item from Sneeit. Please visit <a href="%s" target="_blank">Sneeit</a> and purchase one. Or you can send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit'), array(
				'a' => array(
					'href' => array(),
					'target' => array()
				)
			)), $home_link),
			
			SNEEIT_API_WP_THEME_ERROR_NOT_PURCHASE => sprintf(wp_kses(_x('You did not purchase this theme from Sneeit. Please <a href="%s" target="_blank">purchase it here</a>. Or you can send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit'), array(
				'a' => array(
					'href' => array(),
					'target' => array()
				)
			)), $purchase_link),
			
			SNEEIT_API_WP_THEME_ERROR_NOT_LICENSE => sprintf(wp_kses(_x('You did not add license for this website domain on Sneeit. Please access <a href="%s" target="_blank">Sneeit License tab</a> on your Sneeit account to add one. Or you can send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit'), array(
				'a' => array(
					'href' => array(),
					'target' => array()
				)
			)), $license_link),
			
			SNEEIT_API_WP_THEME_ERROR_WRONG_LICENSE => sprintf(wp_kses(_x('The provided license key is wrong or not for your website domain, may be you copied wrong key from wrong domain. Please access <a href="%s" target="_blank">Sneeit License tab</a> on your Sneeit account to double check. Or you can send email to contact@sneeit.com to request support. Thank you!', 'dashboard', 'sneeit'), array(
				'a' => array(
					'href' => array(),
					'target' => array()
				)
			)), $license_link),
		);
		
		if (!empty($error_message[$data['error']])) {
			return $error_message[$data['error']];
		} else {
			return sprintf(esc_html_x('Unknown error return from Sneeit, error code [%s]. Please check your internet connection and try again. Or send email to contact@sneeit.com to request support. Thank you!', $register_link), $data['error']);
		}
	}
	
	/* save to cache */
	if (!empty($data['download_url']) && !empty($data['version'])) {
		set_transient($cache_id, $data, 60 * 60 * 24);
	}
	
	/* and return data */
	return $data;
}
