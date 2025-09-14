<?php

use Elementor\Control_Media;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor video carousel  widget.
 *
 * Elementor widget that displays a bullet list with any chosen icons and texts.
 *
 * @since 1.0.0
 */
class Ideapark_Elementor_Video_Carousel extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve video carousel  widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'ideapark-video-carousel';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve video carousel  widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Product Videos', 'ideapark-goldish' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve video carousel  widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'ip-pr-carousel';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 */
	public function get_categories() {
		return [ 'ideapark-elements' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 * @since  2.1.0
	 * @access public
	 *
	 */
	public function get_keywords() {
		return [ 'carousel', 'video' ];
	}

	/**
	 * Register video carousel  widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Widget Settings', 'ideapark-goldish' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'count',
			[
				'label'   => __( 'Videos count', 'ideapark-goldish' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'default' => 8,
				'step'    => 1,
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'   => __( 'Sort', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'rand',
				'options' => [
					'rand' => __( 'Random sorting', 'ideapark-goldish' ),
					'date' => __( 'Sort by date the product was published', 'ideapark-goldish' ),
				]
			]
		);

		$this->add_control(
			'color',
			[
				'label'       => __( 'Text Color', 'ideapark-goldish' ),
				'description' => __( 'Select color or leave empty for display default.', 'ideapark-goldish' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .c-ip-video-carousel__title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrows',
			[
				'label'   => __( 'Arrows', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'label_on'     => __( 'Show', 'ideapark-goldish' ),
				'label_off'    => __( 'Hide', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'dots',
			[
				'label'   => __( 'Navigation dots', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
				'label_on'     => __( 'Show', 'ideapark-goldish' ),
				'label_off'    => __( 'Hide', 'ideapark-goldish' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render video carousel  widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  .0.0
	 * @access protected
	 */
	protected function render() {
		global $wpdb;
		$settings = $this->get_settings();

		$exclude_term_ids = [];
		if ( $exclude_search_term = get_term_by( 'name', 'exclude-from-search', 'product_visibility' ) ) {
			$exclude_term_ids[] = (int) $exclude_search_term->term_taxonomy_id;
		}
		if ( ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) && ( $exclude_outofstock_term = get_term_by( 'name', 'outofstock', 'product_visibility' ) ) ) {
			$exclude_term_ids[] = (int) $exclude_outofstock_term->term_taxonomy_id;
		}

		$limit = (int) $settings['count'];

		$sql     = "
			SELECT {$wpdb->posts}.ID, {$wpdb->posts}.post_title, pm_video.meta_value AS video_url, pm_video_thumb.meta_value AS video_thumb
			FROM {$wpdb->posts}
			INNER JOIN {$wpdb->postmeta} pm_video ON ( {$wpdb->posts}.ID = pm_video.post_id AND pm_video.meta_key='_ip_product_video_url')
			LEFT JOIN {$wpdb->postmeta} pm_video_thumb ON ( {$wpdb->posts}.ID = pm_video_thumb.post_id AND pm_video_thumb.meta_key='_ip_product_video_thumb')
			WHERE pm_video.meta_value!='' 
			AND ( {$wpdb->posts}.post_type IN ( 'product' ) )
			AND ( {$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'private' )
			" . ( ! empty( $exclude_term_ids ) ? "AND ( {$wpdb->posts}.ID NOT IN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (" . implode( ',', $exclude_term_ids ) . ") ) )" : "" ) . "
			GROUP BY {$wpdb->posts}.ID
			ORDER BY " . ( $settings['orderby'] == 'rand' ? 'RAND()' : 'post_date DESC' ) . "
			LIMIT $limit
			";
		$results = $wpdb->get_results( $sql, OBJECT );
		?>
		<?php if ( ! empty( $results ) ) { ?>
			<div class="c-ip-video-carousel">
				<div class="c-ip-video-carousel__wrap">
					<div
						class="c-ip-video-carousel__list c-ip-video-carousel__list--<?php echo sizeof( $results ); ?> js-video-carousel h-carousel h-carousel--default-dots<?php if ( $settings['arrows'] != 'yes' ) { ?> h-carousel--nav-hide<?php } else { ?> h-carousel--outside h-carousel--round h-carousel--outline<?php } ?><?php if ( $settings['dots'] != 'yes' ) { ?> h-carousel--dots-hide<?php } else { ?> c-ip-video-carousel__list--dots<?php } ?>">
						<?php foreach ( $results as $index => $post ) {
							$video_url  = $post->video_url;
							$image_code = '';
							if ( ( $video_thumb_id = $post->video_thumb ) && ( $image_meta = ideapark_image_meta( $video_thumb_id ) ) ) {
								$image_code = ideapark_img( $image_meta, 'c-ip-video-carousel__img' );
							} else {
								$pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
								if ( preg_match( $pattern, $video_url, $match ) ) {
									$image_url  = 'https://img.youtube.com/vi/' . $match[1] . '/maxresdefault.jpg';
									$image_code = '<img class="c-ip-video-carousel__img" src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $post->post_title ) . '" ' . ( ideapark_mod( 'lazyload' ) ? 'loading="lazy"' : '' ) . ' />';
								}
							}
							?>
							<div class="c-ip-video-carousel__item">
								<a class="js-ip-video" data-vbtype="video" data-autoplay="true"
								   href="<?php echo esc_url( $video_url ); ?>" target="_blank" onclick="return false;">
									<div class="c-ip-video-carousel__thumb">
										<?php echo ideapark_wrap( $image_code ); ?>
										<i class="c-play c-ip-video-carousel__play"></i>
									</div>
								</a>
								<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
									<div class="c-ip-video-carousel__title">
										<?php echo esc_html( $post->post_title ) ?>
									</div>
								</a>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php
	}

	/**
	 * Render video carousel  widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function content_template() {
	}
}
