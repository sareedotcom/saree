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

          let loginWithEmail = 'input[name=loginwithemail]';
          $(element).on('change', loginWithEmail, function (e) {
              e.preventDefault();
              if ($(this).is(":checked")) {
                $(".ajaxloginphtml").show();
              } else {
                $(".ajaxloginphtml").hide();
              }
          });

          let authenticationWrapperButton = '.authentication-wrapper button.action-auth-toggle';
          $(element).on('click', authenticationWrapperButton, function (e) {
              e.preventDefault();
              $(".checkout-index-index .ajax-login-form").css({"display":"block"});
              $(".checkout-index-index .modal-custom.authentication-dropdown.custom-slide").css({"display":"none"});
          });

          let checkoutGotoregister = 'a.gotoregister';
          $(element).on('click', checkoutGotoregister, function (e) {
              e.preventDefault();
              $(".checkout-index-index .ajax-login-form").css({"display":"none"});
              $(".checkout-index-index .ajax-register-form").css({"display":"block"});
          });

          let authenticationWrapperButtonClosediv = '.checkout-index-index .modal .closediv';
          $(element).on('click', authenticationWrapperButtonClosediv, function (e) {
              e.preventDefault();
              $(".checkout-index-index .ajax-login-form").css({"display":"none"});
              $(".checkout-index-index .ajax-register-form").css({"display":"none"});
              $(".checkout-index-index .ajax-forgot-form").css({"display":"none"});
              $(".checkout-index-index .regcontollererror").css({"display":"none"});
          });
          let loginverifyotpCheckout = '.checkout-index-index .loginverifyotp';
          $(element).on('click', loginverifyotpCheckout, function (e) {
              e.preventDefault();
              var mobile = $("#loginotpmob").val();
              var countrycode = $("#country-code-login").val();
              var otp = $("#logintotp").val();
              var url = $(".loginotp-verify-url").val();
              $("#login-verify-please-wait").css({"display":"block"});
              $(".checkloginotperror").css({"display":"none"});
              $(".loginverifyotp").css({"display":"none"});
              let datavalue =  {mobile:mobile,otp:otp,countrycode:countrycode};
              self.logInveriFyotp(url, datavalue);
          });

          let gotoregister = '.gotoregister';
          $(element).on('click', gotoregister, function (e) {
              e.preventDefault();
              console.log(gotoregister);
              $(".loginwithemail .closediv").click();
              //$(".registerlink").click();
              self.openRegisterForm();
              $(".mobileExist").css({"display":"none"});
              $(".ajax-register-form #mobileget").val('');
          });

          let gotologin = '.gotologin';
          $(element).on('click', gotologin, function (e) {
              e.preventDefault();
              $(".ajax-register-form .closediv").click();
              $(".ajaxlogin-login").click();
              $(".loginsendotperror").hide();
              $(".loginwithemail #loginotpmob").val('');
          });

          let forgotlinking = '.forgotlinking';
          $(element).on('click', forgotlinking, function (e) {
              e.preventDefault();
              $(".ajax-login-form").css({"display":"none"});
              $(".ajax-register-form").css({"display":"none"});
              $(".ajax-forgot-form").css({"display":"block"});
              $(".forgotmobileget").css({"display":"block"});
              $(".forgototpverify").css({"display":"none"});
              $(".setnewpass").css({"display":"none"});
              $(".modal input[type='text']").val("");
              $(".modal input[type='password']").val("");
          });

          let forgotWithEmail = 'input[name=forgotwithemail]';
          $(element).on('change', forgotWithEmail, function (e) {
              e.preventDefault();
              if ($(this).is(":checked")) {
                $("#ajaxforgot-forgot-window").show();
              } else {
                $("#ajaxforgot-forgot-window").hide();
              }
          });

          let forgotemaillinking = '#forgotsubmit';
          $(element).on('click', forgotemaillinking, function (e) {
              e.preventDefault();
              $(".ajax-login-form").css({"display":"none"});
              $(".ajax-register-form").css({"display":"none"});
              $(".ajax-forgot-form").css({"display":"block"});
              $(".forgototpverify").css({"display":"none"});
              $(".setnewpass").css({"display":"none"});
              var url = $('#email-forgot-form').attr('action');
              var email = $('#email-forgot').val();
              if(self.isBlank(email) == false){
                $(".forgotemailrequird").css({"display":"block"});
                $(".forgotemailsuccess").css({"display":"none"});
                $(".forgotemailerror").css({"display":"none"});
                return false;
              }
              var dataValue = {email:email};
              self.emailForgot(url, dataValue);
          });

          let loginsubmit = '#loginsubmit';
          $(element).on('click', loginsubmit, function (e) {
              e.preventDefault();
              $("#login-please-wait").css({"display":"block"});
              $("#loginsubmit").css({"display":"none"});
              $(".emailpasswrong").css({"display":"none"});
              var url = $('#mobile-login-form').attr('action');
              let datavalue =  $('#mobile-login-form').serialize();
              self.loginSubmit(url, datavalue);
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
          let Registerformsubmit = '.Registerformsubmit';
          $(element).on('click', Registerformsubmit, function (e) {
              e.preventDefault();
              $("#reg-submit-please-wait").css({"display":"block"});
              $(".Registerformsubmit").css({"display":"none"});
              $(".regcontollererror").css({"display":"none"});
              var url = $('#ajaxlogin-create-form').attr('action');
              var dataValue = $('#ajaxlogin-create-form').serialize();
              self.Registerformsubmit(url, dataValue);
          });
          let submit = '.submit';
          $(element).on('click', submit, function (e) {
              $("#reg-submit-please-wait").css({"display":"block"});
              $(".customer-progress-indicator").css({"display":"block"});
              $("#customer-register-wait").css({"display":"block"});
              $(".Registerformsubmit").css({"display":"none"});
              $(".regcontollererror").css({"display":"none"});
              var url = $('#ajaxlogin-create-form').attr('action');
              var dataValue = $('.form-create-account').serialize();
              self.submit(url, dataValue);
          });
          let verifyotp = '.verifyotp';
            $(element).on('click', verifyotp, function (e) {
              var otp =  $("#otp").val();
              console.log(otp);
              var mobile = $("#mobileget").val();
              var countrycode = $("#country-code-register").val();
              $(".blankerror").css({"display":"none"});
              if(self.isBlank(otp) == false){
                $(".blankerror").css({"display":"block"});
                return false;
              }
              $(".checkotperror").css({"display":"none"});
              $("#reg-otp-verify-please-wait").css({"display":"block"});
              $(".verifyotp").css({"display":"none"});
              var url =  $(".checkotpurl").val();
              var dataValue = {otp:otp,mobile:mobile,countrycode:countrycode};
              self.verifyotp(url, dataValue);
          });

          $(".create-account-resend-otp").click(function(e) {
            $(".regi-sendotp").trigger('click');
            $("#reg-sms-please-wait").css({"display":"none"});
          });
          let mobileverifyotp = '.mobileverifyotp';
            $(element).on('click', mobileverifyotp, function (e) {
              var otp =  $("#mobile-otp").val();
              var mobile = $("#mobile-mobileget").val();
              $(".blankotperror").css({"display":"none"});

              if(self.isBlank(otp) == false){
                $(".blankotperror").css({"display":"block"});
                return false;
              }

              $(".checkotperror").css({"display":"none"});
              $("#reg-otp-verify-please-wait").css({"display":"block"});
              $(".verifyotp").css({"display":"none"});
              $(this).prop('disabled',true);
              var url =  $(".checkotpurl").val();
              var dataValue = {otp:otp,mobile:mobile};
              self.mobileverifyotp(url, dataValue);
          });
          let sendotp = '.sendotp';
            $(element).on('click', sendotp, function (e) {
              var mobile = $("#mobileget").val();
              var countrycode = $("#country-code-register").val();
              $(".blankerror").css({"display":"none"});
              $(".mobileNotValid").css({"display":"none"});
              $(".mobileotpsenderror").css({"display":"none"});
              $(".mobileExist").css({"display":"none"});
              if(!mobile){
                $(".blankerror").css({"display":"block"});
                return false;
              }
              if(self.validateMobile(mobile) == false){
                $(".mobileNotValid").css({"display":"block"});
                return false;
              }

              $(".sendotp").css({"display":"none"});
              $("#reg-sms-please-wait").css({"display":"block"});
              var url = $(".setdotpurl").val();
              var dataValue = {mobile:mobile,countrycode:countrycode};
              self.sendotp(url, dataValue);
          });
          let registersendotp = '.regi-sendotp';
            $(element).on('click', registersendotp, function (e) {
              var mobile = $("#mobile-mobileget").val();
              $(".blankerror").css({"display":"none"});
              $(".mobileNotValid").css({"display":"none"});
              $(".mobileotpsenderror").css({"display":"none"});
              $(".mobileExist").css({"display":"none"});
              $(".resend").css({"display":"none"});
              $(".sending").css({"display":"block"});
              if(!mobile){
                $(".blankerror").css({"display":"block"});
                return false;
              }
              if(self.validateMobile(mobile) == false){
                $(".mobileNotValid").css({"display":"block"});
                return false;
              }
              $(".sendotp").css({"display":"none"});
              $("#reg-sms-please-wait").css({"display":"block"});
              $(this).prop('disabled',true);
              var url =  $(".setdotpurl").val();
              var dataValue = {mobile:mobile};
              self.registersendotp(url, dataValue);
          });
          let forgotsendotp = '.forgotsendotp';
            $(element).on('click', forgotsendotp, function (e) {
              var mobile = $("#forgotmob").val();
              var countrycode = $("#country-code-forgot").val();
              $(".blankerror").css({"display":"none"});
              if(self.isBlank(mobile)== false){
                $(".blankerror").css({"display":"block"});
                return false;
              }
              var validate = self.validateMobile(mobile);
              $(".forgotBlankMobileerror").css({"display":"none"});
              if(validate != true){
                $(".forgotBlankMobileerror").css({"display":"block"});
                return false;
              }
              $(".forgotmobileerror").css({"display":"none"});
              $(".forgotsendotp").css({"display":"none"});
              $("#forgot-sms-please-wait").css({"display":"block"});

              var url = $(".forgot-otp-url").val();
              var dataValue = {mobile:mobile,countrycode:countrycode};
              self.forgotsendotp(url, dataValue);
          });
          let forgotverifyotp = '.forgotverifyotp';
            $(element).on('click', forgotverifyotp, function (e) {
              var mobile = $("#forgotmob").val();
              var countrycode = $("#country-code-forgot").val();
              var forgototp = $("#forgototp").val();
              var myaccountlink = $(".forgotAccountlink").val();
              $(".blankerror").css({"display":"none"});
              if(self.isBlank(forgototp)== false){
                $(".blankerror").css({"display":"block"});
                return false;
              }
              $(".forgotverifyotp").css({"display":"none"});
              $("#forgot-sms-verify-please-wait").css({"display":"block"});
              var url = $(".forgotcheckotpurl").val();
              var dataValue ={mobile:mobile,otp:forgototp,countrycode:countrycode};
              self.forgotverifyotp(url, dataValue);
          });
          let updatepassbtn = '.updatepassbtn';
            $(element).on('click', updatepassbtn, function (e) {
              var mobile = $("#forgotmob").val();
              var countrycode = $("#country-code-forgot").val();
              var forgototp = $("#forgototp").val();
              var newpassotp = $("#newpassotp").val();
              var newpassconrmotp = $("#newpassconrmotp").val();
              var accountlinkotp = $(".accountlinkotp").val();
              $(".blankerror").css({"display":"none"});
              $(".resetpassvalidation").css({"display":"none"});

              if(self.isBlank(newpassotp) == false || self.isBlank(newpassconrmotp) == false ){
                $(".blankerror").css({"display":"block"});
                return false;
              }
              if(self.forgotPassValidation(newpassotp,newpassconrmotp) == false){
                $(".resetpassvalidation").css({"display":"block"});
                return false;
              }

              $(".passmatcherror").css({"display":"none"});
              $(".updatepassbtn").css({"display":"none"});
              $("#set-new-pass-please-wait").css({"display":"block"});
              var url = $(".updatepassotp").val();
              var dataValue = {mobile:mobile,otp:forgototp,newpass:newpassotp,confirmpass:newpassconrmotp,countrycode:countrycode};
              if(newpassotp == newpassconrmotp){
                self.updatepassbtn(url, dataValue);
              }
              else{
                $(".passmatcherror").css({"display":"block"});
                $(".updatepassbtn").css({"display":"block"});
                $("#set-new-pass-please-wait").css({"display":"none"});
              }
          });
          let loginotpmobbtn = '.loginotpmobbtn';
            $(element).on('click', loginotpmobbtn, function (e) {
              var mobile = $("#loginotpmob").val();
              var countrycode = $("#country-code-login").val();
              var validate = self.validateMobile(mobile);
              $(".loginotpmobbtnerror").css({"display":"none"});
              $(".loginsendotperror").css({"display":"none"});
              if(validate != true){
                $(".loginotpmobbtnerror").css({"display":"block"});
                return false;
              }
              $("#login-sms-please-wait").css({"display":"block"});
              $(".loginotpmobbtn").css({"display":"none"});
              $(".updatepasssuccess").css({"display":"none"})  
              var url = $(".loginotp-otp-url").val();
              var dataValue ={mobile:mobile,countrycode:countrycode};
              self.loginotpmobbtn(url, dataValue);
          });
          let loginverifyotp = '.loginverifyotp';
            $(element).on('click', loginverifyotp, function (e) {
              var mobile = $("#loginotpmob").val();
              var countrycode = $("#country-code-login").val();
              var otp = $("#logintotp").val();
              var urlaccount = $(".customeraccount").val();
              $("#login-verify-please-wait").css({"display":"block"});
              $(".checkloginotperror").css({"display":"none"});
              $(".loginverifyotp").css({"display":"none"});
              var url = $(".loginotp-verify-url").val();
              var dataValue = {mobile:mobile,otp:otp,countrycode:countrycode};
              self.loginverifyotp(url, dataValue,urlaccount);
          });
          let mobnumber = '.mobnumber';
          $(element).on('keydown', mobnumber, function (e) {
          // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: Ctrl+C
                (e.keyCode == 67 && e.ctrlKey === true) ||
                 // Allow: Ctrl+X
                (e.keyCode == 88 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                     // let it happen, don't do anything
                     return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))                  {
                e.preventDefault();
            }
          });
        },

        openLoginForm: function () {
          let self = this;
          $(self.options.ajaxForgotForm).css({"display":"none"});
          $(self.options.ajaxRegisterForm).css({"display":"none"});
          $(self.options.ajaxLoginForm).css({"display":"block"});
          $(self.options.modalForm).css({"display":"block"});

        },

        openRegisterForm: function () {
          let self = this;
          $(self.options.ajaxForgotForm).css({"display":"none"});
          $(self.options.ajaxLoginForm).css({"display":"none"});
          $(self.options.ajaxRegisterForm).css({"display":"block"});
        },

        closePopup: function () {
          let self = this;
          $(self.options.ajaxForgotForm).css({"display":"none"});
          $(self.options.ajaxLoginForm).css({"display":"none"});
          $(self.options.ajaxRegisterForm).css({"display":"none"});
          $(self.options.regContollerError).css({"display":"none"});
          $("body").removeClass("_has-modal-custom");
          $("body").removeClass("_has-auth-shown");
        },

        loginSubmit: function (url , dataValue) {
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
                success: function(response) {
                  if(response.error){
                    jQuery(".emailpasswrong").css('display','block');
                    jQuery(".emailpasswrong span").text(response.message);
                    jQuery('#login-please-wait').css('display','none');
                    jQuery("#loginsubmit").css('display','block');
                    return;
                  }
      
                  if (response.redirect) {
                    document.location = response.redirect;
                    return;
                  }
                }
            });
        },

        logInveriFyotp: function (url , dataValue) {
            
            $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: dataValue,
                complete: function (response) {  
                    var responce = response
                      $(".sendotp").css({"display":"block"});
                      $("#reg-sms-please-wait").css({"display":"none"});
                    if(responce == 'true'){
                      $("#createmobile").val(dataValue.countrycode+dataValue.mobile);
                      $(".mobileget").css({"display":"none"});
                      $(".otpverify").css({"display":"block"});
                    }else if(responce == 'exist'){
                      $(".mobileExist").css({"display":"block"});
                    }else{
                      $(".mobileotpsenderror").css({"display":"block"});
                    }
                },
                error: function (xhr, status, errorThrown) {
                    $(".sendotp").css({"display":"block"});
                    $("#reg-sms-please-wait").css({"display":"none"});
                }
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
                complete: function (response) {  
                    var responce = response
                    if(response.error){
                      $(".emailpasswrong").css({"display":"block"});
                      $(".emailpasswrong span").text(response.message);
                      $('#customer-login-please-wait').css({"display":"none"});
                      $(".cuslogsub").css({"display":"block"});
                      $("#loginsubmit").css({"display":"block"});
                      return;
                    }

                    if (response.redirect) {
                      document.location = response.redirect;
                      return;
                    }
                }
            });
        },

        Registerformsubmit: function(url,dataValue){
          $.ajax({
                  url: url,
                  type: 'post',
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
                success: function(transport) {
                  $("#reg-submit-please-wait").css({"display":"none"});
                  $(".Registerformsubmit").css({"display":"block"});
                  if (transport.success == "true") {
                      document.location = transport.redirect;
                      return;
                  }
                  if (transport.success == "false") {
                    $(".regcontollererror span").text(transport.message);
                    $(".regcontollererror").css({"display":"block"});
                  }
                },
                error: function() {
                  $("#reg-submit-please-wait").css({"display":"none"});
                  $(".Registerformsubmit").css({"display":"block"});
                }
            });
        },

        submit: function(url , dataValue){
          $.ajax({
                  url: url,
                  type: 'post',
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
                success: function(transport) {
                  $("#reg-submit-please-wait").css({"display":"none"});
                  $(".Registerformsubmit").css({"display":"block"});
                  $(".customer-progress-indicator").css({"display":"none"});
                  $("#customer-register-wait").css({"display":"none"});

                  if (transport.success == "true") {
                                document.location = transport.redirect;
                                return;
                            }
                  if (transport.success == "false") {
                    $(".messages").css("color","red");
                    $(".messages").text(transport.message);
                    $(".regcontollererror span").text(transport.message);
                    $(".regcontollererror").css({"display":"block"});
                  }
                },  
              error: function() {
                $("#reg-submit-please-wait").css({"display":"none"});
                $(".Registerformsubmit").css({"display":"block"});
              }
            });
        },

        mobileverifyotp: function(url , dataValue){
          $.ajax({
              url: url,
              type: 'GET',
              data:dataValue,
              success: function(data) {
                $(".verifyotp").css({"display":"block"});
                $("#reg-otp-verify-please-wait").css({"display":"none"});
                if(data == 'true'){
                  $("#createotp").val(otp);
                  $(".otpverify").css({"display":"none"});
                  $(".registraionform").css({"display":"block"});
                  //  $(".submit").prop('disabled', false);
                 }else{
                  $(".checkotperror").css({"display":"block"});
                 }
                $(".blankotperror").css({"display":"none"});
                $('.mobileverifyotp').prop('disabled',false);
              },
              error: function() {
                $("#reg-otp-verify-please-wait").css({"display":"none"});
                $(".verifyotp").css({"display":"block"});
                $(this).prop('disabled',false);
              }
          });

        },

        verifyotp: function (url , dataValue) {          
          $.ajax({
                url: url,
                type: 'GET',
                data:dataValue,
              success: function(data) {
                $(".verifyotp").css({"display":"block"});
                $("#reg-otp-verify-please-wait").css({"display":"none"});
                if(data == 'true'){
                  $("#createotp").val(dataValue.otp);
                    $(".otpverify").css({"display":"none"});
                      $(".registraionform").css({"display":"block"});
                }else{
                  $(".checkotperror").css({"display":"block"});
                }
              },
              error: function() {
                $("#reg-otp-verify-please-wait").css({"display":"none"});
                $(".verifyotp").css({"display":"block"});
              }
          });
        },

        sendotp: function(url , dataValue) {
          $.ajax({
             url: url,
             type:'GET',
             data:dataValue,
             success: function(data) {
                $(".sendotp").css({"display":"block"});
                $("#reg-sms-please-wait").css({"display":"none"});
                if(data == 'true'){
                  $("#createmobile").val(dataValue.countrycode+dataValue.mobile);
                  $(".mobileget").css({"display":"none"});
                  $(".otpverify").css({"display":"block"});
                }else if(data == 'exist'){
                 $(".mobileExist").css({"display":"block"});
                }else{
                  $(".mobileotpsenderror").css({"display":"block"});
                }
              },
              error: function() {
                $(".sendotp").css({"display":"block"});
                $("#reg-sms-please-wait").css({"display":"none"});
             }
          });
        },

        registersendotp: function(url, dataValue) {
          $.ajax({
                url: url,
                type:'GET',
                data:dataValue,
                success: function(data) {
                  $(".sendotp").css({"display":"block"});
                  $("#reg-sms-please-wait").css({"display":"none"});
                  if(data == 'true'){
                    $("#createmobile").val(dataValue.mobile);
                    $(".mobileget").css({"display":"block"});
                    $(".regi-sendotp").css({"display":"none"});
                  document.getElementById("mobile-mobileget").readOnly = true;
                  $(".otpverify").css({"display":"block"});
                  $(".resend").css({"display":"block"});
                  $(".sending").css({"display":"none"});

                  }else if(data == 'exist'){
                    $(".mobileExist").css({"display":"block"});
                  }else{
                    if(data != 'error') {
                      $(".mobileotpsenderror").html(data);
                    }
                    $(".mobileotpsenderror").css({"display":"block"});
                 }
                 $('.regi-sendotp').prop('disabled',false);
                },
                error: function() {
                $(".sendotp").css({"display":"block"});
                $("#reg-sms-please-wait").css({"display":"none"});
                $(this).prop('disabled',false);
                }
            });
        },

        forgotsendotp: function(url , dataValue) {
          $.ajax({
              url: url,
              type:'GET',
              data:dataValue,
              success: function(data) {
                $("#forgot-sms-please-wait").css({"display":"none"});
                if(data == 'true'){
                  $(".forgotmobileget").css({"display":"none"});
                  $(".forgototpverify").css({"display":"block"});
                  $(".forgotsendotp").css({"display":"block"});
                }
                else{
                  $(".forgotmobileerror").css({"display":"block"});
                 $(".forgotsendotp").css({"display":"block"});
                }
              },
              error: function() {
                $("#forgot-sms-please-wait").css({"display":"none"});
              }
          });
        },

        forgotverifyotp: function(url , dataValue){
          $.ajax({
              url: url,
              type:'GET',
              data:dataValue,
              success: function(data) {
                $(".forgotverifyotp").css({"display":"block"});
                $("#forgot-sms-verify-please-wait").css({"display":"none"});
                if(data == 'true'){
                  $(".forgotmobileget").css({"display":"none"});
                  $(".forgototpverify").css({"display":"none"});
                  $(".setnewpass").css({"display":"block"});
                }else{
                  $(".checkforgototperror").css({"display":"block"});
                }
              },
              error: function() {
                $(".forgotverifyotp").css({"display":"block"});
                $("#forgot-sms-verify-please-wait").css({"display":"none"});
              }
            });
        },

        updatepassbtn: function (url , dataValue) {
          $.ajax({
              url: url,
              type:'GET',
              data:dataValue,
              success: function(data) {
                $(".updatepassbtn").css({"display":"block"});
                $("#set-new-pass-please-wait").css({"display":"none"});
                  if(data == 'true'){
                  $(".ajax-forgot-form").css({"display":"none"});
                  $(".ajax-register-form").css({"display":"none"});
                  $(".ajax-login-form").css({"display":"block"});
                  $(".updatepasssuccess").css({"display":"block"});
                 }else{
                  $(".forgotmobileerror").css({"display":"block"});
                 }
               },
              error: function() {
                $(".updatepassbtn").css({"display":"block"});
                $("#set-new-pass-please-wait").css({"display":"none"});
              }
          });
        },

        loginotpmobbtn:function (url , dataValue) {
          $.ajax({
               url: url,
               type:'GET',
               data:dataValue,
               success: function(data) {
                 if(data == 'true'){
                  $(".loginotpmobileget").css({"display":"none"});
                  $(".loginotpverify").css({"display":"block"});
                  $("#login-sms-please-wait").css({"display":"none"});
                  $(".loginotpmobbtn").css({"display":"block"});
                 }
                 else{
                  $(".loginsendotperror").css({"display":"block"});
                  $("#login-sms-please-wait").css({"display":"none"});
                  $(".loginotpmobbtn").css({"display":"block"});;
                 }
               },
              error: function() {
              }
            });
        },

        loginverifyotp: function (url , dataValue,urlaccount) {
          $.ajax({
               url: url,
               type:'GET',
               data:dataValue,
               success: function(data) {
                if(data == 'true'){
                  window.location.href = urlaccount;
                }
                else{
                  $("#login-verify-please-wait").css({"display":"none"});
                  $(".checkloginotperror").css({"display":"block"});
                  $(".loginverifyotp").css({"display":"block"});
                 }
               },
                error: function() {
                $(".loginverifyotp").css({"display":"block"});
                $("#login-verify-please-wait").css({"display":"none"});
                $(".checkloginotperror").css({"display":"block"});
               }
            });
        },

        emailForgot: function (url, dataValue) {
          $.ajax({
            url: url,
            type:'POST',
            data:dataValue,
            success: function(data) {
              if(data){
                $(".forgotemailsuccess").css({"display":"block"});
                $(".forgotemailrequird").css({"display":"none"});
              } else{
                $(".forgotemailerror").css({"display":"block"});
                $(".forgotemailrequird").css({"display":"none"});
              }
            }
         });
        },

        addError: function (element, message) {
          $(element).text(message);
          return false;
        },

        redirectUrl: function (url) {
          document.location = url;
          return false;
        },

        validateMobile: function (mobile) {
          var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
          if (filter.test(mobile)) {
            if(mobile.length >= 10 && mobile.length <= 13){
                 var validate = true;
            } else {
          var validate = false;
              }
            }
          else {
            var validate = false;
            }
          return validate;
        },

        forgotPassValidation: function(pass,passconfirm){
          if(pass.length < 6 || passconfirm.length < 6){
            return false;
          }
        },
        
        isBlank: function(value){
          if(!value)
          {
            return false;
          }
        },
       
        doRegister: function(){
          $(".ajax-register-form").css({"display":"block"});
          $(".ajax-forgot-form").css({"display":"none"});
          $(".ajax-login-form").css({"display":"none"});
        },

        doLogin: function(){
          $(".ajax-forgot-form").css({"display":"none"});
          $(".ajax-register-form").css({"display":"none"});
          $(".ajax-login-form").css({"display":"block"});
        },
        isLodingData: function (type) {
          if (type) {
            $("#login-please-wait").css({"display":"block"});
            $("#loginsubmit").css({"display":"none"});
            $(".emailpasswrong").css({"display":"none"});
          } else {
            $(".emailpasswrong").css({"display":"block"});
            $('#login-please-wait').css({"display":"none"});
            $("#loginsubmit").css({"display":"block"});
          }
        }
    }
});