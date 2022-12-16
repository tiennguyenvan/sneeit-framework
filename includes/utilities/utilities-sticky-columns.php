<?php
add_action('sneeit_sticky_columns', 'sneeit_sticky_columns');
function sneeit_sticky_columns($selector) {
	if (empty($selector)) {
		return;
	}
	global $Sneeit_Sticky_Columns;
	$Sneeit_Sticky_Columns = $selector;
	
	add_action('wp_enqueue_scripts', 'sneeit_sticky_columns_enqueue');		
}

function sneeit_sticky_columns_enqueue() {
	global $Sneeit_Sticky_Columns;
	$rtl = '';
	if (is_rtl()) {
		$rtl = '-rtl';
	}		
	if (is_string($Sneeit_Sticky_Columns)) {
		$Sneeit_Sticky_Columns = array($Sneeit_Sticky_Columns);
	}

	wp_enqueue_script('sneeit-sticky-columns', sneeit_front_enqueue_url('front-sticky-columns.js'), array('jquery'), SNEEIT_PLUGIN_VERSION, true);
	wp_localize_script('sneeit-sticky-columns', 'Sneeit_Sticky_Columns', $Sneeit_Sticky_Columns);
}