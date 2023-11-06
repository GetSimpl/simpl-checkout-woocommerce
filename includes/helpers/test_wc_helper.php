<?php
    use Automattic\WooCommerce\StoreApi\Utilities\OrderController;
    
    function simpl_test_create_product() {
        $product = new WC_Product_Variable();
        // Name and image would be enough
        $product->set_name( 'Wizard Hat' );

        // one available for variation attribute
        $attribute = new WC_Product_Attribute();
        $attribute->set_name( 'Magical' );
        $attribute->set_options( array( 'Yes', 'No' ) );
        $attribute->set_position( 0 );
        $attribute->set_visible( true );
        $attribute->set_variation( true ); // here it is
            
        $product->set_attributes( array( $attribute ) );

        // save the changes and go on
        $product->save();

        // now we need two variations for Magical and Non-magical Wizard hat
        $variation = new WC_Product_Variation();
        $variation->set_parent_id( $product->get_id() );
        $variation->set_attributes( array( 'magical' => 'Yes' ) );
        $variation->set_regular_price( 10 );
        $variation->save();
        
        return array("product_id" => $product->get_id(), "variant_id" => $variation->get_id(), "variation" => $variation->get_attributes());
    }

    function simpl_test_create_user() {
        $username = 'test_username';
        $email    = 'test@gmail.com';
        $password = 'test_password';
        $user_id = wp_create_user( $username, $password, $email );
        $user = new WP_User( $user_id );
        $user->set_role( 'customer' );
        $user->save();
        return $user;
    }

    function simpl_test_create_cart($data) {
        WC()->cart->add_to_cart($data['product_id'], 1, $data['variant_id']);
        $shipping_address = array(
            "first_name" => "Donald",
            "last_name" => "Trump",
            "phone" => "9999999999",
            "address_1" => "4444",
            "address_2" => "SC, DC",
            "city" => "Washington",
            "state" => "Delhi",
            "country" => "India",
            "postcode" => "111111"
        );
        $billing_address = array(
            "first_name" => "Donald",
            "last_name" => "Trump",
            "phone" => "9999999999",
            "address_1" => "4444",
            "address_2" => "SC, DC",
            "city" => "Washington",
            "state" => "Delhi",
            "country" => "India",
            "postcode" => "111111"
        );
        SimplWcCartHelper::set_address_in_cart($shipping_address, $billing_address);
    }

    function simpl_test_set_default_shipping_method_in_order($order) {
        $shipping = array(
            "slug" => "test_id",
            "name" => "DEFAULT SHIPPING",
            "amount" => 10
        );
        SimplWcCartHelper::simpl_set_shipping_method_in_order($order, $shipping);
        $order->save();
    }

    function simpl_test_get_shipping_method($order) {
        foreach ($order->get_shipping_methods() as $item_id => $item) {
            $shipping_methods_array["id"] = $item->get_id();
            $shipping_methods_array["slug"] = $item->get_method_id();
            $shipping_methods_array["name"] = $item->get_name();
            $shipping_methods_array["amount"] = wc_format_decimal($item->get_total(), 2);
            $shipping_methods_array["total_tax"] = wc_format_decimal($item->get_total_tax(), 2);
        }
        return $shipping_methods_array;
    }
