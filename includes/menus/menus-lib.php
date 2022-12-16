<?php
function sneeit_validate_menu_locations_declaration($declaration) {
	if (!is_array($declaration)) {
		return array();
	}
	$alternative_declaration = $declaration;
	foreach ($declaration as $menu_location_id => $menu_location_declaration) {
		if (is_numeric($menu_location_id)) {
			unset($alternative_declaration[$menu_location_id]);
			$alternative_declaration[sanitize_title_with_dashes($menu_location_declaration)] = $menu_location_declaration;
			continue;
		}
		if (!$menu_location_declaration) {
			$alternative_declaration[$menu_location_id] = sneeit_slug_to_title($menu_location_id);
		}
	}
	return $alternative_declaration;
}

function sneeit_validate_menu_fields_declaration($declaration) {
	if (!is_array($declaration)) {
		return array();
	}
	foreach ($declaration as $menu_field_id => $menu_field_declaration) :
		if (!isset($declaration[$menu_field_id]['type'])) {
			$declaration[$menu_field_id]['type'] = 'text';
		}
		if (!isset($declaration[$menu_field_id]['default'])) {
			if ($declaration[$menu_field_id]['type'] == 'number') {
				$declaration[$menu_field_id]['default'] = 0;
			} else {
				$declaration[$menu_field_id]['default'] = '';
			}
		}
		if ($declaration[$menu_field_id]['type'] == 'radio' && !isset($declaration[$menu_field_id]['choices'])) {
			$declaration[$menu_field_id]['choices'] = array();
		}			
	endforeach;
	return $declaration;
}

