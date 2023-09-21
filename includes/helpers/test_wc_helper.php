<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
    function create_product() {
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
        
        return array("product_id" => $product->get_id(), "variant_id" => $variation->get_id());
    }

    function scwp_create_test_user() {
        $username = 'test_username';
        $email    = 'test@gmail.com';
        $password = 'test_password';
        $user_id = wp_create_user( $username, $password, $email );
        $user = new WP_User( $user_id );
        $user->set_role( 'customer' );
        $user->save();
        return $user;
    }
