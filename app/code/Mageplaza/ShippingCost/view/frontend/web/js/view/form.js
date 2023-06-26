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
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Mageplaza_ShippingCost/js/model/address',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, _, Component, customerData, address, modal, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageplaza_ShippingCost/form',
            rateListTmpl: 'Mageplaza_ShippingCost/rate-list'
        },
        calcTimeout: null,

        initObservable: function () {
            var self = this;

            this._super().observe([
                'country',
                'region',
                'regions',
                'regionId',
                'postcode',
                'rates',
                'moreRates',
                'isMore',
                'includeCart',
                'address',
                'isLoading'
            ]);

            this.isMore(address.getData('isMore'));
            this.isLoading(false);
            this.includeCart(address.getData('includeCart'));

            _.each(this.fields, function (key) {
                self[key].subscribe(function (value) {
                    switch (key){
                        // filter region options by country
                        case 'country':
                            self.filterRegions(self.getObjectLabel(value, self.countries));
                            break;
                        // set region label by region id
                        case 'regionId':
                            if (self.regions() && self.regions().length && value) {
                                self.region(self.getObjectLabel(Number(value), self.regions()[0].value));
                            }
                            if (value) {
                                address.setData(key, value);
                            }
                            break;
                    }

                    // store input data
                    if (key !== 'regionId') {
                        address.setData(key, value);
                    }

                    self.updateAddressStr();
                });
            });

            _.each(['isMore', 'includeCart'], function (key) {
                self[key].subscribe(function (value) {
                    address.setData(key, value);

                    if (key === 'includeCart') {
                        self.calculate();
                    }
                });
            });

            this.rates.subscribe(function (value) {
                var valueClone = _.extend([], value);

                valueClone.shift();
                self.moreRates(valueClone);
            });

            this.initCalcEvent();

            return this;
        },

        initCalcEvent: function () {
            var self      = this,
                container = $('.product-add-form'),
                events    = {
                    'input.qty': 'change', //reload on changing qty
                    '[data-role=priceBox]': 'updatePrice', //reload on changing customize or bundle options
                    '.swatch-input': 'change', //reload on changing configurable options
                    '#giftcard-amount-input': 'change' //Magento EE Gift Card compatible
                };

            _.each(events, function (event, elem) {
                container.on(event, elem, function () {
                    clearTimeout(this.eventTimeout);
                    this.eventTimeout = setTimeout(function () {
                        self.calculate();
                    }, 0); // prevent event always trigger twice
                });
            });
        },

        initDefaultAddress: function () {
            var self = this;

            // restore address from input data
            this.setInputFieldData([address.getData()]);

            // apply default address from rule & customer
            customerData.reload(['mpshippingcost-customer'], false);
            customerData.get('mpshippingcost-customer').subscribe(function (data) {
                if (data.hasOwnProperty('address')) {
                    self.setInputFieldData([data.address, self.ruleAddress]);
                } else {
                    self.setInputFieldData([self.ruleAddress]);
                }
            });

            this.calculate(true);
        },

        setInputFieldData: function (data) {
            var self = this;

            _.each(data, function (datum) {
                _.each(self.fields, function (key) {
                    if (!datum.hasOwnProperty(key) || !datum[key]) {
                        return;
                    }

                    if (key === 'country' && !self.getObjectLabel(datum[key], self.countries)) {
                        return;
                    }

                    if (!self[key]()) {
                        self[key](datum[key]);
                    }
                });
            });
        },

        getObjectLabel: function (value, array) {
            var object = _.find(array, function (item) {
                return value === item.value;
            });

            return object ? object.label : null;
        },

        filterRegions: function (countryLabel) {
            var regions = _.find(this.allRegions, function (item) {
                return countryLabel === item.label;
            });

            this.regions(regions ? [regions] : false);
        },

        setRegion: function (elem) {
            // set selected region after render option
            if (elem.regionId()) {
                $('[name="mpshippingcost-region-select"]').val(elem.regionId());
            }
        },

        calculate: function (isForce, clickAction) {
            var self = this;

            clearTimeout(this.calcTimeout);

            if (isForce) {
                self.calcAction(clickAction);

                return;
            }

            this.calcTimeout = setTimeout(function () {
                self.calcAction(clickAction);
            }, 500);
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
                self.isLoading(false);
            });
        },

        getAddress: function () {
            var data = {
                'country': this.country(),
                'postcode': this.postcode()
            };

            if (this.regions() && this.regionId()) {
                data.region_id = this.regionId();
            } else if (this.region()) {
                data.region = this.region();
            }

            return JSON.stringify(data);
        },

        setModalElement: function (element) {
            var self = this, options;

            if (!this.popup) {
                return;
            }

            options = {
                type: 'popup',
                title: $t('Choose your location'),
                responsive: true,
                innerScroll: true,
                buttons: [{
                    text: $t('Done'),
                    class: 'action primary',
                    click: function () {
                        self.calculate(true, true);
                        this.closeModal();
                    }
                }]
            };

            this.modalWindow = element;
            modal(options, $(element));
        },

        showModal: function () {
            $(this.modalWindow).modal('openModal');
        },

        updateAddressStr: function () {
            var self = this, str = '';

            if (!this.popup) {
                return;
            }

            _.each(['region', 'country', 'postcode'], function (key) {
                if (!self[key]()) {
                    return;
                }

                if (str) {
                    str += ', ';
                }

                if (key === 'country') {
                    str += self.getObjectLabel(self[key](), self.countries);
                } else {
                    str += self[key]();
                }
            });

            this.address(str);
        },

        selectIncludeCart: function (data, event) {
            $(event.currentTarget).parent().find('input:not(:disabled)').trigger('click');
        },

        toggleMore: function () {
            this.isMore(!this.isMore());
        },

        addressLabel: function () {
            return this.address() && this.address().length ? $t('Change Address') : $t('Choose Address');
        },

        calcLabel: function () {
            return this.isLoading() ? $t('Calculating ...') : $t('Calculate');
        },

        moreLabel: function () {
            return this.isMore() ? $t('Hide') : $t('Show More');
        }
    });
});

