define([
    'Magento_Ui/js/lib/view/utils/async'
], function ($) {
    'use strict';

    // initialize Swiper for easy catalog images on homepage
    $.async(
        {
            selector: '.block-categories .easycatalogimg'
        },
        function (categoryList) {
            require([
                'Swissup_Swiper/js/swiper-wrapper'
            ], function (SwiperWrapper) {
                new SwiperWrapper({
                    target: categoryList,
                    slidesPerView: 3,
                    slidesPerGroup: 3,
                    spaceBetween: 10,
                    loop: false,
                    breakpoints: {
                        767: {
                            slidesPerView: 2,
                            slidesPerGroup: 2
                        },
                        640: {
                            slidesPerView: 1,
                            slidesPerGroup: 1
                        }
                    }
                }, categoryList);
            });
        }
    );

    // initialize Swiper carousel for related products
    $(document).on('relatedproductscreate', function (event) {
        require([
            'Swissup_Swiper/js/swiper-wrapper'
        ], function (SwiperWrapper) {
            var container = $('.products-grid', event.target).get(0);

            new SwiperWrapper({
                target: container,
                slidesPerView: 5,
                spaceBetween: 10,
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                breakpoints: {
                    1024: {
                        slidesPerView: 3
                    },
                    768: {
                        slidesPerView: 2
                    },
                    480: {
                        slidesPerView: 1
                    }
                }
            }, container);
        });
    })
    
    $('.wizzy-search-form-wrapper').click(function(){
        $(this).toggleClass('abc');
    });
    
    $('.wizzy-search-form,.wizzy-search-form-wrapper').click(function(e){
        e.stopPropagation();
    });
    
    $(document).click(function(){
        $('.wizzy-search-form-wrapper').removeClass('abc');
    });
    setTimeout(function () {
    var ImageCount = $('img.fotorama__img').length;
    if(ImageCount == 1){
        $('.catalog-product-view .product.media .fotorama__wrap .fotorama__stage').addClass('test');
    } 
    },7000);
});
