(function ($) {			
			
	if (typeof(Sneeit_Compact_Menu) == 'undefined') {
		return;
	}
	/* SCMM: SNEEIT COMPACT MENU MEGA LIBRARIES */
	var SCMM_Cat = new Object();
	function SCMM_Load(link, location) {
		// if this is loading by another processes
		// or it's already activated, we don't need to load more
		if (link.is('.loading')) {		
			return;
		}

		// find content holder
		// if can not find, we out
		var par = link.parent();

		if (par.parents('.menu-item-mega-category').length) {				
			par = par.parents('.menu-item-mega-category').first();
		}
		par = par.find(' > .menu-item-inner > .menu-mega-block > .menu-mega-block-content > .menu-mega-block-content-inner');
		if (!par.length) {				
			return;
		}

		// get cat id and save link object					
		var item_id = Number(link.attr('data-id'));
		// if link was loaded, we will just show
		if (link.is('.loaded')) {
			link.parents('.sneeit-compact-menu').find('.menu-item-object-category > a').removeClass('active');
			link.addClass('active');	
			par.find('.sneeit-menu-mega-content').removeClass('active');
			par.find('.sneeit-menu-mega-content-'+item_id).each(function(){
				$(this).addClass('active');
				if (typeof(sneeit_img_optimize_thumbnail) == 'function') {
					var optimize_images = $(this).find('.sneeit-thumb img');
					if (optimize_images.length) {
						// we need to wait a bit so theme can apply 
						// their changes on the layout
						setTimeout(function(){
							sneeit_img_optimize_thumbnail(optimize_images);
						}, 100);			
					}
				}
			});
			return;
		}

		// finally, we decided to load content
		// pin to notify that we are working on this			
		var cat = Number(link.attr('data-cat'));				
		par.parent().removeClass('loaded');
		SCMM_Cat[location] = cat;

		link.addClass('loading');

		// load mega content
		var args = new Object();
		args.categories = SCMM_Cat[location];
		args.menu_item_id = parseInt(item_id);
		args.has_children = par.parents('.menu-item-object-category').is('.menu-item-has-children');
		args.location = location;
		$.post(Sneeit_Compact_Menu.ajax_url, {
			action: 'sneeit_compact_menu_mega_content', 
			args: JSON.stringify(args),
			callback: Sneeit_Compact_Menu[location]['mega_block_display_callback'],
		}).done(function( data ) {
			link.removeClass('loading');
			if (link.is('.loaded')) {
				// some other process was loaded this before					
				return;
			}
			var html = '<div class="sneeit-menu-mega-content sneeit-menu-mega-content-'+item_id;
			if (cat == SCMM_Cat[location]) {
				par.find('.sneeit-menu-mega-content').removeClass('active');
				html += ' active';
				link.parents('.sneeit-compact-menu').find('.menu-item-object-category > a').removeClass('active');
				link.addClass('active');
			}
			html += '">'+data+'</div>';

			// this is for you to load
			link.addClass('loaded');	
			par.append(html);
			par.parent().addClass('loaded'); // add this to hide loading icon
		});
	}


	/* 
	 * SCMS : "SNEEIT COMPACT MENU STICKY" LIBRARIES 
	 * */
	var SCMS_Index = new Object();
	
	function scms_sticky_menu_enable(holder, holder_clone, scroller, scroller_selector) {		
		
		if (scroller.is('.sneeit-compact-menu-sticky')) {
			return;
		}	
		
		

		/* only need to make this if resize window */		
		holder_clone.css({
			'width': holder.css('width'),
			'height': holder.css('height'),
			'padding': holder.css('padding'),
			'margin': holder.css('margin'),
			'position': holder.css('position'),
			'top': holder.css('top'),
			'left': holder.css('left'),
			'bottom': holder.css('bottom'),
			'right': holder.css('right')
		});		

		/* mirror position from holder */
		scroller.css('left', holder.offset().left);
		scroller.css('width', holder.width()+'px');
		
		var par = scroller.parent();
		par.css('height', par.height() + 'px');
			
		/* if holder is also the scroller, we have to hide the holder */
		if (!holder.is(scroller_selector)) {
			holder.hide();
		}				
		scroller.addClass('sneeit-compact-menu-sticky');
		holder_clone.show();
		par.css('height', '');
	}
	function scms_sticky_menu_disable(holder, holder_clone, scroller) {		
		
		if (!scroller.is('.sneeit-compact-menu-sticky')) {
			return;
		}
		
		scroller.removeClass('sneeit-compact-menu-sticky');
		scroller.css('left', '').css('width', '');		
		holder.css('height', '');
		
		holder_clone.hide();
		holder.show();
	}
	
	/****************/
	/****************/
	/* MAIN PROCESS */
	/****************/
	/****************/
	$.each(Sneeit_Compact_Menu, function (location, args) {				
		/*PROCESS MEGA MENU*/			
		if (typeof(args['mega_block_display_callback']) != 'undefined' && 
			args['mega_block_display_callback']) {

			SCMM_Cat[location] = -1;

			// show mega menu when mouse hover			
			// at beginning when users hover menu before script load visibility
			$('#'+args['container_id']+' .menu > .menu-item-object-category.menu-item-mega-category > .menu-item-inner').each(function(){			
				// found a menu was hover
				if ($(this).css('visibility') != 'hidden') {				
					$(this).parent().find('> a').each(function(){
						SCMM_Load($(this), location);
					});				
				}
			});

			// when hover an menu item category link
			$('#'+args['container_id']+' .menu .menu-item-object-category.menu-item-mega-category > a, .menu .menu-item-mega-category li.menu-item-object-category > a').mouseenter(function () {
				SCMM_Load($(this), location);
			});	
		}

		/* **************
		 * PROCESS STICKY
		 * **************
		 * */
		if (typeof(args['sticky_enable']) != 'undefined' && args['sticky_enable'] != 'disable') {
			SCMS_Index[location] = -1;

			$('.sneeit-compact-menu-'+location).each(function(){		
				/* INIT */
				SCMS_Index[location]++;

				/* collect data */
				var SCMS_Sticky_Menu = args['sticky_enable'];				
				
				/* we will use the holder to keep place
				 * by mirror size attribute from scroller */
				var SCMS_Sticky_Menu_Holder = $(args['sticky_holder']);
				var SCMS_Sticky_Menu_Scroller = $(args['sticky_scroller']);
				
				if (SCMS_Sticky_Menu_Holder.length == 0) {
					SCMS_Sticky_Menu_Holder = $(this);
				}
				if (SCMS_Sticky_Menu_Scroller.length == 0) {
					SCMS_Sticky_Menu_Scroller = $(this);
				}

				/* create cloner of place holder 
				 * and sizing it to hav similar size as holder */
				$('<div class="sneeit-compact-menu-holder-'+SCMS_Index[location]+'"></div>')
					.insertBefore(SCMS_Sticky_Menu_Holder); // !important, use insertBefore only
			
				var SCMS_Sticky_Menu_Holder_Clone = $('.sneeit-compact-menu-holder-'+SCMS_Index[location]);
				SCMS_Sticky_Menu_Holder_Clone.css({
					'width': SCMS_Sticky_Menu_Holder.css('width'),
					'height': SCMS_Sticky_Menu_Holder.css('height'),
					'padding': SCMS_Sticky_Menu_Holder.css('padding'),
					'margin': SCMS_Sticky_Menu_Holder.css('margin'),
					'position': SCMS_Sticky_Menu_Holder.css('position'),
					'top': SCMS_Sticky_Menu_Holder.css('top'),
					'left': SCMS_Sticky_Menu_Holder.css('left'),
					'bottom': SCMS_Sticky_Menu_Holder.css('bottom'),
					'right': SCMS_Sticky_Menu_Holder.css('right'),
					'display' : 'none'
				});				
				var SCMS_Last_Window_Scroll_Top = 0;
				var SCMS_Enabling = false; /* stop sneak scroll caused by show / hide elements */

				/* When Scrolling */
				$(window).scroll(function() {
					
					if (SCMS_Enabling) {
						SCMS_Enabling = false;
						return;
					}

					var holder_top = 0;
					if (SCMS_Sticky_Menu_Scroller.is('.sneeit-compact-menu-sticky')) {
						holder_top = SCMS_Sticky_Menu_Holder_Clone.offset().top;
					} else {
						holder_top = SCMS_Sticky_Menu_Holder.offset().top;
					}
					var window_top = $(window).scrollTop();					
					if (window_top > holder_top) {
						switch (SCMS_Sticky_Menu) {
						case 'up':							
							if (window_top < SCMS_Last_Window_Scroll_Top) {
								scms_sticky_menu_enable(
									SCMS_Sticky_Menu_Holder, 
									SCMS_Sticky_Menu_Holder_Clone, 
									SCMS_Sticky_Menu_Scroller,
									args['sticky_scroller']
								);								
							} else {								
								scms_sticky_menu_disable(
									SCMS_Sticky_Menu_Holder, 
									SCMS_Sticky_Menu_Holder_Clone, 
									SCMS_Sticky_Menu_Scroller
								);		
							}
							break;

						case 'down':
							if (window_top > SCMS_Last_Window_Scroll_Top) {								
								scms_sticky_menu_enable(
									SCMS_Sticky_Menu_Holder, 
									SCMS_Sticky_Menu_Holder_Clone, 
									SCMS_Sticky_Menu_Scroller);
							} else {								
								scms_sticky_menu_disable(
									SCMS_Sticky_Menu_Holder, 
									SCMS_Sticky_Menu_Holder_Clone, 
									SCMS_Sticky_Menu_Scroller);		
							}
							break;
							
						case 'always':
							scms_sticky_menu_enable(
									SCMS_Sticky_Menu_Holder, 
									SCMS_Sticky_Menu_Holder_Clone, 
									SCMS_Sticky_Menu_Scroller,
									args['sticky_scroller']
							);
							break;

						default:							
							scms_sticky_menu_enable(
								SCMS_Sticky_Menu_Holder, 
								SCMS_Sticky_Menu_Holder_Clone, 
								SCMS_Sticky_Menu_Scroller);
							break;
                                                }
                                                
					} else {						
						scms_sticky_menu_disable(
							SCMS_Sticky_Menu_Holder, 
							SCMS_Sticky_Menu_Holder_Clone, 
							SCMS_Sticky_Menu_Scroller);
					}
					SCMS_Last_Window_Scroll_Top = $(window).scrollTop();
					if (SCMS_Last_Window_Scroll_Top != window_top) {
						SCMS_Enabling = true;
					}
				});
			});
		}
		
		/* PROCESS MOBILE MENU */
		if (typeof(args['mobile_enable']) && 
			args['mobile_enable'] && 
			typeof(args['mobile_container'] != 'undefined') &&
			args['mobile_container'] ) {
			if ($(args['mobile_container']).length) {
				$('#'+args['container_id']+' .menu').clone().addClass('sneeit-mob-menu').appendTo($(args['mobile_container']));				
			}
		}
	});
		
	/* ANIMATE FOR MOBILE MENU */
	$('.sneeit-mob-menu .menu-mega-block').remove();
	$('.sneeit-mob-menu .menu-item-inner .sub-menu').unwrap('.menu-item-inner');
	$('.sneeit-mob-menu .menu-item-inner').remove();
	$('.sneeit-mob-menu').each(function(){
		$(this).find('.menu-item-has-children > a .icon-after').html(
			'<span><i class="fa fa-angle-down inactive"></i><i class="fa fa-angle-up active"></i></span>'
		);		
	});
	
	$('.sneeit-mob-menu a').click(function(){
		var href = $(this).attr('href');
		if (typeof(href) == 'undefined' ||
			href == '#' ||
			href == '') {
			$(this).find('> .icon-after').each(function(){
				if ($(this).is('.active')) {
					$(this).removeClass('active');
					$(this).parent().parent().find('> .sub-menu').stop().slideUp();
				} else {
					$(this).addClass('active');
					$(this).parent().parent().find('> .sub-menu').stop().slideDown();
				}
			});

			return false;
		}
		
	});
	$('.sneeit-mob-menu .icon-after').click(function(){
		if ($(this).is('.active')) {
			$(this).removeClass('active');
			$(this).parent().parent().find('> .sub-menu').stop().slideUp();
		} else {
			$(this).addClass('active');
			$(this).parent().parent().find('> .sub-menu').stop().slideDown();
		}
		
		return false;
	});
		
}) (jQuery);
