<?php
/**
 * MEP Master Data Importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Importer {

	/**
	 * Import Materials from CSV content.
	 *
	 * @param string $csv_content
	 * @return int Number of imported items.
	 */
	public static function import_materials( $csv_content ) {
		$lines = explode( "\n", $csv_content );
		$header = str_getcsv( array_shift( $lines ) );
		$count = 0;

		foreach ( $lines as $line ) {
			if ( empty( $line ) ) continue;
			$data = str_getcsv( $line );
			$item = array_combine( $header, $data );

			$post_id = wp_insert_post( array(
				'post_type'   => 'mep_material',
				'post_title'  => $item['Name'],
				'post_status' => 'publish'
			) );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_mep_sku', $item['SKU'] );
				update_post_meta( $post_id, '_mep_uom', $item['UOM'] );
				update_post_meta( $post_id, '_mep_cost_avg', (float) $item['Cost'] );
				$count++;
			}
		}

		return $count;
	}
}
