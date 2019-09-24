<?php
/*
Plugin Name: Flatsome - Check out now
Plugin URI: https://thangdangblog.com/
Description: Thêm thanh toán nhanh cho Flatsome
Author: Đặng Thắng
Author URI: https://thangdangblog.com/
Text Domain: flatsome-checkout-now-td
Version: 1.0.0
*/

// include "admin-page.php";

define('PLUGINPATH',plugin_dir_url(__FILE__));

global $post;
//add Css to website
function addCssToWebsite(){
    wp_register_style('flatsome_checkout_css',PLUGINPATH."assets/css/flatsome_checkout_css.css",array(),time());
    wp_enqueue_style('flatsome_checkout_css');

    wp_register_script('flatsome_checkout_js',PLUGINPATH."assets/js/flatsome_checkout.js",array('jquery'),time());
    wp_enqueue_script('flatsome_checkout_js');
}
add_action('wp_enqueue_scripts','addCssToWebsite');

//add js to website
function addJsToWebsite(){
    wp_register_script('flatsome_checkout_js',PLUGINPATH."assets/js/flatsome_checkout.js",array('jquery'),time());
    wp_enqueue_script('flatsome_checkout_js');

    //Call Ajax in Wordpress for checkoutnow
    wp_localize_script('flatsome_checkout_js','ajax_checkout',array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));
}
add_action('wp_enqueue_scripts','addJsToWebsite');

//add button to product
function addBtnCheckout(){
    global $post;
    $id_product = $post->ID;
    ?>
    
    <div class='td_check_out_now' data-product='<?php echo $id_product ?>'>Đặt hàng nhanh</div>
    <?php
    
}
add_action('flatsome_product_box_after','addBtnCheckout');
add_action('woocommerce_single_product_summary','addBtnCheckout',40);



function addLightBox(){
    echo do_shortcode('[button text="Lightbox button" link="#lightboxcheckout" class="btn-checkout-flatsome"]');
    echo do_shortcode('[lightbox id="lightboxcheckout" width="600px" padding="20px"]
        <div class="title_checkout_now"></div>
        <div class="content_checkout_now">
            <div class="info_checkout_now">
                <div class="image_checkout_now">
                </div>
                <div class="price_checkout_now">
                </div>
                 <div class="number_checkout_now">
                Số lượng: <input class="amount_flatsome" type="number" min="1" value="1"></input>
                </div>
            </div>
            <div class="form_checkout_now">
               <div class="person_form_checkout_now">Thông tin người mua</div>
               <input type="text" class="buyername" placeholder="Họ và tên"></input>
               <input type="text" class="buyernumber" placeholder="Số điện thoại"></input>
               <input type="text" class="buyeremail" placeholder="Địa chỉ Email"></input>
               <input type="text" class="buyeraddress" placeholder="Địa chỉ nhận hàng"></input>
               <textarea class="buyernote" placeholder="Ghi chú đơn hàng"></textarea>
               <div class="person_form_checkout_now">Mã giảm giá</div>
               <input type="text" class="buyercoupon" placeholder="Mã giảm giá"></input>
               <button  class="btn_buyercoupon">Áp dụng</button>
               <div class="buyer_total_element">
                <span>Tổng tiền: </span>
                <span class="buyer_total">0</span>
               </div>
               <button class="btn-checkout-now">Thanh toán ngay</button>
            </div>
        </div>
    [/lightbox]');
}
add_action('wp_footer','addLightBox');

//ajax return
function handleCheckoutNowAjax(){
    if(isset($_POST['id_product'])){
        $id_product =  $_POST['id_product'];
        $product = wc_get_product( $id_product );
        if($product = wc_get_product( $id_product )){
            $title = $product->get_title();
            $image_thumb = $product->get_image('woocommerce_thumbnail');
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
        }
        $product_data = array(
            'title' => $title,
            'image_thumb' => $image_thumb,
            'regular_price' => ($regular_price),
            'sale_price' => ($sale_price),
            'woocommerce_currency' => get_woocommerce_currency()
        );
        echo json_encode($product_data);
    }
    exit();
}
add_action('wp_ajax_nopriv_flatsome_checkout','handleCheckoutNowAjax');
add_action('wp_ajax_flatsome_checkout','handleCheckoutNowAjax');

function checkoutNow(){
    if(isset($_POST['data'])){
        $dataGuest = $_POST['data'];
        // if($dataGuest->fullname != "" && $dataGuest->phone != ""){
            create_order($dataGuest);
            // add_action('woocommerce_checkout_process', 'create_order');
        // }
    }
    
    die();
}
add_action('wp_ajax_flatsome_checkout_handle','checkoutNow');
add_action('wp_ajax_nopriv_flatsome_checkout_handle','checkoutNow');


function create_order($dataGuest){
    $dataGuestObj = (object)$dataGuest;
    $detail = array(
        'first_name' => $dataGuestObj->fullname,
        'email'      => $dataGuestObj->email,
        'phone'      => $dataGuestObj->phone,
        'address_1'  => $dataGuestObj->address,
    );
    if($order = wc_create_order()){
        $order->add_product(get_product($dataGuestObj->id_product), (int)$dataGuestObj->amount);
        $order->set_address($detail,'billing');
        $order->set_address($detail,'shipping');
        $order->add_order_note($dataGuestObj->note);
        $order->apply_coupon($dataGuestObj->coupon);
        $payment_gateways =WC()->payment_gateways->payment_gateways();
        $order->calculate_totals();
        if($payment_gateways['cod']->process_payment($order->id)['result']  == "success"){
            echo $payment_gateways['cod']->process_payment($order->id)['redirect'];
        }
    }
    
}

function checkCoupon(){
    $yourCoupon = $_POST['fs_coupon'];
    $id_product = $_POST['id_product'];
    $product = wc_get_product($id_product);
    // die(wc_get_product($id_product));
    if(!$product->is_on_sale()){
        $price = $product->regular_price;
    }else{
        $price = $product->sale_price;
    }
    if(existCoupon($yourCoupon)){
        $coupon = new WC_COUPON($yourCoupon);
        if($coupon->discount_type == "fixed_product" || $coupon->discount_type == "fixed_cart" ){
            if($price - $coupon->amount > 0){
                echo json_encode(array(
                    "status" => 1,
                    "price" => $price - $coupon->amount,
                ));
            }else{
                echo json_encode(array(
                    "status" => 1,
                    "price" => 0,
                ));
            }
        }else{
            echo json_encode(array(
                    "status" => 1,
                    "price" => $price - $coupon->amount*$price/100,
                ));
        }
        //percent
        //fixed_product
        //fixed_cart
    }else{
         echo json_encode(array(
                    "status" => 0,
                    "price" => $price,
                ));
    }
    exit();
}
add_action('wp_ajax_nopriv_checkcoupon','checkCoupon');
add_action('wp_ajax_checkcoupon','checkCoupon');

function getAllCoupon(){
    $arrCoupons = array();
    $coupons = get_posts(array(
        'post_type'        => 'shop_coupon',
        'post_status'      => 'publish',
    ));
    foreach ($coupons as $coupon) {
        $arrCoupons[] = $coupon->post_title;
    }
    return $arrCoupons;
}

function existCoupon($coupon){
    if(in_array($coupon,getAllCoupon())){
        return true;
    }
    return false;
}

?>

