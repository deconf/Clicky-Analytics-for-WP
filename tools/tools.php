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
if ( ! class_exists( 'CAWP_Tools' ) ) {

	class CAWP_Tools {

		/**
		 * Loads ISO 3166 country codes
		 * @return array
		 */
		public static function get_countrycodes() {
			include 'iso3166.php';
			return $country_codes;
		}

		/**
		 * Extract the root domain from a URL
		 * @return string
		 */
		public static function get_root_domain() {
			$url = site_url();
			$root = explode( '/', $url );
			preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', str_ireplace( 'www', '', isset( $root[2] ) ? $root[2] : $url ), $root );
			if ( isset( $root['domain'] ) ) {
				return $root['domain'];
			} else {
				return '';
			}
		}

		/**
		 * Simple function to remove the protocol
		 * @param string $domain
		 * @return string
		 */
		public static function strip_protocol( $domain ) {
			return str_replace( array( "https://", "http://", " " ), "", $domain );
		}

		/**
		 * Generates a color variation of a base color
		 * @param string $colour
		 * @param int $per
		 * @return string
		 */
		public static function colourVariator( $colour, $per ) {
			$colour = substr( $colour, 1 );
			$rgb = '';
			$per = $per / 100 * 255;
			if ( $per < 0 ) {
				// Darker
				$per = abs( $per );
				for ( $x = 0; $x < 3; $x++ ) {
					$c = hexdec( substr( $colour, ( 2 * $x ), 2 ) ) - $per;
					$c = ( $c < 0 ) ? 0 : dechex( (int) $c );
					$rgb .= ( strlen( $c ) < 2 ) ? '0' . $c : $c;
				}
			} else {
				// Lighter
				for ( $x = 0; $x < 3; $x++ ) {
					$c = hexdec( substr( $colour, ( 2 * $x ), 2 ) ) + $per;
					$c = ( $c > 255 ) ? 'ff' : dechex( (int) $c );
					$rgb .= ( strlen( $c ) < 2 ) ? '0' . $c : $c;
				}
			}
			return '#' . $rgb;
		}

		/**
		 * Generates multiple color variations from a base color
		 * @param string $base
		 * @return array
		 */
		public static function variations( $base ) {
			$variations[] = $base;
			$variations[] = self::colourVariator( $base, - 10 );
			$variations[] = self::colourVariator( $base, + 10 );
			$variations[] = self::colourVariator( $base, + 20 );
			$variations[] = self::colourVariator( $base, - 20 );
			$variations[] = self::colourVariator( $base, + 30 );
			$variations[] = self::colourVariator( $base, - 30 );
			return $variations;
		}

		/**
		 * Determines if a user has access to a specific feature
		 * @param string $access_level
		 * @param boolean $flag
		 * @return boolean
		 */
		public static function check_roles( $access_level, $flag = false ) {
			if ( is_user_logged_in() && isset( $access_level ) ) {
				$current_user = wp_get_current_user();
				$roles = (array) $current_user->roles;
				if ( ( current_user_can( 'manage_options' ) ) && ! $flag ) {
					return true;
				}
				if ( count( array_intersect( $roles, $access_level ) ) > 0 ) {
					return true;
				} else {
					return false;
				}
			}
		}

		/**
		 * Cookie cleanup on uninstall
		 * @param string $name
		 */
		public static function unset_cookie( $name ) {
			$name = 'cawp_wg_' . $name;
			setcookie( $name, '', time() - 3600, '/' );
			$name = 'cawp_ir_' . $name;
			setcookie( $name, '', time() - 3600, '/' );
		}

		/**
		 * Cache Helper function. I don't use transients because cleanup plugins can break their functionality
		 * @param string $name
		 * @param mixed $value
		 * @param number $expiration
		 */
		public static function set_cache( $name, $value, $expiration = 0 ) {
			update_option( '_cawp_cache_' . $name, $value, 'no' );
			update_option( '_cawp_cache_timeout_' . $name, time() + (int) $expiration, 'no' );
		}

		/**
		 * Cache Helper function. I don't use transients because cleanup plugins can break their functionality
		 * @param string $name
		 * @param mixed $value
		 * @param number $expiration
		 */
		public static function delete_cache( $name ) {
			delete_option( '_cawp_cache_' . $name );
			delete_option( '_cawp_cache_timeout_' . $name );
		}

		/**
		 * Cache Helper function. I don't use transients because cleanup plugins can break their functionality
		 * @param string $name
		 * @param mixed $value
		 * @param number $expiration
		 */
		public static function get_cache( $name ) {
			$value = get_option( '_cawp_cache_' . $name );
			$expires = get_option( '_cawp_cache_timeout_' . $name );
			if ( false === $value || ! isset( $value ) || ! isset( $expires ) ) {
				return false;
			}
			if ( $expires < time() ) {
				delete_option( '_cawp_cache_' . $name );
				delete_option( '_cawp_cache_timeout_' . $name );
				return false;
			} else {
				return $value;
			}
		}

		/**
		 * Cache Helper function. I don't use transients because cleanup plugins can break their functionality
		 */
		public static function clear_cache() {
			global $wpdb;
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%%cawp_cache_%%'" );
		}

		public static function delete_expired_cache() {
			global $wpdb;
			if ( wp_using_ext_object_cache() ) {
				return;
			}
			$wpdb->query( $wpdb->prepare( "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_cawp_cache_timeout_', SUBSTRING( a.option_name, 13 ) )
			AND b.option_value < %d", $wpdb->esc_like( '_cawp_cache_' ) . '%', $wpdb->esc_like( '_cawp_cache_timeout_' ) . '%', time() ) );
			if ( ! is_multisite() ) {
				// Single site stores site transients in the options table.
				$wpdb->query( $wpdb->prepare( "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT( '_site_cawp_cache_timeout_', SUBSTRING( a.option_name, 18 ) )
				AND b.option_value < %d", $wpdb->esc_like( '_site_cawp_cache_' ) . '%', $wpdb->esc_like( '_site_cawp_cache_timeout_' ) . '%', time() ) );
			} elseif ( is_multisite() && is_main_site() && is_main_network() ) {
				// Multisite stores site transients in the sitemeta table.
				$wpdb->query( $wpdb->prepare( "DELETE a, b FROM {$wpdb->sitemeta} a, {$wpdb->sitemeta} b
				WHERE a.meta_key LIKE %s
				AND a.meta_key NOT LIKE %s
				AND b.meta_key = CONCAT( '_site_cawp_cache_timeout_', SUBSTRING( a.meta_key, 18 ) )
				AND b.meta_value < %d", $wpdb->esc_like( '_site_cawp_cache_' ) . '%', $wpdb->esc_like( '_site_cawp_cache_timeout_' ) . '%', time() ) );
			}
		}

		/**
		 * Loads a view file
		 *
		 * $data parameter will be available in the template file as $data['value']
		 *
		 * @param string $template - Template file to load
		 * @param array $data - data to pass along to the template
		 * @return boolean - If template file was found
		 **/
		public static function load_view( $path, $data = array() ) {
			if ( file_exists( CAWP_DIR . $path ) ) {
				require_once ( CAWP_DIR . $path );
				return true;
			}
			return false;
		}

		/**
		 * Doing it wrong function
		 */
		public static function doing_it_wrong( $function, $message, $version ) {
			if ( WP_DEBUG && apply_filters( 'doing_it_wrong_trigger_error', true ) ) {
				if ( is_null( $version ) ) {
					$version = '';
				} else {
					/* translators: %s: version number */
					$version = sprintf( __( 'This message was added in version %s.', 'clicky-analytics' ), $version );
				}
				/* translators: Developer debugging message. 1: PHP function name, 2: Explanatory message, 3: Version information message */
				trigger_error( sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', 'clicky-analytics' ), $function, $message, $version ) );
			}
		}

		/**
		 * Error management system
		 * @param object $e
		 * @param int $timeout
		 */
		public static function set_error( $e, $timeout ) {
			if ( is_object( $e ) ) {
				if ( method_exists( $e, 'getCode' ) && method_exists( $e, 'getErrors' ) ) {
					$error_code = $e->getCode();
					if ( 500 == $error_code || 503 == $error_code ) {
						$timeout = 60;
					}
					self::set_cache( 'capi_errors', array( $e->getCode(), (array) $e->getErrors(), esc_html( print_r( $e, true ) ) ), $timeout );
				} else {
					self::set_cache( 'capi_errors', array( 600, array(), esc_html( print_r( $e, true ) ) ), $timeout );
				}
			} else if ( is_array( $e ) ) {
				self::set_cache( 'capi_errors', array( 600, array(), esc_html( print_r( $e, true ) ) ), $timeout );
			} else {
				self::set_cache( 'capi_errors', array( 600, array(), esc_html( print_r( $e, true ) ) ), $timeout );
			}
			// Count Errors until midnight
			$midnight = strtotime( "tomorrow 00:00:00" ); // UTC midnight
			$midnight = $midnight + 8 * 3600; // UTC 8 AM
			$tomidnight = $midnight - time();
			$errors_count = self::get_cache( 'errors_count' );
			$errors_count = (int) $errors_count + 1;
			self::set_cache( 'errors_count', $errors_count, $tomidnight );
		}

		/**
		 * Anonymize sensitive data before displaying or reporting
		 * @param array $options
		 * @return string
		 */
		public static function anonymize_options( $options ) {
			global $wp_version;
			if ( defined( 'SCRIPT_DEBUG' ) and SCRIPT_DEBUG ) {
				return $options; // don't hide credentials when DEBUG is enabled
			}
			$options['wp_version'] = $wp_version;
			$options['cawp_version'] = CAWP_CURRENT_VERSION;
			if ( $options['sitekey'] ) {
				$options['sitekey'] = 'HIDDEN';
			}
			if ( $options['siteid'] ) {
				$options['siteid'] = 'HIDDEN';
			}
			return $options;
		}

		/**
		 * System details for the Debug screen
		 * @return string
		 */
		public static function system_info() {
			$info = '';
			// Server Software
			$server_soft = "-";
			if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
				$server_soft = $_SERVER['SERVER_SOFTWARE'];
			}
			$info .= 'Server Info: ' . $server_soft . "\n";
			// PHP version
			if ( defined( 'PHP_VERSION' ) ) {
				$info .= 'PHP Version: ' . PHP_VERSION . "\n";
			} else if ( defined( 'HHVM_VERSION' ) ) {
				$info .= 'HHVM Version: ' . HHVM_VERSION . "\n";
			} else {
				$info .= 'Other Version: ' . '-' . "\n";
			}
			// cURL Info
			if ( function_exists( 'curl_version' ) && function_exists( 'curl_exec' ) ) {
				$curl_version = curl_version();
				if ( ! empty( $curl_version ) ) {
					$curl_ver = $curl_version['version'] . " " . $curl_version['ssl_version'];
				} else {
					$curl_ver = '-';
				}
			} else {
				$curl_ver = '-';
			}
			$info .= 'cURL Info: ' . $curl_ver . "\n";
			// Gzip
			if ( is_callable( 'gzopen' ) ) {
				$gzip = true;
			} else {
				$gzip = false;
			}
			$gzip_status = ( $gzip ) ? 'Yes' : 'No';
			$info .= 'Gzip: ' . $gzip_status . "\n";
			return $info;
		}

		/**
		 * Follows the SCRIPT_DEBUG settings
		 * @param string $script
		 * @return string
		 */
		public static function script_debug_suffix() {
			if ( defined( 'SCRIPT_DEBUG' ) and SCRIPT_DEBUG ) {
				return '';
			} else {
				return '.min';
			}
		}

		public static function number_to_kmb( $number ) {
			$number_format = '';
			if ( $number < 1000 ) {
				// Anything less than a thousand
				$number_format = number_format_i18n( $number );
			} else if ( $number < 1000000 ) {
				// Anything less than a milion
				$number_format = number_format_i18n( $number / 1000, 2 ) . 'K';
			} else if ( $number < 1000000000 ) {
				// Anything less than a billion
				$number_format = number_format_i18n( $number / 1000000, 2 ) . 'M';
			} else {
				// At least a billion
				$number_format = number_format_i18n( $number / 1000000000, 2 ) . 'B';
			}
			return $number_format;
		}

		public static function secondstohms( $value ) {
			$value = (float) $value;
			$hours = floor( $value / 3600 );
			$hours = $hours < 10 ? '0' . $hours : (string) $hours;
			$minutes = floor( (int) ( $value / 60 ) % 60 );
			$minutes = $minutes < 10 ? '0' . $minutes : (string) $minutes;
			$seconds = floor( (int)$value % 60 );
			$seconds = $seconds < 10 ? '0' . $seconds : (string) $seconds;
			return $hours . ':' . $minutes . ':' . $seconds;
		}

		/** Keeps compatibility with WP < 5.3.0
		 *
		 * @return string
		 */
		public static function timezone_string() {
			$timezone_string = get_option( 'timezone_string' );

			if ( $timezone_string ) {
				return $timezone_string;
			}

			$offset  = (float) get_option( 'gmt_offset' );
			$hours   = (int) $offset;
			$minutes = ( $offset - $hours );

			$sign      = ( $offset < 0 ) ? '-' : '+';
			$abs_hour  = abs( $hours );
			$abs_mins  = abs( $minutes * 60 );
			$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

			return $tz_offset;
		}

	}
}
