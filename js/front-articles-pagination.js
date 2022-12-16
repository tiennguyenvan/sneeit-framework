if (typeof(Sneeit_Articles_Pagination['block_args']) == 'undefined') {
	Sneeit_Articles_Pagination['block_args'] = new Object();
}
var Site_Args = Sneeit_Articles_Pagination['site_args'];
var Block_Args = Sneeit_Articles_Pagination['block_args'];
var Ajax_Url = Sneeit_Articles_Pagination['ajax_url'];
var Infinite = false;
var Master_Class = 'sneeit-articles-pagination';

// BUILD PAGINATION BUTTON HTML
// initialization for static blocks
jQuery.each(Block_Args, function(block_id, block_args){
	if (typeof(block_args['args']['count']) == 'undefined' || 
		!block_args['args']['count']) {
		return;
	}		
	sneeit_articles_pagination_html(block_id, block_args);
});

// init for menu mega content
jQuery( document ).ajaxComplete(function( event, request, settings) {	
	if ( ( 'data' in settings ) && ( settings.data.indexOf( 'action=sneeit_compact_menu_mega_content' ) != -1 ) ) {
		Block_Args = Sneeit_Articles_Pagination['block_args'];

		jQuery.each(Block_Args, function(block_id, block_args){
			// check if we processed this before or not
			if ( ! ( 'args' in block_args ) ||
				 ! ( 'menu_item_id' in block_args['args'] ) || 
				 block_args['args']['menu_item_id'] == -1 ) {
				return;
			}				
			Sneeit_Articles_Pagination['block_args'][block_id]['args']['menu_item_id'] = -1;
			sneeit_articles_pagination_html(block_id, block_args);
		});
	}
});

// show button html
function sneeit_articles_pagination_html(block_id, block_args) {	
	var args = block_args['args'];		
	var found_posts = Number(block_args['found_posts']);		
	var count = Number(args['count']);
	var html = '';		
	var href = ' href="javascript:void(0)"';
	var st_class = ' class="'+Master_Class;		
	
	if (typeof(args['paged']) == 'undefined') {
		var cur_page = 1;
	} else {	
		var cur_page = Number(args['paged']);
	}

	var cur_total = count * cur_page;
	if (cur_total > found_posts) {
		cur_total = found_posts;
	}
	var max_page = Math.ceil(found_posts / count);
	
	if ( 1 == max_page ) {
		return;
	}

	// DESIGN
	var loading_text = '';
	var end_text = '';
	var status_text = '';
	var older_text = '';
	var newer_text = '';

	// HTML for specific type of pagination
	switch (args['pagination']) {
		///////////////////
		case 'number-ajax':
		case 'number-reload':
			// newer button
			if (typeof(Site_Args['number']) != 'undefined' && 
				typeof(Site_Args['number']['newer_text']) != 'undefined') {
				newer_text = Site_Args['number']['newer_text'];
			}				

			// number buttons
			for (var i = 1; i <= max_page; i++) {
				var active = '';
				if (i == cur_page) {
					active = ' active';
				}
				if (i != 1 && i != max_page && i != cur_page &&
					i != cur_page - 1 && i != cur_page - 2 &&
					i != cur_page + 1 && i != cur_page + 2) {
					continue;
				}
				html += '<a'+href+st_class+'-page'+active+'" data-paged="'+i+'" data-block_id="'+block_id+'">'+i+'</a>';

				if (i == 1 && cur_page - 3 > 1 ||
					i == cur_page + 2 && cur_page + 3 < max_page) {
					html += '<span'+st_class+'-sep">...</span>';
				}
			}

			// older buttton
			if (typeof(Site_Args['number']) != 'undefined' && 
				typeof(Site_Args['number']['newer_text']) != 'undefined') {
				older_text = Site_Args['number']['older_text'];
			}

			// pagination status
			if (typeof(Site_Args['number']) != 'undefined' && 
				typeof(Site_Args['number']['status_text']) != 'undefined') {
				status_text = Site_Args['number']['status_text'];
			}
			if (typeof(Site_Args['number']) != 'undefined' && 
				typeof(Site_Args['number']['loading_text']) != 'undefined') {
				loading_text = Site_Args['number']['loading_text'];					
			}

			break;

		////////////////
		case 'loadmore':
			if (typeof(Site_Args['loadmore']) != 'undefined' && 
				typeof(Site_Args['loadmore']['button_text']) != 'undefined' &&
				cur_page < max_page) {
				var button_text = Site_Args['loadmore']['button_text'];
				if (button_text) {
					html += '<a'+href+st_class+'-more" data-paged="'+(cur_page+1)+'" data-block_id="'+block_id+'">'+
						button_text+
						'</a>';						
				}
			}
			if (typeof(Site_Args['loadmore']) != 'undefined' && 
				typeof(Site_Args['loadmore']['loading_text']) != 'undefined') {
				loading_text = Site_Args['loadmore']['loading_text'];					
			}
			if (typeof(Site_Args['loadmore']) != 'undefined' && 
				typeof(Site_Args['loadmore']['end_text']) != 'undefined') {
				end_text = Site_Args['loadmore']['end_text'];
			}
			break;

		////////////////
		case 'infinite':
			if (cur_page < max_page) {
				Infinite = true;
				html += '<div'+st_class+'-anchor" data-paged="'+(cur_page+1)+'" data-block_id="'+block_id+'"></div>';
			}				

			if (typeof(Site_Args['infinite']) != 'undefined' && 
			typeof(Site_Args['infinite']['end_text']) != 'undefined') {
				end_text = Site_Args['loadmore']['end_text'];						
			}

			if (html && typeof(Site_Args['infinite']) != 'undefined' && 
				typeof(Site_Args['infinite']['loading_text']) != 'undefined') {
				loading_text = Site_Args['infinite']['loading_text'];							
			}				
			break;

		////////////////////
		case 'nextprev-ajax':
		case 'nextprev-reload':
			if (typeof(Site_Args['nextprev']) != 'undefined' && 
				typeof(Site_Args['nextprev']['newer_text']) != 'undefined') {
				newer_text = Site_Args['nextprev']['newer_text'];
			}
			if (typeof(Site_Args['nextprev']) != 'undefined' && 
				typeof(Site_Args['nextprev']['newer_text']) != 'undefined') {
				older_text = Site_Args['nextprev']['older_text'];
			}
			if (typeof(Site_Args['nextprev']) != 'undefined' && 
				typeof(Site_Args['nextprev']['status_text']) != 'undefined') {
				status_text = Site_Args['nextprev']['status_text'];
			}
			if (typeof(Site_Args['nextprev']) != 'undefined' && 
				typeof(Site_Args['nextprev']['loading_text']) != 'undefined') {
				loading_text = Site_Args['nextprev']['loading_text'];					
			}
			break;

		////////////////////
		case 'number-reload':
			break;

		//////////////////////
		case 'nextprev-reload':
			break;
	} // end switch pagination type

	// we need to display with inactive or active instead of hide		
	if (newer_text) {
		if (cur_page != 1) {
			var active = '';				
		} else {
			var active = ' active';
		}			
		html = '<a'+href+st_class+'-newer'+active+'" data-paged="'+(cur_page-1)+'" data-block_id="'+block_id+'">'+
				newer_text+
				'</a>' + html;
	}
	// we need to display with inactive or active instead of hide
	if (older_text) {
		if (cur_page != max_page) {
			var active = '';
		} else {				
			var active = ' active';
		}
		html += '<a'+href+st_class+'-older'+active+'" data-paged="'+(cur_page+1)+'" data-block_id="'+block_id+'">'+
				older_text+
				'</a>';
	}
	if (status_text) {
		status_text = status_text.replace('%1$s', cur_total).replace('%2$s', found_posts);
		html += '<span'+st_class+'-status">'+status_text+'</span>';
	}
	if (html) {
		html = '<div'+st_class+'-content">'+html+'</div>';
	}
	if (end_text && cur_total == found_posts) {
		html += '<div'+st_class+'-end">'+end_text+'</div>';
	}
	if (loading_text) {
		html += '<div'+st_class+'-loading" style="display:none">'+loading_text+'</div>';
	}	
	html = '<div'+st_class+' '+Master_Class+'-'+args['pagination']+'">'+html+'</div>';		

	jQuery('#'+block_id).find(Site_Args['pagination_container']).html(html);
} // end function of pagination html
function sneeit_articles_pagination_redirect(paged) {
	var url = document.location.href;
	    
	paged = encodeURI(paged);
	
	// if this is custom paged url
	if (url.indexOf('/page/') != -1) {		
		var redirect_url = url.split('/page/');
		redirect_url[1] = redirect_url[1].split('/');
		
		// that's it
		if (!isNaN(redirect_url[1][0])) {
			redirect_url[1][0] = paged;
			redirect_url[1] = redirect_url[1].join('/');
			document.location.href = redirect_url.join('/page/');			
			return;
		}
	}
	
	if (url.indexOf('#') != -1) {
		url = url.split('#');
		url = url[0];
	}
	
	// the search has no any key
	if (url.indexOf('?') == -1) {
		document.location.href = (url + '?paged=' + paged);
		return;
	}
	
	url = url.split('?');
	url[1] = '&'+url[1];
		
	// the search has no this key
	if (url[1].indexOf('&paged=') == -1) {
		url[1] = url[1].replace('&', '');
		document.location.href = (url.join('?') + '&paged=' + paged);
		return;
	}
	
	// the search has this key
	url[1] = url[1].split('&paged=');
	url[1][1] = url[1][1].split('&');
	url[1][1][0] = paged;
	url[1][1] = url[1][1].join('&');
	url[1] = url[1].join('&paged=');
	url[1] = url[1].replace('&', '');
	document.location.href = url.join('?');
}

function sneeit_articles_pagination_load(e) {	
	if (e.is('.active')) {
		return;
	}

	// get args
	var paged = Number(e.attr('data-paged'));	
	var block_id = e.attr('data-block_id');
	var args = Block_Args[block_id]['args'];				
	var block = jQuery('#'+block_id);		
	var par = block.find('.'+Master_Class);		
	var content = block.find(Site_Args['content_container']);		
	var p_type = args['pagination'];		
	
	// if this is default query, we will redirect
	if (typeof(args['sneeit_query_vars']) != 'undefined' && p_type.indexOf('-reload') > 0) {
		sneeit_articles_pagination_redirect(paged);
		return;
	}
	
	// process args		
	args['paged'] = paged;
	if (p_type != 'loadmore' && p_type != 'infinite') {
		args['index'] = 0;
	}
	args['post__not_in'] = Block_Args[block_id]['loaded_posts']

	// update current page 
	Block_Args[block_id]['paged'] = paged;
	
	// if reload is required,
	// we will just need to use our action
	// to get the redirect links
	if (p_type.indexOf('-reload') > 0) {
		var p_action = 'sneeit_articles_pagination_redirect';
	} else {
		var p_action = 'sneeit_articles_pagination';
	}
	
	// in case mega menu
	var block_par = block.parent();
	if (block.parents('.sneeit-menu-mega-content').length) {
		block_par = block.parents('.sneeit-menu-mega-content').first();			
	}
	if (block_par.is('.sneeit-menu-mega-content')) {
		block_par.css('height', block_par.height() + 'px');
	}		

	// animated
	par.find('.'+Master_Class+'-loading').show();
	par.find('.'+Master_Class+'-content').remove();		
	
	jQuery.post(Ajax_Url, {
		action: p_action, 
		args: JSON.stringify(args),
		callback: Site_Args['ajax_handler']
	}).done(function( data ) {

		// data modifer before ajax
		if (typeof(Site_Args['ajax_function_before']) != 'undefined' && Site_Args['ajax_function_before']) {
			var func = window[Site_Args['ajax_function_before']];
			if (typeof(func) == 'function') {
				data = func(block_id, args, data);
			}				
		}
		
		// append data
		if (p_type.indexOf('-reload') > 0) {
			window.location.href = data;		
			return;
		}
				
		if ('loadmore' == p_type || 'infinite' == p_type) {
			content.append(data);
		} else {
			content.html(data);						
		}

		// regenerate pagination
		var block_args = Block_Args[block_id];
		sneeit_articles_pagination_html(block_id, block_args);		

		// action after ajax
		if (typeof(Site_Args['ajax_function_after']) != 'undefined' && Site_Args['ajax_function_after']) {
			var func = window[Site_Args['ajax_function_after']];
			if (typeof(func) == 'function') {
				func(block_id, args, data);
			}
		}
		if (block_par.is('.sneeit-menu-mega-content')) {
			block_par.css('height', '');
		}

	}); // end ajax event
}

// when click pagination link
jQuery(document).on('click', '.'+Master_Class+'-page, .'+Master_Class+'-newer, .'+Master_Class+'-older, .'+Master_Class+'-more', function () {
	sneeit_articles_pagination_load(jQuery(this));
});	

// when scroll for infinite
var Sneeit_Articles_Pagination_Prev_Win_Top = -1;

if (Infinite) {
	jQuery(window).scroll(function() {
		// find if we have anchor or not
		var anchor = jQuery(document).find('.'+Master_Class+'-anchor');
		if (anchor.length == 0) {		
			return;
		}
		
		
		// if scroll up, we don't need to take action
		var w_top = jQuery(window).scrollTop();
		if (w_top <= Sneeit_Articles_Pagination_Prev_Win_Top) {				
			return;
		}
		Sneeit_Articles_Pagination_Prev_Win_Top = w_top;

		// find if the window bot > anchor top
		var w_bot = w_top + jQuery(window).height();
		anchor.each(function(){				
			var a_top = jQuery(this).offset().top;

			// we don't need to take action
			if (a_top > w_bot) {
				return;
			}

			sneeit_articles_pagination_load(jQuery(this));
		});
	});
}
