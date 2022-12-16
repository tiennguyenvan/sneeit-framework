// store content to web browser
function sneeit_included_cookie() {
	if ('cookie' in document) {
		return true;
	}
	return false;
}
function sneeit_set_cookie(c_name,value,exdays) {
	if (!sneeit_included_cookie()) {
		return false;
	}
    var exdate=new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value=escape(value) + ((exdays==null) ? '' : '; expires='+exdate.toUTCString())+'; path=/';
    document.cookie=c_name + "=" + c_value;
	if (sneeit_get_cookie(c_name) !== value) {
		return false;
	}
	return true;
}
function sneeit_has_cookie() {
	if (sneeit_set_cookie('test', 'ok')) {
		return true;
	}
	return false;
}
function sneeit_get_cookie(c_name) {
	if (!sneeit_included_cookie()) {
		return '';
	}
    var i,x,y,ARRcookies=document.cookie.split(";");
    for (i=0;i<ARRcookies.length;i++)
    {
        x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
        y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
        x=x.replace(/^\s+|\s+$/g,"");
        if (x==c_name)
        {
            return unescape(y);
        }
    }
	return '';
}
function sneeit_has_storage() {
	if(typeof(localStorage) !== "undefined") {
		return true;
	} 
	return false;
}
function sneeit_set_storage(key,value) {
	if (sneeit_has_storage()) {
		localStorage.setItem(key,value);
		return true;
	}
	return false;
}
function sneeit_get_storage(key) {
	if (sneeit_has_storage()) {
		var ret = localStorage.getItem(key);
		if (ret) {
			return ret;
		}
	}
	return '';
}
function sneeit_update_option(option_name, option_value) {
	if (sneeit_has_storage()) {
		return sneeit_set_storage(option_name, option_value);
	} else if (sneeit_has_cookie()) {
		return sneeit_set_cookie(option_name, option_value);
	}
	return false;
}
function sneeit_get_option(option_name) {
	if (sneeit_has_storage()) {
		return sneeit_get_storage(option_name);
	} else if (sneeit_has_cookie()) {
		return sneeit_get_cookie(option_name);
	}
	return '';
}


(function ($) {
	
	/*POST REVIEW*/
	// review width for init
	$('.'+Sneeit_Rating.id+'-star-bar-top, .'+Sneeit_Rating.id+'-line-bar-top').each(function(){
		var data_value = $(this).attr('data-value');
		
		if (typeof(data_value) != 'undefined' && !isNaN(data_value)) {
			$(this).css('width', data_value+'%');
		}
	});
	
	function sneeit_percent_circle_update(e, percent, percent_text) {
		if (isNaN(percent)) {
			return;
		}
		percent = Number(percent);
		
		// update / add mask if percent > 75
		if (percent > 75) {
			if (e.find('.sneeit-percent-mask').length == 0) {
				$('<span class="sneeit-percent-mask"></span>').insertBefore(e.find('.sneeit-percent-text'));
			}
		// remove mask if percent <= 75
		} else {
			e.find('.sneeit-percent-mask').remove();
		}
		
		// update fill value
		var fill = percent - (percent % 25) + 25;
		e.find('.sneeit-percent-fill').replaceWith('<span class="sneeit-percent-fill sneeit-percent-fill-'+fill+'"></span>');
		
		// modify the modifier
		e.find('.sneeit-percent-modifier').replaceWith('<span class="sneeit-percent-modifier" style="transform: rotate('+(percent * 3.6 + 45)+'deg);"></span>');
		
		// update percent text
		e.find('.sneeit-percent-text').html(percent_text);
	}
	
	// check if support user rating
	$('.'+Sneeit_Rating.id+'-item-user').each(function(){
		var post_id = Number($(this).attr('data-id'));
		var rating_type = $(this).attr('data-type');
		var rating_value = Number($(this).attr('data-value'));
		
		
		// check if user rated or not		
		var user_rated = sneeit_get_option(Sneeit_Rating.id+'-'+rating_type+'-'+post_id);
		
		// process for rating bar
		var process_mouse_out_t = null;// mouser out timer
		var item_name = $(this).find('.'+Sneeit_Rating.id+'-item-name');
		var item_note = $('.'+Sneeit_Rating.id+'-item-user-note');
		var item_bar = $(this).find('.'+Sneeit_Rating.id+'-star-bar');
		var item_bar_top = $(this).find('.'+Sneeit_Rating.id+'-star-bar-top');
		if ('star' != rating_type) {
			item_bar = $(this).find('.'+Sneeit_Rating.id+'-line-bar');
			item_bar_top = $(this).find('.'+Sneeit_Rating.id+'-line-bar-top');
		}
		if (!item_bar.length || 
			!item_bar_top.length || 
			!item_name.length || 
			!item_note.length) {
			return;
		}
		
		item_bar.attr('data-value', rating_value);
		
		
		item_bar
		.each(function(){
			if (user_rated) {
				// you rated
				$(this).addClass('disabled');
				item_note.html(Sneeit_Rating.text.rated.replace('%s', user_rated));
				return;
			} else if (!sneeit_has_storage()) {
				// you did not rated, but browser not support cookie, also storage, so you can not rate
				$(this).addClass('disabled');
				item_note.html(Sneeit_Rating.text.browser_not_support);
				return;	
			}
			if ('star' == rating_type) {
				item_note.html(Sneeit_Rating.text.click_star_rate);
			} else {
				item_note.html(Sneeit_Rating.text.click_line_rate);
			}		
		})
		.mousemove(function(e){
			if ($(this).is('.disabled')) {
				return;
			}
			$(this).addClass('mousemove');
			$(this).removeClass('mouseout');
			if (process_mouse_out_t != 'null' && typeof(process_mouse_out_t) != 'undefined') {
				clearTimeout(process_mouse_out_t);
				process_mouse_out_t = null;
			}

			
			var max_width = $(this).width();		
			

			var ptr_left = e.pageX;	
			// convert point of mouse to value
			item_bar_top.each(function() {
				var cur_left = $(this).offset().left;
				var target_width = ptr_left - cur_left;
				if (target_width < 0) {
					target_width = 0;
				}

				var target_value = Math.floor(target_width * 100 / max_width);			
				if (target_value > 100) {
					target_value = 100;
				}				

				// backward scale
				if ('star' == rating_type) {
					target_value = Math.round(target_value * 5 / 100);
					target_width = Math.round(target_value * max_width / 5);				
				} else if ('point' == rating_type) {
					target_value = Math.round(target_value * 10 / 100);
					target_width = Math.round(target_value * max_width / 10);
				}
				
				$(this).css('width', target_width + 'px').attr('data-target_value', target_value);
				item_note.html(Sneeit_Rating.text.will_rate.replace('%s', target_value));
			});
		})
		.mouseout(function(){
			if ($(this).is('.disabled')) {
				return;
			}
			$(this).removeClass('mousemove');
			if ($(this).is('.mouseout')) {
				return;
			}
			$(this).addClass('mouseout');
			var e = $(this);
			
			process_mouse_out_t = setTimeout(function(){
				$(this).removeClass('mouseout');
				if ($(e).is('.mousemove') || $(e).is('.disabled')) {
					if (process_mouse_out_t != 'null' && typeof(process_mouse_out_t) != 'undefined') {
						clearTimeout(process_mouse_out_t);
						process_mouse_out_t = null;
					}				
					return;
				}

				var data_value = $(e).attr('data-value');
				
				if (typeof(data_value) == 'undefined' || isNaN(data_value)) {
					return;
				}

				item_bar_top.css('width', data_value + '%');
				if ('star' == rating_type) {
					item_note.html(Sneeit_Rating.text.click_star_rate);
				} else {
					item_note.html(Sneeit_Rating.text.click_line_rate);
				}		
			}, 1000);		
		})
		.click(function(){
			if ($(this).is('.disabled')) {
				return;
			}
			$(this).addClass('disabled');
			
			var target_value = item_bar_top.attr('data-target_value');
			if (typeof(target_value) == 'undefined') {
				return;
			}
						
			item_note.html(Sneeit_Rating.text.submitting);
			
			// $.post(wpi_dynamic_js.home_url, { 
			$.post(Sneeit_Rating.ajax_url, { 
				'action': 'sneeit_post_review_user_rating', 
				'id': post_id,
				'value': target_value,
			}).done(function( data ) {
				if (!data || data == '0' || data == '-1' || data.indexOf('**********') == -1) {
					item_note.html(Sneeit_Rating.text.server_not_accept);
					return;
				}
				item_note.html(Sneeit_Rating.text.rated.replace('%s', target_value));
				sneeit_update_option(Sneeit_Rating.id+'-'+rating_type+'-'+post_id, target_value);
				data = data.split('**********');
				
				item_name.html('<strong>'+data[0]+'</strong>');
				
				var average_score = Number(data[1]);
				average_score = average_score.toFixed(1);
				// backward scale
				if ('star' == rating_type) {
					var average_scale_score = Math.round(average_score * 100 / 5);	
					$('.'+Sneeit_Rating.id+'-average-value-text').html(average_score);
					$('.'+Sneeit_Rating.id+'-average-value .'+Sneeit_Rating.id+'-star-bar-top').css('width', average_scale_score + '%');
				} else {
					var average_scale_score = Math.round(average_score * 100 / 10);					
					$('.'+Sneeit_Rating.id+'-average-value-text').find('.sneeit-percent').each(function(){
						sneeit_percent_circle_update($(this), average_scale_score, average_score);
					});
				}
			}).fail(function() {
				item_note.html(Sneeit_Rating.text.server_not_response);
			});
		});
		
		
	}); /*end of checking available user rating*/
	
}) (jQuery);