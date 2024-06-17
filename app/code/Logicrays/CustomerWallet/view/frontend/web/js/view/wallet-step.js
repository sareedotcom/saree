define(
    [
        'ko',
        'mage/url',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Catalog/js/price-utils',
        'uiComponent'
    ],
    function (ko, url, $, quote, totalsDefaultProvider, priceUtils, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Logicrays_CustomerWallet/wallet-step',
            },

            isChecked: ko.observable(true),

            initialize: function() {
                this._super();
                this.isChecked(this.isWalletUsed());
            },

            context: function () {
                return this;
            },

            /**
             *
             * @returns string
             */
            getWalletAmount: function () {
                var walletAmount = window.checkoutConfig.remain_wallet_amount;
                if (walletAmount <= 0) {
                    var walletAmount = 0.00;
                }
                var walletAmount = this.getFormatedPrice(walletAmount);
                return walletAmount;
            },

            /**
             *
             * @returns string
             */
            getValidateWalletAmount: function () {
                var walletAmount = window.checkoutConfig.remain_wallet_amount;
                if (walletAmount <= 0) {
                    var walletAmount = 0;
                }
                return walletAmount;
            },

            /**
             *
             * @returns double
             */
            getOrderGrandTotal: function () {
                var amountToPay = quote.totals().base_grand_total;
                var usedWalletMoney = window.checkoutConfig.wallet_applied_amount;
                if (amountToPay <= 0) {
                    var amountToPay = 0.00;
                } else if (amountToPay > usedWalletMoney) {
                    var amountToPay = amountToPay - usedWalletMoney;
                }
                var amountToPay = this.getFormatedPrice(amountToPay);

                return amountToPay;
            },

            /**
             *
             * @returns string
             */
            getOrderRamainingToPay: function () {
                var ordertotal = quote.totals().base_grand_total;
                var usedWalletMoney = window.checkoutConfig.wallet_applied_amount;
                var orderRamainingToPay = ordertotal - usedWalletMoney;
                var orderRamainingToPay = this.getFormatedPrice(orderRamainingToPay);

                return orderRamainingToPay;
            },

            /**
             *
             * @returns int
             */
            getOrderRamainingToPaySelectWalletMethod: function () {
                var ordertotal = this.getOrderGrandTotal;
                var usedWalletMoney = window.checkoutConfig.wallet_applied_amount;
                var remainingtopay = ordertotal - usedWalletMoney;
                if (remainingtopay <= 0) {
                    return true;
                }
            },

            /**
             *
             * @returns double
             */
            isEnabled: function () {
                return window.checkoutConfig.wallet_module_is_enable;
            },

            isLoggedIn: function () {
                return window.checkoutConfig.is_logged_in;
            },

            /**
             *
             * @returns double
             */
            disableWalletPay: function () {
                return window.checkoutConfig.disable_wallet;
            },

            /**
             *
             * @returns double
             */
            getRemainingWalletBalance: function () {
                var walletAmount = window.checkoutConfig.remain_wallet_amount;
                var usedWalletMoney = window.checkoutConfig.wallet_applied_amount;
                var remainingBalance = walletAmount - usedWalletMoney;
                var ramainingBalance = 0.00;
                if (remainingBalance > 0) {
                    var ramainingBalance = walletAmount - usedWalletMoney;
                }
                var ramainingBalance = this.getFormatedPrice(ramainingBalance);
                return ramainingBalance;
            },

            /**
             *
             * @returns bool
             */
            isWalletUsed: function () {
                var walletAppliedAmount = window.checkoutConfig.wallet_applied_amount;
                if (walletAppliedAmount === '0' || walletAppliedAmount == null) {
                    return false;
                }
                return true;
            },

            /**
             *
             * @returns double
             */
            getOrderBaseGrandTotalToPay: function () {
                var walletAmount = window.checkoutConfig.remain_wallet_amount;
                var amountToPay = quote.totals().base_grand_total;

                if (amountToPay <= 0) {
                    return 0.00;
                }
                if (amountToPay > walletAmount) {
                    return walletAmount;
                }
                var amountToPay = this.getFormatedPrice(amountToPay);
                return amountToPay;
            },

            getFormatedPrice: function (amountToFormat) {
                var quoteRate = checkoutConfig.quoteData.base_to_quote_rate;
                var amountToFormat = amountToFormat * quoteRate;
                var basePriceFormat = checkoutConfig.priceFormat;
                var formatedAmount = priceUtils.formatPrice(amountToFormat, basePriceFormat);
                return formatedAmount;
            },

            applyWalletAmount: function() {
                var amountToPay = this.getOrderBaseGrandTotalToPay();

                $('#wallet').change(function() {
                    $('body').trigger('processStart');
                    var isChecked = ($("#wallet").is(":checked")) ? 1 : 0;
                    var linkUrls = url.build('wallet/customer/applywallet');
                    $.ajax({
                        url: linkUrls,
                        type: "POST",
                        dataType: 'json',
                        data: {
                            isChecked: isChecked,
                            amountToPay: amountToPay
                        },
                        complete: function(response){
                            window.location.reload();
                        }
                    });
                });
            }

        });
    }
);
