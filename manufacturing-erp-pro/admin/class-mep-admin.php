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
	add_action( 'admin_init', array( $this, 'maybe_redirect_to_wizard' ) );
	add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function maybe_redirect_to_wizard() {
	$setup_complete = get_option( 'mep_setup_complete' );
	if ( $setup_complete ) {
		return;
	}

	$current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';

	// Allow access to wizard and dashboard at all times
	if ( $current_page === 'mep-wizard' || $current_page === 'mep-dashboard' ) {
		return;
	}

	// Only redirect if we are on an MEP-specific page
	if ( strpos( $current_page, 'mep-' ) === 0 || ( isset( $_GET['post_type'] ) && strpos( $_GET['post_type'], 'mep_' ) === 0 ) ) {
		wp_redirect( admin_url( 'admin.php?page=mep-wizard' ) );
		exit;
	}
	}

	public function add_menus() {
	// Main Menu
	add_menu_page(
		__( 'ERP Dashboard', 'manufacturing-erp-pro' ),
		__( 'ERP Pro', 'manufacturing-erp-pro' ),
		'read', // Allow all roles to see top level
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
		'read',
		'mep-dashboard',
		array( $this, 'dashboard_page' )
	);

	// --- INVENTORY MODULE ---
	add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'read', 'mep-sep-1', '' );
	add_submenu_page( 'mep-dashboard', __( 'Master Materials', 'manufacturing-erp-pro' ), __( 'Master Materials', 'manufacturing-erp-pro' ), 'mep_manage_inventory', 'edit.php?post_type=mep_material' );
	add_submenu_page( 'mep-dashboard', __( 'Visual Warehouse', 'manufacturing-erp-pro' ), __( 'Visual Warehouse', 'manufacturing-erp-pro' ), 'mep_manage_inventory', 'mep-inventory', array( $this, 'inventory_page' ) );

	// --- ENGINEERING MODULE ---
	add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'read', 'mep-sep-2', '' );
	add_submenu_page( 'mep-dashboard', __( 'Finished Products', 'manufacturing-erp-pro' ), __( 'Finished Products', 'manufacturing-erp-pro' ), 'mep_manage_bom', 'edit.php?post_type=mep_product' );
	add_submenu_page( 'mep-dashboard', __( 'BOM Canvas Builder', 'manufacturing-erp-pro' ), __( 'BOM Canvas Builder', 'manufacturing-erp-pro' ), 'mep_manage_bom', 'mep-bom-builder', array( $this, 'bom_builder_page' ) );

	// --- PRODUCTION MODULE ---
	add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'read', 'mep-sep-3', '' );
	add_submenu_page( 'mep-dashboard', __( 'Production Board', 'manufacturing-erp-pro' ), __( 'Production Board', 'manufacturing-erp-pro' ), 'mep_manage_production', 'mep-production', array( $this, 'production_page' ) );
	add_submenu_page( 'mep-dashboard', __( 'Resource Capacity', 'manufacturing-erp-pro' ), __( 'Resource Capacity', 'manufacturing-erp-pro' ), 'mep_manage_production', 'mep-capacity', array( $this, 'capacity_page' ) );

	// --- QUALITY MODULE ---
	add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'read', 'mep-sep-4', '' );
	add_submenu_page( 'mep-dashboard', __( 'Quality Inspections', 'manufacturing-erp-pro' ), __( 'Quality Inspections', 'manufacturing-erp-pro' ), 'mep_manage_quality', 'edit.php?post_type=mep_qc_check' );
	add_submenu_page( 'mep-dashboard', __( 'Compliance Dashboard', 'manufacturing-erp-pro' ), __( 'Compliance Dashboard', 'manufacturing-erp-pro' ), 'mep_manage_quality', 'mep-quality-dashboard', array( $this, 'quality_dashboard_page' ) );
	add_submenu_page( 'mep-dashboard', __( 'Batch Traceability', 'manufacturing-erp-pro' ), __( 'Batch Traceability', 'manufacturing-erp-pro' ), 'mep_manage_quality', 'mep-traceability', array( $this, 'traceability_page' ) );

	// --- SUPPLY CHAIN & MRP ---
	add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'read', 'mep-sep-5', '' );
	add_submenu_page( 'mep-dashboard', __( 'Supplier Center', 'manufacturing-erp-pro' ), __( 'Supplier Center', 'manufacturing-erp-pro' ), 'mep_manage_production', 'edit.php?post_type=mep_supplier' );
	add_submenu_page( 'mep-dashboard', __( 'Purchase Orders', 'manufacturing-erp-pro' ), __( 'Purchase Orders', 'manufacturing-erp-pro' ), 'mep_manage_production', 'edit.php?post_type=mep_po' );
	add_submenu_page( 'mep-dashboard', __( 'Receipt Workspace', 'manufacturing-erp-pro' ), __( 'Receipt Workspace', 'manufacturing-erp-pro' ), 'mep_manage_inventory', 'mep-po-receiving', array( $this, 'po_receiving_page' ) );
	add_submenu_page( 'mep-dashboard', __( 'MRP Planning Engine', 'manufacturing-erp-pro' ), __( 'MRP Planning Engine', 'manufacturing-erp-pro' ), 'mep_manage_production', 'mep-mrp-planning', array( $this, 'mrp_planning_page' ) );
	add_submenu_page( 'mep-dashboard', __( 'Demand Pegging', 'manufacturing-erp-pro' ), __( 'Demand Pegging', 'manufacturing-erp-pro' ), 'mep_manage_production', 'mep-pegging', array( $this, 'pegging_page' ) );
	add_submenu_page( 'mep-dashboard', __( 'Supplier Scorecard', 'manufacturing-erp-pro' ), __( 'Supplier Scorecard', 'manufacturing-erp-pro' ), 'mep_manage_production', 'mep-supplier-scorecard', array( $this, 'supplier_scorecard_page' ) );

	// --- SETTINGS ---
	add_submenu_page( 'mep-dashboard', '', '<span style="display:block; margin: 10px 0 0 0; border-top:1px solid #ccc;"></span>', 'manage_options', 'mep-sep-6', '' );
	add_submenu_page( 'mep-dashboard', __( 'System Utilities', 'manufacturing-erp-pro' ), __( 'System Utilities', 'manufacturing-erp-pro' ), 'manage_options', 'mep-utilities', array( $this, 'utilities_page' ) );
	add_submenu_page( 'mep-dashboard', __( 'Setup Wizard', 'manufacturing-erp-pro' ), __( 'Setup Wizard', 'manufacturing-erp-pro' ), 'manage_options', 'mep-wizard', array( $this, 'wizard_page' ) );
	}

	public function wizard_page() {
	echo '<div class="wrap"><h1>' . __( 'ERP Pro Setup Wizard', 'manufacturing-erp-pro' ) . '</h1>';
	echo '<div class="mep-wizard-intro" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px; border-radius: 4px;">
			<h3>' . __( 'Welcome to Manufacturing ERP Pro!', 'manufacturing-erp-pro' ) . '</h3>
			<p>' . __( 'This wizard will help you configure your basic factory settings so you can start producing in minutes. We recommend starting with the Sample Data if this is your first time.', 'manufacturing-erp-pro' ) . '</p>
		  </div>';
	echo '<div id="mep-wizard-root"></div></div>';
	}

	public function supplier_scorecard_page() {
	echo '<div class="wrap"><h1>' . __( 'Vendor Performance Analysis', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();
	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'Supplier Scoring Logic:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Evaluate your vendors based on real-world data. The system tracks Quality (percentage of received items that pass QC) and OTD (On-Time Delivery based on PO expected dates).', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: A supplier with a 100% Quality score but 40% OTD may be a bottleneck for your production schedule.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';
	echo '<div id="mep-supplier-scorecard-root"></div></div>';
	}

	public function po_receiving_page() {
	echo '<div class="wrap"><h1>' . __( 'Visual Goods Receipt Workspace', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();
	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'Receiving Instructions:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Close the loop on your procurement. Drag items from open Purchase Orders into their target Warehouse bins to record the receipt and update inventory levels.', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: Drag "500m Leather" from PO #401 into "Bin A1". The system will automatically log the transaction and update your stock on hand.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';
	echo '<div id="mep-po-receiving-root"></div></div>';
	}

	public function quality_dashboard_page() {
	echo '<div class="wrap"><h1>' . __( 'Quality & Compliance Analytics', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();
	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'Quality Control Overview:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Monitor your inspection pass rates and defect distribution. If an inspection fails, the system automatically generates an NCR (Non-Conformance Report). Critical systemic issues should be promoted to CAPA status for resolution.', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: A high fail rate for "Stitching" may indicate a machine calibration issue or training requirement.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';
	echo '<div id="mep-quality-dashboard-root"></div></div>';
	}

	public function traceability_page() {
	echo '<div class="wrap"><h1>' . __( 'Lot & Batch Traceability', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();
	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'Traceability Instructions:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Enter a Lot or Batch ID to visualize the complete genealogy of a product. You can trace back to the specific supplier shipment for raw materials used in any finished good.', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: Enter "LOT-COW-001" to see which Work Orders consumed this specific batch of leather.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';
	echo '<div id="mep-traceability-root"></div></div>';
	}

	public function capacity_page() {
	echo '<div class="wrap"><h1>' . __( 'Resource Capacity & Maintenance', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();
	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'Capacity Planning Logic:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Avoid bottlenecks by monitoring machine load vs. daily capacity. This view aggregates the total time required for all pending Work Orders assigned to each piece of equipment.', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: If your "Laser Cutter" has a load of 600 mins but only 480 mins of daily capacity, you are over-scheduled for that resource.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';
	echo '<div id="mep-capacity-root"></div></div>';
	}

	public function mrp_planning_page() {
	echo '<div class="wrap"><h1>' . __( 'MRP Planning & Procurement', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();
	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'MRP Engine Logic:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'The Material Requirements Planning engine explodes your demand forecasts and compares them with current inventory and open orders. It generates "Purchase Suggestions" to ensure you have the right materials at the right time.', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: If you forecast 100 bags and have 20 in stock, MRP will suggest buying leather for the remaining 80, factoring in your safety stock levels.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';
	echo '<div id="mep-mrp-suggestions-root"></div></div>';
	}

	public function pegging_page() {
	echo '<div class="wrap"><h1>' . __( 'Demand Pegging Visualization', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();
	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'What is Pegging?', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Pegging shows you the link between raw material requirements and the original demand source (e.g., a specific forecast or customer order). It explains "Why" the system is asking you to buy a material.', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: View the pegging tree for "Zippers" to see which upcoming Work Orders are driving the requirement.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';
	echo '<div id="mep-pegging-root"></div></div>';
	}

	public function inventory_page() {
	echo '<div class="wrap"><h1>' . __( 'Warehouse & Inventory Management', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();
	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'How to manage your Warehouse:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Use this visual layout to track stock levels by bin. To move stock, simply drag a material card from its current bin and drop it into a new one. Bins highlighted in Yellow or Red indicate high occupancy.', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: Drag "Cowhide Leather" from "Main Warehouse" to "Cutting Area" when starting a new batch.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';
	echo '<div id="mep-warehouse-root"></div></div>';
	}

	public function production_page() {
	echo '<div class="wrap"><h1>' . __( 'Production Planning & Kanban', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();
	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'Production Workflow:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Manage the lifecycle of your Work Orders. Drag cards to "In Progress" to assign an operator and start production. Move them to "Completed" to record actual scrap and labor time.', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: Release a Work Order for 50 Handbags. Once stitching begins, drag it to In Progress and select the assigned Machine.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';
	echo '<div id="mep-kanban-root"></div></div>';
	}

	public function bom_builder_page() {
	$product_id = isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0;
	echo '<div class="wrap"><h1>' . __( 'Visual BOM Builder', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();

	if ( ! $product_id ) {
		echo '<div class="notice notice-warning"><p>' . __( 'Please select a product from the Products list (ERP Pro > Products) to edit its Bill of Materials.', 'manufacturing-erp-pro' ) . '</p></div>';
		return;
	}

	echo '<div class="mep-page-header" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
			<p><strong>' . __( 'Engineering Instructions:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Drag materials or labor operations from the left library into the central canvas. You can nest assemblies (e.g., adding a "Strap Assembly" product into a "Handbag" BOM) to create multi-level structures. The cost roll-up updates in real-time.', 'manufacturing-erp-pro' ) . '</p>
			<p style="font-size: 12px; color: #666;"><em>' . __( 'Example: To build a Leather Bag, drag 1.2m2 of Leather, 1 Zip, and a "Stitching" operation. Set the scrap factor for leather to 5% to account for cutting waste.', 'manufacturing-erp-pro' ) . '</em></p>
		  </div>';

	echo '<div id="mep-bom-builder-root" data-product-id="' . esc_attr( $product_id ) . '"></div></div>';
	}

	public function dashboard_page() {
	$help_mode = get_option( 'mep_help_mode', 'off' );
	echo '<div class="wrap"><h1>' . __( 'Manufacturing ERP Pro Dashboard', 'manufacturing-erp-pro' ) . '</h1>';
	$this->maybe_show_demo_badge();

	echo '<div class="mep-help-toggle-container mep-card" style="display: flex; align-items: center; gap: 15px;">
			<div style="flex: 1;">
				<strong>' . __( 'Interactive Help Mode:', 'manufacturing-erp-pro' ) . '</strong>
				<form method="post" style="display:inline; margin-left: 10px;">
					<input type="hidden" name="mep_action_toggle_help" value="1">
					' . wp_nonce_field( 'mep_toggle_help', 'mep_nonce', true, false ) . '
					<button type="submit" class="button ' . ( $help_mode === 'on' ? 'button-primary' : '' ) . '">' . ( $help_mode === 'on' ? 'ON' : 'OFF' ) . '</button>
				</form>
				<p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">' . __( 'Toggle this ON to see detailed instructions and examples directly on ERP components.', 'manufacturing-erp-pro' ) . '</p>
			</div>
			<div style="background: #f0f0f1; padding: 10px; border-radius: 4px; font-size: 12px; max-width: 300px;">
				<strong>' . __( 'Quick Start Tip:', 'manufacturing-erp-pro' ) . '</strong> ' . __( 'Start by adding Materials, then create a Product and use the Visual BOM Builder to define how it is made.', 'manufacturing-erp-pro' ) . '
			</div>
		  </div>';

	echo '<div class="mep-dashboard-intro mep-card" style="border-left: 4px solid ' . esc_attr( get_theme_mod( 'mep_accent_color', '#2271b1' ) ) . ';">
			<h2>' . __( 'Welcome to your Factory Command Center', 'manufacturing-erp-pro' ) . '</h2>
			<p>' . __( 'This dashboard provides a real-time overview of your production health, inventory value, and quality metrics.', 'manufacturing-erp-pro' ) . '</p>

			<div class="mep-quick-actions" style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #eee;">
				<h3 class="mep-card-title">⚡ ' . __( 'Global Launch Pad', 'manufacturing-erp-pro' ) . '</h3>
				<div class="mep-step-links">
					<a href="' . admin_url( 'admin.php?page=mep-bom-builder' ) . '" class="mep-step-link">
						<strong>🛠️ ' . __( 'Engineering', 'manufacturing-erp-pro' ) . '</strong>
						<span>' . __( 'Configure Bills of Materials', 'manufacturing-erp-pro' ) . '</span>
					</a>
					<a href="' . admin_url( 'admin.php?page=mep-production' ) . '" class="mep-step-link">
						<strong>📋 ' . __( 'Production', 'manufacturing-erp-pro' ) . '</strong>
						<span>' . __( 'Manage Kanban Board', 'manufacturing-erp-pro' ) . '</span>
					</a>
					<a href="' . admin_url( 'admin.php?page=mep-inventory' ) . '" class="mep-step-link">
						<strong>📦 ' . __( 'Logistics', 'manufacturing-erp-pro' ) . '</strong>
						<span>' . __( 'Visual Warehouse & Bins', 'manufacturing-erp-pro' ) . '</span>
					</a>
					<a href="' . admin_url( 'admin.php?page=mep-mrp-planning' ) . '" class="mep-step-link" style="border-left-color: var(--mep-warning);">
						<strong>🧠 ' . __( 'MRP Engine', 'manufacturing-erp-pro' ) . '</strong>
						<span>' . __( 'Generate Purchase Suggestions', 'manufacturing-erp-pro' ) . '</span>
					</a>
				</div>

				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
					<div class="mep-card" style="border-left: 4px solid #ffa000; margin: 0;">
						<h4 class="mep-card-title">' . __( '🚀 Initialize Demo Data', 'manufacturing-erp-pro' ) . '</h4>
						<p>' . __( 'Populate the system with LeatherCraft Co. sample materials, BOMs, and Work Orders.', 'manufacturing-erp-pro' ) . '</p>
						<form method="post" onsubmit="return confirm(\'' . esc_js(__('Are you sure you want to seed sample data?', 'manufacturing-erp-pro')) . '\');">
							' . wp_nonce_field( 'mep_seed_data', 'mep_nonce', true, false ) . '
							<button type="submit" name="mep_action_seed" class="button button-primary" style="background: #ffa000; border-color: #ff8f00;">' . __( 'Load Sample Factory', 'manufacturing-erp-pro' ) . '</button>
						</form>
					</div>

					<div class="mep-card" style="border-left: 4px solid #d32f2f; margin: 0;">
						<h4 class="mep-card-title">' . __( '⚠️ System Reset', 'manufacturing-erp-pro' ) . '</h4>
						<p>' . __( 'Permanently delete all manufacturing records and start from scratch.', 'manufacturing-erp-pro' ) . '</p>
						<form method="post" onsubmit="return prompt(\'' . esc_js(__('Type RESET to confirm database wipe:', 'manufacturing-erp-pro')) . '\') === \'RESET\';">
							' . wp_nonce_field( 'mep_reset_db_quick', 'mep_nonce', true, false ) . '
							<button type="submit" name="mep_action_reset_hard_quick" class="button button-link-delete" style="color: #d32f2f;">' . __( 'Wipe Manufacturing Data', 'manufacturing-erp-pro' ) . '</button>
						</form>
					</div>
				</div>
			</div>

			<div class="mep-step-links" style="margin-top: 40px; background: #f9f9f9; padding: 20px; border-radius: 8px;">
				<div>
					<strong>' . __( 'Step 1: Inventory', 'manufacturing-erp-pro' ) . '</strong>
					<p style="font-size: 12px; margin-top: 5px;">' . __( 'Add raw materials and receive stock to establish your inventory baseline.', 'manufacturing-erp-pro' ) . '</p>
				</div>
				<div>
					<strong>' . __( 'Step 2: Engineering', 'manufacturing-erp-pro' ) . '</strong>
					<p style="font-size: 12px; margin-top: 5px;">' . __( 'Build multi-level BOMs and define production routes for your finished goods.', 'manufacturing-erp-pro' ) . '</p>
				</div>
				<div>
					<strong>' . __( 'Step 3: Execution', 'manufacturing-erp-pro' ) . '</strong>
					<p style="font-size: 12px; margin-top: 5px;">' . __( 'Run MRP to generate suggestions, release Work Orders, and track them on the Kanban board.', 'manufacturing-erp-pro' ) . '</p>
				</div>
			</div>
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
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
	?>
	<div class="wrap">
		<h1><?php _e( 'System Utilities & Safeguards', 'manufacturing-erp-pro' ); ?></h1>
		<?php $this->maybe_show_demo_badge(); ?>

		<h2 class="nav-tab-wrapper">
			<a href="?page=mep-utilities&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Global Settings', 'manufacturing-erp-pro' ); ?></a>
			<a href="?page=mep-utilities&tab=onboarding" class="nav-tab <?php echo $active_tab == 'onboarding' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Onboarding & Reset', 'manufacturing-erp-pro' ); ?></a>
			<a href="?page=mep-utilities&tab=import" class="nav-tab <?php echo $active_tab == 'import' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Data Import', 'manufacturing-erp-pro' ); ?></a>
			<a href="?page=mep-utilities&tab=audit" class="nav-tab <?php echo $active_tab == 'audit' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Audit Logs', 'manufacturing-erp-pro' ); ?></a>
		</h2>

		<div class="mep-tab-container" class="mep-tab-container">
			<?php if ( $active_tab == 'general' ) : ?>
				<div class="mep-card">
					<h2><?php _e( 'General ERP Settings', 'manufacturing-erp-pro' ); ?></h2>
					<p><?php _e( 'Configure your factory defaults and system behavior.', 'manufacturing-erp-pro' ); ?></p>
					<form method="post">
						<?php wp_nonce_field( 'mep_save_settings', 'mep_nonce' ); ?>
						<table class="form-table">
							<tr>
								<th scope="row"><?php _e( 'Inventory Valuation', 'manufacturing-erp-pro' ); ?></th>
								<td>
									<select name="mep_valuation_method" class="regular-text">
										<option value="fifo" <?php selected( get_option( 'mep_valuation_method', 'fifo' ), 'fifo' ); ?>>FIFO (First-In-First-Out)</option>
										<option value="lifo" <?php selected( get_option( 'mep_valuation_method', 'fifo' ), 'lifo' ); ?>>LIFO (Last-In-First-Out)</option>
									</select>
									<p class="description"><?php _e( 'FIFO assumes oldest stock is consumed first.', 'manufacturing-erp-pro' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'MRP Interval', 'manufacturing-erp-pro' ); ?></th>
								<td>
									<select name="mep_mrp_interval" class="regular-text">
										<option value="hourly" <?php selected( get_option( 'mep_mrp_interval', 'daily' ), 'hourly' ); ?>><?php _e( 'Hourly', 'manufacturing-erp-pro' ); ?></option>
										<option value="daily" <?php selected( get_option( 'mep_mrp_interval', 'daily' ), 'daily' ); ?>><?php _e( 'Daily', 'manufacturing-erp-pro' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Base Currency', 'manufacturing-erp-pro' ); ?></th>
								<td>
									<input type="text" name="mep_currency" value="<?php echo esc_attr( get_option( 'mep_currency', 'USD' ) ); ?>" class="regular-text">
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Weight Unit', 'manufacturing-erp-pro' ); ?></th>
								<td>
									<input type="text" name="mep_weight_unit" value="<?php echo esc_attr( get_option( 'mep_weight_unit', 'kg' ) ); ?>" class="regular-text">
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Std Labor Rate ($/min)', 'manufacturing-erp-pro' ); ?></th>
								<td>
									<input type="number" step="0.01" name="mep_default_labor_rate" value="<?php echo esc_attr( get_option( 'mep_default_labor_rate', '0.50' ) ); ?>" class="regular-text">
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Interactive Help Mode', 'manufacturing-erp-pro' ); ?></th>
								<td>
									<select name="mep_help_mode" class="regular-text">
										<option value="on" <?php selected( get_option( 'mep_help_mode', 'off' ), 'on' ); ?>><?php _e( 'Enabled (Show tooltips)', 'manufacturing-erp-pro' ); ?></option>
										<option value="off" <?php selected( get_option( 'mep_help_mode', 'off' ), 'off' ); ?>><?php _e( 'Disabled', 'manufacturing-erp-pro' ); ?></option>
									</select>
								</td>
							</tr>
						</table>
						<p class="submit">
							<input type="submit" name="mep_action_save_settings" class="button button-primary" value="<?php _e( 'Save Global Settings', 'manufacturing-erp-pro' ); ?>">
						</p>
					</form>
				</div>

			<?php elseif ( $active_tab == 'onboarding' ) : ?>
				<div class="mep-card" style="border-left: 4px solid #dba617;">
					<h2><?php _e( 'Sample Data Seeder', 'manufacturing-erp-pro' ); ?></h2>
					<p><?php _e( 'Populate your system with realistic manufacturing data from LeatherCraft Co. to see how modules connect.', 'manufacturing-erp-pro' ); ?></p>
					<form method="post" onsubmit="return confirm('<?php _e( 'This will add sample records to your database. Continue?', 'manufacturing-erp-pro' ); ?>');">
						<?php wp_nonce_field( 'mep_seed_data', 'mep_nonce' ); ?>
						<button type="submit" name="mep_action_seed" class="button button-secondary">🚀 <?php _e( 'Add Sample Data', 'manufacturing-erp-pro' ); ?></button>
					</form>
				</div>

				<div class="mep-card" style="border-left: 4px solid #d63638; margin-top: 30px;">
					<h2 style="color: #d63638;"><?php _e( 'System Reset (Danger Zone)', 'manufacturing-erp-pro' ); ?></h2>
					<p><?php _e( 'Wipe all manufacturing records to start fresh. This action cannot be undone.', 'manufacturing-erp-pro' ); ?></p>

					<form method="post">
						<?php wp_nonce_field( 'mep_reset_db', 'mep_nonce' ); ?>
						<p>
							<label>
								<input type="checkbox" name="mep_confirm_reset" required>
								<strong><?php _e( 'I confirm that I want to delete ALL manufacturing data.', 'manufacturing-erp-pro' ); ?></strong>
							</label>
						</p>
						<p>
							<label><?php _e( 'Type "RESET" to confirm:', 'manufacturing-erp-pro' ); ?><br>
								<input type="text" name="mep_confirm_phrase_simple" class="regular-text" required placeholder="RESET">
							</label>
						</p>
						<div style="display: flex; gap: 10px;">
							<input type="submit" name="mep_action_reset_hard" class="button button-link-delete" value="<?php _e( 'Hard Reset (Delete All)', 'manufacturing-erp-pro' ); ?>">
							<input type="submit" name="mep_action_reset_soft" class="button button-secondary" value="<?php _e( 'Soft Reset (Keep Master Data)', 'manufacturing-erp-pro' ); ?>">
							<input type="submit" name="mep_action_recreate_tables" class="button" value="<?php _e( 'Recreate Tables Only', 'manufacturing-erp-pro' ); ?>">
						</div>
					</form>
				</div>

			<?php elseif ( $active_tab == 'import' ) : ?>
				<div class="mep-card">
					<h2><?php _e( 'CSV Master Data Importer', 'manufacturing-erp-pro' ); ?></h2>
					<p><?php _e( 'Quickly upload your materials. Format: Name, SKU, UOM, Unit Cost', 'manufacturing-erp-pro' ); ?></p>
					<form method="post">
						<?php wp_nonce_field( 'mep_import_data', 'mep_nonce' ); ?>
						<textarea name="mep_import_csv" style="width:100%; height:150px; font-family: monospace;" placeholder="Cowhide Leather,MAT-001,m2,45.00"></textarea>
						<p class="submit">
							<input type="submit" name="mep_action_import" class="button button-primary" value="<?php _e( 'Import Materials', 'manufacturing-erp-pro' ); ?>">
						</p>
					</form>
				</div>

			<?php elseif ( $active_tab == 'audit' ) : ?>
				<div class="card">
					<h2><?php _e( 'System Audit Logs (Recent 20)', 'manufacturing-erp-pro' ); ?></h2>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th style="width: 150px;"><?php _e( 'Date', 'manufacturing-erp-pro' ); ?></th>
								<th style="width: 120px;"><?php _e( 'User', 'manufacturing-erp-pro' ); ?></th>
								<th style="width: 150px;"><?php _e( 'Object', 'manufacturing-erp-pro' ); ?></th>
								<th style="width: 150px;"><?php _e( 'Action', 'manufacturing-erp-pro' ); ?></th>
								<th><?php _e( 'Details', 'manufacturing-erp-pro' ); ?></th>
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
									<td><code><?php echo esc_html( $log->action ); ?></code></td>
									<td><small><?php echo esc_html( substr( $log->new_value, 0, 100 ) ); ?>...</small></td>
								</tr>
							<?php endforeach; ?>
							<?php if ( empty( $logs ) ) : ?>
								<tr><td colspan="5"><?php _e( 'No audit logs found.', 'manufacturing-erp-pro' ); ?></td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

			<?php endif; ?>
		</div>
	</div>
	<?php
	}

	public function handle_utilities() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

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
		update_option( 'mep_mrp_interval', sanitize_text_field( $_POST['mep_mrp_interval'] ) );
		update_option( 'mep_currency', sanitize_text_field( $_POST['mep_currency'] ) );
		update_option( 'mep_weight_unit', sanitize_text_field( $_POST['mep_weight_unit'] ) );
		update_option( 'mep_default_labor_rate', sanitize_text_field( $_POST['mep_default_labor_rate'] ) );
		update_option( 'mep_help_mode', sanitize_text_field( $_POST['mep_help_mode'] ) );

		add_action( 'admin_notices', function() {
			echo '<div class="updated"><p>' . __( 'Global Settings saved.', 'manufacturing-erp-pro' ) . '</p></div>';
		} );
	}

	if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_seed_data' ) && isset( $_POST['mep_action_seed'] ) ) {
		MEP_Seeder::seed();
		update_option( 'mep_setup_complete', '1' ); // Seeding also completes setup
		add_action( 'admin_notices', function() {
			echo '<div class="updated"><p>' . __( 'Sample data seeded successfully! Setup marked as complete.', 'manufacturing-erp-pro' ) . '</p></div>';
		} );
	}

	if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_skip_wizard' ) && isset( $_POST['mep_action_skip_wizard'] ) ) {
		update_option( 'mep_setup_complete', '1' );
		add_action( 'admin_notices', function() {
			echo '<div class="updated"><p>' . __( 'Setup skipped. All ERP functionalities are now enabled.', 'manufacturing-erp-pro' ) . '</p></div>';
		} );
	}

	if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_reset_db_quick' ) && isset( $_POST['mep_action_reset_hard_quick'] ) ) {
		MEP_Seeder::reset( true ); // Hard reset
		update_option( 'mep_setup_complete', '' ); // Reset setup status
		add_action( 'admin_notices', function() {
			echo '<div class="updated"><p>' . __( 'Database completely wiped and system reset.', 'manufacturing-erp-pro' ) . '</p></div>';
		} );
	}

	if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_import_data' ) && isset( $_POST['mep_action_import'] ) ) {
		$csv = sanitize_textarea_field( $_POST['mep_import_csv'] );
		$count = MEP_Importer::import_materials( $csv );
		add_action( 'admin_notices', function() use ( $count ) {
			echo '<div class="updated"><p>' . sprintf( __( '%d materials imported successfully!', 'manufacturing-erp-pro' ), $count ) . '</p></div>';
		} );
	}

	if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_reset_db' ) && isset( $_POST['mep_action_recreate_tables'] ) ) {
		MEP_DB::create_tables();
		add_action( 'admin_notices', function() {
			echo '<div class="updated"><p>' . __( 'Database tables recreated/updated.', 'manufacturing-erp-pro' ) . '</p></div>';
		} );
	}

	if ( wp_verify_nonce( $_POST['mep_nonce'], 'mep_reset_db' ) && ( isset( $_POST['mep_action_reset_hard'] ) || isset( $_POST['mep_action_reset_soft'] ) ) ) {
		if ( $_POST['mep_confirm_phrase_simple'] !== 'RESET' ) {
			add_action( 'admin_notices', function() {
				echo '<div class="error"><p>' . __( 'Confirmation phrase must be RESET.', 'manufacturing-erp-pro' ) . '</p></div>';
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
	global $typenow;

	// Only enqueue on ERP pages or CPT pages
	$is_mep_cpt = ( $typenow && strpos( $typenow, 'mep_' ) === 0 );
	$is_mep_page = ( strpos( $hook, 'mep-' ) !== false || strpos( $hook, 'erp-pro' ) !== false );

	if ( ! $is_mep_cpt && ! $is_mep_page ) {
		return;
	}

	wp_enqueue_style( 'mep-admin-style', MEP_PLUGIN_URL . 'assets/css/mep-admin.css', array(), MEP_VERSION );

	// Scaffolding for React components
	$deps = array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-api' );

	// Map page slugs to scripts
	$current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';

	$script_map = array(
		'mep-dashboard'         => 'mep-dashboard',
		'mep-inventory'         => 'mep-warehouse-layout',
		'mep-bom-builder'       => 'mep-bom-builder',
		'mep-production'        => 'mep-kanban-board',
		'mep-quality-dashboard' => 'mep-quality-dashboard',
		'mep-traceability'      => 'mep-traceability',
		'mep-po-receiving'      => 'mep-po-receiving',
		'mep-mrp-planning'      => 'mep-mrp-suggestions',
		'mep-pegging'           => 'mep-pegging-view',
		'mep-supplier-scorecard'=> 'mep-supplier-scorecard',
		'mep-capacity'          => 'mep-capacity-planner',
		'mep-wizard'            => 'mep-wizard',
	);

	$active_scripts = array();

	if ( isset( $script_map[ $current_page ] ) ) {
		$handle = $script_map[ $current_page ];
		$filename = str_replace( 'mep-', '', $handle );
		wp_enqueue_script( $handle, MEP_PLUGIN_URL . "assets/js/{$filename}.js", $deps, MEP_VERSION, true );
		$active_scripts[] = $handle;
	}

	// Always enqueue global search
	wp_enqueue_script( 'mep-erp-search', MEP_PLUGIN_URL . 'assets/js/erp-search.js', array( 'wp-element', 'wp-i18n' ), MEP_VERSION, true );
	$active_scripts[] = 'mep-erp-search';

	// Localize help mode and branding
	$settings = array(
		'root'        => esc_url_raw( rest_url() ),
		'nonce'       => wp_create_nonce( 'wp_rest' ),
		'helpMode'    => get_option( 'mep_help_mode', 'off' ),
		'accentColor' => get_theme_mod( 'mep_accent_color', '#2271b1' ),
		'companyName' => get_theme_mod( 'mep_company_legal_name', 'LeatherCraft Manufacturing Co.' ),
	);

	foreach ( $active_scripts as $handle ) {
		wp_localize_script( $handle, 'mepSettings', $settings );
		wp_localize_script( $handle, 'wpApiSettings', array(
			'root' => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' )
		) );
	}
	}
}
