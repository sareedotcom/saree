define(
    [
        'uiComponent'
    ],
    function (Component) {
        "use strict";
        return function (placeOrder) {
            return placeOrder.extend({
    
                syncButtonState: function () {
                    this._super();
                    jQuery("#my_bu").remove();
                    jQuery(".order-review-form .actions-toolbar .primary").show();
                    
                },
            });
        }
    });
    