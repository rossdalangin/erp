<?php
/**
 * MEP Meta Boxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MEP_Meta_Boxes {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ) );
	}

	public static function add_meta_boxes() {
		add_meta_box( 'mep_material_meta', __( 'Material Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_material_meta' ), 'mep_material', 'normal', 'high' );
		add_meta_box( 'mep_product_meta', __( 'Product Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_product_meta' ), 'mep_product', 'normal', 'high' );
		add_meta_box( 'mep_work_order_meta', __( 'Work Order Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_work_order_meta' ), 'mep_work_order', 'normal', 'high' );
		add_meta_box( 'mep_ncr_meta', __( 'NCR / CAPA Details', 'manufacturing-erp-pro' ), array( __CLASS__, 'render_ncr_meta' ), 'mep_ncr', 'normal', 'high' );
	}

	public static function render_material_meta( $post ) {
		$sku = get_post_meta( $post->ID, '_mep_sku', true );
		$uom = get_post_meta( $post->ID, '_mep_uom', true );
		$cost = get_post_meta( $post->ID, '_mep_cost_avg', true );
		$safety = get_post_meta( $post->ID, '_mep_safety_stock', true );
		?>
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
		<?php
	}

	public static function render_work_order_meta( $post ) {
		$qty = get_post_meta( $post->ID, '_mep_work_order_qty', true );
		$due = get_post_meta( $post->ID, '_mep_due_date', true );
		$batch = get_post_meta( $post->ID, '_mep_batch_code', true );
		?>
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
		);

		foreach ( $fields as $key => $meta ) {
			if ( isset( $_POST[$key] ) ) {
				update_post_meta( $post_id, $meta, sanitize_text_field( $_POST[$key] ) );
			}
		}

		if ( isset( $_POST['mep_promote_capa'] ) ) {
			MEP_Quality::promote_to_capa( $post_id, sanitize_textarea_field( $_POST['mep_capa_plan'] ) );
		}
	}
}
