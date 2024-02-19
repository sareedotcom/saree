define(["jquery"], function($) {
    "use strict";
    jQuery(".exportStopProcess").click(function () {
        var x = setInterval(function () {
            jQuery("body").trigger("processStop");
            clearInterval(x);
        }, 1000);
    });
  });