<?php

/**
 * @var $ideapark_var array
 */


$show_breadcrumbs = empty( $ideapark_var['hide_breadcrumbs'] ) || $ideapark_var['hide_breadcrumbs'] != 'yes';
$show_title       = empty( $ideapark_var['hide_title'] ) || $ideapark_var['hide_title'] != 'yes';
$header_class_add = ! empty( $ideapark_var['header_class_add'] ) ? $ideapark_var['header_class_add'] : '';
$is_h1            = true;
$title            = isset( $ideapark_var['title'] ) ? esc_html( $ideapark_var['title'] ) : '';
$header_bg_size   = '';

if ( ideapark_is_shop() && ! $title ) {
	if ( is_search() ) {
		$title = esc_html__( 'Search:', 'goldish' ) . ' ' . esc_html( get_search_query( false ) );
	} else {
		$shop_page_id = wc_get_page_id( 'shop' );
		$title        = get_the_title( $shop_page_id );
	}

} elseif ( ideapark_woocommerce_on() && is_woocommerce() && ! $title ) {
	if ( is_product() ) {
		$is_h1 = false;
		$title = '';
	} else {
		$title = woocommerce_page_title( false );
	}

} elseif ( is_404() ) {
	$title = esc_html__( 'Page not found', 'goldish' );
} elseif ( is_single() ) {
	if ( ! $title ) {
		if ( ideapark_woocommerce_on() && is_product() ) {
			$is_h1 = false;
			$title = '';
		} else {
			$title = ( is_sticky() ? '<i class="ip-sticky c-page-header__sticky"><!-- --></i>' : '' ) . get_the_title();
		}
	}
} elseif ( is_search() && ! $title ) {
	$found_posts = $wp_query->found_posts;
	if ( $found_posts ) {
		$title = esc_html__( 'Search:', 'goldish' ) . ' ' . esc_html( get_search_query( false ) );
	} else {
		$title = esc_html__( 'No search results for:', 'goldish' ) . ' ' . esc_html( get_search_query( false ) );
	}
} elseif ( is_archive() ) {

	if ( ! $title ) {
		if ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			the_post();
			$title = get_the_author();
			rewind_posts();
		} elseif ( is_day() ) {
			$title = get_the_date();
		} elseif ( is_month() ) {
			$title = get_the_date( 'F Y' );
		} elseif ( is_year() ) {
			$title = get_the_date( 'Y' );
		} else {
			$queried_object = get_queried_object();
			$title          = esc_html__( 'Archives', 'goldish' );
		}
	}
} elseif ( is_home() && get_option( 'page_for_posts' ) && 'page' == get_option( 'show_on_front' ) && ! $title ) {
	$title = get_the_title( get_option( 'page_for_posts' ) );
} elseif ( is_front_page() && get_option( 'page_on_front' ) && 'page' == get_option( 'show_on_front' ) && ! $title ) {
	$title = get_the_title( get_option( 'page_on_front' ) );
} elseif ( is_home() && ! $title ) {
	$title = esc_html__( 'Posts', 'goldish' );
}

if ( ! $title && $is_h1 ) {
	$title = get_the_title();
}
?>
<?php ob_start(); ?>
<?php ob_start(); ?>
<?php if ( ideapark_woocommerce_on() && is_account_page() && is_user_logged_in() ) { ?>
	<div class="c-page-header__login-info">
		<?php global $current_user; ?>
		<span class="c-page-header__login-text">
					<?php echo sprintf( esc_attr__( 'Logged in as %s%s%s', 'goldish' ), '<span class="c-page-header__login-name">', esc_html( $current_user->display_name ), '</span>' ); ?>
				</span>
		<a class="c-page-header__logout"
		   href="<?php echo esc_url( function_exists( 'wc_logout_url' ) ? wc_logout_url() : wp_logout_url() ); ?>">
			<?php esc_html_e( 'Logout', 'goldish' ); ?><i class="ip-menu-right c-page-header__logout-icon"></i>
		</a>
	</div>
<?php } elseif ( $show_breadcrumbs && ! ( ideapark_woocommerce_on() && is_product() ) ) {
	get_template_part( 'templates/breadcrumbs' );
}
$title = trim( $title );
echo ideapark_wrap( trim( ob_get_clean() ), '<div class="c-page-header__top-row">', '</div>' ); ?>
<?php if ( $show_title && $title ) { ?>
	<?php if ( $is_h1 ) { ?>
		<h1 class="c-page-header__title"><?php echo ideapark_wrap( $title ); ?></h1>
	<?php } else { ?>
		<div
			class="c-page-header__title"><?php echo ideapark_wrap( $title ); ?></div>
	<?php } ?>
<?php } ?>
<?php $with_subcategories = ideapark_woocommerce_on() && ideapark_header_categories(); ?>
<?php echo ideapark_wrap( trim( ob_get_clean() ), '<header class="l-section c-page-header' . ( $header_class_add ? ' c-page-header--' . esc_attr( $header_class_add ) : '' ) . ( $with_subcategories ? ' c-page-header--sub-cat' : '' ) . ( ideapark_woocommerce_on() && is_product() ? ' c-page-header--product' : '' ) . ( ! $title && ! $with_subcategories ? ' c-page-header--no-border' : '' ) . '">', '</header>' ); ?>
