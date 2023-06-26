define([
    'jquery',
    'Elsner_CheckOrder/js/modules/templates',
    'Elsner_CheckOrder/js/modules/translation'
], function ($, templates, translationModule) {
    var dataMapper = {
        mapOrder: function(data){
            var orderDataArray = [];
            
            if(data == null)
            {
                var row = {
                    "row": templates.applyTemplate('error', translationModule.getTranslation('checkorder_error'), translationModule.getTranslation('checkorder_error_not_found'))
                };                
                orderDataArray.push(row);
            }
            else
            {
                orderDataArray = $.map(data, function(element, index){
                    var row = {
                        "row": templates.applyTemplate(index, element.label, element.value)
                    };
                    return row;
                });
            }

            return orderDataArray;
        }
    };
    
    return dataMapper;
});