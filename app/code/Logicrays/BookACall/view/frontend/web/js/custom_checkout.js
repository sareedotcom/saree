document.addEventListener("DOMContentLoaded", function () {
    var script = document.createElement('script');
    script.src = '/Logicrays_BookACall/js/inject_script.js'; // Path to your custom JavaScript file
    script.type = 'text/javascript';
    document.body.appendChild(script);
    if(checkoutConfig.quoteItemData.length == 1){
        if(checkoutConfig.quoteItemData[0]['sku'] == 'book-a-call'){
            var x = setInterval(function () {
                jQuery("input[name='firstname']").val('4th floor').change();
                jQuery("input[name='lastname']").val('| Asopalav House').change();
                jQuery("input[name='street[0]']").val("Opp, ITC Narmada Hotel").change();
                jQuery("[name='country_id']").val("IN").change();
                jQuery("[name='region']").val("Gujarat").change();
                jQuery("[name='city']").val("Ahmedabad").change();
                jQuery("[name='postcode']").val("380015").change();
                jQuery("[name='telephone']").val("918866799113").change();
                jQuery("button[class='action action-update']").click();
                jQuery(".checkout-billing-address").css("display","none");
            }, 2000);
            setTimeout(function( ) { clearInterval( x ); }, 13000);
        }
    }
    else{
        var y = setInterval(function () {
            jQuery("input[name='firstname']").val("");
            jQuery("input[name='lastname']").val("");
            jQuery("input[name='street[0]']").val("");
            jQuery("[name='country_id']").val("");
            jQuery("[name='region']").val("");
            jQuery("[name='city']").val("");
            jQuery("[name='postcode']").val("");
            jQuery("[name='telephone']").val("");
        }, 2000);
        setTimeout(function( ) { clearInterval( y ); }, 13000);
    }
});