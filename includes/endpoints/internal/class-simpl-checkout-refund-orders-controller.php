<?php
class SimplCheckoutRefundOrderController 
{
    function fetch() {
        try {
            SimplRequestValidator::validate_fetch_order_request($request);
            $refund_order_ids = sv_get_wc_orders_with_refunds()

            $response_payload = array()
            for ($i=0; $i < $refund_order_ids; $i++) { 
                $order = wc_get_order((int)$request->get_params()["order_id"]);
                $si = new SimplCartResponse();
                $order_payload = $si->order_payload($order);   
                array_push($response_payload, $order_payload) 
            }
            
            return $response_payload;
        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in fetching order'), 500);
        }
    }

    function sv_get_wc_orders_with_refunds() {

        $query_args = array(
            'fields'         => 'id=>parent',
            'post_type'      => 'shop_order_refund',
            'post_status'    => 'any',
            'posts_per_page' => -1,
        );
    
        $refunds = get_posts( $query_args );
        
        return array_values( array_unique( $refunds ) );
    }    
}

?>