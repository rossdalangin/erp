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

		$wpdb->insert( $table_name, array(
			'material_id'      => $data['material_id'],
			'warehouse_id'     => $data['warehouse_id'],
			'bin_id'           => $data['bin_id'],
			'quantity'         => $data['quantity'],
			'transaction_type' => $data['type'], // RECEIVE, ISSUE, TRANSFER, ADJUST
			'reference_id'     => isset( $data['reference_id'] ) ? $data['reference_id'] : null,
			'lot_number'       => isset( $data['lot_number'] ) ? $data['lot_number'] : null,
		) );

		return $wpdb->insert_id;
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
}
