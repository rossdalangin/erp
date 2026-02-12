<?php
/**
 * Template for single Product
 */

get_header();

while ( have_posts() ) :
	the_post();
	$product_id = get_the_ID();
	$sku = get_post_meta( $product_id, '_mep_sku', true );
	$cost = MEP_BOM::calculate_roll_up_cost( $product_id );
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'mep-single-view' ); ?>>
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header>

		<div class="entry-content">
			<div class="mep-meta-box">
				<h3><?php _e( 'Product Specifications', 'manufacturing-erp-pro' ); ?></h3>
				<ul>
					<li><strong><?php _e( 'SKU:', 'manufacturing-erp-pro' ); ?></strong> <?php echo esc_html( $sku ); ?></li>
					<li><strong><?php _e( 'Estimated Unit Cost:', 'manufacturing-erp-pro' ); ?></strong> $<?php echo number_format( $cost, 2 ); ?></li>
					<li><strong><?php _e( 'Category:', 'manufacturing-erp-pro' ); ?></strong> <?php the_category( ', ' ); ?></li>
				</ul>
			</div>

			<div class="mep-bom-section">
				<h3><?php _e( 'Bill of Materials (BOM)', 'manufacturing-erp-pro' ); ?></h3>
				<?php
				$bom = MEP_BOM::get_bom_tree( $product_id );
				if ( ! empty( $bom ) ) :
					echo '<ul class="mep-bom-list">';
					foreach ( $bom as $item ) {
						echo '<li>' . esc_html( $item['name'] ) . ' - ' . esc_html( $item['qty'] ) . ' ' . ( isset($item['uom']) ? esc_html($item['uom']) : '' ) . '</li>';
					}
					echo '</ul>';
				else :
					echo '<p>' . __( 'No BOM defined for this product.', 'manufacturing-erp-pro' ) . '</p>';
				endif;
				?>
			</div>

			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
