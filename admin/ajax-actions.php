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

if ( ! class_exists( 'CAWP_Backend_Ajax' ) ) {

	final class CAWP_Backend_Ajax {

		private $cawp;

		public function __construct() {
			$this->cawp = CAWP();

			if ( CAWP_Tools::check_roles( $this->cawp->config->options['access_back'] ) && ( ( 1 == $this->cawp->config->options['backend_item_reports'] ) || ( 1 == $this->cawp->config->options['dashboard_widget'] ) ) ) {
				/**
				 * Items action
				 */
				add_action( 'wp_ajax_cawp_backend_item_reports', array( $this, 'ajax_item_reports' ) );
			}
			if ( current_user_can( 'manage_options' ) ) {
				/**
				 * Admin Widget action
				 */
				add_action( 'wp_ajax_cawp_dismiss_notices', array( $this, 'ajax_dismiss_notices' ) );
			}
		}

		/**
		 * Ajax handler for Item Reports
		 *
		 * @return JsonSerializable|int
		 */
		public function ajax_item_reports() {
			if ( ! isset( $_POST['cawp_security_backend_item_reports'] ) || ! wp_verify_nonce( $_POST['cawp_security_backend_item_reports'], 'cawp_backend_item_reports' ) ) {
				wp_die( 630 );
			}

			if ( isset( $_POST['projectId'] ) && 'false' !== $_POST['projectId'] ) {
				$projectId = sanitize_text_field( $_POST['projectId'] );
			} else {
				$projectId = false;
			}
			$from = sanitize_option( 'date_format', $_POST['from'] );
			$to = sanitize_option( 'date_format', $_POST['to'] );
			$query = sanitize_text_field( $_POST['query'] );
			if ( isset( $_POST['filter'] ) ) {
				$filter_id = (int) $_POST['filter'];
			} else {
				$filter_id = false;
			}
			if ( isset( $_POST['metric'] ) ) {
				$metric = sanitize_text_field( $_POST['metric'] );
			} else {
				$metric = 'visitors';
			}

			if ( ob_get_length() ) {
				ob_clean();
			}

			if ( ! ( CAWP_Tools::check_roles( $this->cawp->config->options['access_back'] ) && ( ( 1 == $this->cawp->config->options['backend_item_reports'] ) || ( 1 == $this->cawp->config->options['dashboard_widget'] ) ) ) ) {
				wp_die( 631 );
			}
			if ( $this->cawp->config->options['sitekey'] && $this->cawp->config->options['siteid'] && $from && $to ) {
				if ( null === $this->cawp->capi_controller ) {
					$this->cawp->capi_controller = new CAWP_CAPI_Controller();
				}
			} else {
				wp_die( 624 );
			}
			if ( false == $projectId ) {
				$projectId = $this->cawp->config->options['siteid'];
			}

			$this->cawp->capi_controller->timeshift = (int) current_time( 'timestamp' ) - time();

			if ( $filter_id ) {
				$uri = get_permalink( $filter_id );
				/**
				 * allow URL correction before sending an API request
				 */
				$filter = apply_filters( 'cawp_backenditem_uri', $uri, $filter_id );
			} else {
				$filter = false;
			}

			$queries = explode( ',', $query );

			$results = array();

			foreach ( $queries as $value ) {
				$results[] = $this->cawp->capi_controller->get( $projectId, $value, $from, $to, $filter, $metric );
			}

			wp_send_json( $results );
		}

		/**
		 * Ajax handler for dismissing Admin notices
		 */
		public function ajax_dismiss_notices() {
			if ( ! isset( $_POST['cawp_security_dismiss_notices'] ) || ! wp_verify_nonce( $_POST['cawp_security_dismiss_notices'], 'cawp_dismiss_notices' ) ) {
				wp_die( 630 );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 631 );
			}

			delete_option( 'cawp_got_updated' );

			wp_die();
		}
	}
}
