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
			<label><?php _e( 'SKU:', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="text" name="mep_sku" value="<?php echo esc_attr( $sku ); ?>" class="widefat">
		</p>
		<p>
			<label><?php _e( 'UOM:', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="text" name="mep_uom" value="<?php echo esc_attr( $uom ); ?>" class="widefat">
		</p>
		<p>
			<label><?php _e( 'Average Cost ($):', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="number" step="0.01" name="mep_cost_avg" value="<?php echo esc_attr( $cost ); ?>" class="widefat">
		</p>
		<p>
			<label><?php _e( 'Safety Stock:', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="number" name="mep_safety_stock" value="<?php echo esc_attr( $safety ); ?>" class="widefat">
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
		?>
		<p>
			<label><?php _e( 'SKU:', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="text" name="mep_sku" value="<?php echo esc_attr( $sku ); ?>" class="widefat">
		</p>
		<?php
	}

	public static function render_work_order_meta( $post ) {
		$qty = get_post_meta( $post->ID, '_mep_work_order_qty', true );
		$due = get_post_meta( $post->ID, '_mep_due_date', true );
		$batch = get_post_meta( $post->ID, '_mep_batch_code', true );
		?>
		<p>
			<label><?php _e( 'Target Quantity:', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="number" name="mep_work_order_qty" value="<?php echo esc_attr( $qty ); ?>" class="widefat">
		</p>
		<p>
			<label><?php _e( 'Due Date:', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="date" name="mep_due_date" value="<?php echo esc_attr( $due ); ?>" class="widefat">
		</p>
		<p>
			<label><?php _e( 'Batch Code:', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="text" name="mep_batch_code" value="<?php echo esc_attr( $batch ); ?>" class="widefat">
		</p>
		<?php
	}

	public static function render_ncr_meta( $post ) {
		$type = get_post_meta( $post->ID, '_mep_ncr_type', true );
		$plan = get_post_meta( $post->ID, '_mep_capa_plan', true );
		$start = get_post_meta( $post->ID, '_mep_capa_start_date', true );
		?>
		<p>
			<label><?php _e( 'Defect Type / Description:', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="text" name="mep_ncr_type" value="<?php echo esc_attr( $type ); ?>" class="widefat">
		</p>
		<hr>
		<h4><?php _e( 'CAPA (Corrective and Preventive Action)', 'manufacturing-erp-pro' ); ?></h4>
		<p>
			<label><?php _e( 'Action Plan:', 'manufacturing-erp-pro' ); ?></label><br>
			<textarea name="mep_capa_plan" class="widefat" rows="4"><?php echo esc_textarea( $plan ); ?></textarea>
		</p>
		<p>
			<label><?php _e( 'CAPA Start Date:', 'manufacturing-erp-pro' ); ?></label><br>
			<input type="text" readonly value="<?php echo esc_attr( $start ); ?>" class="widefat">
		</p>
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
