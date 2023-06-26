define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'mage/url'
], function ($, wrapper, quote, urlBuilder) {
    'use strict';

    return function (selectShippingAddressAction) {

        return wrapper.wrap(selectShippingAddressAction, function (originalAction) {
            

            var result = originalAction();
            var url = urlBuilder.build('checkoutswitcher/index/currency');
            var shippingAddressData = quote.shippingAddress();
            $.ajax({
                url: url,
                type: "post",
                dataType: 'json',
                data: {country: shippingAddressData.countryId},
                success:function(result){
                    if(result.changed) {
                        location.reload();
                    }
                }
            });
            return result;
        });
    };
});