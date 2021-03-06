<?php
define("CONTD_ADMIN_PATH_CUSTOM",plugins_url()."/checkout-now");
function CONTD_addAdminCss(){
    wp_register_style('adminCss',CONTD_ADMIN_PATH_CUSTOM."/assets/css/admin.css");
    wp_enqueue_style('adminCss');
}
add_action('admin_enqueue_scripts','CONTD_addAdminCss');

function CONTD_addMenuPageQFL(){
    add_menu_page("Cài đặt Quick Checkout FlatSome","Quick Checkout FS","manage_options","quickcheckout-flatsome-setting","CONTD_displaySettingQCFL","",10);
}
add_action("admin_menu","CONTD_addMenuPageQFL");

function CONTD_displaySettingQCFL(){
    //If it doesn't have option, it's will add.
    $update = CONTD_updateOption();
    $isshowhome_qcfl = get_option('isshowhome_qcfl') == "true" ? 'checked' : '';
    $isProductPage_qcfl = get_option('isProductPage_qcfl') == "true" ? 'checked' : '';

    //Notice if update
    if($update) echo CONTD_admin_notice_success();
    ?>
    <h1>Quick Checkout Flatsome Setting</h1>
    <div class="conatiner">
   
        <form method="POST" action="">
            <div class="input-block">
            <label class="option-name-label" for="">Display in Homepage</label><input class="value-input" type="checkbox" name="ishowhome-qcfl"
            <?php echo $isshowhome_qcfl ?>><br /> 
        </div>
        <div class="input-block">
            <label class="option-name-label" for="">Display in the Product Page</label><input class="value-input" type="checkbox" name="isProductPage-qcfl"
            <?php echo $isProductPage_qcfl ?>><br />
        </div>
        <input class="button button-primary" name="btn-update-qcfl" type="submit" value="Update">
        </form>
    </div>
    <?php
}

function CONTD_addOption(){
    if(!get_option('isshowhome_qcfl')){
        add_option( 'isshowhome_qcfl','true', '', 'yes' );
    }
    if(!get_option('isProductPage_qcfl')){
        add_option( 'isProductPage_qcfl','true', '', 'yes' );
    }
}
add_action('init','CONTD_addOption');

function CONTD_updateOption(){
    if(isset($_POST['btn-update-qcfl'])){
        $status_ishowhome = $_POST['ishowhome-qcfl'] != NULL ? 'true' : 'false';
        $status_productPage = $_POST['isProductPage-qcfl'] != NULL ? 'true' : 'false';
        update_option('isshowhome_qcfl',$status_ishowhome,'yes');
        update_option('isProductPage_qcfl',$status_productPage,'yes');
        return true;
    }
    return false;
}

function CONTD_admin_notice_success() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Done!', 'flatsome-checkout-now-td' ); ?></p>
    </div>
    <?php
}

?>