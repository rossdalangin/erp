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
		$wh_main = self::create_post( 'mep_warehouse', 'Main Warehouse', 0, array(
			'_mep_location_code' => 'WH-MAIN',
			'_mep_capacity' => 5000
		) );
		$wh_prod = self::create_post( 'mep_warehouse', 'Production Floor', 0, array(
			'_mep_location_code' => 'WH-PROD',
			'_mep_capacity' => 2000
		) );

		// 2. Create Bins
		$bin_leather = self::create_post( 'mep_bin', 'Leather Storage', $wh_main, array( '_mep_capacity' => 1000 ) );
		$bin_cutting = self::create_post( 'mep_bin', 'Cutting Area', $wh_prod, array( '_mep_capacity' => 500 ) );
		$bin_assembly = self::create_post( 'mep_bin', 'Assembly Area', $wh_prod, array( '_mep_capacity' => 500 ) );

		// 3. Create Suppliers
		$sup_a = self::create_post( 'mep_supplier', 'Leather Supplier A', 0, array(
			'_mep_contact_name' => 'John Tanner',
			'_mep_email' => 'john@leather-a.com',
			'_mep_lead_time_avg' => 10
		) );
		$sup_b = self::create_post( 'mep_supplier', 'Sole Supplier B', 0, array(
			'_mep_contact_name' => 'Sara Rubber',
			'_mep_email' => 'sara@soles-b.com',
			'_mep_lead_time_avg' => 14
		) );

		// 4. Create Materials
		$cow_leather = self::create_post( 'mep_material', 'Cowhide leather', 0, array(
			'_mep_sku' => 'MAT-LTH-COW',
			'_mep_uom' => 'm2',
			'_mep_cost_avg' => 45.00,
			'_mep_safety_stock' => 100,
			'_mep_manufacturer_sku' => 'TX-COW-99',
			'_mep_lead_time' => 7,
			'_mep_preferred_supplier' => $sup_a
		) );
		wp_update_post( array( 'ID' => $cow_leather, 'post_content' => 'High-quality top-grain leather. Essential for premium handbag production.' ) );
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
		$tan_bag_id = 0;
		foreach ($variants as $v) {
			$bag = self::create_post( 'mep_product', "Leather Handbag - $v", 0, array(
				'_mep_sku' => "BAG-LTH-" . strtoupper($v),
				'_mep_category' => 'Bags',
				'_mep_weight' => 0.850,
				'_mep_price' => 195.00
			) );

			if ( $v === 'Tan' ) {
				$tan_bag_id = $bag;
			}

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

		// 9. Create Sample Work Orders (Instructional)
		$bag_id = $tan_bag_id;

		if ( ! $bag_id ) {
			$bag_id = self::create_post( 'mep_product', 'Leather Handbag - Tan' );
		}

		$wo_scenarios = array(
			array('title' => 'INSTRUCTION: Draft Work Order', 'status' => 'publish', 'desc' => 'This order is in the backlog. Drag it to "In Progress" on the Kanban board to start.'),
			array('title' => 'EXAMPLE: Active Production', 'status' => 'in-progress', 'desc' => 'This order is currently being worked on by an operator.'),
			array('title' => 'TRAINING: Completed Job', 'status' => 'completed', 'desc' => 'This job is finished. Notice how actual labor and scrap were recorded.'),
		);

		foreach ($wo_scenarios as $i => $scene) {
			wp_insert_post( array(
				'post_type'   => 'mep_work_order',
				'post_title'  => $scene['title'],
				'post_content' => $scene['desc'],
				'post_status' => $scene['status'],
				'post_parent' => $bag_id,
				'meta_input'  => array(
					'_mep_work_order_qty' => 25,
					'_mep_due_date'       => date('Y-m-d', strtotime("+" . ($i+1) . " week")),
					'_mep_is_sample_data' => 1
				)
			) );
		}

		// 10. Create QC Records (Instructional)
		$qc_id = MEP_Quality::record_qc_result( array(
			'object_name' => 'INSTRUCTIONAL QC: Failed Inspection',
			'object_id' => $bag_id,
			'object_type' => 'product',
			'status' => 'FAIL',
			'defects' => 'Example defect: Scratched leather on front panel',
			'trigger_rework' => 1
		) );

		// Promote a sample NCR to CAPA
		$ncr_posts = get_posts( array( 'post_type' => 'mep_ncr', 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'DESC' ) );
		if ( ! empty( $ncr_posts ) ) {
			MEP_Quality::promote_to_capa( $ncr_posts[0]->ID, 'EXAMPLE CAPA PLAN: Investigate supplier storage conditions and implement better protective wrapping for finished goods.' );
		}

		// 11. Create Customers & Forecasts
		$cust = self::create_post( 'mep_customer', 'Luxury Craft Retailers', 0, array(
			'_mep_contact_name' => 'Alice Boutique',
			'_mep_email' => 'alice@luxurycraft.com',
			'_mep_credit_limit' => 50000
		) );
		self::create_post( 'mep_forecast', 'Winter Collection Forecast', $bag_id, array(
			'_mep_forecast_qty' => 200,
			'_mep_customer_id' => $cust
		) );

		// 12. Subcontracting Example (Service Material)
		$tanning_service = self::create_post( 'mep_material', 'SUBCONTRACT: Leather Tanning Service', 0, array(
			'_mep_sku' => 'SRV-TAN-001',
			'_mep_uom' => 'm2',
			'_mep_cost_avg' => 12.50,
			'_mep_is_service' => 1
		) );
		wp_update_post( array( 'ID' => $tanning_service, 'post_content' => 'Example of a service material used for external subcontracting operations.' ) );

		$subcontractor = self::create_post( 'mep_supplier', 'Global Tanning Solutions (Subcontractor)' );

		// Product that uses subcontracting
		$raw_hide = self::create_post( 'mep_material', 'Raw Untanned Hide', 0, array(
			'_mep_sku' => 'MAT-HIDE-RAW',
			'_mep_uom' => 'pcs',
			'_mep_cost_avg' => 25.00
		) );

		$tanned_hide = self::create_post( 'mep_product', 'Tanned Leather Sheet', 0, array(
			'_mep_sku' => 'PRD-LTH-TANNED'
		) );

		$tanned_bom = self::create_post( 'mep_bom', 'BOM for Tanned Leather (Subcontracted)', $tanned_hide );
		update_post_meta( $tanned_bom, '_mep_components', array(
			array('id' => $raw_hide, 'type' => 'material', 'qty' => 1, 'scrap' => 0),
			array('id' => $tanning_service, 'type' => 'material', 'qty' => 1, 'scrap' => 0)
		) );
	}

	/**
	 * Reset data.
	 * @param bool $hard If true, delete everything. If false, keep master data.
	 */
	public static function reset( $hard = true ) {
		global $wpdb;

		// Perform automatic backup before reset
		$upload_dir = wp_upload_dir();
		$mep_dir = $upload_dir['basedir'] . '/mep_backups';
		if ( ! file_exists( $mep_dir ) ) {
			wp_mkdir_p( $mep_dir );
		}
		$filename = $mep_dir . '/backup_before_reset_' . date( 'Y-m-d_H-i-s' ) . '.txt';
		MEP_Reports::export_erp_diagnostic( $filename );

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
