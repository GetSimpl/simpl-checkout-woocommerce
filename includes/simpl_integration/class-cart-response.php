<?php

const DEFAULT_WC_ORDER_FIELDS = ["key", "product_id", "variation_id", "variation", "quantity", "data", "data_hash", "line_tax_data", "line_subtotal", "line_subtotal_tax", "line_total", "line_tax"];

const HEADER_USER_AGENT = "User-Agent";
const HEADER_PLATFORM = "Platform";
const HEADER_ORIGIN = "Origin";

class SimplCartResponse
{
    public function cart_redirection_url($cart, $request)
    {
        $merchant_additional_details = $request['merchant_additional_details'];

        $client_information = self::simpl_get_client_information($request);
        $merchant_additional_details["client_information"] = $client_information;

        $cart_request = self::static_cart_payload($cart, $merchant_additional_details);
        $cart_request["unique_id"] = $request->get_header(SIMPL_WIDGET_SESSION_HEADER);
        $simpl_host = WC_Simpl_Settings::simpl_host();

        $simplHttpResponse = wp_remote_post("https://" . $simpl_host . "/api/v1/wc/cart", array(
            "body" => json_encode($cart_request),
            //TODO: merchantClientID
            "headers" => array(
                "shop-domain" => WC_Simpl_Settings::store_url(),
                "content-type" => "application/json",
            ),
        ));

        self::simpl_hide_error_messages(); // HIDE WOOCOMMERCE SUCCESS OR ERROR NOTIFICATION
        if (!is_wp_error($simplHttpResponse)) {
            $body = json_decode(wp_remote_retrieve_body($simplHttpResponse), true);

            return $body["redirection_url"];
        } else {
            $error_message = $simplHttpResponse->get_error_message();
            throw new Exception($error_message);
        }

        return "";
    }

    public function simpl_get_client_information($request) {
        $client_information = [];
        $header_user_agent = $request->get_header(HEADER_USER_AGENT);
        if ($header_user_agent != "") {
            $client_information["user_agent"] = $header_user_agent;
        }

        $header_platform = $request->get_header(HEADER_PLATFORM);
        if ($header_platform != "") {
            $client_information["platform"] = $header_platform;
        }

        $header_origin = $request->get_header(HEADER_ORIGIN);
        if ($header_origin != "") {
            $client_information["origin"] = $header_origin;
        }

        return $client_information;
    }

    public function static_cart_payload($cart, $merchant_additional_details)
    {
        $request = array("source" => "cart");
        $cart_payload = $this->cart_common_payload($cart, null, $merchant_additional_details);
        $request["cart"] = $cart_payload;
        return $request;
    }

    public function cart_payload($cart, $order = NULL)
    {
        $request = array("source" => "cart");
        $cart_payload = $this->cart_common_payload($cart, $order, null);
        $shipping_address = WC()->customer->get_shipping('edit');
        $billing_address = WC()->customer->get_billing('edit');
        if ($this->is_address_present($shipping_address, $billing_address)) {
            $cart_payload["shipping_address"] = $this->convert_address_response($shipping_address);
            $cart_payload["billing_address"] = $this->convert_address_response(($billing_address));
        }
        $cart_payload['checkout_order_id'] = $order->get_id();
        $request["cart"] = $cart_payload;
        self::simpl_hide_error_messages(); // HIDE WOOCOMMERCE SUCCESS OR ERROR NOTIFICATION
        return $request;
    }

    protected function is_address_present($shipping_address, $billing_address)
    {
        return (isset($shipping_address) && isset($billing_address) && count($shipping_address) > 0 && count($billing_address) > 0) && $shipping_address["country"] != "";
    }

    protected function convert_address_response($address) {
        $country = SimplUtil::country_name_from_code($address["country"]);
        if(!isset($country)) {
            throw new SimplCustomHttpBadRequest("country is not supported");
        }
        $address["country"] = $country;

        if(!empty($address["state"])) {
            $state = SimplUtil::state_name_from_code($address["state"]);
            $address["state"] = $state;
        }
    
        return  $address;
    }

    function cart_common_payload($cart, $order, $merchant_additional_details)
    {
        $totals = $cart->get_totals();
        $cart_payload = array();
        $cart_payload["total_price"] = wc_format_decimal($cart->get_total('float'), 2);
        $cart_payload["applied_discounts"] = $this->formatted_coupons($cart, $cart->get_coupons(), $order);
        $discount_amount = 0;
        if ($cart->get_total_discount()) {
            $discount_amount = $totals['discount_total'] + $totals['discount_tax'];
        }
        $cart_payload["total_discount"] = wc_format_decimal($discount_amount, 2);
        $cart_payload["total_fee"] = wc_format_decimal($cart->get_fee_total() + $cart->get_fee_tax(), 2);

        if (wc_prices_include_tax()) {
            $cart_payload['tax_included'] = true;
        } else {
            $cart_payload['tax_included'] = false;
        }
        $cart_payload["item_subtotal_price"] = wc_format_decimal($cart->get_subtotal() + (float)$cart->get_subtotal_tax(), 2);

        $cart_payload["total_tax"] = wc_format_decimal($cart->get_total_tax(), 2);
        $cart_payload["checkout_url"] = wc_get_checkout_url();
        $cart_payload["shipping_methods"] = $this->get_shipping_methods($cart);
        $cart_payload["applied_shipping_method"] = $this->get_applied_shipping_method($cart);
        $cart_payload["total_shipping"] = wc_format_decimal($cart->get_shipping_total() + $cart->get_shipping_tax(), 2);        
        $cart_content = $cart->get_cart();
        $cart_payload["items"] = $this->getCartLineItem($cart_content);
        $cart_payload['attributes'] = array();
        $this->add_formatted_cart_fees($cart, $cart_payload);		
        $cart_payload["merchant_additional_details"] = $merchant_additional_details;
        return $cart_payload;
    }


    public function order_payload($order)
    {
        $response = array();
        $response["id"] = $order->get_id();
        $response["total_price"] = wc_format_decimal($order->get_total(), 2);
        $response["items"] = $this->getOrderLineItem($order);
        $response["taxes"] = $order->get_tax_totals();
        $this->add_formatted_order_fees($order, $response);		
        $response["shipping_address"] = $this->convert_address_response($order->get_address('shipping'));
        $response["billing_address"] = $this->convert_address_response($order->get_address('billing'));
        $response["applied_discounts"] = $this->formatted_order_coupons($order);
        $discount_amount = 0;
        if ($order->get_total_discount()) {
            $discount_amount = $order->get_total_discount(false);
        }
        $response["total_discount"] = wc_format_decimal($discount_amount, 2);
        $response["item_subtotal_price"] = wc_format_decimal($order->get_subtotal(), 2);
        $response["total_tax"] = wc_format_decimal($order->get_total_tax(), 2);
        $response["total_shipping"] = wc_format_decimal($order->get_shipping_total() + $order->get_shipping_tax(), 2);
        $response["shipping_methods"] = $this->formatted_shipping_methods($order->get_shipping_methods());
        $response["status"] = $order->get_status();
        $response["is_paid"] = $order->is_paid();
        self::simpl_hide_error_messages(); // HIDE WOOCOMMERCE SUCCESS OR ERROR NOTIFICATION
        return $response;
    }

    protected function get_applied_shipping_method($cart)
    {
        $chosen_shipping_method = $cart->calculate_shipping();
        if (count($chosen_shipping_method) > 0) {
            return $chosen_shipping_method[0]->get_id();
        }
        return "";
    }
	
    protected function add_formatted_cart_fees($cart, &$cart_payload) {
        $applied_fees = array();
        $fees = $cart->get_fees();

		if ( $fees ) {
			foreach ( $fees as $id => $fee ) {
				$fee_name = $fee->name;
                $fee_amount = wc_format_decimal($fee->total, 2);
                array_push( $applied_fees, array( "name" => $fee_name, "type" => $fee_name, "amount" => $fee_amount ));
			}
		}

        $cart_payload["fees"] = $applied_fees;
    }

    protected function formatted_coupons($cart, $coupons, $order)
    {
        $applied_discounts = array();
        $applied_discount_count = 0;
        foreach ($coupons as $coupon_code => $coupon) {
            $applied_discounts[$applied_discount_count] = array(
                "code" => $coupon_code,
                "amount" => wc_format_decimal($cart->get_coupon_discount_amount($coupon_code, false), 2),
                "free_shipping" => $coupon->enable_free_shipping(),
                "type" => simpl_is_auto_applied_coupon($order, $coupon) ? "auto" : ""
            );
            $applied_discount_count += 1;
        }
        return $applied_discounts;
    }

    protected function formatted_order_coupons($order)
    {
        $applied_discounts = array();
        $order_items = $order->get_items('coupon');
        $applied_discount_count = 0;
        foreach ($order_items as $item_id => $item) {
            $coupon_post_obj = get_page_by_title($item->get_name(), OBJECT, 'shop_coupon');
            $coupon_id = $coupon_post_obj->ID;

            // Get an instance of WC_Coupon object (necessary to use WC_Coupon methods)
            $coupon = new WC_Coupon($coupon_id);
            $coupon_type = $coupon->get_discount_type();
            if ($coupon_type == "percent") {
                $order_discount_amount = wc_get_order_item_meta($item_id, 'discount_amount', true);
                $order_discount_tax_amount = wc_get_order_item_meta($item_id, 'discount_amount_tax', true);
                $coupon_amount = $order_discount_amount + $order_discount_tax_amount;
            } else {
                $coupon_amount = $coupon->get_amount();
            }

            $applied_discounts[$applied_discount_count] = array("code" => $coupon->get_code(), "amount" => wc_format_decimal($coupon_amount, 2));
            $applied_discount_count += 1;
        }
        return $applied_discounts;
    }

    protected function add_formatted_order_fees($order, &$response) {

        $applied_fees = array();
        $fees_total = 0;
        $fees = $order->get_fees();

		if ( $fees ) {
			foreach ( $fees as $id => $fee ) {
				$fee_name = $fee->get_name();
                $fee_amount = wc_format_decimal($fee->get_total() + $fee->get_total_tax(), 2);
                array_push( $applied_fees, array( "name" => $fee_name, "type" => $fee_name, "amount" => $fee_amount ) );
                $fees_total += $fee_amount;
			}
		}

        $response["fees"] = $applied_fees;
        $response["total_fee"] = $fees_total;
    }

    protected function formatted_shipping_methods($shipping_methods)
    {
        $shipping_methods_array = array();
        foreach ($shipping_methods as $item_id => $item) {
            $shipping_methods_array["id"] = $item->get_id();
            $shipping_methods_array["slug"] = $item->get_method_id();
            $shipping_methods_array["name"] = $item->get_name();
            $shipping_methods_array["amount"] = wc_format_decimal($item->get_total(), 2);
            $shipping_methods_array["total_tax"] = wc_format_decimal($item->get_total_tax(), 2);
            $shipping_methods_array["taxes"] = $item->get_taxes();
        }
        return $shipping_methods_array;
    }

    function get_shipping_methods($cart)
    {
        $cart->calculate_shipping();
        $shipping_methods_count = 0;
        $shipping_methods_array = array();
        foreach ($cart->get_shipping_packages() as $package_id => $package) {
            // Check if a shipping for the current package exist
            if (WC()->session->__isset('shipping_for_package_' . $package_id)) {
                // Loop through shipping rates for the current package
                foreach (WC()->session->get('shipping_for_package_' . $package_id)['rates'] as $shipping_rate_id => $shipping_rate) {
                    $rate_id     = $shipping_rate->get_id(); // same thing that $shipping_rate_id variable (combination of the shipping method and instance ID)
                    $method_id   = $shipping_rate->get_method_id(); // The shipping method slug
                    $label_name  = $shipping_rate->get_label(); // The label name of the method
                    $cost        = $shipping_rate->get_cost(); // The cost without tax
                    $tax_cost    = $shipping_rate->get_shipping_tax(); // The tax cost
                    $taxes       = $shipping_rate->get_taxes(); // The taxes details (array)
                    $shipping_methods_array[$shipping_methods_count] = array("id" => $rate_id, "slug" => $method_id, "name" => $label_name, "amount" => wc_format_decimal($cost, 2), "total_tax" => wc_format_decimal($tax_cost, 2), "taxes" => $taxes);
                    $shipping_methods_count += 1;
                }
            }
        }
        return $shipping_methods_array;
    }

    function getOrderLineItem($order)
    {
        $i = 0;

        foreach ($order->get_items() as $item_id => $item) {
            $product =  wc_get_product($item['product_id']);
            $price = (float)$item['line_subtotal'] + (float)$item['line_subtotal_tax'];
            $data[$i]['id'] = $item->get_id();
            $data[$i]['sku'] = $product->get_sku();
            $data[$i]['quantity'] = (int)$item['quantity'];
            $data[$i]['title'] = mb_substr($product->get_title(), 0, 125, "UTF-8");
            $data[$i]['description'] = mb_substr($product->get_title(), 0, 250, "UTF-8");
            $productImage = $product->get_image_id() ?? null;
            $data[$i]['image'] = $productImage ? wp_get_attachment_url($productImage) : null;
            $data[$i]['url'] = $product->get_permalink();
            $data[$i]['price'] = wc_format_decimal((empty($product->get_price()) === false) ? $price / $item['quantity'] : 0, 2);
            $data[$i]['variant_id'] = $item['variation_id'];
            $data[$i]['product_id'] = $item['product_id'];
            $data[$i]['product_category'] = $this->simpl_get_product_category($product->get_id());
            $data[$i]['attributes'] = empty($item['variation']) ? null : $item['variation'];
            $data[$i]['offer_price'] = (empty($productDetails['sale_price']) === false) ? wc_format_decimal((float)$productDetails['sale_price'], 2) : wc_format_decimal($price / $item['quantity'], 2);
            $i++;
        }

        return $data;
    }


    protected function getCartLineItem($cart)
    {
        $i = 0;

        foreach ($cart as $item_id => $item) {
            $product =  wc_get_product($item['product_id']);
            $price = (float)$item['line_subtotal'] + (float)$item['line_subtotal_tax'];
            $data[$i]['id'] = (string)$item['product_id'] . (string)$item['variation_id'];
            $data[$i]['sku'] = $product->get_sku();
            $data[$i]['quantity'] = (int)$item['quantity'];
            $data[$i]['title'] = mb_substr($product->get_title(), 0, 125, "UTF-8");
            $data[$i]['description'] = mb_substr($product->get_title(), 0, 250, "UTF-8");
            $productImage = $product->get_image_id() ?? null;
            $data[$i]['image'] = $productImage ? wp_get_attachment_url($productImage) : null;
            $data[$i]['url'] = $product->get_permalink();
            $data[$i]['price'] = wc_format_decimal((empty($product->get_price()) === false) ? $price / $item['quantity'] : 0, 2);
            $data[$i]['variant_id'] = $item['variation_id'];
            $data[$i]['product_id'] = $item['product_id'];
            $data[$i]['product_category'] = $this->simpl_get_product_category($product->get_id());
            $data[$i]['attributes'] = empty($item['variation']) ? null : $item['variation'];
            $data[$i]['offer_price'] = wc_format_decimal((empty($productDetails['sale_price']) === false) ? (float) $productDetails['sale_price'] : $price / $item['quantity'], 2);

            $item_data = $this->get_item_data($item);
            $data[$i]['item_data'] = $item_data;

            $i++;
        }

        return $data;
    }

    protected function get_item_data($item) {
        $item_data = [];
        foreach ($item as $item_key => $item_value) {
            if (in_array($item_key, DEFAULT_WC_ORDER_FIELDS)) {
                continue;
            }

            $item_data[$item_key] = $item_value;
        }

        return $item_data;
    }

    protected function simpl_get_product_category($product_id) {
        $product_categories = wp_get_post_terms($product_id,'product_cat',array('fields'=>'names'));
        if(isset($product_categories) && count($product_categories) > 0) {
            $product_category = htmlspecialchars_decode($product_categories[0]);
            return ($product_category == 'Uncategorized') ? "" : $product_category;
        }
        return "";
    }

    //This function will clear all type of notice or success
    protected function simpl_hide_error_messages()
    {

        $_SESSION["simpl_session_message"] = [];
        WC()->session->set('wc_notices', null);
        add_filter('woocommerce_notice_types', '__return_empty_array');
        add_filter('wc_add_to_cart_message_html', '__return_false');
        add_filter('woocommerce_cart_item_removed_notice_type', '__return_false');
        add_filter('woocommerce_coupon_message', '');
        wc_clear_notices();
    }
}
