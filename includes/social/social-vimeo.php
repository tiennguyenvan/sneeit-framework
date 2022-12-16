<?php

// parse FIRST vimeo video ID in any $content
add_filter('sneeit_get_vimeo_id', 'sneeit_get_vimeo_id', 1, 1);
function sneeit_get_vimeo_id($content = '') {
	$vimeo_id = '';
	
	if (strlen($content)) {		
		// search and get vimeo ID
		$key = '//player.vimeo.com/video/';
		$start = strpos($content, $key);		
		if ($start === false) {
			$key = 'vimeo.com/';
			$start = strpos($content, $key);
		}
		if ($start !== false) {
			for ($i = $start + strlen($key); $i < strlen($content); $i++) {
				if (!sneeit_is_slug_name_character($content[$i])) {
					break;
				}
			}
			if ($i <= strlen($content)) {
				$vimeo_id = substr($content, $start + strlen($key), $i - ($start + strlen($key)));
				if (strlen($vimeo_id) > SNEEIT_MAX_VIMEO_VIDEO_ID_LENGTH) {
					$vimeo_id = '';
				}
			}
		}
	}
	
	return $vimeo_id;
}


// $code can be anything: full embed player, url, id, ...
add_filter('sneeit_get_vimeo_player', 'sneeit_get_get_vimeo_player', 1, 3);
function sneeit_get_get_vimeo_player($code, $width = 960, $height = 540) {
	$vimeo_id = sneeit_get_vimeo_id($code);
	$vimeo_player = '';
	
	if ($vimeo_id) {
		$vimeo_player = '<iframe src="https://player.vimeo.com/video/'.$vimeo_id.'" width="'.$width.'" height="'.$height.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	}
	
	return $vimeo_player;
}

function sneeit_get_vimeo_image($content) {
	if ( strpos( $content, 'vimeo' ) === false ) {
		return '';
	}
	// search and get vimeo ID
	$vimeo_id = sneeit_get_vimeo_id($content);
	
	if (!$vimeo_id) {			
		return '';
	}

	// check if we saved in cache
	$src = get_transient('sneeit_vimeo_thumb_'.$vimeo_id);
	if (!empty($src) && sneeit_is_image_src($src)) {		
		return $src;
	}	

	// if we can not found in cache, 
	// load vimeo thumbnail via API
	$vimeo_thumb_xml = wp_remote_get(esc_url('https://vimeo.com/api/v2/video/'.$vimeo_id.'.php'), array( 
		'sslverify' => false, 
		'compress'    => false,
		'decompress'  => false,
		'timeout'	=> SNEEIT_REMOTE_TIMEOUT)
	);

	// fail remote
	if ( is_wp_error($vimeo_thumb_xml) ) {
		// try to get in option
		$src = get_option('sneeit_vimeo_thumb_'.$vimeo_id, '');
		if (!empty($src) && sneeit_is_image_src($src)) {		
			return $src;
		}
		return $src;
	}
	$hash = unserialize(wp_remote_retrieve_body($vimeo_thumb_xml));
	$src = $hash[0]['thumbnail_large'];
	
	// update cache	
	set_transient('sneeit_vimeo_thumb_'.$vimeo_id, $src, 60*60*24*365);
	update_option('sneeit_vimeo_thumb_'.$vimeo_id, $src);				
	
	return ($src);	
}