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

if ( ! class_exists( 'CAWP_Config' ) ) {

	final class CAWP_Config {

		public $options;

		public function __construct() {
			global $wp_version;

			/**
			 * Get plugin options
			 */
			$this->get_plugin_options();
			/**
			 * Provide language packs for all available Network languages
			 */
			if ( is_multisite() ) {
				add_filter( 'plugins_update_check_locales', array( $this, 'translation_updates' ), 10, 1 );
			}
			/**
			 * Clear expired cache using WP Cron
			 */
			if ( ! wp_next_scheduled( 'cawp_expired_cache_hook' ) ) {

				/**
				 * WP < 5.3.0 compatibility
				 */
				if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
					$datetime = new DateTime( 'tomorrow', new DateTimeZone(wp_timezone_string()) );
				} else {
					$datetime = new DateTime( 'tomorrow', new DateTimeZone( CAWP_Tools::timezone_string()) );
				}
				$timestamp = $datetime->getTimestamp();

				wp_schedule_event($timestamp, 'daily', 'cawp_expired_cache_hook');

			}

			add_action ( 'cawp_expired_cache_hook', array( $this, 'delete_expired_cache' ) );

		}

		public function delete_expired_cache (){
			CAWP_Tools::delete_expired_cache();
		}

		/**
		 * Helper function to update language packs for an entire network
		 */
		public function translation_updates( $locales ) {
			$languages = get_available_languages();
			return array_values( $languages );
		}

		/**
		 * Validates options before storing
		 */
		private function validate_data( $options ) {
			/* @formatter:off */
			$numerics = array( 	'dashboard_widget',
																							'frontend_item_reports',
																							'backend_item_reports',
																							'cachetime',
																							'tracking',
																							'track_username',
																							'track_email',
																							'track_youtube',
																							'track_html5',
			);
			foreach ( $numerics as $key ) {
				if ( isset( $options[$key] ) ) {
					$options[$key] = (int) $options[$key];
				}
			}

			$texts = array( 'sitekey',
																			'siteid',
																			'maps_api_key',
																			'theme_color',
																			'track_outbound',
			);
			foreach ( $texts as $key ) {
				if ( isset( $options[$key] ) ) {
					$options[$key] = sanitize_text_field( $options[$key] );
				}
			}
			/* @formatter:on */

			return $options;
		}

		/**
		 * Helper function to store options
		 */
		public function set_plugin_options() {

			$options = $this->options;

			update_option( 'cawp_options', json_encode( $this->validate_data( $options ) ) );

		}

		/**
		 * Retrieve plugin options
		 */
		private function get_plugin_options() {

			if ( ! get_option( 'cawp_options' ) ) {
				CAWP_Install::install();
			}
			$this->options = (array) json_decode( get_option( 'cawp_options' ) );
			// Maintain Compatibility
			$this->maintain_compatibility();

		}

		/**
		 * Helps maintaining backwards compatibility
		 */
		private function maintain_compatibility() {
			$flag = false;

			$prevver = get_option( 'cawp_version' );
			if ( $prevver && CAWP_CURRENT_VERSION != $prevver ) {
				$flag = true;
				update_option( 'cawp_version', CAWP_CURRENT_VERSION );
				update_option( 'cawp_got_updated', true );
				CAWP_Tools::clear_cache();
				CAWP_Tools::delete_cache( 'capi_errors' );
			}

			if ( get_option( 'ca_sitekey' ) ){
				$this->options['sitekey'] = get_option( 'ca_sitekey' );
				delete_option( 'ca_sitekey' );
				$flag = true;
			};

			if ( get_option( 'ca_siteid' ) ){
				$this->options['siteid'] = get_option( 'ca_siteid' );
				delete_option( 'ca_siteid' );
				$flag = true;
			};

			if ( get_option( 'ca_track_email' ) ){
				$this->options['track_email'] = get_option( 'ca_track_email' );
				delete_option( 'ca_track_email' );
				$flag = true;
			};

			if ( get_option( 'ca_track_username' ) ){
				$this->options['track_username'] = get_option( 'ca_track_username' );
				delete_option( 'ca_track_username' );
				$flag = true;
			};

			if ( get_option( 'ca_track_youtube' ) ){
				$this->options['track_youtube'] = get_option( 'ca_track_youtube' );
				delete_option( 'ca_track_youtube' );
				$flag = true;
			};

			if ( get_option( 'ca_track_html5' ) ){
				$this->options['track_html5'] = get_option( 'ca_track_html5' );
				delete_option( 'ca_track_html5' );
				$flag = true;
			};

			if ( get_option( 'ca_tracking' ) ){
				$this->options['tracking'] = get_option( 'ca_tracking' );
				delete_option( 'ca_tracking' );
				$flag = true;
			};

			if ( get_option( 'ca_access' ) ){
				$this->options['access_back'] = get_option( 'ca_access' );
				$this->options['access_front'] = get_option( 'ca_access' );
				delete_option( 'ca_access' );
				$flag = true;
			};

			if ( get_option( 'ca_disabledashboard' ) ){
				$this->options['dashboard_widget'] = get_option( 'ca_disabledashboard' );
				delete_option( 'ca_disabledashboard' );
				$flag = true;
			};

			if ( get_option( 'ca_frontend' ) ){
				$this->options['frontend_item_reports'] = get_option( 'ca_frontend' );
				delete_option( 'ca_frontend' );
				$flag = true;
			};

			if ( get_option( 'ca_track_olp' ) ){
				$this->options['track_outbound'] = get_option( 'ca_track_olp' );
				delete_option( 'ca_track_olp' );
				$flag = true;
			};

			if ( get_option( 'ca_pgd' ) ){
				delete_option( 'ca_pgd' );
				delete_option( 'ca_rd' );
				delete_option( 'ca_sd' );
			};

			if ( ! isset( $this->options['theme_color'] ) ) {
				$this->options['theme_color'] = '#2c5fb2';
				$flag = true;
			}

			/* @formatter:off */
			$zeros = array(		'backend_item_reports',
			);
			foreach ( $zeros as $key ) {
				if ( ! isset( $this->options[$key] ) ) {
					$this->options[$key] = 0;
					$flag = true;
				}
			}

			$unsets = array(		'ca_cachetime',
			);
			foreach ( $unsets as $key ) {
				if ( isset( $this->options[$key] ) ) {
					unset( $this->options[$key] );
					$flag = true;
				}
			}

			$empties = array(		'maps_api_key',
			);
			foreach ( $empties as $key ) {
				if ( ! isset( $this->options[$key] ) ) {
					$this->options[$key] = '';
					$flag = true;
				}
			}

			$ones = array();
			foreach ( $ones as $key ) {
				if ( ! isset( $this->options[$key] ) ) {
					$this->options[$key] = 1;
					$flag = true;
				}
			}

			$arrays = array( 	'access_front',
																					'access_back',
			);
			foreach ( $arrays as $key ) {
				if ( ! is_array( $this->options[$key] ) ) {
					$this->options[$key] = array();
					$flag = true;
				}
			}
			/* @formatter:on */

			if ( $flag ) {
				$this->set_plugin_options();
			}
		}
	}
}

