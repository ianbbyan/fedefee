<?php

if ( ! function_exists( 'ideapark_setup_woocommerce' ) ) {
	function ideapark_setup_woocommerce( $is_force = false ) {
		if ( ( $is_force || ( ideapark_is_requset( 'frontend' ) || ideapark_is_elementor_preview() ) ) && ideapark_woocommerce_on() ) {

			if ( ideapark_is_elementor_preview() || $is_force ) {
				WC()->frontend_includes();
				WC()->initialize_session();
			}

			/* All WC pages */

			if ( ideapark_mod( 'disable_purchase' ) ) {
				add_filter( 'woocommerce_is_purchasable', '__return_false' );
			}

			if ( ideapark_mod( 'hide_prices' ) ) {
				add_filter( 'woocommerce_get_price_html', function ( $price ) {
					return is_admin() ? $price : '';
				}, 999 );
			}

			if ( ( ! is_admin() || IDEAPARK_IS_AJAX_SEARCH || ideapark_is_elementor_preview() ) && ( $category_ids = ideapark_hidden_category_ids() ) ) {

				$f = function ( $args ) use ( $category_ids ) {
					$args['exclude'] = array_unique( array_filter( ! empty( $args['exclude'] ) && is_array( $args['exclude'] ) ? array_merge( $args['exclude'], $category_ids ) : ( ! empty( $args['exclude'] ) ? array_merge( [ (int) $args['exclude'] ], $category_ids ) : $category_ids ) ) );
					if ( ! empty( $args['include'] ) ) {
						$args['include'] = array_unique( array_filter( is_array( $args['include'] ) ? array_diff( $args['include'], $category_ids ) : array_diff( [ (int) $args['include'] ], $category_ids ) ) );
					}

					return $args;
				};

				add_filter( 'woocommerce_product_subcategories_args', $f );
				add_filter( 'woocommerce_product_categories_widget_args', $f );
				add_filter( 'woocommerce_product_categories_widget_dropdown_args', $f );

				add_filter( 'woocommerce_product_related_posts_query', function ( $query ) use ( $category_ids ) {
					global $wpdb;
					$query['join']  .= " LEFT JOIN (SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (" . implode( ',', $category_ids ) . ") ) AS exclude_hidden_join ON exclude_hidden_join.object_id = p.ID ";
					$query['where'] .= " AND exclude_hidden_join.object_id IS NULL";

					return $query;
				} );

				add_action( 'woocommerce_product_query', function ( $q ) use ( $category_ids ) {
					$tax_query   = (array) $q->get( 'tax_query' );
					$tax_query[] = [
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $category_ids,
						'operator' => 'NOT IN'
					];
					$q->set( 'tax_query', $tax_query );

					return $q;
				} );

				add_action( 'woocommerce_shortcode_products_query', function ( $q ) use ( $category_ids ) {
					if ( ! isset( $q['tax_query'] ) || ! is_array( $q['tax_query'] ) ) {
						$q['tax_query'] = [];
					}
					$q['tax_query'][] = [
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $category_ids,
						'operator' => 'NOT IN'
					];

					return $q;
				} );

				add_filter( 'get_terms_args', function ( $params ) use ( $category_ids ) {
					if ( ! is_admin() && $params['taxonomy'] == [ 'product_cat' ] ) {
						$params['exclude'] = implode( ',', $category_ids );
					}

					return $params;
				} );

				add_filter( 'get_the_terms', function ( $terms, $post_ID, $taxonomy ) use ( $category_ids ) {
					if ( $taxonomy == "product_cat" ) {
						foreach ( $terms as $key => $term ) {
							if ( in_array( $term->term_id, $category_ids ) ) {
								unset( $terms[ $key ] );
							}
						}
					}

					return $terms;
				}, 11, 3 );
			}

			ideapark_ra( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
			ideapark_rf( 'woocommerce_product_loop_start', 'woocommerce_maybe_show_product_subcategories' );
			ideapark_ra( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

			/* Structured Data */

			add_action( 'wp_footer', 'ideapark_structured_data', 9 );

			/* Products loop */

			ideapark_ra( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
			ideapark_ra( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
			ideapark_ra( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
			ideapark_ra( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
			ideapark_ra( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			ideapark_ra( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
			ideapark_ra( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			ideapark_ra( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

			if ( ideapark_mod( 'product_grid_pagination' ) !== 'pagination' ) {
				ideapark_ra( 'woocommerce_after_shop_loop', 'woocommerce_pagination' );
				add_action( 'woocommerce_after_shop_loop', 'ideapark_infinity_paging' );
				add_filter( 'template_include', 'ideapark_infinity_loading', 1000 );
			}

			add_filter( 'loop_shop_per_page', function () {
				return ideapark_mod( 'products_per_page' );
			} );

			add_action( 'woocommerce_before_main_content', function () {
				if ( is_product_taxonomy() ) {
					ideapark_mod_set_temp( '_archive_attribute_id', get_queried_object_id() );
				}
			} );

			add_action( 'woocommerce_before_main_content', function () {
				if ( is_archive() ) {
					wc_get_template( 'global/ordering.php' );
				}
			}, 5 );

			add_action( 'woocommerce_before_shop_loop', 'ideapark_woocommerce_search_form', 30 );
			add_action( 'woocommerce_no_products_found', 'ideapark_woocommerce_search_form', 9 );

			add_action( 'woocommerce_before_shop_loop_item_title', function () { ?><div class="c-product-grid__thumb-wrap c-product-grid__thumb-wrap--<?php if ( ideapark_mod( 'shop_modal' ) || ideapark_mod( 'wishlist_page' ) && ideapark_mod( 'wishlist_grid_button' ) ) { ?>buttons<?php } else { ?>no-buttons<?php } ?>"><?php }, 6 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 9 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'ideapark_loop_product_thumbnail', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11 );

			add_action( 'woocommerce_before_shop_loop_item_title', function () {
				echo '<div class="c-product-grid__badges c-badge__list">';
			}, 15 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'ideapark_woocommerce_show_product_loop_badges', 15 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 15 );
			if ( ideapark_mod( 'outofstock_badge_text' ) ) {
				add_action( 'woocommerce_before_shop_loop_item_title', 'ideapark_stock_badge', 15 );
			}
			add_action( 'woocommerce_before_shop_loop_item_title', function () {
				echo '</div><!-- .c-product-grid__badges -->';
			}, 15 );

			add_action( 'woocommerce_before_shop_loop_item_title', 'ideapark_template_product_buttons', 20 );
			add_action( 'woocommerce_before_shop_loop_item_title', function () { ?></div><!-- .c-product-grid__thumb-wrap --><?php }, 50 );
			if ( ideapark_mod( 'show_add_to_cart' ) ) {
				add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 55 );
			}
			add_action( 'woocommerce_before_shop_loop_item_title', function () { ?><div class="c-product-grid__details <?php echo ideapark_grid_text_class( 'c-product-grid__details' ); ?>"><div class="c-product-grid__title-wrap"><?php }, 100 );
			add_action( 'woocommerce_shop_loop_item_title', 'ideapark_cut_product_categories', 8 );
			add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 9 );
			add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11 );
			add_action( 'woocommerce_after_shop_loop_item_title', 'ideapark_template_short_description', 3 );
			if ( ideapark_mod( 'short_description_link' ) ) {
				add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 2 );
				add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 4 );
			}

			add_action( 'woocommerce_after_shop_loop_item_title', function () {
				echo '</div><!-- .c-product-grid__title-wrap --><div class="c-product-grid__price-wrap">';
			}, 50 );
			if ( ideapark_mod( 'product_brand_attribute' ) && taxonomy_exists( ideapark_mod( 'product_brand_attribute' ) ) ) {
				if ( ideapark_mod( 'show_product_page_brand' ) ) {
					add_action( 'woocommerce_product_meta_end', 'ideapark_template_brand_meta' );
				}

				if ( ideapark_mod( 'show_cart_page_brand' ) ) {
					add_filter( 'woocommerce_widget_cart_item_quantity', 'ideapark_cart_mini_brand', 1, 3 );
					add_action( 'woocommerce_after_cart_item_name', 'ideapark_cart_brand', 10, 2 );
				}
			}
			add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 55 );
			add_action( 'woocommerce_after_shop_loop_item_title', function () {
				echo '</div><!-- .c-product-grid__price-wrap -->';
			}, 60 );

			if ( ideapark_mod( 'show_color_variations' ) && ideapark_mod( 'product_color_attribute' ) && ideapark_swatches_plugin_on() && taxonomy_exists( ideapark_mod( 'product_color_attribute' ) ) && ( in_array( ideapark_get_taxonomy_type( ideapark_mod( 'product_color_attribute' ) ), [
					'color',
					'image'
				] ) ) ) {
				add_action( 'woocommerce_before_shop_loop_item', 'ideapark_grid_color_attributes_prepare', 0 );
				add_action( 'woocommerce_after_shop_loop_item_title', 'ideapark_grid_color_attributes', 63 );
			}

			if ( ideapark_mod( 'product_preview_rating' ) ) {
				add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 65 );
			}
		add_action( 'woocommerce_after_shop_loop_item_title', function () { ?></div>
			<!-- .c-product-grid__details --><?php }, 70 );

		add_action( 'woocommerce_archive_description', function () { ?>
			<div
				class="<?php if ( ideapark_mod( 'category_description_position' ) == 'below' ) { ?>l-section <?php } ?>entry-content c-product-grid__cat-desc c-product-grid__cat-desc--<?php echo esc_attr( ideapark_mod( 'category_description_position' ) ); ?>"><?php }, 9 );
		add_action( 'woocommerce_archive_description', function () { ?></div><?php }, 11 );

		add_action( 'woocommerce_before_subcategory_title', function () { ?><span
				class="c-sub-categories__thumb-wrap <?php ideapark_class( ideapark_mod( 'show_subcat_in_header' ), 'c-sub-categories__thumb-wrap--header', 'c-sub-categories__thumb-wrap--content' ); ?>"><?php }, 9 );
		add_action( 'woocommerce_before_subcategory_title', function () { ?></span><?php }, 11 );

			if ( ! ideapark_mod( 'show_subcat_in_header' ) ) {
				add_action( 'woocommerce_before_subcategory', function () { ?><span class="c-sub-categories__item-wrap"><?php }, 15 );
				add_action( 'woocommerce_after_subcategory', function () { ?></span><?php }, 5 );
			}

			if ( ideapark_mod( 'recently_enabled' ) && ideapark_mod( 'recently_shop_show' ) && ideapark_mod( 'recently_product_number' ) ) {
				add_action( 'woocommerce_after_main_content', 'ideapark_recently_products_shop_only', 20 );
			}

			/* Product page */

			add_filter( 'woocommerce_product_tabs', 'ideapark_html_block_tab_add_to_list' );

			if ( ideapark_mod( 'hide_brand_additional_tab' ) ) {
				add_filter( 'woocommerce_display_product_attributes', function ( $attributes ) {
					return array_filter( $attributes, function ( $key ) {
						return $key != 'attribute_' . ideapark_mod( 'product_brand_attribute' );
					}, ARRAY_FILTER_USE_KEY );
				} );
			}

			add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
				$args['posts_per_page'] = (int) ideapark_mod( 'related_product_number' );

				return $args;
			}, 100 );

			if ( ideapark_mod( 'related_product_header' ) ) {
				add_filter( 'woocommerce_product_related_products_heading', function ( $header ) {
					return esc_html( ideapark_mod( 'related_product_header' ) );
				}, 100 );
			}

			if ( ideapark_mod( 'upsells_product_header' ) ) {
				add_filter( 'woocommerce_product_upsells_products_heading', function ( $header ) {
					return esc_html( ideapark_mod( 'upsells_product_header' ) );
				}, 100 );
			}

			if ( ideapark_mod( 'cross_sells_product_header' ) ) {
				add_filter( 'woocommerce_product_cross_sells_products_heading', function ( $header ) {
					return esc_html( ideapark_mod( 'cross_sells_product_header' ) );
				}, 100 );
			}

			if ( ideapark_mod( 'recently_product_header' ) ) {
				add_filter( 'woocommerce_product_recently_products_heading', function ( $header ) {
					return esc_html( ideapark_mod( 'recently_product_header' ) );
				}, 100 );
			}

			add_action( 'woocommerce_before_single_product', function () {
				global $product;
				if ( isset( $product ) && $product->is_type( 'variable' ) && is_product() && ideapark_mod( 'hide_variable_price_range' ) ) {

				} else {
					ideapark_ra( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
					add_filter( 'woocommerce_get_price_html', 'ideapark_add_sale_to_price' );
				}
			} );

			add_filter( 'woocommerce_post_class', function ( $classes ) {
				if ( is_product() && ! ideapark_mod( '_is_product_loop' ) && ! ideapark_mod( '_is_product_set' ) ) {
					ideapark_mod_set_temp( '_is_product_set', true );
					$ip_classes = [ 'c-product', 'c-product--' . ideapark_mod( 'product_page_layout' ), 'l-section' ];
					if ( ideapark_mod( 'product_page_layout' ) == 'layout-1' ) {
						$ip_classes[] = 'c-product--' . ideapark_mod( 'product_tabs_layout' );
					}
					$ip_classes[] = 'c-product--additional-' . ideapark_mod( 'additional_tab_layout' );
					if ( ideapark_mod( 'product_bottom_page' ) ) {
						$ip_classes[] = 'c-product--bottom-block';
					}
					global $product;
					if ( ! $product->is_purchasable() ) {
						$ip_classes[] = 'c-product--not-purchasable';
					}
					switch ( ideapark_mod( 'product_page_layout' ) ) {
						case 'layout-1':
							$ip_classes[] = 'l-section--container';
							break;

						case 'layout-2':
//							$ip_classes[] = 'l-section--container-wide';
							break;
					}

					return array_merge( $ip_classes, $classes );
				} else {
					return $classes;
				}
			}, 100 );
			if ( ideapark_mod( 'hide_variable_price_range' ) ) {
				add_filter( 'woocommerce_get_price_html', function ( $price, $product ) {
					if ( $product->is_type( 'variable' ) && is_product() ) {
						return '';
					}

					return $price;
				}, 99, 2 );

				add_filter( 'woocommerce_show_variation_price', '__return_true' );
			}

			ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 7 );

		add_action( 'woocommerce_before_single_product_summary', function () { ?>
			<div class="c-product__gallery"><?php }, 5 );
		add_action( 'woocommerce_before_single_product_summary', function () { ?></div><!-- .c-product__gallery --><?php }, 50 );
		add_action( 'woocommerce_before_single_product_summary', function () { ?>
			<div class="c-badge__list c-product__badges"><?php }, 8 );
			add_action( 'woocommerce_before_single_product_summary', 'ideapark_woocommerce_show_product_loop_badges', 9 );
		add_action( 'woocommerce_before_single_product_summary', function () { ?></div><!-- .c-product__badges --><?php }, 12 );
			ideapark_ra( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
			ideapark_ra( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
			add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 19 );

			if ( ! ideapark_mod( 'related_product_show' ) || ! ideapark_mod( 'related_product_number' ) ) {
				ideapark_ra( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
			}

			if ( ideapark_mod( 'recently_enabled' ) && ideapark_mod( 'recently_product_number' ) ) {
				add_action( 'woocommerce_after_single_product_summary', 'ideapark_recently_container', ideapark_mod( 'recently_position' ) == 'above' ? 18 : 21 );
			}

			add_action( 'woocommerce_before_single_product_summary', 'ideapark_html_block_section_add_to_list' );

			add_filter( 'wc_get_template', function ( $template, $template_name, $args ) {
				$class = 'c-product-grid__list--carousel js-product-grid-carousel h-carousel h-carousel--border h-carousel--round h-carousel--hover h-carousel--default-dots h-carousel--flex';
				if ( $template_name == 'single-product/recently.php' ) {
					ideapark_mod_set_temp( '_products_count', ! empty( $args['recently_products'] ) && is_array( $args['recently_products'] ) ? sizeof( $args['recently_products'] ) : 10 );
					ideapark_mod_set_temp( '_product_carousel_class', $class );
					ideapark_mod_set_temp( '_product_carousel_data', '' );
				} elseif ( $template_name == 'single-product/related.php' ) {
					ideapark_mod_set_temp( '_products_count', ! empty( $args['related_products'] ) && is_array( $args['related_products'] ) ? sizeof( $args['related_products'] ) : 10 );
					ideapark_mod_set_temp( '_product_carousel_class', $class );
					ideapark_mod_set_temp( '_product_carousel_data', '' );
				} elseif ( $template_name == 'single-product/up-sells.php' ) {
					ideapark_mod_set_temp( '_products_count', ! empty( $args['upsells'] ) && is_array( $args['upsells'] ) ? sizeof( $args['upsells'] ) : 10 );
					ideapark_mod_set_temp( '_product_carousel_class', $class );
					ideapark_mod_set_temp( '_product_carousel_data', '' );
				} elseif ( $template_name == 'cart/cross-sells.php' ) {
					ideapark_mod_set_temp( '_products_count', ! empty( $args['cross_sells'] ) && is_array( $args['cross_sells'] ) ? sizeof( $args['cross_sells'] ) : 10 );
					ideapark_mod_set_temp( '_product_carousel_class', $class );
					ideapark_mod_set_temp( '_product_carousel_data', '' );
				}

				return $template;
			}, 10, 3 );


			if ( ! IDEAPARK_IS_AJAX_QUICKVIEW ) {
				switch ( ideapark_mod( 'product_page_layout' ) ) {
					case 'layout-1':
						add_action( 'woocommerce_before_single_product_summary', 'ideapark_product_breadcrumbs', 2 );
						add_action( 'woocommerce_before_single_product_summary', function () {
							echo '<div class="c-product__wrap c-product__wrap--layout-1">';
						}, 2 );
						add_action( 'woocommerce_before_single_product_summary', function () {
							echo '<div class="c-product__col-1">';
						}, 2 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_2col_start', 9 );
						if ( ideapark_mod( 'product_brand_attribute' ) && ideapark_mod( 'show_product_page_brand' ) && ideapark_mod( 'show_product_page_brand_logo' ) ) {
							add_action( 'woocommerce_single_product_summary', 'ideapark_product_brand_logo', 9 );
						}
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_2col_separator', 39 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_2col_end', 45 );
						switch ( ideapark_mod( 'product_tabs_layout' ) ) {
							case 'tabs-compact':
								add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 5 );
								break;
							case 'tabs-fullwidth':
								add_action( 'woocommerce_after_single_product_summary', 'ideapark_output_product_data_tabs_fullwidth', 5 );
								break;
							case 'tabs-expanded':
								add_action( 'woocommerce_after_single_product_summary', 'ideapark_output_product_data_tabs_expanded', 5 );
								break;
						}
						add_action( 'woocommerce_before_single_product_summary', function () {
							echo '</div><!-- .c-product__col-1 -->';
						}, 99 );
						add_action( 'woocommerce_before_single_product_summary', function () {
							echo '<div class="c-product__col-2">';
						}, 100 );
						add_action( 'woocommerce_after_single_product_summary', function () {
							echo '</div><!-- .c-product__col-2 -->';
						}, 1 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__wrap --><?php }, 1 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?><div class="c-product__after-summary c-product__after-summary--fullwidth"><?php }, 5 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .l-section__container-wide --><?php }, 999 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_add_to_cart', 30 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_features', 39 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_custom_html', 39 );
						break;


					case 'layout-2':
						add_action( 'woocommerce_before_single_product_summary', function () { ?><div class="c-product__wrap c-product__wrap--layout-2"><?php }, 1 );
					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-1">
						<div class="js-sticky-sidebar-nearby"><?php }, 2 );
					add_action( 'woocommerce_before_single_product_summary', function () { ?></div>
						<!-- .c-product__col-1 --></div><!-- .js-sticky-sidebar-nearby --><?php }, 99 );
					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-2">
						<div data-no-offset="yes" class="js-sticky-sidebar"><?php }, 100 );

						add_action( 'woocommerce_single_product_summary', 'ideapark_product_breadcrumbs', 4 );

						if ( ideapark_mod( 'product_brand_attribute' ) && ideapark_mod( 'show_product_page_brand' ) ) {
							add_action( 'woocommerce_single_product_summary', 'ideapark_product_brand_logo', 9 );
						}
						add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 5 );
					add_action( 'woocommerce_after_single_product_summary', function () { ?></div>
						<!-- .c-product__col-2 --></div><!-- .js-sticky-sidebar --><?php }, 5 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__wrap --><?php }, 5 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?><div class="l-section__container-wide c-product__after-summary c-product__after-summary--fullwidth"><?php }, 5 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .l-section__container-wide --><?php }, 909 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_add_to_cart', 30 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_features', 39 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_custom_html', 39 );
						break;


					case 'layout-3':
						add_action( 'woocommerce_before_single_product_summary', function () { ?>
							<div class="c-product__wrap c-product__wrap--layout-3"><?php }, 2 );

					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-2"><?php }, 3 );

					add_action( 'woocommerce_before_single_product_summary', function () { ?></div><!-- .c-product__col-2 --><?php }, 99 );

					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-1"><?php }, 100 );

						add_action( 'woocommerce_before_single_product_summary', function () {
							get_template_part( 'templates/breadcrumbs' );
						}, 101 );

					add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__col-1 --><?php }, 1 );
					add_action( 'woocommerce_after_single_product_summary', function () { ?>
						<div class="c-product__col-3"><?php }, 1 );

						add_action( 'woocommerce_single_product_summary', 'ideapark_product_separator', 40 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_separator_finish', 999 );

						if ( ideapark_mod( 'product_brand_attribute' ) && ideapark_mod( 'show_product_page_brand' ) ) {
							add_action( 'woocommerce_single_product_summary', 'ideapark_product_brand_logo', 45 );
						}

						ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
						add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 50 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_add_to_cart', 55 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_features', 60 );
						add_action( 'woocommerce_single_product_summary', 'ideapark_product_custom_html', 60 );

						add_action( 'woocommerce_after_single_product_summary', 'ideapark_product_separator_content', 1 );

					add_action( 'woocommerce_after_single_product_summary', function () { ?>
						</div><!-- .c-product__col-3 --><?php }, 1 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__wrap --><?php }, 1 );

						ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
						add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_meta', 5 );

						add_action( 'woocommerce_after_single_product_summary', 'ideapark_output_product_data_tabs_expanded', 5 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?><div class="l-section__container-wide c-product__after-summary c-product__after-summary--fullwidth"><?php }, 5 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .l-section__container-wide --><?php }, 999 );
						break;
				}
			} else {
				add_filter( 'woobt_show_items', '__return_false' );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_loop_product_link_open', 4 );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_loop_product_link_close', 6 );

				add_action( 'woocommerce_single_product_summary', 'ideapark_product_add_to_cart', 30 );
				ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
				ideapark_ra( 'woocommerce_before_single_product', 'woocommerce_output_all_notices', 10 );
			}

			ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 8 );

			add_action( 'woocommerce_share', 'ideapark_product_share' );
			ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
			add_action( 'woocommerce_single_product_summary', 'ideapark_product_wishlist_share', 35 );

			if ( ideapark_mod( 'hide_sku' ) ) {
				add_filter( 'wc_product_sku_enabled', function () {
					return ! ( ! is_admin() && is_product() || IDEAPARK_IS_AJAX_QUICKVIEW );
				} );
			}

			if ( ideapark_mod( 'product_bottom_page' ) ) {
				add_action( 'woocommerce_after_single_product', function () {
					if ( ( $page_id = apply_filters( 'wpml_object_id', ideapark_mod( 'product_bottom_page' ), 'any' ) ) && 'publish' == ideapark_post_status( $page_id ) ) {
						global $post;
						if ( ideapark_is_elementor_page( $page_id ) ) {
							$page_content = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $page_id );
						} elseif ( $post = get_post( $page_id ) ) {
							$page_content = apply_filters( 'the_content', $post->post_content );
							$page_content = str_replace( ']]>', ']]&gt;', $page_content );
							$page_content = ideapark_wrap( $page_content, '<div class="entry-content">', '</div>' );
							wp_reset_postdata();
						} else {
							$page_content = '';
						}
						echo ideapark_wrap( $page_content, '<div class="l-section">', '</div>' );
					}

				}, 90 );
			}

			/* Cart page */
			ideapark_ra( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
			add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
			if ( ideapark_mod( 'recently_enabled' ) && ideapark_mod( 'recently_cart_show' ) && ideapark_mod( 'recently_product_number' ) ) {
				add_action( 'woocommerce_after_cart', 'ideapark_recently_container', ideapark_mod( 'recently_position' ) == 'above' ? 9 : 11 );
			}

			add_action( 'woocommerce_before_cart_totals', 'woocommerce_checkout_coupon_form', 10 );

			if ( IDEAPARK_IS_XML_HTTP_REQUEST ) {
				ideapark_ra( 'woocommerce_before_cart', 'woocommerce_output_all_notices', 10 );
				add_action( 'woocommerce_before_cart_table', 'woocommerce_output_all_notices', 10 );
			}

			/* Checkout page */
			ideapark_ra( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
			add_action( 'woocommerce_checkout_before_order_review', 'woocommerce_checkout_coupon_form', 10 );

			/* Snippets */

			if ( ideapark_mod( 'description_tab_header' ) ) {
				add_filter( 'woocommerce_product_description_tab_title', function () {
					return ideapark_mod( 'description_tab_header' );
				} );
			}

			if ( ideapark_mod( 'additional_information_tab_header' ) ) {
				add_filter( 'woocommerce_product_additional_information_tab_title', function () {
					return ideapark_mod( 'additional_information_tab_header' );
				} );
			}

			add_filter( 'product_cat_class', 'ideapark_subcat_class', 10, 3 );

			if ( ideapark_mod( 'store_notice_button_text' ) || ideapark_mod( 'store_notice_button_hide' ) ) {
				add_filter( "woocommerce_demo_store", function ( $notice ) {
					if ( ideapark_mod( 'store_notice_button_hide' ) ) {
						return preg_replace( "~<a href=\"#\" class=\"woocommerce-store-notice__dismiss-link\">[^>]+</a>~", '', $notice );
					} else {
						return preg_replace( "~(dismiss-link\">)([^>]+)(<)~", "\\1" . esc_html( ideapark_mod( 'store_notice_button_text' ) ) . "\\3", $notice );
					}
				} );
			}

			add_filter( 'woocommerce_breadcrumb_home_url', function ( $url ) {
				return home_url( '/' );
			} );

			add_filter( 'woocommerce_layered_nav_count', function ( $html, $count ) {
				return '<span class="count">' . absint( $count ) . '</span>';
			}, 10, 2 );

			add_filter( 'woocommerce_subcategory_count_html', function ( $html, $category ) {
				return ' <mark class="count">' . esc_html( $category->count ) . '</mark>';
			}, 10, 2 );

		}

		if ( ideapark_is_requset( 'admin' ) && ideapark_woocommerce_on() ) {
			add_filter( 'manage_product_cat_custom_column', function ( $content, $column_name, $term_id ) {
				if ( $column_name == 'handle' ) {
					$term = get_term( $term_id );
					if ( $term->taxonomy == 'product_cat' ) {
						if ( ideapark_mod( 'hide_uncategorized' ) && $term->term_id == get_option( 'default_product_cat' )
						     ||
						     ideapark_mod( 'hidden_product_category' ) && $term->term_id == ideapark_mod( 'hidden_product_category' )
						) {
							$content .= '<span class="ideapark-hidden-category">' . esc_html__( 'Hidden', 'goldish' ) . '</span>';
						}
					}
				}

				return $content;
			}, 100, 3 );
			add_filter( "product_cat_row_actions", function ( $actions, $term ) {
				if ( $term->taxonomy == 'product_cat' ) {
					if ( ideapark_mod( 'hide_uncategorized' ) && $term->term_id == get_option( 'default_product_cat' ) ) {
						$actions['unhide'] = sprintf( "<a href=\"%s\">%s</a>", admin_url( 'customize.php?autofocus[control]=hide_uncategorized' ), __( 'Unhide', 'goldish' ) );
					}

					if ( ideapark_mod( 'hidden_product_category' ) && $term->term_id == ideapark_mod( 'hidden_product_category' ) ) {
						$actions['unhide'] = sprintf( "<a href=\"%s\">%s</a>", admin_url( 'customize.php?autofocus[control]=hidden_product_category' ), __( 'Unhide', 'goldish' ) );
					}
				}

				return $actions;
			}, 10, 2 );

			add_action( 'woocommerce_product_write_panel_tabs', function () {
				echo '<li class="ideapark_custom_badge_tab"><a href="#ideapark_custom_badge_tab"><span>' . __( 'Custom badge', 'goldish' ) . '</span></a></li>';
			} );
			add_action( 'woocommerce_product_data_panels', 'ideapark_custom_badge_tab_admin' );
			add_action( 'woocommerce_process_product_meta', 'ideapark_custom_badge_tab_save', 10, 2 );

			add_action( 'woocommerce_product_write_panel_tabs', function () {
				echo '<li class="ideapark_html_block_tab"><a href="#ideapark_html_block_tab"><span>' . __( 'Tab with HTML-block', 'goldish' ) . '</span></a></li>';
			} );
			add_action( 'woocommerce_product_data_panels', 'ideapark_html_block_tab_admin' );
			add_action( 'woocommerce_process_product_meta', 'ideapark_html_block_tab_save', 10, 2 );

			add_action( 'woocommerce_product_write_panel_tabs', function () {
				echo '<li class="ideapark_html_block_section"><a href="#ideapark_html_block_section"><span>' . __( 'Section with HTML-block', 'goldish' ) . '</span></a></li>';
			} );
			add_action( 'woocommerce_product_data_panels', 'ideapark_html_block_section_admin' );
			add_action( 'woocommerce_process_product_meta', 'ideapark_html_block_section_save', 10, 2 );
		}
	}
}

if ( ! function_exists( 'ideapark_cut_product_categories' ) ) {
	function ideapark_cut_product_categories() { ?>
		<?php
		/**
		 * @var $product WC_Product
		 **/
		global $product;

		$separator  = '<span class="h-bullet"></span>';
		$categories = [];
		$brands     = [];

		if ( ideapark_mod( 'shop_category' ) ) {
			$term_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
			foreach ( $term_ids as $term_id ) {
				$categories[] = get_term_by( 'id', $term_id, 'product_cat' );
			}
		}

		if ( ideapark_mod( 'show_product_grid_brand' ) ) {
			if ( $terms = ideapark_brands() ) {
				foreach ( $terms as $term ) {
					$brands[] = '<a class="c-product-grid__category-item c-product-grid__category-item--brand" href="' . esc_url( get_term_link( $term->term_id, ideapark_mod( 'product_brand_attribute' ) ) ) . '">' . esc_html( $term->name ) . '</a>';
				}
				$brands = array_filter( $brands );
			}
		}

		if ( $categories || $brands ) { ?>
			<div class="c-product-grid__category-list">
				<?php
				if ( $categories ) {
					ideapark_category( $separator, $categories, 'c-product-grid__category-item' );
				}
				if ( $brands ) {
					echo ideapark_wrap( $categories ? ideapark_wrap( $separator ) : '' ) . implode( $separator, $brands );
				}
				?>
			</div>
		<?php }
	}
}

if ( ! function_exists( 'ideapark_product_brand_logo' ) ) {
	function ideapark_product_brand_logo() {
		if ( $terms = ideapark_brands() ) {
			$brands = [];
			foreach ( $terms as $term ) {
				if ( ( $image_id = get_term_meta( $term->term_id, 'brand_logo', true ) ) && ( $type = get_post_mime_type( $image_id ) ) ) {
					$brand = '<a class="c-product__brand-logo" href="' . esc_url( get_term_link( $term->term_id, ideapark_mod( 'product_brand_attribute' ) ) ) . '">';
					if ( $type == 'image/svg+xml' ) {
						$brand .= ideapark_get_inline_svg( $image_id, 'c-product__brand-logo-svg' );
					} else {
						$brand .= ideapark_img( ideapark_image_meta( $image_id ), 'c-product__brand-logo-image' );
					}
					$brand    .= '</a>';
					$brands[] = $brand;
				}
			}
			$brands = array_filter( $brands );
			echo ideapark_wrap( implode( '', $brands ), '<div class="c-product__brand-logo-list">', '</div>' );
		}
	}
}

if ( ! function_exists( 'ideapark_output_product_data_tabs_expanded' ) ) {
	function ideapark_output_product_data_tabs_expanded() {
		$tabs_left  = [];
		$tabs_right = [];
		$tabs       = [];

		$product_tabs = apply_filters( 'woocommerce_product_tabs', [] );
		if ( ! empty( $product_tabs ) ) {
			foreach ( $product_tabs as $key => $product_tab ) {
				if ( isset( $product_tab['callback'] ) ) {
					ob_start();
					call_user_func( $product_tab['callback'], $key, $product_tab );
					$tab_content = trim( ob_get_clean() );
					if ( ! $tab_content ) {
						continue;
					}
				} else {
					continue;
				}

				ob_start();
				?>
				<?php if ( $key != 'reviews' ) { ?>
					<div
						class="c-product__tabs-header"><?php echo wp_kses( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ), [ 'sup' => [ 'class' => true ] ] ); ?></div>
				<?php } ?>
				<div
					class="c-product__tabs-panel woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel <?php if ( $key == 'description' ) { ?>entry-content<?php } ?> wc-tab visible"
					id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel"
					aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
					<?php echo ideapark_wrap( $tab_content ); ?>
				</div>
				<?php
				$tabs[ $key ] = ob_get_clean();
			}
		}

		$tabs = array_filter( $tabs );

		if ( ! empty( $tabs['reviews'] ) ) {
			$tabs_left             = $tabs;
			$tabs_right['reviews'] = $tabs['reviews'];
			unset( $tabs_left['reviews'] );
		} else {
			$index = 0;
			foreach ( $tabs as $key => $content ) {
				if ( $index < sizeof( $tabs ) / 2 ) {
					$tabs_left[ $key ] = $content;
				} else {
					$tabs_right[ $key ] = $content;
				}
				$index ++;
			}
		}

		if ( $tabs ) { ?>
			<div class="c-product__tabs-row">
				<?php if ( sizeof( $tabs ) == 1 ) { ?>
					<div class="c-product__tabs-col-center">
						<?php echo ideapark_wrap( implode( '', $tabs_left ) ) ?>
						<?php echo ideapark_wrap( implode( '', $tabs_right ) ) ?>
					</div>
				<?php } else { ?>
					<div class="c-product__tabs-col-1">
						<?php echo ideapark_wrap( implode( '', $tabs_left ) ) ?>
					</div>
					<div class="c-product__tabs-col-2">
						<?php echo ideapark_wrap( implode( '', $tabs_right ) ) ?>
					</div>
				<?php } ?>
			</div>
		<?php } else { ?>
			<div class="c-product__tabs-row-line"></div>
		<?php } ?>
		<?php
		do_action( 'woocommerce_product_after_tabs' );
	}
}

if ( ! function_exists( 'ideapark_loop_product_thumbnail' ) ) {
	function ideapark_loop_product_thumbnail( $is_hover_image = false ) {
		global $product;
		$switch_image_on_hover = ( ideapark_mod( 'switch_image_on_hover' ) && ideapark_mod( 'product_grid_layout' ) != 'compact' && ideapark_mod( 'product_buttons_layout' ) == 'buttons-2' ) && $product->get_gallery_image_ids();
		if ( $product ) {
			$is_inline_video = ! ! get_post_meta( $product->get_id(), '_ip_product_video_grid', true );
			$attr            = [ 'class' => 'c-product-grid__thumb c-product-grid__thumb--' . ideapark_mod( 'grid_image_fit' ) . ( $switch_image_on_hover ? ( $is_hover_image ? ' c-product-grid__thumb--hover' : ' c-product-grid__thumb--base' ) : '' ) ];
			$image_size      = apply_filters( 'single_product_archive_thumbnail_size', 'large' );
			if ( $is_hover_image ) {
				$ids           = $product->get_gallery_image_ids();
				$attachment_id = ( ! empty( $ids[0] ) ) ? $ids[0] : 0;
			} else {
				if ( ( $colors = ideapark_mod( '_archive_attribute_items' ) ) && ( $archive_attribute_id = ideapark_mod( '_archive_attribute_id' ) ) && array_key_exists( $archive_attribute_id, $colors ) && isset( $colors[ $archive_attribute_id ]['image_id'] ) ) {
					$attachment_id  = $colors[ $archive_attribute_id ]['image_id'];
					$_attachment_id = $product->get_image_id();
					if ( $_attachment_id && ( $_image = wp_get_attachment_image_src( $_attachment_id, $image_size ) ) ) {
						$_image_meta = wp_get_attachment_metadata( $_attachment_id );
						if ( is_array( $_image_meta ) ) {
							[ $src, $width, $height ] = $_image;
							$size_array          = [ absint( $width ), absint( $height ) ];
							$fn                  = 'wp_calculate' . '_image_srcset';
							$attr['data-srcset'] = $fn( $size_array, $src, $_image_meta, $attachment_id );
							$attr['data-src']    = $_image[0];
						}
					}
				} else {
					if ( $is_inline_video && ( $video_url = get_post_meta( $product->get_id(), '_ip_product_video_url', true ) ) ) {
						$video_thumb_url = '';
						if ( ( $video_thumb_id = get_post_meta( $product->get_id(), '_ip_product_video_thumb', true ) ) ) {
							if ( $video_thumb_image = wp_get_attachment_image_src( $video_thumb_id, $image_size ) ) {
								$video_thumb_url = $video_thumb_image[0];
							}
						}
						if ( $image_html = ideapark_inline_video( $video_url, $video_thumb_url, 'c-product-grid__thumb c-product-grid__thumb--' . ideapark_mod( 'grid_image_fit' ) . ' c-product-grid__thumb--video js-grid-video', 'muted loop playsinline disablepictureinpicture preload="none"' ) ) {
							echo ideapark_wrap( $image_html );

							return;
						} else {
							$attachment_id = $product->get_image_id();
						}
					} else {
						$attachment_id = $product->get_image_id();
					}
				}

				if ( ! $attachment_id ) {
					$attachment_id = get_post_meta( $product->get_id(), '_ip_product_video_thumb', true );
				}

				if ( ! $attachment_id ) {
					if ( ideapark_mod( '_placeholder_id' ) ) {
						$attachment_id = ideapark_mod( '_placeholder_id' );
					} else {
						if ( ( $placeholder_image = get_option( 'woocommerce_placeholder_image', 0 ) ) && wp_attachment_is_image( $placeholder_image ) ) {
							$attachment_id = (int) $placeholder_image;
							ideapark_mod_set_temp( '_placeholder_id', $attachment_id );
						}
					}
				}
			}

			if ( $attachment_id && ( $image = wp_get_attachment_image_src( $attachment_id, $image_size ) ) ) {
				$image_meta = wp_get_attachment_metadata( $attachment_id );
				if ( is_array( $image_meta ) ) {
					[ $src, $width, $height ] = $image;
					$size_array     = [ absint( $width ), absint( $height ) ];
					$fn             = 'wp_calculate' . '_image_srcset';
					$attr['srcset'] = $fn( $size_array, $src, $image_meta, $attachment_id );
					$attr['sizes']  = '';// wp_calculate_image_sizes( $size_array, $src, $image_meta, $attachment_id );
					$layout         = ideapark_mod( '_product_layout' );
					$layout_mobile  = ideapark_mod( '_product_layout_mobile' );
					$layout_width   = ideapark_mod( '_product_layout_width' );
					$with_sidebar   = ideapark_mod( '_with_sidebar' );
					switch ( $layout ) {
						case '2-per-row':
							if ( $layout_width == 'boxed' ) {
								if ( $with_sidebar ) {
									$attr['sizes'] .= "(min-width: 1190px) 445px, (min-width: 1024px) 50vw, (min-width: 768px) 100vw";
								} else {
									$attr['sizes'] .= "(min-width: 1190px) 580px, (min-width: 1024px) 50vw, (min-width: 768px) 100vw";
								}
							} else {
								$attr['sizes'] .= "(min-width: 1190px) 50vw, (min-width: 768px) 100vw";
							}
							break;
						case '3-per-row':
							if ( $layout_width == 'boxed' ) {
								if ( $with_sidebar ) {
									$attr['sizes'] .= "(min-width: 768px) 294px";
								} else {
									$attr['sizes'] .= "(min-width: 768px) 384px";
								}
							} else {
								$attr['sizes'] .= "(min-width: 768px) 33vw";
							}
							break;
						case '4-per-row':
							if ( $layout_width == 'boxed' ) {
								if ( $with_sidebar ) {
									$attr['sizes'] .= "(min-width: 1190px) 218px, (min-width: 1024px) 25vw, (min-width: 768px) 33vw";
								} else {
									$attr['sizes'] .= "(min-width: 1024px) 285px, (min-width: 768px) 33vw";
								}
							} else {
								if ( $with_sidebar ) {
									$attr['sizes'] .= "(min-width: 1190px) 20vw, (min-width: 1024px) 25vw, (min-width: 768px) 33vw";
								} else {
									$attr['sizes'] .= "(min-width: 1024px) 25vw, (min-width: 768px) 33vw";
								}
							}

							break;
						case 'compact':
							$attr['sizes'] = "(min-width: 768px) 145px";
							break;
					}
					switch ( $layout_mobile ) {
						case '1-per-row-mobile':
							$attr['sizes'] .= ", 100vw";
							break;
						case '2-per-row-mobile':
							$attr['sizes'] .= ", 50vw";
							break;
						case 'compact-mobile':
							$attr['sizes'] .= ", 90px";
							break;
					}
				}
			}
			if ( $attachment_id ) {
				echo wp_get_attachment_image( $attachment_id, $image_size, false, $attr );
			}
			if ( $switch_image_on_hover && ! $is_hover_image ) {
				ideapark_loop_product_thumbnail( true );
			}
		}
	}
}

if ( ! function_exists( 'ideapark_template_product_buttons' ) ) {
	function ideapark_template_product_buttons() {
		wc_get_template( 'global/product-buttons.php' );
	}
}

if ( ! function_exists( 'ideapark_template_short_description' ) ) {
	function ideapark_template_short_description() {
		wc_get_template( 'loop/short-description.php' );
	}
}

if ( ! function_exists( 'ideapark_template_brand_meta' ) ) {
	function ideapark_template_brand_meta() {
		wc_get_template( 'loop/brand_meta.php' );
	}
}

if ( ! function_exists( 'ideapark_cart_info' ) ) {
	function ideapark_cart_info() {
		global $woocommerce;

		if ( isset( $woocommerce->cart ) ) {
			$cart_total = $woocommerce->cart->get_cart_total();
			$cart_count = $woocommerce->cart->get_cart_contents_count();

			return '<span class="js-cart-info">'
			       . ( ! $woocommerce->cart->is_empty() ? ideapark_wrap( esc_html( $cart_count ), '<span class="c-header__cart-count js-cart-count">', '</span>' ) : '' )
			       . ( ! $woocommerce->cart->is_empty() ? ideapark_wrap( $cart_total, '<span class="c-header__cart-sum">', '</span>' ) : '' ) .
			       '</span>';
		}
	}
}

if ( ! function_exists( 'ideapark_wishlist_info' ) ) {
	function ideapark_wishlist_info() {

		if ( ideapark_mod( 'wishlist_page' ) ) {
			$count = sizeof( ideapark_wishlist()->ids() );
		} else {
			$count = 0;
		}

		return '<span class="js-wishlist-info">'
		       . ( $count ? ideapark_wrap( $count, '<span class="c-header__cart-count">', '</span>' ) : '' ) .
		       '</span>';
	}
}

if ( ! function_exists( 'ideapark_header_add_to_cart_hash' ) ) {
	function ideapark_header_add_to_cart_hash( $hash ) {
		return $hash . ( function_exists( 'ideapark_wishlist' ) ? substr( implode( ',', ideapark_wishlist()->ids() ), 0, 8 ) : '' );
	}
}

if ( ! function_exists( 'ideapark_header_add_to_cart_fragment' ) ) {
	function ideapark_header_add_to_cart_fragment( $fragments ) {
		$fragments['.js-cart-info']     = ideapark_cart_info();
		$fragments['.js-wishlist-info'] = ideapark_wishlist_info();
		ob_start();
		wc_print_notices();
		$fragments['ideapark_notice'] = ob_get_clean();

		return $fragments;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_show_product_loop_badges' ) ) {
	function ideapark_woocommerce_show_product_loop_badges() {
		/**
		 * @var $product WC_Product
		 **/
		global $product;

		if ( $title = get_post_meta( $product->get_id(), 'ideapark_custom_badge_tab_title', true ) ) {

			$color    = get_post_meta( $product->get_id(), 'ideapark_custom_badge_tab_color', true );
			$bg_color = get_post_meta( $product->get_id(), 'ideapark_custom_badge_tab_bg_color', true );
			echo '<span class="c-badge c-badge--custom" ' . ( $color || $bg_color ? ( ' style="' . ( $color ? 'color:' . esc_attr( $color ) . ';' : '' ) . ( $bg_color ? 'background-color:' . esc_attr( $bg_color ) . ';' : '' ) . '"' ) : '' ) . '>' . esc_html( $title ) . '</span>';
		}

		if ( ideapark_mod( 'featured_badge_text' ) && $product->is_featured() ) {
			echo '<span class="c-badge c-badge--featured">' . esc_html( ideapark_mod( 'featured_badge_text' ) ) . '</span>';
		}

		$newness = (int) ideapark_mod( 'product_newness' );

		if ( ideapark_mod( 'new_badge_text' ) && $newness > 0 ) {
			$postdate      = get_the_time( 'Y-m-d' );
			$postdatestamp = strtotime( $postdate );
			if ( ( time() - ( 60 * 60 * 24 * $newness ) ) < $postdatestamp ) {
				echo '<span class="c-badge c-badge--new">' . esc_html( ideapark_mod( 'new_badge_text' ) ) . '</span>';
			}
		}
	}
}

if ( ! function_exists( 'ideapark_woocommerce_breadcrumbs' ) ) {
	function ideapark_woocommerce_breadcrumbs() {
		return [
			'delimiter'   => '',
			'wrap_before' => '<nav class="c-breadcrumbs"><ol class="c-breadcrumbs__list">',
			'wrap_after'  => '</ol></nav>',
			'before'      => '<li class="c-breadcrumbs__item">',
			'after'       => '</li>',
			'home'        => esc_html_x( 'Home', 'breadcrumb', 'woocommerce' ),
		];
	}
}

if ( ! function_exists( 'ideapark_woocommerce_get_brands_breadcrumb' ) ) {
	function ideapark_woocommerce_get_brands_breadcrumb( $crumbs, $obj ) {
		if ( is_tax() && ideapark_mod( 'brands_page' ) && ideapark_mod( 'product_brand_attribute' ) ) {
			if (
				( $this_term = $GLOBALS['wp_query']->get_queried_object() ) &&
				! is_wp_error( $this_term ) &&
				! empty( $crumbs[1] ) &&
				$this_term->taxonomy == ideapark_mod( 'product_brand_attribute' ) &&
				( $page_id = apply_filters( 'wpml_object_id', ideapark_mod( 'brands_page' ), 'any' ) )
			) {
				$crumbs[1][0] = get_the_title( $page_id );
				$crumbs[1][1] = get_permalink( $page_id );
			}
		}

		return $crumbs;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_account_menu_items' ) ) {
	function ideapark_woocommerce_account_menu_items( $items ) {
		unset( $items['customer-logout'] );

		return $items;
	}
}

if ( ! function_exists( 'ideapark_product_availability' ) ) {
	function ideapark_product_availability() {
		global $product;

		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
			$availability = $product->get_availability();
			if ( $product->is_in_stock() ) {
				$availability_html = '<span class="c-stock c-stock--in-stock ' . esc_attr( $availability['class'] ) . '">' . ( $availability['availability'] ? esc_html( $availability['availability'] ) : esc_html__( 'In stock', 'goldish' ) ) . '</span>';
			} else {
				$availability_html = '<span class="c-stock c-stock--out-of-stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</span>';
			}
		} else {
			$availability_html = '';
		}

		echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
	}
}

if ( ! function_exists( 'ideapark_remove_product_description_heading' ) ) {
	function ideapark_remove_product_description_heading() {
		return '';
	}
}

if ( ! function_exists( 'ideapark_woocommerce_search_form' ) ) {
	function ideapark_woocommerce_search_form() {
		if ( is_search() ) {
			echo '<div class="c-product-search-form">';
			get_search_form();
			echo '</div>';
		}
	}
}

if ( ! function_exists( 'ideapark_woocommerce_max_srcset_image_width_768' ) ) {
	function ideapark_woocommerce_max_srcset_image_width_768( $max_width, $size_array ) {
		return 768;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_max_srcset_image_width_360' ) ) {
	function ideapark_woocommerce_max_srcset_image_width_360( $max_width, $size_array ) {
		return 360;
	}
}

if ( ! function_exists( 'ideapark_subcategory_archive_thumbnail_size' ) ) {
	function ideapark_subcategory_archive_thumbnail_size( $thumbnail_size ) {
		return 'woocommerce_gallery_thumbnail';
	}
}

if ( ! function_exists( 'ideapark_loop_add_to_cart_link' ) ) {
	function ideapark_loop_add_to_cart_link( $text, $product, $args ) {
		$text = preg_replace( '~(<a[^>]+>)~ui', '\\1<span class="c-product-grid__atc-text">', $text );
		$text = preg_replace( '~(</a>)~ui', '</span>' . '\\1', $text );
		if ( $product->get_type() == 'simple' ) {
			return preg_replace( '~(<a[^>]+>)~ui', '\\1<i class="ip-plus c-product-grid__atc-icon"></i>', $text );
		} else {
			return preg_replace( '~(</a>)~ui', '<i class="ip-button-more c-product-grid__atc-icon"></i>' . '\\1', $text );
		}
	}
}

if ( ! function_exists( 'ideapark_woocommerce_gallery_image_size' ) ) {
	function ideapark_woocommerce_gallery_image_size( $size ) {
		return 'full';
	}
}

if ( ! function_exists( 'ideapark_get_filtered_term_product_counts' ) ) {
	function ideapark_get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type, $tax_query = null, $meta_query = null ) {
		global $wpdb;

		if ( $tax_query === null ) {
			$tax_query = WC_Query::get_main_tax_query();
		}

		if ( $meta_query === null ) {
			$meta_query = WC_Query::get_main_meta_query();
		}

		if ( 'or' === $query_type ) {
			foreach ( $tax_query as $key => $query ) {
				if ( is_array( $query ) && $taxonomy === $query['taxonomy'] ) {
					unset( $tax_query[ $key ] );
				}
			}
		}

		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		// Generate query.
		$query           = [];
		$query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
		$query['from']   = "FROM {$wpdb->posts}";
		$query['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];

		$query['where'] = "
			WHERE {$wpdb->posts}.post_type IN ( 'product' )
			AND {$wpdb->posts}.post_status = 'publish'"
		                  . $tax_query_sql['where'] . $meta_query_sql['where'] .
		                  'AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';

		if ( ! empty( WC_Query::$query_vars ) ) {
			$search = WC_Query::get_main_search_query_sql();
			if ( $search ) {
				$query['where'] .= ' AND ' . $search;
			}
		}

		$query['group_by'] = 'GROUP BY terms.term_id';
		$query             = implode( ' ', $query );

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = md5( $query );

		// Maybe store a transient of the count values.
		$cache = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', true );
		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ) );
		} else {
			$cached_counts = [];
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {
			$results                      = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine
			$counts                       = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
			$cached_counts[ $query_hash ] = $counts;
			if ( true === $cache ) {
				set_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ), $cached_counts, DAY_IN_SECONDS );
			}
		}

		return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
	}
}

if ( ! function_exists( 'ideapark_woocommerce_loop_add_to_cart_args' ) ) {
	function ideapark_woocommerce_loop_add_to_cart_args( $args ) {

		$args['class'] = 'h-cb c-product-grid__atc ' . $args['class'];

		return $args;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_available_variation' ) ) {
	function ideapark_woocommerce_available_variation( $params, $instance, $variation ) {

		$image = wp_get_attachment_image_src( $params['image_id'], 'woocommerce_single' );
		if ( ! empty( $image ) ) {
			$params['image']['gallery_thumbnail_src'] = $image[0];
		}

		return $params;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_pagination_args' ) ) {
	function ideapark_woocommerce_pagination_args( $args ) {
		$args['prev_text'] = ideapark_pagination_prev();
		$args['next_text'] = ideapark_pagination_next();
		$args['end_size']  = 1;
		$args['mid_size']  = 1;

		return $args;
	}
}

if ( ! function_exists( 'ideapark_ajax_product_images' ) ) {
	function ideapark_ajax_product_images() {
		ob_start();

		if ( isset( $_REQUEST['product_id'] ) && ( $product_id = absint( $_REQUEST['product_id'] ) ) ) {
			$variation_id   = isset( $_REQUEST['variation_id'] ) ? absint( $_REQUEST['variation_id'] ) : 0;
			$index          = isset( $_REQUEST['index'] ) ? absint( $_REQUEST['index'] ) : 0;
			$product_images = ideapark_product_images( $product_id, $variation_id );
			$images         = [];
			foreach ( $product_images as $_index => $image ) {
				if ( ! empty( $image['video_url'] ) ) {
					if ( preg_match( '~youtube\.com/shorts/([^/?#]+)~', $image['video_url'], $match ) ) {
						$image['video_url'] = 'https://www.youtube.com/watch?v=' . $match[1];
					}
					$image_html = do_shortcode( '[video ' . ( $_index == $index ? 'autoplay="on"' : '' ) . ' src="' . esc_url( trim( $image['video_url'] ) ) . '"]' );
					if ( strpos( $image_html, 'wp-embedded-video' ) ) {
						$image_html = wp_oembed_get( $image['video_url'] );
					}
					$images[] = [
						'html' => ideapark_wrap( $image_html, '<div class="pswp__video-wrap">', '</div>' )
					];
				} else {
					$images[] = [
						'src' => $image['full'][0],
						'w'   => $image['full'][1],
						'h'   => $image['full'][2],
					];
				}
			}

			ob_end_clean();
			wp_send_json( [ 'images' => $images ] );
		}
		ob_end_clean();
	}
}

if ( ! function_exists( 'ideapark_ajax_product' ) ) {
	function ideapark_ajax_product() {
		global $woocommerce, $product, $post;
		if ( $lang = ideapark_query_lang() ) {
			do_action( 'wpml_switch_language', $lang );
		}
		if (
			ideapark_woocommerce_on() &&
			ideapark_mod( 'shop_modal' ) &&
			! empty( $_POST['product_id'] ) &&
			( $product_id = (int) $_POST['product_id'] ) &&
			( $product = wc_get_product( $_POST['product_id'] ) ) &&
			( $post = get_post( $_POST['product_id'] ) )
		) {
			setup_postdata( $post );
			wc_get_template_part( 'content', 'quickview' );
			wp_reset_postdata();
		}
		exit;
	}
}

if ( ! function_exists( 'ideapark_ajax_attribute_hint' ) ) {
	function ideapark_ajax_attribute_hint() {
		if ( $lang = ideapark_query_lang() ) {
			do_action( 'wpml_switch_language', $lang );
		}
		if (
			ideapark_woocommerce_on() &&
			! empty( $_POST['attribute_id'] ) &&
			( $attr_id = (int) $_POST['attribute_id'] ) &&
			( $hint = get_option( "wc_attribute_hint-$attr_id" ) ) &&
			( $html_block_id = get_option( "wc_attribute_html_block_id-$attr_id" ) ) &&
			( $page_id = apply_filters( 'wpml_object_id', $html_block_id, 'any' ) ) &&
			( 'publish' == ideapark_post_status( $page_id ) )
		) {
			global $post;
			if ( ideapark_is_elementor_page( $page_id ) ) {
				$page_content = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $page_id );
			} elseif ( $post = get_post( $page_id ) ) {
				$page_content = apply_filters( 'the_content', $post->post_content );
				$page_content = str_replace( ']]>', ']]&gt;', $page_content );
				$page_content = ideapark_wrap( $page_content, '<div class="entry-content">', '</div>' );
				wp_reset_postdata();
			} else {
				$page_content = '';
			}
			echo ideapark_wrap( $page_content, '<div class="l-section">', '</div>' );
		}
		die();
	}
}

if ( ! function_exists( 'ideapark_woocommerce_before_widget_product_list' ) ) {
	function ideapark_woocommerce_before_widget_product_list( $content ) {
		return str_replace( 'product_list_widget', 'c-product-list-widget', $content );
	}
}

if ( ! function_exists( 'ideapark_wp_scrset_on' ) ) {
	function ideapark_wp_scrset_on( $name = '' ) {
		$f = 'add_filter';
		$n = 'wp_calculate_image_' . 'srcset';
		call_user_func( $f, $n, 'ideapark_woocommerce_srcset' . ( $name ? '_' : '' ) . $name, 10, 5 );
	}
}

if ( ! function_exists( 'ideapark_wp_scrset_off' ) ) {
	function ideapark_wp_scrset_off( $name = '' ) {
		$f = 'remove_filter';
		$n = 'wp_calculate_image_' . 'srcset';
		call_user_func( $f, $n, 'ideapark_woocommerce_srcset' . ( $name ? '_' : '' ) . $name, 10 );
	}
}

if ( ! function_exists( 'ideapark_wp_max_scrset_on' ) ) {
	function ideapark_wp_max_scrset_on( $name = '' ) {
		$f = 'add_filter';
		$n = 'max_srcset_image_' . 'width';
		call_user_func( $f, $n, 'ideapark_woocommerce_max_srcset_image_width' . ( $name ? '_' : '' ) . $name, 10, 2 );
	}
}

if ( ! function_exists( 'ideapark_wp_max_scrset_off' ) ) {
	function ideapark_wp_max_scrset_off( $name = '' ) {
		$f = 'remove_filter';
		$n = 'max_srcset_image_' . 'width';
		call_user_func( $f, $n, 'ideapark_woocommerce_max_srcset_image_width' . ( $name ? '_' : '' ) . $name, 10 );
	}
}

if ( ! function_exists( 'ideapark_woocommerce_srcset_grid' ) ) {
	function ideapark_woocommerce_srcset_grid( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		foreach ( $sources as $width => $data ) {
			if ( $width != $size_array[0] && $width != $size_array[0] * 2 ) {
				unset( $sources[ $width ] );
			}
		}

		return $sources;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_srcset_retina' ) ) {
	function ideapark_woocommerce_srcset_retina( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		foreach ( $sources as $width => $data ) {
			if ( $width != $size_array[0] && $width != $size_array[0] * 2 ) {
				unset( $sources[ $width ] );
			}
		}

		return $sources;
	}
}

if ( ! function_exists( 'ideapark_product_images' ) ) {
	function ideapark_product_images( $product_id = 0, $variation_id = 0 ) {
		global $product;

		if ( ! $product_id ) {
			$product_id = $product->get_id();
		} else {
			$product = wc_get_product( $product_id );
		}
		$image_size = ideapark_mod( 'product_page_layout' ) == 'layout-1' ? 'large' : 'full';
		$images     = [];
		if ( ! ( $variation_id && ( $attachment_ids = get_post_meta( $variation_id, 'ideapark_variation_images', true ) ) ) ) {
			$attachment_ids = $product->get_gallery_image_ids();
		}
		if ( ! is_array( $attachment_ids ) ) {
			$attachment_ids = [];
		}
		if ( get_post_meta( $product_id, '_thumbnail_id', true ) ) {
			if ( $variation_id && ( $attachment_id = get_post_thumbnail_id( $variation_id ) ) ) {
				array_unshift( $attachment_ids, $attachment_id );
			} else {
				array_unshift( $attachment_ids, get_post_thumbnail_id( $product_id ) );
			}
		}

		if ( $attachment_ids ) {

			add_filter( 'wp_lazy_loading_enabled', '__return_false', 100 );
			foreach ( $attachment_ids as $attachment_id ) {
				try {
					$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
					if ( ! $alt ) {
						$attachment = get_post( $attachment_id );
						if ( ! $attachment ) {
							$alt = '';
						} else {
							$alt = $attachment->post_excerpt;
							if ( ! $alt ) {
								$alt = $attachment->post_title;
							}
						}
					}

					$image = wp_get_attachment_image( $attachment_id, $image_size, false, [
						'alt'   => $alt,
						'class' => 'c-product__slider-img c-product__slider-img--' . ideapark_mod( 'product_image_fit' )
					] );

					$full = wp_get_attachment_image_src( $attachment_id, 'full' );

					$thumb = wp_get_attachment_image( $attachment_id, 'woocommerce_gallery_thumbnail', false, [
						'alt'   => $alt,
						'class' => 'c-product__thumbs-img'
					] );

					$images[] = [
						'attachment_id' => $attachment_id,
						'image'         => $image,
						'full'          => $full,
						'thumb'         => $thumb,
						'alt'			=> $alt
					];
				} catch ( Exception $e ) {
				}
			}
			ideapark_rf( 'wp_lazy_loading_enabled', '__return_false', 100 );
		}

		if ( $video_url = get_post_meta( $product_id, '_ip_product_video_url', true ) ) {

			$is_youtube_preview = false;
			if ( $video_thumb_id = get_post_meta( $product_id, '_ip_product_video_thumb', true ) ) {
				$thumb_url = ( $image = wp_get_attachment_image_src( $video_thumb_id, 'woocommerce_gallery_thumbnail' ) ) ? $image[0] : '';
				$image_url = ( $image = wp_get_attachment_image_src( $video_thumb_id, $image_size ) ) ? $image[0] : '';
				$full_url  = ( $image = wp_get_attachment_image_src( $video_thumb_id, 'full' ) ) ? $image[0] : '';
			} else {
				$pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
				if ( preg_match( $pattern, $video_url, $match ) ) {
					$image_url          = 'https://img.youtube.com/vi/' . $match[1] . '/maxresdefault.jpg';
					$thumb_url          = 'https://img.youtube.com/vi/' . $match[1] . '/default.jpg';
					$full_url           = 'https://img.youtube.com/vi/' . $match[1] . '/maxresdefault.jpg';
					$is_youtube_preview = true;
				} else {
					$image_url = '';
					$thumb_url = '';
					$full_url  = '';
				}
			}
			$video = [
				'thumb_url'          => $thumb_url,
				'image_url'          => $image_url,
				'full_url'           => $full_url,
				'video_url'          => $video_url,
				'is_youtube_preview' => $is_youtube_preview,
			];

			if ( sizeof( $images ) >= ideapark_mod( 'product_video_position' ) ) {
				array_splice( $images, ideapark_mod( 'product_video_position' ) - 1, 0, [ $video ] );
			} else {
				$images[] = $video;
			}
		}

		if ( in_array( 'crc32c', hash_algos() ) ) {
			ideapark_mod_set_temp( '_images_hash', hash( 'crc32c', json_encode( $images ) ) );
		}

		return $images;
	}
}

if ( ! function_exists( 'ideapark_product_wishlist' ) ) {
	function ideapark_product_wishlist() {
		if ( ideapark_mod( 'wishlist_page' ) ) { ?>
			<div
				class="c-product__wishlist"><?php Ideapark_Wishlist()->ideapark__button( 'h-cb c-product__wishlist-button', 'c-product__wishlist-icon', 'c-product__wishlist-text', __( 'Add to Wishlist', 'goldish' ), __( 'Remove from Wishlist', 'goldish' ), 'ip-heart-sm', 'ip-heart-sm-active', '12px' ) ?></div>
		<?php }
	}
}

if ( ! function_exists( 'ideapark_product_share' ) ) {
	function ideapark_product_share() {
		if ( ideapark_mod( 'product_share' ) && shortcode_exists( 'ip-post-share' ) && ( $content = ideapark_shortcode( '[ip-post-share]' ) ) ) { ?>
			<div class="c-product__share">
				<i class="ip-share c-product__share-icon"></i>
				<div class="c-product__share-title"><?php esc_html_e( 'Share', 'goldish' ); ?></div>
				<?php echo ideapark_wrap( $content ); ?>
			</div>
		<?php }
	}
}

if ( ! function_exists( 'ideapark_add_to_cart_ajax_notice' ) ) {
	function ideapark_add_to_cart_ajax_notice( $product_id ) {
		wc_add_to_cart_message( $product_id );
	}
}

if ( ! function_exists( 'ideapark_woocommerce_demo_store' ) ) {
	function ideapark_woocommerce_demo_store( $notice ) {
		global $post;
		if ( ideapark_is_elementor_preview_mode() && ( $post instanceof WP_Post ) && $post->post_type == 'html_block' ) {
			return '';
		} else {
			return str_replace( 'woocommerce-store-notice ', 'woocommerce-store-notice woocommerce-store-notice--' . ideapark_mod( 'store_notice' ) . ' ', $notice );
		}
	}
}

if ( ! function_exists( 'ideapark_woocommerce_product_tabs' ) ) {
	function ideapark_woocommerce_product_tabs( $tabs ) {
		$theme_tabs = ideapark_parse_checklist( ideapark_mod( 'product_tabs' ) );
		$priority   = 10;
		foreach ( $theme_tabs as $theme_tab_index => $enabled ) {
			if ( array_key_exists( $theme_tab_index, $tabs ) ) {
				if ( $enabled ) {
					$tabs[ $theme_tab_index ]['priority'] = $priority;
				} else {
					unset( $tabs[ $theme_tab_index ] );
				}
			}
			$priority += 10;
		}

		return $tabs;
	}
}

if ( ! function_exists( 'ideapark_stock_badge' ) ) {
	function ideapark_stock_badge() {
		global $product;
		/**
		 * @var $product WC_Product
		 */

		$availability = $product->get_availability();
		if ( ! ( $product->is_in_stock() || $product->is_on_backorder() ) ) {
			$availability_html = '<span class="c-badge c-badge--out-of-stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( ideapark_mod( 'outofstock_badge_text' ) ) . '</span>';
			echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
		}
	}
}

if ( ! function_exists( 'ideapark_brands' ) ) {
	function ideapark_brands() {
		global $product;
		if (
			( $brand_taxonomy = ideapark_mod( 'product_brand_attribute' ) ) &&
			( $attributes = $product->get_attributes() ) &&
			is_array( $attributes ) &&
			array_key_exists( $brand_taxonomy, $attributes ) &&
			is_object( $attributes[ $brand_taxonomy ] )
		) {
			$terms = $attributes[ $brand_taxonomy ]->get_terms();

			return is_wp_error( $terms ) ? [] : $terms;
		}
	}
}

if ( ! function_exists( 'ideapark_grid_text_class' ) ) {
	function ideapark_grid_text_class( $class_base ) {
		ob_start();
		if ( in_array( ideapark_mod( '_product_layout' ), [
			'2-per-row',
			'3-per-row',
			'4-per-row'
		] ) ) { ?><?php echo ' ' . esc_attr( $class_base ); ?>--<?php echo ideapark_mod( 'product_grid_text_desktop' ); ?><?php } ?><?php if ( in_array( ideapark_mod( '_product_layout_mobile' ), [
			'1-per-row-mobile',
			'2-per-row-mobile'
		] ) ) { ?><?php echo ' ' . esc_attr( $class_base ); ?>--<?php echo ideapark_mod( 'product_grid_text_mobile' ); ?><?php }

		return ob_get_clean();
	}
}

if ( ! function_exists( 'ideapark_subcat_class' ) ) {
	function ideapark_subcat_class( $classes = [], $class = '', $category = null ) {
		$classes[] = ideapark_mod( 'show_subcat_in_header' ) || ideapark_mod( '_is_header_subcat' ) || ideapark_mod( '_current_product_category' ) ? 'c-page-header__sub-cat-item' : ( 'c-sub-categories__item c-sub-categories__item--' . ( ideapark_mod( '_with_sidebar' ) ? 'sidebar' : 'no-sidebar' ) . ' c-sub-categories__item--' . ideapark_mod( 'product_grid_width' ) );
		if ( ( is_product_category() && $category && $category->term_id == get_queried_object_id() ) || ( $category && $category->term_id == ideapark_mod( '_current_product_category' ) ) ) {
			$classes[] = 'c-page-header__sub-cat-item--current';
		}

		return $classes;
	}
}

if ( ! function_exists( 'ideapark_header_categories' ) ) {
	function ideapark_header_categories( $_parent_id = null ) {
		global $post;
		if ( ideapark_woocommerce_on() && ( $_parent_id !== null || ideapark_mod( 'show_subcat_in_header' ) && ( is_shop() || is_product_category() ) || is_product() && ideapark_mod( 'product_category_carousel' ) ) ) {
			$placeholder = function ( $image ) {
				return $image ?: [ '', 0, 0 ];
			};
			add_filter( 'wp_get_attachment_image_src', $placeholder );
			$is_parent        = false;
			$parent_id        = $_parent_id ?: ( is_product_category() ? get_queried_object_id() : 0 );
			$parent_parent_id = 0;
			if ( is_product() && ! $parent_id ) {
				$terms = wc_get_product_terms(
					$post->ID,
					'product_cat',
					apply_filters(
						'woocommerce_breadcrumb_product_terms_args',
						[
							'orderby' => 'parent',
							'order'   => 'DESC',
						]
					)
				);

				if ( $terms ) {
					$main_term        = apply_filters( 'woocommerce_breadcrumb_main_term', $terms[0], $terms );
					$parent_id        = $main_term->term_id;
					$parent_parent_id = $main_term->parent;
					ideapark_mod_set_temp( '_current_product_category', $parent_id );
				}
			}
			do {
				ob_start();
				ideapark_mod_set_temp( '_is_header_subcat', true );
				woocommerce_output_product_categories(
					[ 'parent_id' => $parent_id ]
				);
				ideapark_mod_set_temp( '_is_header_subcat', false );
				$loop_html = ob_get_clean();
				if ( ! $loop_html ) {
					if ( $is_parent ) {
						break;
					} elseif ( $parent_id ) {
						$parent_id = $parent_parent_id ?: get_queried_object()->parent;
						$is_parent = true;
					} else {
						break;
					}
				} elseif ( $parent_id && ! $_parent_id ) {
					$term_id = $parent_parent_id ?: get_queried_object()->parent;
					$title   = '';
					if ( $term_id ) {
						$term = get_term( $term_id );
						if ( $term && ! is_wp_error( $term ) ) {
							$title = $term->name;
							$link  = get_term_link( (int) $term->term_id );
						}
					} elseif ( $shop_page_id = wc_get_page_id( 'shop' ) ) {
						$title = get_the_title( $shop_page_id );
						$link  = get_permalink( $shop_page_id );
					}
					if ( $title ) {
						$loop_html = '<div class="c-page-header__sub-cat-item product-category product first"><a href="' . esc_url( $link ) . '"><span class="c-sub-categories__thumb-wrap c-sub-categories__thumb-wrap--header c-sub-categories__thumb-wrap--back"><i class="ip-arrow-long c-sub-categories__back"></i></span><h2 class="woocommerce-loop-category__title">' . esc_html( $title ) . '</h2></a></div>' . str_replace( 'product first', '', $loop_html );
					}
				}
			} while ( ! $loop_html );

			$subcategories = apply_filters( 'ideapark_page_header_subcat', $loop_html );
			if ( $subcategories ) {
				$count = preg_match_all( '~c-page-header__sub-cat-item ~', $subcategories, $matches, PREG_SET_ORDER ) ? sizeof( $matches ) : 0;
				echo ideapark_wrap( $subcategories, '<div class="c-page-header__sub-cat' . ( $_parent_id !== null ? ' c-page-header__sub-cat--widget' : '' ) . '"><div class="c-page-header__sub-cat-list c-page-header__sub-cat-list--' . ideapark_mod( 'subcat_in_header_layout' ) . ( $count > 6 ? ' c-page-header__sub-cat-list--carousel ' : '' ) . ( ideapark_mod( 'show_subcat_in_header_nav' ) ? ' h-carousel--nav-mobile' : '' ) . ' js-header-subcat h-carousel h-carousel--dots-hide h-carousel--flex">', '</div></div>' );
			}
			remove_filter( 'wp_get_attachment_image_src', $placeholder );

			return ! ! $subcategories;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'ideapark_attribute_hint' ) ) {
	function ideapark_attribute_hint() {
		$add_func  = function () {
			$id            = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
			$hint          = $id ? get_option( "wc_attribute_hint-$id" ) : '';
			$html_block_id = $id ? get_option( "wc_attribute_html_block_id-$id" ) : 0;
			$dropdown      = wp_dropdown_pages(
				[
					'name'              => 'attribute_html_block_id',
					'echo'              => 0,
					'show_option_none'  => '&mdash; ' . esc_html__( 'Select', 'goldish' ) . ' &mdash;',
					'option_none_value' => '0',
					'selected'          => $html_block_id,
					'post_type'         => 'html_block',
					'post_status'       => [ 'publish' ],
				]
			);
			?>
			<div class="form-field">
				<label for="attribute_hint"><?php esc_html_e( 'Hint title', 'goldish' ); ?></label>
				<input name="attribute_hint" type="text" id="attribute_hint" value="<?php echo esc_attr( $hint ); ?>"/>
				<p class="description"><?php esc_html_e( 'Fill in these two fields if you need a popup hint for the attribute. For example, "Size guide".', 'goldish' ); ?></p>
			</div>
			<div class="form-field">
				<label
					for="attribute_html_block_id"><?php esc_html_e( 'Hint content (HTML block)', 'goldish' ); ?></label>
				<?php echo ideapark_wrap( $dropdown ) ?>
				<div class="ideapark-manage-blocks"><a
						href="<?php echo esc_url( admin_url( 'edit.php?post_type=html_block' ) ); ?>"><?php echo esc_html__( 'Manage html blocks', 'goldish' ); ?></a>
				</div>
			</div>
			<?php
		};
		$edit_func = function () {
			$id            = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
			$hint          = $id ? get_option( "wc_attribute_hint-$id" ) : '';
			$html_block_id = $id ? get_option( "wc_attribute_html_block_id-$id" ) : 0;
			$dropdown      = wp_dropdown_pages(
				[
					'name'              => 'attribute_html_block_id',
					'echo'              => 0,
					'show_option_none'  => '&mdash; ' . esc_html__( 'Select', 'goldish' ) . ' &mdash;',
					'option_none_value' => '0',
					'selected'          => $html_block_id,
					'post_type'         => 'html_block',
					'post_status'       => [ 'publish' ],
				]
			);
			?>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="attribute_hint"><?php esc_html_e( 'Hint title', 'goldish' ); ?></label>
				</th>
				<td>
					<input name="attribute_hint" type="text" id="attribute_hint"
						   value="<?php echo esc_attr( $hint ); ?>"/>
					<p class="description"><?php esc_html_e( 'Fill in these two fields if you need a popup hint for the attribute. For example, "Size guide".', 'goldish' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label
						for="attribute_html_block_id"><?php esc_html_e( 'Hint content (HTML block)', 'goldish' ); ?></label>
				</th>

				<td>
					<?php echo ideapark_wrap( $dropdown ) ?>
					<div class="ideapark-manage-blocks"><a
							href="<?php echo esc_url( admin_url( 'edit.php?post_type=html_block' ) ); ?>"><?php echo esc_html__( 'Manage html blocks', 'goldish' ); ?></a>
					</div>
				</td>
			</tr>
			<?php
		};
		add_action( 'woocommerce_after_add_attribute_fields', $add_func, 99 );
		add_action( 'woocommerce_after_edit_attribute_fields', $edit_func, 99 );

		$save_func = function ( $id ) {
			$option = "wc_attribute_hint-$id";
			if ( is_admin() && isset( $_POST['attribute_hint'] ) ) {
				update_option( $option, $value = sanitize_text_field( trim( $_POST['attribute_hint'] ) ) );
				do_action( 'wpml_register_single_string', IDEAPARK_SLUG, 'Hint title - ' . $value, $value );
			} else {
				delete_option( $option );
			}
			$option = "wc_attribute_html_block_id-$id";
			if ( is_admin() && isset( $_POST['attribute_html_block_id'] ) ) {
				update_option( $option, sanitize_text_field( $_POST['attribute_html_block_id'] ) );
			} else {
				delete_option( $option );
			}
		};
		add_action( 'woocommerce_attribute_added', $save_func );
		add_action( 'woocommerce_attribute_updated', $save_func );

		add_action( 'woocommerce_attribute_deleted', function ( $id ) {
			delete_option( "wc_attribute_hint-$id" );
			delete_option( "wc_attribute_html_block_id-$id" );
		} );
	}
}

if ( ! function_exists( 'ideapark_WPML_attribute_title' ) ) {
	function ideapark_WPML_attribute_title( $id, $data ) {
		if ( is_array( $data ) && isset( $data['attribute_label'] ) ) {
			do_action( 'wpml_register_single_string', IDEAPARK_NAME, "attribute name: " . $data['attribute_label'], $data['attribute_label'] );
		}
	}
}

if ( ! function_exists( 'ideapark_grid_color_attributes_prepare' ) ) {
	function ideapark_grid_color_attributes_prepare() {
		/**
		 * @var $product   WC_Product
		 * @var $attribute WC_Product_Attribute
		 **/
		global $product;
		static $types = [];
		$taxonomy   = ideapark_mod( 'product_color_attribute' );
		$attributes = $product->get_attributes();

		$items = [];

		ideapark_mod_set_temp( '_archive_attribute_color_list', null );
		ideapark_mod_set_temp( '_archive_attribute_items', null );

		if ( array_key_exists( $taxonomy, $attributes ) && is_object( $attributes[ $taxonomy ] ) ) {
			$attribute = $attributes[ $taxonomy ];
			if ( $attribute->get_variation() && $product->is_type( 'variable' ) && ( $variations = $product->get_available_variations() ) ) {
				foreach ( $variations as $variation ) {
					if ( ! empty( $variation['attributes'][ 'attribute_' . $taxonomy ] ) && ! empty( $variation['image'] ) && ! empty( $variation['variation_is_visible'] ) && ! empty( $variation['variation_is_active'] ) ) {
						$value = $variation['attributes'][ 'attribute_' . $taxonomy ];
						if ( ( $term = get_term_by( 'slug', $value, $taxonomy ) ) && ! array_key_exists( $term->term_id, $items ) ) {
							$items[ $term->term_id ] = [
								'src'      => $variation['image']['src'],
								'srcset'   => $variation['image']['srcset'],
								'image_id' => $variation['image_id'],
								'term'     => $term,
							];
						}
					}
				}
			} elseif ( $terms = get_the_terms( $product->get_id(), $taxonomy ) ) {
				foreach ( $terms as $term ) {
					$items[ $term->term_id ] = [
						'term' => $term
					];
				}
			}

			if ( $items && ( sizeof( $items ) > 1 || ! ideapark_mod( 'hide_single_color_variations' ) ) ) {
				uasort( $items, function ( $a, $b ) {
					if ( $a['term']->term_order == $b['term']->term_order ) {
						return 0;
					}

					return ( $a['term']->term_order < $b['term']->term_order ) ? - 1 : 1;
				} );
				$type = '';
				if ( isset( $types[ $taxonomy ] ) ) {
					$type = $types[ $taxonomy ];
				} else {
					$_type = ideapark_get_taxonomy_type( $taxonomy );
					if ( in_array( $_type, [ 'color', 'image' ] ) ) {
						$type = $_type;
					}
					$types[ $taxonomy ] = $type;
				}

				if ( $type ) {
					ob_start();
					?>
					<ul
						class="c-product-grid__color-list c-product-grid__color-list--<?php echo esc_attr( $type ); ?>">
						<?php foreach ( $items as $term_id => $item ) {
							$data_src    = ! empty( $item['src'] ) ? ' data-src="' . esc_attr( $item['src'] ) . '" ' : '';
							$data_srcset = ! empty( $item['srcset'] ) ? ' data-srcset="' . esc_attr( $item['srcset'] ) . '" ' : '';
							switch ( $type ) {
								case 'color':
									$color           = sanitize_hex_color( ideapark_get_product_attribute_color( $item['term'] ) );
									$color_secondary = ( $s_c = ideapark_get_product_attribute_color_secondary( $item['term'] ) ) ? sanitize_hex_color( $s_c ) : '';
									if ( $color && $color_secondary ) {
										$html = sprintf( '<li %s %s class="c-product-grid__color-item c-product-grid__color-item--color %s" style="background: linear-gradient(%6$s, %4$s 0%%, %4$s 50%%, %5$s 50%%, %5$s 100%%);"><span class="c-product-grid__color-title">%7$s</span></li>', $data_src, $data_srcset, $data_src ? 'c-product-grid__color-item--var js-grid-color-var' . ( $term_id == ideapark_mod( '_archive_attribute_id' ) ? ' current' : '' ) : '', esc_attr( $color_secondary ), esc_attr( $color ), apply_filters( 'woo_variation_swatches_dual_color_gradient_angle', '-45deg' ), esc_html( $item['term']->name ) );
									} else {
										$html = sprintf( '<li %s %s class="c-product-grid__color-item c-product-grid__color-item--color %s" style="background-color:%s;"><span class="c-product-grid__color-title">%s</span></li>', $data_src, $data_srcset, $data_src ? 'c-product-grid__color-item--var js-grid-color-var' . ( $term_id == ideapark_mod( '_archive_attribute_id' ) ? ' current' : '' ) : '', esc_attr( $color ), esc_html( $item['term']->name ) );
									}
									break;

								case 'image':
									$attachment_id = absint( ideapark_get_product_attribute_image( $item['term'] ) );
									$image_size    = ideapark_get_wvs_get_option( 'attribute_image_size' );
									$image         = wp_get_attachment_image_src( $attachment_id, $image_size );
									if ( $image ) {
										$html = sprintf( '<li %s %s class="c-product-grid__color-item c-product-grid__color-item--image %s"><img class="c-ip-attribute-filter__thumb" aria-hidden="true" alt="%s" src="%s" width="%d" height="%d" /><span class="c-product-grid__color-title">%s</span></li>', $data_src, $data_srcset, $data_src ? 'c-product-grid__color-item--var js-grid-color-var' . ( $term_id == ideapark_mod( '_archive_attribute_id' ) ? ' current' : '' ) : '', esc_attr( $item['term']->name ), esc_url( $image[0] ), esc_attr( $image[1] ), esc_attr( $image[2] ), esc_html( $item['term']->name ) );
									} else {
										$html = sprintf( '<li %s %s class="c-product-grid__color-item c-product-grid__color-item--image %s"><span class="c-product-grid__color-title">%s</span></li>', $data_src, $data_srcset, $data_src ? 'c-product-grid__color-item--var js-grid-color-var' . ( $term_id == ideapark_mod( '_archive_attribute_id' ) ? ' current' : '' ) : '', esc_html( $item['term']->name ) );
									}
									break;
							}
							echo ideapark_wrap( $html );
						} ?>
					</ul>
				<?php }
				ideapark_mod_set_temp( '_archive_attribute_color_list', ob_get_clean() );
				ideapark_mod_set_temp( '_archive_attribute_items', $items );
			}
		}
	}
}

if ( ! function_exists( 'ideapark_grid_color_attributes' ) ) {
	function ideapark_grid_color_attributes() {
		echo ideapark_mod( '_archive_attribute_color_list' );
	}
}

if ( ! function_exists( 'ideapark_cart_brand' ) ) {
	function ideapark_cart_brand( $cart_item, $cart_item_mini ) {
		/**
		 * @var $product WC_Product
		 **/
		$attribute = ideapark_mod( 'product_brand_attribute' );
		$product   = $cart_item['data'];
		if ( $parent_id = $product->get_parent_id() ) {
			$product = wc_get_product( $parent_id );
		}
		if ( $name = $product->get_attribute( $attribute ) ) {
			echo ideapark_wrap( $name, '<div class="c-cart__shop-brand">', '</div>' );
		}
	}
}

if ( ! function_exists( 'ideapark_cart_mini_brand' ) ) {
	function ideapark_cart_mini_brand( $html, $cart_item, $cart_item_mini ) {
		/**
		 * @var $product WC_Product
		 **/
		$attribute = ideapark_mod( 'product_brand_attribute' );
		$product   = $cart_item['data'];
		if ( $parent_id = $product->get_parent_id() ) {
			$product = wc_get_product( $parent_id );
		}
		if ( $name = $product->get_attribute( $attribute ) ) {
			$html = ideapark_wrap( $name, '<div class="c-product-list-widget__brand">', '</div>' ) . $html;
		}

		return $html;
	}
}

if ( ! function_exists( 'ideapark_ajax_add_to_cart' ) ) {
	function ideapark_ajax_add_to_cart() {
		WC_AJAX::get_refreshed_fragments();
	}
}

if ( ! function_exists( 'ideapark_infinity_paging' ) ) {
	function ideapark_infinity_paging() {

		if ( ! wc_get_loop_prop( 'is_paginated' ) || ! woocommerce_products_will_display() ) {
			return;
		}

		if ( IDEAPARK_IS_AJAX_INFINITY ) {
			ob_start();
		}

		$total   = wc_get_loop_prop( 'total_pages' );
		$current = wc_get_loop_prop( 'current_page' );
		$base    = esc_url_raw( add_query_arg( 'product-page', '%#%', false ) );
		$format  = '?product-page=%#%';

		if ( ! wc_get_loop_prop( 'is_shortcode' ) ) {
			$base   = esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
			$format = '';
		}

		if ( $current < $total ) {
			$link = str_replace( '%_%', $format, $base );
			$link = str_replace( '%#%', $current + 1, $link );
			$link = apply_filters( 'paginate_links', $link );
			if ( ! IDEAPARK_IS_AJAX_INFINITY && $current > 1 ) {
				$link = str_replace( '%_%', $format, $base );
				$link = str_replace( '%#%', $current - 1, $link );
				$link = apply_filters( 'paginate_links', $link ); ?>
				<script>ideapark_redirect_url = '<?php echo esc_url( $link ); ?>';</script>
			<?php } elseif ( ideapark_mod( 'product_grid_pagination' ) == 'loadmore' ) { ?>
				<div class="woocommerce-pagination c-product-grid__load-more-wrap">
					<a href="<?php echo esc_url( $link ); ?>"
					   onclick="return false"
					   class="c-button c-button--outline c-product-grid__load-more js-load-more"><?php echo esc_html( ideapark_mod( 'product_grid_load_more_text' ) ); ?></a>
				</div>
			<?php } elseif ( ideapark_mod( 'product_grid_pagination' ) == 'infinity' ) { ?>
				<div class="woocommerce-pagination c-product-grid__load-more-wrap">
					<span data-href="<?php echo esc_url( $link ); ?>"
						  class="c-product-grid__load-infinity js-load-infinity"></span>
				</div>
			<?php } ?>
		<?php }

		if ( IDEAPARK_IS_AJAX_INFINITY ) {
			ideapark_mod_set_temp( '_infinity_paging', ob_get_clean() );
		}
	}
}

if ( ! function_exists( 'ideapark_infinity_loading' ) ) {
	function ideapark_infinity_loading( $template ) {
		if ( IDEAPARK_IS_AJAX_INFINITY ) {
			$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( empty( $_GET['product-page'] ) ? 1 : $_GET['product-page'] );
			if ( $page > 1 ) {
				if ( strripos( $template, '/archive-product.php' ) !== false || strripos( $template, '/taxonomy-product-cat.php' ) !== false || strripos( $template, '/taxonomy-product-tag.php' ) !== false || strripos( $template, '/taxonomy-product-attribute.php' ) !== false ) {
					$template = IDEAPARK_DIR . '/woocommerce/infinity-product.php';
				} elseif ( ! empty( $_GET['product-page'] ) ) {
					$template = IDEAPARK_DIR . '/woocommerce/infinity-shortcode.php';
				}
			}
		}

		return $template;
	}
}

if ( ! function_exists( 'ideapark_get_taxonomy_type' ) ) {
	function ideapark_get_taxonomy_type( $taxonomy ) {
		if ( ideapark_swatches_plugin_on() && ideapark_woocommerce_on() ) {
			$get_attribute  = woo_variation_swatches()->get_frontend()->get_attribute_taxonomy_by_name( $taxonomy );
			$attribute_type = ( $get_attribute ) ? $get_attribute->attribute_type : '';

			return $attribute_type;
		} else {
			return '';
		}
	}
}

if ( ! function_exists( 'ideapark_get_product_attribute_color' ) ) {
	function ideapark_get_product_attribute_color( $term, $data = [] ) {
		$term_id = 0;
		if ( is_numeric( $term ) ) {
			$term_id = $term;
		}
		if ( is_object( $term ) ) {
			$term_id = $term->term_id;
		}

		return get_term_meta( $term_id, 'product_attribute_color', true );
	}
}

if ( ! function_exists( 'ideapark_get_product_attribute_color_secondary' ) ) {
	function ideapark_get_product_attribute_color_secondary( $term, $data = [] ) {
		$term_id = 0;
		if ( is_numeric( $term ) ) {
			$term_id = $term;
		}
		if ( is_object( $term ) ) {
			$term_id = $term->term_id;
		}

		return wc_string_to_bool( get_term_meta( $term_id, 'is_dual_color', true ) ) ? get_term_meta( $term_id, 'secondary_color', true ) : '';
	}
}

if ( ! function_exists( 'ideapark_get_product_attribute_image' ) ) {
	function ideapark_get_product_attribute_image( $term, $data = [] ) {
		$term_id = 0;
		if ( is_numeric( $term ) ) {
			$term_id = $term;
		}
		if ( is_object( $term ) ) {
			$term_id = $term->term_id;
		}

		return get_term_meta( $term_id, 'product_attribute_image', true );
	}
}

if ( ! function_exists( 'ideapark_recently_container' ) ) {
	function ideapark_recently_container() {
		$product_id   = is_product() ? get_the_ID() : 0;
		$storage_name = 'ip_recently_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() );
		?>
		<section
			id="js-recently-container"
			class="h-hidden c-product__products c-product__products--related c-product__products--<?php echo esc_attr( ideapark_mod( 'product_grid_width' ) ); ?> l-section <?php if ( is_shop() ) {
				ideapark_class( ideapark_mod( 'product_grid_width' ) == 'fullwidth', 'l-section--container-wide', 'l-section--container' );
			} ?>"
			data-number="<?php echo ideapark_mod( 'recently_product_number' ); ?>"
			data-storage-key="<?php echo esc_attr( $storage_name ); ?>"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			data-add-only="<?php if ( $product_id && ! ideapark_mod( 'recently_product_show' ) ) { ?>yes<?php } else { ?>no<?php } ?>">
		</section>
		<script>
			function ideapark_parse_recently(content, container) {
				if (content) {
					var _parser = new DOMParser();
					var _parsed = _parser.parseFromString(content, "text/html");
					if (ideapark_recently_product_id) {
						_parsed.querySelectorAll(".post-" + ideapark_recently_product_id).forEach((_item) => {
							_item.remove();
						});
					}
					var _items = _parsed.querySelectorAll('.c-product-grid__item');
					if (_items.length > ideapark_recently_number) {
						var _index = 0;
						_items.forEach((_item) => {
							_index++;
							if (_index > ideapark_recently_number) {
								_item.remove();
							}
						});
					}
					if (_items.length) {
						var _grid = _parsed.querySelector('.c-product-grid');
						if (_grid) {
							_grid.className = _grid.className.replace(/cnt-\d+/, "cnt-" + _items.length);
						}
						var _wrap = _parsed.querySelector('.c-product-grid__wrap');
						if (_wrap) {
							_wrap.className = _wrap.className.replace(/(cnt)-\d+/, "$1-" + _items.length);
						}
						var _list = _parsed.querySelector('.c-product-grid__list');
						if (_list) {
							_list.className = _list.className.replace(/(cnt)-\d+/, "$1-" + _items.length);
							_list.setAttribute("data-count", _items.length);
						}
						while (_parsed.getRootNode().body.children.length > 0) {
							container.appendChild(_parsed.getRootNode().body.children[0]);
						}
						container.className = container.className.replace('h-hidden', '');
					}
				}
			}

			var ideapark_recently_container = document.getElementById("js-recently-container");
			const ideapark_recently_product_id = parseInt(ideapark_recently_container.dataset.productId);
			const ideapark_recently_number = parseInt(ideapark_recently_container.dataset.number);
			const ideapark_recently_storage_key = ideapark_recently_container.dataset.storageKey;
			const ideapark_recently_add_only = ideapark_recently_container.dataset.addOnly === 'yes';
			var ideapark_supports_html5_storage = false;
			try {
				ideapark_supports_html5_storage = ('localStorage' in window && window.localStorage !== null);
				window.localStorage.setItem('ip', 'test');
				window.localStorage.removeItem('ip');
			} catch (err) {
				ideapark_supports_html5_storage = false;
			}
			if (ideapark_supports_html5_storage && !ideapark_recently_add_only) {
				const content = window.localStorage.getItem(ideapark_recently_storage_key);
				if (content !== null) {
					ideapark_parse_recently(content, ideapark_recently_container);
					ideapark_recently_container = null;
				}
			}
		</script>
		<?php
	}
}

if ( ! function_exists( 'ideapark_ajax_recently' ) ) {
	function ideapark_ajax_recently() {
		if (
			ideapark_woocommerce_on() &&
			ideapark_mod( 'recently_enabled' ) && ideapark_mod( 'recently_product_number' )
		) {
			if ( $lang = ideapark_query_lang() ) {
				do_action( 'wpml_switch_language', $lang );
			}
			$product_id = ! empty( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
			$add_only   = ! empty( $_POST['add_only'] );

			$product_ids = $product_id ? ideapark_recently_add( $product_id ) : ideapark_recently_get();

			if ( ! $add_only ) {
				ideapark_recently_products( $product_ids );
			}
		}
		exit;
	}
}

if ( ! function_exists( 'ideapark_recently_products_shop_only' ) ) {
	function ideapark_recently_products_shop_only() {
		if ( is_shop() ) {
			ideapark_recently_container();
		}
	}
}

if ( ! function_exists( 'ideapark_recently_products' ) ) {
	function ideapark_recently_products( $product_ids ) {
		if ( $product_ids ) {
			wc_set_loop_prop( 'name', 'recently' );
			wc_set_loop_prop( 'columns', 2 );

			wc_get_template( 'single-product/recently.php', [ 'recently_products' => $product_ids ] );
		}
	}
}

if ( ! function_exists( 'ideapark_recently_get' ) ) {
	function ideapark_recently_get() {
		$list = [];
		$key  = '_recently_viewed_products';

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$list    = get_user_meta( $user_id, $key, true );
		} elseif ( ! empty( $_COOKIE[ $key ] ) ) {
			$list = unserialize( stripslashes( $_COOKIE[ $key ] ) );
		}
		if ( ! is_array( $list ) ) {
			$list = [];
		}

		return $list;
	}
}

if ( ! function_exists( 'ideapark_recently_add' ) ) {
	function ideapark_recently_add( $product_id ) {
		$list = ideapark_recently_get();
		array_unshift( $list, $product_id );
		$list     = array_unique( $list );
		$max_size = ideapark_mod( 'recently_product_number' ) + 1;
		if ( sizeof( $list ) > $max_size ) {
			$list = array_slice( $list, 0, $max_size );
		}
		$key = '_recently_viewed_products';
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			update_user_meta( $user_id, $key, $list );
		} else {
			ideapark_set_cookie( $key, serialize( $list ) );
		}

		return $list;
	}
}

if ( ! function_exists( 'ideapark_output_product_data_tabs_fullwidth' ) ) {
	function ideapark_output_product_data_tabs_fullwidth() {
		wc_get_template( 'single-product/tabs/tabs-fullwidth.php' );
	}
}

if ( ! function_exists( 'ideapark_product_features' ) ) {
	function ideapark_product_features() {
		if ( ( $features = ideapark_mod( 'product_feature' ) ) && ( ! empty( $features[0]['name'] ) || ! empty( $features[0]['description'] ) ) ) { ?>
			<div class="c-product-features">
				<ul class="c-product-features__list">
					<?php foreach ( $features as $feature ) { ?>
						<li class="c-product-features__item">
							<?php if ( ! empty( $feature['font-icon'] ) ) { ?><i
								class="c-product-features__icon <?php echo esc_attr( $feature['font-icon'] ); ?>"></i><?php } ?>
							<span class="c-product-features__text">
							<?php echo isset( $feature['name'] ) ? ideapark_wrap( esc_html( apply_filters( 'wpml_translate_single_string', $feature['name'], IDEAPARK_SLUG, 'Product Feature Title - ' . $feature['name'], apply_filters( 'wpml_current_language', null ) ) ), '<span class="c-product-features__name">', '</span>' ) : ''; ?>
							<?php echo isset( $feature['description'] ) ? ideapark_wrap( esc_html( apply_filters( 'wpml_translate_single_string', $feature['description'], IDEAPARK_SLUG, 'Product Feature Description - ' . $feature['description'], apply_filters( 'wpml_current_language', null ) ) ), '<span class="c-product-features__description">', '</span>' ) : ''; ?>
						</span>
						</li>
					<?php } ?>
				</ul>
			</div>
		<?php }
	}
}

if ( ! function_exists( 'ideapark_wc_footer' ) ) {
	function ideapark_wc_footer() { ?>
		<div class="woocommerce-notices-wrapper woocommerce-notices-wrapper--ajax"></div>
		<?php
	}
}

if ( ! function_exists( 'ideapark_get_loop_display_mode' ) ) {
	function ideapark_get_loop_display_mode() {
		$display_type = '';
		if ( is_shop() ) {
			$display_type = get_option( 'woocommerce_shop_page_display', '' );
		} elseif ( is_product_category() ) {
			$parent_id    = get_queried_object_id();
			$display_type = get_term_meta( $parent_id, 'display_type', true );
			$display_type = '' === $display_type ? get_option( 'woocommerce_category_archive_display', '' ) : $display_type;
		}
		if ( '' === $display_type || ! in_array( $display_type, [ 'products', 'subcategories', 'both' ], true ) ) {
			$display_type = 'products';
		}

		return $display_type;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_product_get_image' ) ) {
	function ideapark_woocommerce_product_get_image( $image, $product, $size, $attr, $placeholder ) {
		/**
		 * @var $product WC_Product
		 */
		if ( strpos( $image, 'woocommerce-placeholder' ) !== false && ( $video_thumb_id = get_post_meta( $product->get_id(), '_ip_product_video_thumb', true ) ) ) {
			$image = wp_get_attachment_image( $video_thumb_id, $size, false, $attr );
		}

		return $image;
	}
}

if ( ! function_exists( 'ideapark_structured_data' ) ) {
	function ideapark_structured_data() {
		$new_types = [];
		if ( ideapark_woocommerce_on() && ( $structured_data = ideapark_mod( 'wc_structured_data' ) ) ) {
			foreach ( $structured_data as $type => $markup ) {
				WC()->structured_data->set_data( $markup );
				$type = strtolower( $type );
				if ( ! in_array( $type, $new_types ) ) {
					$new_types[] = $type;
				}
			}

			if ( $new_types ) {
				add_filter( 'woocommerce_structured_data_type_for_page', function ( $types ) use ( $new_types ) {
					return array_unique( array_merge( $types, $new_types ) );
				} );
			}
		}
	}
}

if ( ! function_exists( 'ideapark_product_add_to_cart' ) ) {
	function ideapark_product_add_to_cart() {
		if ( ideapark_mod( 'buy_now' ) ) {
			add_action( 'woocommerce_after_add_to_cart_button', 'ideapark_buy_now' );
		}
		add_action( 'woocommerce_before_add_to_cart_button', '_ideapark_product_atc_start', PHP_INT_MAX );
		add_action( 'woocommerce_after_add_to_cart_button', '_ideapark_product_atc_end', 0 );

		ideapark_rf( 'woocommerce_get_price_html', 'ideapark_add_sale_to_price' );
		ob_start();
		woocommerce_template_single_add_to_cart();
		$content = ob_get_clean();
		?>
		<div
			class="c-product__atc-wrap<?php if ( ideapark_mod( 'sticky_add_to_cart' ) ) { ?> c-product__atc-wrap--sticky<?php } ?><?php if ( ideapark_mod( 'hide_stock_info' ) ) { ?> c-product__atc-wrap--hide-stock<?php } ?><?php if ( defined( 'WOOBT_VERSION' ) && strstr( $content, 'woobt-wrap' ) !== false ) { ?> c-product__atc-wrap--wide<?php } ?>">
			<?php echo ideapark_wrap( $content ); ?>
		</div><!-- .c-product__atc-wrap -->
		<?php
		if ( ideapark_mod( 'buy_now' ) ) {
			ideapark_ra( 'woocommerce_after_add_to_cart_button', 'ideapark_buy_now' );
		}
		ideapark_ra( 'woocommerce_before_add_to_cart_button', '_ideapark_product_atc_start', PHP_INT_MAX );
		ideapark_ra( 'woocommerce_after_add_to_cart_button', '_ideapark_product_atc_end', 0 );
	}
	function _ideapark_product_atc_start() { ?>
		<div class="c-product__atc-row-1<?php if ( ideapark_mod( 'sticky_add_to_cart' ) ) { ?> c-product__atc-row-1--sticky<?php } ?><?php if ( ideapark_mod( 'bottom_buttons_mobile_locations' ) == 'screen' ) { ?> c-product__atc-row-1--menu-sticky<?php } ?>">
	<?php }

	function _ideapark_product_atc_end() { ?>
		</div>
	<?php }
}

if ( ! function_exists( 'ideapark_product_2col_start' ) ) {
	function ideapark_product_2col_start() {
		echo '<div class="c-product__sub-wrap"><div class="c-product__sub-col-1">';
		ideapark_mod_set_temp( '_product_separator', true );
	}
}

if ( ! function_exists( 'ideapark_product_2col_separator' ) ) {
	function ideapark_product_2col_separator() {
		if ( ideapark_mod( '_product_separator' ) ) {
			echo '</div><!-- .c-product__sub-col-1 --><div class="c-product__sub-col-2">';
		}
	}
}

if ( ! function_exists( 'ideapark_product_2col_end' ) ) {
	function ideapark_product_2col_end() {
		if ( ideapark_mod( '_product_separator' ) ) {
			echo '</div><!-- .c-product__sub-col-2 --></div><!-- .c-product__sub-wrap -->';
			ideapark_mod_set_temp( '_product_separator', false );
		}
	}
}

if ( ! function_exists( 'ideapark_product_wishlist_share' ) ) {
	function ideapark_product_wishlist_share() { ?>
		<div class="c-product__buttons-wrap">
			<?php
			ideapark_product_wishlist();
			woocommerce_template_single_sharing();
			?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'ideapark_product_breadcrumbs' ) ) {
	function ideapark_product_breadcrumbs() {
		get_template_part( 'templates/breadcrumbs' );
	}
}

if ( ! function_exists( 'ideapark_product_separator' ) ) {
	function ideapark_product_separator() {
		ob_start();
		ideapark_mod_set_temp( '_product_separator', true );
	}
}

if ( ! function_exists( 'ideapark_product_separator_finish' ) ) {
	function ideapark_product_separator_finish() {
		if ( ideapark_mod( '_product_separator' ) ) {
			ideapark_mod_set_temp( '_product_separator_content', ob_get_clean() );
			ideapark_mod_set_temp( '_product_separator', false );
		}
	}
}

if ( ! function_exists( 'ideapark_product_separator_content' ) ) {
	function ideapark_product_separator_content() {
		echo ideapark_mod( '_product_separator_content' );
	}
}

if ( ! function_exists( 'ideapark_add_sale_to_price' ) ) {
	function ideapark_add_sale_to_price( $html ) {
		if ( ideapark_mod( '_is_product_loop' ) ) {
			return $html;
		} elseif ( is_product() ) {
			ob_start();
			woocommerce_show_product_loop_sale_flash();
			$sale_badge = trim( ob_get_clean() );

			return $sale_badge . $html;
		} else {
			return $html;
		}
	}
}

if ( ! function_exists( 'ideapark_get_sale_percentage' ) ) {
	function ideapark_get_sale_percentage( $product ) {
		$percentage = false;

		if ( $product->is_type( 'variable' ) ) {
			$percentages = [];

			$prices = $product->get_variation_prices();

			foreach ( $prices['price'] as $key => $price ) {
				if ( $prices['regular_price'][ $key ] !== $price && $prices['regular_price'][ $key ] > 0 ) {
					$percentages[] = round( 100 - ( floatval( $prices['sale_price'][ $key ] ) / floatval( $prices['regular_price'][ $key ] ) * 100 ) );
				}
			}
			if ( $percentages ) {
				$percentage = max( $percentages );
			}

		} elseif ( $product->is_type( 'grouped' ) ) {
			$percentages = [];

			$children_ids = $product->get_children();

			foreach ( $children_ids as $child_id ) {
				if ( $child_product = wc_get_product( $child_id ) ) {

					$regular_price = (float) $child_product->get_regular_price();
					$sale_price    = (float) $child_product->get_sale_price();

					if ( ( $sale_price != 0 || ! empty( $sale_price ) && $regular_price > 0 ) ) {
						$percentages[] = round( 100 - ( $sale_price / $regular_price * 100 ) );
					}
				}
			}
			if ( $percentages ) {
				$percentage = max( $percentages );
			}

		} else {
			$regular_price = (float) $product->get_regular_price();
			$sale_price    = (float) $product->get_sale_price();

			if ( ( $sale_price != 0 || ! empty( $sale_price ) ) && $regular_price > 0 ) {
				$percentage = round( 100 - ( $sale_price / $regular_price * 100 ) );
			}
		}

		return $percentage;
	}
}

if ( ! function_exists( 'ideapark_custom_badge_tab_admin' ) ) {
	function ideapark_custom_badge_tab_admin() {
		global $post;

		$_tab_title    = get_post_meta( $post->ID, 'ideapark_custom_badge_tab_title', true ) ?: '';
		$_tab_color    = get_post_meta( $post->ID, 'ideapark_custom_badge_tab_color', true ) ?: '';
		$_tab_bg_color = get_post_meta( $post->ID, 'ideapark_custom_badge_tab_bg_color', true ) ?: '';

		?>
		<div id="ideapark_custom_badge_tab" class="panel wc-metaboxes-wrapper woocommerce_options_panel">
			<div class="options_group">
				<p class="form-field">
					<label for="ideapark_custom_badge_tab_title"><?php esc_html_e( 'Title', 'goldish' ); ?></label>
					<input
						type="text" class="short" name="ideapark_custom_badge_tab_title"
						id="ideapark_custom_badge_tab_title" value="<?php echo esc_attr( $_tab_title ); ?>">
				</p>
				<p class="form-field">
					<label for="ideapark_custom_badge_tab_color"><?php esc_html_e( 'Text Color', 'goldish' ); ?></label>
					<input
						type="text" class="short color-picker" name="ideapark_custom_badge_tab_color"
						data-alpha-enabled="true"
						data-alpha-color-type="hex"
						id="ideapark_custom_badge_tab_color" value="<?php echo esc_attr( $_tab_color ); ?>">
				</p>
				<p class="form-field">
					<label for="ideapark_custom_badge_tab_bg_color"><?php esc_html_e( 'Background Color', 'goldish' ); ?></label>
					<input
						type="text" class="short color-picker" name="ideapark_custom_badge_tab_bg_color"
						data-alpha-enabled="true"
						data-alpha-color-type="hex"
						id="ideapark_custom_badge_tab_bg_color" value="<?php echo esc_attr( $_tab_bg_color ); ?>">
				</p>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'ideapark_custom_badge_tab_save' ) ) {
	function ideapark_custom_badge_tab_save( $post_id, $post ) {

		if ( empty( $post_id ) ) {
			return;
		}

		foreach ( [ 'ideapark_custom_badge_tab_title', 'ideapark_custom_badge_tab_color', 'ideapark_custom_badge_tab_bg_color' ] as $field_name ) {
			$value = ! empty( $_POST[ $field_name ] ) ? wp_unslash( $_POST[ $field_name ] ) : '';

			if ( $value ) {
				update_post_meta( $post_id, $field_name, $value );
				if ( $field_name == 'ideapark_custom_badge_tab_title' ) {
					do_action( 'wpml_register_single_string', IDEAPARK_SLUG, 'Custom badge title - ' . $value, $value );
				}
			} else {
				delete_post_meta( $post_id, $field_name );
			}
		}
	}
}

if ( ! function_exists( 'ideapark_html_block_tab_add_to_list' ) ) {
	function ideapark_html_block_tab_add_to_list( $tabs ) {
		global $product;

		if ( ! isset( $product ) || ! ( $product instanceof WC_Product ) ) {
			return $tabs;
		}

		$product_id = $product->get_id();

		$_html_block_id = get_post_meta( $product_id, 'ideapark_html_block_tab_id', true ) ?: 0;
		$_tab_title     = get_post_meta( $product_id, 'ideapark_html_block_tab_title', true ) ?: '';
		$_tab_title     = apply_filters( 'wpml_translate_single_string', $_tab_title, IDEAPARK_SLUG, 'Tab title - ' . $_tab_title, apply_filters( 'wpml_current_language', null ) );

		if ( $_html_block_id && $_tab_title ) {
			$tabs['html_block'] = [
				'title'    => esc_html( $_tab_title ),
				'priority' => 40,
				'callback' => 'ideapark_html_block_tab_content'
			];
		}

		return $tabs;
	}
}

if ( ! function_exists( 'ideapark_html_block_tab_content' ) ) {
	function ideapark_html_block_tab_content() {
		global $product;

		if ( ! isset( $product ) || ! ( $product instanceof WC_Product ) ) {
			return;
		}

		$product_id = $product->get_id();

		$_html_block_id = get_post_meta( $product_id, 'ideapark_html_block_tab_id', true ) ?: 0;

		if ( $_html_block_id ) {
			$page_id = apply_filters( 'wpml_object_id', $_html_block_id, 'any' );
			if ( $page_id && 'publish' == ideapark_post_status( $page_id ) ) {
				global $post;
				if ( ideapark_is_elementor_page( $page_id ) ) {
					$page_content = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $page_id );
				} elseif ( $post = get_post( $page_id ) ) {
					$page_content = apply_filters( 'the_content', $post->post_content );
					$page_content = str_replace( ']]>', ']]&gt;', $page_content );
					$page_content = ideapark_wrap( $page_content, '<div class="entry-content">', '</div>' );
					wp_reset_postdata();
				} else {
					$page_content = '';
				}

				echo ideapark_wrap( $page_content );
			}
		}
	}
}

if ( ! function_exists( 'ideapark_html_block_tab_save' ) ) {
	function ideapark_html_block_tab_save( $post_id, $post ) {

		if ( empty( $post_id ) ) {
			return;
		}

		$ideapark_html_block_tab_id    = ! empty( $_POST['ideapark_html_block_tab_id'] ) ? intval( $_POST['ideapark_html_block_tab_id'] ) : 0;
		$ideapark_html_block_tab_title = ! empty( $_POST['ideapark_html_block_tab_title'] ) ? wp_unslash( $_POST['ideapark_html_block_tab_title'] ) : '';

		if ( $ideapark_html_block_tab_id ) {
			update_post_meta( $post_id, 'ideapark_html_block_tab_id', $ideapark_html_block_tab_id );
		} else {
			delete_post_meta( $post_id, 'ideapark_html_block_tab_id' );
		}
		if ( $ideapark_html_block_tab_title ) {
			update_post_meta( $post_id, 'ideapark_html_block_tab_title', $ideapark_html_block_tab_title );
			do_action( 'wpml_register_single_string', IDEAPARK_SLUG, 'Tab title - ' . $ideapark_html_block_tab_title, $ideapark_html_block_tab_title );
		} else {
			delete_post_meta( $post_id, 'ideapark_html_block_tab_title' );
		}
	}
}

if ( ! function_exists( 'ideapark_html_block_tab_admin' ) ) {
	function ideapark_html_block_tab_admin() {
		global $post;

		$_html_block_id = get_post_meta( $post->ID, 'ideapark_html_block_tab_id', true ) ?: 0;
		$_tab_title     = get_post_meta( $post->ID, 'ideapark_html_block_tab_title', true ) ?: '';

		$args    = [
			'numberposts' => - 1,
			'post_type'   => 'html_block',
			'orderby'     => 'title'
		];
		$options = [];
		$_posts  = get_posts( $args );
		foreach ( $_posts as $_post ) {
			$options[ $_post->ID ] = $_post->post_title;
		}
		?>
		<div id="ideapark_html_block_tab" class="panel wc-metaboxes-wrapper woocommerce_options_panel">
			<div class="options_group">
				<p class="form-field">
					<label for="ideapark_html_block_tab_title"><?php esc_html_e( 'Title', 'goldish' ); ?></label>
					<input
						type="text" class="short" name="ideapark_html_block_tab_title"
						id="ideapark_html_block_tab_title" value="<?php echo esc_attr( $_tab_title ); ?>">
				</p>
				<p class="form-field">
					<label for="ideapark_html_block_tab_id"><?php esc_html_e( 'HTML Block', 'goldish' ); ?></label>
					<select name="ideapark_html_block_tab_id" id="ideapark_html_block_tab_id" class="select short">
						<option value="0" <?php selected( 0, $_html_block_id ); ?>></option>
						<?php foreach ( $options as $html_block_id => $html_block_title ) { ?>
							<option value="<?php echo esc_attr( $html_block_id ); ?>"
								<?php selected( $html_block_id, $_html_block_id ); ?>><?php echo esc_html( $html_block_title ); ?></option>
						<?php } ?>
					</select>
					<span>&nbsp;&nbsp;&nbsp;<a
							target="_blank"
							href="<?php echo esc_url( admin_url( 'edit.php?post_type=html_block' ) ); ?>"><?php esc_html_e( 'Manage html blocks', 'goldish' ) ?></a>
					</span>
				</p>

				<p class="form-field">
					<label></label>
					<span>
						<?php echo
						ideapark_wp_kses( __( 'To display the tab, all fields above must be filled in. Also make sure that the "HTML block" tab is active ', 'goldish' ) . '<a target="_blank" href="' . admin_url( 'customize.php?autofocus[control]=product_tabs' ) . '">' . __( 'here', 'goldish' ) . '</a>' );
						?>
					</span>
				</p>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'ideapark_html_block_section_save' ) ) {
	function ideapark_html_block_section_save( $post_id, $post ) {

		if ( empty( $post_id ) ) {
			return;
		}

		$ideapark_html_block_section_id       = ! empty( $_POST['ideapark_html_block_section_id'] ) ? intval( $_POST['ideapark_html_block_section_id'] ) : 0;
		$ideapark_html_block_section_title    = ! empty( $_POST['ideapark_html_block_section_title'] ) ? wp_unslash( $_POST['ideapark_html_block_section_title'] ) : '';
		$ideapark_html_block_section_position = ! empty( $_POST['ideapark_html_block_section_position'] ) ? wp_unslash( $_POST['ideapark_html_block_section_position'] ) : 'before_products';

		if ( $ideapark_html_block_section_id ) {
			update_post_meta( $post_id, 'ideapark_html_block_section_id', $ideapark_html_block_section_id );
		} else {
			delete_post_meta( $post_id, 'ideapark_html_block_section_id' );
		}
		if ( $ideapark_html_block_section_title ) {
			update_post_meta( $post_id, 'ideapark_html_block_section_title', $ideapark_html_block_section_title );
			do_action( 'wpml_register_single_string', IDEAPARK_SLUG, 'Section title - ' . $ideapark_html_block_section_title, $ideapark_html_block_section_title );
		} else {
			delete_post_meta( $post_id, 'ideapark_html_block_section_title' );
		}
		if ( $ideapark_html_block_section_position ) {
			update_post_meta( $post_id, 'ideapark_html_block_section_position', $ideapark_html_block_section_position );
		} else {
			delete_post_meta( $post_id, 'ideapark_html_block_section_position' );
		}
	}
}

if ( ! function_exists( 'ideapark_html_block_section_admin' ) ) {
	function ideapark_html_block_section_admin() {
		global $post;

		$_html_block_id       = get_post_meta( $post->ID, 'ideapark_html_block_section_id', true ) ?: 0;
		$_section_title       = get_post_meta( $post->ID, 'ideapark_html_block_section_title', true ) ?: '';
		$_html_block_position = get_post_meta( $post->ID, 'ideapark_html_block_section_position', true ) ?: 'before_products';

		$args    = [
			'numberposts' => - 1,
			'post_type'   => 'html_block',
			'orderby'     => 'title'
		];
		$options = [];
		$_posts  = get_posts( $args );
		foreach ( $_posts as $_post ) {
			$options[ $_post->ID ] = $_post->post_title;
		}
		?>
		<div id="ideapark_html_block_section" class="panel wc-metaboxes-wrapper woocommerce_options_panel">
			<div class="options_group">
				<p class="form-field">
					<label for="ideapark_html_block_section_title"><?php esc_html_e( 'Title', 'goldish' ); ?></label>
					<input
						type="text" class="short" name="ideapark_html_block_section_title"
						id="ideapark_html_block_section_title" value="<?php echo esc_attr( $_section_title ); ?>">
				</p>
				<p class="form-field">
					<label for="ideapark_html_block_section_id"><?php esc_html_e( 'HTML Block', 'goldish' ); ?></label>
					<select name="ideapark_html_block_section_id" id="ideapark_html_block_section_id"
							class="select short">
						<option value="0" <?php selected( 0, $_html_block_id ); ?>></option>
						<?php foreach ( $options as $html_block_id => $html_block_title ) { ?>
							<option value="<?php echo esc_attr( $html_block_id ); ?>"
								<?php selected( $html_block_id, $_html_block_id ); ?>><?php echo esc_html( $html_block_title ); ?></option>
						<?php } ?>
					</select>
					<span>&nbsp;&nbsp;&nbsp;<a
							target="_blank"
							href="<?php echo esc_url( admin_url( 'edit.php?post_type=html_block' ) ); ?>"><?php esc_html_e( 'Manage html blocks', 'goldish' ) ?></a>
					</span>
				</p>
				<p class="form-field">
					<label
						for="ideapark_html_block_section_position"><?php esc_html_e( 'Position', 'goldish' ); ?></label>
					<select name="ideapark_html_block_section_position" id="ideapark_html_block_section_position"
							class="select short">
						<option
							value="before_products" <?php selected( 'before_products', $_html_block_position ); ?>><?php esc_html_e( 'Before products (Related, Up-sell, ...)', 'goldish' ); ?></option>
						<option
							value="after_products" <?php selected( 'after_products', $_html_block_position ); ?>><?php esc_html_e( 'After products (Related, Up-sell, ...)', 'goldish' ); ?></option>
					</select>
				</p>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'ideapark_html_block_section_add_to_list' ) ) {
	function ideapark_html_block_section_add_to_list() {
		global $product;

		if ( ! is_product() ) {
			return;
		}

		if ( ! isset( $product ) || ! ( $product instanceof WC_Product ) ) {
			return;
		}

		$product_id = $product->get_id();

		$_html_block_id       = get_post_meta( $product_id, 'ideapark_html_block_section_id', true ) ?: 0;
		$_html_block_position = get_post_meta( $product_id, 'ideapark_html_block_section_position', true ) ?: 'before_products';

		if ( $_html_block_id ) {
			add_action( 'woocommerce_after_single_product_summary', 'ideapark_html_block_section_content', $_html_block_position == 'after_products' ? 998 : 6 );
		}
	}
}

if ( ! function_exists( 'ideapark_html_block_section_content' ) ) {
	function ideapark_html_block_section_content() {
		global $product;

		if ( ! is_product() ) {
			return;
		}

		if ( ! isset( $product ) || ! ( $product instanceof WC_Product ) ) {
			return;
		}

		$product_id = $product->get_id();

		$_html_block_id = get_post_meta( $product_id, 'ideapark_html_block_section_id', true ) ?: 0;
		$_section_title = get_post_meta( $product_id, 'ideapark_html_block_section_title', true ) ?: '';
		$_section_title = apply_filters( 'wpml_translate_single_string', $_section_title, IDEAPARK_SLUG, 'Section title - ' . $_section_title, apply_filters( 'wpml_current_language', null ) );

		if ( $_html_block_id ) : ?>
			<section
				class="c-product__products c-product__products--html-block c-product__products--<?php echo esc_attr( ideapark_mod( 'product_grid_width' ) ); ?> l-section">
				<?php if ( $_section_title ) { ?>
					<div class="c-product__products-title"><?php echo esc_html( $_section_title ); ?></div>
				<?php } ?>
				<?php
				$page_id = apply_filters( 'wpml_object_id', $_html_block_id, 'any' );
				if ( $page_id && 'publish' == ideapark_post_status( $page_id ) ) {
					global $post;
					if ( ideapark_is_elementor_page( $page_id ) ) {
						$page_content = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $page_id );
					} elseif ( $post = get_post( $page_id ) ) {
						$page_content = apply_filters( 'the_content', $post->post_content );
						$page_content = str_replace( ']]>', ']]&gt;', $page_content );
						$page_content = ideapark_wrap( $page_content, '<div class="entry-content">', '</div>' );
						wp_reset_postdata();
					} else {
						$page_content = '';
					}

					echo ideapark_wrap( $page_content );
				}
				?>
			</section>
		<?php endif;
	}
}

if ( ! function_exists( 'ideapark_fix_notices_template' ) ) {
	function ideapark_fix_notices_template( $template, $template_name, $args, $template_path, $default_path ) {
		if ( in_array( $template_name, [
			'notices/error.php',
			'notices/notice.php',
			'notices/success.php',
		] ) ) {
			$cache_key = sanitize_key( implode( '-', [
				'template',
				$template_name,
				$template_path,
				$default_path,
				Automattic\Jetpack\Constants::get_constant( 'WC_VERSION' )
			] ) );
			$template  = (string) wp_cache_get( $cache_key, 'woocommerce' );

			if ( ! $template ) {
				$template   = wc_locate_template( $template_name, $template_path, $default_path );
				$cache_path = wc_tokenize_path( $template, wc_get_path_define_tokens() );
				wc_set_template_cache( $cache_key, $cache_path );
			} else {
				$template = wc_untokenize_path( $template, wc_get_path_define_tokens() );
			}
		}

		return $template;
	}
}

if ( ! function_exists( 'ideapark_product_custom_html' ) ) {
	function ideapark_product_custom_html() {
		if ( ideapark_mod( 'product_page_custom_html' ) ) { ?>
			<div
				class="c-product__custom-html<?php if ( ideapark_mod( 'product_html_background_color' ) || ideapark_mod( 'product_html_border' ) ) { ?> c-product__custom-html--container<?php } ?>">
				<?php echo do_shortcode( ideapark_mod( 'product_page_custom_html' ) ); ?>
			</div>
		<?php }
	}
}

if ( IDEAPARK_IS_AJAX_IMAGES ) {
	add_action( 'wp_ajax_ideapark_product_images', 'ideapark_ajax_product_images' );
	add_action( 'wp_ajax_nopriv_ideapark_product_images', 'ideapark_ajax_product_images' );
} else {
	add_action( 'init', 'ideapark_attribute_hint' );
	add_action( 'wp_loaded', 'ideapark_setup_woocommerce', 99 );

	add_action( 'wp_ajax_ideapark_ajax_product', 'ideapark_ajax_product' );
	add_action( 'wp_ajax_nopriv_ideapark_ajax_product', 'ideapark_ajax_product' );

	add_action( 'wp_ajax_ideapark_ajax_recently', 'ideapark_ajax_recently' );
	add_action( 'wp_ajax_nopriv_ideapark_ajax_recently', 'ideapark_ajax_recently' );

	add_action( 'wp_ajax_ideapark_ajax_variable_scripts', 'ideapark_ajax_variable_scripts' );
	add_action( 'wp_ajax_nopriv_ideapark_ajax_variable_scripts', 'ideapark_ajax_variable_scripts' );

	add_action( 'wp_ajax_ideapark_ajax_attribute_hint', 'ideapark_ajax_attribute_hint' );
	add_action( 'wp_ajax_nopriv_ideapark_ajax_attribute_hint', 'ideapark_ajax_attribute_hint' );

	add_action( 'wc_ajax_ip_add_to_cart', 'ideapark_ajax_add_to_cart' );
	add_action( 'wc_ajax_nopriv_ip_add_to_cart', 'ideapark_ajax_add_to_cart' );

	add_filter( 'woo_variation_swatches_product_data_tab', '__return_false' );

	add_action( 'woocommerce_ajax_added_to_cart', 'ideapark_add_to_cart_ajax_notice' );

	add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
	add_filter( 'woocommerce_add_to_cart_fragments', 'ideapark_header_add_to_cart_fragment' );
	add_filter( 'woocommerce_cart_hash', 'ideapark_header_add_to_cart_hash' );
	add_filter( 'woocommerce_breadcrumb_defaults', 'ideapark_woocommerce_breadcrumbs' );
	add_filter( 'woocommerce_account_menu_items', 'ideapark_woocommerce_account_menu_items' );
	add_filter( 'woocommerce_product_description_heading', 'ideapark_remove_product_description_heading' );
	add_filter( 'woocommerce_loop_add_to_cart_link', 'ideapark_loop_add_to_cart_link', 99, 3 );
	add_filter( 'woocommerce_gallery_image_size', 'ideapark_woocommerce_gallery_image_size', 99, 1 );
	add_filter( 'woocommerce_loop_add_to_cart_args', 'ideapark_woocommerce_loop_add_to_cart_args', 99 );
	add_filter( 'woocommerce_available_variation', 'ideapark_woocommerce_available_variation', 100, 3 );
	add_filter( 'woocommerce_pagination_args', 'ideapark_woocommerce_pagination_args' );
	add_filter( 'subcategory_archive_thumbnail_size', 'ideapark_subcategory_archive_thumbnail_size', 99, 1 );
	add_filter( 'woocommerce_before_widget_product_list', 'ideapark_woocommerce_before_widget_product_list' );
	add_filter( 'woocommerce_demo_store', 'ideapark_woocommerce_demo_store' );
	add_filter( 'woocommerce_product_tabs', 'ideapark_woocommerce_product_tabs', 11 );
	add_action( 'woocommerce_attribute_updated', 'ideapark_WPML_attribute_title', 10, 2 );
	add_action( 'woocommerce_attribute_added', 'ideapark_WPML_attribute_title', 10, 2 );
	add_filter( 'woocommerce_get_breadcrumb', 'ideapark_woocommerce_get_brands_breadcrumb', 10, 2 );
	add_filter( 'woocommerce_product_get_image', 'ideapark_woocommerce_product_get_image', 10, 5 );
	add_filter( 'wc_get_template', 'ideapark_fix_notices_template', 999, 5 );
	add_filter( 'woocommerce_single_product_photoswipe_enabled', '__return_false', 999 );

	add_action( 'wp_footer', 'ideapark_wc_footer' );
}

add_filter( 'option_woocommerce_thumbnail_cropping', function () {
	return 'uncropped';
} );

add_action( 'after_update_theme_late', function () {
	delete_transient( 'wc_system_status_theme_info' );
} );
add_action( 'woocommerce_page_wc-status', function () { // Fix WooCommerce bug
	if ( ! class_exists( 'WC_Plugin_Updates' ) && ideapark_is_file( WP_PLUGIN_DIR . '/woocommerce/includes/admin/plugin-updates/class-wc-plugin-updates.php' ) ) {
		include_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/plugin-updates/class-wc-plugin-updates.php';
	}
}, 1 );

add_filter( 'mejs_settings', function ( $settings ) {
	$settings['hideVideoControlsOnLoad'] = true;
	$settings['youtube']                 = [
		'playsinline' => 1,
	];

	return $settings;
} );

add_filter( 'woobt_get_setting', function ( $setting, $name, $default ) {
	if ( $name == 'change_image' ) {
		return 'no';
	} elseif ( $name == 'responsive' ) {
		return 'no';
	} elseif ( $name == 'layout' ) {
		if ( ! in_array( $setting, [ 'default', 'separate' ] ) ) {
			return 'default';
		} else {
			return $setting;
		}
	} else {
		return $setting;
	}
}, 10, 3 );

add_action( 'wp', function () {
	global $wp_filter, $ideapark_product_page_hidden_blocks;
	if ( is_admin() || ! ideapark_woocommerce_on() || ! is_product() ) {
		return;
	}
	$product_page_blocks = ideapark_mod( 'product_page_blocks_' . ideapark_mod( 'product_page_layout' ) );
	if ( $product_page_blocks && ! ( strstr( $product_page_blocks, '=' ) === false && is_callable( $product_page_blocks ) ) ) {
		$product_page_blocks = ideapark_parse_checklist( $product_page_blocks );
		$old_callbacks       = [];
		if ( isset( $wp_filter['woocommerce_single_product_summary']->callbacks ) && is_array( $wp_filter['woocommerce_single_product_summary']->callbacks ) ) {
			foreach ( $wp_filter['woocommerce_single_product_summary']->callbacks as $order => &$rows ) {
				foreach ( $rows as $name => &$callback ) {
					if ( is_object( $callback['function'] ) ) {
						$old_callbacks[ $name ] = [
							'name'     => $name,
							'callback' => $callback,
							'order'    => $order,
						];
					} elseif ( is_string( $callback['function'] ) ) {
						$old_callbacks[ $callback['function'] ] = [
							'name'     => $name,
							'callback' => $callback,
							'order'    => $order,
						];
					} elseif ( is_array( $callback['function'] ) ) {
						$old_callbacks[ $callback['function'][1] ] = [
							'name'     => $name,
							'callback' => $callback,
							'order'    => $order,
						];
					}
				}
			}
		}
		$new_callbacks = [];
		$order         = 5;
		foreach ( $product_page_blocks as $name => $enabled ) {
			if ( $enabled ) {
				if ( array_key_exists( $name, $old_callbacks ) ) {
					$new_callbacks[ $order ] = [
						$old_callbacks[ $name ]['name'] => $old_callbacks[ $name ]['callback']
					];

					$order += 5;
				}
			}
		}
		foreach ( $ideapark_product_page_hidden_blocks as $name ) {
			if ( array_key_exists( $name, $old_callbacks ) ) {
				$new_callbacks[ $old_callbacks[ $name ]['order'] ][ $old_callbacks[ $name ]['name'] ] = $old_callbacks[ $name ]['callback'];
			}
		}
		remove_all_filters( 'woocommerce_single_product_summary' );
		foreach ( $new_callbacks as $priority => $callback ) {
			add_action( 'woocommerce_single_product_summary', function ( $a ) {
				return $a;
			}, $priority );
		}
		$wp_filter['woocommerce_single_product_summary']->callbacks = $new_callbacks;
	}
} );

add_filter( 'wc_add_to_cart_message_html', function ( $html ) {
	return '<span class="woocommerce-notices-atc-wrap">' . $html . '</span>';
} );

add_filter( 'coming-soon_template', function ( $template, $type, $templates ) {
	global $wpdb;
	if (
		( $page_id = ideapark_mod( 'maintenance_mode_page' ) ) &&
		( $page_id = apply_filters( 'wpml_object_id', $page_id, 'any' ) ) &&
		ideapark_is_elementor() && ( Elementor\Plugin::instance() ) &&
		( $_template = \Elementor\Plugin::$instance->modules_manager->get_modules( 'page-templates' )->get_template_path( 'elementor_canvas' ) )
	) {
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE ID=%d AND post_type='page'", $page_id ) ) ) {
			query_posts( [
				'p'         => $page_id,
				'post_type' => 'page',
			] );
			$template = $_template;
		}
	}

	return $template;
}, 100, 3 );
