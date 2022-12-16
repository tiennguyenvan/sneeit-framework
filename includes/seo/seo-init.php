<?php
/*this only affect if you using wp_title*/
add_action('sneeit_optimized_wp_title', 'sneeit_seo_init_optimized_wp_title', 1);
function sneeit_custom_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() ) {
		return $title;
	}

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title = "$title $sep $site_description";
	}

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 ) {
		$title = "$title $sep " . __('Page' ,'sneeit') . ' ' . max( $paged, $page );
	}
	return $title;
}
function sneeit_seo_init_optimized_wp_title() {
	add_filter( 'wp_title', 'sneeit_custom_wp_title', 10, 2 );	
}

