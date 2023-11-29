define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/totals',
        'Magento_Ui/js/modal/modal',
        'mage/url',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data'
    ],
    function (Component, $,
              quote,
              fullScreenLoader,
              redirectOnSuccessAction,
              messageContainer,
              totals,
              modal,
              urlBuilder,
              additionalValidators,
              selectPaymentMethodAction,
              checkoutData) {
        'use strict';

        var payglocalResponce, intervalId;
        payglocalResponce = {};
        payglocalResponce ["status"] = "no";
        return Component.extend({
            defaults: {
                template: 'Meetanshi_PayGlocal/payment/payglocal',
                transactionResult: ''
            },
            messageContainer: messageContainer,
            getPayGlocalLogoUrl: function () {
                return window.checkoutConfig.payglocal_imageurl;
            },

            getPayGlocalInstructions: function () {
                return window.checkoutConfig.payglocal_instructions;
            },
            getIframWidth: function () {
                return window.checkoutConfig.iframe_width;
            },
            initialize: function () {
                var self = this;
                self._super();
                var pMethod = quote.paymentMethod() ? quote.paymentMethod().method : null;
                if(pMethod=="payglocal"){
                    self.selectPaymentMethod();
                }
                return self;
            },
            loadPayGlocalJs: function (callback) {
                var scriptEle = document.createElement("script");
                scriptEle.setAttribute("src", window.checkoutConfig.payglocal_scriptUrl);
                scriptEle.setAttribute("type", "text/javascript");
                scriptEle.setAttribute("defer", "");
                scriptEle.setAttribute("data-display-mode", window.checkoutConfig.payglocal_mode);
                scriptEle.setAttribute("data-cd-id", window.checkoutConfig.payglocal_cdid);
                document.head.appendChild(scriptEle);
            },

            initObservable: function () {
                this.loadPayGlocalJs(function () {
                });
                this._super()
                    .observe('active');
                return this;
            },

            getCode: function () {
                return 'payglocal';
            },

            getData: function () {
                var data = {
                    'method': this.item.method,
                    'additional_data': {'payglocalResponce': JSON.stringify(payglocalResponce)}
                };
                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);
                return data;
            },
            selectPaymentMethod: function () {
                var self = this;
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                if (window.checkoutConfig.payglocal_mode == "inline") {
                    jQuery("#PayGlocal_payments iframe").remove();
                    fullScreenLoader.startLoader(true);
                    setTimeout(function () {
                        self.displayPaymentPage();
                        fullScreenLoader.stopLoader(true);
                    }, 500);
                }
                return true;
            },
            realPlaceOrder: function () {
                var self = this;
                this.isPlaceOrderActionAllowed(false);
                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                            fullScreenLoader.stopLoader(true);
                            self.displayPaymentPage();
                        }
                    ).done(
                    function () {
                        self.isPlaceOrderActionAllowed(false);
                        self.afterPlaceOrder();

                        if (self.redirectAfterPlaceOrder) {
                            redirectOnSuccessAction.execute();
                        }
                    }
                ).always(
                    function () {
                        self.isPlaceOrderActionAllowed(true);
                    }
                );
            },
            payResponce: function (data) {
                payglocalResponce = data;
                if (payglocalResponce.status == "SENT_FOR_CAPTURE") {
                    this.isPlaceOrderActionAllowed(false);
                    this.realPlaceOrder();
                }else{
                    this.isPlaceOrderActionAllowed(true);
                }
            },
            displayPaymentPage: function () {
                var self = this;
                fullScreenLoader.startLoader(true);
                $.ajax({
                    type: 'GET',
                    data: {
                        form_key: $("input[name='form_key']").val()
                    },
                    url: urlBuilder.build('payglocal/index/index'),
                    dataType: "json",
                    success: function (response) {
                        if (response.hasOwnProperty('error')) {
                            messageContainer.addErrorMessage({
                                message: response.message
                            });
                        } else {
                            window.PGPay.launchPayment({redirectUrl: response.redirectUrl}, self.payResponce.bind(self));
                        }
                        fullScreenLoader.stopLoader(true);

                    },
                    error: function (err) {
                        fullScreenLoader.stopLoader(true);
                        messageContainer.addErrorMessage({
                            message: err
                        });
                    }
                });

            },
            tPlaceOrder: function (data, event) {
                var self = this;
                if (event) {
                    event.preventDefault();
                }
                if (this.validate() &&
                    additionalValidators.validate() &&
                    this.isPlaceOrderActionAllowed() === true
                ) {
                    if (window.checkoutConfig.payglocal_mode == "inline") {
                        window.PGPay.handlePayNow(event);
                    }else{
                        self.displayPaymentPage();
                    }
                }
                return false;
            }
        });
    }
);