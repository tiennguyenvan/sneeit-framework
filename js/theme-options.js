jQuery( function ( $ ) {	
	function sneeit_theme_options_show_section(section_id) {
		$('#sneeit-theme-options .panel-item[data-id="'+section_id+'"]').each(function(){
			if ($(this).is('.active') && !$('#sneeit-theme-options').is('.searching')) { 
				// click the activated panel
				return;
			}
			
			$('#sneeit-theme-options').removeClass('searching');
			
			// close and reset all
			$('#sneeit-theme-options .panel-item').removeClass('active').addClass('inactive');
			$('#sneeit-theme-options .section').removeClass('active').stop().slideUp();
			if ($(this).is('.level-0')) {
				$('#sneeit-theme-options .panel-item.level-1').stop().slideUp();
			}			
			
			// active this first
			$(this).addClass('active').removeClass('inactive');
			
			// if this is level 0 panel
			var final_section_id = section_id;
			var child_panels = $('#sneeit-theme-options .panel-item[data-parent_id="'+section_id+'"]');
			if ($(this).is('.level-0') && child_panels.length) {
				// show all children
				child_panels.stop().slideDown().first().addClass('active').removeClass('inactive');
				
				// keep the open the first child section
				final_section_id = child_panels.first().attr('data-id');
			} 
			// if this is a level 1 (child) panel
			else {
				// active the parent also
				var parent_id = $(this).attr('data-parent_id');
				$('#sneeit-theme-options .panel-item[data-id="'+parent_id+'"]')
					.addClass('active').removeClass('inactive');
				// open all child panel
				$('#sneeit-theme-options .panel-item[data-parent_id="'+parent_id+'"]').stop().slideDown();
			}
			
			
			
			// open the right section
			$('#sneeit-theme-options .section[data-id="'+final_section_id+'"]')
				.addClass('active').stop().slideDown();
		});
	}
	 
	// handle panel item effects
	// default active
	var hash = window.location.hash;
	var section_id = $('#sneeit-theme-options .panel-item.level-0').first().attr('data-id');
	if (hash) {
		section_id = hash.replace('#', '');		
	}
	sneeit_theme_options_show_section(section_id);
	
	
	// when click an item
	$('#sneeit-theme-options .panel-item a').click(function(){
		sneeit_theme_options_show_section($(this).parent().attr('data-id'));
	});
	
	// trigger save button
	function sneeit_theme_options_enable_save_button() {
		$('#sneeit-to-save[disabled]').each(function(){			
			$(this).removeAttr('disabled');
			var current_text = $(this).html();
			var switch_text = $(this).attr('data-text');
			$(this).attr('data-text', current_text);
			$(this).html(switch_text);
		});		
	}
	$('body').on('change', '#sneeit-theme-options .controls .sneeit-control *', function(){
		sneeit_theme_options_enable_save_button();
	});
	$('body').on('click', '#sneeit-theme-options .controls .sneeit-control .wp-color-result', function(){
		sneeit_theme_options_enable_save_button();
	});
	
	// reset section button
	$('.sneeit-theme-options-section-reset-button').click(function(){
		var data_id = $(this).attr('data-id');
		var par = $(this).parents('.section[data-id="'+data_id+'"]');
		if (confirm(Sneeit_Theme_Options.text.are_you_sure)) {
			par.find('.sneeit-control-reset-button').each(function(){
				$(this).click();
			});
		}
	});
	
	// reset all button
	$('#sneeit-to-reset').click(function(){
		var par = $(this).parents('#sneeit-theme-options');
		var inp = prompt(Sneeit_Theme_Options.text.type_reset_to_confirm);
		
		if (inp == 'reset') {
			par.find('.sneeit-control-reset-button').each(function(){
				$(this).click();
			});
		}
	});
	
	// search controls
	$('.panel-search-input').keypress(function(e) {
		if(e.which != 13) {
			return;
		}

		var key = $.trim($(this).val());
		if (key) {
			key = key.toLowerCase();
			key = key.split(' ');
			var found = 0;
			$('#sneeit-theme-options .section').removeClass('active').hide();
			$('#sneeit-theme-options .sneeit-control').removeClass('found');
			$('#sneeit-theme-options').addClass('searching');
			$('#sneeit-theme-options .sneeit-control').each(function(){				
				var this_text = $(this).find('.sneeit-control-info').text().toLowerCase();
				for (var i = 0; i < key.length; i++) { // scan all words
					if (this_text.indexOf(key[i]) == -1) {
						break;
					}
				}
				if (i >= key.length) { // matched all words
					found++;
					$(this).addClass('found');
				}				
			});
			$('#sneeit-theme-options .section').each(function(){
				if ($(this).find('.sneeit-control.found').length) {
					$(this).stop().fadeIn();
				}
			});
			var search_result_note = Sneeit_Theme_Options.text.search_result_plural;
			if (1 == found) {
				search_result_note = Sneeit_Theme_Options.text.search_result_single;
			} else if (0 == found) {
				search_result_note = Sneeit_Theme_Options.text.search_result_not_found;
			}
			search_result_note = search_result_note.replace('%s', found);
			$('.search-result-note').html(search_result_note);
		}
	});
	
	// save and publish
	$('#sneeit-to-save').click(function(){
		if ($(this).attr('disabled') != null) {
			return;
		}
		
				
		// process save here
		$('#sneeit-theme-options-saving').show();
		
		var data = new Object();
		$('#sneeit-theme-options .sneeit-control').each(function(){
			var data_key = $(this).attr('data-key');
			var value_holder = $(this).find('.sneeit-control-value');
			var value = '';
			if (value_holder.is('[type="checkbox"]')) {
				if (value_holder.is(':checked')) {
					value = 'on';
				} else {
					value = '';
				}
			} else if (value_holder.is('[type="radio"]')) {
				value_holder.each(function(){
					if ($(this).is(':checked')) {
						value = $(this).val();
					}
				});
			} else {
				value = value_holder.val();
			}
			var type = $(this).attr('data-type');
			
			data[data_key] = new Object();
			data[data_key].value = value;
			data[data_key].type = type;
		});	
				
		$.post(ajaxurl, {
			action: 'sneeit_theme_options_save',
			data: data
		}).done(function() {			
			$('#sneeit-to-save').each(function(){
				$(this).attr('disabled', 'true');
				var text = $(this).text();
				var alt_text = $(this).attr('data-text');
				$(this).text(alt_text);
				$(this).attr('data-text', text);
			});
			$('#sneeit-theme-options-saving').hide();
		});
		
		
		
	});
	
	
	
	
	
	
	// effect to show save on bottom when scroll
	$(window).scroll(function() {    	
		var panel_top = $('#sneeit-theme-options .panel').offset().top;		
		var win_top = $(window).scrollTop();
		if (win_top > panel_top) {
			$('#sneeit-theme-options').addClass('fixed-actions');
		} else {
			$('#sneeit-theme-options').removeClass('fixed-actions');
		}
	});
	
	
});