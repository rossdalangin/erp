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
		self::create_post( 'mep_bin', 'Leather Storage (A1)', $wh_main );
		self::create_post( 'mep_bin', 'Cutting Area (P1)', $wh_prod );

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

		// 8. Create Additional Inventory Transactions
		for ($i = 0; $i < 15; $i++) {
			MEP_Inventory::record_transaction( array(
				'material_id'  => $leather,
				'warehouse_id' => ($i % 2 == 0) ? $wh_main : $wh_prod,
				'bin_id'       => ($i % 2 == 0) ? 1 : 2, // Mocked bin IDs
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
