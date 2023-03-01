<?php
class WC_Simpl_Settings {
    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_settings_tab_simpl', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_settings_tab_simpl', __CLASS__ . '::update_settings' );
    }
    
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_tab_simpl'] = __( 'Simpl settings', 'woocommerce-settings-tab-simpl' );
        return $settings_tabs;
    }


    public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }

    public static function GetSimplHost() {
        $staging_env = get_option("wc_settings_tab_simpl_test_env");
        if($staging_env == "yes") {
            return SIMPL_CONFIG_STAGING_URL;
        }
        
        return SIMPL_CONFIG_PRODUCTION_URL;
    }

    public static function IsSimplButtonEnabled() {
        return get_option("wc_settings_tab_simpl_button_activated") == 'yes';
    }

    public static function IsSimplEnabledForAdmin() {
        return get_option("wc_settings_tab_simpl_enabled_to_admin") == 'yes';
    }

    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
    }

    public static function get_settings() {        
        echo(self::GetSimplHost());
        
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

        $simpl_authorized = get_option("simpl_authorized");
        if($simpl_authorized != "true") {
            $settings['api_key'] = array(
                'name' => __( 'Click below link to proceed', 'woocommerce-settings-tab-simpl' ),
                'type' => 'title',
                'desc' => __( '<a class = "button-primary" href = "/wc-auth/v1/authorize?app_name=simpl_wordpress_integration&scope=read_write&user_id=simpl_user_id&return_url=localhost:3000/wp-json/wc-simpl/v1/authenticate_simpl&callback_url=webhook.site/201ead30-af8d-43df-b7c3-22a65d457995">Generate API key for Simpl</a>', 'woocommerce-settings-tab-simpl' ),
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

        $settings['simpl_button_text'] = array(
            'name' => __( 'Button text', 'woocommerce-settings-tab-simpl' ),
            'type' => 'text',
            'desc' => __( 'Enter button place holder', 'woocommerce-settings-tab-simpl' ),
            'id'   => 'wc_settings_tab_simpl_button_text'
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
        return apply_filters( 'wc_settings_tab_simpl_settings', $settings );
    }
}

WC_Simpl_Settings::init();