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

if ( ! class_exists( 'CAWP_Common_Ajax' ) ) {

	final class CAWP_Common_Ajax {

		private $cawp;

		public function __construct() {
			$this->cawp = CAWP();

			if ( CAWP_Tools::check_roles( $this->cawp->config->options['access_back'] ) || CAWP_Tools::check_roles( $this->cawp->config->options['access_front'] ) ) {
				add_action( 'wp_ajax_cawp_set_error', array( $this, 'ajax_set_error' ) );
			}
		}

		/**
		 * Ajax handler for storing JavaScript Errors
		 *
		 * @return int
		 */
		public function ajax_set_error() {
			if ( ! isset( $_POST['cawp_security_set_error'] ) || ! ( wp_verify_nonce( $_POST['cawp_security_set_error'], 'cawp_backend_item_reports' ) || wp_verify_nonce( $_POST['cawp_security_set_error'], 'cawp_frontend_item_reports' ) ) ) {
				wp_die( 640 );
			}
			$timeout = 24 * 60 * 60;
			CAWP_Tools::set_error( $_POST['response'], $timeout );
			wp_die();
		}
	}
}
