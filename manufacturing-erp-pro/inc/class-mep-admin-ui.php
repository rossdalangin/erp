<?php
/**
 * MEP Admin UI Enhancements
 * Adds custom columns and row actions to improve navigability and "functional" feel.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Admin_UI {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// Products Row Actions & Columns
		add_filter( 'post_row_actions', array( $this, 'add_product_row_actions' ), 10, 2 );
		add_filter( 'manage_mep_product_posts_columns', array( $this, 'add_product_columns' ) );
		add_action( 'manage_mep_product_posts_custom_column', array( $this, 'render_product_columns' ), 10, 2 );

		// Materials Row Actions & Columns
		add_filter( 'manage_mep_material_posts_columns', array( $this, 'add_material_columns' ) );
		add_action( 'manage_mep_material_posts_custom_column', array( $this, 'render_material_columns' ), 10, 2 );

		// Work Orders Row Actions & Columns
		add_filter( 'manage_mep_work_order_posts_columns', array( $this, 'add_work_order_columns' ) );
		add_action( 'manage_mep_work_order_posts_custom_column', array( $this, 'render_work_order_columns' ), 10, 2 );

		// Warehouses Row Actions
		add_filter( 'post_row_actions', array( $this, 'add_warehouse_row_actions' ), 10, 2 );

		// Global List Table Headers
		add_action( 'restrict_manage_posts', array( $this, 'add_list_table_instructions' ) );
	}

	/**
	 * Add instructional text to the top of standard WordPress list tables.
	 */
	public function add_list_table_instructions() {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->post_type, 'mep_' ) === false ) {
			return;
		}

		$instructions = array(
			'mep_material' => array(
				'title' => __( 'Materials Inventory', 'manufacturing-erp-pro' ),
				'desc'  => __( 'Define your raw materials and components here. Set average costs and safety stock levels to enable accurate MRP and costing.', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: "Cowhide Leather", SKU: MAT-LTH-001, UOM: m2, Avg Cost: $45.', 'manufacturing-erp-pro' )
			),
			'mep_product' => array(
				'title' => __( 'Finished Products', 'manufacturing-erp-pro' ),
				'desc'  => __( 'Manage your sellable products and assemblies. Use the "Visual BOM Builder" in the row actions to define how each product is manufactured.', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: "Leather Handbag", SKU: BAG-001. A product can contain other assemblies as components.', 'manufacturing-erp-pro' )
			),
			'mep_work_order' => array(
				'title' => __( 'Production Work Orders', 'manufacturing-erp-pro' ),
				'desc'  => __( 'Track active manufacturing jobs. Use the "Production Board" for a visual Kanban view of these orders.', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: WO-1001 for 50 units of "Leather Handbag".', 'manufacturing-erp-pro' )
			),
			'mep_po' => array(
				'title' => __( 'Purchase Orders', 'manufacturing-erp-pro' ),
				'desc'  => __( 'Manage procurement from suppliers. Use the "Receive Shipments" tool to bring these items into inventory visually.', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: PO-501 to "Leather Supplier A" for 200m2 of Cowhide.', 'manufacturing-erp-pro' )
			),
			'mep_supplier' => array(
				'title' => __( 'Supplier Management', 'manufacturing-erp-pro' ),
				'desc'  => __( 'Maintain your vendor list and contact details. Performance is tracked automatically in the Supplier Scorecard.', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: "Sole Supplier B", primary contact for rubber components.', 'manufacturing-erp-pro' )
			),
			'mep_bom' => array(
				'title' => __( 'Bill of Materials (BOM) Versions', 'manufacturing-erp-pro' ),
				'desc'  => __( 'View and manage different versions of your product engineering. Use the Visual BOM Builder to edit active structures.', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: "BOM for Handbag V2" - Updated to use 15% less leather.', 'manufacturing-erp-pro' )
			),
			'mep_qc_check' => array(
				'title' => __( 'Quality Inspection Queue', 'manufacturing-erp-pro' ),
				'desc'  => __( 'Record results for batch inspections. Failing a check automatically triggers a Non-Conformance Report (NCR).', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: QC Check for Lot #102. Result: PASS.', 'manufacturing-erp-pro' )
			),
			'mep_ncr' => array(
				'title' => __( 'NCR / CAPA Records', 'manufacturing-erp-pro' ),
				'desc'  => __( 'Investigate production failures and systemic issues. Promote systemic defects to CAPA (Corrective and Preventive Action) status.', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: NCR for damaged zippers. Action: Switch to YKK brand.', 'manufacturing-erp-pro' )
			),
			'mep_equipment' => array(
				'title' => __( 'Asset & Equipment Registry', 'manufacturing-erp-pro' ),
				'desc'  => __( 'Define factory machines and their daily capacities. The system uses this for load planning and cost roll-ups.', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: "Industrial Stitcher #1", Capacity: 480 mins/day.', 'manufacturing-erp-pro' )
			),
			'mep_forecast' => array(
				'title' => __( 'Demand Forecasting', 'manufacturing-erp-pro' ),
				'desc'  => __( 'Enter expected sales or stock needs. The MRP engine explodes these forecasts into material requirements.', 'manufacturing-erp-pro' ),
				'example' => __( 'Example: 500 units of "Leather Bag" for the Christmas season.', 'manufacturing-erp-pro' )
			),
		);

		if ( isset( $instructions[$screen->post_type] ) ) {
			$info = $instructions[$screen->post_type];
			echo '<div class="mep-list-table-help" style="background: #fff; border-left: 4px solid #2271b1; padding: 12px; margin-bottom: 10px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
					<strong style="display: block; margin-bottom: 4px;">' . esc_html( $info['title'] ) . '</strong>
					<p style="margin: 0; font-size: 13px;">' . esc_html( $info['desc'] ) . '</p>
					<p style="margin: 5px 0 0 0; font-size: 12px; color: #666;"><em>' . esc_html( $info['example'] ) . '</em></p>
				  </div>';
		}
	}

	/**
	 * Add "BOM Builder" and "Production Board" to Product row actions.
	 */
	public function add_product_row_actions( $actions, $post ) {
		if ( $post->post_type === 'mep_product' ) {
			$help_mode = get_option( 'mep_help_mode', 'off' );
			$help_suffix = $help_mode === 'on' ? ' ℹ️' : '';
			$bom_url = admin_url( 'admin.php?page=mep-bom-builder&product_id=' . $post->ID );
			$actions['mep_bom'] = '<a href="' . esc_url( $bom_url ) . '" style="color: #2271b1; font-weight: bold;" title="' . esc_attr__( 'Open visual BOM editor', 'manufacturing-erp-pro' ) . '">' . __( 'Visual BOM Builder', 'manufacturing-erp-pro' ) . $help_suffix . '</a>';
		}
		return $actions;
	}

	/**
	 * Add "Visual Layout" to Warehouse row actions.
	 */
	public function add_warehouse_row_actions( $actions, $post ) {
		if ( $post->post_type === 'mep_warehouse' ) {
			$wh_url = admin_url( 'admin.php?page=mep-inventory&warehouse_id=' . $post->ID );
			$actions['mep_layout'] = '<a href="' . esc_url( $wh_url ) . '">' . __( 'Visual Layout', 'manufacturing-erp-pro' ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Custom Columns for Products.
	 */
	public function add_product_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $val ) {
			if ( $key === 'date' ) {
				$new_columns['sku'] = __( 'SKU', 'manufacturing-erp-pro' );
				$new_columns['cost'] = __( 'Roll-up Cost', 'manufacturing-erp-pro' );
			}
			$new_columns[$key] = $val;
		}
		return $new_columns;
	}

	public function render_product_columns( $column, $post_id ) {
		if ( $column === 'sku' ) {
			echo esc_html( get_post_meta( $post_id, '_mep_sku', true ) ?: '---' );
		}
		if ( $column === 'cost' ) {
			$cost = MEP_BOM::calculate_roll_up_cost( $post_id );
			echo '<strong>$' . number_format( $cost, 2 ) . '</strong>';
		}
	}

	/**
	 * Custom Columns for Materials.
	 */
	public function add_material_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $val ) {
			if ( $key === 'date' ) {
				$new_columns['sku'] = __( 'SKU', 'manufacturing-erp-pro' );
				$new_columns['stock'] = __( 'On Hand', 'manufacturing-erp-pro' );
				$new_columns['safety'] = __( 'Safety Stock', 'manufacturing-erp-pro' );
			$new_columns['where_used'] = __( 'Where Used', 'manufacturing-erp-pro' );
			}
			$new_columns[$key] = $val;
		}
		return $new_columns;
	}

	public function render_material_columns( $column, $post_id ) {
		if ( $column === 'sku' ) {
			echo esc_html( get_post_meta( $post_id, '_mep_sku', true ) ?: '---' );
		}
		if ( $column === 'stock' ) {
			$stock = MEP_Inventory::get_stock_level( $post_id );
			$safety = (float) get_post_meta( $post_id, '_mep_safety_stock', true );
			$color = ( $safety > 0 && $stock < $safety ) ? '#d63638' : '#46b450';
			echo '<span style="color: ' . $color . '; font-weight: bold;">' . number_format( $stock, 2 ) . '</span>';
		}
		if ( $column === 'safety' ) {
			echo number_format( (float) get_post_meta( $post_id, '_mep_safety_stock', true ), 2 );
		}
		if ( $column === 'where_used' ) {
			$used_in = $this->get_where_material_is_used( $post_id );
			if ( empty( $used_in ) ) {
				echo '<small style="color: #999;">' . __( 'Not in any BOM', 'manufacturing-erp-pro' ) . '</small>';
			} else {
				$links = array();
				foreach ( $used_in as $product_id ) {
					$links[] = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $product_id ), get_the_title( $product_id ) );
				}
				echo implode( ', ', $links );
			}
		}
	}

	public function get_where_material_is_used( $material_id ) {
		$used_in = array();
		$boms = get_posts( array( 'post_type' => 'mep_bom', 'numberposts' => -1, 'post_status' => 'any' ) );
		foreach ( $boms as $bom ) {
			$components = get_post_meta( $bom->ID, '_mep_components', true );
			if ( is_array( $components ) ) {
				foreach ( $components as $comp ) {
					if ( isset( $comp['id'] ) && $comp['id'] == $material_id && isset( $comp['type'] ) && $comp['type'] === 'material' ) {
						$used_in[] = $bom->post_parent;
						break;
					}
				}
			}
		}
		return array_unique( $used_in );
	}

	/**
	 * Custom Columns for Work Orders.
	 */
	public function add_work_order_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $val ) {
			if ( $key === 'title' ) {
				$new_columns['status'] = __( 'ERP Status', 'manufacturing-erp-pro' );
			}
			if ( $key === 'date' ) {
				$new_columns['qty'] = __( 'Qty', 'manufacturing-erp-pro' );
				$new_columns['due'] = __( 'Due Date', 'manufacturing-erp-pro' );
			}
			$new_columns[$key] = $val;
		}
		return $new_columns;
	}

	public function render_work_order_columns( $column, $post_id ) {
		if ( $column === 'status' ) {
			$status = get_post_status( $post_id );
			echo '<span class="mep-badge-' . esc_attr($status) . '" style="text-transform: uppercase; font-weight: bold;">' . esc_html( $status ) . '</span>';
		}
		if ( $column === 'qty' ) {
			echo number_format( (float) get_post_meta( $post_id, '_mep_work_order_qty', true ), 2 );
		}
		if ( $column === 'due' ) {
			$due = get_post_meta( $post_id, '_mep_due_date', true );
			if ( $due && strtotime( $due ) < time() ) {
				echo '<span style="color: #d63638; font-weight: bold;">' . esc_html( $due ) . ' ⚠️</span>';
			} else {
				echo esc_html( $due ?: '---' );
			}
		}
	}
}
