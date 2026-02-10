<?php
/**
 * MEP Custom Post Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_CPT {

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	public function register_post_types() {
		$types = array(
			'mep_material'    => __( 'Materials', 'manufacturing-erp-pro' ),
			'mep_product'     => __( 'Products', 'manufacturing-erp-pro' ),
			'mep_bom'         => __( 'BOMs', 'manufacturing-erp-pro' ),
			'mep_work_order'  => __( 'Work Orders', 'manufacturing-erp-pro' ),
			'mep_batch'       => __( 'Batches', 'manufacturing-erp-pro' ),
			'mep_warehouse'   => __( 'Warehouses', 'manufacturing-erp-pro' ),
			'mep_bin'         => __( 'Bins', 'manufacturing-erp-pro' ),
			'mep_supplier'    => __( 'Suppliers', 'manufacturing-erp-pro' ),
			'mep_po'          => __( 'Purchase Orders', 'manufacturing-erp-pro' ),
			'mep_qc_check'    => __( 'QC Checks', 'manufacturing-erp-pro' ),
			'mep_ncr'         => __( 'NCRs', 'manufacturing-erp-pro' ),
			'mep_equipment'   => __( 'Equipment', 'manufacturing-erp-pro' ),
			'mep_route'       => __( 'Routes', 'manufacturing-erp-pro' ),
			'mep_forecast'    => __( 'Forecasts', 'manufacturing-erp-pro' ),
		);

		foreach ( $types as $slug => $label ) {
			register_post_type( $slug, array(
				'labels' => array(
					'name'          => $label,
					'singular_name' => $label,
				),
				'public'       => true,
				'has_archive'  => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-hammer',
				'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
				'menu_position' => 25,
			) );
		}
	}
}
