define(['jquery','Magento_Ui/js/modal/modal'], function($){
    "use strict";
    return function myscript() {
        require(
            [
                'jquery',
                'Magento_Ui/js/modal/modal'
            ],
            function ($, modal) {
                $('.action-remove').on('click', function (e) {
                    var mediaId = e.target.getAttribute('media-id');
                    $('#deleted_media').val($('#deleted_media').val()  + mediaId + ",");
                    $(e.target).parent().parent().parent().remove();
                });
                $(".product-image").on('click', function () {
                    $("#review-image-modal").html("");
                    $("#review-image-modal").prepend('<img id="review-image-lg" src="' + $(this).attr("src") + '"/>');
                    $("#review-image-modal").modal({
                        type: 'popup',
                        title: 'Review Image',
                        clickableOverlay: true,
                        buttons:[],
                        responsive: true
                    }).modal('openModal').css({"text-align":"center"});
                });
            }
        );
    }
});