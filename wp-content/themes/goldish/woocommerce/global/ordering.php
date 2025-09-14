<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ideapark_get_loop_display_mode() == 'subcategories' ) {
	return;
}
$active_filter_counts = '';
if ( ideapark_mod( '_with_filter_desktop' ) || ideapark_mod( '_with_filter' ) ) {
	$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
	$min_price          = isset( $_GET['min_price'] ) ? wp_unslash( $_GET['min_price'] ) : 0;
	$max_price          = isset( $_GET['max_price'] ) ? wp_unslash( $_GET['max_price'] ) : 0;
	$rating_filter      = isset( $_GET['rating_filter'] ) ? array_filter( array_map( 'absint', explode( ',', wp_unslash( $_GET['rating_filter'] ) ) ) ) : [];

	$active_filter_counts = count( $_chosen_attributes ) + ( $min_price > 0 ? 1 : 0 ) + ( $max_price > 0 ? 1 : 0 ) + ( ! empty( $rating_filter ) ? 1 : 0 );
}
?>
<div
	class="c-catalog-ordering l-section <?php ideapark_class( ideapark_mod( '_with_filter_desktop' ), 'c-catalog-ordering--desktop-filter', '' ); ?>">
	<div
		class="c-catalog-ordering__wrap <?php if ( ideapark_mod( 'product_grid_width' ) == 'boxed' ) { ?> l-section--container<?php } else { ?> l-section--container-wide<?php } ?>">
		<div class="c-catalog-ordering__col c-catalog-ordering__col--result">
			<?php woocommerce_result_count(); ?>
		</div>
		<div class="c-catalog-ordering__col c-catalog-ordering__col--ordering">
			<?php woocommerce_catalog_ordering(); ?>
		</div>
		<?php if ( ideapark_mod( '_with_filter_desktop' ) ) { ?>
			<button
				class="h-cb c-catalog-ordering__filter-show-button c-catalog-ordering__filter-show-button--desktop js-filter-show-button"
				type="button"><i
					class="ip-filter c-catalog-ordering__filter-ico"></i><?php esc_html_e( 'Filter', 'goldish' ); ?>
				<?php echo ideapark_wrap( $active_filter_counts ?: '', '<span class="c-catalog-ordering__filter-count">', '</span>' ); ?>
			</button>
		<?php } ?>

		<?php if ( ideapark_mod( '_with_filter' ) ) { ?>
			<button
				class="h-cb c-catalog-ordering__filter-show-button c-catalog-ordering__filter-show-button--mobile js-filter-show-button"
				type="button"><i
					class="ip-filter c-catalog-ordering__filter-ico"></i><?php esc_html_e( 'Filter', 'goldish' ); ?>
				<?php echo ideapark_wrap( $active_filter_counts ?: '', '<span class="c-catalog-ordering__filter-count">', '</span>' ); ?>
			</button>
		<?php } ?>
	</div>
</div>
<div class="c-catalog-ordering__line"></div>