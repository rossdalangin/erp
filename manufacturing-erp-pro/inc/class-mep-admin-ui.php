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
