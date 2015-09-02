/**
 * Tabify
 */
;(function ($) {
	$.fn.tabify = function () {
		return this.each(function () {
			var tabs = $(this);
			$('.tab-content', tabs).hide();
			$('ul.tab-nav li:first', tabs).addClass('current');
			$('div:first', tabs).show();
			var tabLinks = $('ul.tab-nav li', tabs);
			$(tabLinks).click(function () {
				$(this).addClass('current').siblings().removeClass('current');
				$('.tab-content', tabs).hide();
				var activeTab = $(this).find('a').attr('href');
				$(activeTab).show();
				return false;
			});
		});
	};
})(jQuery);

(function ($) {
	$.fn.themify_accordion = function() {
		return this.each(function() {
			var acc = $(this),
					behavior = $(this).data('behavior');
			$('.default-closed', acc).hide();
			var head = $('.accordion-title', acc);
			$(head).on('click', function(e) {
				var def = $(this).closest('li').toggleClass('current')
				.siblings().removeClass('current');

				if( behavior == 'accordion' ) {
					def.find('.accordion-content').each(function(){
						$(this).slideUp();
					});
				}

				$(this).next().slideToggle();
				e.preventDefault();
			});
		});
	};
})(jQuery);