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

if ( ! class_exists( 'CAWP_Frontend_Ajax' ) ) {

	final class CAWP_Frontend_Ajax {

		private $cawp;

		public function __construct() {
			$this->cawp = CAWP();

			if ( CAWP_Tools::check_roles( $this->cawp->config->options['access_front'] ) && $this->cawp->config->options['frontend_item_reports'] ) {
				/**
				 * Item Reports action
				 */
				add_action( 'wp_ajax_cawp_frontend_item_reports', array( $this, 'ajax_item_reports' ) );
			}
		}

		/**
		 * Ajax handler for Item Reports
		 *
		 * @return string|int
		 */
		public function ajax_item_reports() {
			if ( ! isset( $_POST['cawp_security_frontend_item_reports'] ) || ! wp_verify_nonce( $_POST['cawp_security_frontend_item_reports'], 'cawp_frontend_item_reports' ) ) {
				wp_die( 630 );
			}

			$from = sanitize_option( 'date_format', $_POST['from'] );
			$to = sanitize_option( 'date_format', $_POST['to'] );
			$query = sanitize_text_field( $_POST['query'] );
			$uri =  sanitize_option( 'siteurl', $_POST['filter'] );
			if ( isset( $_POST['metric'] ) ) {
				$metric = sanitize_text_field( $_POST['metric'] );
			} else {
				$metric = 'visitors';
			}

			$query = sanitize_text_field( $_POST['query'] );
			if ( ob_get_length() ) {
				ob_clean();
			}

			if ( ! CAWP_Tools::check_roles( $this->cawp->config->options['access_front'] ) || 0 == $this->cawp->config->options['frontend_item_reports'] ) {
				wp_die( 631 );
			}

			if ( $this->cawp->config->options['sitekey'] && $this->cawp->config->options['siteid'] ) {
				if ( null === $this->cawp->capi_controller ) {
					$this->cawp->capi_controller = new CAWP_CAPI_Controller();
				}
			} else {
				wp_die( 624 );
			}

			if ( $this->cawp->config->options['siteid'] ) {
				$projectId = $this->cawp->config->options['siteid'];
			} else {
				wp_die( 626 );
			}

			$this->cawp->capi_controller->timeshift = (int) current_time( 'timestamp' ) - time();

			// allow URL correction before sending an API request
			$filter = apply_filters( 'cawp_frontenditem_uri', $uri );

			$queries = explode( ',', $query );

			$results = array();

			foreach ( $queries as $value ) {
				$results[] = $this->cawp->capi_controller->get( $projectId, $value, $from, $to, $filter, $metric );
			}

			wp_send_json( $results );
		}
	}
}
