require(['jquery', 'jquery/ui', 'slick'], function($) {
    $(document).ready(function() {
        $(".slider-custom-related-images").slick({
            dots: false,
            infinite: true,
            slidesToShow: 3,
            slidesToScroll: 3,
            arrows: true,
            centerMode:false,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        arrows: false,
                        dots: true
                    }
                }
            ]
        });
        $(".coupon-offer").slick({
            dots: false,
            infinite: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
        });
    });
});