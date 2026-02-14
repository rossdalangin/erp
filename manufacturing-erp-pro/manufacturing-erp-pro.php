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
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-equipment.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-reports.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-importer.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-seeder.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-meta-boxes.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-customizer.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-frontend.php';
		require_once MEP_PLUGIN_DIR . 'inc/class-mep-api.php';

		if ( is_admin() ) {
			require_once MEP_PLUGIN_DIR . 'admin/class-mep-admin.php';
			require_once MEP_PLUGIN_DIR . 'inc/class-mep-admin-ui.php';
		}
	}

	/**
	 * Hook into WordPress.
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'MEP_DB', 'create_tables' ) );
		register_activation_hook( __FILE__, array( $this, 'register_roles' ) );
		register_activation_hook( __FILE__, array( 'MEP_Frontend', 'create_portal_pages' ) );

		add_action( 'plugins_loaded', array( $this, 'init_modules' ) );
	}

	/**
	 * Initialize modules.
	 */
	public function init_modules() {
		MEP_CPT::get_instance();
		MEP_Meta_Boxes::init();
		MEP_Customizer::init();
		MEP_Frontend::get_instance();
		MEP_API::get_instance();

		if ( is_admin() ) {
			// Ensure roles are available for API checks
			if ( ! get_role( 'mep_administrator' ) ) {
				$this->register_roles();
			}
			MEP_Admin::get_instance();
			MEP_Admin_UI::get_instance();
		}
	}

	/**
	 * Register custom ERP roles.
	 */
	public function register_roles() {
		add_role( 'mep_administrator', __( 'ERP Administrator', 'manufacturing-erp-pro' ), array(
			'read' => true,
			'manage_options' => true,
			'mep_manage_all' => true
		) );

		add_role( 'mep_production_manager', __( 'ERP Production Manager', 'manufacturing-erp-pro' ), array(
			'read' => true,
			'mep_manage_production' => true,
			'mep_manage_bom' => true
		) );

		add_role( 'mep_warehouse_clerk', __( 'ERP Warehouse Clerk', 'manufacturing-erp-pro' ), array(
			'read' => true,
			'mep_manage_inventory' => true
		) );

		add_role( 'mep_quality_inspector', __( 'ERP Quality Inspector', 'manufacturing-erp-pro' ), array(
			'read' => true,
			'mep_manage_quality' => true
		) );
	}
}

// Start the plugin
Manufacturing_ERP_Pro::get_instance();
