<?php
function authenticate_simpl( WP_REST_Request $request ) {    
    //POST call to simpl with credentials
    //adds header related to shop domain
    add_option("simpl_authorized", "true");
    echo(json_encode($request->get_body_params()));
}
?>