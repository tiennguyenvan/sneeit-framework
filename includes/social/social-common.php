<?php

add_filter('sneeit_social_link_to_fontawesome', 'sneeit_social_common_link_to_fontawesome');
/*
 * format can be: 
 * code, fa-code, fa fa-code, i-tag, a-tag, li-tag
 * Next features:
 * - if you want to use -o, just add -o into the end of format (soon)
 * - same if you want to use -square (soon)
 * - user can input (fa-code) into end or beginning of their link to specific icon
 * 
 */
function sneeit_social_common_link_to_fontawesome_action( $url = '', $format = '') {
	if (!$url) {		
		return;
	}
	
	$host = parse_url($url, PHP_URL_HOST);
	if (!$host) {		
		return;
	}
	
	
	$host = str_replace(array('www.', 'https', 'http', '://'), '', $host);
	
	// replace for some special cases
	$host = str_replace('plus.google', 'google-plus', $host);
	
	$host = explode('.', $host);
	if (count($host) == 3) {
		$code = $host[1];	
	} else {
		$code = $host[0];
	}
	
	
	if (!$format) {
		$format = 'a-tag';
	}
	
	$ret = '';
	switch ($format) {
		case 'code':$ret = $code;break;
		case 'fa-code':$ret = 'fa-'.$code;break;
		case 'fa fa-code':$ret = 'fa fa-'.$code;break;
		case 'i-tag':$ret = '<i class="fa fa-'.$code.'"></i>';break;
		case 'a-tag':$ret = '<a href="'.$url.'" target="_blank" rel="nofollow" title="'.esc_attr(ucfirst(str_replace('-', ' ', $code))).'"><i class="fa fa-'.$code.'"></i></a>';break;		
		case 'li-tag':$ret = '<li><a href="'.$url.'" target="_blank" rel="nofollow" title="'.esc_attr(ucfirst(str_replace('-', ' ', $code))).'"><i class="fa fa-'.$code.'"></i></a></li>';break;
		default:
			break;
	}
	
	return $ret;
	
}

add_filter('sneeit_social_links_to_fontawesome', 'sneeit_social_common_links_to_fontawesome');
/*
 * Links can be array of a textarea value which contains 1 link per lines
 */
function sneeit_social_common_links_to_fontawesome($args = array()) {
	
	if (is_string($args)) {		
		$args = array(
			'urls' => $args
		);		
	}	
	
	$args = wp_parse_args($args, array(
		'urls' => '', 		
		'before' => '',
		'after' => '',
		'before_link' => '',
		'after_link' => '',
	));	
	
	if (empty($args['urls'])) {
		return;
	}

	$urls = $args['urls'];		
		
	if (!is_array($urls)) {		
		$urls = explode(",", str_replace(array("\n", "\r\n"), ",", $urls));
	}	
	
		
	$ret = '';
	foreach ($urls as $url) {		
		$url = trim($url);
		$ret .= $args['before_link'] . sneeit_social_common_link_to_fontawesome_action($url) .$args['after_link'];		
	}
	
	
	$ret = $args['before'] . $ret . $args['after'];		
	return $ret;
}