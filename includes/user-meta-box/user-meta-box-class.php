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
		include_once( sneeit_framework_plugin_path('/includes/controls/controls.php') );
		
		// header of metabox		
		wp_nonce_field( $this->nonce_action, $this->nonce_name );
		?><div class="sneeit-user-meta-box"><?php
			?><h3><?php echo $this->user_meta_box_declaration['title']; ?></h3><?php
		if (isset($this->user_meta_box_declaration['description'])) {
			echo '<p class="sneeit-user-meta-box-description">'.$this->user_meta_box_declaration['description'].'</p>';
		}
		
		

		// show fields for meta box
		foreach ($this->user_meta_box_declaration['fields'] as $field_id => $field_declaration) :
			$field_type = $field_declaration['type'];
			$field_value = get_user_meta($user->ID, $field_id);
			if (!is_array($field_value) || empty($field_value)) {
				$field_value = $field_declaration['default'];
			} else {
				$field_value = $field_value[0];
			}
			
			if (in_array($field_declaration['type'], array(
				'categories', 'tags', 'users', 'sidebars', 'selects'
			))) {
				$field_declaration['name'] .= $field_id.'[]';
			}
			
			new Sneeit_Controls($field_id, $field_declaration, $field_value);
		endforeach;
	
		?></div><?php
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
				$field_value = $_POST[$field_id];
				if (is_array($field_value)) {
					$field_value = implode(',', $field_value);
				}
				$field_value = stripslashes($field_value);
				
				update_user_meta($user_id, $field_id, $field_value);
			} else {
				delete_user_meta($user_id, $field_id);
			}
		}
	}
}