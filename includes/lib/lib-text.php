<?php
function sneeit_slug_to_title($slug = '') {
	return ucfirst(str_replace('_', ' ', str_replace('-', ' ', $slug)));
}
function sneeit_title_to_slug($title = '', $sep = '-') {
	$slug = esc_attr(sanitize_title_with_dashes($title));
	if (!$sep || strlen($sep) > 1) {
		$sep = '-';
	}
	
	if (!((ord($sep) >= ord('A') && ord($sep) <= ord('Z')) || 
		(ord($sep) >= ord('a') && ord($sep) <= ord('z')) || 
		(ord($sep) >= ord('0') && ord($sep) <= ord('9')) ||
		(ord($sep) == ord('_') || ord($sep) == ord($sep))
		)) {
		$sep = '-';
	}
	if (ord($slug[0]) >= ord('0') && ord($slug[0]) <= ord('9')) {
		$slug[0] = $sep;
	}
	for ($i = 0; $i < strlen($slug); $i++) {		
		if ((ord($slug[$i]) >= ord('A') && ord($slug[$i]) <= ord('Z')) || 
			(ord($slug[$i]) >= ord('a') && ord($slug[$i]) <= ord('z')) || 
			(ord($slug[$i]) >= ord('0') && ord($slug[$i]) <= ord('9')) ||
			(ord($slug[$i]) == ord('_') || ord($slug[$i]) == ord($sep))
			) {
			continue;
		}		
		$slug[$i] = $sep;
	}
	return $slug;
}

function sneeit_url_to_slug( $url, $replace_minus = false, $suffix = '', $max_length = 250) {
	// generate key just to use in case using cache
	$slug = sneeit_title_to_slug( $url . $suffix);
	$slug = str_replace('https', '', $slug);
	$slug = str_replace('http', '', $slug);
	$slug = str_replace('www', '', $slug);
	if ($replace_minus) {
		$slug = str_replace('-', '_', $slug);	
	}
	
	if (strlen($slug) > $max_length) {
		$slug = substr($slug, strlen($slug) - $max_length);
	}
	return $slug;
}

function sneeit_is_variable_name_character($character) {
	$character = ord($character);
	if ($character >= ord('a') && 
		$character <= ord('z') ||
		$character >= ord('A') &&
		$character <= ord('Z') ||
		$character >= ord('0') &&
		$character <= ord('9') ||
		$character == ord('_')) {
		return true;
	}

	return false;
}
function sneeit_is_slug_name_character($character) {
	$character = ord($character);
	if ($character >= ord('a') && 
		$character <= ord('z') ||
		$character >= ord('A') &&
		$character <= ord('Z') ||
		$character >= ord('0') &&
		$character <= ord('9') ||
		$character == ord('_') || 
		$character == ord('-')) {
		return true;
	}

	return false;
}

function sneeit_get_all_numbers_in_string($str) {
	$matches = array();
	preg_match_all('!\d+!', $str, $matches);
	return $matches;
}

// use this if you want: intval(preg_replace('/[^0-9]+/', '', $str), 10);
// extrac the first number if a string
function sneeit_get_one_number_in_string( $str ) {
//	sneeit_var_dump($str);
	$number = '';
	
	$str = strtoupper( $str );
	for ( $i = 0; $i < strlen( $str ); $i++ ) {
		$char = substr( $str, $i, 1 );
//		sneeit_var_dump( '>>' .$char.'<<' );		
//		sneeit_var_dump( ord($char) );		
		if ( ( ord( $char ) >= ord('0') && ord( $char ) <= ord('9') ) ||
			 ( ( $char == '.' || 
				 $char == ',' || 
				 $char == 'K' || 
				 $char == 'M' || 
				 $char == 'B' ||
				 ord( $char ) > 125
				) && 
				$number /* only collect special separator when already have first number */
			 )
			) {
			
			if ( ord( $char ) < 125 ) {
				$number .= $char;
			}
//			sneeit_var_dump( '{{'.$number.'}}' );
						
			// met the abbreviate of k = 1000 , m = 1000,000 , b = 1,000,000,000
			if ( $char == 'K' || 
				 $char == 'M' || 
				 $char == 'B' 
				) {
				break;
			}
		} 
		elseif ( $number ) {
			break;			
		} /*endif check valid char for number*/
		
	
	}/*end for string*/
	
//	sneeit_var_dump( $number );
	
	
	// format number before return
	// this is sure format of asia, we must convert to globe standard
	if ( substr_count( $number, '.' ) > 1 ) {
		$number = str_replace( '.', ',', $number );
	} 
	else {
		$asia_number = explode( '.', str_replace( array( 'K', 'M', 'B', ), '', $number ) );
		foreach ( $asia_number as $key => $value ) {
			if ( strlen( $value ) == 3 && $key > 0 ) {
				$number = str_replace( '.', ',', $number );
				break;
			}
		}
	}
	
	// if only a raw interget number
	if ( is_numeric( $number ) ) {
		$temp_num = explode( '.', $number );
		$temp_num[0] = number_format( $temp_num[0] );
		$number = implode('.', $temp_num);
	}

	return $number;
}

function sneeit_get_first_int_in_string( $str ) {
	$int = 0;
		
	for ( $i = 0; $i < strlen( $str ); $i++ ) {
		$char = substr( $str, $i, 1 );
		if (is_numeric($char)) {
			$int = $int * 10 + ((int) $char);
		} else if ($int) {
			return $int;
		}	
	}/*end for string*/

	return $int;
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

function sneeit_get_one_number_in_string_with_filter( $str, $filters ) {	
	
	$count = -1;
	$back_up_str = $str;
	
if (SNEEIT_SOCIAL_DEBUG && 
	!empty($_GET['debug']) && 
	current_user_can( 'manage_options')
) :
	echo 'start sneeit_get_one_number_in_string_with_filter ';
	echo '<pre style="width: 100%; height: 100px; overflow: scroll">';
	echo esc_html($str);
	echo '</pre>';	
endif;
	
	/* extract filter list */
	foreach ( $filters as $filter ) {		
		$str = $back_up_str;
		$found = false;
		$count = -1;
		
		/* and filter the back up string to get result */
		foreach ($filter as $key => $value) {	

			$key = explode('_', $key);
			$key = $key[0];
			$key = ( 'end' == $key || 'close' == $key || 'tail' == $key );

			$index = strpos( $str, $value );

if (SNEEIT_SOCIAL_DEBUG && 
	!empty($_GET['debug']) && 
	current_user_can( 'manage_options')
) :
	var_dump('strpos ['.esc_html($value).'] in str = [' . $index . ']');
endif;

			if ($index === false) {
				break;
			}
			

			if ( $key ) {
				// if cut off the tail, we found				
				$str = substr( $str, 0, $index );
				$found = true;				
				
				break;
			} else {
				// if cut off the head					
				$str = substr( $str, $index + strlen( $value ) );
			}
			
if (SNEEIT_SOCIAL_DEBUG && 
	!empty($_GET['debug']) && 
	current_user_can( 'manage_options')
) :
	var_dump('String after cut ['.$key.']');
	echo '<pre style="width: 100%; height: 100px; overflow: scroll">';
	echo esc_html($str);
	echo '</pre>';	
endif;

		} /* end foreach ($filter as $key => $value) */
		
		// check if our return is a real number
		if ( $found && strlen( $str ) < 100) {
			$count = sneeit_get_one_number_in_string( $str );
			if ($count && $count != -1 && strlen( $count ) <= 15) {
				break;
			}			
		}
	} /* foreach ( $filters as $filter ) */ 
	
	
	return $count;
}


function sneeit_text_in_string_with_filter( $str, $filters ) {		
	$back_up_str = $str;
	
	/* extract filter list */
	foreach ( $filters as $filter ) {		
		$str = $back_up_str;
		$found = false;
		
		/* and filter the back up string to get result */
		foreach ($filter as $key => $value) {			
			$key = explode('_', $key);
			$key = $key[0];
			$key = ( 'end' == $key || 'close' == $key || 'tail' == $key );
			
			$index = strpos( $str, $value );
			
			if ($index === false) {
				break;
			}
			

			if ( $key ) {
				// if cut off the tail, we found				
				$str = substr( $str, 0, $index );
				$found = true;				
				
				break;
			} else {
				// if cut off the head					
				$str = substr( $str, $index + strlen( $value ) );
			}
		}
		
		if ( $found ) {
			break;
		}
	}
		
	return $str;
}


/* mb string replacer */
if (!function_exists('mb_strlen')) {
    function mb_strlen ($string) {
        return strlen($string);
    }
}

if (!function_exists('mb_strpos')) {
    function mb_strpos($haystack,$needle,$offset=0) {
        return strpos($haystack,$needle,$offset);
    }
}
if (!function_exists('mb_strrpos')) {
    function mb_strrpos ($haystack,$needle,$offset=0) {
        return strrpos($haystack,$needle,$offset);
    }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower($string) {
        return strtolower($string);
    }
}
if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper($string){
        return strtoupper($string);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($string,$start,$length = false) {
	    if ($length) {
		    return substr($string,$start, $length);
	    }
	    return substr($string,$start);
    }
}
if (!function_exists('mb_internal_encoding')) {
    function mb_internal_encoding($string) {
        return $string;
    }
}
if (!function_exists('mb_http_output')) {
    function mb_http_output($string) {
        return $string;
    }
}
if (!function_exists('mb_http_output')) {
    function mb_http_output($string) {
        return $string;
    }
}
if (!function_exists('mb_http_input')) {
    function mb_http_input($string) {
        return $string;
    }
}
if (!function_exists('mb_language')) {
    function mb_language($string) {
        return $string;
    }
}
if (!function_exists('mb_regex_encoding')) {
    function mb_regex_encoding($string) {
        return $string;
    }
}

function sneeit_substr($string, $start, $length = 150) {	
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');
	mb_http_input();
	mb_language('uni');
	mb_regex_encoding('UTF-8');
	
	return mb_substr($string, $start, $length);
}

function sneeit_is_image_src($src) {
	$src = strtolower($src);
	return preg_match("/\.(jpeg|jpg|gif|png)$/", $src);
}