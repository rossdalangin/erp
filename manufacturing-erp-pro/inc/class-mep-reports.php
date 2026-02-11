<?php
/**
 * MEP Reporting & KPI Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Reports {

	/**
	 * Get ERP performance KPIs.
	 */
	public static function get_kpis() {
		global $wpdb;

		// 1. Production Output & Scrap Rate
		$prod_table = $wpdb->prefix . 'mep_production_logs';
		$prod_stats = $wpdb->get_row( "SELECT SUM(output_qty) as total_output, SUM(scrap_qty) as total_scrap FROM $prod_table" );

		$output = $prod_stats->total_output ? (float) $prod_stats->total_output : 0;
		$scrap  = $prod_stats->total_scrap ? (float) $prod_stats->total_scrap : 0;
		$scrap_rate = $output > 0 ? round( ( $scrap / ( $output + $scrap ) ) * 100, 1 ) . '%' : '0%';

		// 2. Inventory Value
		$inventory_value = 0;
		$materials = get_posts( array( 'post_type' => 'mep_material', 'numberposts' => -1 ) );
		foreach ( $materials as $mat ) {
			$stock = MEP_Inventory::get_stock_level( $mat->ID );
			$cost  = (float) get_post_meta( $mat->ID, '_mep_cost_avg', true );
			$inventory_value += ( $stock * $cost );
		}

		// 3. Active Orders
		$active_orders = wp_count_posts( 'mep_work_order' );
		$active_count  = (int) $active_orders->publish + (int) $active_orders->{'in-progress'};

		return array(
			'production_output' => $output,
			'scrap_rate'        => $scrap_rate,
			'inventory_value'   => '$' . number_format( $inventory_value, 2 ),
			'on_time_delivery'  => '94%', // Placeholder for complex logic
			'active_orders'     => $active_count,
			'valuation_fifo'    => '$' . number_format( static::get_fifo_valuation(), 2 ),
			'cost_variance'     => static::get_cost_variance(),
			'at_risk_materials' => static::get_at_risk_count(),
			'inventory_aging'   => static::get_inventory_aging()
		);
	}

	/**
	 * Calculate Inventory Aging (Stock grouped by receipt date).
	 */
	public static function get_inventory_aging() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_inventory_transactions';

		$aging = array(
			'0-30 days'  => 0,
			'31-60 days' => 0,
			'61-90 days' => 0,
			'90+ days'   => 0
		);

		$materials = get_posts( array( 'post_type' => 'mep_material', 'numberposts' => -1 ) );
		foreach ( $materials as $mat ) {
			$on_hand = MEP_Inventory::get_stock_level( $mat->ID );
			if ( $on_hand <= 0 ) continue;

			$receipts = $wpdb->get_results( $wpdb->prepare(
				"SELECT quantity, created_at FROM $table_name
				 WHERE material_id = %d AND transaction_type = 'RECEIVE'
				 ORDER BY created_at DESC",
				$mat->ID
			) );

			$remaining = $on_hand;
			foreach ( $receipts as $receipt ) {
				$qty = (float) $receipt->quantity;
				$take = min( $qty, $remaining );

				$days_old = round( ( time() - strtotime( $receipt->created_at ) ) / ( 60 * 60 * 24 ) );

				if ( $days_old <= 30 ) $aging['0-30 days'] += $take;
				elseif ( $days_old <= 60 ) $aging['31-60 days'] += $take;
				elseif ( $days_old <= 90 ) $aging['61-90 days'] += $take;
				else $aging['90+ days'] += $take;

				$remaining -= $take;
				if ( $remaining <= 0 ) break;
			}

			if ( $remaining > 0 ) {
				$aging['90+ days'] += $remaining; // Assume initial stock is old
			}
		}

		return $aging;
	}

	/**
	 * Count materials below safety stock.
	 */
	public static function get_at_risk_count() {
		$materials = get_posts( array( 'post_type' => 'mep_material', 'numberposts' => -1 ) );
		$at_risk_count = 0;
		foreach ( $materials as $mat ) {
			$stock = MEP_Inventory::get_stock_level( $mat->ID );
			$safety = (float) get_post_meta( $mat->ID, '_mep_safety_stock', true );
			if ( $safety > 0 && $stock < $safety ) {
				$at_risk_count++;
			}
		}
		return $at_risk_count;
	}

	/**
	 * Calculate Cost Variance (Estimated vs Actual).
	 */
	public static function get_cost_variance() {
		global $wpdb;
		$logs_table = $wpdb->prefix . 'mep_production_logs';
		$logs = $wpdb->get_results( "SELECT * FROM $logs_table ORDER BY created_at DESC LIMIT 10" );

		$variances = array();
		foreach ( $logs as $log ) {
			$wo_id = $log->work_order_id;
			$product_id = get_post_field( 'post_parent', $wo_id );

			$estimated_unit_cost = MEP_BOM::calculate_roll_up_cost( $product_id );
			$qty = (float) $log->output_qty;

			$total_estimated = $estimated_unit_cost * $qty;

			// Actual: materials (from BOM but with actual scrap) + actual labor
			// Simplified actual calculation for demo
			$actual_scrap = (float) $log->scrap_qty;
			$actual_labor = (float) $log->labor_mins * 0.5; // $0.50/min rate

			$actual_cost = $total_estimated + ( $actual_scrap * 10 ) + $actual_labor; // Assume $10 avg material cost for scrap

			$variances[] = array(
				'wo_id'     => $wo_id,
				'product'   => get_the_title( $product_id ),
				'estimated' => round( $total_estimated, 2 ),
				'actual'    => round( $actual_cost, 2 ),
				'variance'  => round( $actual_cost - $total_estimated, 2 )
			);
		}

		return $variances;
	}

	/**
	 * Calculate Inventory Valuation using FIFO.
	 */
	public static function get_fifo_valuation() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mep_inventory_transactions';
		$materials = get_posts( array( 'post_type' => 'mep_material', 'numberposts' => -1 ) );
		$total_value = 0;

		foreach ( $materials as $mat ) {
			$on_hand = MEP_Inventory::get_stock_level( $mat->ID );
			if ( $on_hand <= 0 ) continue;

			// Get recent receipts in reverse order to find the cost of remaining items
			$receipts = $wpdb->get_results( $wpdb->prepare(
				"SELECT quantity, reference_id FROM $table_name
				 WHERE material_id = %d AND transaction_type = 'RECEIVE'
				 ORDER BY created_at DESC",
				$mat->ID
			) );

			$remaining = $on_hand;
			foreach ( $receipts as $receipt ) {
				$qty = (float) $receipt->quantity;
				$take = min( $qty, $remaining );

				// Get cost at time of receipt (from PO or current avg if PO missing)
				$unit_cost = 0;
				if ( $receipt->reference_id ) {
					// Logic to get cost from PO line
					$unit_cost = (float) get_post_meta( $receipt->reference_id, '_mep_unit_cost', true );
				}
				if ( ! $unit_cost ) {
					$unit_cost = (float) get_post_meta( $mat->ID, '_mep_cost_avg', true );
				}

				$total_value += ( $take * $unit_cost );
				$remaining -= $take;
				if ( $remaining <= 0 ) break;
			}

			// If we still have remaining (e.g. from initial balance), use avg cost
			if ( $remaining > 0 ) {
				$total_value += ( $remaining * (float) get_post_meta( $mat->ID, '_mep_cost_avg', true ) );
			}
		}

		return $total_value;
	}

	/**
	 * Generate Inventory CSV data and send to output.
	 */
	public static function export_inventory_csv() {
		$materials = get_posts( array( 'post_type' => 'mep_material', 'numberposts' => -1 ) );

		$fp = fopen( 'php://output', 'w' );
		fputcsv( $fp, array( 'SKU', 'Name', 'UOM', 'Stock Level', 'Avg Cost', 'Total Value' ) );

		foreach ( $materials as $mat ) {
			$stock = MEP_Inventory::get_stock_level( $mat->ID );
			$cost  = (float) get_post_meta( $mat->ID, '_mep_cost_avg', true );

			fputcsv( $fp, array(
				get_post_meta( $mat->ID, '_mep_sku', true ),
				$mat->post_title,
				get_post_meta( $mat->ID, '_mep_uom', true ),
				$stock,
				$cost,
				$stock * $cost
			) );
		}

		fclose( $fp );
	}
}
