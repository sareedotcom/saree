define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function(
        $,
        modal
    ) {
        'use strict';
        return function(param) {
            var countryData = '';
            $.ajax({
                url : 'https://www.saree.com/currency',
                dataType:'json',
                success : function(data) {
                    countryData = data;
                    if ($.cookie("currencyswitcher")) {
                        var currencyswitcher = $.cookie("currencyswitcher").split('|');
                        $("#swither-popup-country option[value='" + currencyswitcher[0] + "']").prop('selected', true);
                        $("select[name='switcher-currency'] option[value='" + currencyswitcher[1] + "']").prop('selected', true);
                    }
                    console.log("haelllllll");
                    console.log(countryData);
                    if (countryData.length > 0) {
                        var country_code = $("#swither-popup-country option:selected").val();
                        var index = countryData.findIndex(function (country) {
                            return country.alpha2Code == country_code
                        });
                        var flag = countryData[index].flag;
                        console.log("====");
                        console.log(flag);
                        $('#switcher-currency-trigger').append('<style type="text/css">#switcher-currency-trigger:before {content: ""; background: url(' + flag + '); position: absolute; height: 15px; width: 20px; background-size: cover; background-position: center; right: 15px; top: 2px; }</style>');

                        if ((!$.cookie("currencyswitcher") || ($.cookie("currencyswitcher")) && $.cookie("currencyswitcher") == '') && $.cookie("elsnergeoip") && $.cookie("elsnergeoip") != $("#swither-popup-country option:selected").val()) {
                            var geoIpIndex = countryData.findIndex(function (country) {
                                return country.alpha2Code == $.cookie("elsnergeoip")
                            });
                            var currentCountryIndex = countryData.findIndex(function (country) {
                                return country.alpha2Code == $("#swither-popup-country option:selected").val()
                            });
                            if ($('.currency-switcher-tooltip').length == 0) {
                                $('.currency-switcher-options').append('<div class="currency-switcher-tooltip"><div class="tooltip-header"><span>Shipping to ' + countryData[geoIpIndex].name + '?</span></div><div class="tooltip-body"><button name="shop-to-country" id="switch-currency-button" data-currency="' + countryData[geoIpIndex].currencies[0].code + '" data-country="' + countryData[geoIpIndex].alpha2Code + '">Shop ' + countryData[geoIpIndex].name + '</button></div><div class="tooltip-footer"><a href="javascript:void(0);" id="continue-shopping" data-currency="' + countryData[currentCountryIndex].currencies[0].code + '" data-country="' + countryData[currentCountryIndex].alpha2Code + '">Nope - continue shopping for ' + countryData[currentCountryIndex].name + '</a></div></div>');
                            }
                        }
                    }

                    $(document).on('click', '#switch-currency-button', function () {
                        $.cookie("currencyswitcher", $(this).data('country') + "|" + $(this).data('currency'), {expires: 1});
                        $(".switcher.currency .switcher-dropdown .currency-" + $(this).data('currency') + " a").click();
                    });

                    $(document).on('click', '#continue-shopping', function () {
                        $.cookie("currencyswitcher", $(this).data('country') + "|" + $(this).data('currency'), {expires: 1});
                        window.location.reload(true);
                    });

                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        buttons: [{
                            text: $.mage.__('Save'),
                            class: 'save-currency',
                            click: function () {
                                $.cookie("currencyswitcher", $("#swither-popup-country option:selected").val() + "|" + $("select[name='switcher-currency'] option:selected").val(), {expires: 1});
                                if ($("select[name='switcher-currency'] option:selected").val() == param.currentCurrencyCode) {
                                    window.location.reload(true);
                                }
                                var selectedOption = $("select[name='switcher-currency'] option:selected").val();
                                $(".switcher.currency .switcher-dropdown .currency-" + selectedOption + " a").click();
                            }
                        },
                            {
                                text: $.mage.__('Cancel'),
                                class: 'cancel',
                                click: function () {
                                    this.closeModal();
                                }
                            }]
                    };

                    var popup = modal(options, $('.switcher-currency-modal'));
                    $(".switcher-trigger").on('click', function () {
                        $(".switcher-currency-modal").modal("openModal");
                    });

                    $("#swither-popup-country").change(function () {
                        var country_code = $('#swither-popup-country').val();

                        if (countryData.length == 0) {
                            alert('Something went wrong! Please try again.');
                        }

                        var index = countryData.findIndex(function (country) {
                            return country.alpha2Code == country_code
                        });
                        var currency = countryData[index].currencies[0].code;

                        if ($("select[name='switcher-currency'] option[value='" + currency + "']").length) {
                            $("select[name='switcher-currency'] option[value='" + currency + "']").prop('selected', true);
                        } else {
                            $("select[name='switcher-currency'] option[value='INR']").prop('selected', true);
                        }
                    });
                },
                error : function(request,error)
                {
                    console.log("Request: "+JSON.stringify(request));
                }
            });

        }
    });