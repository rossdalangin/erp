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

		wp_update_post( array(
			'ID'          => $id,
			'post_status' => $status
		) );

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

	public function get_warehouses( $request ) {
		$posts = get_posts( array( 'post_type' => 'mep_warehouse', 'numberposts' => -1 ) );
		$data = array();
		foreach ( $posts as $post ) {
			$data[] = array( 'id' => $post->ID, 'name' => $post->post_title );
		}
		return new WP_REST_Response( $data, 200 );
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

	public function update_bom( $request ) {
		$product_id = $request['id'];
		$components = $request->get_param( 'components' );

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

		update_post_meta( $bom_id, '_mep_components', $components );

		return new WP_REST_Response( array( 'success' => true, 'bom_id' => $bom_id ), 200 );
	}
}
