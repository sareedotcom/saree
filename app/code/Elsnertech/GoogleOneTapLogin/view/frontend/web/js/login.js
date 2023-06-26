define([
    "jquery"
], function($){
    "use strict";
   return  {
        ajaxCall: function (url, credentialResponse) {

            jQuery.ajax
            ({
                url: url,
                type: "POST",
                showLoader: true,
                async: false,
                dataType: "json",
                data:credentialResponse,
                success: function(response)
                 {
                   setInterval('location.reload()', 3000);
                },
                
            });

        },

        getAttribute: function(url, credentialResponse){
            this.ajaxCall(url, credentialResponse)
        },
   }
});
