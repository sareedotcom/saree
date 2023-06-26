var config = {
    paths: {
        slick: 'Magento_Catalog/js/slick'
    },
    shim: {
        slick: {
            deps: ['jquery', 'jquery/jquery-migrate']
        }
    },
    map: {
        '*': {
            custom: 'js/custom'
        }
    }
};