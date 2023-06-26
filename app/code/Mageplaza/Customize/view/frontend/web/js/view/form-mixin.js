define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Mageplaza_ShippingCost/js/model/address',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, _, Component, customerData, address, modal, $t) {
    'use strict';
    return function (ShippingCostComponent) {
        return ShippingCostComponent.extend({
            defaults: {
                template: 'Mageplaza_Customize/form-mixin',
                rateListTmpl: 'Mageplaza_Customize/rate-list-mixin'
            },
            calcAction: function (clickAction) {
                var self     = this,
                    form     = $('#product_addtocart_form'),
                    formData = new FormData(form[0]);

                if (clickAction) {
                    if (!form.valid()) {
                        return;
                    }
                } else if (!form.validate().checkForm()) {
                    return;
                }

                formData.append('address', this.getAddress());
                if (this.includeCart()) {
                    formData.append('include_cart', 1);
                }

                this.isLoading(true);
                $.ajax({
                    method: 'POST',
                    url: this.calcUrl,
                    showLoader: false,
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response && response.error) {
                            $('body, html').animate({scrollTop: 0}, 'slow');

                            return;
                        }

                        self.rates(response);
                    }
                }).always(function () {
                    self.isMore(true);
                    self.isLoading(false);
                });
            }
        })
    }
});
