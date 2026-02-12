<?php
/**
 * Template for single Work Order
 */

get_header();

while ( have_posts() ) :
	the_post();
	$wo_id = get_the_ID();
	$status = get_post_status( $wo_id );
	$qty = get_post_meta( $wo_id, '_mep_work_order_qty', true );
	$due = get_post_meta( $wo_id, '_mep_due_date', true );
	$product_id = get_post_field( 'post_parent', $wo_id );
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'mep-single-view' ); ?>>
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			<div class="mep-status-badge status-<?php echo esc_attr( $status ); ?>">
				<?php echo strtoupper( $status ); ?>
			</div>
		</header>

		<div class="entry-content">
			<div class="mep-wo-info">
				<h3><?php _e( 'Production Details', 'manufacturing-erp-pro' ); ?></h3>
				<ul>
					<li><strong><?php _e( 'Product:', 'manufacturing-erp-pro' ); ?></strong> <a href="<?php echo get_permalink( $product_id ); ?>"><?php echo get_the_title( $product_id ); ?></a></li>
					<li><strong><?php _e( 'Quantity:', 'manufacturing-erp-pro' ); ?></strong> <?php echo number_format( (float) $qty, 2 ); ?></li>
					<li><strong><?php _e( 'Due Date:', 'manufacturing-erp-pro' ); ?></strong> <?php echo esc_html( $due ?: 'Not set' ); ?></li>
					<li><strong><?php _e( 'Operator:', 'manufacturing-erp-pro' ); ?></strong> <?php the_author(); ?></li>
				</ul>
			</div>

			<div class="mep-wo-actions">
				<a href="<?php echo rest_url( 'mep/v1/work-orders/' . $wo_id . '/print' ); ?>?_wpnonce=<?php echo wp_create_nonce( 'wp_rest' ); ?>" class="button button-primary" target="_blank">
					<?php _e( 'Print Shop Traveler', 'manufacturing-erp-pro' ); ?>
				</a>
			</div>

			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
