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

if ( ! class_exists( 'CAWP_Backend_Widgets' ) ) {

	class CAWP_Backend_Widgets {

		private $cawp;

		public function __construct() {
			$this->cawp = CAWP();
			if ( CAWP_Tools::check_roles( $this->cawp->config->options['access_back'] ) && ( 1 == $this->cawp->config->options['dashboard_widget'] ) ) {
				add_action( 'wp_dashboard_setup', array( $this, 'add_widget' ) );
			}
		}

		public function add_widget() {
			wp_add_dashboard_widget( 'cawp-widget', __( "Clicky Analytics", 'clicky-analytics' ), array( $this, 'dashboard_widget' ), $control_callback = null );
		}

		public function dashboard_widget() {
			$projectId = 0;

			if ( empty( $this->cawp->config->options['sitekey'] ) ) {
				echo '<p>' . __( "This plugin needs an authorization:", 'clicky-analytics' ) . '</p><form action="' . menu_page_url( 'cawp_setup', false ) . '" method="POST">' . get_submit_button( __( "Authorize Plugin", 'clicky-analytics' ), 'secondary' ) . '</form>';
				return;
			}

				if ( $this->cawp->config->options['siteid'] ) {
					$projectId = $this->cawp->config->options['siteid'];
				} else {
					echo '<p>' . __( "An admin should asign a default Clicky Analytics Site ID / Site Key.", 'clicky-analytics' ) . '</p><form action="' . menu_page_url( 'cawp_setup', false ) . '" method="POST">' . get_submit_button( __( "Add credentials", 'clicky-analytics' ), 'secondary' ) . '</form>';
					return;
				}

			if ( ! ( $projectId ) ) {
				echo '<p>' . __( "Something went wrong while retrieving property data. You need to create and properly configure a Clicky Analytics account:", 'clicky-analytics' ) . '</p> <form action="https://deconf.com/clicky-analytics-dashboard-wordpress/" method="POST">' . get_submit_button( __( "Find out more!", 'clicky-analytics' ), 'secondary' ) . '</form>';
				return;
			}

			?>
<div id="cawp-window-1"></div>
<?php
		}
	}
}
