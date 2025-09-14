<?php

class Goldish_Theme_Fix {
	public $is_changed = false;

	public function __construct() {
		add_action( 'after_update_theme', [ $this, 'image_post_format_fix' ], 10, 1 );
	}

	public function image_post_format_fix( $old ) {
		global $post;
		if ( version_compare( $old, '3.14', '<' ) ) {
			$posts = new WP_Query( [
				'post_type' => 'post',
				'tax_query' => [
					[
						'taxonomy' => 'post_format',
						'field' => 'slug',
						'terms' => [
							'post-format-image','post-format-video','post-format-gallery',
						],
						'operator' => 'NOT IN'
					]
				]
			] );

			if ( $posts->have_posts() ) {
				while ( $posts->have_posts() ) {
					$posts->the_post();
					set_post_format($post->ID, 'image' );
				}
			}
		}
	}
}

new Goldish_Theme_Fix();