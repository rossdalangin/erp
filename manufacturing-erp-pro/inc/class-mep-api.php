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
			'permission_callback' => array( $this, 'check_inventory_permission' ),
		) );

		register_rest_route( 'mep/v1', '/work-orders', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_work_orders' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/work-orders/(?P<id>\d+)/status', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'update_work_order_status' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/work-orders/(?P<id>\d+)/print', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'print_work_order' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/bom/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_bom' ),
			'permission_callback' => array( $this, 'check_bom_permission' ),
		) );

		register_rest_route( 'mep/v1', '/bom/(?P<id>\d+)', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'update_bom' ),
			'permission_callback' => array( $this, 'check_bom_permission' ),
		) );

		register_rest_route( 'mep/v1', '/mrp/run', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'run_mrp' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/mrp/status', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_mrp_status' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/mrp/pegging', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_pegging_data' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/inventory/transfer', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'transfer_inventory' ),
			'permission_callback' => array( $this, 'check_inventory_permission' ),
		) );

		register_rest_route( 'mep/v1', '/inventory/receive', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'receive_inventory' ),
			'permission_callback' => array( $this, 'check_inventory_permission' ),
		) );

		register_rest_route( 'mep/v1', '/warehouses', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_warehouses' ),
			'permission_callback' => array( $this, 'check_inventory_permission' ),
		) );

		register_rest_route( 'mep/v1', '/warehouses/(?P<id>\d+)/bins', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_warehouse_bins' ),
			'permission_callback' => array( $this, 'check_inventory_permission' ),
		) );

		register_rest_route( 'mep/v1', '/reports/kpis', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_kpis' ),
			'permission_callback' => array( $this, 'check_read_permission' ),
		) );

		register_rest_route( 'mep/v1', '/reports/quality', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_quality_reports' ),
			'permission_callback' => array( $this, 'check_quality_permission' ),
		) );

		register_rest_route( 'mep/v1', '/qc/trace/(?P<lot>[a-zA-Z0-9\-_]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_lot_trace' ),
			'permission_callback' => array( $this, 'check_quality_permission' ),
		) );

		register_rest_route( 'mep/v1', '/qc/trace/(?P<lot>[a-zA-Z0-9\-_]+)/report', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_lot_trace_report' ),
			'permission_callback' => array( $this, 'check_quality_permission' ),
		) );

		register_rest_route( 'mep/v1', '/equipment/capacity', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_equipment_capacity' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/equipment', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_equipment' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/equipment/detailed', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_equipment_detailed' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/reports/inventory-csv', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_inventory_csv' ),
			'permission_callback' => array( $this, 'check_inventory_permission' ),
		) );

		register_rest_route( 'mep/v1', '/reports/diagnostic-export', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_diagnostic_export' ),
			'permission_callback' => array( $this, 'check_admin_permission' ),
		) );

		register_rest_route( 'mep/v1', '/customers', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_customers' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/procurement/po-from-items', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'create_po_from_items' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/suppliers', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_suppliers' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/procurement/supplier-score/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_supplier_score' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/operators', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_operators' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );

		register_rest_route( 'mep/v1', '/settings', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'update_settings' ),
			'permission_callback' => array( $this, 'check_admin_permission' ),
		) );

		register_rest_route( 'mep/v1', '/seed', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'run_seeder' ),
			'permission_callback' => array( $this, 'check_admin_permission' ),
		) );

		register_rest_route( 'mep/v1', '/production/release', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'release_production' ),
			'permission_callback' => array( $this, 'check_production_permission' ),
		) );
	}

	public function check_admin_permission() {
		return current_user_can( 'manage_options' ) || current_user_can( 'mep_manage_all' );
	}

	public function check_inventory_permission() {
		return $this->check_admin_permission() || current_user_can( 'mep_manage_inventory' );
	}

	public function check_production_permission() {
		return $this->check_admin_permission() || current_user_can( 'mep_manage_production' );
	}

	public function check_bom_permission() {
		return $this->check_admin_permission() || current_user_can( 'mep_manage_bom' );
	}

	public function check_quality_permission() {
		return $this->check_admin_permission() || current_user_can( 'mep_manage_quality' );
	}

	public function check_read_permission() {
		return current_user_can( 'read' );
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
			$operator = get_userdata( $post->post_author );
			$eq_id    = get_post_meta( $post->ID, '_mep_assigned_equipment_id', true );
			$data[] = array(
				'id'             => $post->ID,
				'title'          => $post->post_title,
				'status'         => $post->post_status,
				'qty'            => get_post_meta( $post->ID, '_mep_work_order_qty', true ),
				'due_date'       => get_post_meta( $post->ID, '_mep_due_date', true ),
				'operator_name'  => $operator ? $operator->display_name : '',
				'equipment_name' => $eq_id ? get_the_title( $eq_id ) : '',
			);
		}

		return new WP_REST_Response( $data, 200 );
	}

	public function update_work_order_status( $request ) {
		$id = $request['id'];
		$status = $request['status'];
		$params = $request->get_params();

		$old_status = get_post_field( 'post_status', $id );

		$update_data = array(
			'ID'          => $id,
			'post_status' => $status
		);

		if ( isset( $params['operator_id'] ) ) {
			$update_data['post_author'] = intval( $params['operator_id'] );
		}

		wp_update_post( $update_data );

		if ( isset( $params['equipment_id'] ) ) {
			update_post_meta( $id, '_mep_assigned_equipment_id', intval( $params['equipment_id'] ) );
		}

		if ( isset( $params['scrap_qty'] ) ) {
			update_post_meta( $id, '_mep_actual_scrap', (float) $params['scrap_qty'] );
		}
		if ( isset( $params['labor_mins'] ) ) {
			update_post_meta( $id, '_mep_actual_labor_mins', (float) $params['labor_mins'] );
		}

		$qc_id = 0;
		$lot_number = '';

		// Also log to production logs table if completed
		if ( $status === 'completed' ) {
			global $wpdb;
			$output_qty = (float) get_post_meta( $id, '_mep_work_order_qty', true );
			$wpdb->insert( $wpdb->prefix . 'mep_production_logs', array(
				'work_order_id' => $id,
				'output_qty'    => $output_qty,
				'scrap_qty'     => (float) ( $params['scrap_qty'] ?? 0 ),
				'labor_mins'    => (float) ( $params['labor_mins'] ?? 0 ),
				'created_at'    => current_time( 'mysql' )
			) );

			$lot_number = $params['lot_number'] ?? sprintf( 'LOT-%d-%s', $id, date('md') );
			update_post_meta( $id, '_mep_batch_code', $lot_number );

			// Trigger automatic Quality Check post
			$qc_id = wp_insert_post( array(
				'post_type'   => 'mep_qc_check',
				'post_title'  => sprintf( 'QC for Lot %s (WO #%d)', $lot_number, $id ),
				'post_status' => 'publish',
				'post_parent' => $id
			) );
			update_post_meta( $qc_id, '_mep_qc_status', 'PENDING' );
			update_post_meta( $qc_id, '_mep_lot_number', $lot_number );
		}

		MEP_DB::log_audit( 'work_order', $id, 'STATUS_CHANGE', $old_status, $status );

		return new WP_REST_Response( array(
			'success' => true,
			'qc_id'   => $qc_id,
			'lot_number' => $lot_number
		), 200 );
	}

	public function print_work_order( $request ) {
		$id = $request['id'];
		$wo = get_post( $id );
		if ( ! $wo ) return new WP_Error( 'not_found', 'Work Order not found', array( 'status' => 404 ) );

		$product_id = (int) $wo->post_parent;

		// Gather data
		$data = array(
			'id'           => $id,
			'status'       => $wo->post_status,
			'qty'          => get_post_meta( $id, '_mep_work_order_qty', true ),
			'start_date'   => get_post_meta( $id, '_mep_start_date', true ),
			'assignee'     => get_the_author_meta( 'display_name', $wo->post_author ),
			'product_name' => get_the_title( $product_id ),
			'product_sku'  => get_post_meta( $product_id, '_mep_sku', true ),
			'batch_code'   => get_post_meta( $id, '_mep_batch_code', true ) ?: 'N/A',
			'bom'          => MEP_BOM::get_bom_tree( $product_id ),
			'route'        => array()
		);

		// Get Routing Steps
		$route_posts = get_posts( array( 'post_type' => 'mep_route', 'post_parent' => $product_id, 'numberposts' => 1 ) );
		if ( ! empty( $route_posts ) ) {
			$steps = get_post_meta( $route_posts[0]->ID, '_mep_steps', true );
			if ( is_array( $steps ) ) {
				foreach ( $steps as $step ) {
					$step['work_center_name'] = get_the_title( $step['work_center'] );
					$data['route'][] = $step;
				}
			}
		}

		header( 'Content-Type: text/html' );
		include MEP_PLUGIN_DIR . 'templates/work-order-print.php';
		exit;
	}

	public function get_bom( $request ) {
		$product_id = $request['id'];
		$tree = MEP_BOM::get_bom_tree( $product_id );
		$cost = MEP_BOM::calculate_roll_up_cost( $product_id );

		// Get latest version
		$bom_posts = get_posts( array(
			'post_type'   => 'mep_bom',
			'post_parent' => $product_id,
			'post_status' => 'publish',
			'numberposts' => 1,
			'orderby'     => 'ID',
			'order'       => 'DESC'
		) );
		$version = ! empty( $bom_posts ) ? (int) get_post_meta( $bom_posts[0]->ID, '_mep_version', true ) : 1;

		return new WP_REST_Response( array(
			'product_id' => $product_id,
			'bom'        => $tree,
			'total_cost' => $cost,
			'version'    => $version ?: 1
		), 200 );
	}

	public function run_mrp( $request ) {
		MEP_MRP::start_background_run();
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function get_mrp_status( $request ) {
		$status = MEP_MRP::get_status();
		return new WP_REST_Response( $status, 200 );
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

	public function receive_inventory( $request ) {
		$params = $request->get_params();
		$id = MEP_Inventory::record_transaction( $params );
		return new WP_REST_Response( array( 'success' => (bool)$id, 'transaction_id' => $id ), $id ? 200 : 400 );
	}

	public function get_warehouses( $request ) {
		$posts = get_posts( array( 'post_type' => 'mep_warehouse', 'numberposts' => -1 ) );
		$data = array();
		foreach ( $posts as $post ) {
			$data[] = array( 'id' => $post->ID, 'name' => $post->post_title );
		}
		return new WP_REST_Response( $data, 200 );
	}

	public function get_operators( $request ) {
		$users = get_users( array( 'role__in' => array( 'mep_production_manager', 'administrator', 'editor' ) ) );
		$data = array();
		foreach ( $users as $user ) {
			$data[] = array( 'id' => $user->ID, 'name' => $user->display_name );
		}
		return new WP_REST_Response( $data, 200 );
	}

	public function get_suppliers( $request ) {
		$posts = get_posts( array( 'post_type' => 'mep_supplier', 'numberposts' => -1 ) );
		$data = array();
		foreach ( $posts as $post ) {
			$data[] = array( 'id' => $post->ID, 'name' => $post->post_title );
		}
		return new WP_REST_Response( $data, 200 );
	}

	public function get_supplier_score( $request ) {
		$id = $request['id'];
		$score = MEP_Procurement::calculate_supplier_score( $id );
		return new WP_REST_Response( $score, 200 );
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

	public function get_quality_reports( $request ) {
		$data = MEP_Reports::get_quality_metrics();
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

	public function get_equipment( $request ) {
		$posts = get_posts( array( 'post_type' => 'mep_equipment', 'numberposts' => -1 ) );
		$data = array();
		foreach ( $posts as $post ) {
			$data[] = array(
				'id' => $post->ID,
				'name' => $post->post_title,
				'labor_rate' => get_post_meta($post->ID, '_mep_labor_rate', true) ?: 0.5
			);
		}
		return new WP_REST_Response( $data, 200 );
	}

	public function get_equipment_detailed( $request ) {
		$posts = get_posts( array( 'post_type' => 'mep_equipment', 'numberposts' => -1 ) );
		$data = array();
		foreach ( $posts as $post ) {
			$data[] = array(
				'id'               => $post->ID,
				'name'             => $post->post_title,
				'capacity'         => get_post_meta($post->ID, '_mep_daily_capacity_mins', true) ?: 480,
				'maintenance_logs' => get_post_meta($post->ID, '_mep_maintenance_logs', true) ?: array()
			);
		}
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

	public function get_diagnostic_export( $request ) {
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="erp-diagnostic.txt"' );
		MEP_Reports::export_erp_diagnostic();
		exit;
	}

	public function release_production( $request ) {
		$product_id = $request->get_param( 'product_id' );
		$qty = $request->get_param( 'qty' );
		$due_date = $request->get_param( 'due_date' );

		if ( ! $product_id || ! $qty ) {
			return new WP_Error( 'invalid_data', 'Product ID and Quantity are required.', array( 'status' => 400 ) );
		}

		$wo_id = wp_insert_post( array(
			'post_type'   => 'mep_work_order',
			'post_title'  => sprintf( 'WO-%s-%s', get_post_meta( $product_id, '_mep_sku', true ), date( 'mdHis' ) ),
			'post_parent' => $product_id,
			'post_status' => 'publish',
		) );

		if ( ! is_wp_error( $wo_id ) ) {
			update_post_meta( $wo_id, '_mep_work_order_qty', (float) $qty );
			update_post_meta( $wo_id, '_mep_due_date', sanitize_text_field( $due_date ) );
			MEP_DB::log_audit( 'work_order', $wo_id, 'RELEASE_FROM_BOM', '', array( 'product_id' => $product_id, 'qty' => $qty ) );
			return new WP_REST_Response( array( 'success' => true, 'wo_id' => $wo_id ), 200 );
		}

		return new WP_Error( 'create_failed', 'Failed to create work order.', array( 'status' => 500 ) );
	}

	public function run_seeder( $request ) {
		MEP_Seeder::seed();
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function update_settings( $request ) {
		$params = $request->get_params();
		foreach ( $params as $key => $val ) {
			if ( strpos( $key, 'mep_' ) === 0 ) {
				update_option( $key, sanitize_text_field( $val ) );
			}
		}
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function update_bom( $request ) {
		$product_id = $request['id'];
		$components = $request->get_param( 'components' );
		$new_version = $request->get_param( 'create_new_version' );

		if ( ! is_array( $components ) ) {
			return new WP_Error( 'invalid_data', 'BOM components must be an array.', array( 'status' => 400 ) );
		}

		// Find latest version
		$bom_posts = get_posts( array(
			'post_type'   => 'mep_bom',
			'post_parent' => $product_id,
			'post_status' => 'any',
			'numberposts' => 1,
			'orderby'     => 'ID',
			'order'       => 'DESC'
		) );

		$latest_version = ! empty( $bom_posts ) ? (int) get_post_meta( $bom_posts[0]->ID, '_mep_version', true ) : 0;

		if ( empty( $bom_posts ) || $new_version ) {
			// Create new post for new version
			$version = $latest_version + 1;
			$bom_id = wp_insert_post( array(
				'post_type'   => 'mep_bom',
				'post_title'  => sprintf( __( 'BOM for Product #%d (v%d)', 'manufacturing-erp-pro' ), $product_id, $version ),
				'post_parent' => $product_id,
				'post_status' => 'publish',
			) );
			update_post_meta( $bom_id, '_mep_version', $version );
		} else {
			// Update existing latest post
			$bom_id = $bom_posts[0]->ID;
		}

		$old_components = get_post_meta( $bom_id, '_mep_components', true );
		update_post_meta( $bom_id, '_mep_components', $components );

		MEP_DB::log_audit( 'bom', $bom_id, 'UPDATE_COMPONENTS', $old_components, $components );

		return new WP_REST_Response( array( 'success' => true, 'bom_id' => $bom_id, 'version' => get_post_meta($bom_id, '_mep_version', true) ), 200 );
	}
}
