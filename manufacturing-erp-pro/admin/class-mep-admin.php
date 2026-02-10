<?php
/**
 * MEP Admin Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Admin {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menus' ) );
		add_action( 'admin_init', array( $this, 'handle_utilities' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function add_menus() {
		add_menu_page(
			__( 'ERP Dashboard', 'manufacturing-erp-pro' ),
			__( 'ERP Pro', 'manufacturing-erp-pro' ),
			'manage_options',
			'mep-dashboard',
			array( $this, 'dashboard_page' ),
			'dashicons-chart-pie',
			24
		);

		add_submenu_page(
			'mep-dashboard',
			__( 'Inventory', 'manufacturing-erp-pro' ),
			__( 'Inventory', 'manufacturing-erp-pro' ),
			'manage_options',
			'mep-inventory',
			array( $this, 'inventory_page' )
		);

		add_submenu_page(
			'mep-dashboard',
			__( 'Production', 'manufacturing-erp-pro' ),
			__( 'Production', 'manufacturing-erp-pro' ),
			'manage_options',
			'mep-production',
			array( $this, 'production_page' )
		);

		add_submenu_page(
			'mep-dashboard',
			__( 'BOM Builder', 'manufacturing-erp-pro' ),
			__( 'BOM Builder', 'manufacturing-erp-pro' ),
			'manage_options',
			'mep-bom-builder',
			array( $this, 'bom_builder_page' )
		);

		add_submenu_page(
			'mep-dashboard',
			__( 'System Utilities', 'manufacturing-erp-pro' ),
			__( 'System Utilities', 'manufacturing-erp-pro' ),
			'manage_options',
			'mep-utilities',
			array( $this, 'utilities_page' )
		);

		add_submenu_page(
			'mep-dashboard',
			__( 'Setup Wizard', 'manufacturing-erp-pro' ),
			__( 'Setup Wizard', 'manufacturing-erp-pro' ),
			'manage_options',
			'mep-wizard',
			array( $this, 'wizard_page' )
		);

		add_submenu_page(
			'mep-dashboard',
			__( 'Traceability', 'manufacturing-erp-pro' ),
			__( 'Traceability', 'manufacturing-erp-pro' ),
			'manage_options',
			'mep-traceability',
			array( $this, 'traceability_page' )
		);
	}

	public function wizard_page() {
		echo '<div class="wrap"><h1>' . __( 'ERP Pro Setup Wizard', 'manufacturing-erp-pro' ) . '</h1>';
		echo '<div id="mep-wizard-root"></div></div>';
	}

	public function traceability_page() {
		echo '<div class="wrap"><h1>' . __( 'Lot & Batch Traceability', 'manufacturing-erp-pro' ) . '</h1>';
		echo '<div id="mep-traceability-root"></div></div>';
	}

	public function inventory_page() {
		echo '<div class="wrap"><h1>' . __( 'Warehouse & Inventory Management', 'manufacturing-erp-pro' ) . '</h1>';
		echo '<div id="mep-warehouse-root"></div></div>';
	}

	public function production_page() {
		echo '<div class="wrap"><h1>' . __( 'Production Planning & Kanban', 'manufacturing-erp-pro' ) . '</h1>';
		echo '<div id="mep-kanban-root"></div></div>';
	}

	public function bom_builder_page() {
		$product_id = isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0;
		echo '<div class="wrap"><h1>' . __( 'Visual BOM Builder', 'manufacturing-erp-pro' ) . '</h1>';
		if ( ! $product_id ) {
			echo '<p>' . __( 'Please select a product from the Products list to edit its BOM.', 'manufacturing-erp-pro' ) . '</p>';
			return;
		}
		echo '<div id="mep-bom-builder-root" data-product-id="' . esc_attr( $product_id ) . '"></div></div>';
	}

	public function dashboard_page() {
		$help_mode = get_option( 'mep_help_mode', 'off' );
		echo '<div class="wrap"><h1>' . __( 'Manufacturing ERP Pro Dashboard', 'manufacturing-erp-pro' ) . '</h1>';
		echo '<div class="mep-help-toggle-container" style="background: #fff; padding: 10px; border: 1px solid #ccc; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
				<strong>' . __( 'Interactive Help Mode:', 'manufacturing-erp-pro' ) . '</strong>
				<form method="post" style="display:inline;">
					<input type="hidden" name="mep_action_toggle_help" value="1">
					' . wp_nonce_field( 'mep_toggle_help', 'mep_nonce', true, false ) . '
					<button type="submit" class="button ' . ( $help_mode === 'on' ? 'button-primary' : '' ) . '">' . ( $help_mode === 'on' ? 'ON' : 'OFF' ) . '</button>
				</form>
				<small>' . __( 'When ON, hover over elements to see guided instructions.', 'manufacturing-erp-pro' ) . '</small>
			  </div>';
		echo '<div id="mep-dashboard-root"></div></div>';
	}

	public function utilities_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'System Utilities & Safeguards', 'manufacturing-erp-pro' ); ?></h1>

			<div class="card">
				<h2><?php _e( 'Sample Data Seeder', 'manufacturing-erp-pro' ); ?></h2>
				<p><?php _e( 'Populate the system with "LeatherCraft Manufacturing Co." demo data.', 'manufacturing-erp-pro' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'mep_seed_data', 'mep_nonce' ); ?>
					<input type="submit" name="mep_action_seed" class="button button-primary" value="<?php _e( 'Run Seeder', 'manufacturing-erp-pro' ); ?>">
				</form>
			</div>

			<div class="card" style="border: 1px solid #d63638;">
				<h2 style="color: #d63638;"><?php _e( 'Danger Zone: Database Reset', 'manufacturing-erp-pro' ); ?></h2>
				<p><?php _e( 'Wipe ERP data. This action is irreversible.', 'manufacturing-erp-pro' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'mep_reset_db', 'mep_nonce' ); ?>
					<p>
						<label>
							<input type="checkbox" name="mep_confirm_reset" required>
							<?php _e( 'I understand this will delete all ERP data.', 'manufacturing-erp-pro' ); ?>
						</label>
					</p>
					<p>
						<label><?php _e( 'Type phrase to confirm:', 'manufacturing-erp-pro' ); ?> <code>RESET PRODUCTION ENVIRONMENT</code><br>
							<input type="text" name="mep_confirm_phrase" class="regular-text" required>
						</label>
					</p>
					<input type="submit" name="mep_action_reset_hard" class="button button-link-delete" value="<?php _e( 'Hard Reset (Delete All)', 'manufacturing-erp-pro' ); ?>">
					<input type="submit" name="mep_action_reset_soft" class="button button-secondary" value="<?php _e( 'Soft Reset (Keep Master Data)', 'manufacturing-erp-pro' ); ?>">
				</form>
			</div>
		</div>
		<?php
	}

	public function handle_utilities() {
		if ( ! isset( $_POST['mep_nonce'] ) ) {
			return;
		}

		if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_toggle_help' ) && isset( $_POST['mep_action_toggle_help'] ) ) {
			$current = get_option( 'mep_help_mode', 'off' );
			update_option( 'mep_help_mode', $current === 'on' ? 'off' : 'on' );
			return;
		}

		if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_seed_data' ) && isset( $_POST['mep_action_seed'] ) ) {
			MEP_Seeder::seed();
			add_action( 'admin_notices', function() {
				echo '<div class="updated"><p>' . __( 'Sample data seeded successfully!', 'manufacturing-erp-pro' ) . '</p></div>';
			} );
		}

		if ( isset( $_POST['mep_action_reset_hard'] ) || isset( $_POST['mep_action_reset_soft'] ) ) {
			if ( $_POST['mep_confirm_phrase'] !== 'RESET PRODUCTION ENVIRONMENT' ) {
				add_action( 'admin_notices', function() {
					echo '<div class="error"><p>' . __( 'Invalid confirmation phrase.', 'manufacturing-erp-pro' ) . '</p></div>';
				} );
				return;
			}

			$hard = isset( $_POST['mep_action_reset_hard'] );
			MEP_Seeder::reset( $hard );

			add_action( 'admin_notices', function() use ($hard) {
				$msg = $hard ? __( 'System hard reset complete.', 'manufacturing-erp-pro' ) : __( 'System soft reset complete.', 'manufacturing-erp-pro' );
				echo '<div class="updated"><p>' . $msg . '</p></div>';
			} );
		}
	}

	public function enqueue_assets( $hook ) {
		// Only enqueue on ERP pages
		if ( strpos( $hook, 'mep-' ) === false && strpos( $hook, 'edit.php?post_type=mep_' ) === false ) {
			return;
		}

		wp_enqueue_style( 'mep-admin-style', MEP_PLUGIN_URL . 'assets/css/mep-admin.css', array(), MEP_VERSION );

		// Scaffolding for React components
		wp_enqueue_script( 'mep-bom-builder', MEP_PLUGIN_URL . 'assets/js/bom-builder.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );
		wp_enqueue_script( 'mep-kanban-board', MEP_PLUGIN_URL . 'assets/js/kanban-board.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );
		wp_enqueue_script( 'mep-warehouse-layout', MEP_PLUGIN_URL . 'assets/js/warehouse-layout.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );
		wp_enqueue_script( 'mep-dashboard', MEP_PLUGIN_URL . 'assets/js/dashboard.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );
		wp_enqueue_script( 'mep-wizard', MEP_PLUGIN_URL . 'assets/js/wizard.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );
		wp_enqueue_script( 'mep-traceability', MEP_PLUGIN_URL . 'assets/js/traceability.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );

		wp_localize_script( 'mep-bom-builder', 'mepSettings', array(
			'helpMode' => get_option( 'mep_help_mode', 'off' )
		) );
	}
}
