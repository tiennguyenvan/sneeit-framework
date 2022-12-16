(function ($) {
	if (typeof(Sneeit_Responsive) == 'undefined') {
		return;
	}
	
	/* fill header clone and move content */
	$('.sneeit-mob-ctn-clone').each(function(){
		var clone = $($(this).attr('data-clone'));
		
		if (clone.length == 0) {
			return;
		}
		var clone_container = $(this);
		clone.each(function(){
			$(this).clone().appendTo(clone_container);
		});
	});
	$('.sneeit-mob-ctn-move').each(function(){
		var move = $($(this).attr('data-move'));
		
		if (move.length == 0) {
			return;
		}
		var move_container = $(this);
		move.each(function(){
			$(this).appendTo(move_container);
		});
	});
	
	/* sneeit responsive header sticky */
	if (typeof(Sneeit_Responsive['sticky_enable']) != 'undefined' && 
		Sneeit_Responsive['sticky_enable'] &&
		Sneeit_Responsive['sticky_enable'] != 'disable') {
	
		var Sneeit_Mob_Holder = $('.sneeit-mob');
		var Sneeit_Mob_Holder_Clone = $('.sneeit-mob-clone');
		function sneeit_mob_sticky_enable() {
			if (Sneeit_Mob_Holder.is('.sneeit-mob-sticky')) {
				return;
			}
			
			Sneeit_Mob_Holder_Clone.css({
				'height': Sneeit_Mob_Holder.height()+'px',				
			});				
			Sneeit_Mob_Holder.addClass('sneeit-mob-sticky');
			Sneeit_Mob_Holder_Clone.show();
		}
		function sneeit_mob_sticky_disable() {
			if (!Sneeit_Mob_Holder.is('.sneeit-mob-sticky')) {
				return;
			}
			Sneeit_Mob_Holder.removeClass('sneeit-mob-sticky');
			Sneeit_Mob_Holder_Clone.hide();	
		}
		var Sneeit_Mob_Last_Window_Scroll_Top = 0;
		var Sneeit_Mob_Sticky_Enabling = false;
		/* When Scrolling */
		$(window).scroll(function() {
			if (Sneeit_Mob_Sticky_Enabling) {
				Sneeit_Mob_Sticky_Enabling = false;
				return;
			}
			var holder_top = 0;			
			if (Sneeit_Mob_Holder.is('.sneeit-mob-sticky')) {
				holder_top = Sneeit_Mob_Holder_Clone.offset().top;				
			} else {
				holder_top = Sneeit_Mob_Holder.offset().top;				
			}
			var window_top = $(window).scrollTop();			
			
			if (window_top > holder_top) {				
				switch (Sneeit_Responsive['sticky_enable']) {
					case 'up':
						if (window_top < Sneeit_Mob_Last_Window_Scroll_Top) {
							sneeit_mob_sticky_enable();
						} else {
							sneeit_mob_sticky_disable();
						}
						break;

					case 'down':
						if (window_top > Sneeit_Mob_Last_Window_Scroll_Top) {
							sneeit_mob_sticky_enable();
						} else {
							sneeit_mob_sticky_disable();
						}
						break;

					default:
						sneeit_mob_sticky_enable();
						break;
				}
			} else {
				sneeit_mob_sticky_disable();
			}
			Sneeit_Mob_Last_Window_Scroll_Top = $(window).scrollTop();
			if (Sneeit_Mob_Last_Window_Scroll_Top != window_top) {
				Sneeit_Mob_Sticky_Enabling = true;
			}
		});
	}
	
		
	
	/* box appreanace */
	$('.sneeit-mob-tgl').click(function(){
		if ($(this).is('.sneeit-mob-tgl-left')) {
			var target = $('.sneeit-mob-ctn-left');
			var action = Sneeit_Responsive.left_action;
		} else {
			var target = $('.sneeit-mob-ctn-right');
			var action = Sneeit_Responsive.right_action;
		}
		
		if (action.indexOf(':') != -1) {
			action = action.split(':');
			target = $(action[1]);
			action = action[0];
		} else {
			action = action.split('-');
			action = action[0];
		}
		
		if (target.length == 0) {
			return;
		}
				
		switch (action) {
			case 'pop':
				break;
			default: 
				if ($(this).is('.active')) {
					$(this).removeClass('active');
					target.removeClass('active').stop().slideUp();
					$('.sneeit-mob').removeClass('ctn-active');
				} else {
					$('.sneeit-mob-tgl').removeClass('active');
					$('.sneeit-mob-ctn.active').removeClass('active').stop().slideUp();
					$(this).addClass('active');
					target.addClass('active').stop().slideDown(function(){
						$(this).css('height', '');
					});
					target.find('input').focus();
					$('.sneeit-mob').addClass('ctn-active');
				}
				break;
		}
		
	});
	
}) (jQuery);