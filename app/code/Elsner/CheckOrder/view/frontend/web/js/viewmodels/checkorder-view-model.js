define([
    'jquery',
    'uiComponent',
    'ko',
    'Elsner_CheckOrder/js/models/checkorder-model'
], function ($, Component, ko, checkOrderModel) {
        'use strict';
        return Component.extend({
            orderData: checkOrderModel.orderData,
            initialize: function () {
                this._super();
            }
        });
    }
);