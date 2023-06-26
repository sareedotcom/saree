/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    shim: {
        'Potato_Zendesk/js/tiny_mce/tiny_mce_src': {
            'exports': 'tinymce'
        }
    },
    map: {
        '*': {
            'zendesk_tinymce': 'Potato_Zendesk/js/tiny_mce/tiny_mce_src'
        }
    }
};
