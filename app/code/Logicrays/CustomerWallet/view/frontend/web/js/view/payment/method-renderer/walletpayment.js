define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'jquery',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, url, $, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Logicrays_CustomerWallet/payment/walletpayment'
            },

            context: function () {
                return this;
            }
        });
    }
);
