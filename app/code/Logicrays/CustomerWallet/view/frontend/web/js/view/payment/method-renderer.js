define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'walletpayment',
                component: 'Logicrays_CustomerWallet/js/view/payment/method-renderer/walletpayment'
            }
        );
        return Component.extend({});
    }
);
