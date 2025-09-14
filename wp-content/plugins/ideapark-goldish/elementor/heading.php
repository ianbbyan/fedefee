<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor heading widget.
 *
 * Elementor widget that displays an eye-catching headlines.
 *
 * @since 1.0.0
 */
class Ideapark_Elementor_Heading extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve heading widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'ideapark-heading';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve heading widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Goldish Heading', 'ideapark-goldish' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve heading widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'eicon-t-letter';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the heading widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * @return array Widget categories.
	 * @since  2.0.0
	 * @access public
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
		return [ 'heading', 'title', 'text' ];
	}

	/**
	 * Register heading widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Title', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Title', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => __( 'Enter your title', 'ideapark-goldish' ),
				'default'     => __( 'Add Your Heading Text Here', 'ideapark-goldish' ),
				'dynamic'     => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label'     => __( 'Link', 'ideapark-goldish' ),
				'type'      => Controls_Manager::URL,
				'default'   => [
					'url' => '',
				],
				'separator' => 'before',
				'dynamic'   => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'header_size',
			[
				'label'   => __( 'HTML Tag', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default' => 'div',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_title_style',
			[
				'label' => __( 'Title Settings', 'ideapark-goldish' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'size',
			[
				'label'   => __( 'Size', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'ideapark-goldish' ),
					'large'   => __( 'Large', 'ideapark-goldish' ),
				],
			]
		);

		$this->add_control(
			'bullet',
			[
				'label'   => __( 'Divider', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'hide',
				'options' => [
					'hide'  => __( 'Hide', 'ideapark-goldish' ),
					'after' => __( 'After', 'ideapark-goldish' ),
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => __( 'Alignment', 'ideapark-goldish' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => __( 'Left', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => __( 'Center', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => __( 'Right', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}} .c-ip-heading' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_color',
			[
				'label'     => __( 'Text Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					// Stronger selector to avoid section style from overwriting
					'{{WRAPPER}} .c-ip-heading' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'background_color',
			[
				'label'     => __( 'Background Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					// Stronger selector to avoid section style from overwriting
					'{{WRAPPER}} .c-ip-heading__inner' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'inner_padding',
			[
				'label'      => __( 'Inner Padding', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-heading .c-ip-heading__inner' => 'padding: calc({{SIZE}}{{UNIT}} / 2) {{SIZE}}{{UNIT}} calc({{SIZE}}{{UNIT}} / 2 + 0.1em) {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'max_width',
			[
				'label'      => __( 'Max width', 'ideapark-goldish' ),
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
					'{{WRAPPER}} .c-ip-heading .c-ip-heading__inner' => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .c-ip-heading--bullet-after:after'  => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'block_align',
			[
				'label'   => __( 'Block alignment', 'ideapark-goldish' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [
						'title' => __( 'Left', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default' => '',
			]
		);

		$this->add_control(
			'starting_number',
			[
				'label'   => __( 'Starting Number', 'ideapark-goldish' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .c-ip-heading',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .c-ip-heading',
			]
		);

		$this->add_control(
			'blend_mode',
			[
				'label'     => __( 'Blend Mode', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => __( 'Normal', 'ideapark-goldish' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'luminosity'  => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} .c-ip-heading' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render heading widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		if ( '' === $settings['title'] ) {
			return;
		}

		$this->add_render_attribute( 'title', 'class', 'c-ip-heading' );
		$this->add_render_attribute( 'title', 'class', 'c-ip-heading--' . $settings['size'] );
		$this->add_render_attribute( 'title', 'class', 'c-ip-heading--' . ( ! empty( $settings['align'] ) ? $settings['align'] : 'left' ) );
		if ( ! empty( $settings['align_tablet'] ) ) {
			$this->add_render_attribute( 'title', 'class', 'c-ip-heading--tablet-' . $settings['align_tablet'] );
		}
		if ( ! empty( $settings['align_mobile'] ) ) {
			$this->add_render_attribute( 'title', 'class', 'c-ip-heading--mobile-' . $settings['align_mobile'] );
		}
		$this->add_render_attribute( 'title', 'class', 'c-ip-heading--bullet-' . $settings['bullet'] );
		if ( $settings['block_align'] ) {
			$this->add_render_attribute( 'title', 'class', 'c-ip-heading--block-align-' . $settings['block_align'] );
		}

		$title = nl2br( $settings['title'] );

		if ( $settings['size'] == 'default' ) {
			$title = '<p class="c-ip-heading_p">' . preg_replace( '~(<br>|<br />)([\s\t\r\n]*(<br>|<br />))+~im', '</p><p class="c-ip-heading_p">', $title ) . '</p>';
		}

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'url', $settings['link'] );

			$title = sprintf( '<a %1$s>%2$s</a>', $this->get_render_attribute_string( 'url' ), $title );
		}

		$title_html = sprintf( '<%1$s %2$s><div class="c-ip-heading__inner">%3$s</div></%1$s>', $settings['header_size'], $this->get_render_attribute_string( 'title' ), $title );

		echo $title_html;
	}

	/**
	 * Render heading widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function content_template() {

	}
}
