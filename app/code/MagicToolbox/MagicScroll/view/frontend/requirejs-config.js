
var config = {
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'MagicToolbox_MagicScroll/js/configurable': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'MagicToolbox_MagicScroll/js/swatch-renderer': true
            },
            /* NOTE: for Magento v2.0.x */
            'Magento_Swatches/js/SwatchRenderer': {
                'MagicToolbox_MagicScroll/js/swatch-renderer': true
            }
        }
    },
    map: {
        '*': {
        }
    }
};
