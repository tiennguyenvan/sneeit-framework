/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(function( $ ) {
	$(document).ready(function(){
		
		// active current value
		
		$(document).find('.sneeit-elementor-control-visual-wrapper').each(function(){
			var inp = $('#'+$(this).find('> label').first().attr('for'));
			var cur_val = inp.val();
			
			$(this).find('> label').each(function(){
				var val = $(this).attr('data-value');
				if (val == cur_val) {
					$(this).addClass('active');
				}
			});
		});
		
		// action when click a visual
		$(document).on('click', '.sneeit-elementor-control-visual-label', function(){			
			if ($(this).is('.active')) {
				return;
			}
			
			$(this).parent().find('label.active').removeClass('active');
			$(this).addClass('active');
		})
	});	
});	