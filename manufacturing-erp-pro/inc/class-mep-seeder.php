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
		$bin_leather = self::create_post( 'mep_bin', 'Leather Storage', $wh_main );
		$bin_cutting = self::create_post( 'mep_bin', 'Cutting Area', $wh_prod );
		$bin_assembly = self::create_post( 'mep_bin', 'Assembly Area', $wh_prod );

		// 3. Create Suppliers
		$sup_a = self::create_post( 'mep_supplier', 'Leather Supplier A' );
		$sup_b = self::create_post( 'mep_supplier', 'Sole Supplier B' );

		// 4. Create Materials
		$cow_leather = self::create_post( 'mep_material', 'Cowhide leather', 0, array(
			'_mep_sku' => 'MAT-LTH-COW',
			'_mep_uom' => 'm2',
			'_mep_cost_avg' => 45.00,
			'_mep_safety_stock' => 100
		) );
		$pu_leather = self::create_post( 'mep_material', 'PU leather', 0, array(
			'_mep_sku' => 'MAT-LTH-PU',
			'_mep_uom' => 'm2',
			'_mep_cost_avg' => 15.00
		) );
		$rubber_soles = self::create_post( 'mep_material', 'Rubber soles', 0, array(
			'_mep_sku' => 'MAT-RUB-SOL',
			'_mep_uom' => 'pcs',
			'_mep_cost_avg' => 5.50
		) );
		$thread = self::create_post( 'mep_material', 'Thread', 0, array(
			'_mep_sku' => 'MAT-THR-001',
			'_mep_uom' => 'spool',
			'_mep_cost_avg' => 2.00
		) );
		$zippers = self::create_post( 'mep_material', 'Zippers', 0, array(
			'_mep_sku' => 'MAT-ZIP-001',
			'_mep_uom' => 'pcs',
			'_mep_cost_avg' => 1.20
		) );

		// 5. Create Equipment
		$eq_cutting = self::create_post( 'mep_equipment', 'Heavy Duty Laser Cutter', 0, array(
			'_mep_daily_capacity_mins' => 480,
			'_mep_labor_rate' => 0.75
		) );
		$eq_stitching = self::create_post( 'mep_equipment', 'Industrial Stitching Machine', 0, array(
			'_mep_daily_capacity_mins' => 480,
			'_mep_labor_rate' => 0.50
		) );
		$eq_assembly = self::create_post( 'mep_equipment', 'Manual Assembly Bench', 0, array(
			'_mep_daily_capacity_mins' => 480,
			'_mep_labor_rate' => 0.40
		) );
		$eq_qc = self::create_post( 'mep_equipment', 'QC Inspection Station', 0, array(
			'_mep_daily_capacity_mins' => 480,
			'_mep_labor_rate' => 0.60
		) );
		$eq_packing = self::create_post( 'mep_equipment', 'Packing Table', 0, array(
			'_mep_daily_capacity_mins' => 480,
			'_mep_labor_rate' => 0.30
		) );

		// 6. Create Product: Leather Handbag (3 Variants) with Nested Assemblies
		$bag_body_asm = self::create_post( 'mep_product', 'Bag Body Assembly', 0, array(
			'_mep_sku' => 'ASM-BAG-BODY'
		) );
		$strap_asm = self::create_post( 'mep_product', 'Strap Assembly', 0, array(
			'_mep_sku' => 'ASM-STRAP'
		) );

		// BOM for Bag Body
		$body_bom = self::create_post( 'mep_bom', 'BOM for Bag Body', $bag_body_asm );
		update_post_meta( $body_bom, '_mep_components', array(
			array('id' => $cow_leather, 'type' => 'material', 'qty' => 0.8, 'scrap' => 0.05),
			array('id' => $thread, 'type' => 'material', 'qty' => 0.05, 'scrap' => 0)
		) );

		// BOM for Strap
		$strap_bom = self::create_post( 'mep_bom', 'BOM for Strap', $strap_asm );
		update_post_meta( $strap_bom, '_mep_components', array(
			array('id' => $cow_leather, 'type' => 'material', 'qty' => 0.3, 'scrap' => 0.02),
			array('id' => $thread, 'type' => 'material', 'qty' => 0.02, 'scrap' => 0)
		) );

		$variants = array('Tan', 'Black', 'Wine');
		foreach ($variants as $v) {
			$bag = self::create_post( 'mep_product', "Leather Handbag - $v", 0, array(
				'_mep_sku' => "BAG-LTH-" . strtoupper($v)
			) );

			// BOM for Bag (Nested)
			$bom_id = self::create_post( 'mep_bom', "BOM for Handbag - $v", $bag );
			update_post_meta( $bom_id, '_mep_components', array(
				array('id' => $bag_body_asm, 'type' => 'product', 'qty' => 1, 'scrap' => 0),
				array('id' => $strap_asm, 'type' => 'product', 'qty' => 2, 'scrap' => 0),
				array('id' => $zippers, 'type' => 'material', 'qty' => 1, 'scrap' => 0)
			) );

			// Route for Bag
			$route_id = self::create_post( 'mep_route', "Standard Route for $v Bag", $bag );
			update_post_meta( $route_id, '_mep_steps', array(
				array('work_center' => $eq_cutting, 'time' => 15, 'desc' => 'Cutting'),
				array('work_center' => $eq_stitching, 'time' => 45, 'desc' => 'Stitching'),
				array('work_center' => $eq_assembly, 'time' => 30, 'desc' => 'Assembly'),
				array('work_center' => $eq_qc, 'time' => 10, 'desc' => 'QC'),
				array('work_center' => $eq_packing, 'time' => 5, 'desc' => 'Packing')
			) );
		}

		// 7. Create Product: Leather Slippers (2 Variants)
		$sole_asm = self::create_post( 'mep_product', 'Slipper Sole Assembly', 0, array(
			'_mep_sku' => 'ASM-SLP-SOLE'
		) );
		$sole_bom = self::create_post( 'mep_bom', 'BOM for Slipper Sole', $sole_asm );
		update_post_meta( $sole_bom, '_mep_components', array(
			array('id' => $rubber_soles, 'type' => 'material', 'qty' => 1, 'scrap' => 0)
		) );

		$s_variants = array('Standard', 'Premium');
		foreach ($s_variants as $v) {
			$slipper = self::create_post( 'mep_product', "Leather Slippers - $v", 0, array(
				'_mep_sku' => "SLP-LTH-" . strtoupper($v)
			) );

			// BOM for Slipper
			$bom_id = self::create_post( 'mep_bom', "BOM for Slippers - $v", $slipper );
			update_post_meta( $bom_id, '_mep_components', array(
				array('id' => ($v == 'Premium' ? $cow_leather : $pu_leather), 'type' => 'material', 'qty' => 0.4, 'scrap' => 0.03),
				array('id' => $sole_asm, 'type' => 'product', 'qty' => 2, 'scrap' => 0),
				array('id' => $thread, 'type' => 'material', 'qty' => 0.05, 'scrap' => 0)
			) );

			// Route for Slipper
			$route_id = self::create_post( 'mep_route', "Slipper Route - $v", $slipper );
			update_post_meta( $route_id, '_mep_steps', array(
				array('work_center' => $eq_cutting, 'time' => 10, 'desc' => 'Cutting'),
				array('work_center' => $eq_stitching, 'time' => 20, 'desc' => 'Stitching'),
				array('work_center' => $eq_assembly, 'time' => 15, 'desc' => 'Assembly'),
				array('work_center' => $eq_qc, 'time' => 5, 'desc' => 'QC'),
				array('work_center' => $eq_packing, 'time' => 5, 'desc' => 'Packing')
			) );
		}

		// 8. Create Inventory Levels
		MEP_Inventory::record_transaction( array(
			'material_id'  => $cow_leather,
			'warehouse_id' => $wh_main,
			'bin_id'       => $bin_leather,
			'quantity'     => 500,
			'type'         => 'RECEIVE',
			'lot_number'   => 'LOT-COW-001'
		) );
		MEP_Inventory::record_transaction( array(
			'material_id'  => $rubber_soles,
			'warehouse_id' => $wh_main,
			'bin_id'       => $bin_leather,
			'quantity'     => 1000,
			'type'         => 'RECEIVE',
			'lot_number'   => 'LOT-RUB-001'
		) );

		// 9. Create Sample Work Orders
		$bag_posts = get_posts(array('post_type'=>'mep_product', 'title'=>'Leather Handbag - Tan', 'numberposts'=>1));
		$bag_id = ! empty( $bag_posts ) ? $bag_posts[0]->ID : 0;

		if ( ! $bag_id ) {
			// Fallback if title search failed (unlikely in fresh seed)
			$bag_id = wp_insert_post( array( 'post_type' => 'mep_product', 'post_title' => 'Leather Handbag - Tan', 'post_status' => 'publish' ) );
		}

		for ($i=1; $i<=3; $i++) {
			wp_insert_post( array(
				'post_type'   => 'mep_work_order',
				'post_title'  => "WO-HANDBAG-00$i",
				'post_status' => ($i == 1 ? 'publish' : ($i == 2 ? 'in-progress' : 'completed')),
				'post_parent' => $bag_id,
				'meta_input'  => array(
					'_mep_work_order_qty' => 50,
					'_mep_due_date'       => date('Y-m-d', strtotime("+$i week")),
					'_mep_is_sample_data' => 1
				)
			) );
		}

		// 10. Create QC Records
		MEP_Quality::record_qc_result( array(
			'object_name' => 'Handbag Batch #A1',
			'object_id' => $bag_id,
			'object_type' => 'product',
			'status' => 'FAIL',
			'defects' => 'Scratched leather on front panel',
			'trigger_rework' => 1
		) );

		// 11. Create Customers & Forecasts
		$cust = self::create_post( 'mep_customer', 'Luxury Craft Retailers' );
		self::create_post( 'mep_forecast', 'Winter Collection Forecast', $bag_id, array(
			'_mep_forecast_qty' => 200,
			'_mep_customer_id' => $cust
		) );
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
			'mep_production_logs',
			'mep_stock_reservations'
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
