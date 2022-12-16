<?php

function sneeit_validate_post_meta_box_declaration($declaration) {
	if (!is_array($declaration)) {
		return array();
	}
	foreach ($declaration as $post_meta_box_id => $post_meta_box_declaration) {
		if (!isset($declaration[$post_meta_box_id]['id'])) {
			$declaration[$post_meta_box_id]['id'] = $post_meta_box_id;
		}
		if (!isset($declaration[$post_meta_box_id]['title'])) {
			$declaration[$post_meta_box_id]['title'] = sneeit_slug_to_title($post_meta_box_id);
		}
		if (!isset($declaration[$post_meta_box_id]['description'])) {
			$declaration[$post_meta_box_id]['description'] = '';
		}
		if (!isset($declaration[$post_meta_box_id]['context'])) {
			$declaration[$post_meta_box_id]['context'] = 'advanced';
		}
		if (!isset($declaration[$post_meta_box_id]['fields'])) {
			$declaration[$post_meta_box_id]['fields'] = array();
		}				
		foreach ($declaration[$post_meta_box_id]['fields'] as $post_meta_box_field_id => $post_meta_box_field_declaration) :
			if (!isset($declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['label'])) {
				$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['label'] = sneeit_slug_to_title($post_meta_box_field_id);
			}
			if (!isset($declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['type'])) {
				$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['type'] = 'text';
			}
			if (!isset($declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['default'])) {
				if (	$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['type'] == 'number'
					) {
					$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['default'] = 0;
				} else {
					$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['default'] = '';
				}
			} else {
				if ($declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['type'] == 'textarea') {
					if ( !current_user_can('unfiltered_html') ) {
						$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['default'] 
								=	stripslashes( 
										wp_filter_post_kses( 
												addslashes(
														$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['default']
												) 
										)
									);
					}
				}
			}
			if ((	
					$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['type'] == 'radio' || 
					$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['type'] == 'select'
				) && 
				!isset($declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['choices'])) {				
				$declaration[$post_meta_box_id]['fields'][$post_meta_box_field_id]['choices'] = array();
			}
		endforeach;
	}
	return $declaration;
}

