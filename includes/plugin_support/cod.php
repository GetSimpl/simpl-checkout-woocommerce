<?php 

class CodShippingRate {
    function cod_shipping_modes() {
        if (!is_plugin_active('wc-smart-cod/wc-smart-cod.php')) {
            return false;
        }        
        $_POST['payment_method'] = "cod";
        $cod = new Wc_Smart_Cod_Public('wc-smart-cod');
        $cod_fee = $cod->apply_smart_cod_fees(WC()->cart, false);
        if(!$cod_fee) return;      
        return array("id" => "cod:simpl_shipping_rate", "slug" => "cod", "name" => "Shipping via COD", "amount" => wc_format_decimal($cod_fee, 2), "total_tax" =>wc_format_decimal(0, 2), "taxes" => array(), "is_cod" => true);
    }
}
