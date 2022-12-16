<?php

/*
short code must have more fields, special similar with widgets fields.
 * https://www.wpexplorer.com/wordpress-tinymce-tweaks/
 *  */

global $Sneeit_ShortCodes;
$Sneeit_ShortCodes = array();

// add filter to nested shortcode
function sneeit_parse_content_for_nested_shortcodes($content){        
	$matches=array();
	$subject = $content;
	$pattern = '/\[column.*?(\[column.*?\].*?\[\/column\])/';
	preg_match_all($pattern, $subject, $matches);
	if(count($matches)>0 && isset($matches[1])){
		foreach($matches[1] as $regmatch){
			$content = str_replace($regmatch, stripslashes(do_shortcode($regmatch)), $content);
		}
	}

	return $content;
}

add_action('sneeit_setup_shortcodes', 'sneeit_setup_shortcodes', 1, 1);
function sneeit_setup_shortcodes($args) {
	
	if (!isset($args['declarations'])) {
		return;
	}
	global $Sneeit_ShortCodes;
	$Sneeit_ShortCodes = $args;
	
	require_once 'shortcodes-lib.php';
	
	$Sneeit_ShortCodes['declarations'] = sneeit_validate_shortcodes_declaration($Sneeit_ShortCodes['declarations']);
	
	foreach ($Sneeit_ShortCodes['declarations'] as $shortcode_id => $shortcode_declaration) {
		if (isset($shortcode_declaration['display_callback'])) {
			add_shortcode($shortcode_id, $shortcode_declaration['display_callback']);
			
			// add process for unlimited nested shortcode
			if ($shortcode_id == 'column') {
				for ($i = 0; $i < SNEEIT_MAX_NESTED_COLUMN_LEVEL; $i++) {
					add_shortcode($shortcode_id.'-'.SNEEIT_NESTED_COLUMN_SEPARATOR.'-'.$i, $shortcode_declaration['display_callback']);
				}	
				
				add_filter( "the_content", 'sneeit_parse_content_for_nested_shortcodes' ); 
			}
			
			// for nested callback
			if (isset($shortcode_declaration['nested'])) {
				foreach ($shortcode_declaration['nested'] as $nested_shortcode_id => $nested_shortcode_declaration) {
					if (isset($nested_shortcode_declaration['display_callback'])) {
						add_shortcode($nested_shortcode_id, $nested_shortcode_declaration['display_callback']);
					}
				}
			}
		}
	}
	
	include_once 'shortcodes-enqueue.php';	
	add_action( 'admin_init', 'sneeit_shortcodes_init_admin_init');
	

	/* Advance Tinymce */
	add_action( 'init', 'sneeit_advance_tinymce' );
	
	// init elementor when have front script
	
	if (empty($Sneeit_ShortCodes['script'])) {
		return;		
	}
	include_once 'elementor/sneeit-elementor.php';
	
	// Instantiate Elementor_Hello_World.
	new Sneeit_Elementor($Sneeit_ShortCodes);
}

/**
 * Advance Tinymce
 */
function sneeit_advance_tinymce(){

	/* Add the button/option in second row */
	add_filter( 'mce_buttons_2', 'sneeit_advance_tinymce_buttons', 1, 2 ); // 2nd row
}
function sneeit_advance_tinymce_buttons( $buttons, $id ){

	/* Only add this for content editor, you can remove this line to activate in all editor instance */
//	if ( 'content' != $id ) return $buttons;

	/* Add the button/option after 4th item */
	array_splice( $buttons, 4, 0, 'backcolor' );
    array_splice( $buttons, 13, 0, 'wp_page' );
   
	return $buttons;
}

// add user interface for shortcode
function sneeit_shortcodes_init_admin_init() {
	if ( current_user_can( 'edit_posts' )) { 
		add_filter( 'mce_buttons', 'sneeit_mce_shortcode_buttons' );
		add_filter( 'mce_external_plugins', 'sneeit_mce_shortcode_plugins');
	}
}

// show shortcode button on tmce
function sneeit_mce_shortcode_buttons( $buttons ) {
	global $Sneeit_ShortCodes;

	if (empty($Sneeit_ShortCodes)) {
		return;
	}
	
	if (!empty($Sneeit_ShortCodes['title'])) {
		array_push( $buttons, 'sneeit-shortcode-dropdown');
		return $buttons;
	}

	foreach ($Sneeit_ShortCodes['declarations'] as $shortcode_id => $shortcode_declaration) {
		if (isset($shortcode_declaration['icon']) && 
			isset($shortcode_declaration['display_callback']) &&
			$shortcode_id != 'column') {
			// only display shortcodes which have icon define for mce
			array_push( $buttons, $shortcode_id );
		}		
	}
	
	return $buttons;
}

function sneeit_mce_shortcode_plugins( $plugins ) {
	// this plugin file will work the magic of our button
	$plugins['sneeit_shortcodes'] = SNEEIT_PLUGIN_URL_JS . 'shortcodes.js'; /*index must use underscore*/
	return $plugins;
}

include_once 'shortcodes-ajax.php';