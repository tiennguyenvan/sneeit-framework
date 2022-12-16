function sneeit_page_builder_main_content_field() {
	var content_field = jQuery('#content');
	if (content_field.length) {
		return content_field;
	}
	
	// get real content field
	content_field = jQuery('.editor-post-text-editor');				
	if (1 == content_field.lenght) {
		return content_field;
	}
	if (!content_field.length) {
		jQuery('.edit-post-more-menu button').click();
		jQuery('.edit-post-sidebar')
			.next().find('.components-popover__content .components-menu-group').first()
			.next().find('button').first().next().click();
	}
	return content_field.first();	
}
function sneeit_page_builder_width_percent_to_column_label(width_percent, extra_text) {	
	for (var i = 0; i < Sneeit_PageBuilder_Options.column_pattern.length; i++) {
		var pattern = Sneeit_PageBuilder_Options.column_pattern[i].split('/');
		var numerator = Number(pattern[0]);
		var denominator = Number(pattern[1]);
		var current_width_percent = numerator/denominator*100;		
		if (current_width_percent.toString() == width_percent.toString()) {
			return Sneeit_PageBuilder_Options.column_pattern[i];
		}
	}
	if (typeof(extra_text) != 'undefined') {
		return (width_percent + extra_text);
	}
	return width_percent;
}
function sneeit_page_builder_column_label_to_width_percent(column_label) {
	var pattern = column_label.split('/');
	var numerator = Number(pattern[0]);
	var denominator = Number(pattern[1]);
	return numerator/denominator*100;	
}
function sneeit_page_builder_box_toolbar_tab_columns(position) {
	var ret = '';
	if (typeof(position) == 'undefined') {
		position = 'header';
	}	
	if (!('column' in Sneeit_Shortcodes.declaration)) {
		return ret;
	}
		
	if (position == 'header') {
		ret += '<li><a href="#sneeit-page-builder-toolbar-tab-content-columns">'
				+ '<i class="fa fa-columns"></i> '
				+ '<span>'+Sneeit_PageBuilder_Options.text.Columns+'</span>'
				+ '</a><div class="current-tab-arrow"></div></li>';
	} else {
		ret += '<div id="sneeit-page-builder-toolbar-tab-content-columns"><p class="tab-content-inner">';
		var column_pattern = Sneeit_PageBuilder_Options.column_pattern;
		var each_col_width = 1;
		if (column_pattern.length) {
			each_col_width = (100 - Sneeit_PageBuilder_Options.style.toolbar_tab_content_column_button_margin * (column_pattern.length + 1)) / column_pattern.length;
		}
		for (var i = 0; i < column_pattern.length; i++) {			
			var width_percent = sneeit_page_builder_column_label_to_width_percent(column_pattern[i]);

			ret += '<a class="sneeit-page-builder-toolbar-tab-content-columns-button sneeit-page-builder-toolbar-tab-content-button" href="javascript:void(0)" style="width: '+each_col_width+'%;margin-left:'+Sneeit_PageBuilder_Options.style.toolbar_tab_content_column_button_margin+'%" data-width="'+width_percent+'" title="Click to add">\
				<span class="sneeit-page-builder-toolbar-tab-content-columns-button-icon sneeit-page-builder-toolbar-tab-content-button-icon">\
					<span style="width: '+width_percent+'%">\n\
						<span></span><span></span><span></span><span></span><span></span><span></span>\n\
					</span>\
				</span>\
				<span class="sneeit-page-builder-toolbar-tab-content-columns-button-text sneeit-page-builder-toolbar-tab-content-button-text">'
					+column_pattern[i]
				+'</span>\
			</a>';
		}
		ret += '</p></div>';
	}
	return ret;
}

/**
 * Return the HTML code (tabs and shortcode buttons)
 * which will display on header of page builder
 * 
 * @param {type} position
 * @returns {String}
 */
function sneeit_page_builder_box_toolbar_tab_shortcodes(position) {
	if (typeof(position) == 'undefined') {
		position = 'header';
	}

	var ret = '';
	if (position == 'header') {
		ret += '<li><a href="#sneeit-page-builder-toolbar-tab-shortcodes">'
				+ '<i class="fa fa-code"></i> '
				+ '<span>'+Sneeit_PageBuilder_Options.text.Shortcodes+'</span>'
				+'</a><div class="current-tab-arrow"></div></li>';
	} else {
		ret += '<div id="sneeit-page-builder-toolbar-tab-shortcodes"><p class="tab-content-inner">';
		
		jQuery.each(Sneeit_Shortcodes.declaration, function (shortcode_id, shortcode_declaration) {
			if (shortcode_id == 'column') {
				return;
			}
			ret += '<a class="sneeit-page-builder-toolbar-tab-shortcodes-button sneeit-page-builder-toolbar-tab-content-button" href="javascript:void(0)" data-shortcode_id="'+shortcode_id+'" title="Click to add"';
			if ('tooltip' in shortcode_declaration) {
				ret += 'data-tooltip="'+shortcode_declaration.tooltip+'"';
			}
			ret += '>';
			ret += '<span><span class="sneeit-page-builder-toolbar-tab-shortcodes-button-icon sneeit-page-builder-toolbar-tab-content-button-icon">';
			if ('icon' in shortcode_declaration) {
				if (sneeit_is_image_src(shortcode_declaration.icon)) {
					ret += '<img src="'+shortcode_declaration.icon+'/>';
				} else {
					ret += '<i class="'+sneeit_valid_icon_code(shortcode_declaration.icon)+'"></i>';
				}
			} else {
				ret += '<i class="fa fa-code"></i>';
			}
			ret += '</span> ';
			ret += '<span class="sneeit-page-builder-toolbar-tab-shortcodes-button-text sneeit-page-builder-toolbar-tab-content-button-text">'
						+shortcode_declaration.title
					+'</span>';
			ret += '</span></a>';
		});		
		ret += '</p></div>';
	}
	return ret;
}

function sneeit_page_builder_workspace_wrapper_html(shortcode_id, shortcode_declaration, shortcode_content) {	
	var ret = '';
	var width_percent = 100;
	jQuery('#sneeit-page-builder-null').html(shortcode_content);
	if (shortcode_id == 'column') {		
		width_percent = jQuery('#sneeit-page-builder-null > div[data-sneeit_shortcode_id="'+shortcode_id+'"]').attr('width');				
		if (typeof(width_percent) == 'undefined' || !width_percent) {
			width_percent = 100;
		}		
	}
	var title = jQuery('#sneeit-page-builder-null > div[data-sneeit_shortcode_id="'+shortcode_id+'"]').attr('title');
	if (typeof(title) == 'undefined') {
		title = '';
	}

	// start box
	ret += '<div class="sneeit-page-builder-workspace-box sneeit-page-builder-workspace-box-'+(shortcode_id == 'column'? 'column':'shortcode sneeit-page-builder-workspace-box-'+shortcode_id)+'" data-shortcode_id="'+shortcode_id+'" style="width:'+width_percent+'%" data-width="'+width_percent+'">';

	// header for workspace box
	ret +=		'<div class="sneeit-page-builder-workspace-box-header sneeit-page-builder-workspace-box-header-'+shortcode_id+'">';

	// specific if the box is a column
	ret += 			'<div class="sneeit-page-builder-workspace-box-header-start">';
	if (shortcode_id == 'column') {
		ret += 			'<a title="'+Sneeit_PageBuilder_Options.text.Decrease_width+'" href="javascript:void(0)" class="sneeit-page-builder-workspace-box-column-decrease-width sneeit-page-builder-workspace-box-column-change-width"><i class="fa fa-minus"></i></a>';		
		ret += 			'<span class="sneeit-page-builder-workspace-box-header-column-value">\
							'+sneeit_page_builder_width_percent_to_column_label(width_percent, '%')+'\
						</span>';
		ret += 			'<a title="'+Sneeit_PageBuilder_Options.text.Increase_width+'" href="javascript:void(0)" class="sneeit-page-builder-workspace-box-column-increase-width sneeit-page-builder-workspace-box-column-change-width"><i class="fa fa-plus"></i></a>';
	} else {		
		if ('icon' in shortcode_declaration) {
			if (sneeit_is_image_src(shortcode_declaration.icon)) {
				ret += '<img src="'+shortcode_declaration.icon+'"/>';
			} else {
				ret += '<i class="'+sneeit_valid_icon_code(shortcode_declaration.icon)+'"></i>';
			}
		} else {
			ret += 		'<i class="fa fa-code"></i>';
		}
		ret += 			' <span>'+shortcode_declaration.title+(title? ': <strong>'+title+'</strong>' : '')+'</span>';			
	}
	ret +=			'</div>';

	ret +=			'<div class="sneeit-page-builder-workspace-box-header-end">';
	
	ret += 				'<a title="'+Sneeit_PageBuilder_Options.text.Edit+'" href="javascript:void(0)" class="sneeit-page-builder-workspace-box-header-button-edit"><i class="fas fa-pencil-alt"></i></a>';
	ret += 				'<a title="'+Sneeit_PageBuilder_Options.text.Duplicate+'" href="javascript:void(0)" class="sneeit-page-builder-workspace-box-header-button-duplicate"><i class="fa fa-copy"></i></a>';
	ret += 				'<a title="'+Sneeit_PageBuilder_Options.text.Delete+'" href="javascript:void(0)" class="sneeit-page-builder-workspace-box-header-button-delete"><i class="fa fa-close"></i></a>';
	ret += 			'</div>';
	ret += 		DIV_CLEAR+'</div>';

	// content for workspace box
	ret +=		'<div class="sneeit-page-builder-workspace-box-content sneeit-page-builder-workspace-box-content-'+shortcode_id+'">';	
	ret += 			shortcode_content;
	ret += 		'</div>';

	// end box
	ret += '</div>';

	return ret;
}
function sneeit_page_builder_shortcode_to_html(content, shortcode_list, nested_level) {
	if (nested_level >= Sneeit_PageBuilder_Options.max_nested_level) {
		// only allow max level of nested list
		return content;
	}

	// replace all nested column shortcode
	for (var i = Sneeit_PageBuilder_Options.max_nested_level - 1; i >= 0 ; i-=1) {
		content = content.replaceAll('column-'+Sneeit_PageBuilder_Options.separator_nested_level+'-'+i, 'column');
	}

	// replace shortcode to real html tags
	jQuery.each(shortcode_list, function (shortcode_id, shortcode_declaration) {	
		var start_key = '['+shortcode_id;
		var end_key = '[/'+shortcode_id+']';

		// ***********************************************
		// replace shortcode [shortcode attr="***"/] first,
		var index = 0;
		for (var i = 0; i < content.length; i++) {
			var start = content.indexOf(start_key, index);
			if (start == -1 || start+start_key.length == content.length) {
				// not found start tag or the start tag has invalid end at the end of content
				break;
			}

			var next_string = content.substring(start+start_key.length, start+start_key.length + 1);			
			
			if (!sneeit_is_slug_name_character(next_string)) {
				// if next character is not a name character,
				//  mean we FOUND exactly the shortcode start point
				
				var end = content.indexOf(']', start+start_key.length);
				if (end == -1) {
					// short code start tag has no end tag, invalid					
					break;
				}
				

				var prev_string = content.substring(end - 1, end);				
				if (prev_string == '/') {
					// FOUND exactly [shortcode attr="***"/] to replace
					var shortcode_content = content.substring(start + start_key.length, end - 1);
					
					shortcode_content = '<div data-sneeit_shortcode_id="'+shortcode_id+'" '+shortcode_content+'></div>';
					content = content.substring(0, start) + shortcode_content + content.substring(end+1);
					index = start + shortcode_content.length;
				} else {
					// FOUND the shortcode but is not an instant close, just go next
					index = start + start_key.length;
				}
			} else {
				// This is not exactly the shortcode we are finding, 
				// just a longer name which contains our shortcode name
				index = start + start_key.length;
			}
		}
		

		// *******************************************
		// Then [shortcode attr="***"]****[/shortcode]
		var index = 0;
		for (var i = 0; i < content.length; i++) {
			var end = content.indexOf(end_key, index);
			if (end == -1) {
				// not found any end tag
				break;
			}

			var prev_content = content.substring(0, end); // cut head of content before end tag
			var start = -1;
			for (var j = 0; j < prev_content.length; j++) {
				var start = prev_content.lastIndexOf(start_key);			
				if (start == -1) {
					// can not find the start tag, so we must move to next point in content
					break;
				}
				var next_string = prev_content.substring(start+start_key.length, start+start_key.length + 1);
				if (!sneeit_is_slug_name_character(next_string)) {
					// FOUND exactly start tag of shortcode
					var close = content.indexOf(']', start);
					if (close >= end) {
						// can not find the close tag before end point, so we must move to next point in content
						start = -1;
						break;
					}

					// FOUND the full start tag, replacing everything here
					var shortcode_start = content.substring(start + start_key.length, close);
					var shortcode_content = content.substring(close+1, end);

					// Replace nested shortcode if this shortcode has
					if ('nested' in shortcode_declaration) {
						shortcode_content = sneeit_page_builder_shortcode_to_html(shortcode_content, shortcode_declaration['nested'], nested_level+1);
					}					


					shortcode_content = '<div data-sneeit_shortcode_id="'+shortcode_id+'" '+shortcode_start+'>'+shortcode_content+'</div>';
					content = content.substring(0, start) + shortcode_content + content.substring(end+end_key.length);
					// after replacing, break and go to next point of end key
					index = start + shortcode_content.length;
					break; 
				} else {
					// this is another shortcode which has name contains our shortcode id
					// we must reward back more to find another, until go to the beginning of content
					prev_content = prev_content.substring(0, start);
					start = -1;
				}
			}

			// jump to next point
			if (start == -1) {
				// seem this close tag is for something, not for our shortcode					
				index = end+end_key.length;			
			}
		}
		

		// *******************************************
		// finally, replace all [shortcode attr="***"]
		var index = 0;
		for (var i = 0; i < content.length; i++) {
			var start = content.indexOf(start_key, index);
			if (start == -1 || start+start_key.length == content.length) {
				// not found start tag or the start tag has invalid end at the end of content
				break;
			}
			var next_string = content.substring(start+start_key.length, start+start_key.length + 1);
			if (!sneeit_is_slug_name_character(next_string)) {
				// if next character is not a name character,
				//  mean we FOUND exactly the shortcode start point
				var end = content.indexOf(']', start+start_key.length);
				if (end == -1) {
					// short code start tag has no end tag, invalid					
					break;
				}
				
				// FOUND exactly [shortcode attr="***"] to replace
				var shortcode_content = content.substring(start + start_key.length, end);				
				shortcode_content = '<div data-sneeit_shortcode_id="'+shortcode_id+'" '+shortcode_content+'></div>';
				content = content.substring(0, start) + shortcode_content + content.substring(end+1);
				index = start + shortcode_content.length;				
			} else {
				// This is not exactly the shortcode we are finding, 
				// just a longer name which contains our shortcode name
				index = start + start_key.length;
			}
		}
	});
	
	// disable scripts
	content = content.replaceAll('<script>', '<sneeit_script>');
	content = content.replaceAll('<script ', '<sneeit_script ');
	content = content.replaceAll('</script>', '</sneeit_script>');
	
	return content;	
}


// convert workspace html to shortcode
function sneeit_page_builder_workspace_html_to_shortcode(workspace_html, shortcode_list, replace_parent, nested_level) {
	if (nested_level >= Sneeit_PageBuilder_Options.max_nested_level) {
		// only allow max level of nested list
		return content;
	}

	var workspace_selector = document.createElement('div');
	workspace_selector.innerHTML = workspace_html;	

	// covert HTML to shortcode and unwrap all box
	jQuery.each(shortcode_list, function(shortcode_id, shortcode_declaration) {
		jQuery(workspace_selector).find('div[data-sneeit_shortcode_id="'+shortcode_id+'"]').each(function () {
			var shortcode_fields = new Object();
			var shortcode_content = jQuery(this).html();
			var shortcode_holder = jQuery(this);
			// get all shortcode field values
			jQuery.each(shortcode_declaration['fields'], function (field_id, field_declaration) {
				if ('type' in field_declaration && field_declaration['type'] != 'textarea' && field_declaration['type'] != 'content') {	
					var field_value = shortcode_holder.attr(field_id);
					if (typeof(field_value) == 'undefined') {										
//						shortcode_fields[field_id] = field_declaration.default;
						shortcode_fields[field_id] = field_declaration['default'];
					} else {
						shortcode_fields[field_id] = shortcode_holder.attr(field_id);
					}
				}
			});

			// replace nested shortcode
			if ('nested' in shortcode_declaration) {
				shortcode_content = 
					sneeit_page_builder_workspace_html_to_shortcode(
						shortcode_content, 
						shortcode_declaration['nested'], 
						'',
						nested_level+1
					);
			}

			// update in 2.5 for unlimted nested columns and shortcodes
			if (shortcode_content.indexOf('data-shortcode_id=') != -1) {
				shortcode_content = 
					sneeit_page_builder_workspace_html_to_shortcode(
						shortcode_content, 
						shortcode_list, 
						replace_parent,
						nested_level+1
					);
			}
			

			// then place shortcode
			var shortcode_id_fixed = shortcode_id;
			if (shortcode_id == 'column') {
				shortcode_id_fixed = 'column-'+Sneeit_PageBuilder_Options.separator_nested_level+'-'+nested_level;
			}
			var shortcode = '['+shortcode_id_fixed;
			jQuery.each(shortcode_fields, function(field_id, field_value) {
				shortcode += ' '+field_id+'="'+sneeit_shortcodes_esc_attr(field_value)+'"';
			});
			
			// add back scripts
			shortcode_content = shortcode_content.replaceAll('sneeit_script', 'script');
			
			shortcode += ']'+shortcode_content+'[/'+shortcode_id_fixed+']';
			if (replace_parent) {
				jQuery(this).parents(replace_parent).first().replaceWith(shortcode);
			} else {
				jQuery(this).replaceWith(shortcode);
			}
		});
	});	
	

	return workspace_selector.innerHTML.replaceAll('sneeit_script', 'script');
}
function sneeit_page_builder_workspace_to_content_textarea(content_field, sneeit_gutenberg) {
	
	// move content to dummy
	jQuery('#sneeit-page-builder-dummy').html(jQuery('#sneeit-page-builder-workspace').html());
		

	// escape from linked sortable list
	for (var i = 0; i < 100; i++) {
		if (jQuery('#sneeit-page-builder-dummy .sneeit-page-builder-workspace-box-content-column-linked-sortable').length == 0) {
			break;
		}
		jQuery('#sneeit-page-builder-dummy .sneeit-page-builder-workspace-box-content-column-linked-sortable').each(function () {
			var shortcode_html = jQuery(this).html();
			jQuery(this).replaceWith(shortcode_html);
		});
		
	}

	// then convert html to shortcode
	jQuery('#sneeit-page-builder-dummy .ui-sortable-handle').removeClass('ui-sortable-handle');	
	// remove empty p tags
	jQuery('#sneeit-page-builder-dummy').find('p').each(function(){
		var text = jQuery(this).text();
		text = jQuery.trim(text);
		if ('' == text) {
			jQuery(this).remove();
		}
	});
	var content = sneeit_page_builder_workspace_html_to_shortcode(		
		jQuery('#sneeit-page-builder-dummy').html(), 
		Sneeit_Shortcodes.declaration, 
		'.sneeit-page-builder-workspace-box',
		0
	);
	
//	if (sneeit_gutenberg && content.indexOf('<!-- wp:shortcode -->') != 0) {
//		content = '<!-- wp:shortcode -->\n' + content + '\n\n<!-- \/wp:shortcode -->';
//		wp.data.dispatch( 'core/block-editor' ).resetBlocks( wp.blocks.parse( content ) );
//	}
	if (sneeit_gutenberg) {		
		wp.data.dispatch( 'core/block-editor' ).resetBlocks( wp.blocks.parse( content ) );
	}
	content_field.val(content).change();
}


function sneeit_page_builder_box(toolbar_content, workspace_content) {
	jQuery('#wp-content-editor-container').prepend('\
<div id="sneeit-page-builder-box">\n\
	<div id="sneeit-page-builder-toolbar">'+toolbar_content+'</div>\n\
	<div id="sneeit-page-builder-workspace-wrapper"><div id="sneeit-page-builder-workspace" class="sneeit-page-builder-linked-sortable">'+workspace_content+'</div></div>\n\
	<div id="sneeit-page-builder-dummy" style="display:none!important"></div>\n\
	<div id="sneeit-page-builder-null" style="display:none!important"></div>\n\
</div>');
}

function sneeit_page_builder_apply_sortable(callback = null) {
	// apply sortable wrapper for column shortcodes
	return jQuery("#sneeit-page-builder-workspace-wrapper .sneeit-page-builder-linked-sortable").sortable({
		connectWith: '#sneeit-page-builder-workspace-wrapper .sneeit-page-builder-linked-sortable',
		item: '.sneeit-page-builder-workspace-box-header',
		revert: true,
		forceHelperSize: true,
		forcePlaceholderSize: true,
		tolerance: "pointer",
		distance: 5,
		helper: 'clone',
		start: function( event, ui ) {			
			if (typeof(callback) == 'function') {
				callback();
			} else if (typeof(callback) == 'string') {
				window[callback]();
			}
		},
		stop: function( event, ui ) {				
			if (typeof(callback) == 'function') {
				callback();
			} else if (typeof(callback) == 'string') {
				window[callback]();
			}
		},
		receive: function(event, ui){
			if (typeof(callback) == 'function') {
				callback();
			} else if (typeof(callback) == 'string') {
				window[callback]();
			}
			if (!Sneeit_PageBuilder_Declaration.nested) {
				// disable drop column in column 
				if (jQuery(this).is('.sneeit-page-builder-workspace-box-content-column-linked-sortable') && 
					ui.item.is('.sneeit-page-builder-workspace-box.sneeit-page-builder-workspace-box-column')) {
					jQuery(ui.sender).sortable('cancel');
				}
			}			
    	}
	}).disableSelection(); 
}