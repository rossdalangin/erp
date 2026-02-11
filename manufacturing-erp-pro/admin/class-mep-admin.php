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
		// Main Menu
		add_menu_page(
			__( 'ERP Dashboard', 'manufacturing-erp-pro' ),
			__( 'ERP Pro', 'manufacturing-erp-pro' ),
			'manage_options',
			'mep-dashboard',
			array( $this, 'dashboard_page' ),
			'dashicons-chart-pie',
			24
		);

		// Re-register Dashboard as first submenu to control label
		add_submenu_page(
			'mep-dashboard',
			__( 'Dashboard', 'manufacturing-erp-pro' ),
			__( 'Dashboard', 'manufacturing-erp-pro' ),
			'manage_options',
			'mep-dashboard',
			array( $this, 'dashboard_page' )
		);

		// --- INVENTORY MODULE ---
		add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'manage_options', '#', '' );
		add_submenu_page( 'mep-dashboard', __( 'Materials', 'manufacturing-erp-pro' ), __( 'Materials', 'manufacturing-erp-pro' ), 'manage_options', 'edit.php?post_type=mep_material' );
		add_submenu_page( 'mep-dashboard', __( 'Visual Warehouse', 'manufacturing-erp-pro' ), __( 'Visual Warehouse', 'manufacturing-erp-pro' ), 'manage_options', 'mep-inventory', array( $this, 'inventory_page' ) );

		// --- PRODUCTION MODULE ---
		add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'manage_options', '#', '' );
		add_submenu_page( 'mep-dashboard', __( 'Products & BOMs', 'manufacturing-erp-pro' ), __( 'Products', 'manufacturing-erp-pro' ), 'manage_options', 'edit.php?post_type=mep_product' );
		add_submenu_page( 'mep-dashboard', __( 'BOM Builder', 'manufacturing-erp-pro' ), __( 'BOM Builder', 'manufacturing-erp-pro' ), 'manage_options', 'mep-bom-builder', array( $this, 'bom_builder_page' ) );
		add_submenu_page( 'mep-dashboard', __( 'Production Board', 'manufacturing-erp-pro' ), __( 'Production Board', 'manufacturing-erp-pro' ), 'manage_options', 'mep-production', array( $this, 'production_page' ) );

		// --- QUALITY MODULE ---
		add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'manage_options', '#', '' );
		add_submenu_page( 'mep-dashboard', __( 'Quality Checks', 'manufacturing-erp-pro' ), __( 'Quality Checks', 'manufacturing-erp-pro' ), 'manage_options', 'edit.php?post_type=mep_qc_check' );
		add_submenu_page( 'mep-dashboard', __( 'Traceability', 'manufacturing-erp-pro' ), __( 'Traceability', 'manufacturing-erp-pro' ), 'manage_options', 'mep-traceability', array( $this, 'traceability_page' ) );

		// --- PROCUREMENT & MRP ---
		add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'manage_options', '#', '' );
		add_submenu_page( 'mep-dashboard', __( 'Suppliers', 'manufacturing-erp-pro' ), __( 'Suppliers', 'manufacturing-erp-pro' ), 'manage_options', 'edit.php?post_type=mep_supplier' );
		add_submenu_page( 'mep-dashboard', __( 'Supplier Scorecard', 'manufacturing-erp-pro' ), __( 'Supplier Scorecard', 'manufacturing-erp-pro' ), 'manage_options', 'mep-supplier-scorecard', array( $this, 'supplier_scorecard_page' ) );
		add_submenu_page( 'mep-dashboard', __( 'Purchase Orders', 'manufacturing-erp-pro' ), __( 'Purchase Orders', 'manufacturing-erp-pro' ), 'manage_options', 'edit.php?post_type=mep_po' );
		add_submenu_page( 'mep-dashboard', __( 'Receive Shipments', 'manufacturing-erp-pro' ), __( 'Receive Shipments', 'manufacturing-erp-pro' ), 'manage_options', 'mep-po-receiving', array( $this, 'po_receiving_page' ) );
		add_submenu_page( 'mep-dashboard', __( 'Forecasts', 'manufacturing-erp-pro' ), __( 'Forecasts', 'manufacturing-erp-pro' ), 'manage_options', 'edit.php?post_type=mep_forecast' );
		add_submenu_page( 'mep-dashboard', __( 'MRP Planning', 'manufacturing-erp-pro' ), __( 'MRP Planning', 'manufacturing-erp-pro' ), 'manage_options', 'mep-mrp-planning', array( $this, 'mrp_planning_page' ) );
		add_submenu_page( 'mep-dashboard', __( 'Pegging View', 'manufacturing-erp-pro' ), __( 'Pegging View', 'manufacturing-erp-pro' ), 'manage_options', 'mep-pegging', array( $this, 'pegging_page' ) );

		// --- SETTINGS ---
		add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'manage_options', '#', '' );
		add_submenu_page( 'mep-dashboard', __( 'System Utilities', 'manufacturing-erp-pro' ), __( 'System Utilities', 'manufacturing-erp-pro' ), 'manage_options', 'mep-utilities', array( $this, 'utilities_page' ) );
		add_submenu_page( 'mep-dashboard', __( 'Setup Wizard', 'manufacturing-erp-pro' ), __( 'Setup Wizard', 'manufacturing-erp-pro' ), 'manage_options', 'mep-wizard', array( $this, 'wizard_page' ) );
	}

	public function wizard_page() {
		echo '<div class="wrap"><h1>' . __( 'ERP Pro Setup Wizard', 'manufacturing-erp-pro' ) . '</h1>';
		echo '<div id="mep-wizard-root"></div></div>';
	}

	public function supplier_scorecard_page() {
		echo '<div class="wrap"><h1>' . __( 'Vendor Performance Analysis', 'manufacturing-erp-pro' ) . '</h1>';
		$this->maybe_show_demo_badge();
		echo '<div id="mep-supplier-scorecard-root"></div></div>';
	}

	public function po_receiving_page() {
		echo '<div class="wrap"><h1>' . __( 'Visual Goods Receipt Workspace', 'manufacturing-erp-pro' ) . '</h1>';
		$this->maybe_show_demo_badge();
		echo '<div id="mep-po-receiving-root"></div></div>';
	}

	public function traceability_page() {
		echo '<div class="wrap"><h1>' . __( 'Lot & Batch Traceability', 'manufacturing-erp-pro' ) . '</h1>';
		$this->maybe_show_demo_badge();
		echo '<div id="mep-traceability-root"></div></div>';
	}

	public function mrp_planning_page() {
		echo '<div class="wrap"><h1>' . __( 'MRP Planning & Procurement', 'manufacturing-erp-pro' ) . '</h1>';
		$this->maybe_show_demo_badge();
		echo '<div id="mep-mrp-suggestions-root"></div></div>';
	}

	public function pegging_page() {
		echo '<div class="wrap"><h1>' . __( 'Demand Pegging Visualization', 'manufacturing-erp-pro' ) . '</h1>';
		$this->maybe_show_demo_badge();
		echo '<div id="mep-pegging-root"></div></div>';
	}

	public function inventory_page() {
		echo '<div class="wrap"><h1>' . __( 'Warehouse & Inventory Management', 'manufacturing-erp-pro' ) . '</h1>';
		$this->maybe_show_demo_badge();
		echo '<div id="mep-warehouse-root"></div></div>';
	}

	public function production_page() {
		echo '<div class="wrap"><h1>' . __( 'Production Planning & Kanban', 'manufacturing-erp-pro' ) . '</h1>';
		$this->maybe_show_demo_badge();
		echo '<div id="mep-kanban-root"></div></div>';
	}

	public function bom_builder_page() {
		$product_id = isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0;
		echo '<div class="wrap"><h1>' . __( 'Visual BOM Builder', 'manufacturing-erp-pro' ) . '</h1>';
		$this->maybe_show_demo_badge();
		if ( ! $product_id ) {
			echo '<p>' . __( 'Please select a product from the Products list to edit its BOM.', 'manufacturing-erp-pro' ) . '</p>';
			return;
		}
		echo '<div id="mep-bom-builder-root" data-product-id="' . esc_attr( $product_id ) . '"></div></div>';
	}

	public function dashboard_page() {
		$help_mode = get_option( 'mep_help_mode', 'off' );
		echo '<div class="wrap"><h1>' . __( 'Manufacturing ERP Pro Dashboard', 'manufacturing-erp-pro' ) . '</h1>';
		$this->maybe_show_demo_badge();
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

	public function maybe_show_demo_badge() {
		$has_sample = get_posts( array( 'post_type' => 'mep_material', 'meta_key' => '_mep_is_sample_data', 'numberposts' => 1 ) );
		if ( ! empty( $has_sample ) ) {
			echo '<div class="mep-demo-badge" style="background: #dba617; color: #fff; padding: 5px 15px; border-radius: 20px; display: inline-block; font-weight: bold; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">🚀 ' . __( 'DEMO MODE ACTIVE: LeatherCraft Co.', 'manufacturing-erp-pro' ) . '</div>';
		}
	}

	public function utilities_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'System Utilities & Safeguards', 'manufacturing-erp-pro' ); ?></h1>
			<?php $this->maybe_show_demo_badge(); ?>

			<div class="card">
				<h2><?php _e( 'General ERP Settings', 'manufacturing-erp-pro' ); ?></h2>
				<form method="post">
					<?php wp_nonce_field( 'mep_save_settings', 'mep_nonce' ); ?>
					<p>
						<label><?php _e( 'Inventory Valuation Method:', 'manufacturing-erp-pro' ); ?><br>
							<select name="mep_valuation_method">
								<option value="fifo" <?php selected( get_option( 'mep_valuation_method', 'fifo' ), 'fifo' ); ?>>FIFO (First-In-First-Out)</option>
								<option value="lifo" <?php selected( get_option( 'mep_valuation_method', 'fifo' ), 'lifo' ); ?>>LIFO (Last-In-First-Out)</option>
							</select>
						</label>
					</p>
					<input type="submit" name="mep_action_save_settings" class="button button-primary" value="<?php _e( 'Save Settings', 'manufacturing-erp-pro' ); ?>">
				</form>
			</div>

			<div class="card">
				<h2><?php _e( 'Master Data Importer', 'manufacturing-erp-pro' ); ?></h2>
				<p><?php _e( 'Import Materials from a CSV file. Header: Name,SKU,UOM,Cost', 'manufacturing-erp-pro' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'mep_import_data', 'mep_nonce' ); ?>
					<textarea name="mep_import_csv" style="width:100%; height:100px;" placeholder="Name,SKU,UOM,Cost"></textarea><br><br>
					<input type="submit" name="mep_action_import" class="button button-secondary" value="<?php _e( 'Import Materials', 'manufacturing-erp-pro' ); ?>">
				</form>
			</div>

			<div class="card">
				<h2><?php _e( 'System Audit Logs (Recent 20)', 'manufacturing-erp-pro' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th>Date</th>
							<th>User</th>
							<th>Object</th>
							<th>Action</th>
							<th>Details</th>
						</tr>
					</thead>
					<tbody>
						<?php
						global $wpdb;
						$logs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mep_audit_logs ORDER BY created_at DESC LIMIT 20" );
						foreach ( $logs as $log ) :
							$user = get_userdata( $log->user_id );
							?>
							<tr>
								<td><?php echo esc_html( $log->created_at ); ?></td>
								<td><?php echo esc_html( $user ? $user->display_name : 'System' ); ?></td>
								<td><?php echo esc_html( strtoupper( $log->object_type ) . ' #' . $log->object_id ); ?></td>
								<td><?php echo esc_html( $log->action ); ?></td>
								<td><small><?php echo esc_html( substr( $log->new_value, 0, 50 ) ); ?>...</small></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

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
					<a href="<?php echo esc_url( rest_url('mep/v1/reports/diagnostic-export') ); ?>?_wpnonce=<?php echo wp_create_nonce('wp_rest'); ?>" class="button"><?php _e( 'Export ERP Database (Backup)', 'manufacturing-erp-pro' ); ?></a>
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

		if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_save_settings' ) && isset( $_POST['mep_action_save_settings'] ) ) {
			update_option( 'mep_valuation_method', sanitize_text_field( $_POST['mep_valuation_method'] ) );
			add_action( 'admin_notices', function() {
				echo '<div class="updated"><p>' . __( 'Settings saved.', 'manufacturing-erp-pro' ) . '</p></div>';
			} );
		}

		if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_seed_data' ) && isset( $_POST['mep_action_seed'] ) ) {
			MEP_Seeder::seed();
			add_action( 'admin_notices', function() {
				echo '<div class="updated"><p>' . __( 'Sample data seeded successfully!', 'manufacturing-erp-pro' ) . '</p></div>';
			} );
		}

		if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_import_data' ) && isset( $_POST['mep_action_import'] ) ) {
			$csv = sanitize_textarea_field( $_POST['mep_import_csv'] );
			$count = MEP_Importer::import_materials( $csv );
			add_action( 'admin_notices', function() use ( $count ) {
				echo '<div class="updated"><p>' . sprintf( __( '%d materials imported successfully!', 'manufacturing-erp-pro' ), $count ) . '</p></div>';
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
		wp_enqueue_script( 'mep-mrp-suggestions', MEP_PLUGIN_URL . 'assets/js/mrp-suggestions.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );
		wp_enqueue_script( 'mep-pegging-view', MEP_PLUGIN_URL . 'assets/js/pegging-view.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );
		wp_enqueue_script( 'mep-supplier-scorecard', MEP_PLUGIN_URL . 'assets/js/supplier-scorecard.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );
		wp_enqueue_script( 'mep-po-receiving', MEP_PLUGIN_URL . 'assets/js/po-receiving.js', array( 'wp-element', 'wp-api-fetch' ), MEP_VERSION, true );

		wp_localize_script( 'mep-bom-builder', 'mepSettings', array(
			'helpMode' => get_option( 'mep_help_mode', 'off' )
		) );
	}
}
