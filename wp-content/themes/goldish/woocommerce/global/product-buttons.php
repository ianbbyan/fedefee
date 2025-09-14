<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $product;
/**
 * @var $product WC_Product
 **/
if ( ideapark_mod( 'shop_modal' ) || ideapark_mod( 'wishlist_page' ) && ideapark_mod( 'wishlist_grid_button' ) ) { ?>
	<div class="c-product-grid__thumb-button-list <?php ideapark_class( ideapark_mod( 'product_grid_layout_mobile' ) != 'compact-mobile' && ideapark_mod( 'hide_add_to_cart_mobile_2_per_row' ) || ideapark_mod( 'product_grid_layout_mobile' ) == 'compact-mobile' && ideapark_mod( 'hide_add_to_cart_mobile_compact' ) , 'c-product-grid__thumb-button-list--hide-mobile' ); ?>">
		<?php if ( ideapark_mod( 'shop_modal' ) ) { ?>
			<button class="h-cb c-product-grid__thumb-button c-product-grid__thumb-button--quickview js-grid-zoom" type="button" data-lang="<?php echo esc_attr( ideapark_current_language() ); ?>" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="<?php esc_attr_e('Quick view', 'goldish') ; ?>">
				<i class="ip-eye c-product-grid__icon c-product-grid__icon--quickview"></i>
				<span class="c-product-grid__icon-text"><?php esc_html_e('Quick view', 'goldish') ; ?></span>
			</button>
		<?php } ?>
		<?php if ( ideapark_mod( 'wishlist_page' ) && ideapark_mod( 'wishlist_grid_button' ) ) { ?>
			<?php ideapark_wishlist()->ideapark__button( 'h-cb c-product-grid__thumb-button c-product-grid__thumb-button--wishlist', 'c-product-grid__icon c-product-grid__icon--wishlist', 'c-product-grid__icon-text c-product-grid__icon-text--' . ideapark_mod( 'product_buttons_layout' ), __( 'Add to Wishlist', 'goldish' ), __( 'Remove from Wishlist', 'goldish' )  ); ?>
		<?php } ?>
	</div>
<?php }
