requirejs(['jquery'], function($) {
	wizzy.registerEvent(wizzy.allowedEvents.AFTER_PRODUCTS_TRANSFORMED, function(products) {
	    if (Array.isArray(products)) {
		var totalProducts = products.length;

		for (var i = 0; i < totalProducts; i++) {
		    if (typeof products[i].attributes !== "undefined" && Array.isArray(products[i].attributes)) {
		        var totalAttributes = products[i].attributes.length;

		        var sku = null;
		        var brand = null;

		        for (var j = 0; j < totalAttributes; j++) {
		            if (typeof products[i].attributes[j].values !== "undefined" && products[i].attributes[j].values.length > 0 && typeof products[i].attributes[j].values[0].value !== "undefined" && products[i].attributes[j].values[0].value.length > 0) {
		                if (products[i].attributes[j].id == "sku") {
		                    sku = products[i].attributes[j].values[0].value[0];
		                }
		                if (products[i].attributes[j].id == "brand") {
		                    brand = products[i].attributes[j].values[0].value[0];
		                }
		            }
		        }

				products[i]['images'] = [];
				products[i]['mainImage'] = "https://www.saree.com/media/catalog/product/cache/105f7cd698c40f8c8cb493800ea1f5ad/p/c/pccdl2393a-lime-green-floral-printed-lehenga-in-organza-with-sequins-work1.jpg"
		        products[i]['sku'] = sku;
		        products[i]['brand'] = brand;
		    }
		}
	    }

	    return products;
	});

	$('body').on('click', '.wizzy-search-form-wrapper label', function(e){
		$('.page-header .block-search .input-text').show();
		$(this).hide();
	});
	
	$('body').on('click', '.wizzy-search-form-wrapper.mobileTapped .wizzy-search-back', function(e){ 
		$('.wizzy-search-form-wrapper label').show();
		$('.page-header .block-search .input-text').hide();
	});
});
