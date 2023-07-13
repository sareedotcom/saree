
define([
    'jquery',
    'Magento_ProductVideo/js/load-player'
], function ($) {
    'use strict';

    if (typeof($.mage.videoVimeo.prototype.options.mtConfig) != 'undefined') {
        return;
    }

    var VimeoPlayer = null,
        useFroogaloopApi = null;

    $.widget('mage.videoVimeo', $.mage.videoVimeo, {
        options: {
            mtConfig: {
            }
        },

        _create: function () {
            if (useFroogaloopApi === null) {
                useFroogaloopApi = !(typeof(window.$f) == 'undefined');
            }

            if (!useFroogaloopApi) {
                if (VimeoPlayer == null) {
                    VimeoPlayer = require('vimeoPlayer');
                }
                window.$f = this.getPlayer;
            }

            this._super();
        },

        getPlayer: function (element) {
            var id = element.id,
                player = new VimeoPlayer(element);

            player.addEvent || (player.addEvent = function (event, fnc) {});
            player.ready().then(function() {
                $('#' + id).closest('.fotorama__stage__frame').addClass('fotorama__product-video--loaded');
            });

            return player;
        },

        play: function () {
            if (useFroogaloopApi) {
                this._player.api('play');
            } else {
                this._player.play().catch(function(error) {
                    console.error('Error playing the video:', error.name);
                });
            }
            this._playing = true;
        },

        pause: function () {
            if (useFroogaloopApi) {
                this._player.api('pause');
            } else {
                this._player.pause().catch(function(error) {
                    console.error('Error pausing the video:', error.name);
                });
            }
            this._playing = false;
        },

        stop: function () {
            if (useFroogaloopApi) {
                this._player.api('unload');
            } else {
                this._player.unload().catch(function(error) {
                    console.error('Error unloading the video:', error.name);
                });                
            }
            this._playing = false;
        }
    });
});
