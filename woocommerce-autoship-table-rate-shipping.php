<?php
/*
Plugin Name: WC Auto-Ship Table Rate Shipping
Plugin URI: http://wooautoship.com
Description: Integrate WC Auto-Ship with Table Rate Shipping
Version: 1.0
Author: Patterns in the Cloud
Author URI: http://patternsinthecloud.com
License: Single-site
*/

define( 'WC_Autoship_Table_Rate_Shipping_Version', '1.0.0' );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce-autoship/woocommerce-autoship.php' ) && is_plugin_active( 'woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php' ) ) {
	
	function wc_autoship_table_rate_shipping_install() {
	}
	register_activation_hook( __FILE__, 'wc_autoship_table_rate_shipping_install' );
	
	function wc_autoship_table_rate_shipping_deactivate() {
	
	}
	register_deactivation_hook( __FILE__, 'wc_autoship_table_rate_shipping_deactivate' );
	
	function wc_autoship_table_rate_shipping_uninstall() {

	}
	register_uninstall_hook( __FILE__, 'wc_autoship_table_rate_shipping_uninstall' );
	
	function wc_autoship_table_rate_shipping_add_methods( $methods ) {
		require_once( 'classes/wc-autoship-table-rate-shipping.php' );
		if ( ! in_array( 'WC_Autoship_Table_Rate_Shipping', $methods ) ) {
			$methods[] = 'WC_Autoship_Table_Rate_Shipping';
		}
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'wc_autoship_table_rate_shipping_add_methods', 10, 1 );
	
	function wc_autoship_table_rate_shipping_remove_method( $is_available ) {
		return false;
	}
	add_filter( 'woocommerce_shipping_table_rate_is_available', 'wc_autoship_table_rate_shipping_remove_method', 10, 1 );
}
