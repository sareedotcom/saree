/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'uiRegistry',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/model/messages',
    'uiLayout',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'mage/validation'
], function (
    ko,
    $,
    Component,
    placeOrderAction,
    selectPaymentMethodAction,
    quote,
    customer,
    paymentService,
    checkoutData,
    checkoutDataResolver,
    registry,
    additionalValidators,
    Messages,
    layout,
    redirectOnSuccessAction,
    modal,
    url,
    validate
) {
    'use strict';

    /**
     * Async promise call to validate popup configurations  
     */
    async function validateOrderOtpPopupConfigurations(validation_url, payMethod) {
        async function promised_validation_fetch(validation_url, payMethod) {
           return new Promise((resolve, reject) => {
              $.ajax({
                 url:  validation_url,
                 data: payMethod,
                 type: 'POST',
                 success: (response) => {
                    resolve(response);
                 },
                 error: (err) => {
                    reject(err);
                 }
              });
           });
        }
        let validationResponse = await promised_validation_fetch(validation_url, payMethod);
        return validationResponse;
    }

    /**
     * Async promise call to verify OTP
     */
    async function verifyOrderOtp(verification_url, otp) {
        async function promised_verify_fetch(verification_url, otp) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url:  verification_url,
                    data: otp,
                    type: 'POST',
                    success: (response) => {
                        resolve(response);
                    },
                    error: (err) => {
                        reject(err);
                    }
                });
            });
        }
        var verifyResponse = await promised_verify_fetch(verification_url, otp);
        return verifyResponse;
    }
     
    return Component.extend({
        redirectAfterPlaceOrder: true,
        isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

        /**
         * After place order callback
         */
        afterPlaceOrder: function () {
            // Override this function and put after place order logic here
        },

        /**
         * Initialize view.
         *
         * @return {exports}
         */
        initialize: function () {
            var billingAddressCode,
                billingAddressData,
                defaultAddressData;

            this._super().initChildren();
            quote.billingAddress.subscribe(function (address) {
                this.isPlaceOrderActionAllowed(address !== null);
            }, this);
            checkoutDataResolver.resolveBillingAddress();

            billingAddressCode = 'billingAddress' + this.getCode();
            registry.async('checkoutProvider')(function (checkoutProvider) {
                defaultAddressData = checkoutProvider.get(billingAddressCode);

                if (defaultAddressData === undefined) {
                    // Skip if payment does not have a billing address form
                    return;
                }
                billingAddressData = checkoutData.getBillingAddressFromData();

                if (billingAddressData) {
                    checkoutProvider.set(
                        billingAddressCode,
                        $.extend(true, {}, defaultAddressData, billingAddressData)
                    );
                }
                checkoutProvider.on(billingAddressCode, function (providerBillingAddressData) {
                    checkoutData.setBillingAddressFromData(providerBillingAddressData);
                }, billingAddressCode);
            });

            return this;
        },

        /**
         * Initialize child elements
         *
         * @returns {Component} Chainable.
         */
        initChildren: function () {
            this.messageContainer = new Messages();
            this.createMessagesComponent();

            return this;
        },

        /**
         * Create child message renderer component
         *
         * @returns {Component} Chainable.
         */
        createMessagesComponent: function () {

            var messagesComponent = {
                parent: this.name,
                name: this.name + '.messages',
                displayArea: 'messages',
                component: 'Magento_Ui/js/view/messages',
                config: {
                    messageContainer: this.messageContainer
                }
            };

            layout([messagesComponent]);

            return this;
        },

        /**
         * Order OTP popup form validation on keyup
         */
        orderOtpPopupValidation: function() {
            let dataForm = $('#form-validate');
            $('#logicrays-order-otp').keyup( function() {
                $('#otp-error-message').html('');
                dataForm.validation('isValid');
            });
        },

        /**
         * Send Order OTP
         */
        sendOrderOtp: function(isResend) {
            let linkUrl = url.build('otppopup/otp/send');
            $.ajax(linkUrl, {
                data: ko.toJSON({ resend: isResend }),
                type: "post",
                dataType: "json",
                success: function (result) {
                    if(result.success == true) {
                        $('#otp-success-message').html(result.message);
                    } else if(result.success == false) {
                        $('#otp-error-message').html(result.message);
                    }
                },
                error: function (){
                    $('#otp-error-message').html('Unable to send OTP, please try again later.');
                }
            });
        },

        /**
         * Normal Magento order flow
         */
        normalOrderPlaceProcedure: function (data, event) {
            var self = this;
            if (event) {
                event.preventDefault();
            }
            if (this.validate() &&
                additionalValidators.validate() &&
                this.isPlaceOrderActionAllowed() === true
            ) {
                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .done(
                        function () {
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
                return true;
            }
            return false;
        },

        /**
         * OTP Popup Order Flow
         */
        otpPopupOrderFlow : function(self, data, event) {
            self.orderOtpPopupValidation();
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Verify OTP',
                buttons: [{
                    text: $.mage.__('Submit'),
                    class: '',
                    click: function () {
                        let modalObj = this;
                        let otpNo = $('#logicrays-order-otp').val();
                        // verify order OTP
                        let isValidOtp = verifyOrderOtp(
                            url.build('otppopup/otp/verify'),
                            {'otp':otpNo}
                        );

                        isValidOtp.then(function(result) {
                            if (result.success === true) {
                                modalObj.closeModal();
                                return self.normalOrderPlaceProcedure(data, event);
                            } else {
                                $('#otp-error-message').html(result.message);
                                return false;
                            }
                        }).catch(function(err) {
                            $('#otp-error-message').html('Unable to verify OTP, Please try again later.');
                            return false;
                        })
                    }
                },{
                    text: $.mage.__('Resend OTP'),
                    class: '',
                    click: function () {
                        $('#otp-success-message').html('');
                        // resenf order otp
                        self.sendOrderOtp(true);
                    }
                }]
            };

            var popup = modal(options, $('#otp-popup-content'));
            $('#otp-popup-content').modal('openModal');

            // Send Order OTP
            self.sendOrderOtp(false);

            $('#otp-popup-content').on('modalclosed', function() {
                $('#otp-success-message').html('');
                $('#otp-error-message').html('');
                $('#logicrays-order-otp').val('');
                return false;
            });
            return false;
        },

        /**
         * Customized place order flow with OTP popup.
         */
        placeOrder: function (data, event) {
            var self = this;
            let popUpEnable = validateOrderOtpPopupConfigurations(
                url.build('otppopup/config/check'),
                {'paymentMethod':self.getCode()}
            );

            popUpEnable.then(function(result) {
                if (result.success === true) {
                    return self.otpPopupOrderFlow(self, data, event);
                } else {
                    return self.normalOrderPlaceProcedure(data, event);
                }
            }).catch(function(err) {
                $('#otp-error-message').html('Technical error, Please try again later.');
                return false;
            });
        },

        /**
         * @return {*}
         */
        getPlaceOrderDeferredObject: function () {
            return $.when(
                placeOrderAction(this.getData(), this.messageContainer)
            );
        },

        /**
         * @return {Boolean}
         */
        selectPaymentMethod: function () {
            selectPaymentMethodAction(this.getData());
            checkoutData.setSelectedPaymentMethod(this.item.method);

            return true;
        },

        isChecked: ko.computed(function () {
            return quote.paymentMethod() ? quote.paymentMethod().method : null;
        }),

        isRadioButtonVisible: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length !== 1;
        }),

        /**
         * Get payment method data
         */
        getData: function () {
            return {
                'method': this.item.method,
                'po_number': null,
                'additional_data': null
            };
        },

        /**
         * Get payment method type.
         */
        getTitle: function () {
            return this.item.title;
        },

        /**
         * Get payment method code.
         */
        getCode: function () {
            return this.item.method;
        },

        /**
         * @return {Boolean}
         */
        validate: function () {
            return true;
        },

        /**
         * @return {String}
         */
        getBillingAddressFormName: function () {
            return 'billing-address-form-' + this.item.method;
        },

        /**
         * Dispose billing address subscriptions
         */
        disposeSubscriptions: function () {
            // dispose all active subscriptions
            var billingAddressCode = 'billingAddress' + this.getCode();

            registry.async('checkoutProvider')(function (checkoutProvider) {
                checkoutProvider.off(billingAddressCode);
            });
        }
    });
});
