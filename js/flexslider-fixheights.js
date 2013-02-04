/**
 * Hooks in to Flex Slider start callback and adds vertical padding to the slides
 * so that they are all vertically centered;
 */
(function($){
	var wait = 0;

	var fixHeight = function ( slider ) {
		var maxHeight = 0,
			heights = [],
			slides = $(slider.vars.selector, slider); // Get all slides including clones

		// Find the max height needed
		for ( var i = 0; i < slides.length; i++ ) {
			heights[i] = $(slides[i]).height();

			// If the height is 0, the image hasn't loaded yet
			// come back in 500ms and try again. Give up after 4000ms.
			if ( wait < 4000 && 0 === heights[i] ) {
				setTimeout( function() { wait += 500; fixHeight( slider ); }, 500 );
				return;
			}

			maxHeight = Math.max(maxHeight, heights[i]);
		}

		// Add vertical padding
		for ( i = 0; i < slides.length; i++ ) {
			$(slides[i]).css('padding', (maxHeight - heights[i]) / 2 + 'px 0');
			maxHeight = Math.max(maxHeight, heights[i]);
		}

		// Set the height on the viewport
		slider.find('.slides').height( maxHeight );

		// Bind to resize. Uses debounce plugin
		$(window).resize($.debounce(350, function() { fixHeight(slider); } ));
	};

	// Add the callback ready for init
	bissoFlexsliderSettings.flexsliderSettings.start = fixHeight;
})(jQuery);