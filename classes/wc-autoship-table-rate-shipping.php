<?php
class WC_Autoship_Table_Rate_Shipping extends WC_Shipping_Method {
	/**
	 * Constructor for your shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->id = 'wc_autoship_table_rate_shipping';
		$this->title = __( 'WC Auto-Ship Table Rates', 'wc-autoship' );
		$this->method_description = __( 'Extends Table Rate Shipping for compatibility with WC Auto-Ship' ); // 
		$this->enabled = "yes";
		$this->has_settings = false;
		$this->init();
	}

	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 */
	function init() {
		// Load the settings API
		$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
		$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

		// Save settings in admin if you have any defined
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping( $package ) {
		global $wpdb;
		
		$shipping_methods_results = $wpdb->get_results(
			"SELECT shipping_method_id
			FROM {$wpdb->prefix}woocommerce_shipping_zone_shipping_methods
			WHERE shipping_method_type = 'table_rate'"
		);
		
		foreach ( $shipping_methods_results as $shipping_method ) {
		
			$table_rate_settings = get_option( 'woocommerce_table_rate-' . $shipping_method->shipping_method_id . '_settings' );
			
			$table_rates_result = $wpdb->get_results( $wpdb->prepare(
				"SELECT rate_id, rate_class, rate_min, rate_max, rate_cost, shipping_method_id
				FROM {$wpdb->prefix}woocommerce_shipping_table_rates
				WHERE shipping_method_id = %d AND rate_condition = 'price'",
				$shipping_method->shipping_method_id
			) );
			
			// Get shipping classes
			$rate_shipping_classes = array();
			foreach  ( $table_rates_result as $table_rate ) {
				$rate_shipping_classes[ $table_rate->rate_class ] = true;
			}
			
			// Calculate cart total
			$cart_total = 0.0;
			foreach ( $package['contents'] as $item ) {
				// Get WooCommerce product
				$product = $item['data'];
				if ( empty( $product ) ) {
					continue;
				}
				$shipping_classes = get_the_terms( $product->id, 'product_shipping_class' );
				if ( $shipping_classes && ! is_wp_error( $shipping_classes ) && isset( $shipping_classes[0] ) && key_exists( $shipping_classes[0]->term_id, $rate_shipping_classes ) ) {
					$cart_total += $item['line_total'];
				}
			}

			// Loop through table rates
			foreach  ( $table_rates_result as $table_rate ) {
				// Check price
				if ( ( empty( $table_rate->rate_min ) || $table_rate->rate_min <= $cart_total ) 
						&& ( empty( $table_rate->rate_max ) || $table_rate->rate_max >= $cart_total ) ) {
					$rate = array(
						'id' => $this->id,
						'label' => $table_rate_settings['title'] . ' (Autoship)',
						'cost' => $table_rate->rate_cost,
						'taxes' => ( $table_rate_settings['tax_status'] == 'taxable' ) ? '' : false,
						'calc_tax' => 'per_order',
					);
					$this->add_rate( $rate );
					break;
				}
			}
			
		}
		
	}
	
	/**
	 * is_available function.
	 *
	 * @param array $package
	 * @return bool
	 */
	public function is_available( $package ) {
		return true;
	}
}