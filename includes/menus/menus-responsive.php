<?php
/*

'selector' => '.fn-top-menu-wrapper',
'min_width' => 'flex_half', // flex or a specific integer number. Flex mean if total width of ul.menu > li larger than window width, responsive will be activated. flex_half is about window width / 2
'fold' => comming soon: left, right, top, bottom, left-absolute, right-absolute, top-absolute, bottom-absolute,
'text' => wp_kses(__('<i class="fa fa-bars"></i> TOP MENU', 'flatnews'), array(
	'i' => array(
		'class'
	)
)),
 */
add_action('sneeit_setup_responsive_menus', 'sneeit_setup_responsive_menus');
function sneeit_setup_responsive_menus($args) {
	global $Sneeit_Setup_Responsive_Menus;
	$Sneeit_Setup_Responsive_Menus = $args;
	add_action( 'wp_enqueue_scripts', 'sneeit_setup_responsive_menus_enqueue', 1 );
}

function sneeit_setup_responsive_menus_enqueue() {
	global $Sneeit_Setup_Responsive_Menus;
	
	$rtl = '';
	if (is_rtl()) {
		$rtl = '-rtl';
	}	
	wp_enqueue_style( 'sneeit-responsive-menus', SNEEIT_PLUGIN_URL_CSS . 'menus-responsive'.$rtl.SNEEIT_MIN_ENQUEUE.'.css', array(), SNEEIT_PLUGIN_VERSION );
	wp_enqueue_script( 'sneeit-responsive-menus', SNEEIT_PLUGIN_URL_JS . 'menus-responsive'.$rtl.SNEEIT_MIN_ENQUEUE.'.js', array( 'jquery'), SNEEIT_PLUGIN_VERSION, true );	
	
	wp_localize_script( 'sneeit-responsive-menus', 'Sneeit_Responsive_Menus', $Sneeit_Setup_Responsive_Menus);
}