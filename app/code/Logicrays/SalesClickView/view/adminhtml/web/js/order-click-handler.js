define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'text!Logicrays_SalesClickView/templates/order-click.html'
    ], function (Column, $, mageTemplate, orderItemTemplate) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        getOrderItems: function (row) { return row[this.index + '_orderItems']; },

        /**
         * Order item table.
         *
         * @param {Object} row
         */
        orderItemTable: function (row) {
            $('.admin__data-grid-wrap').removeClass('admin__data-grid-wrap');
            var modalHtml = mageTemplate(
                orderItemTemplate,
                {
                    entityid: row.entity_id,
                    orderItems: this.getOrderItems(row)
                }
            );
            if (this.getOrderItems(row).length > 1) {
                let currentRow = $("#idscheck"+row.entity_id).closest('tr');
                var colSpan = $(currentRow).find("td").length;
                if ($("#view-"+row.entity_id).length) {
                     $("#view-"+row.entity_id).remove();
                } else {
                    $(".sales-quick-view").remove();
                    $(currentRow).after("<tr class='sales-quick-view' id='view-"+row.entity_id+"'><td colspan='"+colSpan+"'>"+modalHtml+"</td></tr>");
                }
            }
        },

        /**
         * Get field handler per row.
         *
         * @param {Object} row
         * @returns {Function}
         */
        getFieldHandler: function (row) {
            return this.orderItemTable.bind(this, row);
        }
    });
});