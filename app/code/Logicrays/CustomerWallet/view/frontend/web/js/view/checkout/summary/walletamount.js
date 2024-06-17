define(
    [
       'jquery',
       'Magento_Checkout/js/view/summary/abstract-total',
       'Magento_Checkout/js/model/quote',
       'Magento_Checkout/js/model/totals',
       'Magento_Catalog/js/price-utils'
    ],
    function ($, Component, quote, totals, priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Logicrays_CustomerWallet/checkout/summary/walletamount'
            },
            totals: quote.getTotals(),
            isDisplayedWalletamount : function () {
                return true;
            },

            getWalletPaidAmount : function () {
                var walletAppliedAmount = window.checkoutConfig.wallet_applied_amount;
                var currencyRate = window.checkoutConfig.quoteData.base_to_quote_rate;
                var walletAppliedAmount = walletAppliedAmount * currencyRate;
                return this.getFormattedPrice(walletAppliedAmount);
            },

            /**
             *
             * @returns bool
             */
            isWalletUsedShowInSummary: function () {
                var walletAppliedAmount = window.checkoutConfig.wallet_applied_amount;
                if (walletAppliedAmount === '0' || walletAppliedAmount == null) {
                    return false;
                }
                return true;
            }

         });
    }
);