<?php
function sneeit_update_menu_fields( $menu_id, $menu_item_db_id, $args ) {
	global $Sneeit_Menu_Fields_Declaration;
	if (!is_array($Sneeit_Menu_Fields_Declaration)) {
		return;
	}
	
	foreach ($Sneeit_Menu_Fields_Declaration as $menu_field_id => $menu_field_declaration) {
		// reset all fields
		delete_post_meta($menu_item_db_id, $menu_field_id);
		
		// then update fields
		if (isset($_REQUEST['menu-item-'.$menu_field_id])) {
			if ( is_array( $_REQUEST['menu-item-'.$menu_field_id]) ) {
				if (isset($_REQUEST['menu-item-'.$menu_field_id][$menu_item_db_id])) {
					$value = $_REQUEST['menu-item-'.$menu_field_id][$menu_item_db_id];
					if ($menu_field_declaration['type'] == 'checkbox') {
						$value = 'on';
					}
					if (is_array($value)) {
						$value = implode(',', $value);
					}
					update_post_meta( $menu_item_db_id, $menu_field_id, $value );
				}
			}
		}
	}
}

