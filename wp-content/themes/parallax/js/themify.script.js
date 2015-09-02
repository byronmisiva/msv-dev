// Fix iPhone viewport scaling bug on orientation change - By @mathias @cheeaun @jdalton
;if(navigator.userAgent.match(/iPhone/i)){(function(doc){
	var addEvent = 'addEventListener', type = 'gesturestart', qsa = 'querySelectorAll',
		scales = [1, 1], meta = qsa in doc ? doc[qsa]('meta[name=viewport]') : [];
	function fix() {
		meta.content = 'width=device-width,minimum-scale=' + scales[0] + ',maximum-scale=' + scales[1];
		doc.removeEventListener(type, fix, true); }

	if ((meta = meta[meta.length - 1]) && addEvent in doc) {
		fix(); scales = [0.25, 1.6];
		doc[addEvent](type, fix, true);	}
}(document));}

// Themify Lightbox, Fixed Header and Parallax /////////////////////////
var ThemifyGallery, FixedHeader, ThemifyParallax;

// Begin jQuery functions
(function($) {
	
	$(window).resize(function() {		
		$('#headerwrap').css( { 'height' : $(window).height() } );		
	});	
	
	$("[rel='tooltip']").tooltip();    
	 
    $('#experiencias .section-inner .section-content .loops-wrapper .thumbnail').hover(
        function(){
            $(this).find('.caption-experiencias').fadeIn(250); //.slideDown(250)
        },
        function(){
            $(this).find('.caption-experiencias').fadeOut(250); //.slideUp(205)
        }
    ); 

	function UpdateQueryString(a,b,c){
		c||(c=window.location.href);var d=RegExp("([?|&])"+a+"=.*?(&|#|$)(.*)","gi");if(d.test(c))return b!==void 0&&null!==b?c.replace(d,"$1"+a+"="+b+"$2$3"):c.replace(d,"$1$3").replace(/(&|\?)$/,"");if(b!==void 0&&null!==b){var e=-1!==c.indexOf("?")?"&":"?",f=c.split("#");return c=f[0]+e+a+"="+b,f[1]&&(c+="#"+f[1]),c}return c;
	}

	// Themify Scroll
	(function($, window, document, undefined) {

		// Create the defaults once
		var pluginName = "themifyScroll", defaults = {
			speed : 1800
		};

		// The actual plugin constructor
		function Plugin(element, options) {
			this.element = element;
			this.options = $.extend({}, defaults, options);
			this._defaults = defaults;
			this._name = pluginName;
			this.onClicking = false;
			this.init();
		}

		Plugin.prototype = {
			init : function() {
				var self = this, sections = [];

				// collects position scrollto
				$('#main-nav li a').each(function() {
					var url = $(this).attr('href');
					if ( 'undefined' != typeof url && url.indexOf('#') != -1 && url.length > 1) {
						sections.push($(this).prop('hash'));
					}
				});

				// set caching position top
				this.updateSecPosition(sections);
				$(window).resize(function(){
					self.updateSecPosition(sections);
				});

				// click event
				$('body').on('click', 'a[href*=#]', function(e) {
					var elementClick = $(this).prop('hash');
					
					if ( 'undefined' != typeof elementClick && elementClick.indexOf('#') != -1 && elementClick.length > 1 ) {
						var destination = $(elementClick).data('offsetY'), newHash = '#!/' + elementClick.replace('#', '');
						if( $(elementClick).length === 0 ) { return; }

						if( 'undefined' == typeof destination ) {
							destination = $(elementClick).offset().top;
						}

						// update state
						self.onClicking = true;

						var $portfolioExpanded = $('.portfolio-expanded');
						if( $portfolioExpanded.length > 0 && $(elementClick).offset().top > $portfolioExpanded.offset().top ) {
							var marginTop = Number( $portfolioExpanded.css('margin-top').replace(/[^-\d\.]/g, '') );
							var marginBottom = Number( $portfolioExpanded.css('margin-bottom').replace(/[^-\d\.]/g, '') );
							destination += $portfolioExpanded.outerHeight() + marginTop + marginBottom;
						}

						if ( typeof skrollr !== 'undefined') {
							var s = skrollr.get(),
									offset = is_touch_device() ? s.relativeToAbsolute(document.getElementById(elementClick.replace('#', '')), 'top', 'top') : destination;
							s.animateTo(offset, {duration: self.options.speed, done: function(){
								self.onClicking = false;
							}});
						} else {
							$("html,body").animate({
								scrollTop : destination
							}, self.options.speed, function(){
								self.onClicking = false;
							});
						}

						// add active class																		
						if ($(this).attr('href') == "#header") {
							$("#main-nav li").removeClass('current_page_item');
						}
						else{
							$(this).parent('li').addClass('current_page_item').siblings().removeClass('current_page_item');
						}

						if(elementClick.replace('#','') !== 'header'){
							location.hash = newHash;
						} else {
							self.clearHash();
						}

						// close mobile menu
						if($(window).width() <= 760 && $('#main-nav').is(':visible')){
							$('#menu-icon').trigger('click');
						}

						e.preventDefault();
					}

				});

				// change hash url
				this.changeHash();
			},

			changeHash : function() {
				var sections = [], self = this;

				// collects scrollto hash
				$('#main-nav li a').each(function() {
					var url = $(this).prop('hash');
					if ( 'undefined' != typeof url && url.indexOf('#') != -1 && url.length > 1) {
						sections.push(url);
					}
				});

				if (sections.length > 0) {
					$.each(sections, function(index, value){
						var section = value, obj = $(value);
						if (obj.length > 0) {
							var offsetY = (is_touch_device() && typeof skrollr !== 'undefined') ? s.relativeToAbsolute(document.getElementById(section.replace('#', '')), 'top', 'top') : obj.data('offsetY'),
								elemHeight = obj.height();
							$(window).on('scroll', function(){
								var scrollAmount = $(window).scrollTop();
									// If hit top of element
									if(scrollAmount > offsetY && (offsetY + elemHeight) > scrollAmount) {
										if(self.onClicking) { return; }
										if(section.replace('#','') !== 'header') {
											location.hash = '#!/' + section.replace('#', '');
										}
										$('a[href*='+section+']').parent('li').addClass('current_page_item').siblings().removeClass('current_page_item');	
										
										// remove hash if header
										if(section.replace('#','') == 'header') {
											self.clearHash();
										}
									}
							});
						}
					});
				}
			},

			clearHash: function() {
				// remove hash
				if ( window.history && window.history.replaceState ) { 
					window.history.replaceState('', '', window.location.pathname); 
				} else { 
					window.location.href = window.location.href.replace(/#.*$/, '#'); 
				}
			},

			updateSecPosition: function(sections) {
				if(sections.length > 0) {
					$.each(sections, function(index, value){
						// cache the position
						$(value).each(function() {
							$(this).data('offsetY', parseInt($(this).offset().top));
						});
					});
				}
			}
		};

		$.fn[pluginName] = function(options) {
			return this.each(function() {
				if (!$.data(this, "plugin_" + pluginName)) {
					$.data(this, "plugin_" + pluginName, new Plugin(this, options));
				}
			});
		};

	})(jQuery, window, document);

	// Test if touch event exists //////////////////////////////
	function is_touch_device() {
		try {
			document.createEvent('TouchEvent');
			return true;
		} catch(e) {
			return false;
		}
	}

	function is_ie() {
		if(navigator.appName == 'Microsoft Internet Explorer'){
			return true;
		} else {
			return false;
		}
	}

	// Start infinite scroll and isotope
	function infiniteIsotope(containerSelector, itemSelectorIso, itemSelectorInf, containerInfinite, doIso, isoColW) {

		// Get max pages for regular category pages and home
		var scrollMaxPages = parseInt(themifyScript.maxPages), $container = $(containerSelector), $containerInfinite = $(containerInfinite);
		// Get max pages for Query Category pages
		if ( typeof qp_max_pages != 'undefined')
			scrollMaxPages = qp_max_pages;

		if ((!$('body.list-post').length > 0) && doIso) {
			// isotope init
			$container.isotope({
				masonry : {
					columnWidth : isoColW
				},
				itemSelector : itemSelectorIso,
				transformsEnabled : false,
				animationEngine : 'jquery'
			});
			$(window).resize();
		}

		// infinite scroll
		$containerInfinite.infinitescroll({
			navSelector : '#load-more a:last', // selector for the paged navigation
			nextSelector : '#load-more a:last', // selector for the NEXT link (to page 2)
			itemSelector : itemSelectorInf, // selector for all items you'll retrieve
			loadingText : '',
			donetext : '',
			loading : {
				img : themifyScript.loadingImg
			},
			maxPage : scrollMaxPages,
			behavior : 'auto' != themifyScript.autoInfinite ? 'twitter' : '',
			pathParse : function(path, nextPage) {
				return path.match(/^(.*?)\b2\b(?!.*\b2\b)(.*?$)/).slice(1);
			}
		}, function(newElements) {
			// call Isotope for new elements
			var $newElems = $(newElements).hide();

			// Mark new items: remove newItems from already loaded items and add it to loaded items
			$('.post.newItems').removeClass('newItems');
			$newElems.addClass('newItems');

			$newElems.imagesLoaded(function() {

				$newElems.fadeIn();

				$('#infscr-loading').fadeOut('normal');
				if (1 == scrollMaxPages) {
					$('#load-more, #infscr-loading').remove();
				}

				// Apply lightbox/fullscreen gallery to new items
				if(typeof ThemifyGallery !== 'undefined'){ ThemifyGallery.init({'context': jQuery(themifyScript.lightboxContext)}); }

				if ( typeof jQuery.fn.carouFredSel !== 'undefined') {
					// Create carousel on portfolio new items
					createCarousel($('.type-portfolio.newItems .slideshow'));
				}

				if ((!$('body.list-post').length > 0) && doIso) {
					$container.isotope('appended', $newElems);
				}

				// Apply Parallax
				if( typeof ThemifyParallax !== 'undefined') {
					ThemifyParallax.parallaxElements();
				}
			});

			scrollMaxPages = scrollMaxPages - 1;
			if (1 < scrollMaxPages && 'auto' != themifyScript.autoInfinite)
				$('#load-more a').show();
		});

		// disable auto infinite scroll based on user selection
		if ('auto' == themifyScript.autoInfinite) {
			$('#load-more, #load-more a').hide();
		}

	}

	// Initialize carousels
	function createCarousel(obj) {
		obj.each(function() {
			var $this = $(this);
			$this.carouFredSel({
				responsive : true,
				prev : '#' + $this.data('id') + ' .carousel-prev',
				next : '#' + $this.data('id') + ' .carousel-next',
				pagination : {
					container : '#' + $this.data('id') + ' .carousel-pager'
				},
				circular : true,
				infinite : true,
				scroll : {
					items : 1,
					wipe : true,
					fx : $this.data('effect'),
					duration : parseInt($this.data('speed'))
				},
				auto : {
					play : !!('off' != $this.data('autoplay')),
					pauseDuration : 'off' != $this.data('autoplay') ? parseInt($this.data('autoplay')) : 0
				},
				items : {
					visible : {
						min : 1,
						max : 1
					},
					width : 222
				},
				onCreate : function() {
					$this.closest('.slideshow-wrap').css({
						'visibility' : 'visible',
						'height' : 'auto'
					});
					$('.carousel-next, .carousel-prev', $this.closest('.slideshow-wrap')).hide();
					$(window).resize();
				}
			});
		});
	}

	// Fixed Header /////////////////////////
	FixedHeader = {
		init : function() {
			this.headerHeight = $('#header hgroup').outerHeight() - $('#nav-bar').outerHeight();
			$(window).on('scroll', this.activate);
			$(window).on('touchstart.touchScroll', this.activate);
			$(window).on('touchmove.touchScroll', this.activate);
		},
		activate : function() {
			var scrollAmount = $(window).scrollTop();

			if (scrollAmount <= FixedHeader.headerHeight) {
				FixedHeader.scrollDisabled();
			} else {
				FixedHeader.scrollEnabled();
			}
		},
		scrollDisabled : function() {
			$('#nav-bar').removeClass('fixed-nav-bar');
		},
		scrollEnabled : function() {
			$('#nav-bar').addClass('fixed-nav-bar');
		}
	};

	// Parallax /////////////////////////
	ThemifyParallax = {
		init: function() {
			this.windowHeight = $(window).height();
			$(window).resize(function () {
				this.windowHeight = $(window).height();
			});

			if(themifyScript.scrollingEffect == 'enabled') {
				if(themifyScript.scrollingEffectType == 'effect1'){
					this.setup_effect_1();
				} else {
					this.setup_effect_2();
				}
				this.headerParallax();
			} else {
				this.setBackground();
			}
			this.parallaxElements();
		},

		setup_effect_1: function() {
			var self = ThemifyParallax;

			self.setBackground(); // set background fullcover

			// only work in query section
			if ( ! $('body').hasClass('query-section') || is_ie() ){ return; }
			
			$('.section-post, #headerwrap').each(function(){
				var ids = $(this).prop('id'),
					$elemns = $('#' + ids);
				
				$(window).on('scroll', function(){
					self.run($elemns);
				});
				$(window).on('touchstart.touchScroll', function(){
					self.run($elemns);
				});
				$(window).on('touchmove.touchScroll', function(){
					self.run($elemns);
				});
			});
		},

		setup_effect_2: function(){
			$('.section-post, #headerwrap').each(function(){
				// Store some variables based on where we are
				var $self = $(this),
					bgImage = typeof $self.data('bg') !== 'undefined' ? 'url("'+ $self.data('bg') +'")' : '';

				$self.addClass('scrolling-bg-image').css({
					backgroundImage: bgImage
				});

				if(typeof $.fn.parallax !== 'undefined' && ! is_touch_device()){
					$self.parallax("50%", 0.1);
				}
			});
		},

		run: function(elemns) {
			var $window = $(window),
				pos = $window.scrollTop(),
				$element = elemns,
				top = $element.offset().top,
				height = $element.outerHeight(true);	

			// Check if totally above or totally below viewport
			if (top + height < pos || top > pos + ThemifyParallax.windowHeight) {
				return;
			}
			var cssTopPos = Math.max(Math.round((pos - top) * 0.2), 0) + 'px';

			$element.css('top', cssTopPos);

			if($('#nav-bar').hasClass('fixed-nav-bar')) {
				$('#nav-bar').css({'bottom': ''});
			}
		},

		headerParallax: function() {
			// disable on ie
			if ( is_ie() ) { return; }

			$('#header hgroup').each(function(){
				var self = ThemifyParallax,
						$this = $(this),
						offset = ($('body').hasClass('admin-bar')) ? 30 : 60,
						elemnTop = parseInt( $('#site-logo').offset().top - offset );

				$(window).on('scroll', function(){
					self.plHeaderRun($this, elemnTop, offset);
				});
				$(window).on('touchstart.touchScroll', function(){
					self.plHeaderRun($this, elemnTop, offset);
				});
				$(window).on('touchmove.touchScroll', function(){
					self.plHeaderRun($this, elemnTop, offset);
				});
			});
		},

		plHeaderRun: function(obj, elemnTop, offset) {
			var $this = obj,
				scrollAmount = $(window).scrollTop(),
				ratio = scrollAmount - elemnTop,
				animateTop = -(ratio) + 'px';
			
			if(scrollAmount > (Math.max(elemnTop - 50,0) )){
				$this.css({
					'-webkit-transform' : 'translateY('+animateTop+')',
					'-moz-transform' : 'translateY('+animateTop+')',
					'-o-transform' : 'translateY('+animateTop+')',
					'-ms-transform' : 'translateY('+animateTop+')',
					'transform' : 'translateY('+animateTop+')'
				});
			} else {
				$this.removeAttr('style');
			}
		},

		parallaxElements: function(){
			if ( typeof skrollr !== 'undefined') {
				// shortcode columns add class
				$('.col2-1.first, .col3-1.first, .col4-1.first, .col4-2.first').each(function(){
					var $this = $(this);
					if($this.hasClass('col2-1')) {
						$this.next('.col2-1').addClass('last');
					} else if($this.hasClass('col3-1')) {
						$this.next('.col3-1').addClass('second').next('.col3-1').addClass('last');
					} else if($this.hasClass('col4-1')) {
						$this.next('.col4-1').addClass('second').next('.col4-1').addClass('third').next('.col4-1').addClass('last');
					} else if($this.hasClass('col4-2')) {
						$this.next('.col4-1').addClass('third').next('.col4-1').addClass('last');
					}
				});
				$.each(themifyScript.transitionSetup, function(i, val){
					$(val.selector).attr(val.value);
				});
				s.refresh();
			}
		},

		setBackground: function(){
			// Fullscreen bg
			if ( typeof $.fn.backstretch !== 'undefined') {
				$('.section-post, #headerwrap').each(function() {
					var bg = $(this).data('bg');
					if ( typeof bg !== 'undefined') {
						if ($(this).hasClass('fullcover')) {
							$(this).backstretch(bg);
						} else {
							$(this).css('background-image', 'url(' + bg + ')');
						}
					}
				});

				$(window).on('backstretch.show', function(e, instance) {
					instance.$container.css('z-index', '');
				});
			}
		}
	};

	// portfolio ajax post break point
	function portfolio_item_break_point() {
		// section portfolio break point
		$('.section-post .shortcode.portfolio').each(function(){
			var selector = '', viewport = $(window).width();

			// reset
			$(this).find('.sec-post-break').remove();

			if($(this).hasClass('grid2-thumb') || $(this).hasClass('grid2')){
				selector = '.post:nth-child(2n+2)';
			} else if($(this).hasClass('grid4')) {
				selector = '.post:nth-child(4n+4)';
			} else if($(this).hasClass('grid3')){
				selector = '.post:nth-child(3n+3)';
			} else if($(this).hasClass('grid2')) {
				selector = '.post:nth-child(2n+2)';
			} else {
				selector = '.post:nth-child(1n+1)';
			}

			// mobile
			if(viewport < 480) {
				selector = '.post:nth-child(1n+1)';
			}

			$(this).find(selector).each(function(){
				$('<div class="sec-post-break" />').insertAfter($(this));
			});
		});
	}

	/////////////////////////////////////////////
	// Execute when DOM is ready
	/////////////////////////////////////////////
	$(document).ready(function() {

		var $body = $('body'), $placeholder = $('[placeholder]');

		/////////////////////////////////////////////
		// HTML5 placeholder fallback
		/////////////////////////////////////////////
		var isInputSupported = 'placeholder' in document.createElement('input'),
			isTextareaSupported = 'placeholder' in document.createElement('textarea');
		if( ! isInputSupported && ! isTextareaSupported ) {
			$placeholder.focus(function() {
				var input = $(this);
				if (input.val() == input.attr('placeholder')) {
					input.val('');
					input.removeClass('placeholder');
				}
			}).blur(function() {
				var input = $(this);
				if (input.val() === '' || input.val() == input.attr('placeholder')) {
					input.addClass('placeholder');
					input.val(input.attr('placeholder'));
				}
			}).blur();
			$placeholder.parents('form').submit(function() {
				$(this).find('[placeholder]').each(function() {
					var input = $(this);
					if (input.val() == input.attr('placeholder')) {
						input.val('');
					}
				});
			});
		}

		// Toggle main nav on mobile
		$body.on('click', '#menu-icon', function(event){
			event.preventDefault();
			$("#main-nav").fadeToggle();
			$("#headerwrap #top-nav").hide();
			$(this).toggleClass("active");
		});

		if ( typeof skrollr !== 'undefined') {
			// scroll top
			$('.back-top a').on('click', function(e){
				e.preventDefault();
				s.animateTo(0, {duration: 800});
			});
		} else {
			/////////////////////////////////////////////
			// Scroll to top
			/////////////////////////////////////////////
			$('.back-top a').click(function() {
				$('body,html').animate({
					scrollTop : 0
				}, 800);
				return false;
			});
		}

		// If is touch device, add class
		if (is_touch_device()) {
			$body.addClass('is-touch');
			var vh = $(window).height();
			if(navigator.userAgent.match(/iPhone/i)) {
				vh = vh + 60;
			}
			$('.query-section #headerwrap').css('height', vh);
		}
		
		// Show/Hide direction arrows
		$('#body').on('mouseover mouseout', '.slideshow-wrap', function(event) {
			if (event.type == 'mouseover') {
				if( $(window).width() > 600 ){
					$('.carousel-next, .carousel-prev', $(this)).css('display', 'block');
				}
			} else {
				if( $(window).width() > 600 ){
					$('.carousel-next, .carousel-prev', $(this)).css('display', 'none');
				}
			}
		});

		// Fixed header
		if(!is_touch_device() && typeof skrollr !== 'undefined'){
			FixedHeader.init();
		} else if(typeof skrollr === 'undefined') {
			FixedHeader.init();
		}

		// Height of top bar
		var menuHeight = $('#nav-bar').outerHeight();

		// portfolio breakpoint
		portfolio_item_break_point();

		// Portfolio expand js
		var portoInitialPos;
		$('.section-post .porto-expand-js').on('click', function(e){
			e.preventDefault();
			var $this = $(this),
				prefixLink = $this.data('prefix-link'),
				url = UpdateQueryString( 'porto_expand', 1, $this.attr('href') ),
				placehold = $this.closest('.post').nextAll('.sec-post-break').first();

			if ( placehold.length === 0 ) {
				placehold = $this.closest('.shortcode');
			}

			// cache the initial position
			portoInitialPos = $(window).scrollTop();
			
			$.ajax({
				type: "POST",
				url: url,
				dataType: 'html',
				beforeSend: function(xhr){
					$('.portfolio-expanded').remove();
					$('<div class="portfolio-expanded" />')
					.appendTo(placehold).hide();
					$('.portfolio-loader').remove();
					$('<div class="portfolio-loader">').appendTo($this.closest('.portfolio-post').find('.post-image')).show();
				},
				success: function( data ){
					var $newElems = $(data);
					$('.portfolio-expanded').hide().append($newElems).slideDown('slow').promise().done(function(){
						// Carousel initialization //////////////////////////
						if(typeof $.fn.carouFredSel !== 'undefined') {
							var porto_id = '#'+$('.portfolio-expanded > .type-portfolio').attr('id');
							createCarousel($( '.portfolio-expanded > ' + porto_id + ' .slideshow'));
						}
						$('html, body').animate({
							scrollTop : $('.portfolio-expanded').offset().top - menuHeight
						}, 1000);
					});
					
					$('.portfolio-loader').remove();
				}
			});
		});

		// portfolio close
		$body.on('click', '.close-expanded', function(e){
			e.preventDefault();
			$(this).parent().slideUp(800, function(){
				$(this).remove();
			});
			$('html, body').animate({
				scrollTop : portoInitialPos
			}, 1000);
		});

		$body.on('touchstart touchmove touchend', '#main-nav', function(e) {
			e.stopPropagation();
		});

	});

	/////////////////////////////////////////////
	// Execute when page is fully loaded
	/////////////////////////////////////////////
	$(window).load(function() {
		// Lightbox / Fullscreen initialization ///////////
		if(typeof ThemifyGallery !== 'undefined'){ ThemifyGallery.init({'context': jQuery(themifyScript.lightboxContext)}); }
		
		// scrolling nav
		$('body').themifyScroll({
			speed : 700
		});

		// Carousel initialization //////////////////////////
		if(typeof $.fn.carouFredSel !== 'undefined') {
			createCarousel($('.portfolio-post .slideshow'));
		}

		if( typeof skrollr !== 'undefined' ) {
			s.refresh();
		}

		// parallax section background
		ThemifyParallax.init();

		var target = window.location.hash.replace(/^#\!\//, "").replace('#','');
		if (target !== '' && $('#' + target).length > 0) {
			var anchor = $('#' + target), destination = anchor.offset().top;
			$("html,body").animate({
				scrollTop : destination
			}, 1000);
		}

		// Check if isotope is enabled ////////////////
		if ( typeof ($.fn.infinitescroll) !== 'undefined') {

			if ($('.post').length > 0) {
				// isotope container, isotope item, item fetched by infinite scroll, infinite scroll
				infiniteIsotope('#loops-wrapper', '.post', '#content .post', '#loops-wrapper', false, '');
			}

			if ($('.portfolio-post').length > 0) {
				// isotope container, isotope item, item fetched by infinite scroll, infinite scroll
				infiniteIsotope('.portfolio-wrapper', '.portfolio-post', '.portfolio-post', '.portfolio-wrapper', false, '');
			}

		}
	});

	if ( typeof skrollr !== 'undefined') {
		// initialize parallax
		if(is_touch_device()){
			$('#pagewrap').wrap('<div id="skrollr-body" />'); // iscroll support
			$('body').addClass('touch-device-body');
		}
		var $navClone = $('#nav-bar').clone(),
			s = skrollr.init({
			smoothScrolling : true,
			easing: {
				wtf: Math.random,
				inverted: function(p) {
					return 1 - p;
				}
			},
			forceHeight : false,
			render: function(data) {
				if(is_touch_device()){
					var $navBar = $('#nav-bar'),
						headerHeight = $('#header hgroup').outerHeight() - $navBar.outerHeight();
					if(data.curTop >= headerHeight) {
						if(!$navBar.parent().hasClass('touch-device-body')) {
							$navBar.remove();
							$navClone.prependTo($('body')).addClass('fixed-nav-bar');
						}
					} else {
						if($navBar.parent().hasClass('touch-device-body')) {
							$navBar.remove();
							$navClone.appendTo($('#header'));
							$navBar.removeClass('fixed-nav-bar');
						}
					}
				}
			}
		});
	}

}(jQuery));