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
 * Elementor slider widget.
 *
 * Elementor widget that displays a bullet list with any chosen icons and texts.
 *
 * @since 1.0.0
 */
class Ideapark_Elementor_Slider extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve slider widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'ideapark-slider';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve slider widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Slider', 'ideapark-goldish' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve slider widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'eicon-slides';
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
		return [ 'carousel', 'slider' ];
	}

	/**
	 * Register slider widget controls.
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
			'image_desktop',
			[
				'label'   => __( 'Image (Desktop)', 'ideapark-goldish' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'image_mobile',
			[
				'label'   => __( 'Image (Mobile)', 'ideapark-goldish' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'title',
			[
				'label'       => __( 'Title', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter title', 'ideapark-goldish' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'subtitle',
			[
				'label'       => __( 'Subtitle', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter text', 'ideapark-goldish' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'button_text',
			[
				'label'       => __( 'Button text', 'ideapark-goldish' ),
				'default'     => __( 'Read more', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter title', 'ideapark-goldish' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'button_link',
			[
				'label'     => __( 'Button link', 'ideapark-goldish' ),
				'type'      => Controls_Manager::URL,
				'default'   => [
					'url' => '#',
				],
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'background_color',
			[
				'label'     => __( 'Background Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'background-color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'custom_color',
			[
				'label'     => __( 'Custom Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .c-ip-slider__title--layout-1'    => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .c-ip-slider__subtitle--layout-1' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .c-ip-slider__description'        => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'slider_list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_slider_settings',
			[
				'label' => __( 'Slider Settings', 'ideapark-goldish' ),
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
				]
			]
		);

		$this->add_responsive_control(
			'max_width',
			[
				'label'      => __( 'Max width of the text block', 'ideapark-goldish' ),
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
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-slider__wrap--layout-1' => 'max-width: {{SIZE}}{{UNIT}};',
				],

				'condition' => [
					'layout' => 'layout-1',
				],
			]
		);

		$this->add_control(
			'vertical_align',
			[
				'label'     => __( 'Vertical align', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'bottom',
				'options'   => [
					'bottom' => __( 'Bottom', 'ideapark-goldish' ),
					'middle' => __( 'Middle', 'ideapark-goldish' ),
				],
				'condition' => [
					'layout' => 'layout-1',
				],
			]
		);

		$this->add_control(
			'button_type',
			[
				'label'   => __( 'Button Type', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'ideapark-goldish' ),
					'outline' => __( 'Outline', 'ideapark-goldish' ),
				]
			]
		);

		$this->add_control(
			'slider_animation',
			[
				'label'   => __( 'Animation', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''               => __( 'Default', 'ideapark-goldish' ),
					'banners-fade'   => __( 'Fade', 'ideapark-goldish' ),
					'owl-fade-scale' => __( 'Fade and Scale', 'ideapark-goldish' ),
				]
			]
		);

		$this->add_control(
			'slider_autoplay',
			[
				'label'     => __( 'Autoplay', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Yes', 'ideapark-goldish' ),
				'label_off' => __( 'No', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'slider_animation_timeout',
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
					'slider_autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'arrows',
			[
				'label'     => __( 'Arrows', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Show', 'ideapark-goldish' ),
				'label_off' => __( 'Hide', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'dots',
			[
				'label'     => __( 'Navigation dots', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Show', 'ideapark-goldish' ),
				'label_off' => __( 'Hide', 'ideapark-goldish' ),
			]
		);


		$this->add_control(
			'random',
			[
				'label'     => __( 'Random sorting', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'label_on'  => __( 'Yes', 'ideapark-goldish' ),
				'label_off' => __( 'No', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'fixed_height',
			[
				'label'     => __( 'Fixed height', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'label_on'  => __( 'Yes', 'ideapark-goldish' ),
				'label_off' => __( 'No', 'ideapark-goldish' ),
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'      => __( 'Height', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1280,
					],
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-slider__item' => 'height: {{SIZE}}{{UNIT}};min-height:unset;max-height: unset;',
				],

				'condition' => [
					'fixed_height' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'font_size',
			[
				'label'      => __( 'Custom title font size', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 20,
						'max' => 100,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-slider__title' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'font_size_subtitle_title',
			[
				'label'      => __( 'Custom subtitle font size', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 30,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-slider__subtitle' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_slider_colors',
			[
				'label' => __( 'Colors', 'ideapark-goldish' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __( 'Text Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .c-ip-slider' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_nav_dots',
			[
				'label'     => __( 'Arrows and Dots Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					'{{WRAPPER}} .h-carousel .owl-prev' => 'color: {{VALUE}}',
					'{{WRAPPER}} .h-carousel .owl-next' => 'color: {{VALUE}}',
					'{{WRAPPER}} .owl-dots'             => 'color: {{VALUE}};',
					'{{WRAPPER}} .c-ip-slider__circle'  => 'color: {{VALUE}};',
					'{{WRAPPER}} .c-ip-slider__scroll'  => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'content_bg_color',
			[
				'label'     => __( 'Content Background Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-ip-slider__wrap--layout-2' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'layout' => 'layout-2'
				]
			]
		);

		$this->add_control(
			'content_bg_image',
			[
				'label'     => __( 'Content Background Image', 'ideapark-goldish' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [
					'layout' => 'layout-2'
				]
			]
		);

		$this->add_control(
			'default_bg_color',
			[
				'label'     => __( 'Button Background Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button--default' => 'background-color: {{VALUE}};border-color: {{VALUE}};',
				],
				'condition' => [
					'button_type' => 'default'
				]
			]
		);

		$this->add_control(
			'default_text_color',
			[
				'label'     => __( 'Button Text Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button--default' => 'color: {{VALUE}};',
				],
				'condition' => [
					'button_type' => 'default'
				]
			]
		);

		$this->add_control(
			'outline_text_color',
			[
				'label'     => __( 'Button Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button--outline' => 'color: {{VALUE}};border-color: {{VALUE}};',
				],
				'condition' => [
					'button_type' => 'outline'
				]
			]
		);

		$this->add_control(
			'hover_bg_color',
			[
				'label'     => __( 'Button Background Color On Hover', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button:hover' => 'background-color: {{VALUE}};border-color: {{VALUE}};',
				]
			]
		);

		$this->add_control(
			'hover_text_color',
			[
				'label'     => __( 'Button Text Color On Hover', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button:hover' => 'color: {{VALUE}};',
				]
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render slider widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div
			class="c-ip-slider <?php if ( $settings['fixed_height'] == 'yes' ) { ?> c-ip-slider--fixed-height<?php } ?> c-ip-slider--<?php echo ideapark_mod( 'header_type' ); ?> c-ip-slider--<?php echo $settings['layout']; ?> js-slider">
			<div
				class="c-ip-slider__list c-ip-slider__list--<?php echo $settings['layout']; ?> js-slider-carousel h-carousel h-carousel--big-dots <?php if ( $settings['dots'] != 'yes' ) { ?> h-carousel--dots-hide<?php } else { ?> c-ip-slider__list--dots <?php if ( $settings['slider_autoplay'] == 'yes' ) { ?> h-carousel--dot-animated <?php } ?><?php } ?> <?php if ( $settings['arrows'] != 'yes' ) { ?> h-carousel--nav-hide<?php } ?>"
				data-autoplay="<?php echo esc_attr( $settings['slider_autoplay'] ); ?>"
				data-animation="<?php echo esc_attr( $settings['slider_animation'] ); ?>"
				<?php if ( ! empty( $settings['slider_animation_timeout']['size'] ) ) { ?>
					data-animation-timeout="<?php echo esc_attr( abs( $settings['slider_animation_timeout']['size'] * 1000 ) ); ?>"
				<?php } ?>
				data-widget-id="<?php echo esc_attr( $this->get_id() ); ?>">
				<?php
				if ( $settings['random'] == 'yes' ) {
					shuffle( $settings['slider_list'] );
				}
				?>
				<?php foreach ( $settings['slider_list'] as $index => $item ) { ?>
					<?php $dot = $settings['slider_autoplay'] == 'yes' ? '<svg role="button" data-index="' . $index . '" class="c-ip-slider__circle" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid"><path d="M0,0 " id="arc-' . $this->get_id() . '-' . $index . '" fill="none" stroke="inherit" stroke-width="1"/></svg><button role="button" class="h-cb c-ip-slider__dot" ></button>' : '<button role="button" class="h-cb c-ip-slider__dot" ></button>'; ?>
					<div
						class="c-ip-slider__item c-ip-slider__item--<?php echo $settings['layout']; ?> elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>"
						data-dot="<?php echo esc_attr( $dot ); ?>"
						data-index="<?php echo esc_attr( $index ); ?>">
						<?php
						$has_desktop_image = ! empty( $item['image_desktop']['id'] );
						$has_mobile_image  = ! empty( $item['image_mobile']['id'] );
						if ( $has_desktop_image ) {
							echo ideapark_img( ideapark_image_meta( $item['image_desktop']['id'] ), 'c-ip-slider__image c-ip-slider__image--' . $settings['layout'] . ( $has_mobile_image ? ' c-ip-slider__image--desktop' : '' ), $index && ideapark_mod( 'lazyload' ) ?: 'eager', [ 'data-index' => $index ] );
						}
						if ( $has_mobile_image ) {
							echo ideapark_img( ideapark_image_meta( $item['image_mobile']['id'] ), 'c-ip-slider__image c-ip-slider__image--' . $settings['layout'] . ' c-ip-slider__image--mobile', $index && ideapark_mod( 'lazyload' ) ?: 'eager', [ 'data-index' => $index ] );
						}
						if ( ! empty( $item['button_link']['url'] ) ) {
							$link_key = 'link_' . $index;
							$this->add_link_attributes( $link_key, $item['button_link'] );
							if ( $item['button_text'] ) {
								$this->add_render_attribute( $link_key, 'class', 'c-button c-button--' . $settings['button_type'] . ' c-ip-slider__button c-ip-slider__button--' . $settings['layout'] );
							} else {
								$this->add_render_attribute( $link_key, 'class', 'c-ip-slider__link' );
							}
						} else {
							$link_key = '';
						}
						?>
						<div
							class="c-ip-slider__wrap c-ip-slider__wrap--<?php echo $settings['layout']; ?> <?php if ( $settings['layout'] == 'layout-1' ) { ?>c-ip-slider__wrap--<?php echo $settings['vertical_align']; ?><?php } ?>">
							<?php if ( $settings['layout'] == 'layout-2' && ! empty( $settings['content_bg_image']['id'] ) && ( $type = get_post_mime_type( $settings['content_bg_image']['id'] ) ) ) {
								if ( $type == 'image/svg+xml' ) {
									echo ideapark_get_inline_svg( $settings['content_bg_image']['id'], 'c-ip-slider__bg-image' );
								} else {
									echo ideapark_img( ideapark_image_meta( $settings['content_bg_image']['id'] ), 'c-ip-slider__bg-image', $index && ideapark_mod( 'lazyload' ) ?: 'eager' );
								}
							} ?>
							<?php echo ideapark_wrap( $item['title'], '<div class="c-ip-slider__title c-ip-slider__title--' . $settings['layout'] . '"><span class="c-ip-slider__title-inner">', '</span></div>' ); ?>
							<?php echo ideapark_wrap( $item['subtitle'], '<div class="c-ip-slider__subtitle c-ip-slider__subtitle--' . $settings['layout'] . '"><span class="c-ip-slider__subtitle-inner">', '</span></div>' ); ?>
							<?php if ( $link_key && $item['button_text'] ) { ?>
								<a <?php echo $this->get_render_attribute_string( $link_key ); ?>><?php echo esc_html( $item['button_text'] ); ?></a>
							<?php } ?>
						</div>
						<?php if ( $link_key && ! $item['button_text'] ) { ?>
							<a <?php echo $this->get_render_attribute_string( $link_key ); ?>></a>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render slider widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected
	function content_template() {
	}
}
