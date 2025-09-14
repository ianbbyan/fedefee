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
class Ideapark_Elementor_Image_List_3 extends Widget_Base {

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
		return 'ideapark-image-list-3';
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
		return __( 'Categories', 'ideapark-goldish' );
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
		return [ 'category', 'image list', 'image', 'list' ];
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
				'label' => __( 'Category List', 'ideapark-goldish' ),
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
				]
			]
		);

		$this->add_responsive_control(
			'items_per_row',
			[
				'label'     => __( 'Items per row', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''               => __( 'Default', 'ideapark-goldish' ),
					'100%'           => __( '1', 'ideapark-goldish' ),
					'calc(100% / 2)' => __( '2', 'ideapark-goldish' ),
					'calc(100% / 3)' => __( '3', 'ideapark-goldish' ),
					'calc(100% / 4)' => __( '4', 'ideapark-goldish' ),
					'calc(100% / 5)' => __( '5', 'ideapark-goldish' ),
				],
				'devices'   => [ 'desktop', 'tablet' ],
				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-3__item--grid' => 'width: {{value}};',
				],
				'condition' => [
					'layout' => 'grid',
				],
			]
		);

		$this->add_responsive_control(
			'item_height',
			[
				'label'      => __( 'Item height', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'size' => 300,
					'unit' => 'px',
				],
				'range'      => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-3__wrap' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'carousel_on_mobile',
			[
				'label'     => __( 'Carousel on mobile', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'label_on'  => __( 'Yes', 'ideapark-goldish' ),
				'label_off' => __( 'No', 'ideapark-goldish' ),
				'condition' => [
					'layout' => 'grid',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'      => __( 'Autoplay', 'ideapark-goldish' ),
				'type'       => Controls_Manager::SWITCHER,
				'label_on'   => __( 'Yes', 'ideapark-goldish' ),
				'label_off'  => __( 'No', 'ideapark-goldish' ),
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'carousel'
						],
						[
							'name'     => 'carousel_on_mobile',
							'operator' => '==',
							'value'    => 'yes'
						]
					]
				],
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

		$this->add_control(
			'arrows',
			[
				'label'      => __( 'Arrows', 'ideapark-goldish' ),
				'type'       => Controls_Manager::SWITCHER,
				'default'    => 'yes',
				'label_on'   => __( 'Show', 'ideapark-goldish' ),
				'label_off'  => __( 'Hide', 'ideapark-goldish' ),
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'carousel'
						],
						[
							'name'     => 'carousel_on_mobile',
							'operator' => '==',
							'value'    => 'yes'
						]
					]
				],
			]
		);

		$this->add_control(
			'arrows_layout',
			[
				'label'     => __( 'Arrows Layout', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => [
					'default' => __( 'Default', 'ideapark-goldish' ),
					'round'   => __( 'Round', 'ideapark-goldish' ),
				],
				'condition' => [
					'arrows' => 'yes',
				],
			]
		);

		$this->add_control(
			'dots',
			[
				'label'      => __( 'Navigation dots', 'ideapark-goldish' ),
				'type'       => Controls_Manager::SWITCHER,
				'default'    => 'no',
				'label_on'   => __( 'Show', 'ideapark-goldish' ),
				'label_off'  => __( 'Hide', 'ideapark-goldish' ),
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'layout',
							'operator' => '==',
							'value'    => 'carousel'
						],
						[
							'name'     => 'carousel_on_mobile',
							'operator' => '==',
							'value'    => 'yes'
						]
					]
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
					'size' => 23,
				],
				'range'      => [
					'px' => [
						'min' => 12,
						'max' => 40,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-3__title' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'darkening',
			[
				'label'     => __( 'Darkening', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 0.2,
				],
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-3__shadow' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'darkening_hover',
			[
				'label'     => __( 'Darkening on Hover', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 0.4,
				],
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-3__item:hover .c-ip-image-list-3__shadow' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_width',
			[
				'label'      => __( 'Title Width', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'size' => 100,
					'unit' => '%',
				],
				'range'      => [
					'px' => [
						'min' => 90,
						'max' => 500,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-image-list-3__title' => 'max-width: {{SIZE}}{{UNIT}};',
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
		$class    = "js-image-list-3 h-carousel h-carousel--flex "
		            . ( $settings['dots'] == 'yes' ? ' h-carousel--default-dots' : ' h-carousel--dots-hide' )
		            . ( $settings['arrows'] == 'yes' ? ' h-carousel--inner h-carousel--hover' : ' h-carousel--nav-hide' );

		$count = sizeof( $settings['icon_list'] );

		if ( $settings['layout'] == 'grid' ) {
			$sizes = '(min-width: 1190px) ' . ( $settings['items_per_row'] ? str_replace( '%', 'vw', $settings['items_per_row'] ) : 'calc(100vw / 3)' ) . ',' .
			         '(min-width: 768) ' . ( ! empty( $settings['items_per_row_tablet'] ) ? str_replace( '%', 'vw', $settings['items_per_row_tablet'] ) : 'calc(100vw / 2)' ) . ',' .
			         '100vw';
		} else {
			$sizes = '(min-width: 1190px) calc(100vw / ' . min( $count, 6 ) . '),' .
			         '(min-width: 768) calc(100vw / ' . min( $count, 3 ) . '),' .
			         '100vw';
		}

		?>
		<div
			class="c-ip-image-list-3 c-ip-image-list-3--<?php echo $settings['layout']; ?>">
			<div
				class="c-ip-image-list-3__list c-ip-image-list-3__list--<?php echo $count; ?> c-ip-image-list-3__list--<?php echo $settings['layout']; ?> <?php if ( $settings['layout'] == 'carousel' ) {
					echo $class;
				} ?> <?php if ( $settings['layout'] == 'grid' && $settings['carousel_on_mobile'] == 'yes' ) { ?> c-ip-image-list-3__list--combined js-image-list-3-combined<?php } ?>
				<?php if (!empty($settings['arrows_layout']) && $settings['arrows_layout'] == 'round') { ?> h-carousel--border h-carousel--round<?php } ?>"
				data-layout="<?php echo esc_attr( $settings['layout'] ); ?>"
				data-count="<?php echo $count; ?>"
				<?php if ( ! empty( $settings['autoplay'] ) ) { ?>
					data-autoplay="<?php echo esc_attr( $settings['autoplay'] ); ?>"
					<?php if ( ! empty( $settings['animation_timeout']['size'] ) ) { ?>
						data-animation-timeout="<?php echo esc_attr( abs( $settings['animation_timeout']['size'] * 1000 ) ); ?>"
					<?php } ?>
				<?php } ?>
				<?php if ( $settings['layout'] == 'grid' && $settings['carousel_on_mobile'] == 'yes' ) { ?>data-combined="<?php echo esc_attr( $class ); ?>"<?php } ?>>
				<?php
				foreach ( $settings['icon_list'] as $index => $item ) : ?>
					<div
						class="c-ip-image-list-3__item c-ip-image-list-3__item--<?php echo $count; ?> c-ip-image-list-3__item--<?php echo $settings['layout']; ?>">
						<?php
						if ( ! empty( $item['link']['url'] ) ) {
							$is_link  = true;
							$link_key = 'link_' . $index;

							$this->add_link_attributes( $link_key, $item['link'] );
							$this->add_render_attribute( $link_key, 'class', 'c-ip-image-list-3__link' );
						} else {
							$is_link = false;
						} ?>
						<?php if ( $is_link ) { ?>
						<a <?php echo $this->get_render_attribute_string( $link_key ); ?>>
							<?php } ?>
							<?php if ( ! empty( $item['image']['id'] ) && ( $type = get_post_mime_type( $item['image']['id'] ) ) ) {
								if ( $type == 'image/svg+xml' ) {
									echo ideapark_get_inline_svg( $item['image']['id'], 'c-ip-image-list-3__svg' );
								} else {
									echo ideapark_img( ideapark_image_meta( $item['image']['id'], 'full', $sizes ), 'c-ip-image-list-3__image' );
								}
							}
							?>
							<div class="c-ip-image-list-3__shadow"></div>
							<div
								class="c-ip-image-list-3__wrap c-ip-image-list-3__wrap--<?php echo $settings['layout']; ?>">
								<?php if ( ! empty( $item['title_text'] ) ) { ?>
									<div class="c-ip-image-list-3__title"><?php echo $item['title_text']; ?></div>
								<?php } ?>
							</div>
							<?php if ( $is_link ) { ?>
						</a>
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
