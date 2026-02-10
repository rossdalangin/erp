<?php
/**
 * MEP Database Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_DB {

	/**
	 * Create custom tables for the ERP.
	 */
	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Inventory Transactions Table
		$table_name = $wpdb->prefix . 'mep_inventory_transactions';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			material_id bigint(20) NOT NULL,
			warehouse_id bigint(20) NOT NULL,
			bin_id bigint(20) NOT NULL,
			quantity decimal(18,4) NOT NULL,
			transaction_type varchar(50) NOT NULL,
			reference_id bigint(20) DEFAULT NULL,
			lot_number varchar(100) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Audit Logs Table
		$table_name = $wpdb->prefix . 'mep_audit_logs';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			object_type varchar(50) NOT NULL,
			object_id bigint(20) NOT NULL,
			action varchar(50) NOT NULL,
			old_value longtext,
			new_value longtext,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Production Logs Table
		$table_name = $wpdb->prefix . 'mep_production_logs';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			work_order_id bigint(20) NOT NULL,
			operation_id bigint(20) NOT NULL,
			resource_id bigint(20) NOT NULL,
			start_time datetime DEFAULT NULL,
			end_time datetime DEFAULT NULL,
			output_qty decimal(18,4) DEFAULT 0,
			scrap_qty decimal(18,4) DEFAULT 0,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );

		// Stock Reservations Table
		$table_name = $wpdb->prefix . 'mep_stock_reservations';
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			material_id bigint(20) NOT NULL,
			work_order_id bigint(20) NOT NULL,
			quantity decimal(18,4) NOT NULL,
			status varchar(50) DEFAULT 'ACTIVE',
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );
	}

	/**
	 * Record an audit log entry.
	 */
	public static function log_audit( $object_type, $object_id, $action, $old_value = '', $new_value = '' ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'mep_audit_logs', array(
			'user_id'     => get_current_user_id(),
			'object_type' => $object_type,
			'object_id'   => $object_id,
			'action'      => $action,
			'old_value'   => is_scalar( $old_value ) ? $old_value : json_encode( $old_value ),
			'new_value'   => is_scalar( $new_value ) ? $new_value : json_encode( $new_value ),
		) );
	}
}
