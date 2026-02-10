<?php
/**
 * MEP REST API Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_API {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( 'mep/v1', '/materials', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_materials' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/work-orders', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_work_orders' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/work-orders/(?P<id>\d+)/status', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'update_work_order_status' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/bom/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_bom' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/bom/(?P<id>\d+)', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'update_bom' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/mrp/run', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'run_mrp' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/mrp/pegging', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_pegging_data' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/inventory/transfer', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'transfer_inventory' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/warehouses', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_warehouses' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/warehouses/(?P<id>\d+)/bins', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_warehouse_bins' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/reports/kpis', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_kpis' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/qc/trace/(?P<lot>[a-zA-Z0-9\-_]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_lot_trace' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/qc/trace/(?P<lot>[a-zA-Z0-9\-_]+)/report', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_lot_trace_report' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/equipment/capacity', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_equipment_capacity' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/reports/inventory-csv', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_inventory_csv' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/customers', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_customers' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/procurement/po-from-items', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'create_po_from_items' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );
	}

	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	public function get_materials( $request ) {
		$posts = get_posts( array( 'post_type' => 'mep_material', 'numberposts' => -1 ) );
		$data  = array();

		foreach ( $posts as $post ) {
			$data[] = array(
				'id'   => $post->ID,
				'name' => $post->post_title,
				'sku'  => get_post_meta( $post->ID, '_mep_sku', true ),
				'uom'  => get_post_meta( $post->ID, '_mep_uom', true ),
				'cost' => get_post_meta( $post->ID, '_mep_cost_avg', true ),
			);
		}

		return new WP_REST_Response( $data, 200 );
	}

	public function get_work_orders( $request ) {
		$posts = get_posts( array( 'post_type' => 'mep_work_order', 'numberposts' => -1, 'post_status' => 'any' ) );
		$data  = array();

		foreach ( $posts as $post ) {
			$data[] = array(
				'id'     => $post->ID,
				'title'  => $post->post_title,
				'status' => $post->post_status,
			);
		}

		return new WP_REST_Response( $data, 200 );
	}

	public function update_work_order_status( $request ) {
		$id = $request['id'];
		$status = $request['status'];
		$params = $request->get_params();

		$old_status = get_post_field( 'post_status', $id );
		wp_update_post( array(
			'ID'          => $id,
			'post_status' => $status
		) );

		if ( isset( $params['scrap_qty'] ) ) {
			update_post_meta( $id, '_mep_actual_scrap', (float) $params['scrap_qty'] );
		}
		if ( isset( $params['labor_mins'] ) ) {
			update_post_meta( $id, '_mep_actual_labor_mins', (float) $params['labor_mins'] );
		}

		// Also log to production logs table if completed
		if ( $status === 'completed' ) {
			global $wpdb;
			$wpdb->insert( $wpdb->prefix . 'mep_production_logs', array(
				'work_order_id' => $id,
				'output_qty'    => (float) get_post_meta( $id, '_mep_work_order_qty', true ),
				'scrap_qty'     => (float) ( $params['scrap_qty'] ?? 0 ),
				'labor_mins'    => (float) ( $params['labor_mins'] ?? 0 ),
				'created_at'    => current_time( 'mysql' )
			) );
		}

		MEP_DB::log_audit( 'work_order', $id, 'STATUS_CHANGE', $old_status, $status );

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function get_bom( $request ) {
		$product_id = $request['id'];
		$tree = MEP_BOM::get_bom_tree( $product_id );
		$cost = MEP_BOM::calculate_roll_up_cost( $product_id );

		return new WP_REST_Response( array(
			'product_id' => $product_id,
			'bom'        => $tree,
			'total_cost' => $cost
		), 200 );
	}

	public function run_mrp( $request ) {
		$suggestions = MEP_MRP::run();
		return new WP_REST_Response( $suggestions, 200 );
	}

	public function get_pegging_data( $request ) {
		$data = MEP_MRP::get_pegging_data();
		return new WP_REST_Response( $data, 200 );
	}

	public function transfer_inventory( $request ) {
		$params = $request->get_params();

		// Map source/target if not provided explicitly (fallback for visual UI)
		if ( ! isset( $params['source_warehouse_id'] ) ) {
			$params['source_warehouse_id'] = get_post_field( 'post_parent', $params['source_bin_id'] );
		}
		if ( ! isset( $params['target_warehouse_id'] ) ) {
			$params['target_warehouse_id'] = get_post_field( 'post_parent', $params['target_bin_id'] );
		}

		$success = MEP_Inventory::transfer( $params );
		return new WP_REST_Response( array( 'success' => $success ), $success ? 200 : 400 );
	}

	public function get_warehouses( $request ) {
		$posts = get_posts( array( 'post_type' => 'mep_warehouse', 'numberposts' => -1 ) );
		$data = array();
		foreach ( $posts as $post ) {
			$data[] = array( 'id' => $post->ID, 'name' => $post->post_title );
		}
		return new WP_REST_Response( $data, 200 );
	}

	public function create_po_from_items( $request ) {
		$items = $request->get_param( 'items' );
		if ( empty( $items ) ) {
			return new WP_Error( 'empty_items', 'No items provided for PO.', array( 'status' => 400 ) );
		}

		$po_ids = MEP_Procurement::generate_pos_from_mrp( $items );

		foreach ( $po_ids as $id ) {
			MEP_DB::log_audit( 'po', $id, 'CREATE_FROM_MRP', '', $items );
		}

		return new WP_REST_Response( array( 'success' => true, 'po_ids' => $po_ids ), 200 );
	}

	public function get_warehouse_bins( $request ) {
		$warehouse_id = $request['id'];
		$data = MEP_Inventory::get_bins_with_inventory( $warehouse_id );
		return new WP_REST_Response( $data, 200 );
	}

	public function get_kpis( $request ) {
		$data = MEP_Reports::get_kpis();
		return new WP_REST_Response( $data, 200 );
	}

	public function get_lot_trace( $request ) {
		$lot = $request['lot'];
		$data = MEP_Quality::trace_lot( $lot );
		return new WP_REST_Response( $data, 200 );
	}

	public function get_lot_trace_report( $request ) {
		$lot = $request['lot'];
		$data = MEP_Quality::trace_lot( $lot );

		header( 'Content-Type: text/html' );
		include MEP_PLUGIN_DIR . 'templates/genealogy-report.php';
		exit;
	}

	public function get_equipment_capacity( $request ) {
		$data = MEP_Equipment::get_capacity_data();
		return new WP_REST_Response( $data, 200 );
	}

	public function get_customers( $request ) {
		$posts = get_posts( array( 'post_type' => 'mep_customer', 'numberposts' => -1 ) );
		$data = array();
		foreach ( $posts as $post ) {
			$data[] = array( 'id' => $post->ID, 'name' => $post->post_title );
		}
		return new WP_REST_Response( $data, 200 );
	}

	public function get_inventory_csv( $request ) {
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="inventory-export.csv"' );
		MEP_Reports::export_inventory_csv();
		exit;
	}

	public function update_bom( $request ) {
		$product_id = $request['id'];
		$components = $request->get_param( 'components' );

		if ( ! is_array( $components ) ) {
			return new WP_Error( 'invalid_data', 'BOM components must be an array.', array( 'status' => 400 ) );
		}

		// Find or create BOM post
		$bom_posts = get_posts( array(
			'post_type'   => 'mep_bom',
			'post_parent' => $product_id,
			'post_status' => 'any',
			'numberposts' => 1,
		) );

		if ( empty( $bom_posts ) ) {
			$bom_id = wp_insert_post( array(
				'post_type'   => 'mep_bom',
				'post_title'  => sprintf( __( 'BOM for Product #%d', 'manufacturing-erp-pro' ), $product_id ),
				'post_parent' => $product_id,
				'post_status' => 'publish',
			) );
		} else {
			$bom_id = $bom_posts[0]->ID;
		}

		$old_components = get_post_meta( $bom_id, '_mep_components', true );
		update_post_meta( $bom_id, '_mep_components', $components );

		MEP_DB::log_audit( 'bom', $bom_id, 'UPDATE_COMPONENTS', $old_components, $components );

		return new WP_REST_Response( array( 'success' => true, 'bom_id' => $bom_id ), 200 );
	}
}
