<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Control_Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ideapark_Elementor_Instagram extends Widget_Base {
	/**
	 * Retrieve the widget name.
	 */
	public function get_name() {
		return 'ideapark-instagram';
	}

	/**
	 * Retrieve the widget title.
	 */
	public function get_title() {
		return esc_html__( 'Instagram Photo Gallery', 'ideapark-goldish' );
	}

	/**
	 * Retrieve the widget icon.
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
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_instagram',
			[
				'label' => __( 'Image gallery', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => __( 'Layout', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'layout-1',
				'options' => [
					'layout-1' => __( 'Layout 1 (max 6 images)', 'ideapark-goldish' ),
					'layout-2' => __( 'Layout 2 (max 6 images)', 'ideapark-goldish' ),
					'layout-3' => __( 'Layout 3 (max 8 images)', 'ideapark-goldish' ),
				]
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Account Title', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'Enter title', 'ideapark-goldish' ),
				'dynamic'     => [
					'active' => true,
				],
			]
		);


		$this->add_control(
			'link',
			[
				'label'       => __( 'Account Link', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => [
					'active' => true,
				],
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'gallery',
			[
				'label'      => __( 'Add Images', 'ideapark-goldish' ),
				'type'       => Controls_Manager::GALLERY,
				'show_label' => false,
				'dynamic'    => [
					'active' => true,
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 */
	protected function render() {
		$settings  = $this->get_settings();
		$max_items = $settings['layout'] == 'layout-3' ? 8 : 6;
		if ( $settings['gallery'] ) {
			$ids     = array_slice( wp_list_pluck( $settings['gallery'], 'id' ), 0, $max_items );
			$content = '';
			$count   = sizeof( $ids );
			if ( $count > $max_items ) {
				$count = $max_items;
			}
			$classes = [
				1 => [ 'left-1' ],
				2 => [ 'left-1', 'right-1' ],
				3 => [ 'left-2', 'left-1', 'right-1' ],
				4 => [ 'left-2', 'left-1', 'right-1', 'right-2' ],
				5 => [ 'left-3', 'left-2', 'left-1', 'right-1', 'right-2' ],
				6 => [ 'left-3', 'left-2', 'left-1', 'right-1', 'right-2', 'right-3' ],
			];

			if ($settings['layout'] == 'layout-3') {
				$sizes = '(min-width: 601px) 18vw, calc(100vw / 3)';
			} else {
				$sizes = '(min-width: 901px) calc(100vw / 6), (min-width: 601px) calc(100vw / 4), calc(100vw / 2)';
			}

			foreach ( $ids as $index => $id ) {
				if ( $image_meta = ideapark_image_meta( $id, 'full', $sizes ) ) {
					$item_class = $settings['layout'] == 'layout-3' ? $index + 1 : $classes[ $count ][ $index ];
					$content    .= '<div class="c-ip-instagram__item c-ip-instagram__item--' . $settings['layout'] . ' c-ip-instagram__item--' . esc_attr( $item_class ) . '"><div class="c-ip-instagram__item_wrap">' . ideapark_img( $image_meta ) . '</div></div>';
				}
				?>
			<?php }
			if ( $settings['title'] ) {
				$info = '<div class="c-ip-instagram__info c-ip-instagram__info--' . $settings['layout'] . '"><div class="c-ip-instagram__insta-wrap"><div class="c-ip-instagram__insta"><i class="ip-instagram c-ip-instagram__logo"></i>' . esc_html__( 'Instagram', 'ideapark-goldish' ) . '</div><div class="c-ip-instagram__title">@' . esc_html( $settings['title'] ) . '</div></div><div class="c-ip-instagram__button">' . esc_html__( 'Follow us', 'ideapark-goldish' ) . '</div></div>';
				if ( ! empty( $settings['link']['url'] ) ) {
					$link_key = 'link';
					$this->add_link_attributes( $link_key, $settings['link'] );
					$info = ideapark_wrap( $info, '<a ' . $this->get_render_attribute_string( $link_key ) . '>', '</a>' );
				}
			} else {
				$info = '';
			}
			echo ideapark_wrap( $content, '<div class="c-ip-instagram c-ip-instagram--' . $settings['layout'] . ' js-instagram"><div class="c-ip-instagram__wrap c-ip-instagram__wrap--' . $settings['layout'] . '">', '</div>' . $info . '</div>' );
		}
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 */
	protected function content_template() {

	}
}
