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

class CAWP_Install {

	public static function install() {
		$options = array();

		$options['dashboard_widget'] = 1;
		$options['tracking'] = 1;
		$options['track_username'] = 0;
		$options['theme_color'] = '#2c5fb2';
		$options['track_email'] = 1;
		$options['track_youtube'] = 0;
		$options['track_html5'] = 0;
		$options['sitekey'] = '';
		$options['siteid'] = '';
		$options['maps_api_key'] = '';
		$options['track_outbound'] = '/go/,/out/';
		$options['access_back'][] = 'Administrator';
		$options['access_front'][] = 'Administrator';
		$options['backend_item_reports'] = 0;
		$options['frontend_item_reports'] = 0;

		add_option( 'cawp_options', json_encode( $options ) );
	}
}
