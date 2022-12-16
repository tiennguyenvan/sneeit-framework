function sneeit_shortcodes_esc_attr(value) {
	if (value == null) {
		return value;
	}
	value = value.toString();
	return value
         .replace(/\[/gi, "&amp;#91;")
         .replace(/]/gi, "&amp;#93;")
         .replace(/"/gi, "&amp;quot;")
         /*.replace(/'/gi, "&amp;#039;")*/
         .replace(/>/gi, "&amp;gt;")
         .replace(/</gi, "&amp;lt;");
}
function sneeit_shortcodes_box_come_in() {		
	var header_height = jQuery('#sneeit-shortcode-box > .header').height();
	var action_height = jQuery('#sneeit-shortcode-box > .actions').height();
	
	jQuery('#sneeit-shortcode-box').css('height', (jQuery(window).height() - 2 * header_height) + 'px');
	jQuery('#sneeit-shortcode-box > .content').css('height', (jQuery(window).height() - 3 * header_height - action_height) + 'px');
	
	// disable scroll
	jQuery('body').addClass('disabled-scroll');
}
function sneeit_shortcodes_box_go_out(fadeout_delay) {		
	if (typeof(fadeout_delay) == 'undefined') {
		fadeout_delay = 200;
	}
	// remove other things
	jQuery('body').removeClass('disabled-scroll').css('height', '');		
	if (fadeout_delay == 0) {		
		jQuery('#sneeit-shortcode-box').remove();
		jQuery('#sneeit-shortcode-box-overlay').remove();
	} else {
		jQuery('#sneeit-shortcode-box-overlay').fadeOut(fadeout_delay);
		jQuery('#sneeit-shortcode-box').fadeOut(fadeout_delay, function () {
			jQuery('#sneeit-shortcode-box').remove();
			jQuery('#sneeit-shortcode-box-overlay').remove();
		});
	}	
}

// collect data to generate shortcode before out
function sneeit_shortcodes_collect_data(selected_content, container_selector, separator, shortcode_id, shortcode_fields) {
	var shortcode = '';
		
//	search in each container and collect data
	jQuery(container_selector).first().each(function(){
		var the_container = jQuery(this);
		var content = '';
		var attributes = new Object();		
				
		// insert shortcode		
		
		jQuery.each(shortcode_fields, function(field_id, field_declaration) {
//			var field_selector = '#sneeit-shortcode-'+separator+'-'+field_id;
			var field_selector = '[name="sneeit-shortcode-'+separator+'-'+field_id+'"]';
			var field_selector_array = '[name="sneeit-shortcode-'+separator+'-'+field_id+'[]"]';
			
			switch (field_declaration['type']) {				
				case 'content':
				case 'textarea':
					jQuery(the_container).find(field_selector).each(function(){
						content += jQuery(this).val();
					});					
					break;
				case 'checkbox':
					jQuery(the_container).find(field_selector).each(function(){						
						if (jQuery(this).is(':checked')) {
							attributes[field_id] = 'on';
						} else {							
							attributes[field_id] = '';
						}						
					});	
					break;
					
				case 'radio':
					attributes[field_id] = '';
					jQuery(the_container).find(field_selector).each(function(){						
						if (jQuery(this).is(':checked')) {
							attributes[field_id] = jQuery(this).val();
						}		
					});	
					
					break;
					
				case 'categories' :
				case 'tags' :
				case 'users' :
				case 'sidebars' :
				case 'selects' :
					
					jQuery(the_container).find(field_selector).each(function(){
						var field_value = jQuery(this).val();
						if (field_value === null) {
							field_value = '';
						}
						if (typeof(field_value) == 'Object' || typeof(field_value) == 'Array') {
							field_value = field_value.join(',');
						}
						
						attributes[field_id] = sneeit_shortcodes_esc_attr(field_value);						
					});	

					break;
				
				default:
					jQuery(the_container).find(field_selector).each(function(){
						var field_value = jQuery(this).val();
						if (field_value === null) {
							field_value = '';
						}
						
						attributes[field_id] = sneeit_shortcodes_esc_attr(field_value);						
					});
					break;
			}
		});
		
		if (content == '') {
			content = selected_content;
		}		
//		console.log(attributes);
		shortcode += '['+shortcode_id;
		if (!jQuery.isEmptyObject(attributes)) {
			jQuery.each(attributes, function(attribute_name, attribute_value) {
				shortcode += ' '+attribute_name+'="'+attribute_value+'"';
			});
		}
		shortcode += ']'+content + '[/'+shortcode_id+']';		
		jQuery(this).remove();
	});
		
	return shortcode;
}

// main function for shortcode box, add wrapper and header
// the editor is for insert value when using page builder
var Sneeit_Shortcode_Nested_Index = 0;
function sneeit_shortcodes_box(editor, shortcode_id, shortcode_declaration) {
	var html = '';
	
	// remove current shortcode box if have
	sneeit_shortcodes_box_go_out();
	
	// HTML for shortcode box
	// ######################
	// open box
	html += '<div id="sneeit-shortcode-box-overlay"></div>';
	html += '<div id="sneeit-shortcode-box" class="sneeit-shortcode-box">';
	
	// header
	html += '<div class="header">'+shortcode_declaration['title']+'<a href="javascript:void(0)" id="sneeit-shortcode-button-box-close"><span class="dashicons dashicons-no-alt"></span></a></div>';
	
	// body content with form
	html += 
	'<div class="content">'+
		'<div class="side"><div class="inner">' +			
		'</div></div>' +
		'<div class="main"><div class="inner">'+	
			'<div class="sneeit-shortcode-box-loading-icon">' +
				'<i class="fa fa-spin fa-spinner"></i>'+
			'</div>' +	
		'</div></div>'+
	'</div>';
	
	// actions
	html += '<div class="actions">';
	html += '<a href="javascript:void(0)" id="sneeit-shortcode-button-insert" class="button button-large button-primary">'+Sneeit_Shortcodes.text.insert_shortcode+'</a>';
	html += '<a href="javascript:void(0)" id="sneeit-shortcode-button-cancel" class="button button-large">'+Sneeit_Shortcodes.text.cancel+'</a>';	
	html += '</div>';	
	
	// close box
	html += '</div>';
	
	jQuery(html).appendTo(jQuery('body'));
	
	// get shortcode HTML frame from declaration
	jQuery.post(ajaxurl, {
		action: 'sneeit_shortcodes',
		sub_action: 'control_html',
		shortcode_id: shortcode_id
	}).done(function( data ) {
		jQuery('#sneeit-shortcode-box .content .main .inner').html(data);				
		jQuery('#sneeit-shortcode-box .sneeit-control').first().addClass('first');
		jQuery('#sneeit-shortcode-nested-box .sneeit-control').first().addClass('first');
		
		// init side box for shortcode box
		// - list of controls
		var control_html = '';
		jQuery('#sneeit-shortcode-box .content .main .inner .sneeit-control').each(function(){
			control_html += html_tag(
				'option', 
				'value=#'+jQuery(this).attr('id'), 
				jQuery(this).find('.sneeit-control-title b').text()
			);
		});
		control_html = html_tag(
			'select', 
			'class=search&data-placeholder='+Sneeit_Shortcodes.text['Type Field Name and Enter'], 
			control_html
		);
		jQuery('#sneeit-shortcode-box .content .side .inner').append(control_html);
		jQuery('#sneeit-shortcode-box .content .side .inner .search').chosen({no_results_text: Sneeit_Shortcodes.text['Oops, nothing found!']});
		// -- scroll to controls when input search
		jQuery('#sneeit-shortcode-box .content .side .inner .search').change(function(e){			
			var scroll_to = jQuery(this.options[e.target.selectedIndex]).attr('value');
			if (!empty(scroll_to)) {
				scroll_to = jQuery(scroll_to);
			}
			var container = jQuery('#sneeit-shortcode-box .content .main');										
			if (empty(scroll_to)) {
				container.stop().animate({
					scrollTop: 0
				});
				return;
			}
			
			container.stop().animate({
				scrollTop: (scroll_to.offset().top - container.offset().top + container.scrollTop())
			});			
		});

		// - list of headings
		if (jQuery('#sneeit-shortcode-box .content .main .inner .sneeit-control-separator').length) {
			var heading_html = html_tag_a(null, Sneeit_Shortcodes.text.Top);
			
			jQuery('#sneeit-shortcode-box .content .main .inner .sneeit-control-separator').each(function(){
				heading_html += html_tag_a('data-target=#'+jQuery(this).attr('id'), jQuery(this).find('span').text());
			});
			heading_html = html_tag('div', '.headings', heading_html);						
			
			jQuery('#sneeit-shortcode-box .content .side .inner').append(heading_html);			
			
			// scroll to a control when click on a heading
			jQuery('#sneeit-shortcode-box .content .side .inner .headings a').click(function(){				
				var scroll_to = jQuery(this).attr('data-target');
				if (!empty(scroll_to)) {
					scroll_to = jQuery(scroll_to);
				}
				var container = jQuery('#sneeit-shortcode-box .content .main');							
				var panel = jQuery(this);				
				jQuery('#sneeit-shortcode-box .content .side .inner .headings a').removeClass('active');
				panel.addClass('active');
				if (empty(scroll_to)) {
					container.stop().animate({
						scrollTop: 0
					});
					return;
				}

				container.stop().animate({
					scrollTop: (scroll_to.offset().top - container.offset().top + container.scrollTop())
				});
				
			});
		}
		
		
		
			
		// if have nested, we need to create nested ui and actions
		if (typeof(shortcode_declaration['nested']) !== 'undefined') {
			var the_pattern_html = jQuery('#sneeit-shortcode-nested-box').html();
			jQuery('#sneeit-shortcode-nested-box .sneeit-shortcode-nested-box.pattern').remove();
			
			// clone
			if (typeof(editor) !== 'undefined' && typeof(editor['nested']) !== 'undefined') {
				for (var i = 0; i < editor['nested'].length; i++) {
					jQuery('#sneeit-shortcode-nested-box')
					.append(the_pattern_html.replaceAll('__i__', Sneeit_Shortcode_Nested_Index));
					Sneeit_Shortcode_Nested_Index++;
				}
			} else { // just init default
				jQuery('#sneeit-shortcode-nested-box')
					.append(the_pattern_html.replaceAll('__i__', Sneeit_Shortcode_Nested_Index));
				Sneeit_Shortcode_Nested_Index++;
			}
			
			
			
				
			// Apply effects and transition for shortcode box
			// ##############################################
			
			jQuery('#sneeit-shortcode-nested-box').sortable().disableSelection();			
			
			// collaps / expand nested box
			jQuery('#sneeit-shortcode-nested-box').on('click', '.sneeit-shortcode-nested-box-close-button', function(){				
				var par = jQuery(this).parents('.sneeit-shortcode-nested-box');
				if (par.is('.collapsed')) {					
					par.removeClass('collapsed');
				} else {					
					par.addClass('collapsed');
				}
			});

			// remove nested
			jQuery('#sneeit-shortcode-nested-box').on('click', '.sneeit-shortcode-button-remove-nested', function(){
				jQuery(this).parents('.sneeit-shortcode-nested-box').remove();
			});
			
			// add new nested box
			jQuery('#sneeit-shortcode-button-new-nested').click(function(){
				jQuery('#sneeit-shortcode-nested-box').append(the_pattern_html.replaceAll('__i__', Sneeit_Shortcode_Nested_Index));
				Sneeit_Shortcode_Nested_Index++;
				jQuery.event.trigger({type: 'sneeit_controls_init'});
				jQuery('#sneeit-shortcode-nested-box').sortable().disableSelection();
			});
		}
		
		/////////////////////
		// fill up value for shortcode (top level)
		/////////////////////////////////////////
		if (typeof(editor) !== 'undefined') {
			jQuery.each(shortcode_declaration['fields'], function(field_id, field_declaration) {
				if (field_declaration['type'] == 'content' && 
					editor.selection.getContent()) {
					field_declaration['value'] = editor.selection.getContent();
				}
								
				if ('value' in field_declaration) {
					// convert back script tags if have
					field_declaration['value'] = field_declaration['value'].replaceAll('sneeit_script', 'script');
					
					
					jQuery('#sneeit-shortcode-box .sneeit-control[data-key="'+field_id+'"] .sneeit-control-value').each(function(){
						if (jQuery(this).is('.sneeit-control-checkbox-value')) {
							if (field_declaration['value']) {
								jQuery(this).prop('checked', true);
							} else {
								jQuery(this).prop('checked', false);
							}
						} else if (jQuery(this).is('.sneeit-control-radio-value')) {
							jQuery(this).filter('[value=' + field_declaration['value'] +']').attr('checked', true);
						} else if (jQuery(this).is('select[data-value]')) {
							jQuery(this).attr('data-value', field_declaration['value']);
						} else {
							jQuery(this).val(field_declaration['value']);
						}
					});
				}			
			});
		}
		
		//////////////////////////////////////
		// fill up value for nested shortcodes
		//////////////////////////////////////
		if (typeof(editor) !== 'undefined' && typeof(editor['nested']) !== 'undefined' && typeof(shortcode_declaration['nested']) !== 'undefined') {
			for (var i = 0; i < editor['nested'].length; i++) {
				jQuery.each(shortcode_declaration['nested'], function (nested_shortcode_id, nested_shortcode_declaration) {
					jQuery.each(nested_shortcode_declaration['fields'], function (nested_shortcode_field_id, nested_shortcode_field_declaration) {
						jQuery('#sneeit-shortcode-nested-box .sneeit-shortcode-nested-box').eq(i).find('.sneeit-shortcode-nested-box-item-'+nested_shortcode_id+' .sneeit-control[data-key="'+nested_shortcode_field_id+'"] .sneeit-control-value').each(function(){

							if (jQuery(this).is('input[type="checkbox"]')) {
								if (editor['nested'][i][nested_shortcode_id][nested_shortcode_field_id]) {
									jQuery(this).prop('checked', true);
								} else {
									jQuery(this).prop('checked', false);
								}
							} else {
								jQuery(this).val(editor['nested'][i][nested_shortcode_id][nested_shortcode_field_id]);
							}
						});
						
					});					
				});				
			}
		}
		
	
		// remake field ui
		jQuery.event.trigger({type: 'sneeit_controls_init'});
		
		
		// shortcode box button actions
		// ############################
		jQuery('#sneeit-shortcode-button-cancel, #sneeit-shortcode-box-overlay, #sneeit-shortcode-button-box-close').click(function(){		
			sneeit_shortcodes_box_go_out();
		});

		jQuery('#sneeit-shortcode-button-insert').click(function() {
			// we will switch from visual editor to code editor tabs for all MCE editor
			// this action will help update the content to from MCE editor
			jQuery(document).find('.sneeit-shortcode-box .wp-editor-tabs .wp-switch-editor.switch-html').click();
			
			// we will take action after 200ms, wating any iris color box finish toggle action
			jQuery('#sneeit-shortcode-box').fadeOut(200, function () {
				var shortcode = '';
				var nested_shortcode = editor.selection.getContent();

				// get nested shortcode
				if (typeof(shortcode_declaration['nested']) != 'undefined') {
					nested_shortcode = '';
					var nested_length = jQuery('.sneeit-shortcode-nested-box-item').length; // must hold nested length because we will remove elements
					for (var i = 0; i < nested_length; i++) {
						jQuery.each(shortcode_declaration['nested'], function (nested_shortcode_id, nested_shortcode_declaration) {
							if (!jQuery.isEmptyObject(nested_shortcode_declaration['fields'])) {						
								nested_shortcode += sneeit_shortcodes_collect_data(
									'', 
									'.sneeit-shortcode-nested-box-item-'+nested_shortcode_id,
									'nested-field',
									nested_shortcode_id, 
									nested_shortcode_declaration['fields']
								);
							} else {
								nested_shortcode += '['+nested_shortcode_id+']'+editor.selection.getContent()+'[/'+nested_shortcode_id+']';
							}					
						});	
					}
				}

				shortcode = sneeit_shortcodes_collect_data(
					nested_shortcode, 
					'#sneeit-shortcode-box',
					'field',
					shortcode_id, 
					shortcode_declaration['fields']
				);		
				editor.execCommand('mceInsertContent', 0, shortcode);
				sneeit_shortcodes_box_go_out(0);
			});
		});
		
		jQuery('#sneeit-shortcode-box > .actions, #sneeit-shortcode-button-box-close').show();		

		
		// show the box
		sneeit_shortcodes_box_come_in();		
		jQuery(window).resize(function () {
			if (jQuery('#sneeit-shortcode-box').length) {
				sneeit_shortcodes_box_come_in();
			}		
		});
		
		
	});	
	
	
}