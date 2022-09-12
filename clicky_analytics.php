<?php
/**
 * Plugin Name: Clicky Analytics
 * Plugin URI: https://deconf.com
 * Description: Displays Clicky Analytics reports into your Dashboard. Automatically inserts the tracking code in every page of your website.
 * Author: Alin Marcu
 * Version: 1.7
 * Author URI: https://deconf.com
 * Text Domain: clicky-analytics
 * Domain Path: /languages
 */
define( 'CADASH_CURRENT_VERSION', '1.7' );
$GLOBALS['CADASH_ALLOW'] = array( 'a' => array( 'href' => array(), 'title' => array() ), 'br' => array(), 'em' => array(), 'strong' => array() );
/*
 * Install
 */
register_activation_hook( __FILE__, 'cadash_install' );
/*
 * Uninstall
 */
register_uninstall_hook( __FILE__, 'cadash_uninstall' );
$plugin = plugin_basename( __FILE__ );
add_filter( 'the_content', 'ca_front_content' );
add_action( 'wp_dashboard_setup', 'ca_setup' );
add_action( 'admin_menu', 'ca_admin_actions' );
add_action( 'admin_menu', 'ca_dashboard_menu' );
add_action( 'wp_enqueue_scripts', 'ca_front_scripts' );
add_action( 'plugins_loaded', 'ca_init' );
add_action( 'wp_head', 'ca_tracking' );
add_action( 'wp_footer', 'ca_tracking_body' );
add_filter( "plugin_action_links_$plugin", 'ca_dash_settings_link' );
// Admin Styles
add_action( 'admin_enqueue_scripts', 'ca_admin_scripts' );

function ca_dashboard_menu() {
	add_dashboard_page( __( 'Clicky Analytics', 'clicky-analytics' ), __( 'Clicky Analytics', 'clicky' ), get_option( 'ca_access' ), 'clicky_analytics', 'ca_dashboard_page' );
}

function ca_dashboard_page() {
	$siteid = get_option( 'ca_siteid' );
	$sitekey = get_option( 'ca_sitekey' );
	?>
<br />
<iframe id="clicky-analytics" style="margin-left: 20px; width: 100%; height: 1000px;" src="https://clicky.com/stats/wp-iframe?site_id=<?php echo esc_attr($siteid); ?>&sitekey=<?php echo esc_attr($sitekey); ?>"></iframe>
<?php
}

function ca_admin_scripts( $hook ) {
	$valid_hooks = array( 'settings_page_Clicky_Analytics_Dashboard' );
	if ( ! in_array( $hook, $valid_hooks ) and 'index.php' != $hook )
		return;
	wp_enqueue_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), CADASH_CURRENT_VERSION );
	wp_enqueue_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), CADASH_CURRENT_VERSION );
	wp_register_style( 'clicky-analytics', plugins_url( 'clicky-analytics.css', __FILE__ ) );
	wp_enqueue_style( 'clicky-analytics' );
}

function ca_admin() {
	include ( 'clicky-analytics-admin.php' );
}

function ca_admin_actions() {
	if ( current_user_can( 'manage_options' ) ) {
		add_options_page( __( "Clicky Analytics", 'clicky-analytics' ), __( "Clicky Analytics", 'clicky-analytics' ), "manage_options", "Clicky_Analytics_Dashboard", "ca_admin" );
	}
}

function ca_init() {
	load_plugin_textdomain( 'clicky-analytics', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

function ca_front_scripts() {
	if ( get_option( 'ca_track_youtube' ) and ! wp_script_is( 'jquery' ) ) {
		wp_enqueue_script( 'jquery' );
	}
	if ( current_user_can( get_option( 'ca_access' ) ) && get_option( 'ca_frontend' ) ) {
		wp_enqueue_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), CADASH_CURRENT_VERSION );
	}
}

function ca_setup() {
	if ( current_user_can( get_option( 'ca_access' ) ) ) {
		wp_add_dashboard_widget( 'clicky-analytics-widget', 'Clicky Analytics Dashboard', 'ca_content', $control_callback = null );
	}
}

function ca_dash_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=Clicky_Analytics_Dashboard">' . __( "Settings", 'clicky-analytics' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

function ca_tracking() {
	$ca_traking = get_option( 'ca_tracking' );
	if ( $ca_traking != 2 ) {
		require_once 'functions.php';
		global $current_user;
		do_action( 'clicky_analytics_before_tracking', $current_user ); // DO NOT REMOVE THIS HOOK
		echo ca_tracking_code();
	}
}

function ca_tracking_body() {
	$ca_traking = get_option( 'ca_tracking' );
	if ( $ca_traking != 2 ) {
		require_once 'functions.php';
		echo ca_tracking_code_body();
	}
}

function cadash_install() {
	if ( ! get_option( 'ca_sitekey' ) ) {
		update_option( 'ca_sitekey', '' );
		update_option( 'ca_siteid', '' );
		update_option( 'ca_access', 'manage_options' );
		update_option( 'ca_pgd', 1 );
		update_option( 'ca_rd', 1 );
		update_option( 'ca_sd', 1 );
		update_option( 'ca_frontend', 1 );
		update_option( 'ca_cachetime', 3600 );
		update_option( 'ca_tracking', 1 );
		update_option( 'ca_track_username', 1 );
		update_option( 'ca_track_email', 1 );
		update_option( 'ca_track_youtube', 0 );
		update_option( 'ca_track_html5', 0 );
		update_option( 'ca_track_olp', '/go/,/out/' );
	}
}

function cadash_uninstall() {
	global $wpdb;
	require_once 'functions.php';
	if ( is_multisite() ) { // Cleanup Network install
		foreach ( ca_get_sites() as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_clicky_qr%%'" );
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_clicky_qr%%'" );
			delete_option( 'ca_sitekey' );
			delete_option( 'ca_siteid' );
			delete_option( 'ca_access' );
			delete_option( 'ca_pgd' );
			delete_option( 'ca_rd' );
			delete_option( 'ca_sd' );
			delete_option( 'ca_frontend' );
			delete_option( 'ca_cachetime' );
			delete_option( 'ca_tracking' );
			delete_option( 'ca_track_username' );
			delete_option( 'ca_track_email' );
			delete_option( 'ca_track_youtube' );
			delete_option( 'ca_track_html5' );
			delete_option( 'ca_track_olp' );
		}
		restore_current_blog();
	} else { // Cleanup Single install
		$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_clicky_qr%%'" );
		$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_clicky_qr%%'" );
		delete_option( 'ca_sitekey' );
		delete_option( 'ca_siteid' );
		delete_option( 'ca_access' );
		delete_option( 'ca_pgd' );
		delete_option( 'ca_rd' );
		delete_option( 'ca_sd' );
		delete_option( 'ca_frontend' );
		delete_option( 'ca_cachetime' );
		delete_option( 'ca_tracking' );
		delete_option( 'ca_track_username' );
		delete_option( 'ca_track_email' );
		delete_option( 'ca_track_youtube' );
		delete_option( 'ca_track_html5' );
		delete_option( 'ca_track_olp' );
	}
}

function ca_front_content( $content ) {
	global $post;
	if ( ! current_user_can( get_option( 'ca_access' ) ) or ! get_option( 'ca_frontend' ) ) {
		return $content;
	}
	if ( ( is_page() || is_single() ) && ! is_preview() ) {
		require_once 'functions.php';
		$api_url = "https://api.clicky.com/api/stats/4?";
		$siteid = get_option( 'ca_siteid' );
		$sitekey = get_option( 'ca_sitekey' );
		if ( ! get_option( 'ca_cachetime' ) ) {
			update_option( 'ca_cachetime', "3600" );
		}
		$content .= '<style>
		#ca_sdata td{
			line-height:1.5em;
			padding:2px;
			font-size:1em;
		}
		#ca_sdata{
			line-height:10px;
		}
		#ca_div, #ca_sdata{
			clear:both;
		}
		</style>';
		$page_url = esc_url( $_SERVER["REQUEST_URI"] );
		$post_id = $post->ID;
		$metric = 'type=pages';
		$from = "date=last-30-days";
		try {
			$serial = 'clicky_qr21' . $post_id;
			$transient = get_transient( $serial );
			if ( empty( $transient ) ) {
				$url = $api_url . "site_id=" . $siteid . "&sitekey=" . $sitekey . "&" . $from . "&" . $metric . "&daily=1" . "&filter=" . urlencode( $page_url ) . "&output=php";
				$result = unserialize( file_get_contents_clicky( $url ) );
				set_transient( $serial, $result, get_option( 'ca_cachetime' ) );
			} else {
				$result = $transient;
			}
		} catch ( exception $e ) {
			return $content;
		}
		$i = 0;
		if ( is_array( $result ) ) {
			foreach ( $result as $item ) {
				if ( is_array( $item ) ) {
					foreach ( $item as $date => $item1 ) {
						$goores[$i][0] = $date;
						if ( isset( $item1 ) ) {
							foreach ( $item1 as $item2 ) {
								$goores[$i][1] = $item2['value'];
							}
						} else {
							$goores[$i][1] = 0;
						}
						$i++;
					}
				} else {
					return $content;
				}
			}
		} else {
			return $content;
		}
		$j = 0;
		$ca_statsdata = "";
		for ( $j = $i - 1; $j >= 0; $j-- ) {
			if ( isset( $goores[$j][1] ) ) {
				$ca_statsdata .= "['" . $goores[$j][0] . "'," . $goores[$j][1] . "],";
			}
		}
		$ca_statsdata = wp_kses( rtrim( $ca_statsdata, ',' ), $GLOBALS['CADASH_ALLOW'] );
		$code = '<script type="text/javascript">

		  google.charts.load("current", {"packages":["corechart", "table", "orgchart", "geochart"]});

		  google.charts.setOnLoadCallback(ca_callback);

		  function ca_callback(){
				ca_drawstats();
				if(typeof ca_drawsd == "function"){
					ca_drawsd();
				}
		  }

		  function ca_drawstats() {
			var data = google.visualization.arrayToDataTable([' . "
			  ['" . __( "Date", 'clicky-analytics' ) . "', 'Visitors']," . $ca_statsdata . "
			]);

			var options = {
			  legend: {position: 'none'},
			  colors:['darkorange','#004411'],
			  pointSize: 3,
			  title: 'Visitors',
			  vAxis: {minValue: 0},
			  chartArea: {width: '90%', height: '50%'},
			  hAxis: { title: '" . __( "Date", 'clicky-analytics' ) . "',  titleTextStyle: {color: 'black'}, showTextEvery: 5}
			};

			var chart = new google.visualization.AreaChart(document.getElementById('ca_div'));
			chart.draw(data, options);

		  }
		";
		$code .= "</script>";
		$content .= $code . '<p><div id="ca_div"></div></p>';
		$metric = 'type=segmentation&segments=searches';
		$from = "date=last-30-days";
		try {
			$serial = 'clicky_qr22' . $post_id;
			$transient = get_transient( $serial );
			if ( empty( $transient ) ) {
				$url = $api_url . "site_id=" . $siteid . "&sitekey=" . $sitekey . "&" . $from . "&" . $metric . "&limit=30" . "&href=" . urlencode( $page_url ) . "&output=php";
				$result = unserialize( file_get_contents_clicky( $url ) );
				set_transient( $serial, $result, get_option( 'ca_cachetime' ) );
			} else {
				$result = $transient;
			}
		} catch ( exception $e ) {
			return $content;
		}
		$i = 0;
		if ( is_array( $result ) ) {
			foreach ( $result as $item ) {
				if ( is_array( $item ) ) {
					foreach ( $item as $date => $item1 ) {
						if ( ! $item1 ) {
							return $content;
						}
						foreach ( $item1 as $item2 ) {
							$goores[$i][0] = ca_validation( $item2['title'] );
							$goores[$i][1] = ca_validation( $item2['value'] );
							$i++;
						}
					}
				} else {
					return $content;
				}
			}
		}
		$j = 0;
		$ca_organicdata = "";
		for ( $j = 0; $j <= $i - 1; $j++ ) {
			$ca_organicdata .= "['" . $goores[$j][0] . "'," . $goores[$j][1] . "],";
		}
		$ca_organicdata = wp_kses( rtrim( $ca_organicdata, ',' ), $GLOBALS['CADASH_ALLOW'] );
		if ( $ca_organicdata ) {
			$code .= '<script type="text/javascript">
					google.charts.load("current", {"packages":["corechart", "table", "orgchart", "geochart"]});

					function ca_drawsd() {

					var datas = google.visualization.arrayToDataTable([' . "
					  ['" . __( "Top Searches", 'clicky-analytics' ) . "', '" . __( "Visits", 'clicky-analytics' ) . "']," . $ca_organicdata . "
					]);

					var options = {
						page: 'enable',
						pageSize: 6,
						width: '99%',
					};

					var chart = new google.visualization.Table(document.getElementById('ca_sdata'));
					chart.draw(datas, options);

				  }";
		}
		$code .= "</script>";
		$content .= $code . '<p><div id="ca_sdata" ></div></p>';
	}
	return $content;
}

function ca_content() {
	require_once 'functions.php';
	$api_url = "https://api.clicky.com/api/stats/4?";
	$siteid = get_option( 'ca_siteid' );
	$sitekey = get_option( 'ca_sitekey' );
	if ( ( ! get_option( 'ca_siteid' ) ) or ( ! get_option( 'ca_sitekey' ) ) ) {
		echo "<p>" . __( "Check your Site ID and Site Key! For further help check", 'clicky-analytics' ) . " <a href='https://deconf.com/clicky-analytics-dashboard-wordpress/'>" . __( "the documentation", 'clicky-analytics' ) . "</a></p>";
		ca_clear_cache();
		return;
	}
	if ( isset( $_REQUEST['ca_query'] ) ) {
		$ca_query = sanitize_text_field( $_REQUEST['ca_query'] );
	} else {
		$ca_query = "visits";
	}
	if ( isset( $_REQUEST['ca_period'] ) ) {
		$ca_period = sanitize_text_field( $_REQUEST['ca_period'] );
	} else {
		$ca_period = "last-30-days";
	}
	$from = "date=" . $ca_period;
	switch ( $ca_query ) {
		case 'actions' :
			$title = __( "Actions", 'clicky-analytics' );
			$metric = "type=actions";
			break;
		case 'traffic-sources' :
			$title = __( "Searches", 'clicky-analytics' );
			$metric = "type=traffic-sources";
			break;
		case 'time-average' :
			$title = __( "Time Average", 'clicky-analytics' );
			$metric = "type=time-average";
			break;
		case 'bounce-rate' :
			$title = __( "Bounce Rate", 'clicky-analytics' );
			$metric = "type=bounce-rate";
			break;
		default :
			$title = __( "Visitors", 'clicky-analytics' );
			$metric = "type=visitors";
	}
	try {
		$serial = 'clicky_qr1' . str_replace( array( ',', '-', date( 'Y' ) ), "", $from . $metric );
		$transient = get_transient( $serial );
		if ( empty( $transient ) ) {
			$url = $api_url . "site_id=" . $siteid . "&sitekey=" . $sitekey . "&" . $from . "&" . $metric . "&daily=1" . "&output=php";
			$result = unserialize( file_get_contents_clicky( $url ) );
			set_transient( $serial, $result, get_option( 'ca_cachetime' ) );
		} else {
			$result = $transient;
		}
	} catch ( exception $e ) {
		echo "<p>" . __( "ERROR LOG:", 'clicky-analytics' ) . "</p><p>" . $e . "</p>";
		ca_clear_cache();
		return;
	}
	if ( ! is_array( $result ) ) {
		echo "<p>" . __( "ERROR LOG:", 'clicky-analytics' ) . "</p><p>" . __( "Check your Site ID and Site Key! For further help check", 'clicky-analytics' ) . " <a href='https://deconf.com/clicky-analytics-dashboard-wordpress/'>" . __( "the documentation", 'clicky-analytics' ) . "</a></p>";
		ca_clear_cache();
		return;
	}
	$i = 0;
	foreach ( $result as $item ) {
		if ( is_array( $item ) ) {
			foreach ( $item as $date => $item1 ) {
				$goores[$i][0] = $date;
				if ( is_array( $item1 ) ) {
					foreach ( $item1 as $item2 ) {
						if ( isset( $item2['title'] ) and $item2['title'] == "Searches" )
							$goores[$i][1] = $item2['value'];
						else if ( ! isset( $item2['title'] ) )
							$goores[$i][1] = $item2['value'];
					}
				} else {
					$goores[$i][1] = 0;
				}
				$i++;
			}
		} else {
			echo $item . "<p>" . __( "For further help check", 'clicky-analytics' ) . " <a href='https://deconf.com/clicky-analytics-dashboard-wordpress/'>" . __( "the documentation", 'clicky-analytics' ) . "</a></p>";
			ca_clear_cache();
			return;
		}
	}
	$j = 0;
	$chart1_data = "";
	for ( $j = $i - 1; $j >= 0; $j-- ) {
		$chart1_data .= "['" . $goores[$j][0] . "'," . $goores[$j][1] . "],";
	}
	$chart1_data = wp_kses( rtrim( $chart1_data, ',' ), $GLOBALS['CADASH_ALLOW'] );
	$metrics = 'type=visitors,actions,visitors-online,traffic-sources,time-average,bounce-rate';
	try {
		$serial = 'clicky_qr2' . str_replace( array( ',', '-', date( 'Y' ) ), "", $from );
		$transient = get_transient( $serial );
		if ( empty( $transient ) ) {
			$url = $api_url . "site_id=" . $siteid . "&sitekey=" . $sitekey . "&" . $from . "&" . $metrics . "&output=php";
			$result = unserialize( file_get_contents_clicky( $url ) );
			set_transient( $serial, $result, get_option( 'ca_cachetime' ) );
		} else {
			$result = $transient;
		}
	} catch ( exception $e ) {
		echo "<p>" . __( "ERROR LOG:", 'clicky-analytics' ) . "</p><p>" . $e . "</p>";
		ca_clear_cache();
		return;
	}
	$i = 0;
	$goores = array_fill( 0, 5, array_fill( 0, 1, 0 ) );
	foreach ( $result as $item ) {
		if ( is_array( $item ) ) {
			foreach ( $item as $date => $item1 ) {
				if ( is_array( $item1 ) ) {
					$goores[$i][0] = $date;
					foreach ( $item1 as $item2 ) {
						if ( isset( $item2['title'] ) and $item2['title'] == "Searches" )
							$goores[$i][1] = $item2['value'];
						else if ( ! isset( $item2['title'] ) )
							$goores[$i][1] = $item2['value'];
					}
				}
				$i++;
			}
		} else {
			echo $item . "<p>" . __( "For further help check", 'clicky-analytics' ) . " <a href='https://deconf.com/clicky-analytics-dashboard-wordpress/'>" . __( "the documentation", 'clicky-analytics' ) . "</a></p>";
			return;
		}
	}
	$code = '
    <script type="text/javascript">

	  google.charts.load("current", {"packages":["corechart", "table", "orgchart", "geochart"]});

	  google.charts.setOnLoadCallback(ca_callback);

	  function ca_callback(){
			ca_drawstats();
			if(typeof ca_drawmap == "function"){
				ca_drawmap();
			}
			if(typeof ca_toppages == "function"){
				ca_toppages();
			}
			if(typeof ca_topreferrers== "function"){
				ca_topreferrers();
			}
			if(typeof ca_searches == "function"){
				ca_searches();
			}
	  }

      function ca_drawstats() {
        var data = google.visualization.arrayToDataTable([' . "
          ['" . __( "Date", 'clicky-analytics' ) . "', '" . $title . "']," . $chart1_data . "
        ]);

        var options = {
		  legend: {position: 'none'},
		  colors:['darkorange','#004411'],
		  pointSize: 3,
          title: '" . $title . "',
		  chartArea: {width: '80%'},
		  vAxis: {minValue: 0},
          hAxis: { title: '" . __( "Date", 'clicky-analytics' ) . "',  titleTextStyle: {color: 'black'}, showTextEvery: 6}
		};

        var chart = new google.visualization.AreaChart(document.getElementById('ca_div'));
		chart.draw(data, options);

      }";
	if ( get_option( 'ca_pgd' ) ) {
		$ca_toppages = ca_top_pages( $api_url, $siteid, $sitekey, $from );
		if ( $ca_toppages ) {
			$code .= '
					google.charts.load("current", {"packages":["corechart", "table", "orgchart", "geochart"]});

					function ca_toppages() {

					var datas = google.visualization.arrayToDataTable([' . "
					  ['" . __( "Top Pages", 'clicky-analytics' ) . "', '" . __( "Visits", 'clicky-analytics' ) . "']," . $ca_toppages . "
					]);

					var options = {
						page: 'enable',
						pageSize: 6,
						width: '100%',
					};

					var chart = new google.visualization.Table(document.getElementById('ca_toppages'));
					chart.draw(datas, options);

				  }";
		}
	}
	if ( get_option( 'ca_rd' ) ) {
		$ca_referrers = ca_top_referrers( $api_url, $siteid, $sitekey, $from );
		// print_r($ca_referrers);
		if ( $ca_referrers ) {
			$code .= '
					google.charts.load("current", {"packages":["corechart", "table", "orgchart", "geochart"]});

					function ca_topreferrers() {

					var datas = google.visualization.arrayToDataTable([' . "
					  ['" . __( "Top Referrers", 'clicky-analytics' ) . "', '" . __( "Visits", 'clicky-analytics' ) . "']," . $ca_referrers . "
					]);

					var options = {
						page: 'enable',
						pageSize: 6,
						width: '100%',
					};

					var chart = new google.visualization.Table(document.getElementById('ca_referrers'));
					chart.draw(datas, options);

				  }";
		}
	}
	if ( get_option( 'ca_sd' ) ) {
		$ca_searches = ca_top_searches( $api_url, $siteid, $sitekey, $from );
		if ( $ca_searches ) {
			$code .= '
					google.charts.load("current", {"packages":["corechart", "table", "orgchart", "geochart"]});

					function ca_searches() {

					var datas = google.visualization.arrayToDataTable([' . "
					  ['" . __( "Top Searches", 'clicky-analytics' ) . "', '" . __( "Visits", 'clicky-analytics' ) . "']," . $ca_searches . "
					]);

					var options = {
						page: 'enable',
						pageSize: 6,
						width: '100%',
					};

					var chart = new google.visualization.Table(document.getElementById('ca_searches'));
					chart.draw(datas, options);

				  }";
		}
	}
	$code .= "</script>";
	$code .= '
	<div id="clicky-dash">
	<center>
		<div id="ca_buttons_div">
		<center>
			<input class="clickybutton" type="button" value="' . __( "Today", 'clicky-analytics' ) . '" onClick="window.location=\'?ca_period=today&ca_query=' . $ca_query . '\'" />
			<input class="clickybutton" type="button" value="' . __( "Yesterday", 'clicky-analytics' ) . '" onClick="window.location=\'?ca_period=yesterday&ca_query=' . $ca_query . '\'" />
			<input class="clickybutton" type="button" value="' . __( "Last 7 Days", 'clicky-analytics' ) . '" onClick="window.location=\'?ca_period=last-7-days&ca_query=' . $ca_query . '\'" />
			<input class="clickybutton" type="button" value="' . __( "Last 30 Days", 'clicky-analytics' ) . '" onClick="window.location=\'?ca_period=last-30-days&ca_query=' . $ca_query . '\'" />
		</center>
		</div>

		<div id="ca_div"></div>

		<div id="ca_details_div">
			<center>
			<table class="clickytable" cellpadding="4">
			<tr>
			<td width="24%">' . __( "Online", 'clicky-analytics' ) . ':</td>
			<td width="12%" id="clickyonline" class="clickyvalue">' . (int) $goores[2][1] . '</td>
			<td width="24%">' . __( "Visitors", 'clicky-analytics' ) . ':</td>
			<td width="12%" class="clickyvalue"><a href="?ca_query=visitors&ca_period=' . $ca_period . '" class="clickytable">' . (int) $goores[0][1] . '</a></td>
			<td width="24%">' . __( "Actions", 'clicky-analytics' ) . ':</td>
			<td width="12%" class="clickyvalue"><a href="?ca_query=actions&ca_period=' . $ca_period . '" class="clickytable">' . (int) $goores[1][1] . '</a></td>
			</tr>
			<tr>
			<td width="24%">' . __( "Searches", 'clicky-analytics' ) . ':</td>
			<td width="12%" class="clickyvalue"><a href="?ca_query=traffic-sources&ca_period=' . $ca_period . '" class="clickytable">' . ( isset( $goores[3][1] ) ? (int) $goores[3][1] : 0 ) . '</a></td>
			<td width="24%">' . __( "Time AVG", 'clicky-analytics' ) . ':</td>
			<td width="12%" class="clickyvalue"><a href="?ca_query=time-average&ca_period=' . $ca_period . '" class="clickytable">' . (int) $goores[4][1] . '</a></td>
			<td width="24%">' . __( "Bounce", 'clicky-analytics' ) . ':</td>
			<td width="12%" class="clickyvalue"><a href="?ca_query=bounce-rate&ca_period=' . $ca_period . '" class="clickytable">' . (double) $goores[5][1] . '</a></td>
			</tr>
			</table>
			</center>
		</div>
	</center>
	</div>';
	$metrics = 'type=visitors-online';
	$url = $api_url . "site_id=" . $siteid . "&sitekey=" . $sitekey . "&" . $from . "&" . $metrics . "&output=json";
	$code .= '

	<script type="text/javascript">

	 function online_refresh(){
		jQuery.getJSON("' . $url . '&json_callback=?", function(data){
			if (data[0]["dates"][0]["items"][0]["value"]!==document.getElementById("clickyonline").innerHTML){
				jQuery("#clickyonline").fadeOut("slow");
				jQuery("#clickyonline").fadeOut(500);
				jQuery("#clickyonline").fadeOut("slow", function() {
					document.getElementById("clickyonline").innerHTML = data[0]["dates"][0]["items"][0]["value"];
				});
				jQuery("#clickyonline").fadeIn("slow");
				jQuery("#clickyonline").fadeIn(500);
				jQuery("#clickyonline").fadeIn("slow", function() {
				});
			};
		});
   };
   setInterval(online_refresh, 60000);
   </script>';
	$code .= '</center>';
	if ( get_option( 'ca_pgd' ) )
		$code .= '<br /><br /><div id="ca_toppages"></div>';
	if ( get_option( 'ca_rd' ) )
		$code .= '<div id="ca_referrers"></div>';
	if ( get_option( 'ca_sd' ) )
		$code .= '<div id="ca_searches"></div>';
	echo $code;
}
?>
