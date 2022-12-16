(function ($) {	
	if (typeof(Sneeit_Sticky_Columns) == 'undefined' || !Sneeit_Sticky_Columns || $(Sneeit_Sticky_Columns).length == 0) {		
		return;
	}

	// Clone a fake column
	var SSC_Index = 0;		
	
	if (typeof(Sneeit_Sticky_Columns) != 'string' && 
		typeof(Sneeit_Sticky_Columns) != 'String') {
		Sneeit_Sticky_Columns = Sneeit_Sticky_Columns.join(',');
	}
	
	
	
	$(Sneeit_Sticky_Columns).each(function(){		
		var display = $(this).css('display');
		
		// this is a hidden column
		if (typeof(display) == 'undefined' || 'none' == display) {
			return;
		}
		
		// the paraent must be relative
		// and all elements with same level with the column element
		// must be marked with a class
		$(this).parent().css('position', 'relative').find('> *').each(function(){
			if (!$(this).is(Sneeit_Sticky_Columns)) {
				$(this).addClass('ssc-disable');
			}
		});	
		
		// clone this column with empty content		
		var clone = $(this).clone();
//		clone.html('');
		clone.html('<div style="height:1px;clear:both;float:none;margin:0;padding:0"></div>');
				
		// then inserting the clone to the real column
		// and mark it with a class
		$(clone).insertBefore($(this)).hide().addClass('ssc-clone ssc-clone-'+SSC_Index);

		// save data of the clone element to the column element
		// save original display css
		// and mark it as enabled		
		$(this)
			.attr('data-ssc', '.ssc-clone-'+SSC_Index)
			.attr('data-display', display)			
			.addClass('ssc-enable');
		SSC_Index++;
	});
	
	// fixme:
//	return;
	
	// Previous Top of Window before scrolling
	var SSC_PrevWinTop = 0;
	
	// Off set from top in case we have a fixed element
	// on top, example sticky menu
	var SSC_Off_Top = 0;	
	
	function sneeit_sticky_columns() {
		// console.log('>>>>> sneeit_sticky_columns <<<<<');
		// window info
		var win_top = $(window).scrollTop();
		var win_hei = $(window).height();
		var win_bot = win_top + win_hei;	
		
		/* we must have offset on top just in case 
		 * have an element is fixing to prevent hide of column's top */
		if ($('.sneeit-compact-menu-sticky').outerHeight()) {
			SSC_Off_Top = $('.sneeit-compact-menu-sticky').outerHeight();
		}
		
		// with each enabled column
		$('.ssc-enable').each(function(){
			// console.log($(this).attr('id'));
			/* show / hide clone to get original position data */
			var clone = $($(this).attr('data-ssc'));			
			var display = $(this).attr('data-display');
		
			/* to prevent height changing of parent,
			 * you should need to check if your current
			 * sticky column is fixed or not
			 * */
			var par = clone.parent();
			var par_style = par.attr('style');			
			var par_hei = par.height();
			par.css('height', (par_hei+'px')).css('overflow','hidden');
			var prev_clone_display = clone.css('display');
			clone.css('display', display);
			$(this).hide();
			var col_top = clone.offset().top;
			var col_wid = clone.width();
//			clone.hide();
			clone.css('display', prev_clone_display);
			$(this).css('display', display);
			if (par_style != 'undefined' && par_style) {
				par.attr('style', par_style);
			} else {
				par.removeAttr('style');
			}
			
			
			/* the window scrolled over the original top of column
			 * (the close save the original position (incl. top) of real col
			 * so, the real col just return to its natural position
			 * */
			if (win_top < col_top) {			
				// console.log('win_top < col_top', win_top, col_top);
				clone.hide();
				$(this).css({
					'position' : '',
					'top': '',
					'left': '',	
					'bottom' : '',
					'max-width' : '',
				});
				return;
			}
			
			
			var col_hei = $(this).outerHeight();			
			
			// find referer which has max height
			var max_hei = 0;

			$(this).parent().find('> .ssc-disable').each(function(){				
				var hei = $(this).outerHeight();				
				if ($(this).offset().top == col_top && hei > max_hei) {
					max_hei = hei;
				}
			});			
			
			
			/* found nothing to refer or
			 * found one, but its height even lower than this col			
			 */
			if (!max_hei || max_hei < col_hei) {
				// console.log('IF !max_hei || max_hei < col_hei', max_hei, col_hei);
				clone.hide();
				$(this).css({
					'position' : '',
					'top': '',
					'left': '',
					'bottom' : '',
					'max-width' : '',
				});
				return;
			}
			
			
			/* everything is ok, now we calculate the fixed status */
			var col_real_top = $(this).offset().top;
			var col_real_bot = col_real_top + col_hei;
			var ref_bot = col_top + max_hei;
			
			/* in case the col hei is too low */
			if (col_hei <= win_hei) {
				// console.log('IF col_hei <= win_hei', col_hei, win_hei);
				if (win_top < ref_bot - col_hei - SSC_Off_Top) {	
					// console.log(' - if win_top < ref_bot - col_hei - SSC_Off_Top', win_top, ref_bot, col_hei, SSC_Off_Top);
					clone.css('display', display);
					$(this).css({'position' : 'fixed'});
					var margin_left = clone.css('margin-left');
					margin_left = Number(margin_left.replace('px', ''));
					
					$(this).css({
						'top': SSC_Off_Top+'px',
						'bottom' : '',
						'left': (clone.offset().left - margin_left)+ 'px',
						'max-width': col_wid+'px'
					});
				} else {
					// console.log(' - else win_top < ref_bot - col_hei - SSC_Off_Top',					win_top, ref_bot, col_hei, SSC_Off_Top);
					clone.css('display', display);
					$(this).hide();
					var margin_left = clone.css('margin-left');
					margin_left = Number(margin_left.replace('px', ''));					
					$(this).css({					
						'position' : 'relative',
						'top': (max_hei - col_hei) + 'px',
						/* 'left': (clone.offset().left - margin_left) + 'px', */
						'left' : '',
						'bottom' : '', 
						'max-width': col_wid+'px',
					});	
					$(this).css('display', display);
					clone.hide();
				}
			} 
			/* normal col hei is larger than win hei , most of cases*/
			else {	
				// console.log('ELSE col_hei <= win_hei', col_hei, win_hei);
				/* the bottom will over the ref bottom
				 * so just keep the bottom of the col
				 * equal to the ref bot */
				if (win_bot >= ref_bot) {
					// console.log(' - if win_bot >= ref_bot', win_bot, ref_bot);
					clone.css('display', display);
					$(this).hide();
					var margin_left = clone.css('margin-left');
					margin_left = Number(margin_left.replace('px', ''));					
					$(this).css({
						'position' : 'relative',
						'top': (max_hei - col_hei) + 'px',
						/* 'left': (clone.offset().left - margin_left) + 'px', */
						'left' : '',
						'bottom' : '', 
						'max-width': col_wid+'px'
					});	
					$(this).css('display', display);
					clone.hide();
				}
				
				/* is scrolling up */
				else if (win_top < SSC_PrevWinTop) {
					// console.log(' - else if win_top < SSC_PrevWinTop', win_top, SSC_PrevWinTop);
					/* this is not fixing bottom */
					if (col_real_top < col_top) {
						// console.log(' -- if col_real_top < col_top', col_real_top, col_top);
						clone.hide();
						$(this).css({
							'position' : '',
							'top': '',
							'left': '',
							'bottom' : '',
							'max-width' : '',
						});
					} else if (col_real_top > win_top + SSC_Off_Top) {
						// console.log(' -- else if col_real_top > win_top + SSC_Off_Top', col_real_top, win_top, SSC_PrevWinTop );
						clone.css('display', display);
						$(this).css({'position' : 'fixed'});
						var margin_left = clone.css('margin-left');
						margin_left = Number(margin_left.replace('px', ''));
						$(this).css({							
							'top': SSC_Off_Top+'px',
							'bottom' : '',
							'left': (clone.offset().left - margin_left) + 'px',
							'max-width': col_wid+'px'
						});
					} else if ($(this).css('bottom') == '0px' && $(this).css('position') == 'fixed') {
						// console.log(' -- else if 1');
						clone.hide();
						$(this).css({
							'position' : 'relative',
							'top': (col_real_top - col_top) + 'px',
							'left': '',
							'bottom' : '',
							'max-width' : '',
						});						
					}
				}
				/* if scrolling down */
				else if (win_top >= SSC_PrevWinTop) {
					// console.log(' - else if win_top >= SSC_PrevWinTop', win_top, SSC_PrevWinTop);
					/* this is not fixing top */
					if (col_real_bot > ref_bot) {	
						// console.log(' -- if col_real_bot > ref_bot', col_real_bot, ref_bot);
						clone.css('display', display);
						$(this).hide();
						var margin_left = clone.css('margin-left');
						margin_left = Number(margin_left.replace('px', ''));
						
						$(this).css({
							'position' : 'relative',
							'top': (max_hei - col_hei) + 'px',
							/* 'left': (clone.offset().left - margin_left) + 'px',*/
							'left' : '',
							'bottom' : '', 
							'max-width': col_wid+'px'
						});
						$(this).css('display', display);
						clone.hide();
					} else if (col_real_bot < win_bot) {
						// console.log(' -- else if col_real_bot < win_bot', col_real_bot, win_bot);	
						
						clone.css('display', display);
						$(this).css({'position' : 'fixed'});
						
						var margin_left = clone.css('margin-left');
						margin_left = Number(margin_left.replace('px', ''));
						
//						console.log(' ---',							
//							$('#delipress-sub-sidebar.ssc-enable').css('position'),
//							$('.ssc-clone-1').css('display'), 
//							$('.ssc-clone-1').offset().left,
//							$('#delipress-main-sidebar.ssc-enable').css('position'),
//							$('.ssc-clone-0').css('display'), 							
//							$('.ssc-clone-0').offset().left 							
//						);
				
						$(this).css({							
							'top': '',
							'bottom' : '0px',
							'left': (clone.offset().left - margin_left) + 'px',
							'max-width': col_wid+'px'
						});
						
						
						
					} else if (
						$(this).css('top') == (SSC_Off_Top+'px') && 
						$(this).css('position') == 'fixed'
					) {
						// console.log(' -- else if 2');
						
						clone.hide();
						$(this).css({
							'position' : 'relative',
							'top': (col_real_top - col_top) + 'px',
							'left': '',
							'bottom' : '',
							'max-width' : '',
						});	
					}
				}
			}
		});		
	}
	
	// console.log('INIT');
	sneeit_sticky_columns();

	
	// sticky sidebar when scrolling
	$(window).scroll(function() {
		// console.log('SCROLL');
		sneeit_sticky_columns();
		SSC_PrevWinTop = $(window).scrollTop();
	});	
	$( document ).ajaxComplete(function() {		
		// console.log('AJAX');
		sneeit_sticky_columns();		
	});
}) (jQuery);