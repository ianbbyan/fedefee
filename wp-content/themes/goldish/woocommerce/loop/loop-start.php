<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see           https://docs.woocommerce.com/document/template-structure/
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version        10.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$loop_name = wc_get_loop_prop( 'name' );

if ( ! ( $layout = ideapark_mod( '_product_layout' ) ) ) {
	$layout = ideapark_mod( 'product_grid_layout' );
}

if ( ! ( $layout_mobile = ideapark_mod( '_product_layout_mobile' ) ) ) {
	$layout_mobile = ideapark_mod( 'product_grid_layout_mobile' );
}

if ( ! ( $layout_width = ideapark_mod( '_product_layout_width' ) ) ) {
	$layout_width = ideapark_mod( 'product_grid_width' );
}

if ( $loop_name == 'recently' && ideapark_mod( 'recently_grid_layout' ) != 'default' ) {
	$layout = ideapark_mod( 'recently_grid_layout' );
}

ideapark_mod_set_temp( '_product_short_description', ideapark_mod( 'product_short_description' ) );

if ( $loop_name == 'recently' && ideapark_mod( 'hide_recently_short_description' )
     || in_array( $loop_name, [
		'up-sells',
		'cross-sells',
		'related'
	] ) && ideapark_mod( 'hide_related_short_description' ) ) {
	ideapark_mod_set_temp( 'product_short_description', false );
}

if ( in_array( $loop_name, [
		'up-sells',
		'cross-sells',
		'related'
	] ) && ideapark_mod( 'related_grid_layout' ) != 'default' ) {
	$layout = ideapark_mod( 'related_grid_layout' );
}

if ( $layout == 'compact' ) {
	$layout_buttons = 'buttons-c';
} else {
	if ( ! ( $layout_buttons = ideapark_mod( '_product_layout_buttons' ) ) ) {
		$layout_buttons = ideapark_mod( 'product_buttons_layout' );
	}
}

if ( ! ( $layout_buttons_mobile = ideapark_mod( '_product_layout_buttons_mobile' ) ) ) {
	$layout_buttons_mobile = ideapark_mod( 'product_buttons_layout' );
}

ideapark_mod_set_temp( '_product_layout', $layout );
ideapark_mod_set_temp( '_product_layout_width', $layout_width );
ideapark_mod_set_temp( '_product_layout_mobile', $layout_mobile );
ideapark_mod_set_temp( '_product_layout_buttons', $layout_buttons );
ideapark_mod_set_temp( '_product_layout_buttons_mobile', $layout_buttons_mobile );
ideapark_mod_set_temp( '_product_layout_class',
	'c-product-grid__item--' . $layout .
	' c-product-grid__item--' . $layout_mobile .
	( $layout_mobile !== 'compact-mobile' ? ' c-product-grid__item--not-compact-mobile' : '' ) .
	' c-product-grid__item--' . $layout_buttons .
	' c-product-grid__item--' . $layout_buttons_mobile . '-mobile' .
	ideapark_grid_text_class( 'c-product-grid__item' ) );
ideapark_mod_set_temp( '_is_product_loop', true );
$count = 4;
if ( $loop_name ) {
	switch ( $loop_name ) {
		case 'recently':
		case 'related':
		case 'cross-sells':
		case 'up-sells':
			$count = ideapark_mod( '_products_count' ) ?: $count;
			break;
		default:
			$count = wc_get_loop_prop( 'total' );
			break;
	}
}

if ( ideapark_mod( '_extra_items_count' ) ) {
	$count += ideapark_mod( '_extra_items_count' );
	ideapark_mod_set_temp( '_extra_items_count', 0 );
}

?>
<?php if ( ! ideapark_mod( '_hide_grid_wrapper' ) ) { ?>
<div
	class="c-product-grid__wrap c-product-grid__wrap--<?php echo esc_attr( $layout ); ?> c-product-grid__wrap--<?php echo esc_attr( $layout_mobile ); ?> c-product-grid__wrap--<?php echo esc_attr( $layout_width ); ?> c-product-grid__wrap--cnt-<?php echo esc_attr( $count ); ?>">
	<div
		class="c-product-grid__list c-product-grid__list--<?php echo esc_attr( $layout ); ?> c-product-grid__list--<?php echo esc_attr( $layout_width ); ?> c-product-grid__list--<?php echo esc_attr( $layout_mobile ); ?> <?php echo ideapark_grid_text_class( 'c-product-grid__list' ); ?> <?php ideapark_class( ideapark_mod( '_with_sidebar' ), 'c-product-grid__list--sidebar', '' ); ?> <?php echo ideapark_mod( '_product_carousel_class' ); ?>" <?php echo ideapark_mod( '_product_carousel_data' ); ?>
		data-count="<?php echo esc_attr( $count ); ?>"
		data-layout="<?php echo ideapark_mod( '_product_layout' ); ?>"
		data-layout-width="<?php echo ideapark_mod( '_product_layout_width' ); ?>"
		data-layout-mobile="<?php echo ideapark_mod( '_product_layout_mobile' ); ?>">
		<?php } ?>
		<!-- grid-start -->