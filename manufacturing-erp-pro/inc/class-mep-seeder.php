<?php
/**
 * MEP Seeder Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Seeder {

	/**
	 * Seed sample data.
	 */
	public static function seed() {
		// 1. Create Warehouses
		$wh_main = self::create_post( 'mep_warehouse', 'Main Warehouse' );
		$wh_prod = self::create_post( 'mep_warehouse', 'Production Floor' );

		// 2. Create Bins
		$bin_main = self::create_post( 'mep_bin', 'Leather Storage (A1)', $wh_main );
		$bin_prod = self::create_post( 'mep_bin', 'Cutting Area (P1)', $wh_prod );

		// 3. Create Suppliers
		self::create_post( 'mep_supplier', 'Leather Supplier A' );

		// 4. Create Materials
		$leather = self::create_post( 'mep_material', 'Cowhide Leather', 0, array(
			'_mep_sku' => 'LTH-COW-001',
			'_mep_uom' => 'm2',
			'_mep_cost_avg' => 45.00
		) );

		// 5. Create Products
		$bag = self::create_post( 'mep_product', 'Signature Leather Handbag', 0, array(
			'_mep_sku' => 'BAG-SIG-001'
		) );

		// 6. Create BOM
		$bom_id = self::create_post( 'mep_bom', 'BOM for Handbag V1', $bag );
		update_post_meta( $bom_id, '_mep_components', array(
			array('id' => $leather, 'type' => 'material', 'qty' => 1.5, 'scrap' => 0.05)
		) );

		// 7. Create Additional Work Orders
		$statuses = array('publish', 'in-progress', 'in-progress', 'completed', 'publish');
		foreach ($statuses as $i => $status) {
			wp_insert_post( array(
				'post_type'   => 'mep_work_order',
				'post_title'  => "Work Order #100" . ($i + 1),
				'post_status' => $status,
				'meta_input'  => array('_mep_is_sample_data' => 1)
			) );
		}

		// 8. Create Equipment
		$eq_cutting = self::create_post( 'mep_equipment', 'Heavy Duty Laser Cutter', 0, array(
			'_mep_daily_capacity_mins' => 480,
			'_mep_labor_rate' => 0.75
		) );
		$eq_stitching = self::create_post( 'mep_equipment', 'Industrial Stitching Machine', 0, array(
			'_mep_daily_capacity_mins' => 480,
			'_mep_labor_rate' => 0.50
		) );

		// 9. Create Routes
		$route_id = self::create_post( 'mep_route', 'Standard Bag Assembly Route', $bag );
		update_post_meta( $route_id, '_mep_steps', array(
			array('work_center' => $eq_cutting, 'time' => 15, 'desc' => 'Cutting pattern'),
			array('work_center' => $eq_stitching, 'time' => 45, 'desc' => 'Main stitching')
		) );

		// 10. Create Customers
		$cust_a = self::create_post( 'mep_customer', 'Luxury Boutiques Inc.' );

		// 11. Create Forecasts
		self::create_post( 'mep_forecast', 'Q4 Sales Forecast', $bag, array(
			'_mep_forecast_qty' => 500,
			'_mep_customer_id' => $cust_a
		) );

		// 12. Create QC Checks
		$qc_id = MEP_Quality::record_qc_result( array(
			'object_name' => 'Leather Handbag Batch #1',
			'object_id' => $bag,
			'object_type' => 'product',
			'status' => 'FAIL',
			'defects' => 'Loose threads on handle',
			'trigger_rework' => 1
		) );

		// 13. Create Additional Inventory Transactions
		for ($i = 0; $i < 15; $i++) {
			MEP_Inventory::record_transaction( array(
				'material_id'  => $leather,
				'warehouse_id' => ($i % 2 == 0) ? $wh_main : $wh_prod,
				'bin_id'       => ($i % 2 == 0) ? $bin_main : $bin_prod,
				'quantity'     => rand(10, 50),
				'type'         => 'RECEIVE',
				'lot_number'   => 'LOT-ABC-' . rand(100, 999)
			) );
		}
	}

	/**
	 * Reset data.
	 * @param bool $hard If true, delete everything. If false, keep master data.
	 */
	public static function reset( $hard = true ) {
		global $wpdb;

		$types_to_delete = array(
			'mep_inventory_transactions',
			'mep_audit_logs',
			'mep_production_logs'
		);

		foreach ( $types_to_delete as $table ) {
			$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}$table" );
		}

		if ( $hard ) {
			$cpts = array(
				'mep_material', 'mep_product', 'mep_bom', 'mep_work_order',
				'mep_batch', 'mep_warehouse', 'mep_bin', 'mep_supplier',
				'mep_po', 'mep_qc_check', 'mep_ncr', 'mep_equipment',
				'mep_route', 'mep_forecast'
			);

			foreach ( $cpts as $cpt ) {
				$posts = get_posts( array( 'post_type' => $cpt, 'numberposts' => -1, 'post_status' => 'any' ) );
				foreach ( $posts as $post ) {
					wp_delete_post( $post->ID, true );
				}
			}
		}
	}

	private static function create_post( $type, $title, $parent = 0, $meta = array() ) {
		$id = wp_insert_post( array(
			'post_type'   => $type,
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_parent' => $parent
		) );

		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_mep_is_sample_data', 1 );
			foreach ( $meta as $key => $value ) {
				update_post_meta( $id, $key, $value );
			}
		}
		return $id;
	}
}
