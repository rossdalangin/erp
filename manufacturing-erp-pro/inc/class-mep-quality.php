<?php
/**
 * MEP Quality & Traceability Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Quality {

	/**
	 * Record a QC check result.
	 */
	public static function record_qc_result( $data ) {
		$id = wp_insert_post( array(
			'post_type'   => 'mep_qc_check',
			'post_title'  => sprintf( __( 'QC Check for %s', 'manufacturing-erp-pro' ), $data['object_name'] ),
			'post_status' => 'publish',
		) );

		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_mep_qc_status', $data['status'] ); // PASS, FAIL
			update_post_meta( $id, '_mep_qc_object_id', $data['object_id'] );
			update_post_meta( $id, '_mep_qc_object_type', $data['object_type'] );
			update_post_meta( $id, '_mep_qc_defects', $data['defects'] );

			if ( $data['status'] === 'FAIL' ) {
				self::create_ncr( $id, $data );
			}
		}

		return $id;
	}

	/**
	 * Create a Non-Conformance Report (NCR).
	 */
	private static function create_ncr( $qc_id, $data ) {
		wp_insert_post( array(
			'post_type'   => 'mep_ncr',
			'post_title'  => sprintf( __( 'NCR for QC #%d', 'manufacturing-erp-pro' ), $qc_id ),
			'post_status' => 'publish',
			'post_parent' => $qc_id,
		) );
	}

	/**
	 * Trace lot genealogy.
	 *
	 * @param string $lot_number
	 * @return array
	 */
	public static function trace_lot( $lot_number ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_inventory_transactions';

		// Find transactions for this lot
		$transactions = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table_name WHERE lot_number = %s",
			$lot_number
		) );

		$trace = array(
			'lot' => $lot_number,
			'history' => $transactions,
			'upstream' => array(),
			'downstream' => array()
		);

		return $trace;
	}
}
