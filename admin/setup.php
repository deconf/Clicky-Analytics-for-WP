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

if ( ! class_exists( 'CAWP_Backend_Setup' ) ) {

	final class CAWP_Backend_Setup {

		private $cawp;

		public function __construct() {
			$this->cawp = CAWP();
			/**
			 * Styles & Scripts
			 */
			add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );
			/**
			 * Site Menu
			 */
			add_action( 'admin_menu', array( $this, 'site_menu' ) );
			/**
			 * Add Custom Dashboard
			 */
			add_action( 'admin_menu', array( $this, 'dashboard_menu' ) );
			/**
			* Setup link
			*/
			add_filter( "plugin_action_links_" . plugin_basename( CAWP_DIR . 'cawp.php' ), array( $this, 'setup_link' ) );
			/**
			 * Updated admin notice
			 */
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
		}

		/**
		 * Add Site Menu
		 */
		public function site_menu() {
			global $wp_version;
			if ( current_user_can( 'manage_options' ) ) {
				include ( CAWP_DIR . 'admin/settings.php' );
				add_menu_page( __( "Clicky Analytics", 'clicky-analytics' ), __( "Clicky Analytics", 'clicky-analytics' ), 'manage_options', 'cawp_setup', array( 'CAWP_Settings', 'setup' ), 'dashicons-analytics' );
				add_submenu_page( 'cawp_setup', __( "Setup", 'clicky-analytics' ), __( "Setup", 'clicky-analytics' ), 'manage_options', 'cawp_setup', array( 'CAWP_Settings', 'setup' ) );
				add_submenu_page( 'cawp_setup', __( "Settings", 'clicky-analytics' ), __( "Settings", 'clicky-analytics' ), 'manage_options', 'cawp_settings', array( 'CAWP_Settings', 'settings' ) );
				add_submenu_page( 'cawp_setup', __( "Debug", 'clicky-analytics' ), __( "Debug", 'clicky-analytics' ), 'manage_options', 'cawp_errors_debugging', array( 'CAWP_Settings', 'errors_debugging' ) );
			}
		}
		/**
		 * Add Dashboard Menu
		 */
		public function dashboard_menu(){
			if ( current_user_can( 'manage_options' ) && $this->cawp->config->options['sitekey'] && $this->cawp->config->options['siteid'] ) {
			 add_dashboard_page( __( 'Clicky Analytics', 'clicky-analytics' ), __( 'Clicky Analytics', 'clicky' ), 'manage_options', 'clicky_analytics', array( $this, 'dashboard_page' ) );
			}
		}
		/**
		 * Add Custom Dashboard
		 */
		public function dashboard_page() {
			$siteid = $this->cawp->config->options['siteid'];
			$sitekey = $this->cawp->config->options['sitekey'];
			?>
				<br />
				<iframe id="clicky-analytics" style="margin-left: 20px; width: 100%; height: 1000px;" src="https://clicky.com/stats/wp-iframe?site_id=<?php echo esc_attr($siteid); ?>&sitekey=<?php echo esc_attr($sitekey); ?>"></iframe>
			<?php
		}
		/**
		 * Styles & Scripts conditional loading (based on current URI)
		 *
		 * @param
		 *            $hook
		 */
		public function load_styles_scripts( $hook ) {
			$new_hook = explode( '_page_', $hook );

			if ( isset( $new_hook[1] ) ) {
				$new_hook = '_page_' . $new_hook[1];
			} else {
				$new_hook = $hook;
			}
			/**
			 * CAWP main stylesheet
			 */
			wp_enqueue_style( 'cawp', CAWP_URL . 'admin/css/cawp' . CAWP_Tools::script_debug_suffix() . '.css', null, CAWP_CURRENT_VERSION );
			/**
			 * CAWP UI
			 */
			if ( CAWP_Tools::get_cache( 'capi_errors' ) ) {
				$ed_bubble = '!';
			} else {
				$ed_bubble = '';
			}

			wp_enqueue_script( 'cawp-backend-ui', plugins_url( 'js/ui' . CAWP_Tools::script_debug_suffix() . '.js', __FILE__ ), array( 'jquery' ), CAWP_CURRENT_VERSION, true );

			/* @formatter:off */
			wp_localize_script( 'cawp-backend-ui', 'cawp_ui_data', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'cawp_dismiss_notices' ),
				'ed_bubble' => $ed_bubble,
			)
			);
			/* @formatter:on */

			$properties = false;

			/**
			 * Main Dashboard Widgets Styles & Scripts
			 */
			$widgets_hooks = array( 'index.php' );

			if ( in_array( $new_hook, $widgets_hooks ) ) {
				if ( CAWP_Tools::check_roles( $this->cawp->config->options['access_back'] ) && $this->cawp->config->options['dashboard_widget'] ) {

					wp_enqueue_style( 'cawp-nprogress', CAWP_URL . 'common/nprogress/nprogress' . CAWP_Tools::script_debug_suffix() . '.css', null, CAWP_CURRENT_VERSION );

					wp_enqueue_style( 'cawp-daterangepicker', CAWP_URL . 'common/daterangepicker/daterangepicker' . CAWP_Tools::script_debug_suffix() . '.css', null, CAWP_CURRENT_VERSION );

					wp_enqueue_style( 'cawp-backend-item-reports', CAWP_URL . 'admin/css/admin-widgets' . CAWP_Tools::script_debug_suffix() . '.css', null, CAWP_CURRENT_VERSION );

					wp_register_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), null );

					wp_enqueue_script( 'cawp-nprogress', CAWP_URL . 'common/nprogress/nprogress' . CAWP_Tools::script_debug_suffix() . '.js', array( 'jquery' ), CAWP_CURRENT_VERSION );

					wp_enqueue_script( 'cawp-moment', CAWP_URL . 'common/daterangepicker/moment' . CAWP_Tools::script_debug_suffix() . '.js', array( 'jquery' ), CAWP_CURRENT_VERSION );

					wp_enqueue_script( 'cawp-daterangepicker', CAWP_URL . 'common/daterangepicker/daterangepicker' . CAWP_Tools::script_debug_suffix() . '.js', array( 'jquery' ), CAWP_CURRENT_VERSION );

					wp_enqueue_script( 'cawp-backend-dashboard-reports', CAWP_URL . 'common/js/reports' . CAWP_Tools::script_debug_suffix() . '.js', array( 'jquery', 'googlecharts', 'cawp-nprogress', 'cawp-moment', 'cawp-daterangepicker', 'jquery-ui-core', 'jquery-ui-position' ), CAWP_CURRENT_VERSION, true );

					/* @formatter:off */

					wp_localize_script( 'cawp-backend-dashboard-reports', 'cawpItemData', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'security' => wp_create_nonce( 'cawp_backend_item_reports' ),
						'reportList' => array(
							'visitors' => __( "Visitors", 'clicky-analytics' ),
							'actions-pageviews' => __( "Pageviews", 'clicky-analytics' ),
							'time-average' => __( "Time Average", 'clicky-analytics' ),
							'bounce-rate' => __( "Bounce Rate", 'clicky-analytics' ),
							'locations' => __( "Locations", 'clicky-analytics' ),
							'pages' =>  __( "Pages", 'clicky-analytics' ),
							'referrers' => __( "Referrers", 'clicky-analytics' ),
						),
						'i18n' => array(
							__( "A JavaScript Error is blocking plugin resources!", 'clicky-analytics' ), //0
							__( "Search ...", 'clicky-analytics' ),
							__( "Download", 'clicky-analytics' ),
							__( "Clicky Analytics", 'clicky-analytics' ),
							__( "", 'clicky-analytics' ),
							__( "Visitors", 'clicky-analytics' ),
							__( "Pageviews", 'clicky-analytics' ),
							__( "Time Average", 'clicky-analytics' ),
							__( "Bounce Rate", 'clicky-analytics' ),
							__( "Server Errors", 'clicky-analytics' ),
							__( "Not Found", 'clicky-analytics' ),
							__( "Invalid response", 'clicky-analytics' ),
							__( "Processing data, please check again in a few days", 'clicky-analytics' ),
							__( "This report is unavailable", 'clicky-analytics' ),
							__( "report generated by", 'clicky-analytics' ), //14
							__( "This plugin needs an authorization:", 'clicky-analytics' ) . ' <a href="' . menu_page_url( 'cawp_settings', false ) . '">' . __( "authorize the plugin", 'clicky-analytics' ) . '</a>.',
						),
						'colorVariations' => CAWP_Tools::variations( $this->cawp->config->options['theme_color'] ),
						'mapsApiKey' => apply_filters( 'cawp_maps_api_key', $this->cawp->config->options['maps_api_key'] ),
						'language' => get_bloginfo( 'language' ),
						'propertyList' => $properties,
						'scope' => 'admin-widgets',
					)

					);
					/* @formatter:on */
				}
			}
			/**
			 * Posts/Pages List Styles & Scripts
			 */
			$contentstats_hooks = array( 'edit.php' );
			if ( in_array( $hook, $contentstats_hooks ) ) {
				if ( CAWP_Tools::check_roles( $this->cawp->config->options['access_back'] ) && $this->cawp->config->options['backend_item_reports'] ) {

					wp_enqueue_style( 'cawp-nprogress', CAWP_URL . 'common/nprogress/nprogress' . CAWP_Tools::script_debug_suffix() . '.css', null, CAWP_CURRENT_VERSION );

					wp_enqueue_style( 'cawp-daterangepicker', CAWP_URL . 'common/daterangepicker/daterangepicker' . CAWP_Tools::script_debug_suffix() . '.css', null, CAWP_CURRENT_VERSION );

					wp_enqueue_style( 'cawp-backend-item-reports', CAWP_URL . 'admin/css/item-reports' . CAWP_Tools::script_debug_suffix() . '.css', null, CAWP_CURRENT_VERSION );

					wp_enqueue_style( "wp-jquery-ui-dialog" );

					wp_register_script( 'googlecharts', 'https://www.gstatic.com/charts/loader.js', array(), null );

					wp_enqueue_script( 'cawp-nprogress', CAWP_URL . 'common/nprogress/nprogress' . CAWP_Tools::script_debug_suffix() . '.js', array( 'jquery' ), CAWP_CURRENT_VERSION );

					wp_enqueue_script( 'cawp-moment', CAWP_URL . 'common/daterangepicker/moment' . CAWP_Tools::script_debug_suffix() . '.js', array( 'jquery' ), CAWP_CURRENT_VERSION );

					wp_enqueue_script( 'cawp-daterangepicker', CAWP_URL . 'common/daterangepicker/daterangepicker' . CAWP_Tools::script_debug_suffix() . '.js', array( 'jquery' ), CAWP_CURRENT_VERSION );

					wp_enqueue_script( 'cawp-backend-item-reports', CAWP_URL . 'common/js/reports' . CAWP_Tools::script_debug_suffix() . '.js', array( 'cawp-nprogress', 'googlecharts', 'cawp-moment', 'cawp-daterangepicker', 'jquery', 'jquery-ui-dialog' ), CAWP_CURRENT_VERSION, true );

					/* @formatter:off */
					wp_localize_script( 'cawp-backend-item-reports', 'cawpItemData', array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'security' => wp_create_nonce( 'cawp_backend_item_reports' ),
						'reportList' => array(
							'visitors' => __( "Visitors", 'clicky-analytics' ),
							'actions-pageviews' => __( "Pageviews", 'clicky-analytics' ),
							'time-average' => __( "Time Average", 'clicky-analytics' ),
							'bounce-rate' => __( "Bounce Rate", 'clicky-analytics' ),
							'locations' => __( "Locations", 'clicky-analytics' ),
							'referrers' => __( "Referrers", 'clicky-analytics' ),
						),
						'i18n' => array(
							__( "A JavaScript Error is blocking plugin resources!", 'clicky-analytics' ), //0
							__( "", 'clicky-analytics' ),
							__( "Download", 'clicky-analytics' ),
							__( "", 'clicky-analytics' ),
							__( "", 'clicky-analytics' ),
							__( "Visitors", 'clicky-analytics' ),
							__( "Pageviews", 'clicky-analytics' ),
							__( "Time Average", 'clicky-analytics' ),
							__( "Bounce Rate", 'clicky-analytics' ),
							__( "Server Errors", 'clicky-analytics' ),
							__( "Not Found", 'clicky-analytics' ),
							__( "Invalid response", 'clicky-analytics' ),
							__( "Processing data, please check again in a few days", 'clicky-analytics' ),
							__( "This report is unavailable", 'clicky-analytics' ),
							__( "report generated by", 'clicky-analytics' ), //14
							__( "This plugin needs an authorization:", 'clicky-analytics' ) . ' <a href="' . menu_page_url( 'cawp_settings', false ) . '">' . __( "authorize the plugin", 'clicky-analytics' ) . '</a>.',
						),
						'colorVariations' => CAWP_Tools::variations( $this->cawp->config->options['theme_color'] ),
						'mapsApiKey' => apply_filters( 'cawp_maps_api_key', $this->cawp->config->options['maps_api_key'] ),
						'language' => get_bloginfo( 'language' ),
						'propertyList' => false,
						'scope' => 'admin-item',
						)
					);
					/* @formatter:on */
				}
			}
			/**
			 * Settings Styles & Scripts
			 */
			$settings_hooks = array( '_page_cawp_setup', '_page_cawp_settings', '_page_cawp_frontend_settings', '_page_cawp_errors_debugging' );

			if ( in_array( $new_hook, $settings_hooks ) ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker-script-handle', plugins_url( 'js/wp-color-picker-script' . CAWP_Tools::script_debug_suffix() . '.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
				wp_enqueue_script( 'cawp-settings', plugins_url( 'js/settings' . CAWP_Tools::script_debug_suffix() . '.js', __FILE__ ), array( 'jquery' ), CAWP_CURRENT_VERSION, true );
			}
		}

		/**
		 * Add "Settings" link in Plugins List
		 *
		 * @param
		 *            $links
		 * @return array
		 */
		public function setup_link( $links ) {
			$setup_link = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=cawp_setup' ) ) . '">' . __( "Settings", 'clicky-analytics' ) . '</a>';
			array_unshift( $links, $setup_link );
			return $links;
		}

		/**
		 *  Add an admin notice after a manual or atuomatic update
		 */
		function admin_notice() {
			$currentScreen = get_current_screen();

			if ( ! current_user_can( 'manage_options' ) || strpos( $currentScreen->base, '_cawp_' ) === false ) {
				return;
			}

			if ( get_option( 'cawp_got_updated' ) ) :
				?>
<div id="cawp-notice" class="notice is-dismissible">
	<p><?php echo sprintf( __('Clicky Analytics has been updated to version %s.', 'clicky-analytics' ), CAWP_CURRENT_VERSION).' '.sprintf( __('For details, check out %1$s.', 'clicky-analytics' ), sprintf(' <a href="https://deconf.com/clicky-analytics-dashboard-wordpress/">%s</a>', __('the plugin documentation', 'clicky-analytics') ) ); ?></p>
</div>
<?php
			endif;

		}
	}
}
