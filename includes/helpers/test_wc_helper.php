<?php

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
        $variation->set_regular_price( 1000000 ); // yep, magic hat is quite expensive
        $variation->save();
        
        return array("product_id" => $product->get_id(), "variant_id" => $variation->get_id());
    }
?>