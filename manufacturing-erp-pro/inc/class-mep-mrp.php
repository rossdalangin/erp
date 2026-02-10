<?php
/**
 * MEP MRP Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_MRP {

	/**
	 * Run the MRP Engine.
	 */
	public static function run() {
		$forecasts = get_posts( array(
			'post_type'   => 'mep_forecast',
			'post_status' => 'publish',
			'numberposts' => -1,
		) );

		$requirements = array();

		foreach ( $forecasts as $forecast ) {
			$product_id = $forecast->post_parent;
			$qty = (float) get_post_meta( $forecast->ID, '_mep_forecast_qty', true );

			// Explode BOM
			$exploded = static::explode_requirements( $product_id, $qty );
			foreach ( $exploded as $mat_id => $needed_qty ) {
				if ( ! isset( $requirements[$mat_id] ) ) {
					$requirements[$mat_id] = 0;
				}
				$requirements[$mat_id] += $needed_qty;
			}
		}

		// Netting
		$suggestions = array();
		foreach ( $requirements as $mat_id => $total_needed ) {
			$on_hand = MEP_Inventory::get_stock_level( $mat_id );
			$reserved = 0; // Placeholder for reservation logic

			$net_needed = $total_needed - ( $on_hand - $reserved );

			if ( $net_needed > 0 ) {
				$suggestions[] = array(
					'material_id' => $mat_id,
					'needed'      => $net_needed,
					'type'        => 'PURCHASE', // Or PRODUCTION if it's a sub-assembly
				);
			}
		}

		return $suggestions;
	}

	/**
	 * Recursively explode BOM to find raw material requirements.
	 */
	private static function explode_requirements( $product_id, $qty ) {
		$components = MEP_BOM::get_bom_tree( $product_id );
		$needed = array();

		foreach ( $components as $component ) {
			$comp_qty = (float) $component['qty'] * $qty;
			if ( $component['type'] === 'material' ) {
				if ( ! isset( $needed[$component['id']] ) ) {
					$needed[$component['id']] = 0;
				}
				$needed[$component['id']] += $comp_qty;
			} else {
				$sub_needed = static::explode_requirements( $component['id'], $comp_qty );
				foreach ( $sub_needed as $sub_id => $sub_qty ) {
					if ( ! isset( $needed[$sub_id] ) ) {
						$needed[$sub_id] = 0;
					}
					$needed[$sub_id] += $sub_qty;
				}
			}
		}

		return $needed;
	}

	/**
	 * Background Task Scaffolding using WP Cron.
	 */
	public static function schedule_mrp_run() {
		if ( ! wp_next_scheduled( 'mep_run_mrp_event' ) ) {
			wp_schedule_single_event( time(), 'mep_run_mrp_event' );
		}
	}
}

// Hook into the scheduled event
add_action( 'mep_run_mrp_event', array( 'MEP_MRP', 'run' ) );
