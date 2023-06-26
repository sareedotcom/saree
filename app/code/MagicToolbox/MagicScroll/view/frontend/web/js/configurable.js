
define([
    'jquery'
], function ($) {
    'use strict';

    var mixin = {

        options: {
            mtConfig: {
                enabled: false,
                useOriginalGallery: true,
                currentProductId: null,
                galleryData: [],
                tools: {},
                thumbSwitcherOptions: {},
                mtContainerSelector: 'div.MagicToolboxContainer'
            }
        },

        mtLockedMethods: {},

        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function () {
            if (this._mtLockedOrLockMethod('_initializeOptions')) {
                this._super();
                return;
            }

            this._super();
            this._mtUnlockMethod('_initializeOptions');

            if (typeof(this.options.spConfig.magictoolbox) == 'undefined' || typeof(this.options.spConfig.productId) == 'undefined') {
                return;
            }

            this.options.mtConfig.enabled = true;
            this.options.mtConfig.currentProductId = this.options.spConfig.productId;
            this.options.mtConfig.useOriginalGallery = this.options.spConfig.magictoolbox.useOriginalGallery;
            this.options.mtConfig.galleryData = this.options.spConfig.magictoolbox.galleryData;
            this.options.mtConfig.tools = {
                'Magic360': {
                    'idTemplate': '{tool}-product-{id}',
                    'objName': 'Magic360',
                    'undefined': true
                },
                'MagicSlideshow': {
                    'idTemplate': '{tool}-product-{id}',
                    'objName': 'MagicSlideshow',
                    'undefined': true
                },
                'MagicScroll': {
                    'idTemplate': '{tool}-product-{id}',
                    'objName': 'MagicScroll',
                    'undefined': true
                },
                'MagicZoomPlus': {
                    'idTemplate': '{tool}Image-product-{id}',
                    'objName': 'MagicZoom',
                    'undefined': true
                },
                'MagicZoom': {
                    'idTemplate': '{tool}Image-product-{id}',
                    'objName': 'MagicZoom',
                    'undefined': true
                },
                'MagicThumb': {
                    'idTemplate': '{tool}Image-product-{id}',
                    'objName': 'MagicThumb',
                    'undefined': true
                }
            };
            for (var tool in this.options.mtConfig.tools) {
                this.options.mtConfig.tools[tool].undefined = (typeof(window[tool]) == 'undefined');
            }
            if (!this.options.mtConfig.tools['MagicZoom'].undefined) {
                var suffix = MagicZoom.version.indexOf('Plus') > -1 ? 'Plus' : '';
                this.options.mtConfig.tools['MagicZoom'].undefined = true;
                this.options.mtConfig.tools['MagicZoomPlus'].undefined = true;
                this.options.mtConfig.tools['MagicZoom' + suffix].undefined = false;
            }

            //NOTE: get thumb switcher options
            var container = $(this.options.mtConfig.mtContainerSelector);
            if (container.length && container.magicToolboxThumbSwitcher) {
                this.options.mtConfig.thumbSwitcherOptions = container.magicToolboxThumbSwitcher('getOptions');
            }
        },

        /**
         * Change displayed product image according to chosen options of configurable product
         * @private
         */
        _changeProductImage: function () {
            if (this._mtLockedOrLockMethod('_changeProductImage')) {
                this._super();
                return;
            }

            if (!this.options.mtConfig.enabled || this.options.mtConfig.useOriginalGallery) {
                this._super();
                this._mtUnlockMethod('_changeProductImage');
                return;
            }
            this._mtUnlockMethod('_changeProductImage');

            var spConfig = this.options.spConfig,
                productId = spConfig.productId,
                galleryData = [],
                tools = {};

            if (typeof(this.simpleProduct) != 'undefined') {
                productId = this.simpleProduct;
            }

            galleryData = this.options.mtConfig.galleryData;

            //NOTE: associated product has no images
            if (!galleryData[productId].length) {
                productId = spConfig.productId;
            }

            //NOTE: there is no need to change gallery
            if (this.options.mtConfig.currentProductId == productId) {
                return;
            }

            tools = this.options.mtConfig.tools;

            //NOTE: stop tools
            for (var tool in tools) {
                if (tools[tool].undefined) {
                    continue;
                }
                var id = tools[tool].idTemplate.replace('{tool}', tool).replace('{id}', this.options.mtConfig.currentProductId);
                if (document.getElementById(id)) {
                    window[tools[tool].objName].stop(id);
                }
            }

            //NOTE: stop MagiScroll on selectors
            var id = 'MagicToolboxSelectors'+this.options.mtConfig.currentProductId,
                selectorsEl = document.getElementById(id);
            if (!tools['MagicScroll'].undefined && selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
                MagicScroll.stop(id);
            }

            //NOTE: replace gallery
            if (this.options.gallerySwitchStrategy === 'prepend' && productId != spConfig.productId) {
                var tool = null,
                    galleryDataNode = document.createElement('div'),
                    toolMainNode = null,
                    toolLinkAttrName = null,
                    mpGalleryDataNode = document.createElement('div'),
                    mpSelectors = null,
                    mpSlides = null;

                //NOTE: selected product gallery
                galleryDataNode = $(galleryDataNode).html(galleryData[productId]);

                //NOTE: main product gallery
                mpGalleryDataNode = $(mpGalleryDataNode).html(galleryData[spConfig.productId]);

                //NOTE: determine main tool
                if (galleryData[productId].indexOf('MagicZoomPlus') > -1 || galleryData[spConfig.productId].indexOf('MagicZoomPlus') > -1) {
                    tool = 'MagicZoomPlus';
                    toolMainNode = galleryDataNode.find('a.MagicZoom');
                    toolLinkAttrName = 'data-zoom-id';
                } else if (galleryData[productId].indexOf('MagicZoom') > -1 || galleryData[spConfig.productId].indexOf('MagicZoom') > -1) {
                    tool = 'MagicZoom';
                    toolMainNode = galleryDataNode.find('a.MagicZoom');
                    toolLinkAttrName = 'data-zoom-id';
                } else if (galleryData[productId].indexOf('MagicThumb') > -1 || galleryData[spConfig.productId].indexOf('MagicThumb') > -1) {
                    tool = 'MagicThumb';
                    toolMainNode = galleryDataNode.find('a.MagicThumb');
                    toolLinkAttrName = 'data-thumb-id';
                } else if (galleryData[productId].indexOf('MagicSlideshow') > -1 || galleryData[spConfig.productId].indexOf('MagicSlideshow') > -1) {
                    tool = 'MagicSlideshow';
                    //NOTE: main product slides
                    mpSlides = mpGalleryDataNode.find('.MagicSlideshow').children();
                } else if (galleryData[productId].indexOf('MagicScroll') > -1 || galleryData[spConfig.productId].indexOf('MagicScroll') > -1) {
                    tool = 'MagicScroll';
                    //NOTE: main product slides
                    mpSlides = mpGalleryDataNode.find('.MagicScroll').children();
                }

                mpSelectors = mpGalleryDataNode.find('#MagicToolboxSelectors' + spConfig.productId + ' a');

                if (mpSelectors.length) {
                    var newId = tools[tool].idTemplate.replace('{tool}', tool).replace('{id}', productId);

                    //NOTE: when there are no images in the gallery (only video or spin)
                    if (!toolMainNode.length) {
                        galleryDataNode.find('#mtImageContainer').html(mpGalleryDataNode.find('#mtImageContainer').html());
                        toolMainNode = galleryDataNode.find('a.' + tools[tool].objName);
                        toolMainNode.attr('id', newId);
                    }

                    mpSelectors.filter('[' + toolLinkAttrName + ']').attr(toolLinkAttrName, newId);

                    mpSelectors.removeClass('active-selector');

                    var mpSpinSelector = mpSelectors.filter('.m360-selector'),
                        spinSelector,
                        spinContainer,
                        spin,
                        spinId;

                    if (mpSpinSelector.length) {
                        spinSelector = galleryDataNode.find('#MagicToolboxSelectors' + productId + ' a.m360-selector');
                        if (spinSelector.length) {
                            //NOTE: exclude main product spin selector
                            mpSelectors = mpSelectors.filter(':not(.m360-selector)');
                        } else {
                            //NOTE: append spin
                            spinContainer = mpGalleryDataNode.find('#mt360Container').css('display', 'none');
                            spin = spinContainer.find('.Magic360');
                            spinId = spin.attr('id');
                            spinId = spinId.replace(/\-\d+$/, '-' + productId);
                            spin.attr('id', spinId);
                            galleryDataNode.find('#mt360Container').replaceWith(spinContainer);
                        }
                    }

                    galleryDataNode.find('.MagicToolboxSelectorsContainer').removeClass('hidden-container');
                    galleryDataNode.find('#MagicToolboxSelectors' + productId).append(mpSelectors);
                }

                if (mpSlides && mpSlides.length) {
                    galleryDataNode.find('.' + tool).append(mpSlides);
                }

                $(this.options.mtConfig.mtContainerSelector).replaceWith(galleryDataNode.html());
            } else {
                $(this.options.mtConfig.mtContainerSelector).replaceWith(galleryData[productId]);
            }

            //NOTE: start MagiScroll on selectors
            id = 'MagicToolboxSelectors'+productId;
            selectorsEl = document.getElementById(id);
            if (!tools['MagicScroll'].undefined && selectorsEl && selectorsEl.className.match(/(?:\s|^)MagicScroll(?:\s|$)/)) {
                //NOTE: to do not start MagicScroll with thumb switcher
                selectorsEl.setAttribute('ms-started', true);

                //NOTE: fix orientation before start (for left and right templates)
                if (window.matchMedia('(max-width: 767px)').matches) {
                    var mtContainer = document.querySelector('.MagicToolboxContainer'),
                        dataOptions = selectorsEl.getAttribute('data-options') || '';
                    if (mtContainer && mtContainer.className.match(/(?:\s|^)selectorsLeft|selectorsRight(?:\s|$)/)) {
                        selectorsEl.setAttribute(
                            'data-options',
                            dataOptions.replace(/\borientation *\: *vertical\b/, 'orientation:horizontal')
                        );
                    }
                }

                MagicScroll.start(id);
            }

            //NOTE: initialize thumb switcher widget
            var container = $(this.options.mtConfig.mtContainerSelector);
            if (container.length) {
                this.options.mtConfig.thumbSwitcherOptions.productId = productId;
                if (container.magicToolboxThumbSwitcher) {
                    container.magicToolboxThumbSwitcher(this.options.mtConfig.thumbSwitcherOptions);
                } else {
                    //NOTE: require thumb switcher widget
                    /*
                    require(["magicToolboxThumbSwitcher"], function ($) {
                        container.magicToolboxThumbSwitcher(this.options.mtConfig.thumbSwitcherOptions);
                    });
                    */
                }
            }

            //NOTE: update current product id
            this.options.mtConfig.currentProductId = productId;

            //NOTE: start tools
            for (var tool in tools) {
                if (tools[tool].undefined) {
                    continue;
                }
                var id = tools[tool].idTemplate.replace('{tool}', tool).replace('{id}', this.options.mtConfig.currentProductId);
                if (document.getElementById(id)) {
                    window[tools[tool].objName].start(id);
                }
            }
        },

        /**
         * Check for method is locked or lock method
         *
         * @param {String} methodName
         * @returns {bool}
         * @private
         */
        _mtLockedOrLockMethod: function (methodName) {
            if (this.mtLockedMethods[methodName]) {
                return true;
            }
            this.mtLockedMethods[methodName] = true;
            return false;
        },

        /**
         * Unlock method
         *
         * @param {String} methodName
         * @private
         */
        _mtUnlockMethod: function (methodName) {
            this.mtLockedMethods[methodName] = false;
        }
    };

    return function (widget) {
        var widgetNameSpace, widgetName;

        if (typeof(widget) == 'undefined') {
            widget = $.mage.configurable;
        }

        widgetNameSpace = widget.prototype.namespace || 'mage';
        widgetName = widget.prototype.widgetName || 'configurable';

        /* NOTE: to skip multiple mixins */
        /*
        if (typeof(widget.prototype.options.mtConfig) != 'undefined') {
            return widget;
        }
        */

        $.widget(widgetNameSpace + '.' + widgetName, widget, mixin);

        return $[widgetNameSpace][widgetName];
    };
});
