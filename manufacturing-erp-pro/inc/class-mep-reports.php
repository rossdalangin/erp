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
			'active_orders'     => $active_count
		);
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
