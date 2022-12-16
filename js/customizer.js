jQuery( function ( $ ) {
	
	$(document).ready(function(){	
		
		// add icon for panel and section
		// then add the result button also
		// Sneeit_Customize_Options
		function sneeit_customize_add_title_icon(id, data) {			
			if ('icon' in data && ('sections' in data || 'settings' in data)) {				
				var icon = data['icon'];
				if (sneeit_is_image_src(icon)) {
					icon = '<img src="'+icon+'" class="sneeit-customizer-title-icon"/>';
				} else {
					icon = '<i class="'+sneeit_valid_icon_code(icon)+' sneeit-customizer-title-icon"></i>';
				}
				var panel_section = 'panel';
				if ( 'settings' in data ) {
					panel_section = 'section';
				}
				$('#accordion-'+panel_section+'-'+id+' > .accordion-section-title').prepend(icon);				
			}
		}

		$.each(Sneeit_Customize_Options, function (level_1_id, level_1_data) {
			sneeit_customize_add_title_icon(level_1_id, level_1_data);
			var next_data = '';
			if ('sections' in level_1_data) {
				next_data = level_1_data['sections'];
			} else if ('settings' in level_1_data) {
				next_data = level_1_data['settings'];
			}
			if (next_data) {
				$.each(next_data, function (level_2_id, level_2_data) {					
					if ('settings' in level_2_data) {
						sneeit_customize_add_title_icon(level_2_id, level_2_data);
					}					
				}); // foreach data in child of first level
			} // end of next data from first level
			
		}); // end of add reset button
		
		
	}); // end document ready
});