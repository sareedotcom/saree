define([
    "jquery",
    'slick'
], function($) {
    "use strict";
    return function slider(config, element) {
        var viewportWidth = $(window).width();
        console.log(viewportWidth);
        if (viewportWidth < 991) {
            $('.productListingSlider').not('.slick-initialized').slick({
                infinite: true,
                dots: true,
                arrows: false,
                slidesToShow: 1,
                slidesToScroll: 1
            });
        } 
    }
});