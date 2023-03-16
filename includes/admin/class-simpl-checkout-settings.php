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

    public static function widgetScriptUrl() {
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

    public static function IsSimplButtonEnabled() {
        return get_option("wc_settings_tab_simpl_button_activated") == 'yes';
    }

    public static function showInPdpPage() {
        return get_option("wc_settings_tab_simpl_button_pdp_activated") == 'yes';
    }

    public static function showInCollectionsPage() {
        return get_option("wc_settings_tab_simpl_button_collections_activated") == 'yes';
    }

    public static function showInCartPage() {
        return get_option("wc_settings_tab_simpl_button_cart_activated") == 'yes';
    }

    public static function ctaPositionPdp() {
        return get_option("wc_settings_tab_simpl_button_position");
    }

    public static function ctaBgColor() {
        return get_option("wc_settings_tab_simpl_button_bg");
    }

    public static function IsSimplEnabledForAdmin() {
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
    }

    public static function get_settings() {
        echo(wp_create_nonce( 'wc_store_api' ));
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

        $settings['section_end'] = array(
            'type' => 'sectionend',
            'id' => 'wc_settings_tab_simpl_api_creds_section_end'
        );

        $settings['section_title_2'] = array(
            'name'     => __( 'Button configurations', 'woocommerce-settings-tab-simpl' ),
            'type'     => 'title',
            'desc'     => '',
            'id'       => 'wc_settings_tab_simpl_button_section'
        );

        $settings['simpl_button_position'] = array(
            'name' => __( 'Button Position in PDP', 'woocommerce-settings-tab-simpl' ),
            'type' => 'select',
            'id'   => 'wc_settings_tab_simpl_button_position',
            'options' => array(
                'woocommerce_after_add_to_cart_button' => 'After add to cart button',
                'woocommerce_before_add_to_cart_button' => 'Before add to cart button'
            ),
            'value' => 'woocommerce_before_add_to_cart_button'
        );

        $settings['simpl_button_text'] = array(
            'name' => __( 'Button text', 'woocommerce-settings-tab-simpl' ),
            'type' => 'text',
            'desc' => __( 'Enter button place holder', 'woocommerce-settings-tab-simpl' ),
            'id'   => 'wc_settings_tab_simpl_button_text'
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
        return apply_filters( 'wc_settings_tab_simpl_settings', $settings );
    }
}

WC_Simpl_Settings::init();