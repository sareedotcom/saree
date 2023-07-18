define(['jquery'], function($){
    "use strict";
    return function myscript() {
        require(['jquery', 'domReady!'], function ($) {
            $("#myform").on("submit", function () {
                $('body').trigger('processStart');
            });
        });
        $(function () {
            $("input[type='checkbox']").on('change', function () {
                $(this).closest("tr").find("select[id=item_cancellation_reason]").prop("disabled", !this.checked);
                $(this).closest("tr").find("select[id=item_cancellation_reason]").prop("required", this.checked)
            });
        });

        $('select[name=order_cancellation_option]').change(function () {
            if ($(this).val() == 'cancel_entire_order') {
                $('#order_cancellation_reason').prop('required', true);
            } else {
                $('#order_cancellation_reason').prop('required', false);
            }
        });

        $('#submit').click(function () {
            var optionVal = document.getElementById("order_cancellation_option").value;
            if (optionVal == 'specific_item') {
                var checkboxs = document.getElementsByName("selected_item[]");
                var okay = false;
                for (var i = 0, l = checkboxs.length; i < l; i++) {
                    if (checkboxs[i].checked) {
                        okay = true;
                        break;
                    }
                }
                if (okay) {
                    return true;
                }
                else {
                    alert("Please select atleast one item");
                    return false;
                }
            }
        });

        $(".select_item").hide();
        var unselectable = document.getElementsByClassName('unselectable');
        if (unselectable.length != 0) {
            var optionDropDown = document.getElementById("order_cancellation_option");
            optionDropDown.remove(1);
        }
        $('#order_cancellation_option').on('change', function () {
            var val = $(this).val();
            const element = document.getElementById("tbl_row");
            if (val == 'specific_item') {
                $(".select_item").show();
                $('.select_cancellation_reason').css("display", "none");
            }
            else {
                $(".select_item").hide();
            }
            if (val == 'cancel_entire_order') {
                $('.select_cancellation_reason').css("display", "block");
            }
        });

        $( document ).ready(function() {
            var date = new Date();
            var year = date.toLocaleString("default", { year: "numeric" });
            var month = date.toLocaleString("default", { month: "2-digit" });
            var day = date.toLocaleString("default", { day: "2-digit" });
            var formattedDate = year + "-" + month + "-" + day;
            const orderdate = document.getElementById('order_date').value;
            const orderTimeHours = document.getElementById('order_time').value;
            var d = new Date();
            d.getHours();
            if (formattedDate != orderdate) {
                var dropdownOption = document.getElementById("order_cancellation_reason");
                dropdownOption.remove(1);
                var i;
                var itemDropDown = document.getElementsByClassName("item_cancellation_reason");
                for (i = 0; i < itemDropDown.length; i++) {
                    $($(itemDropDown[i]).children()).each( function(i, item) {
                        if(i == 1) {
                            $(item).remove();
                        }
                    })
                }
            }
            if (formattedDate == orderdate) {
                var time_diff = d.getHours() - orderTimeHours;
                if (time_diff > 2) {
                    var dropdownOption = document.getElementById("order_cancellation_reason");
                    dropdownOption.remove(1);
                    var i;
                    var itemDropDown = document.getElementsByClassName("item_cancellation_reason");
                    for (i = 0; i < itemDropDown.length; i++) {
                        $($(itemDropDown[i]).children()).each( function(i, item) {
                            if(i == 1) {
                                $(item).remove();
                            }
                        })
                    }
                }
            }
        });
    }
});
