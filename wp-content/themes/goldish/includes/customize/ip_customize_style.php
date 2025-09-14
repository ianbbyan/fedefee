<?php

function ideapark_theme_colors() {
	return [
		'background_color'        => $bg_color = ideapark_mod_hex_color_norm( 'background_color', '#ffffff' ),
		'text_color'              => $text_color = ideapark_mod_hex_color_norm( 'text_color', '#000000' ),
		'text_color_body'         => ideapark_hex_to_rgb_overlay( $bg_color, $text_color, 0.8 ),
		'text_color_light'        => ideapark_hex_to_rgb_overlay( $bg_color, $text_color, 0.646 ),
		'text_color_med_light'    => ideapark_hex_to_rgb_overlay( $bg_color, $text_color, 0.38 ),
		'text_color_extra_light'  => ideapark_hex_to_rgb_overlay( $bg_color, $text_color, 0.13 ),
		'accent_color'            => ideapark_mod_hex_color_norm( 'accent_color', '#C6AD8A' ),
		'accent_background_color' => ideapark_mod_hex_color_norm( 'accent_background_color', '#FDF9F2' ),
	];
}

function ideapark_customize_css( $is_return_value = false ) {

	$custom_css = '';

	/**
	 * @var $text_color                       string
	 * @var $text_color_body                  string
	 * @var $text_color_light                 string
	 * @var $text_color_med_light             string
	 * @var $text_color_extra_light           string
	 * @var $background_color                 string
	 * @var $accent_color                     string
	 * @var $accent_background_color          string
	 */
	extract( ideapark_theme_colors() );

	$text_color_lighting = ideapark_hex_lighting( $text_color );

	$lang_postfix = ideapark_get_lang_postfix();
	$font_text    = preg_replace( '~^(custom-|system-)~', '',  (string) ( ideapark_mod( 'theme_font_text' . $lang_postfix ) ?: ideapark_mod( 'theme_font_text' ) ) );

	$custom_css .= '
	<style> 
		:root {
			--text-color: ' . esc_attr( $text_color ) . ';
			--text-color-body: ' . esc_attr( $text_color_body ) . ';
			--text-color-light: ' . esc_attr( $text_color_light ) . ';
			--text-color-med-light: ' . esc_attr( $text_color_med_light ) . ';
			--text-color-extra-light: ' . esc_attr( $text_color_extra_light ) . ';
			--text-color-tr: ' . ideapark_hex_to_rgba( $text_color, 0.15 ) . ';
			--text-color-tr-50: ' . ideapark_hex_to_rgba( $text_color, 0.5 ) . ';
			--background-color: ' . esc_attr( $background_color ) . ';
			--background-color-light: ' . ideapark_hex_to_rgb_overlay( '#FFFFFF', $background_color, 0.5 ) . ';
			--background-color-dark: ' . ideapark_hex_to_rgb_overlay( $background_color, '#000000', 0.03 ) . ';
			--accent-color: ' . esc_attr( $accent_color ) . ';
			--accent-color-dark: ' . ideapark_hex_to_rgb_overlay( $accent_color, '#000000', 0.1 ) . ';
			--accent-background-color: ' . esc_attr( $accent_background_color ) . ';
			
			--accent-button-color: ' . esc_attr( $text_color_lighting > 128 ? '#000000' : '#FFFFFF' ) . ';
			--accent-button-background-color: ' . esc_attr( $text_color_lighting > 128 ? $text_color : $accent_color ) . ';
			--accent-button-hover-background-color: ' . esc_attr( $text_color_lighting > 128 ? $accent_color : $text_color ) . ';
			
			--white-color: ' . esc_attr( $text_color_lighting > 128 ? '#000000' : '#FFFFFF' ) . ';
			--smart-color: ' . esc_attr( $text_color_lighting > 128 ? $background_color : $text_color ) . ';
			--star-rating-color: ' . ideapark_mod_hex_color_norm( 'star_rating_color', $text_color ) . ';
			--font-text: "' . esc_attr( $font_text ) . '", sans-serif;
			--font-icons: "theme-icons";
			--text-transform: ' . ( ideapark_mod( 'capitalize_headers' ) ? 'capitalize' : 'none' ) . ';
			--logo-size: ' . esc_attr( (int) ( ideapark_mod( 'logo_size' ) ) ) . 'px;
			--logo-size-sticky: ' . esc_attr( (int) ( (int) ideapark_mod( 'sticky_logo_desktop_size' ) ?: ideapark_mod( 'logo_size' ) ) ) . 'px;
			--logo-size-mobile: ' . esc_attr( (int) ( ideapark_mod( 'logo_size_mobile' ) ) ) . 'px;
			--shadow-color-desktop: ' . ideapark_hex_to_rgba( ideapark_mod_hex_color_norm( 'shadow_color_desktop', '#FFFFFF' ), 0.95 ) . ';
			--search-color-desktop: ' . esc_attr( ideapark_hex_lighting( ideapark_mod_hex_color_norm( 'shadow_color_desktop', '#FFFFFF' ) ) > 128 ? ( $text_color_lighting > 128 ? $background_color : $text_color ) : ( $text_color_lighting > 128 ? $text_color : $background_color ) ) . ';
			--text-align-left: ' . ( ideapark_is_rtl() ? 'right' : 'left' ) . ';
			--text-align-right: ' . ( ideapark_is_rtl() ? 'left' : 'right' ) . ';
			--custom-transform-transition: visibility 0.5s cubic-bezier(0.86, 0, 0.07, 1), opacity 0.5s cubic-bezier(0.86, 0, 0.07, 1), transform 0.5s cubic-bezier(0.86, 0, 0.07, 1), box-shadow 0.5s cubic-bezier(0.86, 0, 0.07, 1);
			--opacity-transition: opacity 0.3s linear, visibility 0.3s linear;
			--opacity-transform-transition: opacity 0.3s linear, visibility 0.3s linear, transform 0.3s ease-out, box-shadow 0.3s ease-out;
			--hover-transition: opacity 0.15s linear, visibility 0.15s linear, color 0.15s linear, border-color 0.15s linear, background-color 0.15s linear, box-shadow 0.15s linear;
			--star-rating-image: url("data:image/svg+xml;base64,' . ideapark_b64enc( '<svg width="14" height="10" fill="' . ideapark_mod_hex_color_norm( 'star_rating_color', $text_color ) . '" xmlns="http://www.w3.org/2000/svg"><path d="M8.956 9.782c.05.153-.132.28-.27.186L5.5 7.798l-3.19 2.168c-.137.093-.32-.033-.269-.187l1.178-3.563L.07 3.99c-.135-.095-.065-.3.103-.302l3.916-.032L5.335.114c.053-.152.28-.152.333 0L6.91 3.658l3.916.035c.168.001.238.206.103.302L7.78 6.217l1.175 3.565z"/></svg>' ) . '");
			--select-image: url("data:image/svg+xml;base64,' . ideapark_b64enc( '<svg width="10" height="7" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M.47 1.53 1.53.47 5 3.94 8.47.47l1.06 1.06L5 6.06.47 1.53z" fill="' . $text_color . '"/></svg>' ) . '");
			--select-ordering-image: url("data:image/svg+xml;base64,' . ideapark_b64enc( '<svg width="6" height="4" viewBox="0 0 6 4" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 2.551.633.184 0 .816l3 3 3-3-.633-.632L3 2.55Z" fill="' . $text_color_light . '"/></svg>' ) . '");
			--reset-image: url("data:image/svg+xml;base64,' . ideapark_b64enc( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="' . $text_color . '"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>' ) . '");
			--image-grid-prop: ' . (int) ( ideapark_mod( 'grid_image_prop' ) * 100 ) . '%;
			--image-product-prop: ' . (int) ( ideapark_mod( 'product_image_prop' ) * 100 ) . '%;
			--image-product-prop-qv-mobile: ' . round( (float) ideapark_mod( 'product_image_prop' ) * ( ideapark_mod( 'product_image_prop' ) >= 0.8 ? 88 : 100 ) ) . '%;
			--image-product-aspect-ratio: 100 / ' . (int) ( ideapark_mod( 'product_image_prop' ) * 100 ) . ';
			
			--icon-divider: "\f110" /* ip-divider */;
			--icon-zoom: "\f145" /* ip-plus-zoom */;
			--icon-user: "\f158" /* ip-user */;
			--icon-close-small: "\f10d" /* ip-close-small */;
			--icon-check: "\f10b" /* ip-check */;
			--icon-select: "\f113" /* ip-down_arrow */;
			--icon-select-bold: "\f112" /* ip-down */;
			--icon-romb: "\f14d" /* ip-romb */;
			--icon-calendar: "\f106" /* ip-calendar */;
			--icon-li: "\f111" /* ip-dot */;
			--icon-submenu: "\f12b" /* ip-menu-right */;
			--icon-depth: "\f165" /* ip-z-depth */;
			--icon-eye-back: "\f115" /* ip-eye-back */;
			--icon-heart-back: "\f11c" /* ip-heart-active */;
			
			--image-background-color: ' . esc_attr( ideapark_mod_hex_color_norm( 'product_image_background_color', 'transparent' ) ) . ';
			
			--fullwidth-limit: ' . ( ideapark_mod( 'limit_fullwidth_1920' ) ? '1920px' : '100%' ) . ';
			
			--container-default-padding-block-start: 0px;
			--container-default-padding-block-end: 0px;
			--container-default-padding-inline-start: 0px;
			--container-default-padding-inline-end: 0px;
			
			--li-image: url("data:image/svg+xml;base64,' . ideapark_b64enc( '<svg width="7" height="8" viewBox="0 0 7 8" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.6.2a1 1 0 0 1 .2 1.4L3.052 6.597.241 3.317a1 1 0 0 1 1.518-1.301l1.189 1.387L5.2.4A1 1 0 0 1 6.6.2Z" fill="' . $accent_color . '"/></svg>' ) . '");
		}
		
		.woobt-wrap:before {
			content: "' . esc_html__( 'Frequently Bought Together', 'goldish' ) . '";
		}
		
		.owl-nav {
			--disable-color: ' . ideapark_hex_to_rgba( $text_color, 0.2 ) . ';
		}
		
		.c-badge {
			--badge-bgcolor-featured: ' . ideapark_mod_hex_color_norm( 'featured_badge_color', $text_color ) . ';
			--badge-bgcolor-new: ' . ideapark_mod_hex_color_norm( 'new_badge_color', $text_color ) . ';
			--badge-bgcolor-sale: ' . ideapark_mod_hex_color_norm( 'sale_badge_color', $text_color ) . ';
			--badge-bgcolor-outofstock: ' . ideapark_mod_hex_color_norm( 'outofstock_badge_color', $text_color ) . ';
		}
		
		.c-to-top-button {
			--to-top-button-color: ' . ideapark_mod_hex_color_norm( 'to_top_button_color' ) . ';
		}
		
		.c-top-menu__list {
			--top-menu-submenu-color: ' . ideapark_mod_hex_color_norm( 'top_menu_submenu_color', $text_color ) . ';
			--top-menu-submenu-bg-color: ' . ideapark_mod_hex_color_norm( 'top_menu_submenu_bg_color', '#FFFFFF' ) . ';
			--top_menu_submenu_accent_color: ' . ideapark_mod_hex_color_norm( 'top_menu_submenu_accent_color', $accent_color ) . ';
			--top-menu-font-size: ' . esc_attr( (int) ideapark_mod( 'top_menu_font_size' ) ) . 'px;
			--top-menu-item-space: ' . esc_attr( (int) ideapark_mod( 'top_menu_item_space' ) ) . 'px; 
		}
		
		.c-product-grid__item {
			--font-size: ' . ideapark_mod( 'product_font_size' ) . 'px;
			--font-size-mobile-1-rows: ' . round( (float) ideapark_mod( 'product_font_size' ) * 19 / 20 ) . 'px;
			--font-size-mobile-2-rows: ' . round( (float) ideapark_mod( 'product_font_size' ) * 17 / 20 ) . 'px;
			--font-size-compact: ' . ideapark_mod( 'product_font_size_compact' ) . 'px;
			--font-size-compact-mobile: ' . round( (float) ideapark_mod( 'product_font_size_compact' ) * 17 / 18 ) . 'px;
			--color-variations-size: ' . ideapark_mod( 'color_variations_size' ) . 'px;
		}
		
		.product {
			--summary-bg-color: ' . ideapark_mod_hex_color_norm( 'product_summary_background_color', '#FFFFFF' ) . ';
			--font-size-desktop: ' . ideapark_mod( 'product_page_font_size_desktop' ) . 'px;
			--font-size-desktop-qv: ' . round( (float) ideapark_mod( 'product_page_font_size_desktop' ) * 34 / 45 ) . 'px;
			--font-size-mobile: ' . ideapark_mod( 'product_page_font_size_mobile' ) . 'px;
			--font-size-mobile-qv: ' . round( (float) ideapark_mod( 'product_page_font_size_mobile' ) * 34 / 40 ) . 'px;
		}
		
		.l-header {
			--top-color: ' . esc_attr( ideapark_mod_hex_color_norm( 'header_top_color', $text_color ) ) . ';
			--top-accent-color: ' . esc_attr( ideapark_mod_hex_color_norm( 'header_top_accent_color', $accent_color ) ) . ';
			--top-background-color: ' . esc_attr( $bg = ideapark_mod_hex_color_norm( 'header_top_background_color', $background_color ) ) . ';
			--top-border-color: ' . ( ideapark_hex_lighting( $bg ) == 255 ? 'currentColor' : 'transparent' ) . ';
			
			--header-color-mobile: ' . ideapark_mod_hex_color_norm( 'mobile_header_color', $text_color ) . ';
			--header-color-bg-mobile: ' . ideapark_mod_hex_color_norm( 'mobile_header_background_color', '#FFFFFF' ) . ';
			
			--transparent-header-color: ' . ideapark_mod_hex_color_norm( 'transparent_header_color', '#FFFFFF' ) . ';
			--transparent-header-accent: ' . ideapark_mod_hex_color_norm( 'transparent_header_accent_color', $text_color ) . ';
			
			--header-height-mobile: ' . ideapark_mod( 'header_height_mobile' ) . 'px;
			--sticky-header-height-mobile: ' . ideapark_mod( 'sticky_header_height_mobile' ) . 'px; 
		}
		
		.woocommerce-store-notice {
			--store-notice-color: ' . ideapark_mod_hex_color_norm( 'store_notice_color' ) . ';
			--store-notice-background-color: ' . ideapark_mod_hex_color_norm( 'store_notice_background_color' ) . ';
		}
		
		input[type=radio],
		input[type=checkbox],
		.woocommerce-widget-layered-nav-list__item,
		.c-ip-attribute-filter__list {
			--border-color: ' . ideapark_hex_to_rgb_overlay( '#FFFFFF', $text_color, 0.2 ) . ';
		}
		
		.c-product-features {
			--feature-text-color: ' . ideapark_mod_hex_color_norm( 'product_features_text_color', $text_color ) . ';
			--feature-description-color: ' . ideapark_mod_hex_color_norm( 'product_features_description_color', $text_color_light ) . ';
			--feature-background-color: ' . ideapark_mod_hex_color_norm( 'product_features_background_color', $accent_background_color ) . ';
			--feature-border: ' . ( ideapark_mod( 'product_features_border' ) ? 'dashed 1px ' . ideapark_mod_hex_color_norm( 'product_features_border_color', $accent_color ) : 'none' ) . ';
		}
		
		.c-product__slider-item--video .mejs-mediaelement .wp-video-shortcode,
		.c-product__slider-item--video .c-inline-video {
			object-fit: ' . ideapark_mod( 'product_image_fit' ) . ';
		}
		
		.c-product__custom-html {
			--custom-text-color: ' . ideapark_mod_hex_color_norm( 'product_html_text_color', $text_color_body ) . ';
			--custom-background-color: ' . ideapark_mod_hex_color_norm( 'product_html_background_color' ) . ';
			--custom-border: ' . ( ideapark_mod( 'product_html_border' ) ? 'dashed 1px ' . ideapark_mod_hex_color_norm( 'product_html_border_color', $accent_color ) : 'none' ) . ';
			--custom-columns: ' . ( ideapark_mod( 'product_html_2_col' ) ? 2 : 1 ) . ';
		}
			
	</style>';

	$custom_css = preg_replace( '~[\r\n]~', '', preg_replace( '~[\t\s]+~', ' ', str_replace( [
		'<style>',
		'</style>'
	], [ '', '' ], $custom_css ) ) );

	if ( $custom_css ) {
		if ( $is_return_value ) {
			return $custom_css;
		} else {
			wp_add_inline_style( 'ideapark-core', $custom_css );
		}
	}

	return '';
}

function ideapark_uniord( $u ) {
	$k  = mb_convert_encoding( $u, 'UCS-2LE', 'UTF-8' );
	$k1 = ord( substr( $k, 0, 1 ) );
	$k2 = ord( substr( $k, 1, 1 ) );

	return $k2 * 256 + $k1;
}

function ideapark_b64enc( $input ) {

	$keyStr = "ABCDEFGHIJKLMNOP" .
	          "QRSTUVWXYZabcdef" .
	          "ghijklmnopqrstuv" .
	          "wxyz0123456789+/" .
	          "=";

	$output = "";
	$i      = 0;

	do {
		$chr1 = ord( substr( $input, $i ++, 1 ) );
		$chr2 = $i < strlen( $input ) ? ord( substr( $input, $i ++, 1 ) ) : null;
		$chr3 = $i < strlen( $input ) ? ord( substr( $input, $i ++, 1 ) ) : null;

		$enc1 = $chr1 >> 2;
		$enc2 = ( ( $chr1 & 3 ) << 4 ) | ( $chr2 >> 4 );
		$enc3 = ( ( $chr2 & 15 ) << 2 ) | ( $chr3 >> 6 );
		$enc4 = $chr3 & 63;

		if ( $chr2 === null ) {
			$enc3 = $enc4 = 64;
		} else if ( $chr3 === null ) {
			$enc4 = 64;
		}

		$output = $output .
		          substr( $keyStr, $enc1, 1 ) .
		          substr( $keyStr, $enc2, 1 ) .
		          substr( $keyStr, $enc3, 1 ) .
		          substr( $keyStr, $enc4, 1 );
		$chr1   = $chr2 = $chr3 = "";
		$enc1   = $enc2 = $enc3 = $enc4 = "";
	} while ( $i < strlen( $input ) );

	return $output;
}

