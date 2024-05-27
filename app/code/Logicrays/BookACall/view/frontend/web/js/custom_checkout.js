document.addEventListener("DOMContentLoaded", function () {
    var script = document.createElement('script');
    //script.src = '/Logicrays_BookACall/js/inject_script.js'; // Path to your custom JavaScript file
    script.type = 'text/javascript';
    document.body.appendChild(script);
    if(checkoutConfig.quoteItemData.length == 1){
        if(checkoutConfig.quoteItemData[0]['sku'] == 'book-a-call'){
            var x = setInterval(function () {
                jQuery("input[name='firstname']").val('4th floor').change();
                jQuery("input[name='lastname']").val('| Asopalav House').change();
                jQuery("input[name='street[0]']").val("Opp, ITC Narmada Hotel").change();
                jQuery("[name='country_id']").val("US").change();
                jQuery("[name='region_id']").val("62").change();
                jQuery("[name='city']").val("Ahmedabad").change();
                jQuery("[name='postcode']").val("380015").change();
                jQuery("[name='telephone']").val("+91 93132 27352").change();
                jQuery(".checkout-billing-address").css("display","none");
            }, 5000);
            setTimeout(function( ) { clearInterval( x ); jQuery("button[class='action action-update']").click();
 }, 8000);
        }
    }
    else{
        var y = setInterval(function () {
            jQuery("input[name='firstname']").val("");
            jQuery("input[name='lastname']").val("");
            jQuery("input[name='street[0]']").val("");
            jQuery("[name='country_id']").val("");
            jQuery("[name='region']").val("");
            jQuery("[name='region_id']").val("");
            jQuery("[name='city']").val("");
            jQuery("[name='postcode']").val("");
            jQuery("[name='telephone']").val("");
        }, 5000);
        setTimeout(function( ) { clearInterval( y ); }, 8000);
    }
});