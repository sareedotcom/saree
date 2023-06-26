/**
 * @author Elsner Team
 * @copyright Copyright © Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_SMS
 */

require(
  [
    'jquery',
    'Elsnertech_Sms/js/sms'
  ],
 function ($, sms) {
 	sms.options = {
 					"mainElement":".page-wrapper",
 					"ajaxLoginSelector":"a.ajaxlogin-login, .action.action-auth-toggle",
 					"registerlink":".registerlink",
 					"Close":".closediv .close",
 					"Close":".closediv .close",
 					"modalForm":"[data-block=authentication]",
          //"sendOtp":"[data-action=register-sendotp]",
 					"regContollerError":".regcontollererror",
 					"ajaxForgotForm":".ajax-forgot-form",
 					"ajaxRegisterForm":".ajax-register-form",
 					"ajaxLoginForm":".ajax-login-form",
          "ajaxLoginForm":".ajax-login-form",
 				  };
    sms.options.sendOtp = {
                          "sendOtpelement":"[data-action=register-sendotp]",
                          "mobileGet":"#mobileget",
                          "countryCodeRegister":"#country-code-register",
                          "setDotPurl":".setdotpurl"
                          };
  	sms.initCall();
});
