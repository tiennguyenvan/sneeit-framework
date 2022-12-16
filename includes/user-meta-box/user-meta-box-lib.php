<?php

function sneeit_validate_user_meta_box_declaration($declaration) {
	if (!is_array($declaration)) {
		return array();
	}
	foreach ($declaration as $user_meta_box_id => $user_meta_box_declaration) {
		if (!isset($declaration[$user_meta_box_id]['id'])) {
			$declaration[$user_meta_box_id]['id'] = $user_meta_box_id;
		}
		if (!isset($declaration[$user_meta_box_id]['title'])) {
			$declaration[$user_meta_box_id]['title'] = sneeit_slug_to_title($user_meta_box_id);
		}
		if (!isset($declaration[$user_meta_box_id]['description'])) {
			$declaration[$user_meta_box_id]['description'] = '';
		}
		if (!isset($declaration[$user_meta_box_id]['fields'])) {
			$declaration[$user_meta_box_id]['fields'] = array();
		}				
		foreach ($declaration[$user_meta_box_id]['fields'] as $user_meta_box_field_id => $user_meta_box_field_declaration) :
			if (!isset($declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['label'])) {
				$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['label'] = sneeit_slug_to_title($user_meta_box_field_id);
			}			
			if (!isset($declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['description'])) {
				if ($user_meta_box_field_declaration['type'] == 'checkbox') {
					$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['description'] = sneeit_slug_to_title($user_meta_box_field_id);
				} else {
					$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['description'] = '';
				}
				
			}
			
			if (!isset($declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['type'])) {
				$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['type'] = 'text';
			}
			if (!isset($declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['default'])) {
				if (	$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['type'] == 'number'
					) {
					$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['default'] = 0;
				} else {
					$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['default'] = '';
				}
			} else {
				if ($declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['type'] == 'textarea') {
					if ( !current_user_can('unfiltered_html') ) {
						$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['default'] 
								=	stripslashes( 
										wp_filter_post_kses( 
												addslashes(
														$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['default']
												) 
										)
									);
					}
				}
			}
			if ((	
					$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['type'] == 'radio' || 
					$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['type'] == 'select'
				) && 
				!isset($declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['choices'])) {				
				$declaration[$user_meta_box_id]['fields'][$user_meta_box_field_id]['choices'] = array();
			}
		endforeach;
	}
	return $declaration;
}

