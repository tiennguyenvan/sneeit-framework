jQuery(document).ready(function($){
	
	// WIDGET and SIDEBAR TOOLTIP
	$.each(sneeit_widgets.widget_declaration, function (widget_id, widget_declaration) {
		if ('tooltip' in widget_declaration && widget_declaration.tooltip) {
			$('#widget-list .widget[id*="_'+widget_id+'-__i__')
				.addClass('widget-has-tooltip').attr('data-widget_id', widget_id);
		}
	});	
	$.each(sneeit_widgets.sidebar_declaration, function (sidebar_id, sidebar_declaration) {
		if ('tooltip' in sidebar_declaration && sidebar_declaration.tooltip) {
			$('#'+sidebar_id).addClass('sidebar-has-tooltip');
			$('#'+sidebar_id).find('.sidebar-name').addClass('tooltip');			
		}
	});
	
	// SIDEBAR ACCEPT_WIDGETS
	// pending: still need to change in PHP function wp_ajax_save_widget
	// disable widget chooser in widget list select
	$('.widgets-chooser *').removeClass('widgets-chooser-selected');	
	$('#widget-list .widget input[name="id_base"]').each(function(){		
		$(this).parents('.widget').addClass('sneeit-wid-list-' + $(this).val());		
	});
	// search include / exclude widgets
	var sneeit_inc_exc_wids = new Array();		
	var sneeit_wid_chooser_style = '';
	$.each(sneeit_widgets.sidebar_declaration, function (sidebar_id, sidebar_declaration) {
		/* check if sidebar has specific accept widgets */
		if (!('accept_widgets' in sidebar_declaration)) {
			return;
		}
		if (!sidebar_declaration.accept_widgets) {
			return;
		}
				
		/* init accept list for sidebar */
		var wids = sidebar_declaration.accept_widgets.split(',');
		for (var i = 0; i < wids.length; i++) {
			var wid = wids[i];

			/* reject some widgets from adding to this sidebar */
			if (wid.indexOf('!') == 0) {
				wid = wid.substring(1);	

				$('#'+sidebar_id).addClass('reject-wid-'+wid);

				/* add sidebar class widget chooser */
				$('.widgets-chooser li').each(function(){
					var txt = $(this).text();
					if (txt == sidebar_declaration.name) {
						$(this).addClass('reject-wid-'+wid);
					}
				});

				sneeit_wid_chooser_style += 
					'#widget-list .widget.sneeit-wid-list-' + wid + ' .widgets-chooser li.reject-wid-'+wid+'{display:none}';
			}
			/* accept only some widgets to allow adding to this sidebar */
			else {
				$('#'+sidebar_id).addClass('accept-wids accept-wid-'+wid);

				/* add sidebar class widget chooser */
				$('.widgets-chooser li').each(function(){
					var txt = $(this).text();
					if (txt == sidebar_declaration.name) {
						$(this).addClass('accept-wids accept-wid-'+wid);
					}
				});

				sneeit_wid_chooser_style += 
					'#widget-list .widget .widgets-chooser li.accept-wid-'+wid+'{display:none}' +
					'#widget-list .widget.sneeit-wid-list-' + wid + ' .widgets-chooser li{display:none}' +
					'#widget-list .widget.sneeit-wid-list-' + wid + ' .widgets-chooser li.accept-wid-'+wid+'{display:block}';
			}
			if (sneeit_inc_exc_wids.indexOf(wid) == -1) {
				sneeit_inc_exc_wids.push(wid);
			}
		}
		
	});
	
	// update style to hide appropriate sidebar in chooser
	if (sneeit_wid_chooser_style) {
		$('head').append('<style type="text/css">' + sneeit_wid_chooser_style + '</style>');
	}
	
	// apply include / exclude rules
	// - all sidebars which has accept_widgets setting 
	// - must reject all widget at first and then will 
	// - accept only accept-wids widgets
	$('.widget.ui-draggable').draggable(
		'option', 
		'connectToSortable', 
		'div.widgets-sortables:not(.accept-wids)'
	);
	for (var i = 0; i < sneeit_inc_exc_wids.length; i++) {
		var wid = sneeit_inc_exc_wids[i];
		$('.widget.ui-draggable[id*="_'+sneeit_inc_exc_wids[i]+'-__i__"]').draggable(
			'option', 
			'connectToSortable', 
			'div.widgets-sortables.accept-wid-'+wid+':not(.reject-wid-' + wid + ')'
		);
	}	
	
	/* remove all selected sidebar in choose list at beginning */
	$('#widget-list .widget .widget-title').click(function(){		
		setTimeout(function(){
			$('.widgets-chooser *').removeClass('widgets-chooser-selected');
		}, 50);
	});	
	
	
	/* WIDGET TOOLTIP */
	$( document ).tooltip({
		track: true,
		items: '.widget-has-tooltip, .sidebar-has-tooltip .tooltip',
		content: function() {
			var widget_id = $(this).attr('data-widget_id');
			if (typeof(widget_id) != 'undefined') {				
				return '<div class="sneeit-widget-tooltip">'+sneeit_widgets.widget_declaration[widget_id].tooltip+'</div>';
			}
			
			var sidebar_id = $(this).parent().attr('id');
			if (typeof(sidebar_id) != 'undefined' || typeof(sneeit_widgets.sidebar_declaration[sidebar_id]) != 'undefined') {
				return '<div class="sneeit-widget-tooltip">'+sneeit_widgets.sidebar_declaration[sidebar_id].tooltip+'</div>';
			}
			
			return '';
		}
    });
	
	
	// CUSTOM SIDEBARS
	//////////////////////
	if (sneeit_widgets.support_sidebar) {
		// create form to input new custom sidebars
		if (typeof('ajaxurl') != 'undefined') {
			$('.widgets-php h1').append(
				'<a id="sneeit-add-sidebar" class="page-title-action hide-if-no-widgets" href="javascript:void(0)">' +
					sneeit_widgets.text['+ Add New Sidebar'] +
				'</a>'
			);

			var sidebar_format_list = '<select id="sneeit-add-sidebar-format" name="sneeit-add-sidebar-format"><option value="">'+sneeit_widgets.text['Default']+'</option>';
			$.each(sneeit_widgets.sidebar_declaration, function (sidebar_id, sidebar_declaration) {
				sidebar_format_list += '<option value="'+sidebar_id+'">'+sidebar_declaration['name']+'</div>';
			});
			sidebar_format_list += '</select>';


			$('.widgets-php .widget-liquid-left').prepend(
				'<div id="sneeit-add-sidebar-form">'+
					'<div class="sneeit-add-sidebar-error"></div>' +
					'<div class="sneeit-add-sidebar-form-inner">' +
						'<div class="left-col">'+
							'<label for="sneeit-add-sidebar-input">' +
								'<span class="widget-description">'+sneeit_widgets.text['Input Your Sidebar Name']+'</span>'+
								'<input id="sneeit-add-sidebar-input" type="text" placeholder="'+sneeit_widgets.text['Input Your Sidebar Name']+'"/>'+
							'</label>' +
						'</div>' +
						'<div class="right-col">'+
							'<label for="sneeit-add-sidebar-format">'+
								'<span class="widget-description">'+sneeit_widgets.text['Follow Format Of']+'</span>'+
								sidebar_format_list+
							'</label>' +
						'</div>' +
						
						'<div class="clear"></div>' +
					'</div>'+
					'<div class="clear"></div>' +
					'<a id="sneeit-add-sidebar-submit" class="page-title-action hide-if-no-widgets" href="javascript:void(0)">' +
						sneeit_widgets.text['+ Add New Sidebar'] +
					'</a>' +
					'<a id="sneeit-add-sidebar-cancel" class="page-title-action hide-if-no-widgets" href="javascript:void(0)">' +
						sneeit_widgets.text['Cancel'] +
					'</a>' +
				'<div class="clear"></div></div>'
			);

			$('#sneeit-add-sidebar').click(function () {
				$(this).hide();
				$('#sneeit-add-sidebar-form').stop().slideDown();
			});
			$('#sneeit-add-sidebar-cancel').click(function () {
				$('#sneeit-add-sidebar').show();
				$('#sneeit-add-sidebar-form').stop().slideUp();
			});

			$('#sneeit-add-sidebar-submit').click(function () {
				// validate
				var sidebar_name = $('#sneeit-add-sidebar-input').val();
				if (!sidebar_name) {
					$('#sneeit-add-sidebar-error').html('<span>'+sneeit_widgets.text['Your Side Name Is Not Valid']+'</span>');
					return;
				}
				var sidebar_format = $('#sneeit-add-sidebar-format').val();

				// if good, do it
				$.post(ajaxurl, { 
					action: 'sneeit_add_custom_sidebar', 
					name: sidebar_name,
					format: sidebar_format
				}).done(function( data ) {
					if (!data || ((data.indexOf('Warning: ') != -1 || data.indexOf('Fatal error: ') != -1) && data.indexOf(' on line ') != -1)) {
						$('#sneeit-add-sidebar-form').html(
							'<div class="sneeit-add-sidebar-error"><span>'+
								sneeit_widgets.text['Sever Responded an Error Message!']+
								'<br/><br/>' + 
								data+
							'</span></div>'
						);
					} else {						
						location.reload();
					}					
				});
				$('#sneeit-add-sidebar-form').html('<i class="fa fa-cog fa-spin sneeit-custom-sidebar-action-loading-icon"></i>');
			});
		}

		// init UI for new custom sidebars (delete, edit)
		$.each(sneeit_widgets.custom_sidebars, function (sidebar_id, sidebar_declaration) {
			$('#'+sidebar_id).each(function () {
				$(
				'<div class="sidebar-actions">'+
					'<a href="javascript:void(0)" class="sneeit-delete-sidebar" data-id="'+sidebar_id+'">'+
						'<i class="fa fa-trash-o"></i> '+sneeit_widgets.text['Delete Sidebar']+
					'</a>'+
					'<a href="javascript:void(0)" class="sneeit-rename-sidebar" data-id="'+sidebar_id+'">'+
						'<i class="fa fa-i-cursor"></i> '+sneeit_widgets.text['Rename Sidebar']+
					'</a>'+
				'</div>').insertAfter($(this).find('.sidebar-name'));
			});			
		});
		$('.sneeit-delete-sidebar').click(function () {
			var sidebar_id = $(this).attr('data-id');
			if (confirm(sneeit_widgets.text['Are You Sure?'])) {
				$.post(ajaxurl, { 
					action: 'sneeit_delete_custom_sidebar',
					id: sidebar_id
				}).done(function( data ) {						
					if (!data || ((data.indexOf('Warning: ') != -1 || data.indexOf('Fatal error: ') != -1) && data.indexOf(' on line ') != -1)) {
						alert(sneeit_widgets.text['Sever Responded an Error Message!']);
					} else {
						$('#'+sidebar_id).html('<i class="fa fa-cog fa-spin sneeit-custom-sidebar-action-loading-icon"></i>');
						location.reload();
					}						
				});
			}
		});

		$('.sneeit-rename-sidebar').click(function () {
			var sidebar_id = $(this).attr('data-id');
			var sidebar_name = $('#'+sidebar_id).find('.sidebar-name h3').text();
			var new_sidebar_name = prompt(sneeit_widgets.text['Rename Sidebar'], sidebar_name);
			if (new_sidebar_name) {	
				$.post(ajaxurl, { 
					action: 'sneeit_rename_custom_sidebar',
					name: new_sidebar_name,
					id: sidebar_id
				}).done(function( data ) {						
					if (!data || ((data.indexOf('Warning: ') != -1 || data.indexOf('Fatal error: ') != -1) && data.indexOf(' on line ') != -1)) {
						alert(sneeit_widgets.text['Sever Responded an Error Message!']);
					} else {
						$('#'+sidebar_id).html('<i class="fa fa-cog fa-spin sneeit-custom-sidebar-action-loading-icon"></i>');
						location.reload();
					}
				});					
			}				
		});
	}
	
	
	// effect to stick Save and other actions on bottom when scrolling
	function sneeit_widget_action_on_scrolling() {
		$('.widget.open .widget-control-actions').each(function(){
			var w_b = $(window).scrollTop() + $(window).height();
			var par = $(this).parents('.widget.open');
			var w_content = par.find('.widget-content');
			var wc_t = w_content.offset().top;
			var wc_b = wc_t + w_content.height() + 30;
			
			if (w_b < wc_b && w_b > wc_t) {
				$(this).css('width', $(this).width()+'px');
				$(this).addClass('fixed');
			} else {
				$(this).removeClass('fixed');
				$(this).css('width', 'auto');
			}
		});
	}
	$(window).scroll(function() {    	
		sneeit_widget_action_on_scrolling();
	});
	$(document).on('click', '.widget-top *', function(){		
		sneeit_widget_action_on_scrolling();
	});
	
	// effect when click close button
	$(document).on('click', '.widget-control-actions .alignleft a', function(){
		var par = $(this).parents('.widget');
		var w_top = $(window).scrollTop();
		var par_top = par.offset().top;
		var html_pad_top = Number($('html').css('padding-top').replace('px', ''));
		if (par_top - html_pad_top < w_top) {
			$(window).scrollTop(par_top - html_pad_top);
		}
	});


/* Scanning widgets and sidebars jqueryui events */
if (0) {
	/* testing */
	/* sortable event search */
	$('.widget').on('sortactivate', function(){console.log('sortactivate')});
	$('.widget').on('sortbeforestop', function(){console.log('sortbeforestop')});
	$('.widget').on('sortchange', function(){console.log('sortchange')});
	$('.widget').on('sortcreate', function(){console.log('sortcreate')});
	$('.widget').on('sortdeactivate', function(){console.log('sortdeactivate')});
	$('.widget').on('sortout', function(){console.log('sortout')});
	$('.widget').on('sortover', function(){console.log('sortover')});
	$('.widget').on('sortreceive', function(){console.log('sortreceive')});
	$('.widget').on('sortremove', function(){console.log('sortremove')});
	$('.widget').on('sort', function(){console.log('sort')});
	$('.widget').on('sortstart', function(){console.log('sortstart')});
	$('.widget').on('sortstop', function(){console.log('sortstop')});
	$('.widget').on('sortupdate', function(){console.log('sortupdate')});
	
	/* draggable event search */
	$('.widget').on('dragcreate', function(){console.log('dragcreate')});
	$('.widget').on('drag', function(){console.log('drag')});
	$('.widget').on('dragstart', function(){console.log('dragstart')});
	$('.widget').on('dragstop', function(){console.log('dragstop')});
	
	/* droppable event search */
	$('.widget').on('dropactivate', function(){console.log('dropactivate')});
	$('.widget').on('dropcreate', function(){console.log('dropcreate')});
	$('.widget').on('dropdeactivate', function(){console.log('dropdeactivate')});
	$('.widget').on('drop', function(){console.log('drop')});
	$('.widget').on('dropout', function(){console.log('dropout')});
	$('.widget').on('dropover', function(){console.log('dropover')});
	
	/* sortable event search */
	$('#amachow-main-sidebar').on('sortactivate', function(){console.log('sidebar-sortactivate')});
	$('#amachow-main-sidebar').on('sortbeforestop', function(){console.log('sidebar-sortbeforestop')});
	$('#amachow-main-sidebar').on('sortchange', function(){console.log('sidebar-sortchange')});
	$('#amachow-main-sidebar').on('sortcreate', function(){console.log('sidebar-sortcreate')});
	$('#amachow-main-sidebar').on('sortdeactivate', function(){console.log('sidebar-sortdeactivate')});
	$('#amachow-main-sidebar').on('sortout', function(){console.log('sidebar-sortout')});
	$('#amachow-main-sidebar').on('sortover', function(){console.log('sidebar-sortover')});
	$('#amachow-main-sidebar').on('sortreceive', function(){console.log('sidebar-sortreceive')});
	$('#amachow-main-sidebar').on('sortremove', function(){console.log('sidebar-sortremove')});
	$('#amachow-main-sidebar').on('sort', function(){console.log('sidebar-sort')});
	$('#amachow-main-sidebar').on('sortstart', function(){console.log('sidebar-sortstart')});
	$('#amachow-main-sidebar').on('sortstop', function(){console.log('sidebar-sortstop')});
	$('#amachow-main-sidebar').on('sortupdate', function(){console.log('sidebar-sortupdate')});
	
	/* draggable event search */
	$('#amachow-main-sidebar').on('dragcreate', function(){console.log('sidebar-dragcreate')});
	$('#amachow-main-sidebar').on('drag', function(){console.log('sidebar-drag')});
	$('#amachow-main-sidebar').on('dragstart', function(){console.log('sidebar-dragstart')});
	$('#amachow-main-sidebar').on('dragstop', function(){console.log('sidebar-dragstop')});
	
	/* droppable event search */
	$('#amachow-main-sidebar').on('dropactivate', function(){console.log('sidebar-dropactivate')});
	$('#amachow-main-sidebar').on('dropcreate', function(){console.log('sidebar-dropcreate')});
	$('#amachow-main-sidebar').on('dropdeactivate', function(){console.log('sidebar-dropdeactivate')});
	$('#amachow-main-sidebar').on('drop', function(){console.log('sidebar-drop')});
	$('#amachow-main-sidebar').on('dropout', function(){console.log('sidebar-dropout')});
	$('#amachow-main-sidebar').on('dropover', function(){console.log('sidebar-dropover')});

	
	
}	


	
/* allow only certain widget types to add to certain sidebar */
/* waiting ui-draggable ready*/
//$('.widget.ui-draggable[id*="_meta_bar-__i__"]').draggable('option', 'connectToSortable', 'div.widgets-sortables:not(#amachow-main-sidebar)');
//$('.widget.ui-draggable[id*="_button-__i__"]').draggable('option', 'connectToSortable', '#amachow-main-sidebar');

//$('.widgets-chooser *').removeClass('widgets-chooser-selected');

/* also remove from quick add 
 * and then use css (generate by js) to hide
 * */
//console.log($('.widgets-chooser').length());





});
