define([
    'ko',
    'uiComponent',
    'jquery',    
    'mage/url',
    'Magento_Checkout/js/action/get-totals',    
    'Mageants_GiftCard/js/action/set-gift-code',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils'
    ],
    function(ko,Component,$,urlBuilder,getTotalsAction,setGiftCodeAction,quote,priceUtils)
    {
        'use strict';
    var show = window.checkoutConfig.giftcertificatestatus;
    var cat_ids = window.checkoutConfig.giftcategoryids;
    var giftcertificatecode = window.checkoutConfig.giftcertificatecode;
    var readonlyValue = "";
    var buttonText = "";
    if (giftcertificatecode == null) {      
        giftcertificatecode = "";
    }
    if (giftcertificatecode != "" ) {
        readonlyValue = true; 
        buttonText = "Cancel";
    }
    else{
        readonlyValue = false;        
        buttonText = "Apply";
    }
    return Component.extend({defaults:{
        template:'Mageants_GiftCard/checkout/payment/giftCode'
    },
        shouldShowMessage: ko.observable(show),
        giftCode: ko.observable(giftcertificatecode),
        readonlyValue: ko.observable(readonlyValue),
        buttonText: ko.observable(buttonText),
        /**/
        applycode : function() {

                var actionUrl = "";
                console.log($("#applycode").text());
                if ($("#applycode").text() == "Apply") {
                    actionUrl = urlBuilder.build('giftcertificate/cart/apply');
                }
                else{
                    actionUrl = urlBuilder.build('giftcertificate/cart/cancel');
                }
                jQuery("#gift-certificate-form").prop( "disabled", true );
                jQuery(".loading").toggle();
                jQuery(".gift-code-block").css('opacity',0.2);
                if(jQuery("#gift-certificate-code").val()==''){
                    jQuery(".error-msg").show(500);
                    jQuery(".loading").toggle();
                    jQuery(".gift-code-block").css('opacity',1);
                    jQuery("#gift-certificate-form").prop( "disabled", false );
                    return false;
                }  
                
                
                    jQuery.ajax({
                    type:'POST',
                        data:{gift_code:jQuery("#gift-certificate-code").val(),categoryids:cat_ids},
                        url:actionUrl,
                        success:function(data){
                         var code = data;
                           if (code[0]=='3')
                           {
                                jQuery("#message").html(code[1]);
                                var deferred = $.Deferred();
                                 
                                if ($("#applycode").text() == "Apply") {   
                                    setGiftCodeAction(jQuery("#gift-certificate-code").val());
                                   // getTotalsAction([], deferred);                                 
                                    $("#gift-certificate-code").attr('readonly', true);
                                    $("#applycode").text("Cancel");
                                }
                                else{
                                    $("#applycode").text("Apply");
                                    getTotalsAction([], deferred);
                                    $("#gift-certificate-code").attr('readonly', false);
                                    $("#gift-certificate-code").attr('value', "");
                                }

                                if(jQuery('#gift-code-arrow').hasClass("fa-angle-down")){
                                    jQuery("#gift-code-arrow").removeClass("fa-angle-down").addClass("fa-angle-up");
                                }
                                else{
                                    jQuery("#gift-code-arrow").removeClass("fa-angle-up").addClass("fa-angle-down");
                                }
                                jQuery(".gift-code-block").toggle();
                           }
                           else if(code[0]=='5')
                            {
                            jQuery("#message").html(code[1]);  
                            jQuery(".loading").hide();
                            jQuery(".gift-code-block").css('opacity',1);
                            jQuery("#gift-certificate-form").prop( "disabled", false );
                            return false;

                            }
                           else
                           {
                            jQuery("#message").html(code[1]);
                           }
                            jQuery(".loading").hide();
                            jQuery(".gift-code-block").css('opacity',1);
                            jQuery("#gift-certificate-form").prop( "disabled", false );
                
                        }   
                    });     
            
            return false;
        },
        checkstatus : function() {
            var linkUrl = urlBuilder.build('giftcertificate/cart/check');
            jQuery("#gift-certificate-form").prop( "disabled", true );
            jQuery(".loading").toggle();
            jQuery(".gift-code-block").css('opacity',0.2);

            if(jQuery("#gift-certificate-code").val()==''){
                jQuery(".error-msg").show(500);
                jQuery(".loading").toggle();
                jQuery(".gift-code-block").css('opacity',1);
                jQuery("#gift-certificate-form").prop( "disabled", false );

                return false;
            }   
                jQuery.ajax({
                type:'POST',
                    data:{gift_code:jQuery("#gift-certificate-code").val()},
                    url:linkUrl,
                    success:function(data){
                        jQuery("#message").html(data);
                        jQuery(".loading").toggle();
                        jQuery(".gift-code-block").css('opacity',1);
                        jQuery("#gift-certificate-form").prop( "disabled", false );
                    }   
            });     
        return false;
        },
        giftcodetoggle : function() {
            if(jQuery('#gift-code-arrow').hasClass("fa-angle-down")){
                jQuery("#gift-code-arrow").removeClass("fa-angle-down").addClass("fa-angle-up");
            }
            else{
                jQuery("#gift-code-arrow").removeClass("fa-angle-up").addClass("fa-angle-down");
            }
            jQuery(".gift-code-block").toggle();
            return false;
        }
    });
});