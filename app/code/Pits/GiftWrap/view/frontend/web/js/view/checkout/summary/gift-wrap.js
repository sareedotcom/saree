/**
 * PIT Solutions
 *
 * NOTICE OF LICENSE
 * This source file is licenced under Webshop Extensions software license.
 * Once you have purchased the software with PIT Solutions AG or one of its
 * authorised resellers and provided that you comply with the conditions of this contract,
 * PIT Solutions AG grants you a non-exclusive license, unlimited in time for the usage of
 * the software in the manner of and for the purposes specified in the documentation according
 * to the subsequent regulations.
 *
 * @category Pits
 * @package  Pits_GiftWrap
 * @author   Pit Solutions Pvt. Ltd.
 * @copyright Copyright (c) 2021 PIT Solutions AG. (www.pitsolutions.ch)
 * @license https://www.webshopextension.com/en/licence-agreement/
 */

define([
    'ko',
    'Magento_Checkout/js/model/totals',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/quote',
    'jquery',
    'Pits_GiftWrap/js/action/gift-wrap-handler',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Checkout/js/model/cart/cache',
    'Magento_Customer/js/customer-data',
    'mage/storage',
    'mage/validation',
    'jquery/ui',
    'mage/cookies',
], function (ko, totals, Component, stepNavigator, quote, $, giftWrapHandler, defaultTotal, cartCache) {
    'use strict';

    var imageData = window.checkoutConfig.imageData;
    var arrayIndex = [];
    var giftWrapUIRenderer = ko.observableArray([]);
    var quoteGiftWrapData = ko.observableArray([]);
    var giftWrapData = window.checkoutConfig.giftWrapData;

    return Component.extend({
        defaults: {
            template: 'Pits_GiftWrap/checkout/summary/gift-wrap'
        },
        totals: totals.totals(),
        items: ko.observable([]),
        imageData: imageData,
        arrayIndex: arrayIndex,
        giftWrapData: giftWrapData,
        getItems: totals.getItems(),

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            this.setItems(totals.getItems()());
            totals.getItems().subscribe(function (items) {
                this.setItems(items);
            }.bind(this));

            self = this;
            var giftDataOrder = $.parseJSON(giftWrapData);
            self.uiRenderer = ko.observable({});
            self.savedGiftWrapData = ko.observable({
                id: ko.observable(false),
                message: ko.observable(null)
            });
            self.uiRenderer({
                displayGiftWrapForm: ko.observable(true),
                itemGiftWrapSelected: ko.observable(false),
                showGiftMessageContainer: ko.observable(false),
                displayItemGiftMessage: ko.observable(false),
                giftWrapSummary: ko.observable(false),
                canWrapItem: ko.observable(false)
            });
            if (giftDataOrder.order != null && giftDataOrder.order.id) {
                self.itemGiftMessage = giftDataOrder.order.message;
                self.uiRenderer().displayGiftWrapForm(false);
                self.uiRenderer().giftWrapSummary(true);
                self.savedGiftWrapData().id(giftDataOrder.order.id);
                self.savedGiftWrapData().message(giftDataOrder.order.message);
            }
            giftWrapUIRenderer.push(self.uiRenderer());
            quoteGiftWrapData.push(self.savedGiftWrapData());
            self.index = giftWrapUIRenderer().length - 1;
            self.giftWrapSaveUrl = giftDataOrder.giftWrapSaveUrl;
            self.giftWrapRemoveUrl = giftDataOrder.giftWrapRemoveUrl;
            self.giftWrapLabelWithPrice = giftDataOrder.giftWrapLabelWithPrice;
            self.cartWrapNote = giftDataOrder.cartWrapNote;
            self.itemWrapNote = giftDataOrder.itemWrapNote;
            self.showSummary = giftDataOrder.showSummary;
            self.canCartWrap = giftDataOrder.canCartWrap;
            self.hasTwoMore = giftDataOrder.hasTwoMore;
            self.addGiftMessageLabel = giftDataOrder.addGiftMessageLabel;
            self.giftWrapFeeLabel = giftDataOrder.giftWrapFeeLabel;
            self.itemGiftMessage = null;
            self.isWholeCart = false;
            let items;
            if (items = giftDataOrder.items) {
                for (let i = 0; i < items.length; ++i) {
                    self.savedGiftWrapData = ko.observable({
                        id: ko.observable(false),
                        message: ko.observable(false)
                    });
                    self.uiRenderer = ko.observable({});
                    self.uiRenderer({
                        displayGiftWrapForm: ko.observable(true),
                        itemGiftWrapSelected: ko.observable(false),
                        showGiftMessageContainer: ko.observable(false),
                        displayItemGiftMessage: ko.observable(false),
                        giftWrapSummary: ko.observable(false),
                        canWrapItem: ko.observable(items[i].canWrapItem),
                    });
                    let $msg = null;
                    if (items[i].message) {
                        $msg = items[i].message;
                    }
                    self.itemGiftMessage = $msg;
                    self.savedGiftWrapData = ko.observable({
                        id: ko.observable(items[i].id),
                        message: ko.observable($msg)
                    });
                    if (items[i].id != null) {

                        self.uiRenderer({
                            displayGiftWrapForm: ko.observable(false),
                            itemGiftWrapSelected: ko.observable(false),
                            showGiftMessageContainer: ko.observable(false),
                            displayItemGiftMessage: ko.observable(false),
                            giftWrapSummary: ko.observable(true),
                            canWrapItem: ko.observable(items[i].canWrapItem)
                        });
                        self.uiRenderer().displayGiftWrapForm(false);
                        self.uiRenderer().giftWrapSummary(true);
                        self.uiRenderer().canWrapItem(items[i].canWrapItem);
                    }
                    giftWrapUIRenderer.push(self.uiRenderer());
                    quoteGiftWrapData.push(self.savedGiftWrapData());
                    self.arrayIndex[items[i].itemId] = giftWrapUIRenderer().length - 1;
                }
            }
        },

        /**
         * Get the variable value assigned in the ui renderer array
         *
         * @param index
         * @param variableName
         * @return {boolean}
         */
        rendererValueWithIndex: function (index, variableName) {
            let returnVal = false;
            ko.utils.arrayForEach(giftWrapUIRenderer(), function (uiData, uiIndex) {
                if (index === uiIndex && typeof uiData[variableName] != "undefined") {
                    returnVal = uiData[variableName]();
                }
            });

            return returnVal;
        },

        /**
         * Get gift wrap renderering value from the respective observable array
         *
         * @param index
         * @param wrapDataIdentifier
         * @return {boolean}
         */
        giftWrapRendererValueWithIndex: function (index, wrapDataIdentifier) {
            let returnVal = false;
            ko.utils.arrayForEach(quoteGiftWrapData(), function (wrapData, dataIndex) {
                if (index === dataIndex && typeof wrapData[wrapDataIdentifier] != "undefined") {
                    returnVal = wrapData[wrapDataIdentifier]();
                }
            });
            return returnVal;
        },

        /**
         * Trigger gift wrap message update
         */
        triggerGiftWrapUpdateWithIndex: function () {
            this.updateObservableValues(this.index, {
                giftWrapSummary: false,
                displayGiftWrapForm: true,
                itemGiftWrapSelected: true,
                showGiftMessageContainer: true,
                displayItemGiftMessage: true,
            });
        },

        /**
         * Set items to observable field
         *
         * @param {Object} items
         */
        setItems: function (items) {
            if (items && items.length > 0) {
                items = items.slice(parseInt(-this.maxCartItemsToDisplay, 10));
            }
            this.items(items);
        },

        getItemsLength: function() {
            return totals.getItems()().length;
        },

        /**
         * @return array
         * @param item_id
         */
        getImageItem: function (item_id) {
            if (this.imageData[item_id]) {
                return this.imageData[item_id];
            }

            return [];
        },

        /**
         * @return string|null
         * @param item_id
         */
        getSrc: function (item_id) {
            if (this.imageData[item_id]) {
                return this.imageData[item_id].src;
            }

            return null;
        },

        /**
         * @return string|null
         * @param item_id
         */
        getWidth: function (item_id) {
            if (this.imageData[item_id]) {
                return this.imageData[item_id].width;
            }

            return null;
        },

        /**
         * @return string|null
         * @param item_id
         */
        getHeight: function (item_id) {
            if (this.imageData[item_id]) {
                return this.imageData[item_id].height;
            }

            return null;
        },

        /**
         * @return string|null
         * @param item_id
         */
        getAlt: function (item_id) {
            if (this.imageData[item_id]) {
                return this.imageData[item_id].alt;
            }

            return null;
        },

        /**
         * Get index value from item id
         *
         * @param item_id
         * @returns int
         */
        getArrayIndex: function (item_id) {
            return self.arrayIndex[item_id];
        },

        /**
         * Get the variable value assigned in the ui renderer array
         *
         * @param item_id
         * @param variableName
         * @return {boolean}
         */
        rendererValue: function (item_id, variableName) {
            let returnVal = false;
            var index = this.getArrayIndex(item_id);
            ko.utils.arrayForEach(giftWrapUIRenderer(), function (uiData, uiIndex) {
                if (index === uiIndex && typeof uiData[variableName] != "undefined") {
                    returnVal = uiData[variableName]();
                }
            });

            return returnVal;
        },

        /**
         * Set ui renderer value inside the observable array
         *
         * @param index
         * @param variableName
         * @param value
         * @return {*}
         */
        setUiRendererValue: function (index, variableName, value) {
            ko.utils.arrayForEach(giftWrapUIRenderer(), function (uiData, arrayIndex) {
                if (index === arrayIndex && typeof uiData[variableName] != "undefined") {
                    uiData[variableName](value);
                }
            });
        },

        /**
         * Trigger gift wrap checkbox selection
         *
         * @param data
         * @param element
         */
        triggerGiftWrapSelection: function (data, element) {
            let elementTriggered = $(element.target);
            let triggeredIndex = elementTriggered.data('index');
            let triggeredItemId = elementTriggered.data('itemid');
            let giftMessage = null;
            let selectedValue = elementTriggered.is(":checked");
            self.updateObservableValues(triggeredIndex, {
                showGiftMessageContainer: selectedValue,
                itemGiftWrapSelected: selectedValue
            });
            self.updateGiftWrapSelection(triggeredIndex, triggeredItemId, giftMessage);
        },

        /**
         * Update gift wrap selection to db
         *
         * @param arrayIndex
         * @param triggeredItemId
         * @param giftMessage
         */
        updateGiftWrapSelection: function (arrayIndex, triggeredItemId, giftMessage) {
            let params = {
                'itemId': triggeredItemId,
                'message': this.rendererValueWithIndex(arrayIndex, 'displayItemGiftMessage') ? giftMessage : '',
                'removeGiftWrap': !this.rendererValueWithIndex(arrayIndex, 'itemGiftWrapSelected'),
                'is_whole_order': triggeredItemId ? false : true,
                'index': arrayIndex
            };
            giftWrapHandler(params, this.giftWrapSaveUrl, this.updateDOMAfterAddingGiftWrap);
        },

        /**
         * Trigger add gift wrap message checkbox selection
         *
         * @param data
         * @param element
         */
        triggerAddMessageSelection: function (data, element) {
            let elementTriggered = $(element.target);
            let triggeredIndex = elementTriggered.data('index');
            let selectedValue = elementTriggered.is(":checked");
            let updateUIElements = {displayItemGiftMessage: selectedValue};
            if (!selectedValue) {
                updateUIElements['itemGiftMessage'] = null;
            }
            self.updateObservableValues(triggeredIndex, updateUIElements);
            if (!selectedValue && this.itemGiftMessage) {
                this.updateGiftWrapSelection(triggeredIndex);
            }
        },

        /**
         * General method to update set ui renderer observable elements
         *
         * @param triggeredIndex
         * @param dataToUpdate
         */
        updateObservableValues: function (triggeredIndex, dataToUpdate) {
            ko.utils.objectForEach(dataToUpdate, function (key, value) {
                self.setUiRendererValue(triggeredIndex, key, value);
            })
        },

        /**
         * Update gift wrap message
         * When add gift message checkbox is not selected and contains gift message,
         * then remove the message from the wrap data
         */
        triggerMessageUpdate: function () {
            if (!this.displayItemGiftMessage() && this.itemGiftMessage) {
                this.updateItemGiftMessage();
            }
        },

        /**
         * Update gift wrap message to the wrap selected
         */
        updateItemGiftMessage: function (event, element) {
            let arrayIndex = $(element.target).data('index');
            let triggeredItemId = $(element.target).data('itemid');
            let giftMessage = $(element.target).val();
            self.updateGiftWrapSelection(arrayIndex, triggeredItemId, giftMessage);
        },

        /**
         * Update DOM after adding a gift wrap to the item
         * Show summary if message is also entered and remove the form
         *
         * @param response
         */
        updateDOMAfterAddingGiftWrap: function (response) {
            if (response.giftWrap) {
                let updateGiftWrapData = {id: response.giftWrap.id};
                if (response.giftWrap.message) {
                    updateGiftWrapData['message'] = response.giftWrap.message;
                    self.updateObservableValues(response.index, {
                        giftWrapSummary: true,
                        displayGiftWrapForm: false
                    });
                }
                self.setGiftWrapRendererValue(response.index, updateGiftWrapData);
            }
            cartCache.set('totals', null);
            defaultTotal.estimateTotals();
        },

        /**
         * Remove gift wrap for item
         *
         * @param event
         * @param element
         */
        removeGiftWrap: function (event, element) {
            let params = {
                'itemId': $(element.target).data('itemid'),
                'wrapId': $(element.target).data('wrap-id'),
                'index': $(element.target).data('index')
            };
            giftWrapHandler(params, self.giftWrapRemoveUrl, self.updateDOMAfterRemovingGiftWrap);
        },

        /**
         * Update DOM after removing gift wrap from item
         * Show wrap form with everything deselected
         *
         * @param response
         */
        updateDOMAfterRemovingGiftWrap: function (response) {
            self.setGiftWrapRendererValue(response.index, {id: '', message: ''});
            self.updateObservableValues(response.index, {
                giftWrapSummary: false,
                displayGiftWrapForm: true,
                itemGiftWrapSelected: false,
                showGiftMessageContainer: false,
                displayItemGiftMessage: false,
            });
            cartCache.set('totals', null);
            defaultTotal.estimateTotals();
        },

        /**
         * Trigger gift wrap message update
         */
        triggerGiftWrapUpdate: function (data, element) {
            let elementTriggered = $(element.target);
            var triggeredIndex = elementTriggered.data('index');
            self.updateObservableValues(triggeredIndex, {
                giftWrapSummary: false,
                displayGiftWrapForm: true,
                itemGiftWrapSelected: true,
                showGiftMessageContainer: true,
                displayItemGiftMessage: true,
            });
        },

        /**
         * Get gift wrap renderering value from the respective observable array
         *
         * @param item_id
         * @param wrapDataIdentifier
         * @return {boolean}
         */
        giftWrapRendererValue: function (item_id, wrapDataIdentifier) {
            let returnVal = false;
            var index = this.getArrayIndex(item_id);
            ko.utils.arrayForEach(quoteGiftWrapData(), function (wrapData, dataIndex) {
                if (index === dataIndex && typeof wrapData[wrapDataIdentifier] != "undefined") {
                    returnVal = wrapData[wrapDataIdentifier]();
                }
            });
            return returnVal;
        },

        /**
         * Set Gift wrap value inside the observable array
         *
         * @param index
         * @param dataToUpdate
         * @return {*}
         */
        setGiftWrapRendererValue: function (index, dataToUpdate) {
            ko.utils.objectForEach(dataToUpdate, function (field, value) {
                ko.utils.arrayForEach(quoteGiftWrapData(), function (wrapData, dataIndex) {
                    if (index === dataIndex && typeof wrapData[field] != "undefined") {
                        wrapData[field](value);
                    }
                });
            })
        },
    });
});
