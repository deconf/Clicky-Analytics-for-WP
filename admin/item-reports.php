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

if ( ! class_exists( 'CAWP_Backend_Item_Reports' ) ) {

	final class CAWP_Backend_Item_Reports {

		private $cawp;

		public function __construct() {
			$this->cawp = CAWP();

			if ( CAWP_Tools::check_roles( $this->cawp->config->options['access_back'] ) && 1 == $this->cawp->config->options['backend_item_reports'] ) {
				/**
				 * Add custom column in Posts List
				 */
				add_filter( 'manage_posts_columns', array( $this, 'add_columns' ), 99 );
				/**
				 * Populate custom column in Posts List
				 */
				add_action( 'manage_posts_custom_column', array( $this, 'add_icons' ), 99, 2 );
				/**
				 * Add custom column in Pages List
				 */
				add_filter( 'manage_pages_columns', array( $this, 'add_columns' ), 99 );
				/**
				 * Populate custom column in Pages List
				 */
				add_action( 'manage_pages_custom_column', array( $this, 'add_icons' ), 99, 2 );
			}
		}

		public function add_icons( $column, $id ) {
			global $wp_version;

			if ( 'cawp_stats' != $column ) {
				return;
			}

			echo '<a id="cawp-' . $id . '" title="' . get_the_title( $id ) . '" href="#' . $id . '" class="cawp-icon dashicons-before dashicons-chart-line">&nbsp;</a>';
		}

		public function add_columns( $columns ) {
			return array_merge( $columns, array( 'cawp_stats' => '<span class="dashicons dashicons-analytics" title="Clicky Analytics"></span>' ) );
		}
	}
}
