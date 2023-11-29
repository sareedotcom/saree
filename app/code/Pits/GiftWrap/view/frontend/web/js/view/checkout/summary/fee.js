/**
 * PIT Solutions
 *
 * NOTICE OF LICENSE
 * This source file is licenced under Webshop Extensions software license.
 * Once you have purchased the software with PIT Solutions AG or one of its
 * authorised resellers and provided that you comply with the conditions of this contract,
 * PIT Solutions AG grants you a non-exclusive license, unlimited in time for the usage of
 * the software in the manner of and for the purposes specified in the documentation according
 * to the subsequent regulations.
 *
 * @category Pits
 * @package  Pits_GiftWrap
 * @author   Pit Solutions Pvt. Ltd.
 * @copyright Copyright (c) 2021 PIT Solutions AG. (www.pitsolutions.ch)
 * @license https://www.webshopextension.com/en/licence-agreement/
 */

define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Pits_GiftWrap/checkout/summary/fee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

            /**
             * Fee is displayed or not
             *
             * @returns {*}
             */
            isDisplayed: function () {
                return this.isFullMode();
            },

            /**
             * Get gift wrap fee
             *
             * @returns {*}
             */
            getValue: function () {
                var price = 0;
                if (this.totals() && totals.getSegment('giftwrap_fee')) {
                    price = totals.getSegment('giftwrap_fee').value;
                }
                return this.getFormattedPrice(price);
            },

            /**
             * Get gift wrap fee base value
             *
             * @returns {String}
             */
            getBaseValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_giftwrap_fee;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat(), false);
            }
        });
    }
);
