<?php

add_action('sneeit_optimize_images', 'sneeit_optimize_images');
function sneeit_optimize_images() {
	add_action( 'wp_enqueue_scripts', 'sneeit_optimize_images_enqueue', 1 );
	add_filter( 'sneeit_articles_get_post_image_before', 'sneeit_optimize_attachment_add');
	add_filter( 'sneeit_articles_get_post_image_after', 'sneeit_optimize_attachment_remove');
}

function sneeit_optimize_images_enqueue() {	
	wp_enqueue_style( 'sneeit-optimize-images', 
		sneeit_front_enqueue_url('front-optimize-images.css'), 
		array(), 
		SNEEIT_PLUGIN_VERSION 
	);
	wp_enqueue_script( 'sneeit-optimize-images', 
		sneeit_front_enqueue_url('front-optimize-images.js'),		
		array( 'jquery'), 
		SNEEIT_PLUGIN_VERSION, 
		true );
	
	wp_localize_script( 'sneeit-optimize-images', 'sneeit_optimize_img', array(
		'use_smaller_thumbnails' => (sneeit_is_gpsi() || wp_is_mobile())
	));	
}
function sneeit_optimize_attachment_add($ret) {
	add_filter( 'wp_get_attachment_image_attributes', 'sneeit_optimize_images_get_attachment');
	return $ret;
}
function sneeit_optimize_attachment_remove($ret) {
	remove_filter('wp_get_attachment_image_attributes', 'sneeit_optimize_images_get_attachment');
	return $ret;
}

/*in case need optimize src*/
function sneeit_optimize_images_get_attachment($attr) {
	if (!empty($attr['src'])) {
		$attr['data-s'] = $attr['src'];
		$attr['src'] = 'data:image/gif;base64,';
	}
	if (!empty($attr['srcset'])) {
		$attr['data-ss'] = $attr['srcset'];
		unset($attr['srcset']);
	}		
	if (!empty($attr['sizes'])) {		
		unset($attr['sizes']);
	}
	return $attr;
}
