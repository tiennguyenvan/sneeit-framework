<?php
/* return
 * - id (int) if found an attachment
 * - src of first image or youtube or video
 */
function sneeit_article_get_image_src($content = '') {	
	// DEFINES
	$src = '';
	
	// START SCANNING IMAGE
	// check if ocntent has image or not
	if (strpos($content, '<img ') !== false) {		
				
		$wp_image_class = (strpos($content, 'wp-image-') === false ? '' : 'wp-image-');	
		
		// we found an attachment image
		$wp_img = '';
		if (strpos($content, 'wp-image-') !== false) {
			$wp_img = explode('wp-image-', $content);
			$wp_img = sneeit_get_first_int_in_string($wp_img[1]);
			
			// check and return if this is an attachment IMAGE
			if (!empty($wp_img) && wp_attachment_is_image($wp_img)) {
				return $wp_img;
			}
		}
				
		// search images in content
		$src = explode('<img ', $content);
		$src = explode('src=', $src[1]);
		$src = $src[1];
		
		// don't change order, they are important
		$delimiter = array(
			'/>', '>', ' ', '"', '\'', '\t', '\n', '\r', '\0', '\x0B'
		);
		$src = trim($src, ' "\'\t\n\r\0\x0B');
		foreach ($delimiter as $value) {			
			$src = explode($value, $src);
			$src = $src[0];
		}
	} 	
	// if have no image, but youtube
	else if ( strpos( $content, 'youtube' ) !== false || strpos( $content, 'youtu.be' ) !== false ) {	
		$src = sneeit_get_youtube_image($content);
	}
	// or vimeo
	else if ( strpos( $content, 'vimeo' ) !== false ) {
		$src = sneeit_get_vimeo_image($content);		
	} // end of check content
	
	return $src;
}


/* we don't care about size,
 * If an image from media lib, we have srcset and js will handle
 * If an image from some where, we output the src directly
 */
function sneeit_article_get_post_image($post_id = 0, $priority_content = '', $size = 'post-thumbnail', $attr = array()) {
	// DEFINE
	$html = '';
	$src = '';	
	static $cache = array();
	
	// CHECK IN CACHE FIRST
	if (isset($cache[$post_id.$size])) {
		return $cache[$post_id.$size];
	}
	
	// IF HAVE THUMBNAIL
	if (has_post_thumbnail( $post_id ) ) {
		$cache[$post_id.$size] = get_the_post_thumbnail( $post_id, $size, $attr );
		return $cache[$post_id.$size];
	}
	
	// CHECK IF HAVE FEATURE MEDIA FIELD	
	if ($priority_content) {
		$src = sneeit_get_youtube_image($priority_content);
		if (!$src) {
			$src = sneeit_get_vimeo_image($priority_content);
		}		
	}
	
	if (!$src) {
		// NOW, WE MUST SCAN THE FIRST IMAGE
		if (!isset($ret[$post_id])) {
			return $ret[$post_id];
		}	
		$src = sneeit_article_get_image_src(get_the_content());
		
		// found an attachment id
		if (is_numeric($src)) {
			$cache[$post_id] = wp_get_attachment_image($src, $size, false, $attr);
			return $cache[$post_id];
		}
	}
	
	if ( $src ) {
		// maybe external image or not in library
		$html = '<img src="' . esc_url( $src ) . '"';
		foreach ( $attr as $key => $value ) {
			$html .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}
		$html .= '/>';
		
		$cache[$post_id.$size] = $html;
	}
	
	return $html;
}


add_filter('sneeit_articles_archive_link', 'sneeit_articles_archive_link');
// this function args will compatible with article query fields
function sneeit_articles_archive_link($args = array()) {
	$args = wp_parse_args($args, array(
		'categories' => '',		
		'authors' => '',
		'tags' => '',
		'paged' => 1,
		'post_type' => 'post'
	));
	
	// generate link
	$link = '';
	// base on categories
	if (!empty($args['categories'])) {
		if ($link) {
			$link .= '&';
		} else {
			$link .= '?';
		}
		$link .= 'cat='.$args['categories'];		
	}	
	// base on authors
	if (!empty($args['authors'])) {
		if ($link) {
			$link .= '&';
		} else {
			$link .= '?';
		}
		$link .= 'author='.$args['authors'];
	}
	// base on tags
	if (!empty($args['tags'])) {
		
		$tag_ids = explode(',', $args['tags']);
		$tag_link = '';
		foreach ($tag_ids as $tag_id) {
			$tag = get_tag($tag_id);
			if (!is_wp_error($tag)) {
				if ($tag_link) {
					$tag_link .= ',';
				}
				$tag_link .= $tag->slug;
			}			
		}
		if ($tag_link) {
			if ($link) {
				$link .= '&';
			} else {
				$link .= '?';
			}
			$link .= 'tags=' .$tag_link;
		}
	}	
	
	$paged = '';
	if (!empty($args['paged']) && is_numeric($args['paged'])) {
		$args['paged'] = (int) $args['paged'];
		
		if ($args['paged'] > 1) {			
			$paged = '&paged=' . $args['paged'];
		}
	}
	
	$post_type = '';
	if (!empty($args['post_type'])) {
		$post_type = '&post_type=' . $args['post_type'];		
	}
	$orderby = '';
	if (!empty($args['orderby'])) {
		if ('random' == $args['orderby']) {
			$orderby = '&orderby=rand';
		}
	}
	
	
	// in case not found anything
	// just link to recent post
	if ($link) {
		$link = get_home_url() . $link . $paged . $post_type . $orderby;
	} else {
		$link = get_home_url() . '?s'.$paged . $post_type . $orderby;
	}
	return $link;
}
