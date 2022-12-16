<?php
/* TODO:
- check with image for icon, make sure display properly for buttons, item, shortcode button
- check if browser supports HTML5 or not, if not, require to download chrome
*/
global $Sneeit_PageBuilder_Declaration;
$Sneeit_PageBuilder_Declaration=array();
function sneeit_page_builder_init_setup_page_builder($declaration = array(
	'nested' => false
)) {
	global $Sneeit_PageBuilder_Declaration;
	$Sneeit_PageBuilder_Declaration = $declaration;
	require_once 'page-builder-enqueue.php';
}
add_action('sneeit_setup_page_builder', 'sneeit_page_builder_init_setup_page_builder', 2, 1);