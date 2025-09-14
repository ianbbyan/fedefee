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
 * Elementor banners widget.
 *
 * Elementor widget that displays a bullet list with any chosen icons and texts.
 *
 * @since 1.0.0
 */
class Ideapark_Elementor_Banners extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve banners widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'ideapark-banners';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve banners widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Banners', 'ideapark-goldish' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve banners widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
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
		return [ 'banners', 'image', 'list' ];
	}

	/**
	 * Register banners widget controls.
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
				'label' => __( 'Banners', 'ideapark-goldish' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			[
				'label'   => __( 'Choose image', 'ideapark-goldish' ),
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
			'subheader',
			[
				'label'       => __( 'Subheader', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '',
				'placeholder' => __( 'Enter banner subheader', 'ideapark-goldish' ),
				'label_block' => true,
			]
		);


		$repeater->add_control(
			'header',
			[
				'label'       => __( 'Header', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( 'Header', 'ideapark-goldish' ),
				'placeholder' => __( 'Enter banner header', 'ideapark-goldish' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'button_text',
			[
				'label'       => __( 'Button text', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'Enter text', 'ideapark-goldish' ),
				'default'     => __( 'Shop now', 'ideapark-goldish' ),
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
			'background_color',
			[
				'label'     => __( 'Background color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.c-ip-banners__item--layout-1'        => 'background-color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}}.c-ip-banners__item--layout-2'        => 'background-color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}}.c-ip-banners__item--layout-3:before' => 'background-color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'text_color',
			[
				'label'     => __( 'Text color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'color: {{VALUE}};',
				],
			]
		);

		$repeater->add_control(
			'accent_color',
			[
				'label'     => __( 'Accent color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .c-ip-banners__subheader' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'banner_list',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ header }}}',
			]
		);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_image_settings',
			[
				'label' => __( 'Banners Settings', 'ideapark-goldish' ),
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
			'text_color',
			[
				'label'     => __( 'Text color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .c-ip-banners__item' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label'     => __( 'Accent color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .c-ip-banners__subheader' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'background_color',
			[
				'label'     => __( 'Background color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .c-ip-banners__item--layout-1'        => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .c-ip-banners__item--layout-2'        => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .c-ip-banners__item--layout-3:before' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label'     => __( 'Border color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .c-ip-banners--layout-1 .c-ip-banners__list:after'                                         => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .c-ip-banners--layout-1 .c-ip-banners__list:before'                                        => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .c-ip-banners--layout-1 .c-ip-banners__list--static .c-ip-banners__item:not(:first-child)' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'layout' => 'layout-1',
				],
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label'     => __( 'Hover color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'(desktop) {{WRAPPER}} .c-ip-banners__item--layout-1.c-ip-banners__item--link:hover .c-ip-banners__header' => 'color: {{VALUE}};',
					'(desktop) {{WRAPPER}} .c-ip-banners__item--layout-2.c-ip-banners__item--link:hover .c-ip-banners__header' => 'color: {{VALUE}};',
				],
				'condition' => [
					'layout' => [ 'layout-1', 'layout-2' ],
				],
			]
		);


		$this->add_responsive_control(
			'font_size',
			[
				'label'      => __( 'Header font size', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 38,
				],
				'range'      => [
					'px' => [
						'min' => 12,
						'max' => 50,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-banners__header' => 'font-size: {{SIZE}}{{UNIT}};',
				],
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
						'max' => 600,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-banners__wrap' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'min_height',
			[
				'label'      => __( 'Min banner height', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 50,
						'max' => 600,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-banners__item--layout-1'                     => 'min-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .c-ip-banners__item--layout-2'                     => 'min-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .c-ip-banners__item--layout-3 .c-ip-banners__wrap' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'random_sorting',
			[
				'label'        => __( 'Random sorting', 'ideapark-goldish' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ideapark-goldish' ),
				'label_off'    => __( 'No', 'ideapark-goldish' ),
				'return_value' => 'yes',
			]
		);


		$this->add_control(
			'banner_animation',
			[
				'label'   => __( 'Autoplay', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''                   => __( 'Disabled', 'ideapark-goldish' ),
					'banners-fade'       => __( 'Fade', 'ideapark-goldish' ),
					'banners-fade-scale' => __( 'Fade and Scale', 'ideapark-goldish' ),
					'banners-slide-up'   => __( 'Slide Up', 'ideapark-goldish' ),
				]
			]
		);

		$this->add_control(
			'banner_animation_timeout',
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
					'banner_animation!' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render banners widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="c-ip-banners c-ip-banners--<?php echo $settings['layout']; ?>">
			<div
				class="c-ip-banners__list c-ip-banners__list--<?php echo sizeof( $settings['banner_list'] ); ?><?php ideapark_class( $settings['banner_animation'], 'c-ip-banners__list--animated js-ip-banners', 'c-ip-banners__list--static' ); ?>"
				data-layout="<?php echo esc_attr( $settings['layout'] ); ?>"
				<?php if ( $settings['banner_animation'] ) { ?>
					data-animation="<?php echo esc_attr( $settings['banner_animation'] ); ?>"
					data-animation-timeout="<?php echo esc_attr( abs( $settings['banner_animation_timeout']['size'] * 1000 ) ); ?>"
				<?php } ?>>
				<?php
				if ( $settings['random_sorting'] ) {
					shuffle( $settings['banner_list'] );
				}
				foreach ( $settings['banner_list'] as $index => $item ) : ?>
					<?php
					if ( ! empty( $item['link']['url'] ) ) {
						$is_link  = true;
						$link_key = 'link_' . $index;

						$this->add_link_attributes( $link_key, $item['link'] );
						$this->add_render_attribute( $link_key, 'class', 'c-ip-banners__link' );
						$this->add_render_attribute( $link_key, 'aria-label', $item['header']);
					} else {
						$is_link = false;
					}
					$item_id = ( ! empty( $item['image']['id'] ) ? $item['image']['id'] . '-' : '' ) . substr( md5( $item['header'] . ( $is_link ? $item['link']['url'] : '' ) ), 0, 8 );
					?>
					<div
						data-id="<?php echo esc_attr( $item_id ); ?>"
						class="c-ip-banners__item c-ip-banners__item--<?php echo $settings['layout']; ?> <?php if ( $is_link ) { ?> c-ip-banners__item--link<?php } ?> elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>">

						<?php if ( $settings['layout'] == 'layout-3' ) { ?>
						<div class="c-ip-banners__image-wrap">
							<?php } ?>
							<?php if ( ! empty( $item['image']['id'] ) && ( $type = get_post_mime_type( $item['image']['id'] ) ) ) {
								if ( $type == 'image/svg+xml' ) {
									echo ideapark_get_inline_svg( $item['image']['id'], 'c-ip-banners__svg' );
								} else {
									echo ideapark_img( ideapark_image_meta( $item['image']['id'], 'full', '(min-width: 768px) 33vw, (min-width: 415px) 415px, 100vw' ), 'c-ip-banners__image' );
								}
							}
							?>
							<?php if ( $settings['layout'] == 'layout-3' ) { ?>
						</div>
					<?php } ?>
						<div
							class="c-ip-banners__wrap">
							<?php if ( ! empty( $item['subheader'] ) ) { ?>
								<div
									class="c-ip-banners__subheader"><?php echo nl2br(esc_html( $item['subheader'] )); ?></div>
							<?php } ?>
							<?php if ( ! empty( $item['header'] ) ) { ?>
								<div class="c-ip-banners__header"><span
										class="c-ip-banners__header-size"><?php echo nl2br(esc_html( $item['header'] )); ?></span>
								</div>
							<?php } ?>
							<?php if ( $is_link && $item['button_text'] ) { ?>
								<span
									class="c-ip-banners__button"><?php echo esc_html( $item['button_text'] ); ?><i
										class="c-ip-banners__button-icon ip-banner-more"></i></span>
							<?php } elseif ( $settings['layout'] != 'layout-3' ) { ?>
								<span class="c-ip-banners__spacer"></span>
							<?php } ?>
						</div>
						<?php if ( $is_link ) { ?>
							<a <?php echo $this->get_render_attribute_string( $link_key ); ?>></a>
						<?php } ?>
					</div>
				<?php
				endforeach;
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render banners widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function content_template() {
	}
}
