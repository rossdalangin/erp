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

		register_rest_route( 'mep/v1', '/bom/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_bom' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		register_rest_route( 'mep/v1', '/mrp/run', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'run_mrp' ),
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
		$posts = get_posts( array( 'post_type' => 'mep_work_order', 'numberposts' => -1 ) );
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
}
