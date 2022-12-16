<?php
class Sneeit_User_Meta_Box {
	var $user_meta_box_id;
	var $user_meta_box_declaration;
	var $nonce_action;
	var $nonce_name;
	
	public function __construct($user_meta_box_id, $user_meta_box_declaration) {
		$this->user_meta_box_id = $user_meta_box_id;
		$this->user_meta_box_declaration = $user_meta_box_declaration;
		$this->nonce_action = 'sneeit-'.$user_meta_box_id.'-nonce-action';
		$this->nonce_name = 'sneeit-'.$user_meta_box_id.'-nonce-name';
		
		add_action( 'show_user_profile', array( $this, 'render_meta_box_content' ) );
		add_action( 'edit_user_profile', array( $this, 'render_meta_box_content' ) );
		
		add_action( 'personal_options_update', array( $this, 'save' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save' ) );
	}
	
	/**
	 * Render Meta Box content.
	 *
	 * @param WP_User $user The post object.
	 */
	public function render_meta_box_content($user) {
		// header of metabox		
		wp_nonce_field( $this->nonce_action, $this->nonce_name );
		
		echo '<h3>'.$this->user_meta_box_declaration['title'].'</h3>';
		if (isset($this->user_meta_box_declaration['description'])) {
			echo '<p class="sneeit-user-meta-box-description">'.$this->user_meta_box_declaration['description'].'</p>';
		}
		
		echo '<table class="form-table"><tbody>';

		// show fields for meta box
		foreach ($this->user_meta_box_declaration['fields'] as $field_id => $field_declaration) :
			$field_type = $field_declaration['type'];
			$field_value = get_user_meta($user->ID, $field_id);
			if (!is_array($field_value) || empty($field_value)) {
				$field_value = $field_declaration['default'];
			} else {
				$field_value = $field_value[0];
			}
			?><tr class="user-<?php echo $field_id; ?>-wrap"><th><?php
			if ($field_type != 'checkbox' && $field_type != 'radio') {
				echo '<label for="'.$field_id.'">';
			}
			echo $field_declaration['label'] .'<br/>';				
			if ($field_type != 'checkbox' && $field_type != 'radio') {
				echo '</label>';
			}
			?></th><td><?php

			switch ($field_type) :					
				case 'textarea': 
					?><textarea class="widefat" rows="5" cols="30" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>"><?php echo $field_value; ?></textarea><?php
					break;
				case 'checkbox':					
					?><label for="<?php echo $field_id; ?>"><input id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" type="checkbox" <?php echo checked(!empty($field_value));?> /><?php echo $field_declaration['description']; ?></label><?php
					break;

				case 'select':
					?><select class="regular-select" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>">
					<?php
					foreach ($field_declaration['choices'] as $value => $label) {
						?><option value="<?php echo $value; ?>"<?php selected( $field_value, $value, true ); ?>><?php echo $label; ?></option><?php
					}
					?></select><?php
					break;					

				case 'radio':					
					foreach ($field_declaration['choices'] as $value => $label) {
					?><input type="radio" id="<?php echo $field_id; ?>-<?php echo $value; ?>" name="<?php echo $field_id; ?>" value="<?php echo $value; ?>"<?php checked( $field_value, $value, true ); ?>/><label><?php echo $label; ?></label><br/><?php
					}					
					break;								
				case 'color': 
					?><input class="widefat sneeit-color-field" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" type="text" value="<?php echo $field_value; ?>" /><?php
					break;				

				default: 
					?><input class="regular-text" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" type="<?php echo $field_type; ?>" value="<?php echo $field_value; ?>" /><?php
					break;
			endswitch;

			if ($field_type != 'checkbox' && $field_type != 'radio' && $field_declaration['description']) {
				echo '<p class="description">'.$field_declaration['description'].'</p>';			
			}
			?></td></tr><?php
		endforeach;
		
		echo '</table>';
	}
	
	public function save($user_id) {
		if ( ! isset( $_POST[$this->nonce_name] ) ) {
			return $user_id;
		}		
		$nonce = $_POST[$this->nonce_name];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, $this->nonce_action ) ) {
			return $user_id;
		}
			
		/*it's safe to save user data*/
		/* OK, its safe for us to save the data now. */
		foreach ($this->user_meta_box_declaration['fields'] as $field_id => $field_declaration) {
			$field_type = $field_declaration['type'];
			$field_value = $field_declaration['default'];
			if (isset( $_POST[$field_id] )) {
				switch ($field_type) {
					case 'textarea':
						if ( current_user_can('unfiltered_html') ) {
							$field_value = $_POST[$field_id];
						} else {
							$field_value = stripslashes( wp_filter_post_kses( addslashes($_POST[$field_id]) ) );
						}						
						break;
					case 'checkbox':
						$field_value = $_POST[$field_id];			
						break;

					default:
						$field_value = sanitize_text_field($_POST[$field_id]);
						break;
				}
				update_user_meta($user_id, $field_id, $field_value);
			} else {
				delete_user_meta($user_id, $field_id);
			}
		}
	}
}