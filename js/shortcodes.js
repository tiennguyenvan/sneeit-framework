if (typeof(Sneeit_Shortcodes) != 'undefined' && typeof(Sneeit_Shortcodes['declaration']) != 'undefined') {
	jQuery(function($) {	
				
		(function() {
						
			tinymce.create('tinymce.plugins.Sneeit_Shortcodes', {
				init : function(editor, url) {
					
					var Sneeit_Shortcodes_Dropdown_Menu = new Array();
					$.each(Sneeit_Shortcodes.declaration, function (shortcode_id, shorcode_declaration) {
						if (typeof(shorcode_declaration['icon']) == 'undefined') {
							return;
						}
						if (typeof(shorcode_declaration['display_callback']) == 'undefined') {
							return;
						}
						var shortcode_button_options = {
							title : shorcode_declaration['title']
						};
						
						// if developer want to show as dropdown list
						// we will need button text when dropdown
						if (Sneeit_Shortcodes.title) {
							shortcode_button_options['text'] = shorcode_declaration['title'];
						}
						
						if (sneeit_is_image_src(shorcode_declaration['icon'])) {
							shortcode_button_options['image'] = shorcode_declaration['icon'];
						} else {
							shortcode_button_options['icon'] = sneeit_valid_icon_code(shorcode_declaration['icon']) + ' sneeit-custom-shortcode-icon '+shortcode_id;
						}
						shortcode_button_options['onclick'] = function() {								
							if (!$.isEmptyObject(shorcode_declaration['fields']) || typeof(shorcode_declaration['nested']) != 'undefined') {																
								sneeit_shortcodes_box(editor, shortcode_id, shorcode_declaration);
							} else {										
								editor.execCommand('mceInsertContent', 0, '['+shortcode_id+']'+editor.selection.getContent()+'[/'+shortcode_id+']');
							}
						}
						// if developer want to show as dropdown list
						// we will need button text when dropdown
						if (Sneeit_Shortcodes.title) {
							Sneeit_Shortcodes_Dropdown_Menu.push(shortcode_button_options);
						} 
						// or we just need to show one by one shortcode button
						else {
							editor.addButton(shortcode_id, shortcode_button_options);
						}
					});	
					
					// show dropdown list if need
					
					if (Sneeit_Shortcodes.title) {
						editor.addButton('sneeit-shortcode-dropdown', {
							text: Sneeit_Shortcodes.title,
							icon: sneeit_valid_icon_code(Sneeit_Shortcodes.icon),
							type: 'menubutton',
							menu: Sneeit_Shortcodes_Dropdown_Menu
						});
						return;
					}
				},
			});
			// Register plugin
			tinymce.PluginManager.add( 'sneeit_shortcodes', tinymce.plugins.Sneeit_Shortcodes );
		})();
		
		$('#wpwrap').click(function () {
			$('html,body').remove('disabled-scroll'); // just in case the shortcode not work properly
		})
	});
} /*end checking shortcode action*/
