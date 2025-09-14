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
 * Elementor news carousel  widget.
 *
 * Elementor widget that displays a bullet list with any chosen icons and texts.
 *
 * @since 1.0.0
 */
class Ideapark_Elementor_News_Carousel extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve news carousel  widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'ideapark-news-carousel';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve news carousel  widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'News Carousel', 'ideapark-goldish' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve news carousel  widget icon.
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
		return [ 'carousel', 'news' ];
	}

	/**
	 * Register news carousel  widget controls.
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
			'layout',
			[
				'label'   => __( 'Layout', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'layout-1',
				'options' => [
					'layout-1' => __( 'Layout 1', 'ideapark-goldish' ),
					'layout-2' => __( 'Layout 2', 'ideapark-goldish' ),
					'layout-3' => __( 'Layout 3', 'ideapark-goldish' ),
				]
			]
		);

		$this->add_control(
			'count',
			[
				'label'   => __( 'News count', 'ideapark-goldish' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'default' => 3,
				'step'    => 1,
			]
		);

		$options = [ 0 => __( 'All', 'ideapark-goldish' ) ];
		if ( $categories = get_categories() ) {
			foreach ( $categories as $category ) {
				$options[ $category->term_id ] = $category->name;
			}
		}
		$this->add_control(
			'category',
			[
				'label'   => __( 'Category', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 0,
				'options' => $options
			]
		);

		$this->add_control(
			'arrows',
			[
				'label'   => __( 'Navigation arrows', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'label_on'     => __( 'Show', 'ideapark-goldish' ),
				'label_off'    => __( 'Hide', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'background_color',
			[
				'label'     => __( 'Arrows background color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .owl-prev' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .owl-next' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'arrows' => 'yes',
				]
			]
		);

		$this->add_control(
			'dots',
			[
				'label'   => __( 'Navigation dots', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'label_on'     => __( 'Show', 'ideapark-goldish' ),
				'label_off'    => __( 'Hide', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'border_color',
			[
				'label'     => __( 'Border color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .c-post-list__wrap' => 'border-color: {{VALUE}};',
				]
			]
		);

		$this->add_control(
			'placeholder',
			[
				'label' => __( 'Placeholder', 'ideapark-goldish' ),
				'type'  => Controls_Manager::MEDIA
			]
		);



		$this->end_controls_section();
	}

	/**
	 * Render news carousel  widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  .0.0
	 * @access protected
	 */
	protected function render() {
		global $post;
		$settings = $this->get_settings();
		$args     = [
			'numberposts' => $settings['count'],
			'suppress_filters' => false
		];
		if ( ! empty( $settings['category'] ) ) {
			$args['category'] = $settings['category'];
		}
		$news = get_posts( $args );
		if ( ! $news ) {
			return;
		}
		switch ( $settings['layout'] ) {
			case 'layout-1':
				$layout = 'grid';
				break;
			case 'layout-2':
				$layout = 'list';
				break;
			case 'layout-3':
				$layout = 'grid';
				break;
		}
		$old_post_layout  = ideapark_mod( 'post_layout' );
		ideapark_mod_set_temp( 'post_layout', $layout );
		ideapark_mod_set_temp( '_disable_post_image_gallery', true );
		if ( ! empty( $settings['placeholder']['id'] ) ) {
			add_filter( 'ideapark_post_placeholder', function () use ( $settings ) {
				if ( $type = get_post_mime_type( $settings['placeholder']['id'] ) ) {
					if ( $type == 'image/svg+xml' ) {
						return ideapark_get_inline_svg( $settings['placeholder']['id'], 'c-post-list__img' );
					} else {
						return ideapark_img( ideapark_image_meta( $settings['placeholder']['id'] ), 'c-post-list__img' );
					}
				}
			} );
		}
		?>
		<div class="c-ip-news-carousel">
			<div
				class="l-section <?php ideapark_class( $settings['layout'] == 'layout-1', 'l-section--container-wide', 'l-section--container' ); ?>">
				<div
					class="c-ip-news-carousel__wrap c-ip-news-carousel__wrap--<?php echo sizeof( $news ); ?> c-ip-news-carousel__wrap--<?php echo $settings['layout']; ?> ">
					<div
						class="c-ip-news-carousel__list c-ip-news-carousel__list--<?php echo $settings['layout']; ?> c-ip-news-carousel__list--<?php echo sizeof( $news ); ?> js-news-carousel h-carousel h-carousel--flex <?php if ( $settings['arrows'] == 'yes' ) { ?><?php if ( $settings['layout'] != 'layout-3' ) { ?> h-carousel--border<?php } else { ?> h-carousel--outside<?php } ?> h-carousel--round h-carousel--outline<?php } ?><?php if ( $settings['dots'] != 'yes' ) { ?> h-carousel--dots-hide<?php } else { ?> h-carousel--default-dots c-ip-news-carousel__list--dots<?php } ?>"
						data-layout="<?php echo esc_attr( $settings['layout'] ); ?>"
						data-count="<?php echo sizeof( $news ); ?>">
						<?php foreach ( $news as $index => $post ) { ?>
							<?php setup_postdata( $post ); ?>
							<?php get_template_part( 'templates/content-list' ); ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		remove_all_filters( 'ideapark_post_placeholder' );
		ideapark_mod_set_temp( 'post_layout', $old_post_layout );
		ideapark_mod_set_temp( '_disable_post_image_gallery', false );
		wp_reset_postdata();
	}

	/**
	 * Render news carousel  widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function content_template() {
	}
}
