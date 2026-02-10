<?php
/**
 * Plugin Name: Manufacturing ERP Pro
 * Description: A full-featured Manufacturing ERP for WordPress. Supports discrete and light process manufacturing.
 * Version: 1.0.0
 * Author: Jules
 * Text Domain: manufacturing-erp-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'MEP_VERSION', '1.0.0' );
define( 'MEP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MEP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class Manufacturing_ERP_Pro {

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-db.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-cpt.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-inventory.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-bom.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-mrp.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-quality.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-procurement.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-reports.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-seeder.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-api.php';

		if ( is_admin() ) {
			require_once MEP_PLUGIN_DIR . 'admin/class-mep-admin.php';
		}
	}

	/**
	 * Hook into WordPress.
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'MEP_DB', 'create_tables' ) );

		add_action( 'plugins_loaded', array( $this, 'init_modules' ) );
	}

	/**
	 * Initialize modules.
	 */
	public function init_modules() {
		MEP_CPT::get_instance();
		MEP_API::get_instance();

		if ( is_admin() ) {
			MEP_Admin::get_instance();
		}
	}
}

// Start the plugin
Manufacturing_ERP_Pro::get_instance();
