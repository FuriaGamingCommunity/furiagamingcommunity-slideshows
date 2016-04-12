/**
 * Furia Gaming Community - Slideshow
 * Author: Xavier Giménez Segovia
 */
var $ = jQuery.noConflict();

 $(window).load(function() {
 	if ( $('.flexslider').length > 0 ) {
 		$('.flexslider').flexslider({
 			animation: "slide"
 		});
 	}
 })(jQuery);