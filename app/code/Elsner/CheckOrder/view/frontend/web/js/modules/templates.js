define([
    'underscore',
    'Elsner_CheckOrder/js/modules/translation',
    'jquery'
], function (_, translationModule, $) {
    var templateEngine = {
        applyTemplate: function (type, label, data) {
            var html = "";
            var template = null;
            if (typeof templateEngine.templates[type] !== 'undefined') {
                template = _.template(templateEngine.templates[type]);
            }
            else {
                template = _.template(templateEngine.templates["default"]);
            }

            html = template({label: label, data: data, translationModule: translationModule});
            var id = $('.progress-track').attr('data-id');
            if(id == 0){
                $('.track-image').hide();
                $('.progress-track-error').hide();
                $('.progress-track').show();
                $("#result").text(data);
                 if(data == "processing"){
                    $('.processing').addClass('_active');
                    $('.complete').removeClass('_active');
                    $('.readytoship').removeClass('_active');
                    $('.smoothning').removeClass('_active');
                    $('.procurement').removeClass('_active');
                    $('.opc-progress-bar-item1').addClass('pending_status');
                    $('.opc-progress-bar-item2').removeClass('processing_status');
                    $('.opc-progress-bar-item3').removeClass('procurement_status');
                    $('.opc-progress-bar-item4').removeClass('smoothing_status');
                    $('.opc-progress-bar-item5').removeClass('transit_status');
                }else if(data == "under_procurement"){
                    $('.procurement').addClass('_active');
                    $('.processing').addClass('_active');
                    $('.readytoship').removeClass('_active');
                    $('.smoothning').removeClass('_active');
                    $('.complete').removeClass('_active');
                    $('.opc-progress-bar-item1').addClass('pending_status');
                    $('.opc-progress-bar-item2').addClass('processing_status');
                    $('.opc-progress-bar-item3').removeClass('procurement_status');
                    $('.opc-progress-bar-item4').removeClass('smoothing_status');
                    $('.opc-progress-bar-item5').removeClass('transit_status');
                }else if(data == "under_smoothing"){
                    $('.smoothning').addClass('_active');
                    $('.procurement').addClass('_active');
                    $('.processing').addClass('_active');
                    $('.complete').removeClass('_active');
                     $('.readytoship').removeClass('_active');
                    $('.opc-progress-bar-item1').addClass('pending_status');
                    $('.opc-progress-bar-item2').addClass('processing_status');
                    $('.opc-progress-bar-item3').addClass('procurement_status');
                    $('.opc-progress-bar-item4').removeClass('smoothing_status');
                    $('.opc-progress-bar-item5').removeClass('transit_status');
                }else if(data == "ready_to_ship"){
                    $('.readytoship').addClass('_active');
                    $('.smoothning').addClass('_active');
                    $('.procurement').addClass('_active');
                    $('.processing').addClass('_active');
                    $('.opc-progress-bar-item1').addClass('pending_status');
                    $('.opc-progress-bar-item2').addClass('processing_status');
                    $('.opc-progress-bar-item3').addClass('procurement_status');
                    $('.opc-progress-bar-item4').addClass('smoothing_status');
                    $('.opc-progress-bar-item5').removeClass('transit_status');
                    $('.complete').removeClass('_active');
                }else if(data == "complete"){
                    $('.complete').addClass('_active');
                    $('.readytoship').addClass('_active');
                    $('.smoothning').addClass('_active');
                    $('.procurement').addClass('_active');
                    $('.processing').addClass('_active');
                    $('.opc-progress-bar-item1').addClass('pending_status');
                    $('.opc-progress-bar-item2').addClass('processing_status');
                    $('.opc-progress-bar-item3').addClass('procurement_status');
                    $('.opc-progress-bar-item4').addClass('smoothing_status');
                    $('.opc-progress-bar-item5').addClass('transit_status');
                }else if(data == "canceled"){
                  $('.track-image').hide();
                  $('.progress-track').hide();
                  $('.progress-track-error').show();
                  $('.progress-track-error').text('Sorry ! Your order has been canceled.')
                }else if(data == "Order not found"){
                  $('.track-image').hide();
                  $('.progress-track').hide();
                  $('.progress-track-error').show();
                  $('.progress-track-error').text('Sorry ! Your did not find your order id.')
                }else{
                    $('.processing').removeClass('_active');
                    $('.complete').removeClass('_active');
                    $('.readytoship').removeClass('_active');
                    $('.smoothning').removeClass('_active');
                    $('.procurement').removeClass('_active');
                    $('.opc-progress-bar-item1').removeClass('pending_status');
                    $('.opc-progress-bar-item2').removeClass('processing_status');
                    $('.opc-progress-bar-item3').removeClass('procurement_status');
                    $('.opc-progress-bar-item4').removeClass('smoothing_status');
                    $('.opc-progress-bar-item5').removeClass('transit_status');
                }
                $('.progress-track').attr('data-id',1);
            }
            return html;
        },

        templates: {
            "default": '<div class="left"><%= label %></div><div class="right ab1"><b><%= data %></b></div>',
            "error": '<div class="top error"><%= label %></div><div class="bottom"><b><%= data %></b></div>',
            "prod": '<div class="top"><%= label %></div><div class="bottom"><ul><% _.each(data, function(element){ %> <li><%= element.qty_ordered %> x <%= element.name %> (<%= element.price_incl_tax %>)</li> <% }); %></ul></div>',
            "track": '<div class="left"><%= label %></div><div class="right ab2"><% _.each(data, function(element){ %> <a href="<%= element %>" target="_blank"><b><%= translationModule.getTranslation("checkorder_show") %></b></a> <% }); %></div>'            
        }        
    };
    
    return templateEngine;
});