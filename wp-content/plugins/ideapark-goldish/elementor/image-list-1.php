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
 * Elementor image list widget.
 *
 * Elementor widget that displays a bullet list with any chosen icons and texts.
 *
 * @since 1.0.0
 */
class Ideapark_Elementor_Image_List_1 extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve image list widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'ideapark-image-list-1';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve image list widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Image List', 'ideapark-goldish' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve image list widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'ip-icon-list';
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
		return [ 'image list', 'image', 'list' ];
	}

	/**
	 * Register image list widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_image',
			[
				'label' => __( 'Image List', 'ideapark-goldish' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'label'   => __( 'Choose Image', 'ideapark-goldish' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
				],
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);


		$repeater->add_control(
			'title_text',
			[
				'label'       => __( 'Title', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'This is the heading', 'ideapark-goldish' ),
				'placeholder' => __( 'Enter your title', 'ideapark-goldish' ),
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

		$this->add_control(
			'icon_list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title_text }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'List Settings', 'ideapark-goldish' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => __( 'Layout', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid'     => __( 'Grid', 'ideapark-goldish' ),
					'carousel' => __( 'Carousel', 'ideapark-goldish' ),
					'combined' => __( 'Grid on Desktop / Carousel on Tablet and Mobile', 'ideapark-goldish' ),
				]
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
				'condition' => [
					'layout' => [ 'carousel', 'combined' ],
				],
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
				'condition' => [
					'layout' => [ 'carousel', 'combined' ],
				],
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
					'{{WRAPPER}} .c-ip-image-list-1' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_hover',
			[
				'label'       => __( 'Text Color on Hover', 'ideapark-goldish' ),
				'description' => __( 'Select color or leave empty for display default.', 'ideapark-goldish' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '',
				'selectors'   => [
					'{{WRAPPER}} .c-ip-image-list-1__item:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'zoom',
			[
				'label'     => __( 'Zoom on Hover', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Yes', 'ideapark-goldish' ),
				'label_off' => __( 'No', 'ideapark-goldish' ),
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => __( 'Icon size', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 60,
				],
				'range'      => [
					'px' => [
						'min' => 16,
						'max' => 100,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-1__thumb' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'font_size',
			[
				'label'      => __( 'Font size', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 14,
				],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 22,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-1__item' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label'      => __( 'Space between icon and text', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 25,
				],
				'range'      => [
					'px' => [
						'min' => 5,
						'max' => 30,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-1__title' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_width',
			[
				'label'      => __( 'Item Width', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'size' => 160,
					'unit' => 'px',
				],
				'range'      => [
					'px' => [
						'min' => 90,
						'max' => 250,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-1__item' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label'      => __( 'Item padding', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 25,
				],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-1__wrap' => 'margin: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .c-ip-image-list-1__item' => 'padding: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render image list widget output on the frontend.
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
			class="c-ip-image-list-1 c-ip-image-list-1--<?php echo $settings['layout']; ?><?php if ( in_array( $settings['layout'], [
					'carousel',
					'combined'
				] ) && $settings['arrows'] == 'yes' ) { ?> c-ip-image-list-1--nav<?php } ?><?php if ( in_array( $settings['layout'], [
					'carousel',
					'combined'
				] ) && $settings['dots'] == 'yes' ) { ?> c-ip-image-list-1--dots<?php } ?>">
			<div class="c-ip-image-list-1__wrap c-ip-image-list-1__wrap--<?php echo $settings['layout']; ?>">
				<div
					class="c-ip-image-list-1__list c-ip-image-list-1__list--<?php echo $settings['layout']; ?> <?php if ( in_array( $settings['layout'], [
						'carousel',
						'combined'
					] ) ) { ?> js-image-list-1 h-carousel <?php if ( in_array( $settings['layout'], [
							'carousel',
							'combined'
						] ) && $settings['dots'] == 'yes' ) { ?> h-carousel--default-dots<?php } else { ?> h-carousel--dots-hide<?php } ?> <?php if ( $settings['arrows'] != 'yes' ) { ?>h-carousel--nav-hide<?php } else { ?>h-carousel--hover h-carousel--round h-carousel--outline h-carousel--tr h-carousel--border<?php } ?><?php } ?>">
					<?php
					foreach ( $settings['icon_list'] as $index => $item ) : ?>
						<div
							class="c-ip-image-list-1__item c-ip-image-list-1__item--<?php echo $settings['layout']; ?><?php ideapark_class( $settings['zoom'], 'c-ip-image-list-1__item--zoom', '' ); ?>">
							<?php
							if ( ! empty( $item['link']['url'] ) ) {
								$is_link  = true;
								$link_key = 'link_' . $index;

								$this->add_link_attributes( $link_key, $item['link'] );
								$this->add_render_attribute( $link_key, 'class', 'c-ip-image-list-1__link' );
							} else {
								$is_link = false;
							} ?>
							<?php if ( $is_link ) { ?>
							<a <?php echo $this->get_render_attribute_string( $link_key ); ?>>
								<?php } ?>
								<div class="c-ip-image-list-1__thumb">
									<?php if ( ! empty( $item['image']['id'] ) && ( $type = get_post_mime_type( $item['image']['id'] ) ) ) {
										if ( $type == 'image/svg+xml' ) {
											echo ideapark_get_inline_svg( $item['image']['id'], 'c-ip-image-list-1__svg' );
										} else {
											echo ideapark_img( ideapark_image_meta( $item['image']['id'], 'thumbnail' ), 'c-ip-image-list-1__image' );
										}
									}
									?>
								</div>
								<?php if ( ! empty( $item['title_text'] ) ) { ?>
									<div class="c-ip-image-list-1__title"><?php echo $item['title_text']; ?></div>
								<?php } ?>
								<?php if ( $is_link ) { ?>
							</a>
						<?php } ?>
						</div>
					<?php
					endforeach;
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render image list widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function content_template() {
	}
}
