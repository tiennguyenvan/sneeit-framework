<?php
class WP_Sneeit_Widget extends WP_Widget {
	var $widget_declaration;
	var $widget_id;
	function __construct($widget_id, $widget_declaration) {
		$this->widget_declaration = $widget_declaration;
		$this->widget_id = $widget_id;
		parent::__construct(
			$widget_id, // Base ID
			$widget_declaration['title'], // Name
			array( 
				'description' => $widget_declaration['description']
			)
		);
	}


	/** use this function to decide how the widget settings 
	will display in your admin dashboard */
	public function form( $instance ) {
		include_once sneeit_framework_plugin_path('/includes/controls/controls.php');
		
		// validate instance first
		foreach ($this->widget_declaration['fields'] as $widget_field_id => $widget_field_declaration) :
			if (!isset($instance[$widget_field_id]) && isset($widget_field_declaration['default'])) {
				$instance[$widget_field_id] = $widget_field_declaration['default'];
			}
		endforeach;
		
		// show title fields
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : ''; 
		?><div class="sneeit-widget-title"><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'sneeit' ); ?></label><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></div><?php
		
		// show other fields
		// remember skip title
		foreach ($this->widget_declaration['fields'] as $widget_field_id => $widget_field_declaration) :
			if ('title' == $widget_field_id) {
				continue;
			}
			$widget_field_declaration['id'] = $this->get_field_id($widget_field_id);
			$widget_field_declaration['name'] = $this->get_field_name($widget_field_id);
			
			if (in_array($widget_field_declaration['type'], array(
				'categories', 'tags', 'users', 'sidebars', 'selects'
			))) {
				$widget_field_declaration['name'] .= '[]';
			}
			
			$field_value = $widget_field_declaration['default'];
			if (isset( $instance[$widget_field_id] )) {
				$field_value = $instance[$widget_field_id];
			}
			
			?><p><?php
			new Sneeit_Controls($widget_field_id, $widget_field_declaration, $field_value);
			?></p><?php			
		endforeach;
	}

	/** use this function to decide the way widget data will
	be saved after admin update widget data */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$debug = array(
			$new_instance
		);
		foreach ($this->widget_declaration['fields'] as $widget_field_id => $widget_field_declaration) :
			$field_type = $widget_field_declaration['type'];
			$field_value = $widget_field_declaration['default'];
			array_push($debug,$field_type);
			if (isset( $new_instance[$widget_field_id] )) {
				switch ($field_type) {					
					case 'categories':		
					case 'selects':					
					case 'tags':
					case 'users':
					case 'sidebars':
						$field_value = '';
						if (!empty($new_instance[$widget_field_id])) {
							$field_value = implode(',', $new_instance[$widget_field_id]);
						}
						break;
					
					case 'content':						
					default:
						$field_value = $new_instance[$widget_field_id];
						break;
				}
				$instance[$widget_field_id] = $field_value;
			} else {
				$instance[$widget_field_id] = '';
			}
			
		endforeach;
		
//		set_transient('debug', $debug);

		return $instance;
	}
	
	
	/** use this function to decide how the widget
	will display in your theme */
	public function widget( $args, $instance ) {
		
		if (isset($this->widget_declaration['display_callback']) && function_exists($this->widget_declaration['display_callback'])) {
			// validate fields before delevring
			foreach ($this->widget_declaration['fields'] as $widget_field_id => $widget_field_declaration) :
				if (!isset($instance[$widget_field_id]) && isset($widget_field_declaration['default'])) {
					$instance[$widget_field_id] = $widget_field_declaration['default'];
				} else if (	is_array($instance[$widget_field_id]) && 
							count($instance[$widget_field_id]) == 1 && 
							$instance[$widget_field_id][0] == '' &&
					(	$widget_field_declaration['type'] == 'categories' ||
						$widget_field_declaration['type'] == 'tags' ||
						$widget_field_declaration['type'] == 'users' ||
						$widget_field_declaration['type'] == 'selects'
					)
				) {
					$instance[$widget_field_id] = '';
				}
			endforeach;
			
			// add some base things, so user can easy work on it
			$args['sneeit_widget_header'] = '';
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

			$args['sneeit_widget_header'] .= $args['before_widget'];
			if ( $title ) {
				$args['sneeit_widget_header'] .= ($args['before_title'] . $title . $args['after_title']);
			}
			
			$args['sneeit_widget_footer'] = $args['after_widget'];
			
			call_user_func($this->widget_declaration['display_callback'], $args, $instance, $this->widget_id, $this->widget_declaration);
			return;
		}		
	}
}
