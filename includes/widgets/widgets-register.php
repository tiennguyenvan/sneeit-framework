<?php


global $Sneeit_Widgets_Declaration;
global $wp_widget_factory;
foreach ($Sneeit_Widgets_Declaration as $widget_id => $widget_declaration) {
	$wp_widget_factory->widgets[$widget_id] = new WP_Sneeit_Widget($widget_id, $widget_declaration);
}