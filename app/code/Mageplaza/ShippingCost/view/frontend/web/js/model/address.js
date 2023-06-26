/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingCost
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';

    var cacheKey = 'mpshippingcost-address';

    return {
        getData: function (key) {
            var data = customerData.get(cacheKey)();

            return typeof key === 'undefined' ? data : data[key];
        },

        setData: function (key, value) {
            var data = customerData.get(cacheKey)();

            data[key] = value;

            customerData.set(cacheKey, data);
        }
    };
});
