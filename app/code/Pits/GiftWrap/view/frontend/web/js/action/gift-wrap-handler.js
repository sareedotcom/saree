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

define(
    [
        'ko',
        'jquery',
        'mage/storage',
        'Magento_Customer/js/customer-data'
    ],
    function (
        ko,
        $,
        storage,
        customerData
    ) {
        'use strict';
        return function (data, postUrl, callback) {
            $('body').trigger('processStart');
            return storage.post(
                postUrl,
                JSON.stringify(data),
                false
            ).done(
                function (parsedResponse) {
                    $('body').trigger('processStop');
                    if (parsedResponse && parsedResponse.error) {
                        customerData.set('messages', {
                            messages: [{
                                type: 'error',
                                text: parsedResponse.message
                            }]
                        });
                        if (callback) {
                            callback(parsedResponse);
                        }
                        // navigate to top when an error occurs
                        $('html, body').animate({
                            scrollTop: 0
                        }, 'slow');
                    } else if (parsedResponse) {
                        customerData.set('messages', {});
                        if (callback) {
                            if (data.index || data.index == 0) {
                                parsedResponse.index = data.index;
                            }
                            callback(parsedResponse);
                        }
                    }
                }
            ).fail(
                function (response) {
                    $('body').trigger('processStop');
                }
            );
        };
    }
);
