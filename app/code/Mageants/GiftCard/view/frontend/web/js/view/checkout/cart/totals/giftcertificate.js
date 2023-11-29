define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Mageants_GiftCard/checkout/summary/giftcertificate'
            },
            totals: quote.getTotals(),
            isDisplayed: function() {
                return this.isFullMode() && this.getPureValue() != 0;
            },
            getGiftCardCode: function() {
                if (this.totals()) {
                    return totals.getSegment('giftcertificate').title;
                }
                return null;
            },
            getPureValue: function() {
                var price = 0,
                    giftcertificate = totals.getSegment('giftcertificate');
                if (this.totals() && giftcertificate !== null && giftcertificate.value) {
                    price = giftcertificate.value;
                }
                return price;
            },
            getValue: function() {
                return this.getFormattedPrice(this.getPureValue());
            }
        });
    }
);
