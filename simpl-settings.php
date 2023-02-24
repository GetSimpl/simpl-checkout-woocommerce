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


    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
    }

    public static function get_settings() {

        $settings = array(
            'section_title' => array(
                'name'     => __( 'API credentials', 'woocommerce-settings-tab-simpl' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_simpl_api_creds_section'
            ),
            'api_key' => array(
                'name' => __( 'API key', 'woocommerce-settings-tab-simpl' ),
                'type' => 'text',
                'desc' => __( 'Enter your API key', 'woocommerce-settings-tab-simpl' ),
                'id'   => 'wc_settings_tab_simpl_api_key'
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_tab_simpl_api_creds_section_end'
            ),
            'section_title_2' => array(
                'name'     => __( 'Button configurations', 'woocommerce-settings-tab-simpl' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_simpl_button_section'
            ),
            'simpl_button_text' => array(
                'name' => __( 'Button text', 'woocommerce-settings-tab-simpl' ),
                'type' => 'text',
                'desc' => __( 'Enter button place holder', 'woocommerce-settings-tab-simpl' ),
                'id'   => 'wc_settings_tab_simpl_button_text'
            ),
            'simpl_button_activated' => array(
                'name' => __( 'Activate', 'woocommerce-settings-tab-simpl' ),
                'type' => 'checkbox',
                'desc' => __( 'activate simpl checkout button', 'woocommerce-settings-tab-simpl' ),
                'id'   => 'wc_settings_tab_simpl_button_activated'
            ),            
            'section_end_2' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_tab_simpl_button_section_end'
            )
        );

        return apply_filters( 'wc_settings_tab_simpl_settings', $settings );
    }

}

WC_Simpl_Settings::init();