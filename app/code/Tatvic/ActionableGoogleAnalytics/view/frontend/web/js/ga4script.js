define(['jquery','ga4_script'], function($,ga4_script){
    "use strict";
    return function ga4_script() {
        this.custom_dimentions = function () {

            gtag('config', tvc_measurement_IDGA4, {
                'custom_map': {
                    'dimension1': 'stock_status',
                    'dimension2': 'page_type',
                    'dimension3': 'user_type',
                    'dimension4': 'product_discount',
                    'dimension5': 'payment_method',
                    'dimension6': 'ship_bill_city',
                    'dimension7': 'day_type',
                    'dimension8': 'local_timeslot',
                    'dimension9': 'product_page_sequence',
                    'dimension10': 'add_to_cart_position',
                    'dimension11': 'no_product_stock',
                    'dimension12': 'product_size',
                    'dimension13': 'time_taken_to_purchase',
                    'dimension14': 'time_taken_for_add_to_cart',
                    'dimension15': 'clientId',
                    'dimension16': 'new_or_repeat_customer',
                    'send_page_view' : false
                }
            });

            gtag('get', tvc_UA_ID, 'client_id', (client_id) => {
                gtag('event', 'Client Id', { 'clientId': client_id });
            });

            if(tvc_link_attr === 1){
                // Enable enhanced link attribution
                gtag('config', tvc_measurement_IDGA4, { 'link_attribution': true,'send_page_view' : false ,'non_interaction': true});
            }
            if (tvc_user_id !== '') {
                gtag('config', tvc_measurement_IDGA4, { 'user_id': tvc_user_id,'send_page_view' : false,'non_interaction': true });
            }
            if(tvc_ads_feature === 1){
                gtag('config', tvc_measurement_IDGA4, { 'allow_ad_personalization_signals': false,'send_page_view' : false,'non_interaction': true });
            }
            if(tvc_ip == 1){
                gtag('config', tvc_measurement_IDGA4, { 'anonymize_ip': true,'send_page_view' : false,'non_interaction': true });
            }
        }

        this.custom_metrics = function () {
            // Configures custom metric<Index> to use the custom parameter
            // 'metric_name' for GA_MEASUREMENT_ID, where <Index> is a number
            // representing the index of the custom metric.
            gtag('config', tvc_measurement_IDGA4, {
                'custom_map': {
                    'metric1': 'no_of_clicks_on_cat_page',
                    'metric2': 'no_of_clicks_on_prod_page',
                    'metric3': 'no_of_orders_of_register_customer',
                    'non_interaction': true
                }
            });
        }
    };
});

