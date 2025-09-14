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
class Ideapark_Elementor_Banners_Vert extends Widget_Base {

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
		return 'ideapark-banners-vert';
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
		return __( 'Vertical Banners', 'ideapark-goldish' );
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
		return [ 'banners', 'vertical', 'image', 'list' ];
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
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);


		$repeater->add_control(
			'header',
			[
				'label'       => __( 'Header', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Header', 'ideapark-goldish' ),
				'placeholder' => __( 'Enter banner header', 'ideapark-goldish' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'subheader',
			[
				'label'       => __( 'Subheader', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Enter banner subheader', 'ideapark-goldish' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'button_text',
			[
				'label'       => __( 'Button text', 'ideapark-goldish' ),
				'description' => __( 'Leave empty for a link to the entire block', 'ideapark-goldish' ),
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
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'background-color: {{VALUE}};',
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
			'text_color',
			[
				'label'     => __( 'Text color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .c-ip-banners-vert__item' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .c-ip-banners-vert__item' => 'background-color: {{VALUE}};',
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
					'size' => 34,
				],
				'range'      => [
					'px' => [
						'min' => 12,
						'max' => 50,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'.c-ip-banners-vert__header-size'                  => 'font-size: {{SIZE}}{{UNIT}};',
					'(desktop) {{WRAPPER}} .c-ip-banners-vert__header' => 'font-size: calc({{SIZE}}{{UNIT}} * 0.67647 + ({{SIZE}} * 0.32353) * ((100vw - 1190px) / (1920 - 1190)));',
					'(tablet) {{WRAPPER}} .c-ip-banners-vert__header'  => 'font-size: calc({{SIZE}}{{UNIT}} * 0.67647 + ({{SIZE}} * 0.32353) * ((100vw - 768px) / (1189 - 768)));',
					'(mobile) {{WRAPPER}} .c-ip-banners-vert__header'  => 'font-size: calc({{SIZE}}{{UNIT}} * 0.67647 + ({{SIZE}} * 0.32353) * ((100vw - 320px) / (767 - 320)));',
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
		<div class="c-ip-banners-vert">
			<div
				class="c-ip-banners-vert__list c-ip-banners-vert__list--<?php echo sizeof( $settings['banner_list'] ); ?>">
				<?php
				foreach ( $settings['banner_list'] as $index => $item ) : ?>
					<?php
					if ( ! empty( $item['link']['url'] ) ) {
						$is_link  = true;
						$link_key = 'link_' . $index;

						$this->add_link_attributes( $link_key, $item['link'] );
						$this->add_render_attribute( $link_key, 'class', $item['button_text'] ? 'c-ip-banners-vert__button c-button c-button--outline-white' : 'c-ip-banners-vert__link' );
					} else {
						$is_link = false;
					}
					$item_id = ( ! empty( $item['image']['id'] ) ? $item['image']['id'] . '-' : '' ) . substr( md5( $item['header'] . ( $is_link ? $item['link']['url'] : '' ) ), 0, 8 );
					?>
					<div
						data-id="<?php echo esc_attr( $item_id ); ?>"
						class="c-ip-banners-vert__item <?php if ( $is_link ) { ?> c-ip-banners-vert__item--link<?php } ?> elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>">

						<?php if ( ! empty( $item['image']['id'] ) && ( $type = get_post_mime_type( $item['image']['id'] ) ) ) {
							if ( $type == 'image/svg+xml' ) {
								echo ideapark_get_inline_svg( $item['image']['id'], 'c-ip-banners-vert__svg' );
							} else {
								echo ideapark_img( ideapark_image_meta( $item['image']['id'] ), 'c-ip-banners-vert__image' );
							}
						}
						?>

						<div
							class="c-ip-banners-vert__wrap">
							<?php if ( ! empty( $item['header'] ) ) { ?>
								<div class="c-ip-banners-vert__header"><span
										class="c-ip-banners-vert__header-size"><?php echo esc_html( $item['header'] ); ?></span>
								</div>
							<?php } ?>
							<?php if ( ! empty( $item['subheader'] ) ) { ?>
								<div
									class="c-ip-banners-vert__subheader"><?php echo esc_html( $item['subheader'] ); ?></div>
							<?php } ?>
							<?php if ( $is_link && $item['button_text'] ) { ?>
								<a <?php echo $this->get_render_attribute_string( $link_key ); ?>>
									<?php echo esc_html( $item['button_text'] ); ?>
								</a>
							<?php } ?>
						</div>
						<?php if ( $is_link && ! $item['button_text'] ) { ?>
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
