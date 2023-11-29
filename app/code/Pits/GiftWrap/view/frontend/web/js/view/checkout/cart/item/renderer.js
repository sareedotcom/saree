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
        'jquery',
        'uiComponent',
        'ko',
        'Pits_GiftWrap/js/action/gift-wrap-handler',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/cart/cache',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'mage/storage',
        'mage/validation',
        'jquery/ui',
        'mage/cookies'
    ],
    function (
        $,
        Component,
        ko,
        giftWrapHandler,
        defaultTotal,
        cartCache
    ) {
        var giftWrapUIRenderer = ko.observableArray([]);
        var quoteGiftWrapData = ko.observableArray([]);
        return Component.extend({
            defaults: {
                template: 'Pits_GiftWrap/checkout/cart/item/gift-wrap'
            },
            initialize: function () {
                this._super();
                self = this;
                self.orderItemId = this.orderItemId;
                self.savedGiftWrapData = ko.observable({
                    id: ko.observable(false),
                    message: ko.observable(false)
                });
                self.giftWrapSaveUrl = this.giftWrapSaveUrl;
                self.giftWrapRemoveUrl = this.giftWrapRemoveUrl;
                self.giftWrapLabelWithPrice = this.giftWrapLabelWithPrice;
                self.itemGiftMessage = null;
                self.uiRenderer = ko.observable({});
                self.uiRenderer({
                    displayGiftWrapForm: ko.observable(true),
                    itemGiftWrapSelected: ko.observable(false),
                    showGiftMessageContainer: ko.observable(false),
                    displayItemGiftMessage: ko.observable(false),
                    giftWrapSummary: ko.observable(false),
                });
                if (this.giftWrapData.id) {
                    self.itemGiftMessage = this.giftWrapData.message;
                    self.uiRenderer().displayGiftWrapForm(false);
                    self.uiRenderer().giftWrapSummary(true);
                    self.savedGiftWrapData().id(this.giftWrapData.id);
                    self.savedGiftWrapData().message(this.giftWrapData.message);
                }
                giftWrapUIRenderer.push(self.uiRenderer());
                quoteGiftWrapData.push(self.savedGiftWrapData());
                self.arrayIndex = giftWrapUIRenderer().length - 1;
                self.isWholeCart = this.isWholeCart;
            },

            /**
             * Get the variable value assigned in the ui renderer array
             *
             * @param index
             * @param variableName
             * @return {boolean}
             */
            rendererValue: function (index, variableName) {
                let returnVal = false;
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
                let selectedValue = elementTriggered.is(":checked");
                this.updateObservableValues(triggeredIndex, {
                    showGiftMessageContainer: selectedValue,
                    itemGiftWrapSelected: selectedValue
                });
                this.updateGiftWrapSelection(triggeredIndex);
            },

            /**
             * Update gift wrap selection to db
             *
             * @param arrayIndex
             */
            updateGiftWrapSelection: function (arrayIndex) {
                let params = {
                    'itemId': this.orderItemId,
                    'message': this.rendererValue(arrayIndex, 'displayItemGiftMessage') ? this.itemGiftMessage : '',
                    'removeGiftWrap': !this.rendererValue(arrayIndex, 'itemGiftWrapSelected'),
                    'is_whole_order': this.isWholeCart,
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
                this.updateObservableValues(triggeredIndex, updateUIElements);
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
                this.updateGiftWrapSelection(arrayIndex);
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
                    'itemId': this.orderItemId,
                    'wrapId': $(element.target).data('wrap-id'),
                    'index': this.arrayIndex
                };
                giftWrapHandler(params, this.giftWrapRemoveUrl, this.updateDOMAfterRemovingGiftWrap);
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
            triggerGiftWrapUpdate: function () {
                this.updateObservableValues(this.arrayIndex, {
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
             * @param index
             * @param wrapDataIdentifier
             * @return {boolean}
             */
            giftWrapRendererValue: function (index, wrapDataIdentifier) {
                let returnVal = false;
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
        })
    })
