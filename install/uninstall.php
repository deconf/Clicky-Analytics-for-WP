<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

class CAWP_Uninstall {

	public static function uninstall() {
		global $wpdb;
		$cawp = CAWP();
		if ( is_multisite() ) { // Cleanup Network install
			foreach ( CAWP_Tools::get_sites( array( 'number' => apply_filters( 'cawp_sites_limit', 100 ) ) ) as $blog ) {
				switch_to_blog( $blog['blog_id'] );
				$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%%cawp_cache_%%'" );
				delete_option( 'cawp_options' );
				restore_current_blog();
			}
			delete_site_option( 'cawp_network_options' );
		} else { // Cleanup Single install
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%%cawp_cache_%%'" );
			delete_option( 'cawp_options' );
		}
		CAWP_Tools::unset_cookie( 'default_metric' );
		CAWP_Tools::unset_cookie( 'default_dimension' );
		CAWP_Tools::unset_cookie( 'default_view' );
		CAWP_Tools::unset_cookie( 'default_swmetric' );

		$timestamp = wp_next_scheduled( 'cawp_expired_cache_hook' );
		wp_unschedule_event( $timestamp, 'cawp_expired_cache_hook' );


	}
}
