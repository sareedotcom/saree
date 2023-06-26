define([
    'jquery',
    'Elsner_CheckOrder/js/models/checkorder-model',
    'Elsner_CheckOrder/js/modules/data-mapper'
], function ($, checkOrderModel, dataMapperModule) {
    var $node = null;

    var ui = {
        init: function (node) {
            $node = $(node);
            ui.initListeners();
        },
        initListeners: function () {
            $node.find('.show-hide').on('click', function () {
                ui.showHide();
            });

            $node.find('form').on('submit', function (event) {
                ui.submitForm();
                event.preventDefault();
            });
            
            $node.find('.checkorder-results button.back').on('click', function () {
                $node.find('.block-content').show();
                $node.find('.checkorder-results').hide();
            });
            
            $(document).mouseup(function (e)
            {
                if (!$node.is(e.target) && $node.has(e.target).length === 0)
                {
                    $node.removeClass('opened');
                }
            });         
        },
        showHide: function () {
            if ($node.hasClass('opened'))
            {
                $node.removeClass('opened');
            }
            else
            {
                $node.addClass('opened');
            }
        },
        submitForm: function () {
            var $form = $node.find('form');
            
            if($form.valid())
            {
                $.ajax({
                    type: $form.attr('method'),
                    url: $form.attr('action'),
                    data: $form.serialize(),
                    showLoader: true,
                    success: function (data) {
                        checkOrderModel.orderData(dataMapperModule.mapOrder(data));
                        $node.find('.block-content').hide();
                        $node.find('.checkorder-results').show();
                    }
                });                
            }                                   
        }
    };

    return ui;
});