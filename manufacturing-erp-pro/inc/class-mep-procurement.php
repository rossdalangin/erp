<?php
/**
 * MEP Procurement & Supplier Management
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Procurement {

	/**
	 * Calculate supplier score based on history.
	 *
	 * @param int $supplier_id
	 * @return array
	 */
	public static function calculate_supplier_score( $supplier_id ) {
		// 1. Quality Rate calculation
		$qc_checks = get_posts( array(
			'post_type'  => 'mep_qc_check',
			'meta_query' => array(
				array( 'key' => '_mep_qc_supplier_id', 'value' => $supplier_id )
			),
			'numberposts' => -1
		) );

		$total_qc = count( $qc_checks );
		$passed_qc = 0;
		foreach ( $qc_checks as $qc ) {
			if ( get_post_meta( $qc->ID, '_mep_qc_status', true ) === 'PASS' ) {
				$passed_qc++;
			}
		}
		$quality_rate = $total_qc > 0 ? ( $passed_qc / $total_qc ) : 1.0;

		// 2. OTD (On-Time Delivery) calculation
		$pos = get_posts( array(
			'post_type'  => 'mep_po',
			'meta_query' => array(
				array( 'key' => '_mep_supplier_id', 'value' => $supplier_id )
			),
			'post_status' => 'any',
			'numberposts' => -1
		) );

		$total_received = 0;
		$on_time = 0;
		foreach ( $pos as $po ) {
			$actual = get_post_meta( $po->ID, '_mep_received_date', true );
			$expected = get_post_meta( $po->ID, '_mep_expected_date', true );
			if ( $actual && $expected ) {
				$total_received++;
				if ( strtotime( $actual ) <= strtotime( $expected ) ) {
					$on_time++;
				}
			}
		}
		$otd_rate = $total_received > 0 ? ( $on_time / $total_received ) : 1.0;

		$overall_score = round( ( ( $quality_rate * 0.6 ) + ( $otd_rate * 0.4 ) ) * 100 );

		return array(
			'quality_rate' => $quality_rate,
			'on_time_rate' => $otd_rate,
			'score'        => $overall_score,
		);
	}

	/**
	 * Automatically generate Purchase Orders from MRP suggestions.
	 *
	 * @param array $suggestions
	 */
	public static function generate_pos_from_mrp( $suggestions ) {
		$grouped = array();

		// Group suggestions by preferred supplier (mocked logic)
		foreach ( $suggestions as $sug ) {
			$supplier_id = get_post_meta( $sug['material_id'], '_mep_preferred_supplier', true );
			if ( ! $supplier_id ) {
				// Find any active supplier as fallback
				$suppliers = get_posts( array( 'post_type' => 'mep_supplier', 'numberposts' => 1 ) );
				if ( ! empty( $suppliers ) ) $supplier_id = $suppliers[0]->ID;
			}

			if ( $supplier_id ) {
				$grouped[$supplier_id][] = $sug;
			}
		}

		$po_ids = array();
		foreach ( $grouped as $supplier_id => $items ) {
			$po_id = wp_insert_post( array(
				'post_type'   => 'mep_po',
				'post_title'  => sprintf( __( 'Auto-Generated PO for %s', 'manufacturing-erp-pro' ), get_the_title( $supplier_id ) ),
				'post_status' => 'publish'
			) );

			if ( ! is_wp_error( $po_id ) ) {
				update_post_meta( $po_id, '_mep_supplier_id', $supplier_id );
				update_post_meta( $po_id, '_mep_po_lines', $items );
				$po_ids[] = $po_id;
			}
		}

		return $po_ids;
	}
}
