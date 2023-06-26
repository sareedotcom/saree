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

define(['jquery'], function ($) {
    'use strict';

    $.widget('mpshippingcost.rule', {
        _create: function () {
            var self = this;

            this.filterRegion($('#mpshippingcost_default_values_country'));

            $('body').on('change', '#mpshippingcost_default_values_country', function () {
                self.filterRegion($(this));
            });
        },

        filterRegion: function (country) {
            var countryLabel = country.children('option:selected').text(),
                regionSelect = $('#mpshippingcost_default_values_region_select'),
                regionText   = $('#mpshippingcost_default_values_region_text'),
                regionOption = regionSelect.children('optgroup[label="' + countryLabel + '"]');

            regionSelect.children('optgroup').hide();

            if (regionOption.length) {
                regionOption.show();
                regionSelect.closest('tr').show();
                regionText.closest('tr').hide();
            } else {
                regionSelect.closest('tr').hide();
                regionText.closest('tr').show();
            }
        }
    });

    return $.mpshippingcost.rule;
});

