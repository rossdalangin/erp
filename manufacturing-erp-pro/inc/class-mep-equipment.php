<?php
/**
 * MEP Equipment & Capacity Management
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Equipment {

	/**
	 * Get current capacity and load for all equipment.
	 */
	public static function get_capacity_data() {
		$equipment = get_posts( array(
			'post_type'   => 'mep_equipment',
			'numberposts' => -1,
		) );

		$data = array();
		foreach ( $equipment as $item ) {
			$capacity = (float) get_post_meta( $item->ID, '_mep_daily_capacity_mins', true );
			if ( ! $capacity ) $capacity = 480; // Default 8 hours

			$load = self::calculate_current_load( $item->ID );

			$data[] = array(
				'id'       => $item->ID,
				'name'     => $item->post_title,
				'capacity' => $capacity,
				'load'     => $load,
				'percent'  => $capacity > 0 ? round( ( $load / $capacity ) * 100 ) : 0
			);
		}

		return $data;
	}

	/**
	 * Calculate current load for a specific machine.
	 */
	private static function calculate_current_load( $equipment_id ) {
		// Query work orders assigned to this machine that are not completed
		$work_orders = get_posts( array(
			'post_type'  => 'mep_work_order',
			'meta_query' => array(
				array( 'key' => '_mep_assigned_equipment_id', 'value' => $equipment_id )
			),
			'post_status' => array( 'publish', 'in-progress' ),
			'numberposts' => -1
		) );

		$total_load = 0;
		foreach ( $work_orders as $wo ) {
			$total_load += (float) get_post_meta( $wo->ID, '_mep_estimated_time_mins', true );
		}

		return $total_load;
	}
}
