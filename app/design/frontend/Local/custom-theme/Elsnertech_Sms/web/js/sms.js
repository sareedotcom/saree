/**
 * @author Elsner Team
 * @copyright Copyright © Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_SMS
 */

define(
  [
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal'
  ],
 function ($) {
    'use strict';
    return  {
        options : {},
        hideElement : '{"display":"none"}',
        showElement : '{"display":"block"}',
        initCall: function (url , form) {
          let self = this;
          let element = self.options.mainElement;
          let ajaxLoginSelector = self.options.ajaxLoginSelector;
          let registerlink = self.options.registerlink;
          $(element).on('click', ajaxLoginSelector, function (e) {
              e.preventDefault();
              self.openLoginForm();
          });
          $(element).on('click', registerlink, function (e) {
              e.preventDefault();
              self.openRegisterForm();
          });
          $(element).on('click', self.options.Close, function (e) {
              e.preventDefault();
              self.closePopup();
          });

          let customerloginsubmit = '#customerloginsubmit';
          $(element).on('click', customerloginsubmit, function (e) {
              e.preventDefault();
              $("#customer-login-please-wait").css({"display":"block"});
               $(".cuslogsub").css({"display":"none"});
             //  $("#loginsubmit").css({"display":"none"});
             $(".emailpasswrong").css({"display":"none"});
              var url = $('#customer-login-form').attr('action');
              let datavalue =   $('#customer-login-form').serialize();
              self.customerLoginSubmit(url, datavalue);
          });
        },

        customerLoginSubmit: function (url , dataValue) {
            $.ajax({
                    url: url,
                    type: 'POST',
                    data: dataValue,
                    xhrFields: {
                      withCredentials: true
                    },
                create: function(response) {
                    var t = response.transport;
                    t.setRequestHeader = t.setRequestHeader.wrap(function(original, k, v) {
                        if (/^(accept|accept-language|content-language|cookie|access-control-allow-origin|access-control-allow-headers|access-control-allow-credentials)$/i.test(k))
                            return original(k, v);
                        if (/^content-type$/i.test(k) &&
                            /^(application\/x-www-form-urlencoded|multipart\/form-data|text\/plain)(;.+)?$/i.test(v))
                            return original(k, v);
                        return;
                    });
                },
                success: function (response) {
                    if(response.error){
                      $(".emailpasswrong").css({"display":"block"});
                      $(".emailpasswrong span").text(response.message);
                      $('#customer-login-please-wait').css({"display":"none"});
                      $(".cuslogsub").css({"display":"block"});
                      $("#loginsubmit").css({"display":"block"});
                      return;
                    }

                    if (response.redirect) {
                      document.location = response.redirect+'customer/account';
                      return;
                    }
                }
            });
        }
    }
});