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
		
		$table_rates_result = $wpdb->get_results(
			"SELECT rate_id, rate_class, rate_min, rate_max, rate_cost, shipping_method_id
			FROM {$wpdb->prefix}woocommerce_shipping_table_rates
			WHERE rate_condition = 'price'"
		);
		
		foreach ( $package['contents'] as $item ) {
			// Get WooCommerce product
			$product = $item['data'];
			if ( empty( $product ) ) {
				continue;
			}
			
			// Get product price
			$product_price = $product->get_price();
			// Calculate line price
			$line_price = $product_price * $item['quantity'];
			
			// Loop through table rates
			foreach  ( $table_rates_result as $table_rate ) {
				$shipping_classes = get_the_terms( $product->id, 'product_shipping_class' );
				if ( $shipping_classes && ! is_wp_error( $shipping_classes ) && isset( $shipping_classes[0] ) ) {
					// Check shipping class
					if ( $table_rate->rate_class == $shipping_classes[0]->term_id ) {
						// Check price
						if ( $table_rate->rate_min <= $line_price && $table_rate->rate_max >= $line_price ) {
							$rate = array(
								'id' => $this->id . ':' . $table_rate->rate_id,
								'label' => "Autoship Table Rate",
								'cost' => $table_rate->rate_cost,
								'calc_tax' => 'per_item'
							);
							$this->add_rate( $rate );
						}
					}
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