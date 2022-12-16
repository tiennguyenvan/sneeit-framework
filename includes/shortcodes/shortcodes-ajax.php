<?php
function sneeit_shortcodes_ajax() {	
	if (!current_user_can('moderate_comments')) {
		die();
	}
	$sub_action = sneeit_get_server_request('sub_action');	
	
	if (!$sub_action) {
		die();
	}
	
		
	switch ($sub_action) {
		// build actions
		case 'control_html':
			$shortcode_id = sneeit_get_server_request('shortcode_id');
			if (!$shortcode_id) {
				die();
			}
			global $Sneeit_ShortCodes;
			if (empty($Sneeit_ShortCodes) ||
				!isset($Sneeit_ShortCodes['declarations']) ||
				!isset($Sneeit_ShortCodes['declarations'][$shortcode_id])) {
				die();
			}
			$shortcode_declaration = $Sneeit_ShortCodes['declarations'][$shortcode_id];
			if (!isset($shortcode_declaration['fields'])) {
				die();
			}
			$shorcode_fields = $shortcode_declaration['fields'];
			
			include_once( sneeit_framework_plugin_path('/includes/controls/controls.php') );
			
			foreach ($shorcode_fields as $field_id => $field_declaration) {
				$field_declaration['name'] = 'sneeit-shortcode-field-'.$field_id;
				$field_declaration['id'] = 'sneeit-shortcode-field-'.$field_id;
				new Sneeit_Controls($field_id, $field_declaration);
			}
			
			if (!isset($shortcode_declaration['nested'])) {						
				die();
			}
			
			echo '<ul id="sneeit-shortcode-nested-box">';
						
			echo '<li class="sneeit-shortcode-nested-box sneeit-shortcode-box nested ui-state-default pattern">';
			
			// action buttons for header
			echo '<a href="javascript:void(0)" class="sneeit-shortcode-nested-box-close-button">'.					
					'<i class="icon up fa fa-angle-up"></i>'.
					'<i class="icon down fa fa-angle-down"></i>'.
				'</a>';
			
			
			echo '<a href="javascript:void(0)" class="sneeit-shortcode-button-remove-nested">'.	
					esc_html__('Remove', 'sneeit').
					' <i class="icon fa fa-trash"></i>'.
				'</a>';
			
			
			
			foreach ($shortcode_declaration['nested'] as $nested_shortcode_id => $nested_shortcode_declaration) {
				echo '<div class="sneeit-shortcode-nested-box-item sneeit-shortcode-nested-box-item-'.$nested_shortcode_id.'">';
				echo '<div class="header">'.$nested_shortcode_declaration['title'].'</div>';
				echo '<div class="nested content"><div class="inner">';
				
				foreach ($nested_shortcode_declaration['fields'] as $nested_shortcode_field_id => $nested_shortcode_field_declaration) {
					$nested_shortcode_field_declaration['name'] = 'sneeit-shortcode-nested-field-'.$nested_shortcode_field_id;
					$nested_shortcode_field_declaration['id'] = 'sneeit-shortcode-nested-field-'.$nested_shortcode_field_id.'-__i__';
					new Sneeit_Controls($nested_shortcode_field_id, $nested_shortcode_field_declaration);
				}
				
				echo '</div></div>'; // end of content
				
				echo '</div>';// end of nested wrap box				
			}
		
			echo '</li>'; // close box of nested shortcode
			
			echo '</ul>'; // close nested box for all nested shortcodes
			
			// nested actions
			echo '<div class="nested-actions">';

			// action for add new nested item
			echo '<a href="javascript:void(0)" id="sneeit-shortcode-button-new-nested" class="button button-large">'.
				esc_html__('Add New', 'sneeit').
			'</a>';	
			echo '</div>'; // end of nested action
			
			break;		
		
		default:
			break;
	}
	
	die();
}
if (is_admin()) :
	add_action( 'wp_ajax_nopriv_sneeit_shortcodes', 'sneeit_shortcodes_ajax' );
	add_action( 'wp_ajax_sneeit_shortcodes', 'sneeit_shortcodes_ajax' );
endif;// is_admin for ajax

