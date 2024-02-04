<?php
/**
 * Plugin Name: Clicky Analytics
 * Plugin URI: https://deconf.com
 * Description: Displays Clicky Analytics reports into your Dashboard. Automatically inserts the tracking code in every page of your website.
 * Author: Alin Marcu
 * Version: 2.2.3
 * Author URI: https://deconf.com
 * Text Domain: clicky-analytics
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

// Plugin Version
if ( ! defined( 'CAWP_CURRENT_VERSION' ) ) {
	define( 'CAWP_CURRENT_VERSION', '2.2.3' );
}

if ( ! defined( 'CAWP_ENDPOINT_URL' ) ) {
	define( 'CAWP_ENDPOINT_URL', 'https://api.clicky.com/api/stats/4?' );
}

if ( ! defined( 'CAWP_SITE_URL' ) ) {
	define( 'CAWP_SITE_URL', site_url( '/' ) );
}

if ( ! class_exists( 'CAWP_Manager' ) ) {

	final class CAWP_Manager {

		private static $instance = null;

		public $config = null;

		public $frontend_actions = null;

		public $common_actions = null;

		public $backend_actions = null;

		public $frontend_item_reports = null;

		public $backend_setup = null;

		public $frontend_setup = null;

		public $backend_widgets = null;

		public $backend_item_reports = null;

		public $capi_controller = null;

		public $tracking = null;

		/**
		 * Construct forbidden
		 */
		private function __construct() {
			if ( null !== self::$instance ) {
				_doing_it_wrong( __FUNCTION__, __( "This is not allowed, read the documentation!", 'clicky-analytics' ), '4.6' );
			}
		}

		/**
		 * Clone warning
		 */
		private function __clone() {
			_doing_it_wrong( __FUNCTION__, __( "This is not allowed, read the documentation!", 'clicky-analytics' ), '4.6' );
		}

		/**
		 * Wakeup warning
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( "This is not allowed, read the documentation!", 'clicky-analytics' ), '4.6' );
		}

		/**
		 * Creates a single instance for CAWP and makes sure only one instance is present in memory.
		 *
		 * @return CAWP_Manager
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
				self::$instance->setup();
				self::$instance->config = new CAWP_Config();
			}
			return self::$instance;
		}

		/**
		 * Defines constants and loads required resources
		 */
		private function setup() {

			// Plugin Path
			if ( ! defined( 'CAWP_DIR' ) ) {
				define( 'CAWP_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin URL
			if ( ! defined( 'CAWP_URL' ) ) {
				define( 'CAWP_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin main File
			if ( ! defined( 'CAWP_FILE' ) ) {
				define( 'CAWP_FILE', __FILE__ );
			}

			/**
			 * Load Tools class
			 */
			include_once ( CAWP_DIR . 'tools/tools.php' );

			/**
			 * Load Config class
			 */
			include_once ( CAWP_DIR . 'config.php' );

			/**
			 * Load CAPI Controller class
			 */
			include_once ( CAWP_DIR . 'tools/capi.php' );

			/**
			 * Plugin i18n
			 */
			add_action( 'init', array( self::$instance, 'load_i18n' ) );

			/**
			 * Plugin Init
			 */
			add_action( 'init', array( self::$instance, 'load' ) );

			/**
			 * Include Install
			 */
			include_once ( CAWP_DIR . 'install/install.php' );
			register_activation_hook( CAWP_FILE, array( 'CAWP_Install', 'install' ) );

			/**
			 * Include Uninstall
			 */
			include_once ( CAWP_DIR . 'install/uninstall.php' );
			register_uninstall_hook( CAWP_FILE, array( 'CAWP_Uninstall', 'uninstall' ) );
		}

		/**
		 * Load i18n
		 */
		public function load_i18n() {
			load_plugin_textdomain( 'clicky-analytics', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Conditional load
		 */
		public function load() {
			if ( is_admin() ) {
				if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
					if ( CAWP_Tools::check_roles( self::$instance->config->options['access_back'] ) ) {
						/**
						 * Load Backend ajax actions
						 */
						include_once ( CAWP_DIR . 'admin/ajax-actions.php' );
						self::$instance->backend_actions = new CAWP_Backend_Ajax();
					}

					/**
					 * Load Frontend ajax actions
					 */
					include_once ( CAWP_DIR . 'front/ajax-actions.php' );
					self::$instance->frontend_actions = new CAWP_Frontend_Ajax();

					/**
					 * Load Common ajax actions
					 */
					include_once ( CAWP_DIR . 'common/ajax-actions.php' );
					self::$instance->common_actions = new CAWP_Common_Ajax();

					if ( self::$instance->config->options['backend_item_reports'] ) {
						/**
						 * Load Backend Item Reports for Quick Edit
						 */
						include_once ( CAWP_DIR . 'admin/item-reports.php' );
						self::$instance->backend_item_reports = new CAWP_Backend_Item_Reports();
					}
				} else if ( CAWP_Tools::check_roles( self::$instance->config->options['access_back'] ) ) {
					/**
					 * Load Backend Setup
					 */
					include_once ( CAWP_DIR . 'admin/setup.php' );
					self::$instance->backend_setup = new CAWP_Backend_Setup();

					if ( self::$instance->config->options['dashboard_widget'] ) {
						/**
						 * Load Backend Widget
						 */
						include_once ( CAWP_DIR . 'admin/widgets.php' );
						self::$instance->backend_widgets = new CAWP_Backend_Widgets();
					}

					if ( self::$instance->config->options['backend_item_reports'] ) {
						/**
						 * Load Backend Item Reports
						 */
						include_once ( CAWP_DIR . 'admin/item-reports.php' );
						self::$instance->backend_item_reports = new CAWP_Backend_Item_Reports();
					}
				}
			} else {
				if ( CAWP_Tools::check_roles( self::$instance->config->options['access_front'] ) ) {
					/**
					 * Load Frontend Setup
					 */
					include_once ( CAWP_DIR . 'front/setup.php' );
					self::$instance->frontend_setup = new CAWP_Frontend_Setup();

					if ( self::$instance->config->options['frontend_item_reports'] ) {
						/**
						 * Load Frontend Item Reports
						 */
						include_once ( CAWP_DIR . 'front/item-reports.php' );
						self::$instance->frontend_item_reports = new CAWP_Frontend_Item_Reports();
					}
				}

				if ( isset( self::$instance->config->options['siteid'] ) && self::$instance->config->options['siteid'] && (1 == self::$instance->config->options['tracking']) ) {
					/*
					 * Load tracking
					 */
					include_once ( CAWP_DIR . 'front/tracking.php' );
					self::$instance->tracking = new CAWP_Tracking();
				}
			}
		}
	}
}

/**
 * Returns a unique instance of CAWP
 */
function CAWP() {
	return CAWP_Manager::instance();
}

/**
 * Start CAWP
 */
CAWP();
