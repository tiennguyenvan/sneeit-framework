<?php
// $code can be anything: full tag, full icon code, combined icon code, short icon code, ...
add_filter('sneeit_get_font_awesome_tag', 'sneeit_font_awesome_tag', 1, 1);
add_filter('sneeit_font_awesome_tag', 'sneeit_font_awesome_tag', 1, 1);
add_filter('sneeit_get_fontawesome_tag', 'sneeit_font_awesome_tag', 1, 1);
add_filter('sneeit_fontawesome_tag', 'sneeit_font_awesome_tag', 1, 1);
function sneeit_font_awesome_tag($code) {
	// validate code
	$code = trim(strtolower($code));
	$n0 = ord('0');
	$n9 = ord('9');
	$a  = ord('a');
	$z  = ord('z');
//	$A  = ord('A');
//	$Z  = ord('Z');
	$m  = ord('-');
	$s  = ord(' ');
//	$u  = ord('_');
	
	/* replace all none allowed characters to _ */
	for ($i = 0; $i < strlen($code); $i++) {
		$c = ord((string) $code[$i]);
		
		/* this is a valid character */
		if ($c >= $n0 && $c <= $n9 ||
			$c >= $a && $c <= $z ||
			$c == $m || $c == $s) {
			continue;
		}
		$code = substr($code, 0, $i).'_'.substr($code, $i+1);
	}
	
	$code = 'fa-'.implode(' fa-', explode(' ', trim(str_replace(array('fa-', 'fa_', 'fa ', '_'), '', $code))));
		
	// generate
	return '<i class="fa '.$code.'"></i>';
}
function sneeit_get_dashicons_tag($code) {
	// validate code
	$code = strtolower($code);
	$n0 = ord('0');
	$n9 = ord('9');
	$a  = ord('a');
	$z  = ord('z');
	$A  = ord('A');
	$Z  = ord('Z');
	$m  = ord('-');
	$u  = ord('_');
	for ($i = 0; $i < strlen($code); $i++) {
		$c = ord((string) $code[$i]);
		if ($c >= $n0 && $c <= $n9 ||
			$c >= $a && $c <= $z ||
			$c >= $A && $c <= $Z ||
			$c == $m || $c == $u) {
			continue;
		}
		$code = substr($code, 0, $i).'#'.substr($code, $i+1);
	}
	
	$code = str_replace('#', '', $code);
	$code = str_replace('dashicons-', '', $code);
	$code = 'dashicons-' . str_replace('dashicons', '', $code);
	
	// generate
	return '<i class="dashicons '.$code.'"></i>';
}

add_action('sneeit_grid', 'sneeit_grid');
function sneeit_grid() {
	add_action( 'wp_enqueue_scripts', 'sneeit_grid_enqueue', 1 );	
}

function sneeit_grid_enqueue() {		
	wp_enqueue_script( 'sneeit-grid', 
		sneeit_front_enqueue_url('front-grid.js'),		
		array( 'jquery'), 
		SNEEIT_PLUGIN_VERSION, 
		true 
	);
}


add_action('sneeit_carousel', 'sneeit_carousel');
function sneeit_carousel() {
	add_action( 'wp_enqueue_scripts', 'sneeit_carousel_enqueue', 1 );	
}

function sneeit_carousel_enqueue() {		
	wp_enqueue_script( 'sneeit-carousel', 
		sneeit_front_enqueue_url('front-carousel.js'),		
		array( 'jquery'), 
		SNEEIT_PLUGIN_VERSION, 
		true 
	);
}
add_filter('sneeit_is_localhost', 'sneeit_is_localhost', 1, 0);
function sneeit_is_localhost() {
	return 
	( 
		(
			!empty($_SERVER['HTTP_REFERER']) &&
			strpos($_SERVER['HTTP_REFERER'], '://localhost/') !== false
		)
		||
		(
			!empty($_SERVER['REDIRECT_SCRIPT_URI']) &&
			strpos($_SERVER['REDIRECT_SCRIPT_URI'], '://localhost/') !== false
		)
		||
		(
			!empty($_SERVER['SCRIPT_URI']) &&
			strpos($_SERVER['SCRIPT_URI'], '://localhost/') !== false
		)
		||
		(
			!empty($_SERVER['SERVER_ADDR']) &&
			!empty($_SERVER['REMOTE_ADDR']) &&
			$_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'] &&
			$_SERVER['SERVER_ADDR'] == '127.0.0.1'
		)		
	);
}
