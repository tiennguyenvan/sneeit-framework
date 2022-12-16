<?php

function sneeit_validate_shortcodes_field_declaration($shortcodes_field_id, $shortcodes_field_declaration) {
	if (!isset($shortcodes_field_declaration['type'])) {
		$shortcodes_field_declaration['type'] = 'text';
	}
	if (!isset($shortcodes_field_declaration['label'])) {
		$shortcodes_field_declaration['label'] = sneeit_slug_to_title($shortcodes_field_id);
	}
	if (!isset($shortcodes_field_declaration['default'])) {
		if ($shortcodes_field_declaration['type'] == 'number') {
			$shortcodes_field_declaration['default'] = 0;
		} else {
			$shortcodes_field_declaration['default'] = '';
		}
	} else {
		if ($shortcodes_field_declaration['type'] == 'textarea') {
			if ( !current_user_can('unfiltered_html') ) {
				$shortcodes_field_declaration['default'] = stripslashes( 
					wp_filter_post_kses( 
						addslashes($shortcodes_field_declaration['default']) 
					)
				);
			}
		}
		
		if ($shortcodes_field_declaration['type'] == 'checkbox' &&
			isset($shortcodes_field_declaration['default'])) {
			if ($shortcodes_field_declaration['default']) {
				$shortcodes_field_declaration['default'] = 'on';
			} else {
				$shortcodes_field_declaration['default'] = '';
			}
		}
	}
	if ((	$shortcodes_field_declaration['type'] == 'radio' 
			|| $shortcodes_field_declaration['type'] == 'select' ) 
		&& (!isset($shortcodes_field_declaration['choices'])) ) {
		$shortcodes_field_declaration['choices'] = array();
	}
	
	return $shortcodes_field_declaration;
}
function sneeit_validate_shortcodes_header_declaration($shortcodes_id, $shortcodes_declaration) {
	if (!isset($shortcodes_declaration['id'])) {
		$shortcodes_declaration['id'] = $shortcodes_id;
	}
	if (!isset($shortcodes_declaration['title'])) {
		$shortcodes_declaration['title'] = sneeit_slug_to_title($shortcodes_id);
	}
	if (!isset($shortcodes_declaration['fields'])) {
		$shortcodes_declaration['fields'] = array();
	}
	return $shortcodes_declaration;
}
function sneeit_validate_shortcodes_declaration($declaration) {
	if (!is_array($declaration)) {
		return array();
	}
	foreach ($declaration as $shortcodes_id => $shortcodes_declaration) {
		// check basic things
		$declaration[$shortcodes_id] 
			= sneeit_validate_shortcodes_header_declaration($shortcodes_id, $shortcodes_declaration);		
		
		// valide for column shortcode, special shortcode for page builder
		if ($shortcodes_id == 'column') {
			// the column must has width field
			if (isset($declaration[$shortcodes_id]['fields']['width'])) {
				$declaration[$shortcodes_id]['fields']['width']['type'] = 'number';
			} else {
				$declaration[$shortcodes_id]['fields']['width'] = array(
					'type' => 'number',
					'label' => __('Column width in percent (%)', 'sneeit'),
					'default' => 100
				);
			}
			
			// the column can not has nested shortcode
			if (isset($declaration[$shortcodes_id]['nested'])) {
				unset($declaration[$shortcodes_id]['nested']);
			}			
		}
		
		// a shortcode id has nested will not allow has content field
		if (isset($declaration[$shortcodes_id]['nested'])) {
			foreach ($declaration[$shortcodes_id]['fields'] as $shortcodes_field_id => $shortcodes_field_declaration) :			
				if (isset($shortcodes_field_declaration['type']) && $shortcodes_field_declaration['type'] == 'content') {					
					unnset($declaration[$shortcodes_id]['fields'][$shortcodes_field_id]);
				}				
			endforeach;
		}
		
		// a shortcode has only ONE content field
		$had_content_field = false;
		foreach ($declaration[$shortcodes_id]['fields'] as $shortcodes_field_id => $shortcodes_field_declaration) :
			if (isset($shortcodes_field_declaration['type']) && $shortcodes_field_declaration['type'] == 'content') {
				if ($had_content_field) {
					unset($declaration[$shortcodes_id]['fields'][$shortcodes_field_id]);
				} else {
					$had_content_field = true;
				}				
			}				
		endforeach;
		
		
		// check shortcode field declaration
		foreach ($declaration[$shortcodes_id]['fields'] as $shortcodes_field_id => $shortcodes_field_declaration) :			
			$declaration[$shortcodes_id]['fields'][$shortcodes_field_id] 
				=  sneeit_validate_shortcodes_field_declaration($shortcodes_field_id, $shortcodes_field_declaration);				
		endforeach;
		
		// validate for nested shortcodes
		if (isset($declaration[$shortcodes_id]['nested'])) {
			// check basic thing of nested shortcode
			foreach ($declaration[$shortcodes_id]['nested'] as $nested_shortcodes_id => $nested_shortcodes_declaration) {
				// nested shortcode header
				$declaration[$shortcodes_id]['nested'][$nested_shortcodes_id] 
					= sneeit_validate_shortcodes_header_declaration($nested_shortcodes_id, $nested_shortcodes_declaration);
				
				// nested shortcode can not have nested again
				if (isset($declaration[$shortcodes_id]['nested'][$nested_shortcodes_id]['nested'])) {
					unset($declaration[$shortcodes_id]['nested'][$nested_shortcodes_id]['nested']);
				}
				
				// nested shortcode can also has only ONE content field
				$had_content_field = false;
				foreach ($declaration[$shortcodes_id]['nested'][$nested_shortcodes_id]['fields'] 
						as $nested_shortcodes_field_id => $nested_shortcodes_field_declaration) :
					if (isset($nested_shortcodes_field_declaration['type']) && $nested_shortcodes_field_declaration['type'] == 'content') {
						if ($had_content_field) {
							unnset($declaration[$shortcodes_id]['nested'][$nested_shortcodes_id]['fields'][$nested_shortcodes_field_id]);
						} else {
							$had_content_field = true;
						}				
					}				
				endforeach;

				// nested shortcode fields
				foreach ($declaration[$shortcodes_id]['nested'][$nested_shortcodes_id]['fields']  as $nested_shortcodes_field_id => $nested_shortcodes_field_declaration) :			
					$declaration[$shortcodes_id]['nested'][$nested_shortcodes_id]['fields'][$nested_shortcodes_field_id] 
						=  sneeit_validate_shortcodes_field_declaration($nested_shortcodes_field_id, $nested_shortcodes_field_declaration);				
				endforeach;

			}			
		}
	}
	return $declaration;
}


