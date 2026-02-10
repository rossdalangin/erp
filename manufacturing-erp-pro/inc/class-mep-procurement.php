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
		// In a real system, this would analyze PO lead times and QC pass rates.
		// For this implementation, we return a mock performance object.
		return array(
			'quality_rate' => 0.95, // 95% pass rate
			'on_time_rate' => 0.88, // 88% on-time delivery
			'score'        => 92,   // Overall score
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
