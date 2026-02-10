<?php
/**
 * MEP Inventory Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Inventory {

	/**
	 * Record an inventory transaction.
	 */
	public static function record_transaction( $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_inventory_transactions';

		$wpdb->query( 'START TRANSACTION' );

		// Pessimistic Lock: Ensure no other process is calculating levels for this material simultaneously
		$wpdb->get_row( $wpdb->prepare(
			"SELECT id FROM $table_name WHERE material_id = %d FOR UPDATE",
			$data['material_id']
		) );

		$inserted = $wpdb->insert( $table_name, array(
			'material_id'      => $data['material_id'],
			'warehouse_id'     => $data['warehouse_id'],
			'bin_id'           => $data['bin_id'],
			'quantity'         => $data['quantity'],
			'transaction_type' => $data['type'], // RECEIVE, ISSUE, TRANSFER, ADJUST
			'reference_id'     => isset( $data['reference_id'] ) ? $data['reference_id'] : null,
			'lot_number'       => isset( $data['lot_number'] ) ? $data['lot_number'] : null,
		) );

		$id = $wpdb->insert_id;

		if ( $inserted ) {
			MEP_DB::log_audit( 'inventory', $id, 'TRANSACTION_' . $data['type'], '', $data );
			$wpdb->query( 'COMMIT' );
		} else {
			$wpdb->query( 'ROLLBACK' );
		}

		return $id;
	}

	/**
	 * Get on-hand quantity for a material.
	 */
	public static function get_stock_level( $material_id, $warehouse_id = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_inventory_transactions';

		$query = "SELECT SUM(quantity) FROM $table_name WHERE material_id = %d";
		$params = array( $material_id );

		if ( $warehouse_id ) {
			$query .= " AND warehouse_id = %d";
			$params[] = $warehouse_id;
		}

		return (float) $wpdb->get_var( $wpdb->prepare( $query, $params ) );
	}

	/**
	 * Get bin details including inventory levels.
	 */
	public static function get_bins_with_inventory( $warehouse_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_inventory_transactions';

		$bins = get_posts( array(
			'post_type'   => 'mep_bin',
			'post_parent' => $warehouse_id,
			'numberposts' => -1
		) );

		$data = array();
		foreach ( $bins as $bin ) {
			$on_hand = $wpdb->get_results( $wpdb->prepare(
				"SELECT material_id, SUM(quantity) as qty
				 FROM $table_name
				 WHERE bin_id = %d
				 GROUP BY material_id HAVING qty > 0",
				$bin->ID
			) );

			$data[] = array(
				'id'    => $bin->ID,
				'name'  => $bin->post_title,
				'items' => $on_hand
			);
		}

		return $data;
	}
}
