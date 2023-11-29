define([
    "jquery", "Magento_Ui/js/modal/modal"
], function($){
    var PromotionModel = {
        initModal: function(config, element) {
            $target = $(config.target);
            $target.modal();
            $element = $(element);
            $element.click(function() {
                $target.modal('openModal');
            });
        }
    };

    return {
        'promotion-modal': PromotionModel.initModal
    };
}
);