/**
  * Mageants GiftCard Magento2 Extension                           
*/
require([
		"jquery",
		"giftcertificate",
		'Magento_Catalog/js/price-utils',
        "Magento_Checkout/js/model/cart/cache"],
        function($, _, priceUtils, cartCache) {
		jQuery(document).on('click', ".template-image", function () {
		 		var img=jQuery(this).attr('src');
		 		jQuery("#giftimage").val(img);
		 		jQuery(".no-sirv-lazy-load").attr('src',img);
		 		jQuery(".fotorama__img--full").attr('src',img);
		 		jQuery("#template_id").val(jQuery(this).siblings('.temp_id').val())
		 		
		});

			jQuery(document).on('click', "#gift-code", function () {
					if(jQuery('#gift-code-arrow').hasClass("fa-angle-down")){
						jQuery("#gift-code-arrow").removeClass("fa-angle-down").addClass("fa-angle-up");
					}
					else{
						jQuery("#gift-code-arrow").removeClass("fa-angle-up").addClass("fa-angle-down");
					}
				jQuery(".gift-code-block").toggle();
			
 			});
 			jQuery(document).on('submit', "#gift-certificate-form", function () {
 				var cat_ids=[];
 				var str='';
 				var button = jQuery("#applyCode").text();
 				var actionUrl = "";
 				if (button == "Apply") {
 					actionUrl = jQuery("#base-gift-url").val()+"giftcertificate/cart/apply";
 				}
 				else{
 					actionUrl = jQuery("#base-gift-url").val()+"giftcertificate/cart/cancel";
 				}
 				jQuery("#gift-certificate-form").prop( "disabled", true );
 				jQuery(".loading").toggle();
 				jQuery(".gift-code-block").css('opacity',0.2);
 				if(jQuery("#gift-certificate-code").val()==''){
 					jQuery(".error-msg").show(500);
 					jQuery(".loading").toggle();
 					jQuery(".gift-code-block").css('opacity',1);
 					jQuery("#gift-certificate-form").prop( "disabled", false );
 					return false;
 				}	
 				jQuery(".categoryids").each(function() {
 					var categoryids=jQuery(this).val();
 					cat_ids.push(jQuery(this).val());
 				});		
 					jQuery.ajax({
					type:'POST',
				  		data:{gift_code:jQuery("#gift-certificate-code").val(),categoryids:cat_ids},
						url:actionUrl,
						success:function(data){
						 var code = data;
						   if (code[0]=='3')
						   {
								jQuery("#message").html(code[1]);
								cartCache.set('totals',null);
                				location.reload(true);
						   }
						   else if(code[0]=='5')
							{
							 jQuery("#message").html(code[1]);	
							 jQuery(".loading").hide();
 							jQuery(".gift-code-block").css('opacity',1);
 							jQuery("#gift-certificate-form").prop( "disabled", false );
 							return false;

							}
						   else
						   {
							jQuery("#message").html(code[1]);
						   }
							jQuery(".loading").hide();
 							jQuery(".gift-code-block").css('opacity',1);
 							jQuery("#gift-certificate-form").prop( "disabled", false );
				
						}	
					});		
			
			return false;
 			});

 			jQuery(document).on('click', "#check_status", function(){
 				jQuery("#gift-certificate-form").prop( "disabled", true );
 				jQuery(".loading").toggle();
 				jQuery(".gift-code-block").css('opacity',0.2);

				if(jQuery("#gift-certificate-code").val()==''){
 					jQuery(".error-msg").show(500);
					jQuery(".loading").toggle();
 					jQuery(".gift-code-block").css('opacity',1);
 					jQuery("#gift-certificate-form").prop( "disabled", false );

 					return false;
 				}	
 					jQuery.ajax({
					type:'POST',
				  		data:{gift_code:jQuery("#gift-certificate-code").val()},
						url:jQuery("#base-gift-url").val()+"giftcertificate/cart/check",
						success:function(data){
							var code = data;
							console.log(code);
							jQuery("#message").html(data);
		 					jQuery(".loading").toggle();
		 					jQuery(".gift-code-block").css('opacity',1);
		 					jQuery("#gift-certificate-form").prop( "disabled", false );
						}	
				});		
			return false;
 			});
 			jQuery(".deletequote").click(function(){
 				var quoteid=jQuery(this).siblings('.quote-id').val();
 				var productId=jQuery(this).siblings('#productid').val();
 				jQuery(".loading-cart").toggle();
 				jQuery(".additional-info").css('opacity',0.2);
 				if(quoteid==''){
 					return false;
 				}
 					jQuery.ajax({
 							type:'POST',
				  		data:{quoteid:quoteid, productId:productId},
						url:jQuery("#base-gift-url").val()+"giftcertificate/cart/remove",
						success:function(data){
							location.reload(true);
 						}
 					});
 			});
 			jQuery(document).on('click','.switch-prices', function(){
 				var ButtonText = $(".switch-prices").text();
 				var ButtonText = $.trim(ButtonText);
				  if(ButtonText=='Buy With Your Price')
				  {
				  	jQuery('#product-addtocart-button').prop("disabled", true);
				  	$('#manual-price').keyup(function() {
				  		console.log("This function is called 2");
							var manual_price = document.getElementById('manual-price').value;
							var entr_value = parseFloat(manual_price);
							var minprice = jQuery('#minprice').val();
							var maxprice = jQuery('#maxprice').val();
							jQuery(".gift-row span").css("display","none");

							if(entr_value >= parseFloat(minprice) && entr_value <= parseFloat(maxprice))
							{
								var finalPrice= entr_value;
								
								jQuery(".gift-row span").css("display","none");
						        jQuery("#gift-template-label span").css("display","none");

								jQuery('#product-addtocart-button').prop("disabled", false);
						        
						        formatedPrice = priceUtils.formatPrice(parseFloat(finalPrice));
								jQuery('span.price-final_price > span.price-wrapper > span.price').text(formatedPrice);
				        		$('#manual-price').val(finalPrice);
								formatedPrice = priceUtils.formatPrice(parseFloat(finalPrice));
						        jQuery('span.price-final_price > span.price-wrapper > span.price').text(formatedPrice);	
								
							}
							
							else
							{
								var gift_price = jQuery("#gift-prices option:selected").val();
								var finalPrice= parseFloat(gift_price);

							  	jQuery(".gift-row span").css("display","block");
					        	$(".gift-row span").fadeIn(1000);
								$(".gift-row span").fadeOut(2000);
						       
						        jQuery('#product-addtocart-button').prop("disabled", true);

							  	formatedPrice = priceUtils.formatPrice(parseFloat(finalPrice));
								jQuery('span.price-final_price > span.price-wrapper > span.price').text(formatedPrice);
								//finalPrice=(pricePerText*textObjects)+(pricePerImage*imageObjects);
								formatedPrice = priceUtils.formatPrice(parseFloat(finalPrice));
						        jQuery('span.price-final_price > span.price-wrapper > span.price').text(formatedPrice);
							
							}
						});
				  	 	jQuery('#manual-price').val('');
				  		jQuery(this).text('Buy With System Price');
					    jQuery("select.gift-prices").toggle();
					    jQuery("input.manual-price").toggle();
				  }
				  else
				  {
					  	jQuery('#product-addtocart-button').prop("disabled", false);
					  	var gift_price = jQuery("#gift-prices option:selected").val();
						var finalPrice= parseFloat(gift_price);

						jQuery(".gift-row span").css("display","none");
						jQuery("#gift-template-label span").css("display","none");
						jQuery(this).text('Buy With Your Price');
						jQuery("select.gift-prices").toggle();
				    	jQuery("input.manual-price").toggle();

				    	jQuery('#product-addtocart-button').prop("disabled", false);
				    	
						formatedPrice = priceUtils.formatPrice(parseFloat(finalPrice));
				  		jQuery('span.price-final_price > span.price-wrapper > span.price').text(formatedPrice);
				  		formatedPrice = priceUtils.formatPrice(parseFloat(finalPrice));
				  		jQuery('span.price-final_price > span.price-wrapper > span.price').text(formatedPrice);
				     	jQuery(this).text('Buy With Your Price');
				  }
  			});
  			jQuery('.manual-price').keyup(function () { 
   				 this.value = this.value.replace(/[^0-9\.]/g,'');
			});
 		
	});	
