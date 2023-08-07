<?php
/**
 * Author: Alin Marcu
 * Copyright 2019 Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'CAWP_Tracking' ) ) {

	final class CAWP_Tracking {

		private $cawp;

		public function __construct() {
			$this->cawp = CAWP();
			/**
			 * Add verification META
			 */
			add_action( 'wp_head', array( $this, 'output' ) );
		}

		public function output() {
			global $current_user;
			$custom_tracking = "";
			wp_get_current_user();
			if ( $current_user->user_login ) {
				if ( ( $this->cawp->config->options['track_username'] ) and ( ( $this->cawp->config->options['track_email'] ) ) ) {
					$custom_tracking = "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.visitor = clicky_custom.visitor || {};
  clicky_custom.visitor [\"username\"] = '" . $current_user->user_login . "';
  clicky_custom.visitor [\"email\"] = '" . $current_user->user_email . "';
</script>\n";
				} else if ( $this->cawp->config->options['track_username'] ) {
					$custom_tracking = "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.visitor = clicky_custom.visitor || {};
  clicky_custom.visitor [\"username\"] = '" . $current_user->user_login . "';
</script>\n";
				} else if ( $this->cawp->config->options['track_email'] ) {
					$custom_tracking = "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.visitor = clicky_custom.visitor || {};
  clicky_custom.visitor [\"email\"] = '" . $current_user->user_email . "';
</script>\n";
				}
			}
			$video_tracking = "";
			if ( $this->cawp->config->options['track_youtube'] ) {
				$video_tracking .= "<script src='//static.getclicky.com/inc/javascript/video/youtube.js'></script>";
			}
			if ( $this->cawp->config->options['track_html5'] ) {
				$custom_tracking .= "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.html_media_track = 1;
</script>\n";
			}
			if ( $this->cawp->config->options['track_outbound'] ) {
				$ca_olp = explode( ',', $this->cawp->config->options['track_outbound'] );
				$ca_olp_string = "";
				foreach ( $ca_olp as $key => $pattern ) {
					$ca_olp_string .= "'" . esc_js($pattern) . "'" . ",";
				}
				$ca_olp_string = '[' . rtrim( $ca_olp_string, ',' ) . ']';
				$custom_tracking .= "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.outbound_pattern = $ca_olp_string;
</script>\n";
			}
			$main_tracking = '<script async src="//static.getclicky.com/' . esc_js($this->cawp->config->options['siteid']) . '.js"></script>';
			$tracking = "\n<!-- BEGIN Clicky Analytics v" . CAWP_CURRENT_VERSION . " Tracking - https://deconf.com/clicky-analytics-dashboard-wordpress/ -->\n";
			$tracking .= $custom_tracking . "\n" . $main_tracking . "\n" . $video_tracking;
			$tracking .= "\n<!-- END Clicky Analytics v" . CAWP_CURRENT_VERSION . " Tracking - https://deconf.com/clicky-analytics-dashboard-wordpress/ -->\n\n";
			echo $tracking;
		}
	}
}