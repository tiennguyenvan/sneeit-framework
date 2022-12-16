(function( $ ){
	/**
	 * 
	 * @param {type} wrapper
	 * @param {type} options
	 * @returns {sneeit-carousel_L6.SneeitCarousel}
	 * 
	 * @todo
	 * - cross browser
	 * - bug dots move out of AB after drag	
	 * - use refresh rate for responsive to 
	 * calculate exactly column and margin 	 
	 */
	function SneeitCarousel(ele, opt) {		
		this._ = ele;
		this.wid = null;
		this.debug = false;
		
		/**
		 * only original items,
		 * before cloning
		 */
		this.oItemNum = null;
		
		/**
		 * all item, included clones
		 */
		this.items = null;		
		this.outer = null;
		this.stage = null;
		this.dots = null;
		this.itemNum = null;				
		this.x = 0;		
		
		this.xStart = 0;
		this.xEnd = 0;
		
		this.totalDots = 0;		
		/* Javascript float has problem with precision
		 * we will use this to eleminate that precision
		 * The epsilon will never larger than 1/10 pixel */
		this.epsilon = 0;
		
		/* Reset Distance from xStart to xEnd		  
		 */
		this.resetAB = 0;
		
		this.prevColNum = -1;
		
		if (typeof(opt) != 'object') {
			opt = {};
		}
		
		this.rtl = (this._.css('direction') == 'rtl');
				
		
		/*
		 * Total original item must be always 
		 * larger than column number,
		 * Because if it's lower, so we don't 
		 * need to run carousel because
		 * the reader is seeing all items
		 * 
		 * It's simple don't make sense
		 */	
		
		this.opt = $.extend({}, SneeitCarousel.Defaults, opt);
		this.colNum = this.opt.columnNumber;		
		this.margin = this.opt.margin;
		this.wrapperClass = '.' + this.opt.wrapperClass;
		this.outerClass = '.' + this.opt.outerClass;
		this.stageClass = '.' + this.opt.stageClass;
		this.itemClass = '.' + this.opt.itemClass;				
		this.dotsClass = '.' + this.opt.dotsClass;
		this.dotClass = '.' + this.opt.dotClass;				
		this.navClass = '.' + this.opt.navClass;
		this.nextClass = '.' + this.opt.nextClass;
		this.prevClass = '.' + this.opt.prevClass;
		this.activeClass = '.' + this.opt.activeClass;
		
		// validate duration
		if (isNaN(this.opt.displayDuration)) {
			this.opt.displayDuration = SneeitCarousel.Defaults.displayDuration;
		} else {
			this.opt.displayDuration = Number(this.opt.displayDuration);
		}
		if (isNaN(this.opt.animateDuration)) {
			this.opt.animateDuration = SneeitCarousel.Defaults.animateDuration;
		} else {
			this.opt.animateDuration = Number(this.opt.animateDuration);
		}
				
		/* real left */
		this.left = this.rtl ? 'right' : 'left';
		
		/* we use LEFT css instead of 
		 * margin-left because safari not work */
		this.xCSS = this.left;
//		this.xCSS = 'margin-' + this.left;
				
		/* timer for displaying */		
		this.displaying = null;
		
		/* animate */
		this.animating = false;
		
		/* hover */
		this.hover = false;
		
		/* drag */
		this.drag = false;		
		this.dragging = false;		
		
		this.init(false);					
	}
	
	
	/**
	 * Default options for the carousel.
	 * @public
	 */
	SneeitCarousel.Defaults = {
		/* number of columns you want to display */
		columnNumber: 1,
		
		/* item width must be not smaller than 
		 * these amount of pixels or we have 
		 * to auto decrease column number
		 * good for responsive designs
		 * */
		minColumnWidth: 150,		
		
		/* margin between columns, in pixel */
		margin: 20,
		
		/*
		 * The PERCENTAGE of margin width related to
		 * wrapper width. The gap will never
		 * larger than this width
		 * 
		 * The Unit is PERCENT %
		 */
		maxMargin: 6.25,
		
		/* How long we will stop to display before animate to 
		 * other items. Integer number, unit is millsecond
		 */
		displayDuration: 3000,								
		
		/* How long we will need to animate to
		 * other items. Integer number, unit is millsecond
		 */
		animateDuration: 500,
				
		/* dots */		
		dotsClass: 'sneeit-carousel-dots',
		dotClass: 'sneeit-carousel-dot',
		dotText: function (args) {
			return '&bull;'
		},
				
		/* navigation arrows */
		navClass: 'sneeit-carousel-nav',
		prevClass: 'sneeit-carousel-prev',
		nextClass: 'sneeit-carousel-next',				
		prevText: function (args) {
			return '&lang;';
		},
		nextText: function (args) {
			return '&rang;';
		},
		
		/* classes */
		wrapperClass: 'sneeit-carousel',
		outerClass: 'sneeit-carousel-outer',
		stageClass: 'sneeit-carousel-stage',		
		itemClass: 'sneeit-carousel-item',
		activeClass: 'active',
		
		/* callback */
		/* fire before start animate */
		//animate: function(args) {}
		/* fire after finish animate */
		//animated: function(args) {}
	};
	
	SneeitCarousel.prototype.animate = function (x, duration) {
		// we don't need carousel when all items were displayed 
		if (this.colNum >= this.oItemNum) {			
			return;
		}
		
		this.animating = x;
		if (typeof(x) == 'undefined') {
			x = this.x - this.iWid;
		}		
		this.activeDot(x);
		this.activeNav(x);				
		
		var animate_option = {};
		animate_option[this.xCSS] = x + '%';
				
		if (typeof(duration) == 'undefined') {
			duration = this.opt.animateDuration;
		}
		
		var that = this;		
		
		if (typeof(this.opt.animate) == 'function') {		
			this.opt.animate({
				this: that,
				x: x			
			});
		}
		
		if (this.debug) {			
			console.log('animate', x, duration, animate_option, this);	
		}		
		
		this.stage.stop().animate(animate_option, duration, function(){									
			that.x = x;			
			that.animating = false;
			if (typeof(that.opt.animated) == 'function') {		
				that.opt.animated({
					this: that		
				});
			}
			that.display();
		});
	}
	
	SneeitCarousel.prototype.getX = function() {
		if (this.debug) {
			console.log('getX');
		}
		
		
		var x = this.stage.css(this.xCSS);
		x = Number(x.replace('px', ''));		
		
		return x * 100 / this.wid;
	}
	
	
	/* reset current index if 
	 * the window is near the end
	 * of clones 
	 * */
	SneeitCarousel.prototype.resetX = function() {		
	
		var reset = false;
		if (this.x <= this.xEnd) {
			this.x += this.resetAB;
			reset = true;
		} else if (this.x >= this.xStart) {
			this.x -= this.resetAB;
			reset = true;
		}		
		
		if (reset) {
			this.stage.css(
				this.xCSS, this.x + '%'
			);
		}
		
		if (this.debug) {			
			console.log('resetX', reset, this);
		}
	}
	SneeitCarousel.prototype.activeItem = function () {		
		
		/* set active items 
		 * only items have X in Start-End 
		 * will be set as activ
		 * */
		var x = 0;
		var xStart = this.x;
		var xEnd = this.x - this.colNum * this.iWid;
		for (var i = 0; i < this.itemNum; i++) {
			x = - (i * this.iWid + this.iWid / 2);
			
			if (x < xStart && 
				x > xEnd
			) {			
				$(this.items[i]).addClass(this.opt.activeClass);
			} else {				
				$(this.items[i]).removeClass(this.opt.activeClass);
			}			
		}
		
		if (this.debug) {
			console.log('activeItem', x, xStart, xEnd, this);
		}
	}
	SneeitCarousel.prototype.display = function() {	
		
		this.resetX();		
		var that = this;		
		
		/* active dots, navigation and items */
		this.activeDot(this.x);
		this.activeNav(this.x);
		this.activeItem();
		
		if (this.debug) {			
			console.log('display', this);
			return;
		}
		
		
		/* run to next slider */						
		this.displaying = setTimeout(function(){			
			clearTimeout(that.displaying);
			that.displaying = null;
			
			if (that.hover) {
				return;
			}
			
			that.animate();
		}, this.opt.displayDuration);		
		
	}
	
	SneeitCarousel.prototype.stop = function() {	
		if (this.debug) {
			console.log('stop');
		}
		this.stage.stop();
		if (this.displaying != null) {
			clearTimeout(this.displaying);
			this.displaying = null;
		}
		this.x = this.getX();
	}
	
	
	/* we don't need to reinit if the 
	 * column did not change than previous 
	 * but we need to update the values
	 * that have unit in pixel
	 * */
	SneeitCarousel.prototype.init = function(reinit) {		
		
		if (reinit) {			
			this.stop();
			this.colNum = this.opt.columnNumber;
			this.margin = this.opt.margin;
			this.stage.css(this.xCSS, 0);
			this.x = 0;
		}
		
		/* validate column number */
		var items = null;
		
		if (!reinit) {
			items = this._.find('>*');
			this.oItemNum = items.length;
			if (!this.oItemNum) {
				return;
			}

			/* the number must not larger than number of items */
			if (!this.colNum) {
				this.colNum = 1;
			}
			if (this.colNum > this.oItemNum) {
				this.colNum = this.oItemNum;
			}
		}
		
		/* the margin must not larger than max margin */
		this.wid = this._.width();
		this._.css('width', this.wid + 'px');
		if (!this.wid) {		
			return;
		}		
				
		
		if (100 * this.margin / this.wid > this.opt.maxMargin) {
			this.margin = this.opt.maxMargin * this.wid / 100;
		}
				
		/* decrease column number if item width smaller than min */
		var marSum = (this.colNum - 1) * this.margin;
		if (this.colNum <= 1) {
			marSum = 0;
		}	
		
		/* item width in pixel */
		var iWid = (this.wid - marSum) / this.colNum;
		
		for (	;	
				this.colNum > 1 && 
				iWid < this.opt.minColumnWidth;
				this.colNum--
		) {
			marSum = (this.colNum - 1) * this.margin;			
			if (this.colNum <= 1) {
				marSum = 0;
			}
			iWid = this.wid / this.colNum;
			if (this.colNum <= 1 || iWid >= this.opt.minColumnWidth) {
				break;
			}
		}

		if (0 == this.colNum) {
			this.colNum = 1;
		}
		
		/* update again the item width to 
		 * suitable with new column number
		 * marSum is margin sum in pixel
		 * iWid is item width in pixel
		 * */
		marSum = (this.colNum - 1) * this.margin;
		iWid = (this.wid - marSum) / this.colNum;
		
		if (reinit && this.colNum == this.prevColNum) {						
			return;
		}
		this.prevColNum = this.colNum;
				
		
		if (!reinit) {
			/* wrap content with properly class */
			items.wrap('<div class="'+this.opt.itemClass+'"></div>');
			this._.wrapInner('<div class="'+this.opt.stageClass+'"></div>');		
			this._.wrapInner('<div class="'+this.opt.outerClass+'"></div>');

			/* save element objects */
			items = this._.find(this.itemClass);		
			this.stage = this._.find(this.stageClass);
			this.outer = this._.find(this.outerClass);

			/* clone items */		
			items.clone().prependTo(this.stage);
			items.clone().appendTo(this.stage);
			this.items = this._.find(this.itemClass);
			this.itemNum = this.items.length;			
		}
		
		/* CALCULATE EVERYTHING IN PERCENT 
		 * */				
		/* stage width in pixel
		 * */
		var stageWidPx = this.itemNum * iWid + (this.itemNum - 1) * this.margin;		
		
		/* margin width related to stage in percent 
		 * */
		var iMarStage = this.margin * 100 / stageWidPx;
		
		/* item width related to wrapper in percent 
		 * = item width in pixel + margin in pixel convert to percent
		 * */
		this.iWid = (iWid * 100 / this.wid) + (this.margin * 100 / this.wid);
		
		/* accept precision in JavaScript float */
		this.epsilon = 100 / this.wid / 10;
		
		/* the distance that allows you jump
		 * to similar item index in carousel 
		 * */
		this.resetAB = this.oItemNum * this.iWid;
		
		/* the boudaries of x that you need 
		 * to reset if x go out of these */
		this.xStart = 
			- (this.oItemNum - this.colNum - 1) * this.iWid;
		this.xEnd = 
			- (this.oItemNum * 2 + this.colNum - 2) * this.iWid;
		this.totalDots = Math.ceil(this.oItemNum / this.colNum);		
				

		/* init styles */
		this.outer.css({
			'overflow' : 'hidden',
			'position': 'relative'
		});		
		
		this.stage.css({
			'position' :'relative',
			'width': (stageWidPx * 100 / this.wid) + '%',
			'max-width' : 'none'
		});
		
		
		this.items.css({
			'width':  (iWid * 100 / stageWidPx) + '%',
			'float': this.left
		});		
		
		if (iMarStage) {
			this.items.css('margin-' + this.left, iMarStage + '%');
		}
		
		$(this.items[0]).css('margin-' + this.left, 0);
		if (this.debug) {
			console.log('init', this);
		}		
		
		/* display and enable everything */
		this.display();		
		this.enableDots();		
		
		if (!reinit) {
			this.enableDrag();
			this.enableKeyboard();
			this.enableResponsive();
			this.enableNav();
			this.enableHover();
			
			/* add this to fire finish event */
			this._.addClass(this.opt.wrapperClass);
		}
		
		
	}	
	SneeitCarousel.prototype.getPageX = function(e) {		
		if (this.debug) {
			console.log('getPageX');
		}
		
		if (typeof(e['pageX']) == 'undefined' && 
			typeof(e['originalEvent']['touches'][0]['clientX']) != 'undefined') {
			return e['originalEvent']['touches'][0]['clientX'];
		}
		return e.pageX;
	}
	
	/**
	 * Detect 
	 * @returns {undefined}
	 */
	SneeitCarousel.prototype.dragStart = function() {
		
	}
	SneeitCarousel.prototype.dragMove = function() {
		
	}
	SneeitCarousel.prototype.dragMove = function() {
		
	}
	SneeitCarousel.prototype.enableDrag = function() {		
		if (this.debug) {
			console.log('enableDrag');
		}
		/* process mouse drag */
		var that = this;
	
		this.stage.on('mousedown', function(e){						
			e.preventDefault();
			
			// we are in dragging so we don't need 
			// to start another one
			if (that.drag !== false) {				
				return false;
			}
						
			
			/* process if touch event */
			e.pageX = that.getPageX(e);				

			/* stop stage, update X and curMargin */
			that.drag = e.pageX;			
			that.stop();
						
			
			return false;
		});
		$(document).on('mousemove', function(e){
			// we only move things if have no 
			// dragging process on the stage now
			if (that.drag === false) {				
				return;
			}			
			
			e.preventDefault();
			
			/* process if touch event */
			e.pageX = that.getPageX(e);
			
			/* calculate for target x */
			if (that.rtl) {				
				that.x -= (e.pageX - that.drag) * 100 / that.wid;
			} else {
				that.x += (e.pageX - that.drag) * 100 / that.wid;
			}
			
			if (e.pageX < that.drag) {
				that.dragging = 'left';
			} else if (e.pageX > that.drag) {
				that.dragging = 'right';
			}			
			
			that.drag = e.pageX;			
			that.stage.css(that.xCSS, that.x + '%');
			that.resetX();
			
			return false;
		});		

		/* preventing move to href after ending drag on an A tag */
		this.stage.find('a[href]').on('click', function(e){			
			if (that.dragging) {				
				e.preventDefault();
				return false;
			}
		});
		
		/* when mouse up (end of mouse drag), we will recalculate x,
		 * also animate item back to nearest item border 
		 * Priority animate to direction of the mouse move
		 * */
		$(document).on('mouseup', function(){
			// if we did not drag, so we don't need to reset
			if (that.drag === false) {				
				return;
			}
			
			var x = 0;			
			for (var i = 0; i < that.itemNum - 1; i++) {				
				x = - (i * that.iWid);																
				
				/* find the item which contain current x point */
				if (that.x < x - that.epsilon && 
					that.x > x - that.iWid + that.epsilon) {
					break;
				}
			}
			
			if (i >= that.itemNum - 1) {
				that.drag = false;
				that.dragging = false;			
				that.display();
				return;
			}
			
			/* animate depending on drag direction and site direction 
			 * to animate to right position */
			var x = - (i * that.iWid);
			if (that.dragging == that.left) {
				x -= that.iWid;
			}										
			that.animate(x);
			
			/* we don't set down immediately 
			 * to prevent click events from a tags */
			setTimeout(function(){
				that.drag = false;
				that.dragging = false;
			}, 5);	
		});
		
		//////////////////
		// MOBILE CONTROL
		// ///////////////
		// Detect touch support
		$.support.touch = 'ontouchend' in document;

		// Ignore browsers without touch support
		if (!$.support.touch) {
		  return;
		}
		this.stage.on('touchmove', function(e) {			
			
			// this is multi touch, not swipe
			if (e.originalEvent.touches.length > 1) {
				return;
			}
			var touch = e.originalEvent.changedTouches[0];
			if (that.touch == null) {
				that.touch = touch;				
				return;
			}						
			
			var delX = touch.pageX - that.touch.pageX;
			var delY = touch.pageY - that.touch.pageY;			
			
			// this is not horizontal swipe, so return
			if (Math.abs(delX) < Math.abs(delY)) {				
				that.touch = touch;
				return;
			}
			
			// CONTROL THE CAROUSEL
			///////////////////////
			
			// Simulate MouseDownEvent			
			/* stop stage*/
			that.stop();
						
			
			// Simulate MouseMoveEvent					
						
			/* process if touch event */
			e.pageX = touch.pageX;
			
			/* calculate for target x so we can finish 
			 * the move when touchend */
			if (that.rtl) {				
				that.x -= delX * 100 / that.wid;
			} else {
				that.x += delX * 100 / that.wid;
			}
			
			if (delX < 0) {
				that.dragging = 'left';
			} else if (delX > 0) {
				that.dragging = 'right';
			}
						
			that.stage.css(that.xCSS, that.x + '%');
			that.resetX();	
		
			// update touch
			///////////////
			that.touch = touch;
		});
		this.stage.on('touchend', function(e) {
			if (that.touch === null) {
				return;
			}
			
			// cleanup touch
			that.touch = null;

			var x = 0;			
			for (var i = 0; i < that.itemNum - 1; i++) {				
				x = - (i * that.iWid);																

				/* find the item which contain current x point */
				if (that.x < x - that.epsilon && 
					that.x > x - that.iWid + that.epsilon) {
					break;
				}
			}

			if (i >= that.itemNum - 1) {				
				that.dragging = false;			
				that.display();
				return;
			}

			/* animate depending on drag direction and site direction 
			 * to animate to right position */
			var x = - (i * that.iWid);
			if (that.dragging == that.left) {
				x -= that.iWid;
			}										
			that.animate(x);

			/* we don't set down immediately 
			 * to prevent click events from a tags */
			setTimeout(function(){
				that.dragging = false;
			}, 5);	
			
		});
	}
	
	
	SneeitCarousel.prototype.activeDot = function(x) {
		if (this.debug) {
			return;			
		}
		if (!this.dots) {			
			return;
		}
		
		var i = Math.round(Math.abs(x / this.iWid)) % this.oItemNum;
		i = Math.floor(i / this.colNum);
		this.dots.removeClass(this.opt.activeClass);
		$(this.dots[i]).addClass(this.opt.activeClass);
	}
	
	/**
	 * Enable dots as pagination for the carousel
	 * @returns {undefined}
	 */
	SneeitCarousel.prototype.enableDots = function() {		
		if (this.debug) {
			console.log('enableDots');
		}
		if (!this.totalDots || 
			!this.opt.dotsClass ||			
			!this.opt.dotClass
		) {
			return;
		}
		
		
		/* append dot raw HTML content to wrapper */
		var dots = '';
		var that = this;
		for (var i = 0; i < this.totalDots; i++) {
			var dot = '';
			if (typeof(this.opt.dotText) == 'function') {
				dot = this.opt.dotText({
					this: that,
					dotIndex: i,					
				});
			} else {
				dot = this.opt.dotText;
			}
			
			/* seem the user disable the dot, so just return */
			if (dot === false) {				
				return;
			}
			dots += '<div class="' + this.opt.dotClass + '" data-index="'+i+'">'+dot+'</div>';
		}
		
		/* if reinit, we have to 
		 * remove all and remake html */
		if (this.dots != null) {
			this._.find(this.dotsClass).html(dots);
		} else {
			dots = '<div class="' + this.opt.dotsClass + '">'+dots+'</div>';
			this._.append(dots);
		}
		
		this.dots = this._.find(this.dotClass);
		$(this.dots[0]).addClass(this.opt.activeClass);
		
		
		/* action when click dots */		
		this.dots.click(function(){						
			var index = $(this).attr('data-index');			
			if (typeof(index) == 'undefined' || isNaN(index)) {
				return;
			}
			
			that.stop();
			
			/* set dot index and active state */
			index = Number(index);						
//			that.dots.removeClass(that.opt.activeClass);
//			$(this).addClass(that.opt.activeClass);
			
			/* calculate nearest target x */				
			var x = - index * that.colNum * that.iWid;	
			var AB = x - that.x;
			var maxMatchPos = that.itemNum / that.oItemNum;
			var stepMatch = that.resetAB;
			for (var i = 1; i < maxMatchPos; i++) {
				var _x = x - i * stepMatch;
				var _AB = _x - that.x;
				if (Math.abs(_AB) < Math.abs(AB)) {
					AB = _AB;
				}
			}
			x = AB + that.x;
			
			/* it already stayed at the right position */
			if (Math.abs(AB) < that.epsilon) {				
				that.display();
				return;
			}			
			
			that.animate(x);
		});
	}
	
	SneeitCarousel.prototype.activeNav = function(x) {				
		if (this.debug) {
			console.log('activeNav', x, this);
			return;			
		}
		/* previous arrow */
		var prevText = '';
		var that = this;
		if (typeof(this.opt.prevText) == 'function') {
			prevText = this.opt.prevText({
				this: that
			});
		} else {
			prevText = this.opt.prevText;
		}
		if (prevText !== false && this.opt.prevClass) {
			this._.find(this.prevClass).html(prevText);
		}				
		
		
		/* next arrow */
		var nextText = '';
		if (typeof(this.opt.nextText) == 'function') {
			nextText = this.opt.nextText({
				this: that
			});
		} else {
			nextText = this.opt.nextText;
		}
		if (nextText !== false && this.opt.nextClass) {
			this._.find(this.nextClass).html(nextText);
		}
	}
	
	SneeitCarousel.prototype.enableNav = function() {		
		if (this.debug) {
			console.log('enableNav');
		}
		if (!this.opt.navClass) {
			return;
		}
		/* append arrow HTML content to wrapper */
		var arrowsContent = '';		
		var that = this;
		
		/* previous arrow */
		var prevText = '';
		if (typeof(this.opt.prevText) == 'function') {
			prevText = this.opt.prevText({
				this: that
			});
		} else {
			prevText = this.opt.prevText;
		}
		if (prevText && this.opt.prevClass) {
			arrowsContent += '<div class="' + this.opt.prevClass + '">'+prevText+'</div>';
		}		
		
		
		/* next arrow */
		var nextText = '';
		if (typeof(this.opt.nextText) == 'function') {
			nextText = this.opt.nextText({
				this: that
			});
		} else {
			nextText = this.opt.nextText;
		}
		if (nextText && this.opt.nextClass) {
			arrowsContent += '<div class="' + this.opt.nextClass + '">'+nextText+'</div>';			
		}
		
		if (!arrowsContent) {
			return;
		}		
				
		this._.append('<div class="' + this.opt.navClass + '">' + arrowsContent + '</div>');		
		
		/* action when click arrows */		
		/* when click Previous Arrow */
		if (this.opt.prevClass) {
			this._.find(this.prevClass).click(function(){								
				that.prev();
			});
		}
		
		/* when click Next Arrow */		
		if (this.opt.nextClass) {
			this._.find(this.nextClass).click(function(){				
				that.next();
			});
		}
	}
	
	SneeitCarousel.prototype.prev = function() {		
		if (this.debug) {
			console.log('prev');
		}
		this.stop();		
		var i = Math.floor(this.x / this.iWid);
		var x = (i + 1) * this.iWid;
		
		/* the distance must be at least larger 
		 * than one item width */
		if (this.x - x > - this.iWid + this.epsilon) {
			x += this.iWid;
		}
		
		/* reset here to prevent move 
		 * out of the view area */
		if (x > this.xStart) {
			this.x -= this.resetAB;
			x -= this.resetAB;
			this.stage.css(this.xCSS, this.x +'%');
		}		

		// animate to the target
		this.animate(x, 90);
	}
	SneeitCarousel.prototype.next = function() {		
		if (this.debug) {
			console.log('next');
		}
		this.stop();		
		var i = Math.floor(Math.abs(this.x / this.iWid));
		var x = -(i + 1) * this.iWid;
		
		/* the distance must be at least larger 
		 * than one item width */
		if (Math.abs(this.x) > i * this.iWid + this.epsilon) {					
			x -= this.iWid;
		}
		
		/* reset here to prevent move 
		 * out of the view area */
		if (x < this.xEnd) {
			this.x += this.resetAB;
			x += this.resetAB;
			this.stage.css(this.xCSS, this.x +'%');
		}				
		// animate to the target
		this.animate(x, 90);
		
	}
	
	SneeitCarousel.prototype.enableKeyboard = function() {		
		if (this.debug) {
			console.log('enableKeyboard');
		}
		var that = this;
		$(document).keydown(function(e){			
			switch(e.which) {
				case 37: // left
					that.prev();
					break;				

				case 39: // right
					that.next();
					break;

				default: return; // exit this handler for other keys
			}
			e.preventDefault(); // prevent the default action (scroll / move caret)
		});
	}
	
	SneeitCarousel.prototype.enableResponsive = function() {		
		if (this.debug) {
			console.log('enableResponsive');
		}
		var that = this;
		$(window).resize(function(){			
			that._.css('width', '');
			that.init(true);
		});
	}
	
	/**
	 * stop and resume when mouse hover
	 * @returns {undefined}
	 */
	SneeitCarousel.prototype.enableHover = function() {		
		if (this.debug) {
			console.log('enableHover');
		}
		var that = this;
		this._.on('mouseenter', function(){									
			that.hover = true;	
		});
		this._.on('mouseleave', function(){						
			that.hover = false;						
			/* only resume if the slider is not displaying
			 * (which will auto animate after the end) 
			 * and not animating (because if animating 
			 * then we don't need to resume
			 * */
			if (!that.displaying && that.animating === false) {
				that.display();
			}
		});
	}
	
	
	/**
	 * The jQuery Plugin for the Sneeit Carousel
	 * @todo Navigation plugin `next` and `prev`
	 * @public
	 */
	$.fn.sneeitCarousel = function(option) {						
		return this.each(function() {			
			new SneeitCarousel($(this), option);
		});		
	};
})( jQuery );