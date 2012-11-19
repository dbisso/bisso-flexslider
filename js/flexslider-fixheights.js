jQuery('document').ready( function($){

	var fixHeight = function ( slider ) {
		var maxHeight = 0,
			heights = [];

		// Find the max height needed
		for ( var i = 0; i < slider.slides.length; i++ ) {
			heights[i] = $(slider.slides[i]).height();
			maxHeight = Math.max(maxHeight, heights[i]);
		}

		// Add vertical padding
		for ( i = 0; i < slider.slides.length; i++ ) {
			$(slider.slides[i]).css('padding', (maxHeight - heights[i]) / 2 + 'px 0');
			maxHeight = Math.max(maxHeight, heights[i]);
		}

		// Set the height on the viewport
		slider.find('.slides').height( maxHeight );
	};

	bissoFlexsliderSettings.flexsliderSettings.start = fixHeight;

	var slider = $('.flexslider').flexslider(bissoFlexsliderSettings.flexsliderSettings).data('flexslider');

	if ( 'undefined' !== typeof slider ) $(window).bind('resize', function() { fixHeight(slider); } );
});