jQuery( function ( $ ) {		
	var Sneeit_Controls_Media_Uploader = null;
	var Sneeit_Controls_Sidebars_Options = '';
	var Sneeit_Controls_Categories = null;
	var Sneeit_Controls_Tags = null;
	var Sneeit_Controls_Users = null;
	var Sneeit_Controls_Dependency_Detonators = new Object();
	var Sneeit_Controls_Editor_Pattern = new Object();
	var Sneeit_Controls_Is_Typing = null;
	
	if (typeof(tinyMCEPreInit) !== 'undefined') {
		Sneeit_Controls_Editor_Pattern.mceInit = tinyMCEPreInit.mceInit[Sneeit_Controls.sneeit_controls_editor_id];
		Sneeit_Controls_Editor_Pattern.qtInit = tinyMCEPreInit.qtInit[Sneeit_Controls.sneeit_controls_editor_id];
		Sneeit_Controls_Editor_Pattern.html = $('#wp-sneeitframeworkcontroleditorid-wrap')[0].outerHTML;
	}
	
	// pre process special variables
	if (typeof(Sneeit_Controls_Sidebars) != 'undefined') {
		$.each(Sneeit_Controls_Sidebars, function (id, sidebar){
			Sneeit_Controls_Sidebars_Options += '<option value="'+id+'">'+sidebar.name+'</option>';
		});
	}
	
	/* ENHANCE FOR CHOSEN */
	// This will keep order of item after selected
	function sneeit_enhance_chosen(e) {
		e.on('change', function() {
			// check condition if enough
			var chosen_conainter = $(this).next();
			if (!chosen_conainter.is('.chosen-container')) {
				return;
			}
			var search_choice_close = chosen_conainter.find('.search-choice-close');
			if (search_choice_close.length == 0) {
				return;
			}
			var options = $(this).find('option' );
			if (options.length == 0) {
				return '';
			}
			
			// reorder and reselect
			var chosen_value = $(this).val();
			if (!chosen_value || chosen_value.length < 2) {
				return '';
			}
			var chosen_index = new Array();
			
			search_choice_close.each(function(){
				chosen_index.push(Number($(this).attr('data-option-array-index')));
			});			
			
			// reorder base on chosen index
			// get data
			var chosen_detach = new Array();
			for (var i = 0; i < chosen_index.length; i++) {
				var opt = $( options[ chosen_index[i] ] );
				var opt_val = opt.val();				
				if (chosen_value.indexOf(opt_val) == -1) {					
					continue;
				}				
				chosen_detach.push(opt);	
			}
			
			// reorder
			if (chosen_detach.length < 2) {
				return '';				
			}
			for (var i = 0; i < chosen_detach.length; i++) {
				chosen_detach[i].appendTo($(this)).prop('selected', true);
			}
			$(this).trigger('chosen:updated');					
		});
	}
	
	
	/* LOAD CATEGORIES, TAGS and USERS */
	/**************************************/	
	function sneeit_controls_ajax_parse_data(data) {
		try {
		    data = $.parseJSON(data);
		} catch (e) {
		    return false;
		}
		if (data == null) {
			return false;
		}
		if ('error' in data) {
			return false;
		}
		return data; 
	}
	function sneeit_controls_ajax_load_taxonomy(sub_action) {
		$.post(Sneeit_Controls.ajaxurl, {
			action: 'sneeit_controls',
			sub_action: sub_action
		}).done(function( data ) {
			data = sneeit_controls_ajax_parse_data(data);
			if (false == data) {
				return '';
			}
			
			// process Sneeit_Controls_Categories
			if (!typeof(data) == 'Object' && !typeof(data) == 'Array') {
				return '';
			}
			
			var html = '';
			$.each(data, function (taxonomy_id, taxonomy_name) {
				html += '<option value="'+taxonomy_id+'">'+taxonomy_name+'</option>';
			});

			if (!html) {
				return '';
			}			
			
			switch (sub_action) {
				case 'categories':
					Sneeit_Controls_Categories = html;
					break;
				case 'tags':
					Sneeit_Controls_Tags = html;
					break;
				case 'users':
					Sneeit_Controls_Users = html;
					break;			
			}			
		}).fail(function(){
			setTimeout(function(){
				sneeit_controls_ajax_load_taxonomy(sub_action);
			}, 1000);
		});
	}	
	sneeit_controls_ajax_load_taxonomy('categories');
	sneeit_controls_ajax_load_taxonomy('tags');
	sneeit_controls_ajax_load_taxonomy('users');
	
	
	
	/* LOAD FONTS */
	/**************/
	var Sneeit_Font_Load_List = new Array();
	var Sneeit_Font_Loaded_List = new Array();
	var Sneeit_Font_Load_List_Counter = 0;
	var Sneeit_Font_Hover_Loading = false;
	// add font into pending list
	$.each(Sneeit_Controls_Fonts.google, function (font_name, font_property) {
		Sneeit_Font_Load_List[Sneeit_Font_Load_List_Counter] = new Object();
		Sneeit_Font_Load_List[Sneeit_Font_Load_List_Counter].name = font_name;
		Sneeit_Font_Load_List[Sneeit_Font_Load_List_Counter].property = font_property;
		Sneeit_Font_Load_List_Counter++;
		
	});
	
	// start load one font each 10 secs
	var Sneeit_Font_Load_Counter = 0;
	var Sneeit_Font_Load_Timer = setInterval(function() {
		if (Sneeit_Font_Loaded_List.indexOf(Sneeit_Font_Load_List[Sneeit_Font_Load_Counter].name) == -1) {
			WebFont.load({
				google: {
					families: [
						Sneeit_Font_Load_List[Sneeit_Font_Load_Counter].name.replace(/ /gi, '+')+':'+
						Sneeit_Font_Load_List[Sneeit_Font_Load_Counter].property
					]
				}
			});
			Sneeit_Font_Loaded_List.push(Sneeit_Font_Load_List[Sneeit_Font_Load_Counter].name);
		}
		Sneeit_Font_Load_Counter++;
		if (Sneeit_Font_Load_Counter >= Sneeit_Font_Load_List_Counter) {
			clearInterval(Sneeit_Font_Load_Timer);
		}
	}, 10000);
	

	// load font style for Uploaded fonts
	if (typeof(Sneeit_Controls_Fonts.upload) != 'undefined') {
		var font_face = '';
		$.each( Sneeit_Controls_Fonts.upload, function( font_name, font_url ) {
			font_face += '@font-face {font-family:\''+font_name+'\';src:url("'+font_url+'");}';
		});
		$('<style type="text/css" id="upload-font-face">'+font_face+'</style>').appendTo($('head'));
	}
	
	// parse font and make up the font style
	function sneeit_controls_makeup_fonts(selector) {	
		// make up by adding the inline font style for the font list in customize backend
		selector.each(function(){
			if ($(this).is('input')) {
				var font_name = $(this).val();
			} else {
				var font_name = $(this).text();
			}
			
			if (typeof(Sneeit_Controls_Fonts.safe) != 'undefined' && typeof(Sneeit_Controls_Fonts.safe[font_name]) != 'undefined') {
				$(this).css('font-family', Sneeit_Controls_Fonts.safe[font_name]);
			}
			if (typeof(Sneeit_Controls_Fonts.google) != 'undefined' && typeof(Sneeit_Controls_Fonts.google[font_name]) != 'undefined') {
				var font_family = '"'+font_name+'", ';
				var font_property = Sneeit_Controls_Fonts.google[font_name];
				if (font_property == 'serif') {
					font_family += 'serif';
				} else if (font_property == 'cursive') {
					font_family += 'cursive';
				} else {
					font_family += 'sans-serif';
				}
				$(this).css('font-family', font_family);
			}
			if (typeof(Sneeit_Controls_Fonts.upload) != 'undefined' && typeof(Sneeit_Controls_Fonts.upload[font_name]) != 'undefined') {
				$(this).css('font-family', '\''+font_name+'\'');
			}
		});	
	}
	
	
	/* CONTROL DEPENDENCY */
	/**********************/	
	function sneeit_controls_dependency_detonator_add(dependency, data_key) {
		if (!$.isArray(dependency)) {
			return;
		}
		for (var i = 0; i < dependency.length; i++) {
			if (!$.isArray(dependency[i])) {
				continue;
			}
			// index 0 is id or name of another control, we call it X			
			// we will create a list of condition of this X control
			var detonator_id = dependency[i][0];
			if (!$.isArray(Sneeit_Controls_Dependency_Detonators[detonator_id])) {				
				Sneeit_Controls_Dependency_Detonators[detonator_id] = new Array();
			}
			
			// if this is new relation ship, we need to add into X control condition list
			if ($.inArray(data_key, Sneeit_Controls_Dependency_Detonators[detonator_id]) == -1) {
				Sneeit_Controls_Dependency_Detonators[detonator_id].push(data_key);
			}
		}		
	}
	
	function sneeit_controls_dependency_detonator_boolean(scope, dependency) {
		// calculate final value for dependency
		var final_boolean = '';		
		for (var i = 0; i < dependency.length; i++) {
			
			// if this is a operator
			if (!$.isArray(dependency[i]) || dependency[i].length != 3) {
				final_boolean += dependency[i];				
				continue;
			}
			

			// value
			var detonator_id = dependency[i][0];
			var inner_operator = dependency[i][1];
			var expect_value = dependency[i][2];
			var detonator = scope.find('.sneeit-control[data-key="'+detonator_id+'"] .sneeit-control-value');			
			
			if (detonator.length == 0) {
				return true;
			}
			var detonator_value = '';
			if (detonator.is('input[type="checkbox"]')) {
				detonator_value = detonator.is(':checked') ? 'on' : '';			
			} 
			else if (detonator.is('input[type="radio"]')) {
				detonator.each(function(){
					if ($(this).is(':checked')) {
						detonator_value = $(this).val();
					}					
				});				
			} 
			else {
				detonator_value = detonator.val();
			}						

			// convert to number if they are
			if (!isNaN(expect_value) && !isNaN(detonator_value)) {
				expect_value = Number(expect_value);
				detonator_value = Number(detonator_value);
			}

			switch (inner_operator) {
				case '!=':
					final_boolean += detonator_value != expect_value;
					break;

				case '>=':
					final_boolean += detonator_value >= expect_value;
					break;

				case '<=':
					final_boolean += detonator_value <= expect_value;
					break;

				case '>':								
					final_boolean += detonator_value > expect_value;
					break;

				case '<':
					final_boolean += detonator_value <= expect_value;
					break;

				// ==
				default:
					final_boolean += expect_value == detonator_value;
					break;
			} // end inner operator case

		}// end for of dependency
		
		// calculate the final boolean
		// we use limit for to prevent unknow case 
		for (var i = 0; i < 1000 && final_boolean !== 'false' && final_boolean !== 'true'; i++) {
			// && first
			final_boolean = final_boolean
				.replaceAll('false&&false', 'false')
				.replaceAll('true&&false', 'false')
				.replaceAll('false&&true', 'false')
				.replaceAll('true&&true', 'true');

			final_boolean = final_boolean
				.replaceAll('false||false', 'false')
				.replaceAll('true||false', 'true')
				.replaceAll('false||true', 'true')
				.replaceAll('true||true', 'true');
		}
		
		if (i == 1000) {
			final_boolean = 'false';
		}
		if ('true' == final_boolean) {			
			return true;
		}
		return false;
	}
	function sneeit_controls_dependency_detonator_onchange(scope, detonator_id) {		
		// if this not is in detonator list. we don't need process
		if (typeof(Sneeit_Controls_Dependency_Detonators[detonator_id]) == 'undefined') {		
			return;
		}				
		
		// access all depend controls of this detonator
		for (var i = 0; i < Sneeit_Controls_Dependency_Detonators[detonator_id].length; i++) {
			var data_key = Sneeit_Controls_Dependency_Detonators[detonator_id][i];
			
			// scan all data_key and change their show / hide status
			scope.find('.sneeit-control-dependency[data-key="'+data_key+'"]').each(function(){
				var affecter = $(this);
				if (affecter.parent().is('.customize-control')) {
					affecter = affecter.parent();
				}
								
				// show this
				if (typeof(Sneeit_Controls_Defines[data_key]) != 'undefined' &&
					typeof(Sneeit_Controls_Defines[data_key].show) != 'undefined') {
										
					if (sneeit_controls_dependency_detonator_boolean(scope, Sneeit_Controls_Defines[data_key].show)) {
						affecter.stop().slideDown();						
					} else {						
						affecter.stop().slideUp();
					}
				}
				
				// hide this
				if (typeof(Sneeit_Controls_Defines[data_key]) != 'undefined' &&
					typeof(Sneeit_Controls_Defines[data_key].hide) != 'undefined') {
					
					if (sneeit_controls_dependency_detonator_boolean(scope, Sneeit_Controls_Defines[data_key].hide)) {
						affecter.stop().slideUp();
					} else {
						affecter.stop().slideDown();
					}
				}
			});
		} // end for to scan all depend controls of this detonator
	}
	
	/* RICH TEXT WORDPRESS EDITOR */
	function sneeit_controls_editor_init(textarea) {
		var textarea_id = $(textarea).attr('id');
		if (!textarea_id) {
			return;
		}
		
		if (typeof( tinyMCEPreInit ) === 'undefined') {
			return;
		}
		
		if ( typeof tinymce !== 'undefined' ) {
			init = tinyMCEPreInit.mceInit[textarea_id];
			tinymce.init( init );

			if ( ! window.wpActiveEditor ) {
				window.wpActiveEditor = textarea_id;
			}	
		}
			

		if ( typeof QTags === 'function' ) {
			QTags( tinyMCEPreInit.qtInit[textarea_id] );
			QTags._buttonsInit();			
			
			if ( ! window.wpActiveEditor ) {
				window.wpActiveEditor = textarea_id;
			}			
		}
		
		$(textarea).unbind( 'onmousedown' );
		$(textarea).bind( 'onmousedown', function(){
			window.wpActiveEditor = textarea_id;
		});
	}
	/*
	 * @notes: error: "Cannot read property 'canvas' of undefined" 
	 * mean the id of editor is wrong and not in tinyMCEPreInit list
	 */
	function sneeit_controls_editor(textarea) {
		var textarea_id = $(textarea).attr('id');
		var textarea_name = $(textarea).attr('name');
		var data_customize_setting_link = $(textarea).attr('data-customize-setting-link');
		
		if (!textarea_id || !textarea_name) {
			return;
		}
		
		if (typeof(tinyMCEPreInit) === 'undefined' || typeof(tinymce) === 'undefined') {
			return;
		}
		
		var par = $(textarea).parents('.sneeit-control');
		
		var textarea_val = $(textarea).val();
		
		// get and replace pattern
		var mceInit = $.extend({}, Sneeit_Controls_Editor_Pattern.mceInit);
		$.each(mceInit, function (key, value){
			if (typeof(value) == 'string' && value.indexOf(Sneeit_Controls.sneeit_controls_editor_id) != -1) {
				mceInit[key] = mceInit[key].replaceAll(Sneeit_Controls.sneeit_controls_editor_id, textarea_id);
			}
		});
		
		// prevent show shortcode in shortcode in control
		mceInit['external_plugins'] = new Object();

		var qtInit = $.extend({}, Sneeit_Controls_Editor_Pattern.qtInit);
		$.each(qtInit, function (key, value){
			if (typeof(value) == 'string' && value.indexOf(Sneeit_Controls.sneeit_controls_editor_id) != -1) {
				qtInit[key] = qtInit[key].replaceAll(Sneeit_Controls.sneeit_controls_editor_id, textarea_id);
			}
		});

		var html = Sneeit_Controls_Editor_Pattern.html;
		html = html.replaceAll(Sneeit_Controls.sneeit_controls_editor_id, textarea_id);
		
		// show pattern
		$(textarea).replaceWith(html);
		textarea = par.find('textarea[id="'+textarea_id+'"]');
		textarea.val(textarea_val).attr('rows', 10).attr('name', textarea_name);
		textarea.addClass('sneeit-control-value sneeit-control-content-value');
		if (typeof(data_customize_setting_link) != 'undefined') {
			textarea.attr('data-customize-setting-link', data_customize_setting_link);
		}
	
		// INIT PATTERN
		///////////////
		// remove if already init
		if (typeof(tinyMCEPreInit.mceInit[ textarea_id ]) != 'undefined') {			
			tinymce.execCommand('mceRemoveEditor', true, textarea_id);
//			tinymce.execCommand('mceAddEditor', true, textarea_id);
		}
		
		// init now
		tinyMCEPreInit.mceInit[ textarea_id ] = mceInit;
		tinyMCEPreInit.qtInit[ textarea_id ] = qtInit;
		
		
		sneeit_controls_editor_init(textarea);

		// switch to HTML at default
		if (typeof(switchEditors) != 'undefined') {
			switchEditors.go(textarea_id, 'html');
		}		
		par.find('.wp-switch-editor.switch-tmce').on('click', function(){			
			if (!$(this).is('.sneeit-visual-tab')) {
				$(this).addClass('sneeit-visual-tab');
				var this_tab = this;
				setTimeout(function(){
					$(this_tab).click();
				}, 100);	
			}
		});
	

		par.find('.wp-editor-wrap').on('mousedown', function() {
			textarea.change();
		});
		
		// write back data to text area
		$(document).on('hover', '.button-primary, input[type="submit"]', function() {
			if (par.find('.wp-editor-wrap').is('.tmce-active')) {
				textarea.val( tinymce.get( textarea_id ).getContent() );
			}
		});
	}
	
	/* par is parent selector, 
	 * we will base on this to find other elements*/
	function sneeit_controls_box_model_value_to_ui(par, val) {
		var value_holder = par.find('.sneeit-control-value');
		
		if (typeof(val) == 'undefined') {
			val = value_holder.val();			
		} else {
			value_holder.val(val).change();
		}
		
		val = val.split(' ');
		if (val.length < 4) {
			return;
		}

		var top = val[0];
		var right = val[1];
		var bottom = val[2];
		var left = val[3];

		var top_value = top.replace('px', '').replace('%', '');
		var top_unit = top.replace(top_value, '');
		if (!top_unit) {top_unit = 'px';}
		par.find('.sneeit-control-box-model-top-value').val(top_value);
		par.find('.sneeit-control-box-model-top-unit').val(top_unit);
		
		var right_value = right.replace('px', '').replace('%', '');
		var right_unit = right.replace(right_value, '');
		if (!right_unit) {right_unit = 'px';}
		par.find('.sneeit-control-box-model-right-value').val(right_value);
		par.find('.sneeit-control-box-model-right-unit').val(right_unit);
		
		var bottom_value = bottom.replace('px', '').replace('%', '');
		var bottom_unit = bottom.replace(bottom_value, '');
		if (!bottom_unit) {bottom_unit = 'px';}
		par.find('.sneeit-control-box-model-bottom-value').val(bottom_value);
		par.find('.sneeit-control-box-model-bottom-unit').val(bottom_unit);
		
		var left_value = left.replace('px', '').replace('%', '');
		var left_unit = left.replace(left_value, '');
		if (!left_unit) {left_unit = 'px';}
		par.find('.sneeit-control-box-model-left-value').val(left_value);
		par.find('.sneeit-control-box-model-left-unit').val(left_unit);
		
	}
	function sneeit_controls_box_model_ui_to_value(par) {
		
		var top_value = par.find('.sneeit-control-box-model-top-value').val();
		var top_unit = par.find('.sneeit-control-box-model-top-unit').val();
		if (!top_unit) {top_unit = 'px';}
		var top = ( top_value ? top_value : '0') + top_unit;
		
		var right_value = par.find('.sneeit-control-box-model-right-value').val();
		var right_unit = par.find('.sneeit-control-box-model-right-unit').val();
		if (!right_unit) {right_unit = 'px';}
		var right = ( right_value ? right_value : '0') + right_unit;
		
		var bottom_value = par.find('.sneeit-control-box-model-bottom-value').val();
		var bottom_unit = par.find('.sneeit-control-box-model-bottom-unit').val();		
		if (!bottom_unit) {bottom_unit = 'px';}
		var bottom = ( bottom_value ? bottom_value : '0') + bottom_unit;
		
		var left_value = par.find('.sneeit-control-box-model-left-value').val();
		var left_unit = par.find('.sneeit-control-box-model-left-unit').val();		
		if (!left_unit) {left_unit = 'px';}
		var left = ( left_value ? left_value : '0') + left_unit;
		
		var final_value = top + ' ' + right + ' ' + bottom + ' ' + left;
				
		var current_value = par.find('.sneeit-control-value').val();
		
		if (final_value != current_value) {
			par.find('.sneeit-control-value').val(final_value).change();		
		}
	}
	
	
	/* RESET FOR EACH CONTROL */
	/* application must build their 
	 * section and whole reset base on this
	 * by click reset links using jQuery 
	 * 
	 * We made this because some control,
	 * after clicking reset, we must also
	 * refill fake input
	 * */
	/**************************/
	function sneeit_controls_reset(button) {
		var data_key = $(button).attr('data-key');
		var data_type = $(button).attr('data-type');
		var par = $(button).parents('.sneeit-control');
		var value_holder = par.find('.sneeit-control-value');
		var default_value = Sneeit_Controls_Defines[data_key].default;
		
		
		switch (data_type) {
			case 'checkbox':
				value_holder.prop('checked', default_value);
				break;
				
			case 'font-family':
				par.find('.font-family-item').each(function () {
					if ($(this).text() == default_value) {
						$(this).click();
					}
				});	
				break;
			
			case 'font':
				// parse default value
				value_holder.val(default_value);
				default_value = default_value.split(' ');
				if (default_value.length < 4) {
					return;
				}
				
				// long font name
				var font_name = default_value[3];
				if (default_value.length > 4) {
					for (var i = 4; i < default_value.length; i++) {
						font_name += ' ' + default_value[i];
					}
				}
				font_name = font_name.replace(/"/gi, '');
				
				// font style
				par.find('.font-style-value').each(function () {
					if ( (default_value[0] == 'normal' && $(this).is('.active')) ||  
						(default_value[0] == 'italic' && $(this).is('.inactive')) ){
						$(this).click();					
					}
				});
				
				// font weight
				par.find('.font-weight-value').each(function () {
					if ( (default_value[1] == 'normal' && $(this).is('.active')) ||  
						(default_value[1] == 'bold' && $(this).is('.inactive')) ){
						$(this).click();					
					}
				});
				
				// font size
				par.find('.font-size-value').val(default_value[2]);
				par.find('.font-size-value option').each(function () {
					if ($(this).attr('value') == default_value[2]) {
						$(this).prop('selected', true);						
					} else {
						$(this).prop('selected', false);						
					}
				});
				par.find('.font-family-item').each(function () {
					if ($(this).text() == font_name) {
						$(this).click();
					}
				});
				break;
				
			case 'color':
				if (default_value) {
					par.find('.wp-picker-default').click();		
				} else {
					par.find('.wp-picker-clear').click();
				}
				
				break;
				
			case 'image':
				value_holder.val(default_value);
				if (!default_value) {
					par.find('.sneeit-control-image-remove-button').click();
				} else {
					par.find('.sneeit-control-image-preview').html('<img src="'+default_value+'"/>');
				}				
				break;
				
			case 'range':
				// validate
				default_value = Number(default_value);
				par.find('.sneeit-control-range-value-number').html(default_value);
				par.find('.sneeit-control-range-slider[data-key="'+data_key+'"]').slider('value', default_value);
				value_holder.val(default_value);
				break;
				
			case 'visual':
				value_holder.val(default_value);
				par.find('.sneeit-control-visual-picker[data-value="'+default_value+'"]').click();
				break;			
				
			case 'box-padding':
			case 'box-margin':
			case 'box-positon':
			case 'box-padding-px':
			case 'box-margin-px':
			case 'box-positon-px':				
				value_holder.val(default_value);				
				sneeit_controls_box_model_value_to_ui(par, default_value);
				break;
				
				
			default:
				value_holder.val(default_value);
				break;
		}
		value_holder.change();
	}
	
	
	
	/* MAIN FUNCTION FOR MAKING UI 
	 * You will put your jQuery events 
	 * for controls here
	 * You will also need to init your controls
	 * just in case an ajax process wipe out everything
	 * */
	/*******************************/
	function sneeit_controls_init() {
		
		///////////////////////////////////////////////////
		// PREVENT INIT IF THE CONTROL IS IN WIDGET PATTERN
		///////////////////////////////////////////////////
		$('.sneeit-control').each(function(){
			if ($(this).attr('id').indexOf('__i__') != -1) {
				$(this).addClass('initialized'); 				
			}
		});
		
		
			
		/*CATEGORY*/
		$('.sneeit-control-category').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized') && !$(this).is('.pending')) {
				return;
			}
			$(this).addClass('pending');
			if (null === Sneeit_Controls_Categories) {
				return;
			}
			
			$(this).find('.sneeit-control-value.ajax').each(function(){
				var data_value = $(this).attr('data-value');				
				
				$(this).append(Sneeit_Controls_Categories);
				
				var proper = $(this).find('option[value="'+data_value+'"]');
				if (proper.length == 0) {
					$(this).find('option').first().prop('selected', true);
				} else {
					proper.prop('selected', true);
				}
				$(this).show().chosen();				
			});
			
			$(this).find('.loading-icon').remove();
			
			$(this).removeClass('pending');
			
		}); // end of color control
		
				
		/*CATEGORIES*/
		$('.sneeit-control-categories').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized') && !$(this).is('.pending')) {
				return;
			}
			$(this).addClass('pending');
			if (null === Sneeit_Controls_Categories) {
				return;
			}
			
			$(this).find('.sneeit-control-value.ajax').each(function(){
				var data_value = $(this).attr('data-value');
				
				if (typeof(data_value) != 'string') {
					return;
				}
				
				data_value = data_value.split(',');
				
				$(this).append(Sneeit_Controls_Categories);
				
				for (var i = 0; i < data_value.length; i++) {
					$(this).find('option[value="'+data_value[i]+'"]').prop('selected', true);					
				}
				
				$(this).show().chosen();				
			});
			
			$(this).find('.loading-icon').remove();
			
			$(this).removeClass('pending');
			
		}); // end of color control
		
		/*TAG*/
		$('.sneeit-control-tag').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized') && !$(this).is('.pending')) {
				return;
			}
			$(this).addClass('pending');
			if (null === Sneeit_Controls_Tags) {
				return;
			}
			
			$(this).find('.sneeit-control-value.ajax').each(function(){
				var data_value = $(this).attr('data-value');				
				
				$(this).append(Sneeit_Controls_Tags);
				
				var proper = $(this).find('option[value="'+data_value+'"]');
				if (proper.length == 0) {
					$(this).find('option').first().prop('selected', true);
				} else {
					proper.prop('selected', true);
				}	
				$(this).show().chosen();				
			});
			
			$(this).find('.loading-icon').remove();
			
			$(this).removeClass('pending');
			
		}); // end of color control
		
		/*TAGS*/
		$('.sneeit-control-tags').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized') && !$(this).is('.pending')) {
				return;
			}
			$(this).addClass('pending');
			if (null === Sneeit_Controls_Tags) {
				return;
			}
			
			$(this).find('.sneeit-control-value.ajax').each(function(){
				var data_value = $(this).attr('data-value');
				
				if (typeof(data_value) != 'string') {
					return;
				}
				
				data_value = data_value.split(',');
				
				$(this).append(Sneeit_Controls_Tags);
				
				for (var i = 0; i < data_value.length; i++) {
					$(this).find('option[value="'+data_value[i]+'"]').prop('selected', true);					
				}
				
				$(this).show().chosen();				
			});
			
			$(this).find('.loading-icon').remove();
			
			$(this).removeClass('pending');
			
		}); // end of color control
		
		/*USER*/
		$('.sneeit-control-user').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized') && !$(this).is('.pending')) {
				return;
			}
			$(this).addClass('pending');
			if (null === Sneeit_Controls_Users) {
				return;
			}
			
			$(this).find('.sneeit-control-value.ajax').each(function(){
				var data_value = $(this).attr('data-value');				
				
				$(this).append(Sneeit_Controls_Users);
				
				var proper = $(this).find('option[value="'+data_value+'"]');
				if (proper.length == 0) {
					$(this).find('option').first().prop('selected', true);
				} else {
					proper.prop('selected', true);
				}	
				$(this).show().chosen();				
			});
			
			$(this).find('.loading-icon').remove();
			
			$(this).removeClass('pending');
			
		}); // end of color control
		
		/*USERS*/
		$('.sneeit-control-users').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized') && !$(this).is('.pending')) {
				return;
			}
			$(this).addClass('pending');
			if (null === Sneeit_Controls_Users) {
				return;
			}
			
			$(this).find('.sneeit-control-value.ajax').each(function(){
				var data_value = $(this).attr('data-value');
				
				if (typeof(data_value) != 'string') {
					return;
				}
				
				data_value = data_value.split(',');
				
				$(this).append(Sneeit_Controls_Users);
				
				for (var i = 0; i < data_value.length; i++) {
					$(this).find('option[value="'+data_value[i]+'"]').prop('selected', true);					
				}
				
				$(this).show().chosen();				
			});
			
			$(this).find('.loading-icon').remove();
			
			$(this).removeClass('pending');
			
		}); // end of color control
		
		/* SIDEBAR & SIDEBARS */
		$('.sneeit-control-sidebar').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
			
			
			$(this).find('.sneeit-control-sidebar-value[data-value]').each(function(){
				var options = $(this).html() + Sneeit_Controls_Sidebars_Options;
				$(this).html(options);

				var current_value = $(this).attr('data-value');			
//				$(this).remove('data-value');
				$(this).find('option[value="' + current_value +'"]').prop('selected', true);				
				
				$(this).chosen();
				
			});			
		});
		$('.sneeit-control-sidebars').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
			
			
			$(this).find('.sneeit-control-sidebars-value[data-value]').each(function(){
				var options = $(this).html() + Sneeit_Controls_Sidebars_Options;
				$(this).html(options);

				var current_value = $(this).attr('data-value');		
				current_value = current_value.split(',');
				$(this).remove('data-value');
				for (var i = 0; i < current_value.length; i++) {
					$(this).find('option[value="' + current_value[i] +'"]').prop('selected', true);
				}
				
				$(this).chosen();				
			});			
		});
		
		/*SELECTS*/
		$('.sneeit-control-selects').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
			
			
			$(this).find('.sneeit-control-selects-value[data-value]').each(function(){
				var current_value = $(this).attr('data-value');		
				current_value = current_value.split(',');
				$(this).remove('data-value');
				
				// reorder and select
				var value_detach = new Array();				
				for (var i = 0; i < current_value.length; i++) {					
					value_detach.push($(this).find('option[value="' + current_value[i] +'"]'));
				}
				for (var i = 0; i < value_detach.length; i++) {
					value_detach[i].appendTo($(this)).prop('selected', true);
				}
												
				$(this).chosen();
				sneeit_enhance_chosen($(this));
			});	
		});
		
		
		/*CONTENT*/
		$('.sneeit-control-content').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
						
			// init
			sneeit_controls_editor($(this).find('.sneeit-control-content-value'));
		}); // end of color control
		
		/* RADIO */
		$('.sneeit-control-radio').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
			var par = $(this);
			
			// init
			var checked = par.find('.sneeit-control-radio-value:checked');
			if (checked.length == 0) {
				par.find('.sneeit-control-radio-value').first().prop('checked', true);
			}			
		}); // end of color control
		
		/* COLOR */
		$('.sneeit-control-color').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
						
			// init
			$(this).find('.sneeit-control-color-value').each(function(){				
				var current = $(this);
				current.wpColorPicker({
					change: function() {
						current.val( current.wpColorPicker('color') ).change();
					},
				});
			});
		}); // end of color control
		

		/* MEDIA & IMAGE */	
		$('.sneeit-control-media, .sneeit-control-image').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
			var par = $(this);
			var value_holder = par.find('.sneeit-control-value');
			if (value_holder.is('.sneeit-control-image-value') && value_holder.val()) {
				par.find('.sneeit-control-image-preview').html('<img src="'+value_holder.val()+'"/>');
			}			

			$(this).find('.sneeit-control-media-upload-button, .sneeit-control-image-upload-button, .sneeit-control-image-preview').click(function () {									
				par.addClass('sneeit-control-uploading');
				if (Sneeit_Controls_Media_Uploader) {
					Sneeit_Controls_Media_Uploader.open();					
					return;
				}	

				//Extend the wp.media object
				Sneeit_Controls_Media_Uploader = wp.media.frames.file_frame = wp.media({
					multiple: false
				}).on('select', function() {
					var par = $('.sneeit-control-uploading');
					var value_holder = par.find('.sneeit-control-value');
					
					var attachment = Sneeit_Controls_Media_Uploader.state().get('selection').first().toJSON();

					value_holder.val(attachment.url).change();			
					if (value_holder.is('.sneeit-control-image-value')) {
						par.find('.sneeit-control-image-remove-button').removeAttr('disabled');
						par.find('.sneeit-control-image-preview').html('<img src="'+attachment.url+'"/>');
					}
					
					par.removeClass('sneeit-control-uploading');
				}).open();				
			});

			$(this).find('.sneeit-control-image-remove-button').click(function(){				
				par.find('.sneeit-control-image-preview').html('<i class="fa fa-plus"></i>');
				value_holder.val('').change();
				$(this).attr('disabled', 'true');
			});
		}); // end of media and image control
		
		/* RANGE */
		$('.sneeit-control-range').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
			$(this).find('.sneeit-control-range-slider').each(function(){				
				var par = $(this).parents('.sneeit-control-input');
				var slide_holder = $(this);
				var value_holder = par.find('.sneeit-control-range-value');
				
								

				if (value_holder.length) {
					var value = value_holder.val();
					var min = value_holder.attr('min');
					var max = value_holder.attr('max');
					var step = value_holder.attr('step');

					var options = new Object();
					options.value = value;
					options.range = 'min';
					if (typeof(min) != 'undefined' && min !== null && !isNaN(min)) {
						options.min = Number(min);
					} else {
						options.min = 0;
					}
					if (typeof(max) != 'undefined' && max !== null && !isNaN(max)) {
						options.max = Number(max);
					} else {
						options.max = 999;
					}

					if (typeof(step) != 'undefined' && step !== null && !isNaN(step)) {
						options.step = Number(step);
					} else {
						options.step = 1;
					}
					if (isNaN(value)) {
						value = 0;
					}
					value = Number(value);
					if (value < options.min) {
						value = options.min;
					}
					if (value > options.max) {
						value = options.max;
					}
					
					par.find('.sneeit-control-range-value-number').html(value);
					
					options.slide = function(e, ui) {
						value_holder.val(ui.value).change();
						par.find('.sneeit-control-range-value-number').html(ui.value);
					}

					slide_holder.slider(options);

					$(this)
						.find('.ui-slider-range')
						.css('background-color', 
							$('#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu').css('background-color')
						);
				
					
				}
			
			
			});
			
			$(this).find('.sneeit-control-range-value-number').click(function(){
				var input_value = prompt(Sneeit_Controls.text.input_your_value, $(this).html());			

				if (input_value !== null && !isNaN(input_value)) {
					// validate
					input_value = Number(input_value);
					var par = $(this).parents('.sneeit-control-input');
					var value_holder = par.find('.sneeit-control-range-value');

					var min = value_holder.attr('min');
					var max = value_holder.attr('max');				
					if (typeof(min) != 'undefined' && Number(min) > input_value) {
						input_value = min;
					}
					if (typeof(max) != 'undefined' && Number(max) < input_value) {
						input_value = max;
					}

					$(this).html(input_value);
					value_holder.val(input_value).change();
					par.find('.sneeit-control-range-slider').slider('value', input_value);
				}
			});
		}); // end of range control
		
			
		/* VISUAL */
		
		$('.sneeit-control-visual').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
			var par = $(this);
			var value_holder = par.find('.sneeit-control-visual-value');
							
			// for init value
			if (par.find('.sneeit-control-visual-picker').length) {
				par.find('.sneeit-control-visual-picker').removeClass('active').addClass(('inactive'));
				var current_value = value_holder.val();					
				if (par.find('.sneeit-control-visual-picker[data-value="'+current_value+'"]').length) {
					par.find('.sneeit-control-visual-picker[data-value="'+current_value+'"]').addClass('active').removeClass('inactive');
				} else {
					par.find('.sneeit-control-visual-picker').first().each(function(){
						$(this).addClass('active').removeClass('inactive');
						value_holder.val($(this).attr('data-value')).change();
					});
				}	
			}
			
			// when change value
			$(this).find('.sneeit-control-visual-picker').click(function(){
				if ($(this).is('.active')) {
					return false;
				}
				par.find('.sneeit-control-visual-picker.active').removeClass('active').addClass('inactive');

				$(this).removeClass('inactive').addClass('active');
				var current_value = $(this).attr('data-value');
				value_holder.val(current_value).change();
				return false;
			});
		});
		
		
		
		/* FONT FAMILY & FONT */
		$('.sneeit-control-font-family, .sneeit-control-font').each(function(){
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
			var par = $(this);
			var value_holder = par.find('.sneeit-control-value');
			
			
			// if empty font name value, we need to init the value is not empty
			var init_value = value_holder.val();
			if (!par.find('.font-family-value .value').val() && init_value) {
				var init_font_name = '';
				var init_font_style = '';
				var init_font_weight = '';
				var init_font_size = '';

				if (par.is('.sneeit-control-font-family')) {
					init_font_name = init_value;
				} else {
					init_value = init_value.split(' ');
					init_font_style = init_value[0];
					init_font_weight = init_value[1];
					init_font_size = init_value[2];
					init_value[0] = init_value[1] = init_value[2] = '';
					init_font_name = $.trim(init_value.join(' '));
					
					if (init_font_style != 'normal') {
						par.find('.font-style-value').removeClass('inactive').addClass('active');
					}
					if (init_font_weight != 'normal') {
						par.find('.font-weight-value').removeClass('inactive').addClass('active');
					}
					if (init_font_size) {
						par.find('.font-size-value').val(init_font_size);
					}
				}
				par.find('.font-family-value .value').val(init_font_name);
				par.find('.font-family-item[data-font_name="'+init_font_name+'"]')
					.removeClass('inactive').addClass('active');
			}
			
			
			// load init font name if have			
			var init_font_name = par.find('.font-family-value .value').val();			
			if (typeof(Sneeit_Controls_Fonts.google[init_font_name]) != 'undefined' && 
				Sneeit_Font_Loaded_List.length < Sneeit_Font_Load_List.length) {
				
			
				var font_property = Sneeit_Controls_Fonts.google[init_font_name];
				par.find('.font-family-value .value').css('font-family', init_font_name);
				
				if (Sneeit_Font_Loaded_List.indexOf(init_font_name) == -1) { // don't reload in case loaded
					WebFont.load({
						google: {
							families: [
								init_font_name.replace(/ /gi, '+')+':'+
								font_property
							]
						}
					});
					Sneeit_Font_Loaded_List.push(init_font_name);
				}					
			}
			// init style for font family and fake values
			sneeit_controls_makeup_fonts($(this).find('.font-family-item'));
			sneeit_controls_makeup_fonts($(this).find('.font-family-value').find('value'));
			
			// load font when hover font-family-item
			$(this).find('.sneeit-control-font-ui .font-family-item').on('hover', function () {
				if (Sneeit_Font_Hover_Loading || Sneeit_Font_Loaded_List.length >= Sneeit_Font_Load_List.length) {
					return;
				}
				Sneeit_Font_Hover_Loading = true;
				setTimeout(function(){
					Sneeit_Font_Hover_Loading = false;
				}, 1000);
				
				// apply font style to the list
				var font_name = $(this).text();
				if ($(this).is('.google-font') && typeof(Sneeit_Controls_Fonts.google[font_name]) != 'undefined') {
					var font_property = Sneeit_Controls_Fonts.google[font_name];

					if (Sneeit_Font_Loaded_List.indexOf(font_name) == -1) { // don't reload in case loaded
						WebFont.load({
							google: {
								families: [
									font_name.replace(/ /gi, '+')+':'+
									font_property
								]
							}
						});
						Sneeit_Font_Loaded_List.push(font_name);
					}					
				}
			});
			
			// when click fake ui or drop, show the font list
			$(this).find('.sneeit-control-font-ui .font-family-value .value, '+
			  '.sneeit-control-font-ui .font-family-value .drop').click(function() {
				var par = $(this).parents('.font-family');
				var lst = par.find('.font-family-list');
				if (par.is('.collapsed')) {
					par.removeClass('collapsed').addClass('expanded');
					lst.stop().slideDown(200);
				} else {
					par.removeClass('expanded').addClass('collapsed');
					lst.stop().slideUp(200);
				}
			});	

			// when select a font, we must update both hidden input + fake value holder
			$(this).find('.sneeit-control-font-ui .font-family-item').click(function () {
				if ($(this).is('.active')) {
					return;
				}
				var font_name = $(this).text();
				var par = $(this).parents('.sneeit-control-input');
				var pui = par.find('.sneeit-control-font-ui');				
				
				var lst = par.find('.font-family-list');
				var data_key = pui.attr('data-key');
				lst.find('.font-family-item.active').removeClass('active').addClass('inactive');
				$(this).removeClass('inactive').addClass('active');
				var value_holder = par.find('.sneeit-control-value');
				if (value_holder.is('.sneeit-control-font-family-value')) {
					value_holder.val(font_name).change();
				} else {
					var current_value = value_holder.val();
					current_value = current_value.split(' ');
					value_holder.val(current_value[0] + ' ' + 
						current_value[1] + ' ' + 
						current_value[2] + ' ' + 
						font_name).change();
				}

				// show on fake font holder
				var fake_value = pui.find('.font-family-value').find('.value');
				fake_value.val(font_name);
				sneeit_controls_makeup_fonts(fake_value);

				// hide font list
				pui.find('.font-family').removeClass('expanded').addClass('collapsed');
				lst.stop().slideUp(200);
								
				
				// apply font style to the list
				if ($(this).is('.google-font') && 
					typeof(Sneeit_Controls_Fonts.google[font_name]) != 'undefined' &&
					Sneeit_Font_Loaded_List.length < Sneeit_Font_Load_List.length ) {
					var font_property = Sneeit_Controls_Fonts.google[font_name];					
					
					if (Sneeit_Font_Loaded_List.indexOf(font_name) == -1) { // don't reload in case loaded
						WebFont.load({
							google: {
								families: [
									font_name.replace(/ /gi, '+')+':'+
									font_property
								]
							}
						});
						Sneeit_Font_Loaded_List.push(font_name);
					}					
				}
			});

			// font decoration : BOLD, Italic, SIZE
			// italic
			$(this).find('.font-style-value').click(function () {
				var par = $(this).parents('.sneeit-control-input');
				var pui = par.find('.sneeit-control-font-ui');
				var data_key = pui.attr('data-key');				
				var value_holder = par.find('.sneeit-control-value');
				var current_value = value_holder.val();
				current_value = current_value.split(' ');
				
				if ($(this).is('.active')) {
					$(this).removeClass('active').addClass('inactive');
					current_value[0] = 'normal';
				} else {
					$(this).removeClass('inactive').addClass('active');
					current_value[0] = 'italic';
				}
				// show on fake font holder
				pui.find('.font-family-value').find('.value').css('font-style', current_value[0]);
				current_value = current_value.join(' ');
				
				value_holder.val(current_value).change();;
			});
			// bold
			$(this).find('.font-weight-value').click(function () {
				var par = $(this).parents('.sneeit-control-input');
				var pui = par.find('.sneeit-control-font-ui');
				var data_key = pui.attr('data-key');				
				var value_holder = par.find('.sneeit-control-value');
				var current_value = value_holder.val();
				current_value = current_value.split(' ');
				
				if ($(this).is('.active')) {				
					$(this).removeClass('active').addClass('inactive');
					current_value[1] = 'normal';
				} else {
					$(this).removeClass('inactive').addClass('active');
					current_value[1] = 'bold';
				}
				pui.find('.font-family-value').find('.value').css('font-weight', current_value[1]);
				current_value = current_value.join(' ');
				
				value_holder.val(current_value).change();;
			});
			// font size
			$(this).find('.font-size-value').on('change', function () {
				var par = $(this).parents('.sneeit-control-input');
				var value_holder = par.find('.sneeit-control-value');
				var current_value = value_holder.val();
				current_value = current_value.split(' ');
				current_value[2] = $(this).val();
				current_value = current_value.join(' ');			
				value_holder.val(current_value).change();;
			});
		}); /* end of font family and font control */		
		
		
		/* BOX MODEL: box-padding, box-margin, box-position */		
		$('.sneeit-control-box-padding, .sneeit-control-box-margin, .sneeit-control-box-position, .sneeit-control-box-padding-px, .sneeit-control-box-margin-px, .sneeit-control-box-position-px').each(function(){						
			// prevent reinit when ajax finish
			if ($(this).is('.initialized')) {return;}
			var par = $(this);
			
			// init
			sneeit_controls_box_model_value_to_ui(par);
			
			// events & actions
			par.find('.sneeit-control-box-model-ui input:not([readonly])')
				.on('change mouseleave mouseout unfocus', function(){
					sneeit_controls_box_model_ui_to_value(par);						
				}).on('focus click', function(){
					$(this).select();
				});			
		}); // end of box model control		
		
				
		/* *********************
		 * PROCESS RESET BUTTONS
		 */
		$('.sneeit-control-reset-button').each(function(){
			var par = $(this).parents('.sneeit-control');
			if (par.is('.initialized')) {
				return;
			}
			$(this).on('click', function() {
				sneeit_controls_reset(this);
			})
		});
		
		/* *******************
		* PROCESS DEPENDENCIES
		*/
	   // update detonator list
	   $('.sneeit-control-dependency').each(function(){
			if ($(this).is('.initialized')) {
				return;
			}			
			var data_key = $(this).attr('data-key');
			
			
			if (typeof(Sneeit_Controls_Defines[data_key]) != 'undefined' &&
				typeof(Sneeit_Controls_Defines[data_key].show) != 'undefined') {
	   			var dependency = Sneeit_Controls_Defines[data_key].show;
	   			sneeit_controls_dependency_detonator_add(dependency, data_key);	   			
	   		}
	   		if (typeof(Sneeit_Controls_Defines[data_key]) != 'undefined' &&
				typeof(Sneeit_Controls_Defines[data_key].hide) != 'undefined') {
	   			var dependency = Sneeit_Controls_Defines[data_key].hide;
	   			sneeit_controls_dependency_detonator_add(dependency, data_key);
	   		}						
		});
				
		
		// process show / hide depend control
		$('.sneeit-control-value').each(function(){
			var par = $(this).parents('.sneeit-control');
			if (par.is('.initialized')) {
				return;
			}
		
			if ($(this).parents('[class*="controls"]').length) {
				var scope = $(this).parents('[class*="controls"]');				
			} else if ($(this).parents('[id*="controls"]').length) {
				var scope = $(this).parents('[id*="controls"]');
			} else {
				var scope = par.parent();
			}
			
			var detonator_id = par.attr('data-key');
			sneeit_controls_dependency_detonator_onchange(scope, detonator_id); // init for first time
			
			// when a detonator on change
			$(this).on('change', function(){
				var detonator_id = par.attr('data-key');
				sneeit_controls_dependency_detonator_onchange(scope, detonator_id);
			});
			
			// detecting user is typing something on an input
			$(this).on('keydown', function(){
				var val = $(this).val();
				var ths = $(this);
				if (Sneeit_Controls_Is_Typing != null) {
					clearInterval(Sneeit_Controls_Is_Typing);
					Sneeit_Controls_Is_Typing = null;
				}
				Sneeit_Controls_Is_Typing = setInterval(function(){
					if (ths.val() != val) {
						ths.change();
					}
					clearInterval(Sneeit_Controls_Is_Typing);
					Sneeit_Controls_Is_Typing = null;
				}, 300);
			});
		});
		
		
		/* ***************************************
		 * PREVENT RELOAD EFFECTS WHEN AJAX FINISH
		 */
		$('.sneeit-control').each(function(){
			// this is for widget fields
			if ($(this).attr('id').indexOf('__i__') != -1) { 
				$(this).removeClass('initialized'); 
				return;
			}
			$(this).addClass('initialized'); 
		});
	}
	/* ******************************
	* end of control options function 
	* ******************************* */
	
	sneeit_controls_init();
		
	$(document).ajaxSuccess(function() {
		// re-init if have ajax loading
		setTimeout(function(){
			// but must wait 100 milisec for 
			// other application can append their content
			sneeit_controls_init();
		}, 300);		
	});
	/* Listen for the control init event */	
	$(document).on('sneeit_controls_init', function () {		
		sneeit_controls_init();
	});
	
	////////////////////////////////////////////
	// export & import customzer / theme options
	$(document).on('click', '.'+Sneeit_Controls.import_key+'-submit', function(){
		var file = $( 'input[name="'+Sneeit_Controls.import_key+'-file"]' );						
		if ( '' == file.val() ) {
			alert( Sneeit_Controls.text.empty_import );
		}
		else {
			$('#sneeit-theme-options-saving').show();
			$( window ).off( 'beforeunload' );
			$( 'body' ).append(
				'<form class="'+Sneeit_Controls.import_key+'-form" method="POST" enctype="multipart/form-data" action="'+Sneeit_Controls.import_action+'">' +
					'<input type="hidden" value="'+Sneeit_Controls.import_nonce+'" name="'+Sneeit_Controls.import_key+'"/>' +
					'<input type="hidden" value="'+window.location.href+'" name="'+Sneeit_Controls.import_key+'-refer"/>' +
				'</form>'
			);
			var form = $('.'+Sneeit_Controls.import_key+'-form');
			file.appendTo(form);
			$( '.'+Sneeit_Controls.import_key+'-msg').show();
			$(this).hide();
			file.hide();
			form.submit();
		}

		return false;
	});
	
});