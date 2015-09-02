;var ThemifyBuilderModuleJs = {
	init: function() {
		this.bindEvents();
	},

	bindEvents: function() {
		var self = ThemifyBuilderModuleJs;

		jQuery(document).ready(function(){
			self.accordion();
			self.tabs();
		});

		jQuery(window).load(function(){
			self.carousel();
		});
	},

	loadOnAjax: function() {
		ThemifyBuilderModuleJs.accordion();
		ThemifyBuilderModuleJs.tabs();
		ThemifyBuilderModuleJs.carousel();
	},

	accordion: function() {
		jQuery('.module.module-accordion').themify_accordion();
	},

	tabs: function() {
		jQuery(".module.module-tab").each(function(){
			$height = jQuery(".tab-nav", this).outerHeight();
			if($height > 200) {
				jQuery(".tab-content", this).css('min-height', $height);
			}
		});
		jQuery(".module.module-tab").tabify();
	},

	carousel: function() {
		jQuery('.themify_builder_slider').each(function(){
			var $this = jQuery(this),
				$args = {
				responsive: true,
				circular: true,
				infinite: true,
				height: 'auto',
				scroll: {
					items: $this.data('scroll'),
					wipe: true,
					pauseOnHover: 'resume',
					duration: parseInt($this.data('speed') * 1000),
					fx: $this.data('effect')
				},
				items: {
					visible: { min: 1, max: $this.data('visible') },
					width: 150
				},
				onCreate: function( items ) {
					var heights = [];
					jQuery('.themify_builder_slider_wrap').css({'visibility':'visible', 'height':'auto'});

					jQuery.each( items, function() {
						heights.push( jQuery(this).outerHeight() );
					});
					jQuery( '.caroufredsel_wrapper, .themify_builder_slider, .themify_builder_slider li', $this ).outerHeight( Math.max.apply( Math, heights ) );

					jQuery('.themify_builder_slider_loader').remove();
				}
			};

			if($this.closest('.themify_builder_slider_wrap').find('.caroufredsel_wrapper').length > 0) {
				return;
			}

			// fix the one slide problem
			if($this.children().length < 2) {
				jQuery('.themify_builder_slider_wrap').css({'visibility':'visible', 'height':'auto'});
				jQuery(window).resize();
				return;
			}

			if(parseInt($this.data('auto-scroll')) > 0) {
				$args.auto = {
					play: true,
					pauseDuration: parseInt($this.data('auto-scroll') * 1000)
				};
			}
			else{
				$args.auto = false;
			}

			if($this.data('arrow') == 'yes') {
				$args.prev = '#' + $this.data('id') + ' .carousel-prev';
				$args.next = '#' + $this.data('id') + ' .carousel-next';
			}

			if($this.data('pagination') == 'yes') {
				$args.pagination = { container: '#' + $this.data('id') + ' .carousel-pager' };
			}

			if($this.data('wrapper') == 'no') {
				$args.wrapper = {
					element: false
				}
			}

			$this.carouFredSel($args);
		});
	},

	initialize: function(address, num, zoom, type) {
		var geo = new google.maps.Geocoder(),
		latlng = new google.maps.LatLng(-34.397, 150.644),
		mapOptions = {
			'zoom': zoom,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		switch( type.toUpperCase() ) {
			case 'ROADMAP':
				mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
				break;
			case 'SATELLITE':
				mapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
				break;
			case 'HYBRID':
				mapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
				break;
			case 'TERRAIN':
				mapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
				break;
		}
		var	map = new google.maps.Map( document.getElementById( 'themify_map_canvas_' + num ), mapOptions );
		geo.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				map.setCenter(results[0].geometry.location);
				var marker = new google.maps.Marker({
					map: map, 
					position: results[0].geometry.location	});
			}
		});
	}
};
// init js
ThemifyBuilderModuleJs.init();