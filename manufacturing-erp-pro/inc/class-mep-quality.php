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

				// Handle Rework Trigger if requested
				if ( ! empty( $data['trigger_rework'] ) ) {
					self::trigger_rework( $id, $data );
				}
			}
		}

		return $id;
	}

	/**
	 * Trigger a Rework Work Order.
	 */
	public static function trigger_rework( $qc_id, $data ) {
		$rework_wo_id = wp_insert_post( array(
			'post_type'   => 'mep_work_order',
			'post_title'  => sprintf( __( 'REWORK: %s (from QC #%d)', 'manufacturing-erp-pro' ), $data['object_name'], $qc_id ),
			'post_status' => 'publish',
			'post_parent' => $data['object_id'], // Original Product/Material
		) );

		if ( ! is_wp_error( $rework_wo_id ) ) {
			update_post_meta( $rework_wo_id, '_mep_is_rework', 1 );
			update_post_meta( $rework_wo_id, '_mep_linked_qc_id', $qc_id );
			update_post_meta( $rework_wo_id, '_mep_work_order_qty', 1 ); // Usually 1 unit for rework

			MEP_DB::log_audit( 'work_order', $rework_wo_id, 'REWORK_TRIGGERED', '', $qc_id );
		}

		return $rework_wo_id;
	}

	/**
	 * Create a Non-Conformance Report (NCR).
	 */
	private static function create_ncr( $qc_id, $data ) {
		$ncr_id = wp_insert_post( array(
			'post_type'   => 'mep_ncr',
			'post_title'  => sprintf( __( 'NCR for QC #%d', 'manufacturing-erp-pro' ), $qc_id ),
			'post_status' => 'publish',
			'post_parent' => $qc_id,
		) );

		if ( ! is_wp_error( $ncr_id ) ) {
			update_post_meta( $ncr_id, '_mep_ncr_type', $data['defects'] );
		}
	}

	/**
	 * Promote NCR to CAPA (Corrective and Preventive Action).
	 */
	public static function promote_to_capa( $ncr_id, $action_plan ) {
		wp_update_post( array(
			'ID'          => $ncr_id,
			'post_status' => 'capa-pending'
		) );

		update_post_meta( $ncr_id, '_mep_capa_plan', $action_plan );
		update_post_meta( $ncr_id, '_mep_capa_start_date', current_time( 'mysql' ) );

		MEP_DB::log_audit( 'ncr', $ncr_id, 'PROMOTED_TO_CAPA', 'publish', 'capa-pending' );
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
