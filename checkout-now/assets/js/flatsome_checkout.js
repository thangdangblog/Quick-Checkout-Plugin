jQuery.noConflict();
//Khai báo biến Global
stringLoading = "<div class='lds-ellipsis'> <div></div><div></div><div></div><div></div> </div>";
jQuery(document).ready(function($) {
    let status = 0; //Trạng thai gọi ajax 0: Chưa gọi - 1 Đang gọi
    $(".td_check_out_now").click(function() {
        if (status == 0) {
            status = 1; // Đang gọi Ajax chưa trả về kết quả, chưa thể gọi cho lần kế tiếp
            $(this).html(stringLoading);
            id_product = $(this).attr("data-product");
            $.ajax({
                type: "POST",
                url: ajax_checkout.ajax_url,
                data: {
                    action: 'flatsome_checkout',
                    id_product: id_product
                },
                dataType: "",
                success: function(res) {
                    status = 0; //Đã gọi và trả về kết quả, có thể gọi cho lần tiếp
                    dataProduct = JSON.parse(res);
                    let pricetg = 0;
                    $(".title_checkout_now").text(dataProduct.title);
                    $(".image_checkout_now").html(dataProduct.image_thumb);
                    if (dataProduct.sale_price > 0) {
                        $(".price_checkout_now").html("<div class='sale_price_flatsome'></div><div class='regular_price_flatsome'></div>");
                        $(".sale_price_flatsome").text(formatNumber(dataProduct.sale_price) + " " + dataProduct.woocommerce_currency);
                        $(".regular_price_flatsome").text(formatNumber(dataProduct.regular_price) + " " + dataProduct.woocommerce_currency);
                        $(".buyer_total").text(formatNumber(dataProduct.sale_price) + " " + dataProduct.woocommerce_currency);
                        pricetg = dataProduct.sale_price;
                    } else if (dataProduct.regular_price == 0) {
                        $(".price_checkout_now").html("<div class='contact_flatsome'>Liên hệ</div>");
                        $(".buyer_total").text('Liên hệ');
                        pricetg = -1;
                    } else {
                        $(".price_checkout_now").html("<div class='regular_price_flatsome'></div>");
                        $(".regular_price_flatsome").text(formatNumber(dataProduct.regular_price) + " " + dataProduct.woocommerce_currency);
                        $(".buyer_total").text(formatNumber(dataProduct.regular_price) + " " + dataProduct.woocommerce_currency);
                        pricetg = dataProduct.regular_price;
                    }

                    $(".btn-checkout-flatsome").click();
                    $(".td_check_out_now").html("Đặt hàng nhanh");

                    //Xử lý giá
                    $(".amount_flatsome").change(function() {
                        if (pricetg != -1 && $(".amount_flatsome").val() != 0 && $(".amount_flatsome").val() > 0) {
                            $(".buyer_total").text(formatNumber(dataProduct.regular_price * $(".amount_flatsome").val()) + " " + dataProduct.woocommerce_currency);
                        } else if ($(".amount_flatsome").val() <= 0) {
                            $(".amount_flatsome").val(1);
                        }
                    });
                    //Gọi hàm Xử lý thanh toán
                    checkCoupon($, id_product, dataProduct.woocommerce_currency);
                    flatsome_checkout($);
                }
            });
        }
    });
});

//Xử lý thanh toán
function flatsome_checkout($) {
    status_checkout = 0; //Trạng thai gọi ajax 0: Chưa gọi - 1 Đang gọi
    $(".btn-checkout-now").click(function() {
        $(this).html(stringLoading);
        //Get data from frontend
        fs_fullname = $(".buyername").val();
        fs_phone = $(".buyernumber").val();
        fs_email_address = $(".buyeremail").val();
        fs_address = $(".buyeraddress").val();
        fs_note = $(".buyernote").val();
        fs_coupon = $(".buyercoupon").val();
        fs_number = $(".amount_flatsome").val();
        // Thêm vào data
        dataGuest = {
            id_product: id_product,
            fullname: fs_fullname,
            phone: fs_phone,
            email: fs_email_address,
            address: fs_address,
            note: fs_note,
            coupon: fs_coupon,
            amount: fs_number,
        };
        if (status_checkout == 0) {
            status_checkout = 1;
            $.ajax({
                type: "POST",
                url: ajax_checkout.ajax_url,
                data: {
                    action: 'flatsome_checkout_handle',
                    data: dataGuest,
                },
                dataType: "",
                success: function(response) {
                    status_checkout = 0;
                    window.location.href = response;
                }
            });
        }
    });
}


function checkCoupon($, price, woocommerce_currency) {
    status_coupon = 0;
    $(".btn_buyercoupon").click(function() {
        $(this).html(stringLoading);
        if (status_coupon == 0) {
            let fs_coupon = $(".buyercoupon").val();
            status_coupon = 1;
            $.ajax({
                type: "POST",
                url: ajax_checkout.ajax_url,
                data: {
                    action: 'checkcoupon',
                    fs_coupon: fs_coupon,
                    id_product: id_product,
                },
                dataType: "",
                success: function(result) {
                    status_coupon = 0;
                    result = JSON.parse(result);
                    $(".btn_buyercoupon").html("Áp dụng");
                    if (result.status == 0) {
                        $(".buyercoupon").css("border-color", "red");
                        $(".buyer_total").text(formatNumber(result.price) + " " + woocommerce_currency);
                    } else {
                        $(".buyer_total").text(formatNumber(result.price) + " " + woocommerce_currency);
                        $(".buyercoupon").css("border-color", "#ddd");
                    }
                }
            });
        }
    });
}

function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}