<?php

class WC_Simpl_Settings {

	public static function init() {
		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_settings_tab_simpl', __CLASS__ . '::settings_tab' );
		add_action( 'woocommerce_update_options_settings_tab_simpl', __CLASS__ . '::update_settings' );

		// add css to admin panel
		function register_simpl_admin_style() {
			wp_register_style( 'simpl-admin-style', plugins_url( 'css/simpl-admin.css', __FILE__ ), false, '1.0.0', 'all' );
			wp_enqueue_script( 'simpl-admin-js', plugin_dir_url( __FILE__ ) . 'js/simpl-admin.js', false, false, true );
		}

		add_action( 'admin_init', 'register_simpl_admin_style' );
		function enqueue_simpl_style() {
			wp_enqueue_style( 'simpl-admin-style' );
		}

		add_action( 'admin_enqueue_scripts', 'enqueue_simpl_style' );

		// This will add Custom class on body TAG
		add_filter( 'admin_body_class', static function ( $classes ) {
			$classes = explode( ' ', $classes );
			$page    = ! empty( $_GET['page'] ) ? $_GET['page'] : '';
			$tab     = ! empty( $_GET['tab'] ) ? $_GET['tab'] : '';
			$classes = array_merge( $classes, [ $page, $tab ] );
			$classes = array_filter( $classes );

			return implode( ' ', array_unique( $classes ) );
		} );

	}

	public static function add_settings_tab( $settings_tabs ) {
		$settings_tabs['settings_tab_simpl'] = __( 'Simpl Checkout', 'woocommerce-settings-tab-simpl' );

		return $settings_tabs;
	}


	public static function settings_tab() {
		woocommerce_admin_fields( self::get_settings() );
	}

	public static function simpl_host() {
		$staging_env = get_option( "wc_settings_tab_simpl_test_env" );
		if ( $staging_env == "yes" ) {
			return SIMPL_CONFIG_STAGING_URL;
		}

		return SIMPL_CONFIG_PRODUCTION_URL;
	}

	public static function test_mode_enabled() {
		$staging_env = get_option( "wc_settings_tab_simpl_test_env" );

		return $staging_env == "yes";
	}

	public static function sentry_environment() {
		$staging_env = get_option( "wc_settings_tab_simpl_test_env" );

		if($staging_env == "yes") {
			return 'staging';
		} else {
			return 'production';
		}
	}

	public static function widget_script_url() {
		if ( SIMPL_ENV == "localhost" ) {
			return WIDGET_SCRIPT_LOCALHOST;
		}
		$staging_env = get_option( "wc_settings_tab_simpl_test_env" );
		if ( $staging_env == "yes" ) {
			return WIDGET_SCRIPT_STAGING_URL;
		}

		return WIDGET_SCRIPT_PRODUCTION_URL;
	}

	public static function is_localhost() {
		return SIMPL_ENV == "localhost";
	}

	public static function simpl_authorized_flag_key() {
		$staging_env = get_option( "wc_settings_tab_simpl_test_env" );
		if ( $staging_env == "yes" ) {
			return "simpl_test_authorized";
		}

		return "simpl_authorized";
	}

	//Disable button for users when test mode is enabled
	public static function is_simpl_button_enabled() {
		return get_option( "wc_settings_tab_simpl_button_activated" ) == 'yes';
	}

	public static function can_display_in_pdp_page() {
		return get_option( "wc_settings_tab_simpl_button_pdp_activated" ) == 'yes';
	}

	public static function can_display_in_collections_page() {
		return get_option( "wc_settings_tab_simpl_button_collections_activated" ) == 'yes';
	}

	public static function can_display_in_cart_page() {
		return get_option( "wc_settings_tab_simpl_button_cart_activated" ) == 'yes';
	}

	public static function cta_position_in_pdp() {
		return get_option( "wc_settings_tab_simpl_button_position_pdp" );
	}

	public static function cta_position_in_cart() {
		return get_option( "wc_settings_tab_simpl_button_position_cart" );
	}

	public static function cta_text() {
		return get_option( "wc_settings_tab_simpl_button_text" );
	}

	public static function merchant_credentials() {
		return array( "client_id"     => get_option( "wc_settings_tab_simpl_merchant_client_id" ),
		              "client_secret" => get_option( "wc_settings_tab_simpl_merchant_client_secret" )
		);
	}

	public static function cta_color() {
		return get_option( "wc_settings_tab_simpl_button_bg" );
	}

	public static function is_simpl_enabled_for_admins() {
		return get_option( "wc_settings_tab_simpl_enabled_to_admin" ) == 'yes';
	}

	public static function store_url() {
		return parse_url( get_site_url(), PHP_URL_HOST );
	}

	public static function store_url_with_prefix() {
		return get_site_url();
	}

	public static function update_settings() {

		$existingSetting    = self::simpl_get_all_latest_settings();
		$simplSettingsField = array(
			"merchant_client_id"     => $_POST["wc_settings_tab_simpl_merchant_client_id"] ?? '',
			"merchant_client_secret" => $_POST["wc_settings_tab_simpl_merchant_client_secret"] ?? '',
			"button_position_pdp"    => $_POST["wc_settings_tab_simpl_button_position_pdp"] ?? '',
			"button_position_cart"   => $_POST["wc_settings_tab_simpl_button_position_cart"] ?? '',

			"test_env"              => ! isset( $_POST["wc_settings_tab_simpl_test_env"] ) ? 0 : 1,
			"button_activated"      => ! isset( $_POST["wc_settings_tab_simpl_button_activated"] ) ? 0 : 1,
			"button_pdp_activated"  => ! isset( $_POST["wc_settings_tab_simpl_button_pdp_activated"] ) ? 0 : 1,
			"button_cart_activated" => ! isset( $_POST["wc_settings_tab_simpl_button_cart_activated"] ) ? 0 : 1,
			"enabled_to_admin"      => ! isset( $_POST["wc_settings_tab_simpl_enabled_to_admin"] ) ? 0 : 1,
		);

		woocommerce_update_options( self::get_settings() );
		self::is_valid_credentials( true );
		if ( serialize($existingSetting) != serialize($simplSettingsField)) {
			$simpl_host = WC_Simpl_Settings::simpl_host();
			$event_data = array(
				"merchant_id" => $simpl_host,
			);
			$event_name = "Update settings";
			$event_data = array_merge($event_data, self::latest_settings());
			$entity = "Manage settings";
			$flow = "Merchant woocommerce-admin page";
			$simplHttpResponse = SimplWcEventHelper::publish_event($event_name, $event_data, $entity, $flow);
			if (!is_wp_error($simplHttpResponse)) {
				$body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
			} else {
				$error_message = $simplHttpResponse->get_error_message();
				throw new Exception( $error_message );
			}
		}
	}

	// its for fetch all Config of simpl checkout
	// TODO: check if any better option available and get this thing done.
	protected static function simpl_get_all_latest_settings() {
		return array(
			"merchant_client_id"     => get_option( "wc_settings_tab_simpl_merchant_client_id" ),
			"merchant_client_secret" => get_option( "wc_settings_tab_simpl_merchant_client_secret" ),
			"button_position_pdp"    => get_option( "wc_settings_tab_simpl_button_position_pdp" ),
			"button_position_cart"   => get_option( "wc_settings_tab_simpl_button_position_cart" ),
			"test_env"               => get_option( "wc_settings_tab_simpl_test_env" ) == 'yes' ? 1 : 0,
			"button_activated"       => get_option( "wc_settings_tab_simpl_button_activated" ) == 'yes' ? 1 : 0,
			"button_pdp_activated"   => get_option( "wc_settings_tab_simpl_button_pdp_activated" ) == 'yes' ? 1 : 0,
			"button_cart_activated"  => get_option( "wc_settings_tab_simpl_button_cart_activated" ) == 'yes' ? 1 : 0,
			"enabled_to_admin"       => get_option( "wc_settings_tab_simpl_enabled_to_admin" ) == 'yes' ? 1 : 0,
		);
	}

	protected static function latest_settings() {
		return array(
			"merchant_client_id"     => get_option( "wc_settings_tab_simpl_merchant_client_id" ),
			"merchant_client_secret" => substr(get_option( "wc_settings_tab_simpl_merchant_client_secret" ),-3),
			"test_env"               => get_option( "wc_settings_tab_simpl_test_env" ),
			"button_activated"       => get_option( "wc_settings_tab_simpl_button_activated" ),
		);
	}

	public static function reset_settings() {
		delete_option( "wc_settings_tab_simpl_button_bg" );
		delete_option( "wc_settings_tab_simpl_test_env" );
		delete_option( "wc_settings_tab_simpl_button_activated" );
		delete_option( "wc_settings_tab_simpl_merchant_client_id" );
		delete_option( "wc_settings_tab_simpl_merchant_client_secret" );
	}

	public static function get_settings() {
		$valid_credentials = self::is_valid_credentials();
		$simplTabDomain    = 'woocommerce-settings-tab-simpl';
		$doneDOM           = '<span class="status-enabled-simpl simpl-accordian-mapping"></span>';
		$errorDOM          = '<span class="status-disabled-simpl simpl-accordian-mapping"></span>';
		$dummyDom          = '<span class="simpl-accordian-mapping"></span>';

		$step1Validate = $valid_credentials ? $doneDOM : $errorDOM;

		$settings   = [];
		$settings[] = array(
			'name' => __( 'Configure Simpl', 'woocommerce-settings-tab-simpl' ),
			'type' => 'title',
			'desc' => '',
			'id'   => 'wc_settings_tab_simpl_api_creds_section'
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'configure_simpl'
		);
		$settings[] = array(
			'title' => __( 'Configure your Merchant Client ID and Merchant Client Secret', $simplTabDomain ),
			'type'  => 'title',
			'desc'  => __( "$step1Validate Merchant Client ID and Merchant Client Secret can be retrieved from 'Simpl Merchant Dashboard'", 'woocommerce' ),
			'id'    => 'checkout_endpoint_options_step1',
		);

		$settings[] = array(
			'name'     => __( 'Enable test mode', $simplTabDomain ),
			'type'     => 'checkbox',
			'id'       => 'wc_settings_tab_simpl_test_env',
			'desc'     => 'It can be used to enable sandbox',
			'desc_tip' => true,
		);

		$settings[] = array(
			'title'    => esc_html__( 'Merchant Client ID', $simplTabDomain ),
			'type'     => 'password',
			'desc'     => 'This identifies the merchant and is obtained post merchant onboarding',
			'desc_tip' => true,
			'id'       => 'wc_settings_tab_simpl_merchant_client_id'
		);

		$settings[] = array(
			'title'    => esc_html__( 'Merchant Client Secret', $simplTabDomain ),
			'type'     => 'password',
			'desc'     => "Confidential code used to verify the client's identity and ensure the security. Not to be shared with anyone",
			'desc_tip' => true,
			'id'       => 'wc_settings_tab_simpl_merchant_client_secret'
		);


		if ( $valid_credentials ) {
			$endpoint      = '/wc-auth/v1/authorize?';
			$params        = [
				'app_name'     => 'simpl_wordpress_integration',
				'scope'        => 'read_write',
				'user_id'      => 2,
				'return_url'   => self::store_url_with_prefix() . "/wp-admin/admin.php?page=wc-settings&tab=settings_tab_simpl",
				'callback_url' => self::store_url_with_prefix() . "/wp-json/wc-simpl/v1/authenticate_simpl"
			];
			$query_string  = http_build_query( $params );
			$auth_endpoint = self::store_url_with_prefix() . $endpoint . $query_string;

			$settings[] = array(
				'name' => __( 'Enable simpl for store admins', $simplTabDomain ),
				'type' => 'checkbox',
				'id'   => 'wc_settings_tab_simpl_enabled_to_admin'
			);

			$settings[]       = array(
				'type' => 'sectionend',
				'id'   => 'wc_settings_tab_simpl_api_creds_section_end'
			);
			$simpl_authorized = get_option( self::simpl_authorized_flag_key() );
			$step2Validate    = $simpl_authorized ? $doneDOM : $errorDOM;

			$settings[] = array(
				'title' => __( 'Authorize access for Simpl', $simplTabDomain ),
				'type'  => 'title',
				'desc'  => __( "$step2Validate Click below button to provide permissions to Simpl", $simplTabDomain ),
				'id'    => 'checkout_endpoint_options',
			);


			$settings[] = array(
				'type' => 'title',
				'desc' => __( $simpl_authorized ? '<button class = "button-primary" disabled>Authorized</button>' : '<a class = "button-primary" href = "' . $auth_endpoint . '">Authorize Simpl</a>', $simplTabDomain ),
				'id'   => 'wc_settings_tab_simpl_api_key'
			);

		}

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'wc_settings_tab_simpl_api_step2_section_end'
		);


		if ( $valid_credentials ) {
			$settings[] = array(
				'title' => __( 'Configure Simpl Checkout Button Visibility', $simplTabDomain ),
				'type'  => 'title',
				'desc'  => __( $dummyDom . 'Enable/disable sections where you want to display button', 'woocommerce' ),
				'id'    => 'wc_settings_tab_simpl_button_visibility',
			);
			$settings[] = array(
				'name' => __( 'Product Page', 'woocommerce-settings-tab-simpl' ),
				'type' => 'checkbox',
				'desc' => __( 'Show simpl checkout button in Product page', 'woocommerce-settings-tab-simpl' ),
				'id'   => 'wc_settings_tab_simpl_button_pdp_activated'
			);

			$settings[] = array(
				'name'    => __( 'Collections Pages', 'woocommerce-settings-tab-simpl' ),
				'type'    => 'hidden',
				'desc'    => __( 'Show simpl checkout button in Collections page', 'woocommerce-settings-tab-simpl' ),
				'id'      => 'wc_settings_tab_simpl_button_collections_activated',
				'default' => 'no',
				'value'   => 'no'
			);

			$settings[] = array(
				'name' => __( 'Cart Page', 'woocommerce-settings-tab-simpl' ),
				'type' => 'checkbox',
				'desc' => __( 'Show simpl checkout button in Cart page', 'woocommerce-settings-tab-simpl' ),
				'id'   => 'wc_settings_tab_simpl_button_cart_activated'
			);

			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'wc_settings_tab_simpl_button_section_end',
			);

			$settings[] = array(
				'title' => __( 'Configure Simpl Checkout Button Position', $simplTabDomain ),
				'type'  => 'title',
				'desc'  => __( $dummyDom . 'Display simpl button above/below the add to cart button', 'woocommerce' ),
				'id'    => 'wc_settings_tab_simpl_button_section_configuration',
			);

			$settings[] = array(
				'name'     => __( 'Button Position in Product Page', 'woocommerce-settings-tab-simpl' ),
				'type'     => 'select',
				'id'       => 'wc_settings_tab_simpl_button_position_pdp',
				'options'  => array(
					'woocommerce_after_add_to_cart_button'  => 'After add to cart button',
					'woocommerce_before_add_to_cart_button' => 'Before add to cart button'
				),
				'desc'     => "This position will be relative to the 'Add to Cart' button",
				'desc_tip' => true,
				'default'  => 'woocommerce_before_add_to_cart_button'
			);

			$settings[] = array(
				'name'     => __( 'Button Position in Cart Page', 'woocommerce-settings-tab-simpl' ),
				'type'     => 'select',
				'id'       => 'wc_settings_tab_simpl_button_position_cart',
				'options'  => array(
					'woocommerce_proceed_to_checkout' => 'Before Proceed to checkout',
					'woocommerce_after_cart_totals'   => 'After Proceed to checkout'
				),
				'default'  => 'woocommerce_proceed_to_checkout',
				'desc'     => "This position will be relative to the 'Proceed to Checkout' button",
				'desc_tip' => true,
			);

			$settings[] = array(
				'name'    => __( 'Button text', 'woocommerce-settings-tab-simpl' ),
				'type'    => 'hidden',
				'desc'    => __( 'select button place holder', 'woocommerce-settings-tab-simpl' ),
				'id'      => 'wc_settings_tab_simpl_button_text',
				'options' => array(
					''                     => 'Default',
					'Buy Now'              => 'Buy Now',
					'Buy It Now'           => 'Buy It Now',
					'Checkout with Simpl'  => 'Checkout with Simpl',
					'Buy with UPI/COD'     => 'Buy with UPI/COD',
					'Buy with UPI'         => 'Buy with UPI',
					'Quick Buy'            => 'Quick Buy',
					'Order Now'            => 'Order Now',
					'Checkout'             => 'Checkout',
					'UPI / Pay-in-3 / COD' => 'UPI / Pay-in-3 / COD'
				),
				'default' => ''
			);

			$settings[] = array(
				'name'    => __( 'Button background', 'woocommerce-settings-tab-simpl' ),
				'type'    => 'hidden',
				'desc'    => __( 'Enter button background color', 'woocommerce-settings-tab-simpl' ),
				'id'      => 'wc_settings_tab_simpl_button_bg',
				'default' => '',
				'value'   => ''
			);

			$settings[] = array(
				'name' => __( 'Activate', 'woocommerce-settings-tab-simpl' ),
				'type' => 'checkbox',
				'desc' => __( 'Activate simpl checkout button', 'woocommerce-settings-tab-simpl' ),
				'id'   => 'wc_settings_tab_simpl_button_activated'
			);

			$settings[] = array(
				'type' => 'sectionend',
				'id'   => 'wc_settings_tab_simpl_button_section_end'
			);
		}

		return apply_filters( 'wc_settings_tab_simpl_settings', $settings );
	}

	protected static function is_valid_credentials( $showMessage = false ) {
		// return true;
		$client_credentials = self::merchant_credentials();
		$simplHttpResponse  = wp_remote_get( "https://" . self::simpl_host() . "/api/v1/wc/app/verify", array(
			"headers" => array(
				"shop_domain"   => self::store_url(),
				"client_id"     => $client_credentials["client_id"],
				"client_secret" => $client_credentials["client_secret"],
				"sentry_monitoring_enabled" => (get_option(SIMPL_SENTRY_DSN_KEY) != "" ? "true": "false"),
				"content-type"  => "application/json"
			),
		) );

		if ( ! is_wp_error( $simplHttpResponse ) ) {
			$body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
			if ( $body["success"] ) {
				if(isset($body["data"]) && isset($body["data"]["config"]) && isset($body["data"]["config"][SIMPL_SENTRY_DSN_KEY]) && $body["data"]["config"][SIMPL_SENTRY_DSN_KEY] != "") {
					simpl_set_sentry_client($body["data"]["config"][SIMPL_SENTRY_DSN_KEY]);
				}
				return true;
			}
		}

		if ( $showMessage ) {
			WC_Admin_Settings::add_error( esc_html__( ucfirst( $body['error']['message'] ), 'woocommerce-settings-tab-simpl' ) );
		}

		return false;
	}
}

WC_Simpl_Settings::init();