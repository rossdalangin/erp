<?php
/**
 * MEP Meta Boxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Meta_Boxes {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_rest_meta' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ) );
	}

	public static function register_rest_meta() {
		$numeric_fields = array(
			'_mep_cost_avg', '_mep_safety_stock', '_mep_weight', '_mep_work_order_qty',
			'_mep_lead_time_avg', '_mep_credit_limit', '_mep_capacity', '_mep_supplier_id',
			'_mep_total_amount', '_mep_batch_size', '_mep_product_id', '_mep_forecast_qty',
			'_mep_customer_id', '_mep_daily_capacity_mins', '_mep_labor_rate',
			'_mep_lead_time', '_mep_price', '_mep_pack_size', '_mep_preferred_supplier',
			'_mep_route_id', '_mep_assigned_equipment_id', '_mep_actual_scrap',
			'_mep_actual_labor_mins', '_mep_moq', '_mep_sales_tax', '_mep_default_wh'
		);

		$array_fields = array(
			'_mep_components', '_mep_steps', '_mep_maintenance_logs'
		);

		$all_fields = array(
			'_mep_sku', '_mep_uom', '_mep_cost_avg', '_mep_safety_stock', '_mep_category',
			'_mep_weight', '_mep_work_order_qty', '_mep_due_date', '_mep_batch_code',
			'_mep_ncr_type', '_mep_capa_plan', '_mep_contact_name', '_mep_email',
			'_mep_phone', '_mep_lead_time_avg', '_mep_credit_limit', '_mep_location_code',
			'_mep_capacity', '_mep_supplier_id', '_mep_expected_date', '_mep_total_amount',
			'_mep_mfg_date', '_mep_expiry_date', '_mep_batch_size', '_mep_product_id',
			'_mep_forecast_qty', '_mep_customer_id', '_mep_daily_capacity_mins',
			'_mep_labor_rate', '_mep_qc_status', '_mep_lot_number', '_mep_qc_defects',
			'_mep_manufacturer_sku', '_mep_lead_time', '_mep_price', '_mep_pack_size',
			'_mep_preferred_supplier', '_mep_route_id', '_mep_assigned_equipment_id',
			'_mep_actual_scrap', '_mep_actual_labor_mins', '_mep_start_date',
			'_mep_components', '_mep_steps', '_mep_maintenance_logs', '_mep_version',
			'_mep_moq', '_mep_std_pkg', '_mep_sales_tax', '_mep_default_wh',
			'_mep_payment_terms', '_mep_carrier'
		);

		foreach ( $all_fields as $meta_key ) {
			$type = 'string';
			if ( in_array( $meta_key, $numeric_fields ) ) {
				$type = 'number';
			} elseif ( in_array( $meta_key, $array_fields ) ) {
				$type = 'array';
			}

			register_post_meta( '', $meta_key, array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => $type,
			) );
		}
	}

	public static function add_meta_boxes() {
		add_meta_box( 'mep_material_meta', __( 'Material Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_material_meta' ), 'mep_material', 'normal', 'high' );
		add_meta_box( 'mep_product_meta', __( 'Product Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_product_meta' ), 'mep_product', 'normal', 'high' );
		add_meta_box( 'mep_work_order_meta', __( 'Work Order Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_work_order_meta' ), 'mep_work_order', 'normal', 'high' );
		add_meta_box( 'mep_ncr_meta', __( 'NCR / CAPA Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_ncr_meta' ), 'mep_ncr', 'normal', 'high' );
		add_meta_box( 'mep_supplier_meta', __( 'Supplier Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_supplier_meta' ), 'mep_supplier', 'normal', 'high' );
		add_meta_box( 'mep_customer_meta', __( 'Customer Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_customer_meta' ), 'mep_customer', 'normal', 'high' );
		add_meta_box( 'mep_warehouse_meta', __( 'Warehouse Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_warehouse_meta' ), 'mep_warehouse', 'normal', 'high' );
		add_meta_box( 'mep_bin_meta', __( 'Bin Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_bin_meta' ), 'mep_bin', 'normal', 'high' );
		add_meta_box( 'mep_po_meta', __( 'Purchase Order Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_po_meta' ), 'mep_po', 'normal', 'high' );
		add_meta_box( 'mep_batch_meta', __( 'Production Batch Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_batch_meta' ), 'mep_batch', 'normal', 'high' );
		add_meta_box( 'mep_forecast_meta', __( 'Forecast Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_forecast_meta' ), 'mep_forecast', 'normal', 'high' );
		add_meta_box( 'mep_equipment_meta', __( 'Equipment Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_equipment_meta' ), 'mep_equipment', 'normal', 'high' );
		add_meta_box( 'mep_qc_check_meta', __( 'QC Inspection Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_qc_check_meta' ), 'mep_qc_check', 'normal', 'high' );
		add_meta_box( 'mep_bom_meta', __( 'BOM Information', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_bom_meta' ), 'mep_bom', 'normal', 'high' );
		add_meta_box( 'mep_route_meta', __( 'Route Information', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_route_meta' ), 'mep_route', 'normal', 'high' );
	}

	public static function render_material_meta( $post ) {
		$sku = get_post_meta( $post->ID, '_mep_sku', true );
		$uom = get_post_meta( $post->ID, '_mep_uom', true );
		$cost = get_post_meta( $post->ID, '_mep_cost_avg', true );
		$safety = get_post_meta( $post->ID, '_mep_safety_stock', true );
		$supplier_id = get_post_meta( $post->ID, '_mep_preferred_supplier', true );
		$suppliers = get_posts( array( 'post_type' => 'mep_supplier', 'numberposts' => -1 ) );
		?>
		<p>
			<label><strong><?php _e( 'Preferred Supplier:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<select name="mep_preferred_supplier" class="widefat">
				<option value=""><?php _e( '-- Select Supplier --', 'manufacturing-erp-pro' ); ?></option>
				<?php foreach ( $suppliers as $s ) : ?>
					<option value="<?php echo $s->ID; ?>" <?php selected( $supplier_id, $s->ID ); ?>><?php echo $s->post_title; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><strong><?php _e( 'SKU (Stock Keeping Unit):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_sku" value="<?php echo esc_attr( $sku ); ?>" class="widefat" placeholder="e.g., MAT-LTH-001">
			<small><?php _e( 'A unique identifier for this material.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Unit of Measure (UOM):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_uom" value="<?php echo esc_attr( $uom ); ?>" class="widefat" placeholder="e.g., m2, pcs, kg">
			<small><?php _e( 'How you measure this material (e.g., square meters for leather).', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Average Unit Cost ($):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" step="0.01" name="mep_cost_avg" value="<?php echo esc_attr( $cost ); ?>" class="widefat" placeholder="45.00">
			<small><?php _e( 'Used for real-time BOM cost roll-ups and inventory valuation.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Safety Stock Level:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_safety_stock" value="<?php echo esc_attr( $safety ); ?>" class="widefat" placeholder="100">
			<small><?php _e( 'The minimum quantity to keep on hand. MRP will flag items below this level.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Manufacturer SKU:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_manufacturer_sku" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mep_manufacturer_sku', true ) ); ?>" class="widefat" placeholder="e.g., LTH-GEN-123">
		</p>
		<p>
			<label><strong><?php _e( 'Lead Time (Days):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_lead_time" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mep_lead_time', true ) ); ?>" class="widefat" placeholder="14">
		</p>
		<p>
			<label><strong><?php _e( 'Minimum Order Quantity (MOQ):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_moq" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mep_moq', true ) ); ?>" class="widefat" placeholder="500">
		</p>
		<p>
			<label><strong><?php _e( 'Standard Packaging:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_std_pkg" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mep_std_pkg', true ) ); ?>" class="widefat" placeholder="e.g., Roll of 50m">
		</p>
		<hr>
		<h4><?php _e( 'Where Used (BOM Presence):', 'manufacturing-erp-pro' ); ?></h4>
		<?php
			$used_in = MEP_Admin_UI::get_instance()->get_where_material_is_used( $post->ID );
			if ( empty( $used_in ) ) {
				echo '<p><em>' . __( 'Not used in any active Bill of Materials.', 'manufacturing-erp-pro' ) . '</em></p>';
			} else {
				echo '<ul style="list-style:disc; margin-left:20px;">';
				foreach ( $used_in as $product_id ) {
					echo '<li><a href="' . get_edit_post_link( $product_id ) . '">' . get_the_title( $product_id ) . '</a></li>';
				}
				echo '</ul>';
			}
		?>
		<?php
	}

	public static function render_product_meta( $post ) {
		$sku = get_post_meta( $post->ID, '_mep_sku', true );
		$cat = get_post_meta( $post->ID, '_mep_category', true );
		$weight = get_post_meta( $post->ID, '_mep_weight', true );
		?>
		<p>
			<label><strong><?php _e( 'Product SKU:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_sku" value="<?php echo esc_attr( $sku ); ?>" class="widefat" placeholder="e.g., BAG-LTH-TAN">
			<small><?php _e( 'Unique identifier for this finished good.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Category:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_category" value="<?php echo esc_attr( $cat ); ?>" class="widefat" placeholder="e.g., Accessories, Footwear">
			<small><?php _e( 'Logical grouping for reporting.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Unit Weight (kg):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" step="0.001" name="mep_weight" value="<?php echo esc_attr( $weight ); ?>" class="widefat" placeholder="0.750">
			<small><?php _e( 'Physical weight of one finished unit.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Standard Selling Price ($):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" step="0.01" name="mep_price" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mep_price', true ) ); ?>" class="widefat" placeholder="299.00">
		</p>
		<p>
			<label><strong><?php _e( 'Pack Size:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_pack_size" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mep_pack_size', true ) ?: 1 ); ?>" class="widefat">
			<small><?php _e( 'Units per shipping carton.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Sales Tax Rate (%):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" step="0.01" name="mep_sales_tax" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mep_sales_tax', true ) ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Default Shipping Warehouse:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<?php $warehouses = get_posts( array( 'post_type' => 'mep_warehouse', 'numberposts' => -1 ) ); ?>
			<select name="mep_default_wh" class="widefat">
				<option value=""><?php _e( '-- Select --', 'manufacturing-erp-pro' ); ?></option>
				<?php foreach ( $warehouses as $wh ) : ?>
					<option value="<?php echo $wh->ID; ?>" <?php selected( get_post_meta( $post->ID, '_mep_default_wh', true ), $wh->ID ); ?>><?php echo $wh->post_title; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public static function render_work_order_meta( $post ) {
		$qty = get_post_meta( $post->ID, '_mep_work_order_qty', true );
		$due = get_post_meta( $post->ID, '_mep_due_date', true );
		$batch = get_post_meta( $post->ID, '_mep_batch_code', true );
		$route_id = get_post_meta( $post->ID, '_mep_route_id', true );
		$routes = get_posts( array( 'post_type' => 'mep_route', 'numberposts' => -1 ) );
		?>
		<p>
			<label><strong><?php _e( 'Production Route:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<select name="mep_route_id" class="widefat">
				<option value=""><?php _e( '-- Select Route --', 'manufacturing-erp-pro' ); ?></option>
				<?php foreach ( $routes as $r ) : ?>
					<option value="<?php echo $r->ID; ?>" <?php selected( $route_id, $r->ID ); ?>><?php echo $r->post_title; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><strong><?php _e( 'Target Production Quantity:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_work_order_qty" value="<?php echo esc_attr( $qty ); ?>" class="widefat" placeholder="50">
			<small><?php _e( 'How many units you intend to manufacture in this job.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Planned Due Date:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="date" name="mep_due_date" value="<?php echo esc_attr( $due ); ?>" class="widefat">
			<small><?php _e( 'Used to calculate the On-Time Delivery (OTD) KPI.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<p>
			<label><strong><?php _e( 'Batch / Lot Code:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_batch_code" value="<?php echo esc_attr( $batch ); ?>" class="widefat" placeholder="e.g., LOT-2024-001">
			<small><?php _e( 'Identifier for the entire production lot. Can be auto-generated during completion.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<?php
	}

	public static function render_supplier_meta( $post ) {
		$contact = get_post_meta( $post->ID, '_mep_contact_name', true );
		$email   = get_post_meta( $post->ID, '_mep_email', true );
		$phone   = get_post_meta( $post->ID, '_mep_phone', true );
		$lead    = get_post_meta( $post->ID, '_mep_lead_time_avg', true );
		?>
		<p>
			<label><strong><?php _e( 'Contact Name:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_contact_name" value="<?php echo esc_attr( $contact ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Email:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="email" name="mep_email" value="<?php echo esc_attr( $email ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Phone:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Average Lead Time (Days):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_lead_time_avg" value="<?php echo esc_attr( $lead ); ?>" class="widefat">
		</p>
		<?php
	}

	public static function render_customer_meta( $post ) {
		$contact = get_post_meta( $post->ID, '_mep_contact_name', true );
		$email   = get_post_meta( $post->ID, '_mep_email', true );
		$credit  = get_post_meta( $post->ID, '_mep_credit_limit', true );
		?>
		<p>
			<label><strong><?php _e( 'Primary Contact:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_contact_name" value="<?php echo esc_attr( $contact ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Email:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="email" name="mep_email" value="<?php echo esc_attr( $email ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Credit Limit ($):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_credit_limit" value="<?php echo esc_attr( $credit ); ?>" class="widefat">
		</p>
		<?php
	}

	public static function render_warehouse_meta( $post ) {
		$code = get_post_meta( $post->ID, '_mep_location_code', true );
		$capacity = get_post_meta( $post->ID, '_mep_capacity', true );
		?>
		<p>
			<label><strong><?php _e( 'Location Code:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_location_code" value="<?php echo esc_attr( $code ); ?>" class="widefat" placeholder="e.g., WH-01">
		</p>
		<p>
			<label><strong><?php _e( 'Total Storage Capacity (Units):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_capacity" value="<?php echo esc_attr( $capacity ); ?>" class="widefat">
		</p>
		<?php
	}

	public static function render_bin_meta( $post ) {
		$capacity = get_post_meta( $post->ID, '_mep_capacity', true );
		$warehouses = get_posts( array( 'post_type' => 'mep_warehouse', 'numberposts' => -1 ) );
		?>
		<p>
			<label><strong><?php _e( 'Parent Warehouse:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<select name="mep_parent_warehouse" class="widefat">
				<option value="0"><?php _e( '-- Select Warehouse --', 'manufacturing-erp-pro' ); ?></option>
				<?php foreach ( $warehouses as $wh ) : ?>
					<option value="<?php echo $wh->ID; ?>" <?php selected( $post->post_parent, $wh->ID ); ?>><?php echo $wh->post_title; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><strong><?php _e( 'Bin Capacity (Max Units):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_capacity" value="<?php echo esc_attr( $capacity ); ?>" class="widefat">
		</p>
		<?php
	}

	public static function render_po_meta( $post ) {
		$supplier_id = get_post_meta( $post->ID, '_mep_supplier_id', true );
		$expected    = get_post_meta( $post->ID, '_mep_expected_date', true );
		$total       = get_post_meta( $post->ID, '_mep_total_amount', true );

		$suppliers = get_posts( array( 'post_type' => 'mep_supplier', 'numberposts' => -1 ) );
		?>
		<p>
			<label><strong><?php _e( 'Supplier:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<select name="mep_supplier_id" class="widefat">
				<option value=""><?php _e( '-- Select Supplier --', 'manufacturing-erp-pro' ); ?></option>
				<?php foreach ( $suppliers as $s ) : ?>
					<option value="<?php echo $s->ID; ?>" <?php selected( $supplier_id, $s->ID ); ?>><?php echo $s->post_title; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><strong><?php _e( 'Expected Delivery Date:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="date" name="mep_expected_date" value="<?php echo esc_attr( $expected ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Total Amount ($):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" step="0.01" name="mep_total_amount" value="<?php echo esc_attr( $total ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Payment Terms:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_payment_terms" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mep_payment_terms', true ) ); ?>" class="widefat" placeholder="e.g., Net 30">
		</p>
		<p>
			<label><strong><?php _e( 'Preferred Carrier:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_carrier" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mep_carrier', true ) ); ?>" class="widefat" placeholder="e.g., DHL, FedEx">
		</p>
		<?php
	}

	public static function render_batch_meta( $post ) {
		$mfg_date = get_post_meta( $post->ID, '_mep_mfg_date', true );
		$expiry   = get_post_meta( $post->ID, '_mep_expiry_date', true );
		$size     = get_post_meta( $post->ID, '_mep_batch_size', true );
		$work_orders = get_posts( array( 'post_type' => 'mep_work_order', 'numberposts' => -1, 'post_status' => 'any' ) );
		?>
		<p>
			<label><strong><?php _e( 'Parent Work Order:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<select name="mep_parent_work_order" class="widefat">
				<option value="0"><?php _e( '-- Select Work Order --', 'manufacturing-erp-pro' ); ?></option>
				<?php foreach ( $work_orders as $wo ) : ?>
					<option value="<?php echo $wo->ID; ?>" <?php selected( $post->post_parent, $wo->ID ); ?>><?php echo $wo->post_title; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><strong><?php _e( 'Manufacturing Date:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="date" name="mep_mfg_date" value="<?php echo esc_attr( $mfg_date ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Expiry Date:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="date" name="mep_expiry_date" value="<?php echo esc_attr( $expiry ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Batch Size:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_batch_size" value="<?php echo esc_attr( $size ); ?>" class="widefat">
		</p>
		<?php
	}

	public static function render_forecast_meta( $post ) {
		$product_id  = get_post_meta( $post->ID, '_mep_product_id', true );
		$qty         = get_post_meta( $post->ID, '_mep_forecast_qty', true );
		$customer_id = get_post_meta( $post->ID, '_mep_customer_id', true );

		$products = get_posts( array( 'post_type' => 'mep_product', 'numberposts' => -1 ) );
		$customers = get_posts( array( 'post_type' => 'mep_customer', 'numberposts' => -1 ) );
		?>
		<p>
			<label><strong><?php _e( 'Product:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<select name="mep_product_id" class="widefat">
				<option value=""><?php _e( '-- Select Product --', 'manufacturing-erp-pro' ); ?></option>
				<?php foreach ( $products as $p ) : ?>
					<option value="<?php echo $p->ID; ?>" <?php selected( $product_id, $p->ID ); ?>><?php echo $p->post_title; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><strong><?php _e( 'Forecast Quantity:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_forecast_qty" value="<?php echo esc_attr( $qty ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Customer (Optional):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<select name="mep_customer_id" class="widefat">
				<option value=""><?php _e( '-- Select Customer --', 'manufacturing-erp-pro' ); ?></option>
				<?php foreach ( $customers as $c ) : ?>
					<option value="<?php echo $c->ID; ?>" <?php selected( $customer_id, $c->ID ); ?>><?php echo $c->post_title; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public static function render_equipment_meta( $post ) {
		$capacity = get_post_meta( $post->ID, '_mep_daily_capacity_mins', true );
		$rate     = get_post_meta( $post->ID, '_mep_labor_rate', true );
		?>
		<p>
			<label><strong><?php _e( 'Daily Capacity (Minutes):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" name="mep_daily_capacity_mins" value="<?php echo esc_attr( $capacity ); ?>" class="widefat" placeholder="480">
		</p>
		<p>
			<label><strong><?php _e( 'Labor Rate ($/min):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="number" step="0.01" name="mep_labor_rate" value="<?php echo esc_attr( $rate ); ?>" class="widefat" placeholder="0.50">
		</p>
		<?php
	}

	public static function render_bom_meta( $post ) {
		$version = get_post_meta( $post->ID, '_mep_version', true );
		?>
		<p>
			<label><strong><?php _e( 'BOM Version:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" readonly value="<?php echo esc_attr( $version ); ?>" class="widefat">
			<small><?php _e( 'Versions are managed via the Visual BOM Builder.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<?php
	}

	public static function render_route_meta( $post ) {
		$steps = get_post_meta( $post->ID, '_mep_steps', true );
		?>
		<p><strong><?php _e( 'Routing Steps:', 'manufacturing-erp-pro' ); ?></strong></p>
		<?php if ( is_array( $steps ) ) : ?>
			<ol>
				<?php foreach ( $steps as $step ) : ?>
					<li><?php echo esc_html( get_the_title( $step['work_center'] ) ); ?> - <?php echo esc_html( $step['time'] ); ?> mins</li>
				<?php endforeach; ?>
			</ol>
		<?php else : ?>
			<p><em><?php _e( 'No steps defined for this route.', 'manufacturing-erp-pro' ); ?></em></p>
		<?php endif; ?>
		<?php
	}

	public static function render_qc_check_meta( $post ) {
		$status = get_post_meta( $post->ID, '_mep_qc_status', true );
		$lot    = get_post_meta( $post->ID, '_mep_lot_number', true );
		$defects = get_post_meta( $post->ID, '_mep_qc_defects', true );
		?>
		<p>
			<label><strong><?php _e( 'QC Status:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<select name="mep_qc_status" class="widefat">
				<option value="PENDING" <?php selected( $status, 'PENDING' ); ?>>PENDING</option>
				<option value="PASS" <?php selected( $status, 'PASS' ); ?>>PASS</option>
				<option value="FAIL" <?php selected( $status, 'FAIL' ); ?>>FAIL</option>
			</select>
		</p>
		<p>
			<label><strong><?php _e( 'Lot Number:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_lot_number" value="<?php echo esc_attr( $lot ); ?>" class="widefat">
		</p>
		<p>
			<label><strong><?php _e( 'Defect Details (if failed):', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<textarea name="mep_qc_defects" class="widefat" rows="3"><?php echo esc_textarea( $defects ); ?></textarea>
		</p>
		<?php
	}

	public static function render_ncr_meta( $post ) {
		$type = get_post_meta( $post->ID, '_mep_ncr_type', true );
		$plan = get_post_meta( $post->ID, '_mep_capa_plan', true );
		$start = get_post_meta( $post->ID, '_mep_capa_start_date', true );
		?>
		<p>
			<label><strong><?php _e( 'Defect Nature:', 'manufacturing-erp-pro' ); ?></strong></label><br>
			<input type="text" name="mep_ncr_type" value="<?php echo esc_attr( $type ); ?>" class="widefat" placeholder="e.g., Critical surface scratch">
			<small><?php _e( 'Brief description of what went wrong during inspection.', 'manufacturing-erp-pro' ); ?></small>
		</p>
		<hr>
		<div style="background: #f0f6fb; padding: 15px; border-left: 4px solid #2271b1;">
			<h4>🛡️ <?php _e( 'CAPA (Corrective and Preventive Action)', 'manufacturing-erp-pro' ); ?></h4>
			<p><?php _e( 'Systemic issues should be promoted to CAPA to ensure the root cause is addressed and prevented in the future.', 'manufacturing-erp-pro' ); ?></p>
			<p>
				<label><strong><?php _e( 'Preventive Action Plan:', 'manufacturing-erp-pro' ); ?></strong></label><br>
				<textarea name="mep_capa_plan" class="widefat" rows="4" placeholder="<?php esc_attr_e( 'e.g., Update operator training for machine #3 and implement hourly nozzle checks.', 'manufacturing-erp-pro' ); ?>"><?php echo esc_textarea( $plan ); ?></textarea>
			</p>
			<p>
				<label><strong><?php _e( 'CAPA Initiation Date:', 'manufacturing-erp-pro' ); ?></strong></label><br>
				<input type="text" readonly value="<?php echo esc_attr( $start ); ?>" class="widefat">
			</p>
		</div>
		<p>
			<?php if ( $post->post_status !== 'capa-pending' ) : ?>
				<button type="submit" name="mep_promote_capa" value="1" class="button button-secondary">
					<?php _e( 'Promote to CAPA', 'manufacturing-erp-pro' ); ?>
				</button>
			<?php endif; ?>
		</p>
		<?php
	}

	public static function save_meta_boxes( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		$fields = array(
			'mep_sku' => '_mep_sku',
			'mep_uom' => '_mep_uom',
			'mep_cost_avg' => '_mep_cost_avg',
			'mep_safety_stock' => '_mep_safety_stock',
			'mep_category' => '_mep_category',
			'mep_weight' => '_mep_weight',
			'mep_work_order_qty' => '_mep_work_order_qty',
			'mep_due_date' => '_mep_due_date',
			'mep_batch_code' => '_mep_batch_code',
			'mep_ncr_type' => '_mep_ncr_type',
			'mep_capa_plan' => '_mep_capa_plan',
			'mep_contact_name' => '_mep_contact_name',
			'mep_email' => '_mep_email',
			'mep_phone' => '_mep_phone',
			'mep_lead_time_avg' => '_mep_lead_time_avg',
			'mep_credit_limit' => '_mep_credit_limit',
			'mep_location_code' => '_mep_location_code',
			'mep_capacity' => '_mep_capacity',
			'mep_supplier_id' => '_mep_supplier_id',
			'mep_expected_date' => '_mep_expected_date',
			'mep_total_amount' => '_mep_total_amount',
			'mep_mfg_date' => '_mep_mfg_date',
			'mep_expiry_date' => '_mep_expiry_date',
			'mep_batch_size' => '_mep_batch_size',
			'mep_product_id' => '_mep_product_id',
			'mep_forecast_qty' => '_mep_forecast_qty',
			'mep_customer_id' => '_mep_customer_id',
			'mep_daily_capacity_mins' => '_mep_daily_capacity_mins',
			'mep_labor_rate' => '_mep_labor_rate',
			'mep_qc_status' => '_mep_qc_status',
			'mep_lot_number' => '_mep_lot_number',
			'mep_qc_defects' => '_mep_qc_defects',
			'mep_manufacturer_sku' => '_mep_manufacturer_sku',
			'mep_lead_time' => '_mep_lead_time',
			'mep_price' => '_mep_price',
			'mep_pack_size' => '_mep_pack_size',
			'mep_preferred_supplier' => '_mep_preferred_supplier',
			'mep_route_id' => '_mep_route_id',
			'mep_moq' => '_mep_moq',
			'mep_std_pkg' => '_mep_std_pkg',
			'mep_sales_tax' => '_mep_sales_tax',
			'mep_default_wh' => '_mep_default_wh',
			'mep_payment_terms' => '_mep_payment_terms',
			'mep_carrier' => '_mep_carrier',
		);

		foreach ( $fields as $key => $meta ) {
			if ( isset( $_POST[$key] ) ) {
				update_post_meta( $post_id, $meta, sanitize_text_field( $_POST[$key] ) );
			}
		}

		if ( isset( $_POST['mep_parent_warehouse'] ) ) {
			wp_update_post( array( 'ID' => $post_id, 'post_parent' => intval( $_POST['mep_parent_warehouse'] ) ) );
		}
		if ( isset( $_POST['mep_parent_work_order'] ) ) {
			wp_update_post( array( 'ID' => $post_id, 'post_parent' => intval( $_POST['mep_parent_work_order'] ) ) );
		}

		if ( isset( $_POST['mep_promote_capa'] ) ) {
			MEP_Quality::promote_to_capa( $post_id, sanitize_textarea_field( $_POST['mep_capa_plan'] ) );
		}
	}
}
