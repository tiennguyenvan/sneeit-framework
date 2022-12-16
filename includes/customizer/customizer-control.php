<?php
class WP_Customize_Sneeit_Control extends WP_Customize_Control {
	var $declarations;
	var $setting_id;
	
	public function render_content() {		
		include_once( sneeit_framework_plugin_path('/includes/controls/controls.php') );
		new Sneeit_Controls($this->setting_id, $this->declarations, $this->value());
	}
}
