// check if is firefox
var Sneeit_Img_Is_Firefox = typeof InstallTrigger !== 'undefined';
var Sneeit_Img_Is_IE = /*@cc_on!@*/false || !!document.documentMode;
var Sneeit_Img_Is_Retina = false;

function sneeit_img_is_high_density(){
    return ((window.matchMedia && (window.matchMedia('only screen and (min-resolution: 124dpi), only screen and (min-resolution: 1.3dppx), only screen and (min-resolution: 48.8dpcm)').matches || window.matchMedia('only screen and (-webkit-min-device-pixel-ratio: 1.3), only screen and (-o-min-device-pixel-ratio: 2.6/2), only screen and (min--moz-device-pixel-ratio: 1.3), only screen and (min-device-pixel-ratio: 1.3)').matches)) || (window.devicePixelRatio && window.devicePixelRatio > 1.3));
}

function sneeit_img_is_retina(){
    return ((window.matchMedia && (window.matchMedia('only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx), only screen and (min-resolution: 75.6dpcm)').matches || window.matchMedia('only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2/1), only screen and (min--moz-device-pixel-ratio: 2), only screen and (min-device-pixel-ratio: 2)').matches)) || (window.devicePixelRatio && window.devicePixelRatio >= 2)) && /(iPad|iPhone|iPod)/g.test(navigator.userAgent);
}

(function() {
    var root = (typeof exports === 'undefined' ? window : exports);
    
    function Retina() {}

    root.Retina = Retina;

    Retina.isRetina = function(){
        
    };
})();

// process retina images
Sneeit_Img_Is_Retina = (sneeit_img_is_high_density() || sneeit_img_is_retina());

if (Sneeit_Img_Is_Retina) {
	jQuery('img[data-retina!=""]').each(function(){
		jQuery(this).attr('src', jQuery(this).attr('data-retina'));
	});
}

function sneeit_img_srcset_parse(srcset) {
	
	var ret = srcset.split(', ');
	
	// colllect sets
	for (var i = 0; i < ret.length; i++) {
		ret[i] = ret[i].split(' ');
		if (ret[i].length == 2) {
			ret[i][1] = ret[i][1].replace('w', '');
			if (isNaN(ret[i][1])) {
				ret[i][1] = 0;
			}
			ret[i][1] = Number(ret[i][1]);
		}
	}
	
	// remove blank
	for (var i = ret.length - 1; i >= 0; i-=1) {
		if (ret[i].length != 2 || ret[i][1] == 0) {
			ret.splice(i, 1);
		}
	}

	// sort from low to high
	for (var i = 0; i < ret.length - 1; i++) {
		for (var j = i+1; j < ret.length; j++) {
			if (ret[i][1] > ret[j][1]) {
				var temp = ret[j];
				ret[j] = ret[i];
				ret[i] = temp;
			}
		}
	}

	return ret;
}
/*
You must make sure your image container has class name .item-thumbnail and add below css into your style sheet.

.item-thumbnail *, .item-thumbnail img {display:block;max-width: 9999px; max-height: 9999px; padding: 0!important;}
.item-thumbnail {overflow: hidden;display: block;z-index:9;}
*/
function sneeit_img_optimize_thumbnail_image(img) {
	var img = jQuery(img);
	var img_w = img.width();
	
	// this image may be in side a hidden container
	if (!img_w) {						
		img.removeClass('optimizing');
		return;		
	}
	
	// get the rest information		
	var thumb = img.parent().parent();
	var thumb_top = thumb.offset().top;
	var thumb_left = thumb.offset().left;
	var img_top = img.offset().top;
	var img_left = img.offset().left;
	
	var img_h = img.height();
	
	
	
	
	// very large width, may be browser slow when drawing image
	if ((img_w > img_h * 3 || Sneeit_Img_Is_Firefox) && (!img.is('.special'))) {
		img.addClass('special');
		// we must delay a bit to wait it drawing image completely
		setTimeout(function() {
			sneeit_img_optimize_thumbnail_image(img)
		}, 50);
		
		return;
	}
	
	if (thumb_left < img_left && img.parent().is('.sneeit-thumb-landscape')) {
		img
			.parent()
				.removeClass('sneeit-thumb-landscape')
				.addClass('sneeit-thumb-portrait');
		img.css('bottom', '0');
		
		// magazine cover image which has height larger than width
		// so may be main content of image can be on the top of image
		// (must update image width and height each time we resize with classes)
		var img_w = img.width();
		var img_h = img.height();
		
		if (img_h > 1.3 * img_w) {
			// we must allow it go to top
			img.css('bottom','auto');
		}
		
		// fix bug for firefox
		if (Sneeit_Img_Is_Firefox) {		
			var img_h = img.height();
			var thumb_h = thumb.height();
			var img_top = ( img_h - thumb_h ) / 2;
			img.css('top', '-' + img_top + 'px').css('bottom', 'auto');				
		}
	} else if (thumb_top < img_top && img.parent().is('.sneeit-thumb-portrait')) {
		img
			.parent()
				.removeClass('sneeit-thumb-portrait')
				.addClass('sneeit-thumb-landscape');
	}
	
	img.parents('.sneeit-thumb').addClass('optimized');	
	img.removeClass('optimizing');
}
function sneeit_img_optimize_thumbnail(images) {
	if (typeof(images) == 'undefined') {
		images = jQuery('.sneeit-thumb img');	
	}	
	
	// replace small images first
	images.each(function(){
		var img = jQuery(this);
		
		// if some process is optimizing, we skip
		if (img.is('.optimizing')) {
			return;
		}
		img.addClass('optimizing');
		
		// we will polling "skip" images
		// until "skip" class was removed by theme scripts
		// we will poll 100 times
		if (jQuery(this).is('.skip')) {
			var skip_img = this;			
			skip_img.sneeit_img_optimize_skip_thumnail_counter = 0;
			skip_img.sneeit_img_optimize_skip_thumnail = setInterval(function(){				
				if (skip_img.sneeit_img_optimize_skip_thumnail_counter >= 100) {
					clearInterval(skip_img.sneeit_img_optimize_skip_thumnail);
					skip_img.sneeit_img_optimize_skip_thumnail = null;
					jQuery(skip_img).removeClass('optimizing');
					return;
				}
				if (jQuery(skip_img).is(':not(.skip)')) {
					clearInterval(skip_img.sneeit_img_optimize_skip_thumnail);
					skip_img.sneeit_img_optimize_skip_thumnail = null;
					jQuery(skip_img).removeClass('optimizing');
					skip_img.sneeit_img_optimize_skip_thumnail_counter = 0;
					
					// the order is import, place this before return
					sneeit_img_optimize_thumbnail(jQuery(skip_img));						
					return;
				}
				
				skip_img.sneeit_img_optimize_skip_thumnail_counter++;
				
			}, 100);
				
			return;
		}		
		
		var src = img.attr('src');		
		
		// if did not wrapped with resizer
		if (img.parent().is('.sneeit-thumb-f')) {
			img.wrap('<span class="sneeit-thumb-landscape"></span>');
		}

		// process with images from media library
		// to select the right image to display
		var srcset = img.attr('data-ss');
		var src_ds = img.attr('data-s');
		var wid = img.attr('width');
		var hei = img.attr('height');		
						
		// this is an image from library which has all needed data		
		if (typeof(srcset) != 'undefined' && typeof(wid) != 'undefined' && typeof(hei) != 'undefined') {
			src = src_ds;
			wid = Number(wid);
			hei = Number(hei);
			var ss = sneeit_img_srcset_parse(srcset);				
			if (ss.length && wid && hei) {
				// find desire width
				var thumb = img.parents('.sneeit-thumb');			
				var thumb_h = thumb.height();				
				var new_w = thumb.width(); // assume image width = thumb width				
				var new_h = hei * new_w / wid; // so image heigh must be
				
				if (new_h < thumb_h) {
					// but seem new height is smaller than need
					// so we just increase width a bit wider
					new_w = wid * thumb_h / hei;
					new_h = thumb_h;
				}				
								
				// search in srcset to find appropriate src
				var new_src = '';
				
				// just select the largest image, if retina
				if (Sneeit_Img_Is_Retina) {
					new_w = new_w * 2;
				}
								
				
				// find for exactly first
				for (var i = 0; i < ss.length; i++) {
					if (ss[i][1] == new_w) {
						new_src = ss[i][0];
						break;
					}
				}
								
				// still can not found the exactly
				// then we need to scan the nearest (bigger)
				if (!new_src) {
					for (var i = 0; i < ss.length; i++) {
						if (ss[i][1] > new_w) {							
							new_src = ss[i][0];
							break;
						}
					}
					
					// found an image
					if (i < ss.length) {
						if (0 == i)	 { 
							new_src = ss[i][0]; // can not find lower image, choos the smallest
						} else if (ss[i-1][1] >= new_w * 0.9 || sneeit_optimize_img.use_smaller_thumbnails) {
							new_src = ss[i-1][0]; // if the lower not lower than 90%
						} else {
							new_src = ss[i][0]; // just pick the nearest bigger
						}
					}
					// not found any image, choose the biggest
					else {
						new_src = ss[ss.length-1][0]; // just pick the nearest bigger
					}
				}
				
				if (!new_src) {
					new_src = src; // not found, get adta from src
				} else {
					src = new_src; // update src for next processes
				}
				img.attr('src', new_src);
			}
		} else if ((typeof(src) == 'undefined' || !src || src == 'data:image/gif;base64,') && typeof(src_ds) != 'undefined') {			
			src = src_ds;
			img.attr('src', src);
		}
		
		// replace youtube thumbnail to largest image
		if (src.indexOf('youtube.com') != -1 && src.indexOf('/default.') != -1) {			
			img.attr('src', src.replace('/default.', '/mqdefault.'));			
		}		
		
		if (img.parent().is('.sneeit-thumb-a')) {
			img.parent().addClass('optimized');
			img.removeClass('optimizing');
			return;		
		}
		
		// immediately optimize if this was cached
		if (this.complete) {
			sneeit_img_optimize_thumbnail_image(this);
		} 
		// or we must waiting until they loaded
		else {
			img.on('load', function(){
				sneeit_img_optimize_thumbnail_image(this);
			});
		}		
	});
}

function sneeit_bg_thumb() {
	jQuery('.sneeit-bg-thumb').each(function(){
		var bg = jQuery(this).attr('data-bg');
		if (typeof(bg) == 'undefined' || !bg) {
			return;
		}
		
		jQuery(this)
			.css('background-image', 'url('+bg+')')
			.css("filter", "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+bg+"',sizingMethod='scale')")
			.css("-ms-filter", "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+bg+"',sizingMethod='scale')")
			.removeAttr('data-bg');
	});
}


/* trigger optimization events*/
/* when start site */
setTimeout(function(){		
	Sneeit_Img_Is_Retina = (sneeit_img_is_high_density() || sneeit_img_is_retina());
	sneeit_img_optimize_thumbnail();	
	sneeit_bg_thumb();
}, 300);
/* when window resize */
jQuery(window).resize(function(){
	setTimeout(function(){		
		Sneeit_Img_Is_Retina = (sneeit_img_is_high_density() || sneeit_img_is_retina());
		sneeit_img_optimize_thumbnail();		
		sneeit_bg_thumb();
	}, 300);
});
/* when ajax complete*/
jQuery( document ).ajaxComplete(function( event, request, settings) {
	if ('responseText' in request && request.responseText.indexOf('sneeit-thumb') != -1) {
		setTimeout(function(){		
			Sneeit_Img_Is_Retina = (sneeit_img_is_high_density() || sneeit_img_is_retina());
			sneeit_img_optimize_thumbnail(jQuery('.sneeit-thumb:not(.optimized) img'));
			sneeit_bg_thumb();
		}, 300);
	}	
});
