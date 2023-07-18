define(['jquery','Magento_Ui/js/modal/modal'], function($){
    "use strict";
    return function myscript() {
        require(
            [
                'jquery',
                'Magento_Ui/js/modal/modal'
            ],
            function ($, modal) {
                $(".product-image").on('click', function () {

                    $("#review-image-modal").html("");
                    $("#review-image-modal").prepend('<img id="review-image-lg" src="' + $(this).attr("src") + '"/>');

                    $("#review-image-modal").modal({
                        type: 'popup',
                        title: 'Review Image',
                        clickableOverlay: true,
                        buttons: [],
                        responsive: true
                    }).modal('openModal').css({"text-align": "center"});
                });
            }
        );
        $(document).on('change','#review_media',function(){
            var files = $(this)[0].files;
            var file = this.files[0];
            var fileType = file["type"];
            var validImageTypes = ["image/gif", "image/jpeg", "image/png"];
            if ($.inArray(fileType, validImageTypes) < 0) {
                $(this).val('');
                alert('You are allowed to upload image file only');
            }
            var fileSize=(this.files[0].size);
            if(fileSize > 5000000) {
                $(this).val('');
                alert('image size should be less than 5 MB');
            };
            if(files.length > 2){
                $(this).val('');
                $("#media_field-error").show();
            }else{
                $("#media_field-error").hide();
            }
        });
    }
});