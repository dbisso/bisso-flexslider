/**
 * Initialise the flexsliders on DOM ready
 */

;(function($) {
	$(document).on('bissoFlexSlider:init', function(event) {
		var sliders = $('.flexslider').not('.js-init'),
			slides = sliders.find('.slides > *');

		slides.each( function(i, element) {
			$(element).html( $(element).html().replace(/<!--/, '').replace(/-->/, '') );
		} );

		sliders.flexslider(bissoFlexsliderSettings.flexsliderSettings).addClass('js-init');
	});
}(jQuery));

jQuery('document').ready( function($){
	$(document).trigger('bissoFlexSlider:init');
});