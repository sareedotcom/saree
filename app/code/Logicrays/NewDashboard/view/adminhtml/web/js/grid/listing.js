define([
    'Magento_Ui/js/grid/listing'
], function (Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Logicrays_NewDashboard/ui/grid/listing'
        },
        getRowClass: function (row) {
            if (row.nearestdispatch.search("red-estimate") >= 0) { 
                return 'red-estimate-row';
            } else if (row.nearestdispatch.search("lightpink-estimate") >= 0) { 
                return 'lightpink-estimate-row';
            } else if (row.nearestdispatch.search("white-estimate-row") >= 0) { 
                return 'white-estimate-row';
            } else {
                return 'default-color';
            }
        }
    });
});
