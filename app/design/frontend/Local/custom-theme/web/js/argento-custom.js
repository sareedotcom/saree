define([
    'jquery',
    'jquery/jquery.cookie',
    'domReady!'
], function($) {
    "use strict";

    $(document).ready(function () {
        var cookieStickyNoti = $.cookie("hide-sticky-notice"); // Get Cookie Value
        if(cookieStickyNoti) 
            { 
                console.log('Cookies Set');
                $(".sticky-notification-bar").hide();
            }
        else
            {
                   console.log('Cookies not Set'); 
                   $(".sticky-notification-bar").show();
            }
    });
    
   
    
    $(".block-subtitle.filter-subtitle").click(function(){ 
        console.log("hii"); 
        $('.block.filter.active').removeClass('active'); 
        $('body').removeClass('filter-active'); 
    });

    $(".sticky-close-icon").click(function(){
        $(".sticky-notification-bar").hide();
        $("header.page-header").css("margin", 0);
        
        var date = new Date();
        var minutes = 60;
        date.setTime(date.getTime() + 24 * 60 * 60 * 1000);
        $.cookie('hide-sticky-notice', '', {path: '/', expires: -1}); // Expire Cookie
        $.cookie('hide-sticky-notice', 'true', {expires: date}); // Set Cookie Expiry Time

        // set the cookie for 24 hours
        // var date = new Date();
        // date.setTime(date.getTime() + 24 * 60 * 60 * 1000); 
        // $.cookie('hide-sticky-notice', true, { expires: date });
    });

    $( ".nav-sections-items" ).append( $('<div class="close-button-mob"><i class="fa fa-close"></i></div>' ) );
    
    $(".close-button-mob").click(function(){
        $("html").removeClass("nav-open"); 
        $("html").removeClass("nav-before-open"); 
    });

    $('.footer-menu.drop-menu h4').click(function(){
        $('.footer-menu.drop-menu h4.active').removeClass('active');
        $(this).addClass('active');    
    });
    
});