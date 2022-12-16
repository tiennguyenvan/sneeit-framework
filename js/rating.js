jQuery(document).ready(function($){
	// add remove button for post review meta box
	$('.'+Sneeit_Rating.prefix+'-criteria label').each(function() {
		$('<a href="javascript:void(0)" class="button button-large '+Sneeit_Rating.prefix+'-remove-criteria">'+Sneeit_Rating.text.remove_criteria+'</a>').appendTo($(this));
	});
	
	$('select.'+Sneeit_Rating.prefix+'-list-review-type').on('change', function(){		
		$('.'+Sneeit_Rating.prefix+'-criteria').hide();
		$('.'+Sneeit_Rating.prefix+'-'+$(this).val()).show();
		
		if ($(this).val() == '') {
			$('.'+Sneeit_Rating.prefix+'-action').hide();
		} else {
			$('.'+Sneeit_Rating.prefix+'-action').show();
		}
	});
	
	$('.'+Sneeit_Rating.prefix+'-add-criteria').click(function(){
		$('.'+Sneeit_Rating.prefix+'-criteria').each(function(){
			if ($(this).css('display') != 'none') {
				var last_label = $(this).find('label').last().clone();
				last_label.appendTo($(this));
				last_label.find('input').val('');
				$(this).find('.hide').removeClass('hide');
			}
		});
	});
	$(document).on('click', '.'+Sneeit_Rating.prefix+'-remove-criteria', function(){
		var par = $(this).parents('.'+Sneeit_Rating.prefix+'-criteria');
		if (par.find('label').length > 1) {
			$(this).parent().remove();
		} else {
			$(this).addClass('hide');
		}
	});
});