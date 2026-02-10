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
	 * Reserve stock for a work order.
	 */
	public static function reserve_stock( $material_id, $work_order_id, $qty ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_stock_reservations';

		$inserted = $wpdb->insert( $table_name, array(
			'material_id'   => $material_id,
			'work_order_id' => $work_order_id,
			'quantity'      => $qty,
			'status'        => 'ACTIVE'
		) );

		if ( $inserted ) {
			MEP_DB::log_audit( 'reservation', $wpdb->insert_id, 'RESERVE', '', array( 'mat' => $material_id, 'wo' => $work_order_id, 'qty' => $qty ) );
		}
		return $inserted;
	}

	/**
	 * Release a stock reservation.
	 */
	public static function release_reservation( $work_order_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_stock_reservations';

		$wpdb->update(
			$table_name,
			array( 'status' => 'RELEASED' ),
			array( 'work_order_id' => $work_order_id, 'status' => 'ACTIVE' )
		);

		MEP_DB::log_audit( 'reservation', $work_order_id, 'RELEASE', 'ACTIVE', 'RELEASED' );
	}

	/**
	 * Get total reserved quantity for a material.
	 */
	public static function get_reserved_qty( $material_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_stock_reservations';

		return (float) $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(quantity) FROM $table_name WHERE material_id = %d AND status = 'ACTIVE'",
			$material_id
		) );
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
	 * Transfer stock between bins atomically.
	 */
	public static function transfer( $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_inventory_transactions';

		$wpdb->query( 'START TRANSACTION' );

		// 1. Debit Source Bin
		$debit = $wpdb->insert( $table_name, array(
			'material_id'      => $data['material_id'],
			'warehouse_id'     => $data['source_warehouse_id'],
			'bin_id'           => $data['source_bin_id'],
			'quantity'         => - (float) $data['quantity'],
			'transaction_type' => 'TRANSFER',
			'reference_id'     => isset( $data['reference_id'] ) ? $data['reference_id'] : null,
		) );

		// 2. Credit Target Bin
		$credit = $wpdb->insert( $table_name, array(
			'material_id'      => $data['material_id'],
			'warehouse_id'     => $data['target_warehouse_id'],
			'bin_id'           => $data['target_bin_id'],
			'quantity'         => (float) $data['quantity'],
			'transaction_type' => 'TRANSFER',
			'reference_id'     => isset( $data['reference_id'] ) ? $data['reference_id'] : null,
		) );

		if ( $debit && $credit ) {
			MEP_DB::log_audit( 'inventory', 0, 'BIN_TRANSFER', '', $data );
			$wpdb->query( 'COMMIT' );
			return true;
		} else {
			$wpdb->query( 'ROLLBACK' );
			return false;
		}
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

			$capacity = (float) get_post_meta( $bin->ID, '_mep_capacity', true );
			if ( ! $capacity ) $capacity = 1000; // Default capacity for visualization

			$total_qty = 0;
			foreach ( $on_hand as $item ) {
				$total_qty += (float) $item->qty;
			}

			$data[] = array(
				'id'         => $bin->ID,
				'name'       => $bin->post_title,
				'capacity'   => $capacity,
				'total_qty'  => $total_qty,
				'occupancy'  => $capacity > 0 ? round( ( $total_qty / $capacity ) * 100 ) : 0,
				'items'      => $on_hand
			);
		}

		return $data;
	}
}
