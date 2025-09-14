<?php

use Elementor\Control_Media;
use Elementor\Group_Control_Image_Size;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor hotspot Carousel widget.
 *
 * Elementor widget that displays a bullet list with any chosen icons and texts.
 *
 * @since 1.0.0
 */
class Ideapark_Elementor_Hotspot_Carousel extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve hotspot Carousel widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'ideapark-hotspot-carousel';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve hotspot Carousel widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Hotspot Carousel', 'ideapark-goldish' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve hotspot Carousel widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'eicon-image-rollover';
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
		return [ 'hotspot', 'hotspot Carousel', 'image', 'list' ];
	}

	/**
	 * Register hotspot Carousel widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_slider_list',
			[
				'label' => __( 'Slides', 'ideapark-goldish' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'label'   => __( 'Choose Image', 'ideapark-goldish' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'hotspots',
			[
				'label' => __( 'Add / Edit Hotspots', 'ideapark-goldish' ),
				'image' => 'image',
				'type'  => 'ideapark-hotspot'
			]
		);


		$repeater->add_control(
			'title_text',
			[
				'label'       => __( 'Header', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'This is the heading', 'ideapark-goldish' ),
				'placeholder' => __( 'Enter your title', 'ideapark-goldish' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'subheader',
			[
				'label'       => __( 'Subheader', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( '', 'ideapark-goldish' ),
				'placeholder' => __( 'Enter text', 'ideapark-goldish' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'link',
			[
				'label'       => __( 'Link', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$repeater->add_control(
			'point_color',
			[
				'label'     => __( 'Point Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .c-ip-hotspot-carousel__point:after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'text_color',
			[
				'label'     => __( 'Text Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'separator',
			[
				'label'     => __( 'Dynamic Background', 'ideapark-goldish' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'dynamic_bg_image',
			[
				'label' => __( 'Dynamic Background Image', 'ideapark-goldish' ),
				'type'  => Controls_Manager::MEDIA,
			]
		);

		$repeater->add_control(
			'dynamic_bg_color',
			[
				'label'   => __( 'Dynamic Background Color', 'ideapark-goldish' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			]
		);

		$this->add_control(
			'image_list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title_text }}}',
			]
		);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_slider_settings',
			[
				'label' => __( 'Hotspot Settings', 'ideapark-goldish' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'point_color',
			[
				'label'     => __( 'Text Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .c-ip-hotspot-carousel' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'font_size',
			[
				'label'      => __( 'Header font size', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 40,
						'max' => 100,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-hotspot-carousel__title' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'max_width',
			[
				'label'      => __( 'Image max width', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1160,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .c-ip-hotspot-carousel__list' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'text_max_width',
			[
				'label'      => __( 'Text max width', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 400,
						'max' => 1160,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .c-ip-hotspot-carousel__title'     => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .c-ip-hotspot-carousel__subheader' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dynamic_background',
			[
				'label'     => __( 'Dynamic background', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'label_on'  => __( 'Yes', 'ideapark-goldish' ),
				'label_off' => __( 'No', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'     => __( 'Autoplay', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'label_on'  => esc_html__( 'Yes', 'ideapark-goldish' ),
				'label_off' => esc_html__( 'No', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'animation_timeout',
			[
				'label'      => __( 'Autoplay Timeout (sec)', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 5,
				],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'condition'  => [
					'autoplay' => 'yes',
				],
			]
		);


		$this->end_controls_section();
	}

	/**
	 * Render hotspot Carousel widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {
		global $product;
		$settings = $this->get_settings_for_display();
		?>
		<div
			class="c-ip-hotspot-carousel <?php ideapark_class( $settings['dynamic_background'] === 'yes', 'c-ip-hotspot-carousel--dynamic-bg', '' ); ?>"
			<?php if ( $settings['dynamic_background'] === 'yes' && ( ! empty( $settings['image_list'][0]['dynamic_bg_image'] ) || ! empty( $settings['image_list'][0]['dynamic_bg_color'] ) ) ) {
				echo ideapark_bg(
					! empty( $settings['image_list'][0]['dynamic_bg_color'] ) ? $settings['image_list'][0]['dynamic_bg_color'] : '',
					! empty( $settings['image_list'][0]['dynamic_bg_image'] ) ? $settings['image_list'][0]['dynamic_bg_image']['url'] : ''
				);
			} ?>>
			<div
				class="c-ip-hotspot-carousel__list js-hotspot-carousel h-carousel"
				data-autoplay="<?php echo esc_attr( $settings['autoplay'] ); ?>"
				<?php if ( ! empty( $settings['animation_timeout']['size'] ) ) { ?>
					data-animation-timeout="<?php echo esc_attr( abs( $settings['animation_timeout']['size'] * 1000 ) ); ?>"
				<?php } ?>
				<?php if ( $settings['dynamic_background'] === 'yes' ) { ?>data-dynamic="yes"<?php } ?>>
				<?php
				foreach ( $settings['image_list'] as $index => $item ) : ?>
					<?php $hotspots = ! empty( $item['hotspots'] ) ? json_decode( $item['hotspots'], true ) : []; ?>
					<div
						class="c-ip-hotspot-carousel__item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>"
						<?php if ( $settings['dynamic_background'] === 'yes' ) { ?>
							data-bg-image="<?php echo esc_url( $item['dynamic_bg_image']['url'] ); ?>"
							data-bg-color="<?php echo esc_attr( $item['dynamic_bg_color'] ); ?>"
						<?php } ?>>

						<?php if ( ! empty( $item['image']['id'] ) ) { ?>
							<div class="c-ip-hotspot-carousel__image-wrap">
								<?php
								if ( ! empty( $item['image']['id'] ) && ( $type = get_post_mime_type( $item['image']['id'] ) ) ) {
									if ( $type == 'image/svg+xml' ) {
										echo ideapark_get_inline_svg( $item['image']['id'], 'c-ip-hotspot-carousel__image c-ip-hotspot-carousel__image--svg' );
									} else {
										if ( $image_meta = ideapark_image_meta( $item['image']['id'], 'full' ) ) {
											echo ideapark_img( $image_meta, 'c-ip-hotspot-carousel__image' );
										}
									}
								} ?>
								<?php if ( is_array( $hotspots ) ) { ?>
									<?php foreach ( $hotspots as $point ) { ?>
										<?php if ( ! empty( $point['product_id'] ) && ( ideapark_woocommerce_on() ) && ( $product = wc_get_product( (int) $point['product_id'] ) ) ) { ?>
											<?php
											/**
											 * @var $product WC_Product
											 **/

											$permalink = $product->get_permalink();
											$thumbnail = $product->get_image( 'ideapark-compact' );
											$title     = $product->get_title();
											?>
											<div class="c-ip-hotspot-carousel__point js-carousel-point"
											     data-left="<?php echo esc_attr( $point['x'] ); ?>"
											     data-top="<?php echo esc_attr( $point['y'] ); ?>">
												<div class="c-ip-hotspot-carousel__point-popup">
													<div class="c-ip-hotspot-carousel__product-thumb">
														<?php
														if ( ! $permalink ) {
															echo ideapark_wrap( $thumbnail );
														} else {
															printf( '<a href="%s">%s</a>', esc_url( $permalink ), $thumbnail );
														}
														?>
													</div>
													<div class="c-ip-hotspot-carousel__col">
														<div class="c-ip-hotspot-carousel__product-categories">
															<?php ideapark_cut_product_categories(); ?>
														</div>
														<div class="c-ip-hotspot-carousel__product-title">
															<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
														</div>
														<div class="c-ip-hotspot-carousel__product-price">
															<?php woocommerce_template_loop_price(); ?>
														</div>
													</div>
												</div>
											</div>
										<?php } ?>
									<?php } ?>
								<?php } ?>
								<?php if ( ! empty( $item['title_text'] ) ) {
									if ( ! empty( $item['link']['url'] ) ) {
										$is_link  = true;
										$link_key = 'link_' . $index;

										$this->add_link_attributes( $link_key, $item['link'] );
										$this->add_render_attribute( $link_key, 'class', 'c-ip-hotspot-carousel__link' );
									} else {
										$is_link = false;
									} ?>
									<?php if ( $is_link ) { ?>
										<a <?php echo $this->get_render_attribute_string( $link_key ); ?>>
									<?php } ?>
									<div class="c-ip-hotspot-carousel__title"><?php echo $item['title_text']; ?></div>
									<?php if ( $is_link ) { ?>
										</a>
									<?php } ?>
								<?php } ?>
							</div>
						<?php } ?>
						<?php if ( ! empty( $item['subheader'] ) ) { ?>
							<div class="c-ip-hotspot-carousel__subheader"><?php echo $item['subheader']; ?></div>
						<?php } else { ?>
							<div class="c-ip-hotspot-carousel__spacer"></div>
						<?php } ?>

						<?php if ( ! empty( $item['link']['url'] ) ) {
							$link_key = 'link_' . $index;
							$this->add_link_attributes( $link_key, $item['link'] );
							$this->add_render_attribute( $link_key, 'class', 'c-ip-hotspot-carousel__link' );
							echo '<a ' . $this->get_render_attribute_string( $link_key ) . '></a>';
						} ?>
					</div>
				<?php
				endforeach;
				?>
			</div>

			<div
				class="c-ip-hotspot-carousel__modal c-header__callback-popup c-header__callback-popup--disabled js-callback-popup js-hotspot-popup">
				<div class="c-header__callback-bg js-callback-close"></div>
				<div class="c-header__callback-wrap c-header__callback-wrap--quickview">
					<div class="js-hotspot-container"></div>
				</div>
				<button type="button" class="h-cb h-cb--svg c-header__callback-close js-callback-close"><i
						class="ip-close-rect"></i></button>
			</div>
		</div>
		<?php
	}
}
