<?php

/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
function file_get_contents_clicky( $url ) {
	$args = array( 'timeout' => 15, 'redirection' => 1, 'httpversion' => '1.0', 'user-agent' => 'Clicky Analytics (+https://deconf.com/clicky-analytics-dashboard-wordpress/)' );
	$result = wp_remote_get( $url );
	if ( is_array( $result ) and 200 == $result['response']['code'] ) {
		$data = $result['body'];
	} else {
		$data = '';
	}
	return $data;
}

function ca_tracking_code_body() {
	$tracking = "\n<!-- BEGIN Clicky Analytics v" . CADASH_CURRENT_VERSION . " Tracking - https://deconf.com/clicky-analytics-dashboard-wordpress/ -->\n";
	$tracking .= '<noscript><p><img alt="Clicky" width="1" height="1" src="//in.getclicky.com/100669114ns.gif" /></p></noscript>';
	$tracking .= "\n<!-- END Clicky Analytics v" . CADASH_CURRENT_VERSION . " Tracking - https://deconf.com/clicky-analytics-dashboard-wordpress/ -->\n";
	return $tracking;
}

function ca_tracking_code() {
	global $current_user;
	$custom_tracking = "";
	wp_get_current_user();
	if ( $current_user->user_login ) {
		if ( ( get_option( 'ca_track_username' ) ) and ( ( get_option( 'ca_track_email' ) ) ) ) {
			$custom_tracking = "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.visitor = clicky_custom.visitor || {};
  clicky_custom.visitor [\"username\"] = '" . $current_user->user_login . "';
  clicky_custom.visitor [\"email\"] = '" . $current_user->user_email . "';
</script>\n";
		} else if ( get_option( 'ca_track_username' ) ) {
			$custom_tracking = "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.visitor = clicky_custom.visitor || {};
  clicky_custom.visitor [\"username\"] = '" . $current_user->user_login . "';
</script>\n";
		} else if ( get_option( 'ca_track_email' ) ) {
			$custom_tracking = "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.visitor = clicky_custom.visitor || {};
  clicky_custom.visitor [\"email\"] = '" . $current_user->user_email . "';
</script>\n";
		}
	}
	$video_tracking = "";
	if ( get_option( 'ca_track_youtube' ) ) {
		$video_tracking .= "<script src='//static.getclicky.com/inc/javascript/video/youtube.js'></script>";
	}
	if ( get_option( 'ca_track_html5' ) ) {
		$custom_tracking .= "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.html_media_track = 1;
</script>\n";
	}
	if ( get_option( 'ca_track_olp' ) ) {
		$ca_olp = explode( ',', get_option( 'ca_track_olp' ) );
		$ca_olp_string = "";
		foreach ( $ca_olp as $key => $pattern ) {
			$ca_olp_string .= "'" . $pattern . "'" . ",";
		}
		$ca_olp_string = '[' . rtrim( $ca_olp_string, ',' ) . ']';
		$custom_tracking .= "<script type=\"text/javascript\">
  var clicky_custom = clicky_custom || {};
  clicky_custom.outbound_pattern = $ca_olp_string;
</script>\n";
	}
	$main_tracking = '<script async src="//static.getclicky.com/' . get_option( 'ca_siteid' ) . '.js"></script>';
	$tracking = "\n<!-- BEGIN Clicky Analytics v" . CADASH_CURRENT_VERSION . " Tracking - https://deconf.com/clicky-analytics-dashboard-wordpress/ -->\n";
	$tracking .= $custom_tracking . "\n" . $main_tracking . "\n" . $video_tracking;
	$tracking .= "\n<!-- END Clicky Analytics v" . CADASH_CURRENT_VERSION . " Tracking - https://deconf.com/clicky-analytics-dashboard-wordpress/ -->\n";
	return $tracking;
}

function ca_validation( $item ) {
	return addslashes( $item );
}

function ca_clear_cache() {
	global $wpdb;
	$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_clicky%%'" );
	$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_clicky%%'" );
}

function ca_safe_get( $key ) {
	if ( array_key_exists( $key, $_POST ) ) {
		return $_POST[$key];
	}
	return false;
}

// Get Top Pages
function ca_top_pages( $api_url, $siteid, $sitekey, $from ) {
	$metric = 'type=pages';
	try {
		$serial = 'clicky_qr3' . $from;
		$transient = get_transient( $serial );
		if ( empty( $transient ) ) {
			$url = $api_url . "site_id=" . $siteid . "&sitekey=" . $sitekey . "&" . $from . "&" . $metric . "&limit=30&output=php";
			// echo $url;
			$result = unserialize( file_get_contents_clicky( $url ) );
			set_transient( $serial, $result, get_option( 'ca_cachetime' ) );
			// echo "QR3-Refresh";
		} else {
			$result = $transient;
			// echo "QR3-Cache";
		}
	} catch ( exception $e ) {
		return "<p>" . __( "ERROR LOG:", 'clicky-analytics' ) . "</p><p>" . $e . "</p>";
	}
	$i = 0;
	if ( ! is_array( $result ) ) {
		return;
	}
	foreach ( $result as $item ) {
		if ( $item != "Invalid sitekey." ) {
			foreach ( $item as $date => $item1 ) {
				if ( ! $item1 ) {
					return;
				}
				foreach ( $item1 as $item2 ) {
					$goores[$i][0] = ca_validation( $item2['title'] );
					$goores[$i][1] = ca_validation( $item2['value'] );
					$i++;
				}
			}
		}
	}
	$j = 0;
	$ca_statsdata = "";
	for ( $j = 0; $j <= $i - 1; $j++ ) {
		$ca_statsdata .= "['" . $goores[$j][0] . "'," . $goores[$j][1] . "],";
	}
	return wp_kses( rtrim( $ca_statsdata, ',' ), $GLOBALS['CADASH_ALLOW'] );
}

// Get Top referrers
function ca_top_referrers( $api_url, $siteid, $sitekey, $from ) {
	$metric = 'type=links-domains';
	try {
		$serial = 'clicky_qr4' . $from;
		$transient = get_transient( $serial );
		if ( empty( $transient ) ) {
			$url = $api_url . "site_id=" . $siteid . "&sitekey=" . $sitekey . "&" . $from . "&" . $metric . "&limit=30&output=php";
			// echo $url;
			$result = unserialize( file_get_contents_clicky( $url ) );
			set_transient( $serial, $result, get_option( 'ca_cachetime' ) );
			// echo "QR4-Refresh";
		} else {
			$result = $transient;
			// echo "QR4-Cache";
		}
	} catch ( exception $e ) {
		return "<p>" . __( "ERROR LOG:", 'clicky-analytics' ) . "</p><p>" . $e . "</p>";
	}
	$i = 0;
	if ( ! is_array( $result ) ) {
		return;
	}
	foreach ( $result as $item ) {
		if ( $item != "Invalid sitekey." ) {
			foreach ( $item as $date => $item1 ) {
				if ( ! $item1 ) {
					return;
				}
				foreach ( $item1 as $item2 ) {
					$goores[$i][0] = ca_validation( $item2['title'] );
					$goores[$i][1] = ca_validation( $item2['value'] );
					$i++;
				}
			}
		}
	}
	$j = 0;
	$ca_statsdata = "";
	for ( $j = 0; $j <= $i - 1; $j++ ) {
		$ca_statsdata .= "['" . $goores[$j][0] . "'," . $goores[$j][1] . "],";
	}
	return wp_kses( rtrim( $ca_statsdata, ',' ), $GLOBALS['CADASH_ALLOW'] );
}

// Get Top searches
function ca_top_searches( $api_url, $siteid, $sitekey, $from ) {
	$metric = 'type=searches';
	try {
		$serial = 'clicky_qr5' . $from;
		$transient = get_transient( $serial );
		if ( empty( $transient ) ) {
			$url = $api_url . "site_id=" . $siteid . "&sitekey=" . $sitekey . "&" . $from . "&" . $metric . "&limit=30&output=php";
			// echo $url;
			$result = unserialize( file_get_contents_clicky( $url ) );
			set_transient( $serial, $result, get_option( 'ca_cachetime' ) );
			// echo "QR4-Refresh";
		} else {
			$result = $transient;
			// echo "QR4-Cache";
		}
	} catch ( exception $e ) {
		return "<p>" . __( "ERROR LOG:", 'clicky-analytics' ) . "</p><p>" . $e . "</p>";
	}
	$i = 0;
	if ( ! is_array( $result ) ) {
		return;
	}
	foreach ( $result as $item ) {
		if ( $item != "Invalid sitekey." ) {
			foreach ( $item as $date => $item1 ) {
				if ( ! $item1 ) {
					return;
				}
				foreach ( $item1 as $item2 ) {
					$goores[$i][0] = ca_validation( $item2['title'] );
					$goores[$i][1] = ca_validation( $item2['value'] );
					$i++;
				}
			}
		}
	}
	$j = 0;
	$ca_statsdata = "";
	for ( $j = 0; $j <= $i - 1; $j++ ) {
		if ( isset( $goores[$j][1] ) ) {
			$ca_statsdata .= "['" . $goores[$j][0] . "'," . $goores[$j][1] . "],";
		}
	}
	return wp_kses( rtrim( $ca_statsdata, ',' ), $GLOBALS['CADASH_ALLOW'] );
}

function ca_get_sites() { // Use wp_get_sites() if WP version is lower than 4.6.0
	global $wp_version;
	if ( version_compare( $wp_version, '4.6.0', '<' ) ) {
		return wp_get_sites();
	} else {
		foreach ( get_sites() as $blog ) {
			$blogs[] = (array) $blog; // Convert WP_Site object to array
		}
		return $blogs;
	}
}
?>
