<?php

function sneeit_validate_sidebars_declaration($declaration) {
	if (!is_array($declaration)) {
		return array();
	}
	foreach ($declaration as $sidebar_id => $sidebar_declaration) {
		if (!isset($sidebar_declaration['id'])) {
			$declaration[$sidebar_id]['id'] = $sidebar_id;
		}
		if (!isset($sidebar_declaration['name'])) {
			$declaration[$sidebar_id]['name'] = sneeit_slug_to_title($sidebar_id);
		}
		if (!isset($sidebar_declaration['description'])) {
			$declaration[$sidebar_id]['description'] = '';
		}
		if (!isset($sidebar_declaration['class'])) {
			$declaration[$sidebar_id]['class'] = $sidebar_id;
		}		
		if (!isset($sidebar_declaration['before_widget'])) {
			$declaration[$sidebar_id]['before_widget'] = '<div id="%1$s" class="widget %2$s"><div class="alt-widget-content">';
		}		
		if (!isset($sidebar_declaration['after_widget'])) {
			$declaration[$sidebar_id]['after_widget'] = '<div class="clear"></div></div></div>';
		}		
		if (!isset($sidebar_declaration['before_title'])) {
			$declaration[$sidebar_id]['before_title'] = '</div><h2 class="widget-title"><span class="widget-title-content">';
		}		
		if (!isset($sidebar_declaration['after_title'])) {
			$declaration[$sidebar_id]['after_title'] = '</span></h2><div class="clear"></div><div class="widget-content">';
		}		
	}
	return $declaration;
}
function sneeit_validate_custom_sidebars_declaration($declaration) {
	if (!is_array($declaration)) {
		return array();
	}
	foreach ($declaration as $sidebar_id => $sidebar_declaration) {
		if (!isset($sidebar_declaration['prefix_id'])) {
			$declaration[$sidebar_id]['prefix_id'] = $sidebar_id;
		}
		if (!isset($sidebar_declaration['name'])) {
			$declaration[$sidebar_id]['name'] = sneeit_slug_to_title($sidebar_id);
		}		
		if (!isset($sidebar_declaration['class'])) {
			$declaration[$sidebar_id]['class'] = '';
		}
		if (!isset($sidebar_declaration['before_widget'])) {
			$declaration[$sidebar_id]['before_widget'] = '<li id="%1$s" class="widget %2$s">';
		}		
		if (!isset($sidebar_declaration['after_widget'])) {
			$declaration[$sidebar_id]['after_widget'] = '</li>';
		}		
		if (!isset($sidebar_declaration['before_title'])) {
			$declaration[$sidebar_id]['before_title'] = '<h2 class="widgettitle">';
		}		
		if (!isset($sidebar_declaration['after_title'])) {
			$declaration[$sidebar_id]['after_title'] = '</h2>';
		}
	}
	return $declaration;
}
function sneeit_validate_widgets_declaration($declaration) {
	if (!is_array($declaration)) {
		return array();
	}
	foreach ($declaration as $widget_id => $widget_declaration) {
		if (!isset($declaration[$widget_id]['id'])) {
			$declaration[$widget_id]['id'] = $widget_id;
		}
		if (!isset($declaration[$widget_id]['title'])) {
			$declaration[$widget_id]['title'] = sneeit_slug_to_title($widget_id);
		}
		if (!isset($declaration[$widget_id]['description'])) {
			$declaration[$widget_id]['description'] = '';
		}
		if (!isset($declaration[$widget_id]['fields'])) {
			$declaration[$widget_id]['fields'] = array();
		}				
		foreach ($declaration[$widget_id]['fields'] as $widget_field_id => $widget_field_declaration) :
			if (!isset($declaration[$widget_id]['fields'][$widget_field_id]['type'])) {
				$declaration[$widget_id]['fields'][$widget_field_id]['type'] = 'text';
			}
			if (!isset($declaration[$widget_id]['fields'][$widget_field_id]['label'])) {
				$declaration[$widget_id]['fields'][$widget_field_id]['label'] = sneeit_slug_to_title($widget_field_id);
			}
			if (!isset($declaration[$widget_id]['fields'][$widget_field_id]['default'])) {
				if (	$declaration[$widget_id]['fields'][$widget_field_id]['type'] == 'number' ||
						$declaration[$widget_id]['fields'][$widget_field_id]['type'] == 'category' || 
						$declaration[$widget_id]['fields'][$widget_field_id]['type'] == 'tag' || 
						$declaration[$widget_id]['fields'][$widget_field_id]['type'] == 'user' ||
						$declaration[$widget_id]['fields'][$widget_field_id]['type'] == 'categories' || 
						$declaration[$widget_id]['fields'][$widget_field_id]['type'] == 'tags' || 
						$declaration[$widget_id]['fields'][$widget_field_id]['type'] == 'users'
					) {
					$declaration[$widget_id]['fields'][$widget_field_id]['default'] = 0;
				} else {
					$declaration[$widget_id]['fields'][$widget_field_id]['default'] = '';
				}
			}
			if ((	
					$declaration[$widget_id]['fields'][$widget_field_id]['type'] == 'radio' || 
					$declaration[$widget_id]['fields'][$widget_field_id]['type'] == 'select'
				) && 
				!isset($declaration[$widget_id]['fields'][$widget_field_id]['choices'])) {				
				$declaration[$widget_id]['fields'][$widget_field_id]['choices'] = array();
			}			
		endforeach;
	}
	return $declaration;
}
