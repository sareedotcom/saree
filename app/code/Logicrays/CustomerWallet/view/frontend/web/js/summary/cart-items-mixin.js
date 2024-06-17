/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

define([], function () {
    'use strict';

    return function (target) {
        return target.extend({

            /**
             * @inheritdoc
             */
            isWalletRequest: function() {
                var walletSku = window.checkoutConfig.wallet_sku;
                var checkoutSku = window.checkoutConfig.checkout_sku;
                if (walletSku == checkoutSku) {
                    return false;
                }
                return true;
            },
        });
    }
});
