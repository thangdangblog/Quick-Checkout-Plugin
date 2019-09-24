<?php 
    function admin_menu_flatsome_checkout(){
        add_menu_page('Quick Checkout Flatsome Setting',"Quick Checkout FS","manage_options","quick-checkout-flatsome","admin_page_show","",11);
    }
    add_action("admin_menu","admin_menu_flatsome_checkout");

    function admin_page_show(){
        ?>
        <h2>Quick Checkout Flatsome Setting</h2>
        <?php
    }
?>