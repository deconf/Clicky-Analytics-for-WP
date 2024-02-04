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
if ( ! class_exists( 'CAWP_CAPI_Controller' ) ) {

	final class CAWP_CAPI_Controller {

		public $timeshift;

		public $sitekey;

		private $cawp;

		/**
		 * Google API Client Initialization
		 */
		public function __construct() {
			$this->cawp = CAWP();
			$httpoptions = array();
			$origin = CAWP_Tools::strip_protocol( get_site_url() );
			$httpoptions['headers'] = array( 'referer' => CAWP_CURRENT_VERSION, 'User-Agent' => $origin );
			$security = wp_create_nonce( 'cawp_security' );
			$this->sitekey = $this->cawp->config->options['sitekey'];
		}

		/**
		 * Handles errors returned by API to avoid unnecessary requests
		 *
		 * @return boolean
		 */
		public function capi_errors_handler() {
			$errors = CAWP_Tools::get_cache( 'capi_errors' );
			if ( false === $errors || ! isset( $errors[0] ) ) { // invalid error
				return false;
			}
			if ( isset( $errors[1][0]['reason'] ) && ( 'invalidParameter' == $errors[1][0]['reason'] || 'badRequest' == $errors[1][0]['reason'] || 'invalidCredentials' == $errors[1][0]['reason'] || 'insufficientPermissions' == $errors[1][0]['reason'] || 'required' == $errors[1][0]['reason'] ) ) {
				return $errors[0];
			}
			if ( 400 == $errors[0] || 401 == $errors[0] || 403 == $errors[0] ) {
				return $errors[0];
			}
			/**
			 * Back-off system for subsequent requests - an Auth error generated after a Service request
			 *  The native back-off system for Service requests is covered by the Google API Client
			 */
			if ( isset( $errors[1][0]['reason'] ) && ( 'authError' == $errors[1][0]['reason'] ) ) {
				if ( $this->cawp->config->options['api_backoff'] <= 5 ) {
					usleep( $this->cawp->config->options['api_backoff'] * 1000000 + rand( 100000, 1000000 ) );
					$this->cawp->config->options['api_backoff'] = $this->cawp->config->options['api_backoff'] + 1;
					$this->cawp->config->set_plugin_options();
					return false;
				} else {
					return $errors[0];
				}
			}
			if ( 500 == $errors[0] || 503 == $errors[0] || $errors[0] < - 50 ) {
				return $errors[0];
			}
			return false;
		}

		/**
		 * Calculates proper timeouts for each CAPI query
		 *
		 * @param
		 *            $interval
		 * @return number
		 */
		public function get_timeouts( $interval = '' ) {
			$local_time = time() + $this->timeshift;
			if ( 'daily' == $interval ) {
				$nextday = explode( '-', date( 'n-j-Y', strtotime( ' +1 day', $local_time ) ) );
				$midnight = mktime( 0, 0, 0, $nextday[0], $nextday[1], $nextday[2] );
				return $midnight - $local_time;
			} else if ( 'midnight' == $interval ) {
				$midnight = strtotime( "tomorrow 00:00:00" ); // UTC midnight
				$midnight = $midnight + 8 * 3600; // UTC 8 AM
				return $midnight - time();
			} else if ( 'hourly' == $interval ) {
				$nexthour = explode( '-', date( 'H-n-j-Y', strtotime( ' +1 hour', $local_time ) ) );
				$newhour = mktime( $nexthour[0], 0, 0, $nexthour[1], $nexthour[2], $nexthour[3] );
				return $newhour - $local_time;
			} else {
				$newtime = strtotime( ' +10 minutes', $local_time );
				return $newtime - $local_time;
			}
		}

		/**
		 * Gets and stores Clicky Analytics Reports
		 *
		 * @param
		 *            $projecId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $dimensions
		 * @param
		 *            $options
		 * @param
		 * 											$filters
		 * @param
		 *            $serial
		 * @return int|array
		 */
		private function handle_clickyanalytics_reports( $projectId, $from, $to, $metrics, $dimensions, $options, $filters, $serial ) {
			$transient = CAWP_Tools::get_cache( $serial );
			if ( false === $transient ) {
				if ( $this->capi_errors_handler() ) {
					return $this->capi_errors_handler();
				}
				// Disable filters
				$filters = '';
				$request = CAWP_ENDPOINT_URL . "site_id=" . $projectId . "&sitekey=" . $this->sitekey . "&date=" . $from . "," . $to . "&type=" . $metrics . $dimensions . $options . $filters . "&output=php";
				$args = array( 'timeout' => 15, 'redirection' => 1, 'httpversion' => '1.0', 'user-agent' => 'Clicky Analytics (+https://deconf.com/clicky-analytics-dashboard-wordpress/)' );
				$result = wp_remote_get( $request, $args );
				if ( is_array( $result ) and 200 == $result['response']['code'] ) {
					$data = unserialize( $result['body'] );
				} else {
					if ( is_wp_error( $result ) ) {
						$timeout = $this->get_timeouts();
						CAWP_Tools::set_error( $result, $timeout );
						return $result->get_error_code();
					}
				}
				if ( $from === $to ) {
					CAWP_Tools::set_cache( $serial, $data, $this->get_timeouts( 'houtly' ) );
				} else {
					CAWP_Tools::set_cache( $serial, $data, $this->get_timeouts( 'daily' ) );
				}
				return $data;
			} else {
				$data = $transient;
			}
			$this->cawp->config->options['api_backoff'] = 0;
			$this->cawp->config->set_plugin_options();
			if ( ! empty( $data ) ) {
				return $data;
			} else {
				$data = array( array() );
				return $data;
			}
		}

		/**
		 * Generates serials for cache using crc32() to avoid exceeding option name lengths
		 *
		 * @param
		 *            $serial
		 * @return string
		 */
		public function get_serial( $serial ) {
			return sprintf( "%u", crc32( $serial ) );
		}

		/**
		 * Search Analytics data for Area Charts
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $query
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_areachart_data( $projectId, $from, $to, $query, $filter = '' ) {
			switch ( $query ) {
				case 'actions-pageviews' :
					$title = __( "PageViews", 'clicky-analytics' );
					break;
				case 'bounce-rate' :
					$title = __( "Bounce Rate", 'clicky-analytics' );
					break;
				case 'time-average' :
					$title = __( "Time Average", 'clicky-analytics' );
					break;
				default :
					$title = __( "Visitors", 'clicky-analytics' );
			}
			if ( ( $from === $to ) && ( 'bounce-rate' !== $query && 'time-average' !== $query ) ) {
				$dimensions = '&hourly=1';
			} else {
				$dimensions = '&daily=1';
			}
			$metrics = $query;
			$options = '';
			if ( $filter ) {
				$filters = '&href=' . urlencode( $filter );
			} else {
				$filters = '';
			}
			$serial = 'qr2_' . $this->get_serial( $projectId . $from . $to . $filter . $metrics );
			$data = $this->handle_clickyanalytics_reports( $projectId, $from, $to, $metrics, $dimensions, $options, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( empty( $data ) ) {
				// unable to render it as an Area Chart, returns a numeric value to be handled by reportsx.js
				return 621;
			}
			if ( '&hourly=1' == $dimensions ) {
				foreach ( $data as $items ) {
					foreach ( $items as $date ) {
						foreach ( $date as $item ) {
							foreach ( $item as $key => $values ) {
								foreach ( $values as $hour => $value ) {
									$cawp_data[] = array( (int) $hour . ':00', round( $value, 2 ) );
								}
							}
						}
					}
				}
			} else {
				foreach ( $data as $item ) {
					foreach ( $item as $date => $value ) {
						/*
						 * translators:
						 * Example: 'l, F j, Y' will become 'Thusday, November 17, 2015'
						 * For details see: http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
						 */
						$cawp_data[] = array( date_i18n( __( 'l, F j, Y', 'clicky-analytics' ), strtotime( $date ) ), round( $value[0]['value'], 2 ) );
					}
				}
			}
			$cawp_data[] = array( __( "Date", 'clicky-analytics' ), $title );
			return array_reverse( $cawp_data );
		}

		/**
		 * Clicky Analytics Summary
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $filter
		 * @return array|int
		 */
		private function get_summary( $projectId, $from, $to, $filter = '' ) {
			$dimensions = '';
			$options = '';
			$metrics = 'visitors,actions-pageviews,time-average,bounce-rate';
			if ( $filter ) {
				$filters = '&href=' . urlencode( $filter );
			} else {
				$filters = '';
			}
			$serial = 'qr3_' . $this->get_serial( $projectId . $from . $to . $filter . $metrics . '0' );
			$data = $this->handle_clickyanalytics_reports( $projectId, $from, $to, $metrics, $dimensions, $options, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			$rawdata = array();
			foreach ( $data as $item ) {
				foreach ( $item as $date => $value ) {
					$rawdata[] = $value[0]['value'];
				}
			}
			// i18n support
			$cawp_data[0] = empty( $rawdata[0] ) ? 0 : CAWP_Tools::number_to_kmb( $rawdata[0] );
			$cawp_data[1] = empty( $rawdata[1] ) ? 0 : CAWP_Tools::number_to_kmb( $rawdata[1] );
			$cawp_data[2] = empty( $rawdata[2] ) ? '00:00' : CAWP_Tools::secondstohms( $rawdata[2] );
			$cawp_data[3] = empty( $rawdata[3] ) ? '0%' : number_format_i18n( $rawdata[3], 2 ) . '%';
			return $cawp_data;
		}

		/**
		 * Clicky Analytics data for Location reports
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $filter
		 * @param
		 *            $metric
		 * @return array|int
		 */
		private function get_locations( $projectId, $from, $to, $metric, $filter = '' ) {
			$metrics = 'visitors-list';
			$options = '&limit=all&page=';
			$dimensions = '&visitor-details=geolocation';
			$title = __( "Countries", 'clicky-analytics' );
			if ( $filter ) {
				$filters = '&href=' . urlencode( $filter );
			} else {
				$filters = '';
			}
			$page = 1;
			$count = 1000;
			$rawdata = array();
			// Iterate pages: free account 100 results per page / pro account 1000 results per page
			while ( 100 == $count || 1000 == $count ) {
				$serial = 'qr4_' . $this->get_serial( $projectId . $from . $to . $filter . $dimensions . $options . $page );
				$data = $this->handle_clickyanalytics_reports( $projectId, $from, $to, $metrics, $dimensions, $options . $page, $filters, $serial );
				if ( is_numeric( $data ) ) {
					return $data;
				}

				if ( NULL !== array_values( $data['visitors-list'] )[0] ) {
					$count = count( array_values( $data['visitors-list'] )[0] );
				} else {
					$count = 0;
				}

				if ( $count > 0 ) {
					foreach ( $data as $item ) {
						foreach ( $item as $date => $location ) {
							foreach ( $location as $geo ) {
								$country = explode( ',', $geo['geolocation'] );

								// If USA, move Country name over State name
								if ( isset( $country[2] ) ) {
									$country[1] = $country[2];
								}

								if ( isset( $country[1] ) ) {
									if ( isset( $rawdata[trim( $country[1] )] ) ) {
										$rawdata[trim( $country[1] )]++;
									} else {
										$rawdata[trim( $country[1] )] = 1;
									}
								} else {
									if ( isset( $rawdata[trim( $country[0] )] ) ) {
										$rawdata[trim( $country[0] )]++;
									} else {
										$rawdata[trim( $country[0] )] = 1;
									}
								}
							}
						}
					}
				}
				$page++;
			}
			$cawp_data = array( array( $title, __( ucfirst( $metric ), 'clicky-analytics' ) ) );
			foreach ( $rawdata as $item => $value ) {
				$cawp_data[] = array( esc_html( $item ), (float) $value );
			}
			return $cawp_data;
		}

		/**
		 * Clicky Analytics data for Table Charts (pages)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $filter
		 * @param
		 *            $metric
		 * @return array|int
		 */
		private function get_pages( $projectId, $from, $to, $metric, $filter = '' ) {
			$metrics = '';
			$options = '&limit=all&page=';
			$dimensions = 'pages';
			if ( $filter ) {
				$filters = '&href=' . urlencode( $filter );
			} else {
				$filters = '';
			}
			$cawp_data = array( array( __( "Pages", 'clicky-analytics' ), __( ucfirst( $metric ), 'clicky-analytics' ) ) );
			$page = 1;
			$count = 1000;
			// Iterate pages: free account 100 results per page / pro account 1000 results per page
			while ( 100 == $count || 1000 == $count ) {
				$serial = 'qr5_' . $this->get_serial( $projectId . $from . $to . $filter . $dimensions . $options . $page );
				$data = $this->handle_clickyanalytics_reports( $projectId, $from, $to, $metrics, $dimensions, $options . $page, $filters, $serial );
				if ( is_numeric( $data ) ) {
					return $data;
				}

				if ( NULL !== array_values( $data['pages'] )[0] ) {
					$count = count( array_values( $data['pages'] )[0] );
				} else {
					$count = 0;
				}

				if ( $count > 0 ) {
					foreach ( $data as $items ) {
						foreach ( $items as $date => $item ) {
							foreach ( $item as $value ) {
								$cawp_data[] = array( esc_html( $value['title'] ), (float) $value['value'] );
							}
						}
					}
				}
				$page++;
			}
			return $cawp_data;
		}

		/**
		 * Clicky Analytics data for Table Charts (referrers)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $filter
		 * @param
		 *            $metric
		 * @return array|int
		 */
		private function get_referrers( $projectId, $from, $to, $metric, $filter = '' ) {
			$metrics = '';
			$options = '&limit=all&page=';
			$dimensions = 'links-domains';
			if ( $filter ) {
				$filters = '&href=' . urlencode( $filter );
			} else {
				$filters = '';
			}
			$cawp_data = array( array( __( "Referrers", 'clicky-analytics' ), __( ucfirst( $metric ), 'clicky-analytics' ) ) );
			$page = 1;
			$count = 1000;
			// Iterate pages: free account 100 results per page / pro account 1000 results per page
			while ( 100 == $count || 1000 == $count ) {
				$serial = 'qr6_' . $this->get_serial( $projectId . $from . $to . $filter . $dimensions . $options . $page );
				$data = $this->handle_clickyanalytics_reports( $projectId, $from, $to, $metrics, $dimensions, $options . $page, $filters, $serial );
				if ( is_numeric( $data ) ) {
					return $data;
				}

				if ( NULL !== array_values( $data['links-domains'] )[0] ) {
					$count = count( array_values( $data['links-domains'] )[0] );
				} else {
					$count = 0;
				}

				if ( $count > 0 ) {
					foreach ( $data as $items ) {
						foreach ( $items as $date => $item ) {
							foreach ( $item as $value ) {
								$cawp_data[] = array( esc_html( $value['title'] ), (float) $value['value'] );
							}
						}
					}
				}
				$page++;
			}
			return $cawp_data;
		}

		/**
		 * Clicky Analytics data for Org Charts (site performance)
		 *
		 * @param
		 *            $projectId
		 * @param
		 *            $from
		 * @param
		 *            $to
		 * @param
		 *            $query
		 * @param
		 *            $filter
		 * @param
		 *            $metric
		 * @return array|int
		 */
		private function get_orgchart_data( $projectId, $from, $to, $query, $metric, $filter = '' ) {
			$options = '';
			$metrics = '';
			$dimensions = 'actions-pageviews,actions-downloads,actions-outbounds,actions-clicks';
			if ( $filter ) {
				$filters = '&href=' . urlencode( $filter );
			} else {
				$filters = '';
			}
			$serial = 'qr7_' . $this->get_serial( $projectId . $from . $to . $filter . $dimensions );
			$data = $this->handle_clickyanalytics_reports( $projectId, $from, $to, $metrics, $dimensions, $options, $filters, $serial );
			if ( is_numeric( $data ) ) {
				return $data;
			}
			if ( empty( $data ) ) {
				// unable to render as an Org Chart, returns a numeric value to be handled by reportsx.js
				return 621;
			}
			$rawdata = array();
			foreach ( $data as $item ) {
				foreach ( $item as $date => $value ) {
					$rawdata[] = $value[0]['value'];
				}
			}
			$res_data['Pageviews'] = empty( $rawdata[0] ) ? 0 : CAWP_Tools::number_to_kmb( $rawdata[0] );
			$res_data['Downloads'] = empty( $rawdata[1] ) ? 0 : CAWP_Tools::number_to_kmb( $rawdata[1] );
			$res_data['Outbounds'] = empty( $rawdata[2] ) ? 0 : CAWP_Tools::number_to_kmb( $rawdata[2] );
			$res_data['Clicks'] = empty( $rawdata[2] ) ? 0 : CAWP_Tools::number_to_kmb( $rawdata[3] );
			;
			if ( $filters ) {
				$block = __( "Site Performance", 'clicky-analytics' );
			} else {
				$block = __( "Site Performance", 'clicky-analytics' );
			}
			$cawp_data = array( array( '<div">' . $block . '</div><div></div>', "" ) );
			foreach ( $res_data as $key => $value ) {
				$cawp_data[] = array( '<div>' . esc_html( $key ) . '</div><div>' . $value . '</div>', '<div>' . $block . '</div><div></div>' );
			}
			return $cawp_data;
		}

		/**
		 * Handles ajax requests and calls the needed methods
		 * @param
		 * 		$projectId
		 * @param
		 * 		$query
		 * @param
		 * 		$from
		 * @param
		 * 		$to
		 * @param
		 * 		$filter
		 * @param
		 *   $metric
		 * @return number|Deconf\CAWP\Google\Service\SearchConsole
		 */
		public function get( $projectId, $query, $from = false, $to = false, $filter = '', $metric = 'sessions' ) {
			if ( empty( $projectId ) ) {
				wp_die( 626 );
			}
			if ( 'summary' == $query ) {
				return $this->get_summary( $projectId, $from, $to, $filter );
			}
			if ( in_array( $query, array( 'visitors', 'actions-pageviews', 'time-average', 'bounce-rate' ) ) ) {
				return $this->get_areachart_data( $projectId, $from, $to, $query, $filter );
			}
			if ( 'locations' == $query ) {
				return $this->get_locations( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'pages' == $query ) {
				return $this->get_pages( $projectId, $from, $to, $metric, $filter );
			}
			if ( 'channelGrouping' == $query || 'deviceCategory' == $query ) {
				return $this->get_orgchart_data( $projectId, $from, $to, $query, $metric, $filter );
			}
			if ( 'referrers' == $query ) {
				return $this->get_referrers( $projectId, $from, $to, $metric, $filter );
			}
			wp_die( 627 );
		}
	}
}
