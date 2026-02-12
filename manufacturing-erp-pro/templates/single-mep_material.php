<?php
/**
 * Template for single Material
 */

get_header();

while ( have_posts() ) :
	the_post();
	$material_id = get_the_ID();
	$sku = get_post_meta( $material_id, '_mep_sku', true );
	$uom = get_post_meta( $material_id, '_mep_uom', true );
	$stock = MEP_Inventory::get_stock_level( $material_id );
	$safety = (float) get_post_meta( $material_id, '_mep_safety_stock', true );
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'mep-single-view' ); ?>>
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header>

		<div class="entry-content">
			<div class="mep-inventory-box <?php echo ( $safety > 0 && $stock < $safety ) ? 'at-risk' : 'healthy'; ?>">
				<h3><?php _e( 'Inventory Status', 'manufacturing-erp-pro' ); ?></h3>
				<div class="stock-display">
					<span class="stock-label"><?php _e( 'Current On-Hand:', 'manufacturing-erp-pro' ); ?></span>
					<span class="stock-value"><?php echo number_format( $stock, 2 ); ?> <?php echo esc_html( $uom ); ?></span>
				</div>
				<?php if ( $safety > 0 && $stock < $safety ) : ?>
					<p class="warning-msg">⚠️ <?php _e( 'Stock level is below safety threshold!', 'manufacturing-erp-pro' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="mep-meta-box">
				<h3><?php _e( 'Material Details', 'manufacturing-erp-pro' ); ?></h3>
				<ul>
					<li><strong><?php _e( 'SKU:', 'manufacturing-erp-pro' ); ?></strong> <?php echo esc_html( $sku ); ?></li>
					<li><strong><?php _e( 'UOM:', 'manufacturing-erp-pro' ); ?></strong> <?php echo esc_html( $uom ); ?></li>
					<li><strong><?php _e( 'Average Cost:', 'manufacturing-erp-pro' ); ?></strong> $<?php echo number_format( (float) get_post_meta( $material_id, '_mep_cost_avg', true ), 2 ); ?></li>
				</ul>
			</div>

			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
