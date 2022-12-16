<?php
function sneeit_utilities_ie_wp_head() {
	echo '<!--[if lt IE 9]>';
	echo '<script src="'.SNEEIT_PLUGIN_URL_JS.'html5.js"></script>';
	echo '<![endif]-->';
}
function sneeit_utilities_ie_support_ie_html5() {
	add_action( 'wp_head', 'sneeit_utilities_ie_wp_head');
}
add_action('sneeit_support_ie_html5', 'sneeit_utilities_ie_support_ie_html5');