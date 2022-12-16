<?php
function sneeit_utilities_blogger_blogspot_body_class($classes){
	if (is_home() || is_front_page()) {
		$classes[] = 'home';
	} else if (is_single()){
		$classes[] = 'item';
	} else if (is_page()){
		$classes[] = 'static_page';
	} else if (is_404()) {
		$classes[] = 'error_page';
	} else if (is_date() || is_month() || is_year() || is_author()) {
		$classes[] = 'archive';
	} else {
		$classes[] = 'index';
	}
	return $classes;
}
function sneeit_utilities_blogger_blogspot_apply_blogspot_body_class () {
	add_filter('body_class', 'sneeit_utilities_blogger_blogspot_body_class', 1, 1);
}
add_action('sneeit_apply_blogspot_body_class', 'sneeit_utilities_blogger_blogspot_apply_blogspot_body_class', 1);