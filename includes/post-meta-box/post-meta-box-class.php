<?php

/** 
 * The Class.
 */
class Sneeit_Post_Meta_Box {
	var $post_meta_box_id;
	var $post_meta_box_declaration;
	var $nonce_action;
	var $nonce_name;
	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct($post_meta_box_id, $post_meta_box_declaration) {
		$this->post_meta_box_id = $post_meta_box_id;
		$this->post_meta_box_declaration = $post_meta_box_declaration;
		$this->nonce_action = 'sneeit-'.$post_meta_box_id.'-nonce-action';
		$this->nonce_name = 'sneeit-'.$post_meta_box_id.'-nonce-name';
				
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {		
		$post_types = array('post', 'page');     //limit meta box to certain post types
		if (isset($this->post_meta_box_declaration['post_types'])) {
			if (is_array($this->post_meta_box_declaration['post_types'])) {
				$post_types = $this->post_meta_box_declaration['post_types'];				
			} else if (	is_string($this->post_meta_box_declaration['post_types']) && 
						strpos($this->post_meta_box_declaration['post_types'], ',') !== false) {
				$post_types = explode(',', $this->post_meta_box_declaration['post_types']);
			} else {
				$post_types = array($this->post_meta_box_declaration['post_types']);
			}
		}
		
		// validate context values
		if (!isset($this->post_meta_box_declaration['context'])) {
			$this->post_meta_box_declaration['context'] = 'advanced';
		}
		$this->post_meta_box_declaration['context'] = strtolower($this->post_meta_box_declaration['context']);
		if ($this->post_meta_box_declaration['context'] != 'advanced' &&
			$this->post_meta_box_declaration['context'] != 'side' && 
			$this->post_meta_box_declaration['context'] != 'normal') {
			$this->post_meta_box_declaration['context'] = 'advanced';
		}
		
		// validate priority value
		if (!isset($this->post_meta_box_declaration['priority'])) {
			$this->post_meta_box_declaration['priority'] = 'default';
		}
		
		if ( in_array( $post_type, $post_types )) {			
			add_meta_box(
				$this->post_meta_box_id
				,(	isset($this->post_meta_box_declaration['title']) ? 
						$this->post_meta_box_declaration['title'] :
						sneeit_slug_to_title($this->post_meta_box_declaration['title'])
				)
				,(	isset($this->post_meta_box_declaration['callback']) ?
						$this->post_meta_box_declaration['callback'] :
						array( $this, 'render_meta_box_content' )
				)					
				,$post_type
				,$this->post_meta_box_declaration['context']
				,$this->post_meta_box_declaration['priority']
				, array(
					'id' => $this->post_meta_box_id,
					'declaration' => $this->post_meta_box_declaration
				)
			);
		}
	}
	
	/*
	* We need to verify this came from the our screen and with proper authorization,
	* because save_post can be triggered at other times.
	*/

	public function verify_authorization($post_id) {
		
		// Check if our nonce is set.
		if ( ! isset( $_POST[$this->nonce_name] ) ) {
			return false;
		}
			
		$nonce = $_POST[$this->nonce_name];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, $this->nonce_action ) ) {
			return false;
		}
			
		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  {
			return false;
		}
			
		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return false;
			}					
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return false;
			}				
		}
		
		return true;
	}
	
	public function save_fields($post_id) {
		/* OK, its safe for us to save the data now. */
		foreach ($this->post_meta_box_declaration['fields'] as $field_id => $field_declaration) {
			if (isset( $_POST[$field_id] )) {
				$field_value = $_POST[$field_id];
				if (is_array($field_value)) {
					$field_value = implode(',', $field_value);
				}
				$field_value = stripslashes($field_value);
			
				update_post_meta($post_id, $field_id, $field_value);
			} else {
				delete_post_meta($post_id, $field_id);
			}
		}
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		if (!$this->verify_authorization($post_id)) {
			return $post_id;
		}
		$this->save_fields($post_id);		
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
		include_once sneeit_framework_plugin_path('/includes/controls/controls.php');
		
		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->nonce_action, $this->nonce_name );
		
		if (!empty($this->post_meta_box_declaration['description'])) {
			echo '<p class="sneeit-post-meta-box-description">'.$this->post_meta_box_declaration['description'].'</p>';
		}

		foreach ($this->post_meta_box_declaration['fields'] as $field_id => $field_declaration) :
			if ($field_declaration['type'] == 'content' && $field_id == 'content') {
				continue;
			}
			
			// check if this field limited for certain post types
			if (!empty($field_declaration['post_types'])) {
				$post_types = $field_declaration['post_types'];     //limit meta box to certain post types
		
				if ( is_string($post_types) ) {
					$post_types = explode(',', $post_types);
				}
				
				$current_screen = get_current_screen();
				if (empty($current_screen) || 
					empty($current_screen->post_type) || 
					!in_array($current_screen->post_type, $post_types)) {
					continue;
				}
			}
			$field_value = get_post_meta($post->ID, $field_id);
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
	}
}