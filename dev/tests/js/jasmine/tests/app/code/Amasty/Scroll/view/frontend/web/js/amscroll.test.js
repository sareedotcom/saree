/**
 *  Amasty Scroll Jasmine test
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'underscore',
    'Amasty_Scroll/js/amscroll',
], function ($, _, amScrollScript) {
    'use strict';

    describe('Testing the Amasty_Scroll/js/amscroll widget (amScrollScript)', function () {
        var widget,
            html,
            fixture;

        beforeEach(function () {
            widget = $.mage.amScrollScript.prototype;
            html = $('body').append($('<div>', {id: 'fixture'}));
            fixture = html.find('#fixture');
        });

        afterEach(function () {
            $('#fixture').remove();
        });

        it('Widget extends jQuery object', function () {
            expect($.fn.amScrollScript).toBeDefined();
        });

        describe('"_initNodes" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_initNodes');
                widget._initNodes();
                expect(widget._initNodes).toHaveBeenCalled();
            });
        });

        describe('"initialize" method check', function () {
            it('calling method', function(){
                spyOn(widget, 'initialize');
                fixture.amScrollScript();

                expect(widget.initialize).toEqual(jasmine.any(Function));
                expect(widget.initialize).toHaveBeenCalled();
            });
        });

        describe('"_validate" method check', function () {
            var successParams;

            beforeEach(function () {
                successParams = {
                    options: {
                        product_container: '.container',
                        product_link: '.link',
                        footerSelector: '.footer'
                    },
                    pagesCount: 2,
                    html: '<div class="container"></div><div class="link"></div><div class="footer"></div>'
                };
            });

            afterEach(function () {
                successParams = {};
            });

            it('success validate', function () {
                fixture.append(successParams.html);
                widget.options = successParams.options;
                widget.pagesCount = successParams.pagesCount;

                expect(widget._validate(widget.options)).toBeTruthy();
            });

            it('"Specify "Products Group" DOM selector" error validate', function () {
                fixture.append(successParams.html);
                widget.options = _.extend(successParams.options, {product_container: ''});
                widget.pagesCount = successParams.pagesCount;

                expect(widget._validate(widget.options)).toBeFalsy();
            });

            it('"Specify "Product Link" DOM selector" error validate', function () {
                fixture.append(successParams.html);
                widget.options = _.extend(successParams.options, {product_link: ''});
                widget.pagesCount = successParams.pagesCount;

                expect(widget._validate(widget.options)).toBeFalsy();
            });

            it('"Specify "Footer Selector" DOM selector" error validate', function () {
                fixture.append(successParams.html);
                widget.options = _.extend(successParams.options, {footerSelector: ''});
                widget.pagesCount = successParams.pagesCount;

                expect(widget._validate(widget.options)).toBeFalsy();
            });

            it('"pagesCount <= 1" error validate', function () {
                fixture.append(successParams.html);
                widget.options = successParams.options;
                widget.pagesCount = 1;

                expect(widget._validate(widget.options)).toBeFalsy();
            });

        });

        describe('"_externalAfterAjax" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_externalAfterAjax');
                widget._externalAfterAjax();
                expect(widget._externalAfterAjax).toHaveBeenCalled();
            });
        });

        describe('"beforeInsertProductBlock" method check', function () {
            it('calling method', function(){
                spyOn(widget, 'beforeInsertProductBlock');
                widget.beforeInsertProductBlock();
                expect(widget.beforeInsertProductBlock).toHaveBeenCalled();
            });
        });

        describe('"doAjax" method check', function () {
            it('calling method', function(){
                spyOn(widget, 'doAjax');
                widget.doAjax();
                expect(widget.doAjax).toHaveBeenCalled();
            });
        });

        describe('"preprocessRawAjaxResponse" method check', function () {
            it('calling method', function(){
                spyOn(widget, 'preprocessRawAjaxResponse');
                widget.preprocessRawAjaxResponse();
                expect(widget.preprocessRawAjaxResponse).toHaveBeenCalled();
            });
        });

        describe('"handleUnexpectedResponse" method check', function () {
            it('calling method', function(){
                spyOn(widget, 'handleUnexpectedResponse');
                widget.handleUnexpectedResponse();
                expect(widget.handleUnexpectedResponse).toHaveBeenCalled();
            });
        });

        describe('"_initPagesCount" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_initPagesCount');
                widget._initPagesCount();
                expect(widget._initPagesCount).toHaveBeenCalled();
            });
        });

        describe('"changeType" method check', function () {
            var type;

            beforeEach(function () {
                type = 'button';
                widget.isReinitialized = false;

                spyOn(widget, 'changeType');
                widget.changeType(type);
            });

            it('returns if isReinitialized is true', function () {
                widget.isReinitialized = true;
                expect(widget.isReinitialized).toEqual(true);
            });

            it('proseed with type auto', function () {
                type = 'auto';
                widget.changeType(type);
                expect(widget.changeType).toHaveBeenCalledWith(type);
            });

            it('proseed with type button', function () {
                expect(widget.changeType).toHaveBeenCalledWith(type);
            });
        });

        describe('"_preloadPages" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_preloadPages');
                widget._preloadPages();
                expect(widget._preloadPages).toHaveBeenCalled();
            });
        });

        describe('"_getCurrentPage" method check', function () {
            beforeEach(function () {
                widget.pagesCount = 5;
                widget.options.current_page = 3;
            });

            it('calling method', function(){
                spyOn(widget, '_getCurrentPage');
                widget._getCurrentPage();
                expect(widget._getCurrentPage).toHaveBeenCalled();
            });

            it('returns current_page when currentPage < this.pagesCount', function () {
                expect(widget._getCurrentPage()).toEqual(widget.options.current_page);
            });

            it('returns pagesCount when currentPage > this.pagesCount', function () {
                widget.options.current_page = 6;

                expect(widget._getCurrentPage()).toEqual(widget.pagesCount);
            });
        });

        describe('"_preloadPageAfter" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_preloadPageAfter');
                widget._preloadPageAfter();
                expect(widget._preloadPageAfter).toHaveBeenCalled();
            });
        });

        describe('"_preloadPageBefore" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_preloadPageBefore');
                widget._preloadPageBefore();
                expect(widget._preloadPageBefore).toHaveBeenCalled();
            });
        });

        describe('"_stop" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_stop');
                widget._stop();
                expect(widget._stop).toHaveBeenCalled();
            });
        });

        describe('"_getAdditionalBlockHeight" method check', function () {
            var heights = {
                    blockAfterProducts: 110,
                    pageFooter: 220,
                    pageBottom: 330
                },
                selectors = {
                    blockAfterProducts: '.main .products ~ .block-static-block',
                    pageFooter: '.page-footer',
                    pageBottom: '.page-bottom'
                },
                elements = {
                    blockAfterProducts: '<div class="main"><div class="products"></div>' +
                        '<div class="block-static-block" style="height: ' + heights.blockAfterProducts + 'px">' +
                        '</div></div>',
                    pageFooter: '<div class="page-footer" style="height: ' + heights.pageFooter + 'px"></div>',
                    pageBottom: '<div class="page-bottom" style="height: ' + heights.pageBottom + 'px"></div>'
                };

            beforeEach(function () {
                widget.options.footerSelector = null;
                widget.additionalHeight = null;
            });

            it('calling method', function(){
                spyOn(widget, '_getAdditionalBlockHeight');
                widget._getAdditionalBlockHeight();

                expect(widget._getAdditionalBlockHeight).toHaveBeenCalled();
            });

            it('block "blockAfterProducts" exist and return its height', function () {
                fixture.append(elements.blockAfterProducts);

                expect(widget._getAdditionalBlockHeight()).toEqual(heights.blockAfterProducts);
            });

            it('block "pageFooter" exist and return its height', function () {
                fixture.append(elements.pageFooter);
                widget.options.footerSelector = selectors.pageFooter;

                expect(widget._getAdditionalBlockHeight()).toEqual(heights.pageFooter);
            });

            it('block "pageBottom" exist and return its height', function () {
                fixture.append(elements.pageBottom);

                expect(widget._getAdditionalBlockHeight()).toEqual(heights.pageBottom);
            });

            it('return all elements height', function () {
                widget.options.footerSelector = selectors.pageFooter;

                $.each(elements, function(key, value) {
                    fixture.append(value);
                });

                expect(widget._getAdditionalBlockHeight()).toEqual(heights.pageBottom + heights.pageFooter + heights.blockAfterProducts);
            });
        });

        describe('"_initPaginator" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_initPaginator');
                widget._initPaginator();

                expect(widget._initPaginator).toHaveBeenCalled();
            });

            it('return when disabled', function(){
                widget.disabled = 1;

                expect(widget._initPaginator()).toBeUndefined();
            });
        });

        describe('"_isScrolledBack" method check', function () {
            var returnValue = true;

            it('calling method', function(){
                spyOn(widget, '_isScrolledBack');
                widget._isScrolledBack();

                expect(widget._isScrolledBack).toHaveBeenCalled();
            });

            it('returns isScrolledBack value', function(){
                widget.isScrolledBack = returnValue;

                expect(widget._isScrolledBack()).toEqual(returnValue);
            });
        });

        describe('"_calculateCurrentScrollPage" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_calculateCurrentScrollPage');
                widget._calculateCurrentScrollPage();

                expect(widget._calculateCurrentScrollPage).toHaveBeenCalled();
            });
        });

        describe('"_updateUrlAndCurrentPage" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_updateUrlAndCurrentPage');
                widget._updateUrlAndCurrentPage();

                expect(widget._updateUrlAndCurrentPage).toHaveBeenCalled();
            });
        });

        describe('"_loadFollowing" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_loadFollowing');
                widget._loadFollowing();

                expect(widget._loadFollowing).toHaveBeenCalled();
            });
        });

        describe('"showFollowing" method check', function () {
            it('calling method', function(){
                spyOn(widget, 'showFollowing');
                widget.showFollowing();

                expect(widget.showFollowing).toHaveBeenCalled();
            });
        });

        describe('"_afterShowFollowing" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_afterShowFollowing');
                widget._afterShowFollowing();

                expect(widget._afterShowFollowing).toHaveBeenCalled();
            });
        });

        describe('"_loadPrevious" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_loadPrevious');
                widget._loadPrevious();

                expect(widget._loadPrevious).toHaveBeenCalled();
            });
        });

        describe('"showPrevious" method check', function () {
            it('calling method', function(){
                spyOn(widget, 'showPrevious');
                widget.showPrevious();

                expect(widget.showPrevious).toHaveBeenCalled();
            });
        });

        describe('"_afterShowPrevious" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_afterShowPrevious');
                widget._afterShowPrevious();

                expect(widget._afterShowPrevious).toHaveBeenCalled();
            });
        });

        describe('"_createLoading" method check', function () {
            it('calling method', function(){
                spyOn(widget, '_createLoading');
                widget._createLoading();

                expect(widget._createLoading).toHaveBeenCalled();
            });
        });

        describe('"_generateButton" method check', function () {
            var pagesLoaded,
                positions = ['before', 'after'],
                pageFirst = 1,
                pagesCount = 9;

            beforeEach(function () {
                widget.pagesCount = pagesCount;
                widget.type = 'button';
            });

            it('calling method', function(){
                spyOn(widget, '_generateButton');
                widget._generateButton();

                expect(widget._generateButton).toHaveBeenCalled();
            });

            it('return if type is not a "button"', function(){
                widget.type = 'auto';

                expect(widget._generateButton()).toBeUndefined();
            });

            it('return if first page is loaded/exist', function(){
                widget.currentPage = pageFirst;
                widget.pagesLoaded = [pageFirst, pageFirst + 1, pageFirst + 2];

                expect(widget._generateButton(positions[0])).toBeUndefined();
            });

            it('return if last page is loaded/exist', function(){
                widget.currentPage = pagesCount;
                widget.pagesLoaded = [pagesCount - 2, pagesCount - 1, pagesCount];

                expect(widget._generateButton(positions[1])).toBeUndefined();
            });




        });
    });
});
