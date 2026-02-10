<?php
/**
 * MEP BOM & Routing Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_BOM {

	/**
	 * Get BOM tree for a product.
	 *
	 * @param int $product_id
	 * @param int $depth Current recursion depth.
	 * @return array
	 */
	public static function get_bom_tree( $product_id, $depth = 0 ) {
		if ( $depth > 10 ) return array(); // Prevent infinite loops

		$bom_posts = get_posts( array(
			'post_type'   => 'mep_bom',
			'post_parent' => $product_id,
			'post_status' => 'publish',
			'numberposts' => 1,
		) );

		if ( empty( $bom_posts ) ) {
			return array();
		}

		$bom_id = $bom_posts[0]->ID;
		$components = get_post_meta( $bom_id, '_mep_components', true );

		if ( ! is_array( $components ) ) {
			$components = array();
		}

		foreach ( $components as &$component ) {
			if ( $component['type'] === 'product' ) {
				$component['sub_bom'] = static::get_bom_tree( $component['id'], $depth + 1 );
			}
		}

		return $components;
	}

	/**
	 * Calculate recursive cost for a product based on BOM.
	 *
	 * @param int $product_id
	 * @return float
	 */
	public static function calculate_roll_up_cost( $product_id ) {
		$components = static::get_bom_tree( $product_id );
		$total_cost = 0;

		foreach ( $components as $component ) {
			$qty = (float) $component['qty'];
			$scrap = isset( $component['scrap'] ) ? (float) $component['scrap'] : 0;
			$adjusted_qty = $qty * ( 1 + $scrap );

			if ( $component['type'] === 'material' ) {
				$unit_cost = (float) get_post_meta( $component['id'], '_mep_cost_avg', true );
				$total_cost += $unit_cost * $adjusted_qty;
			} else {
				// Sub-assembly
				$unit_cost = static::calculate_roll_up_cost( $component['id'] );
				$total_cost += $unit_cost * $adjusted_qty;
			}
		}

		// Add Routing Costs
		$total_cost += static::get_routing_costs( $product_id );

		return $total_cost;
	}

	/**
	 * Get routing labor and machine costs.
	 */
	public static function get_routing_costs( $product_id ) {
		$route_posts = get_posts( array(
			'post_type'   => 'mep_route',
			'post_parent' => $product_id,
			'post_status' => 'publish',
			'numberposts' => 1,
		) );

		if ( empty( $route_posts ) ) return 0;

		$route_id = $route_posts[0]->ID;
		$steps = get_post_meta( $route_id, '_mep_steps', true );
		$cost = 0;

		if ( is_array( $steps ) ) {
			foreach ( $steps as $step ) {
				// Simplified: multiply time by a base rate
				$time = (float) $step['time']; // minutes
				$rate = 0.5; // $0.50 per minute base rate
				$cost += $time * $rate;
			}
		}

		return $cost;
	}
}
