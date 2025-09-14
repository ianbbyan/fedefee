<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ideapark_Custom_Fonts {
	public function __construct() {

		add_filter( 'upload_mimes', function ( $mimes ) {
			if ( current_user_can( 'administrator' ) ) {
				$mimes['ttf']   = 'application/x-font-ttf';
				$mimes['eot']   = 'application/vnd.ms-fontobject';
				$mimes['woff']  = 'application/font-woff';
				$mimes['woff2'] = 'application/font-woff2';
				$mimes['otf']   = 'application/vnd.oasis.opendocument.formula-template';
			}

			return $mimes;
		} );

		add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
			$meta_boxes[] = [
				'id'     => 'ideapark_section_fonts',
				'title'  => __( 'Custom Fonts', 'ideapark-goldish' ),
				'panel'  => '',
				'fields' => [
					[
						'id'         => 'custom_fonts',
						'type'       => 'group',
						'clone'      => true,
						'sort_clone' => false,
						'fields'     => [
							[
								'name' => __( 'Name', 'ideapark-goldish' ),
								'id'   => 'name',
								'type' => 'text',
							],
							[
								'name' => __( 'Font .woff2', 'ideapark-goldish' ),
								'id'   => 'woff2',
								'type' => 'file_input',
							],
							[
								'name' => __( 'Font .woff', 'ideapark-goldish' ),
								'id'   => 'woff',
								'type' => 'file_input',
							],
							[
								'name' => __( 'Font .ttf', 'ideapark-goldish' ),
								'id'   => 'ttf',
								'type' => 'file_input',
							],
							[
								'name' => __( 'Font .svg', 'ideapark-goldish' ),
								'id'   => 'svg',
								'type' => 'file_input',
							],
							[
								'name' => __( 'Font .otf', 'ideapark-goldish' ),
								'id'   => 'otf',
								'type' => 'file_input',
							],
							[
								'name'    => __( 'Font Display', 'ideapark-goldish' ),
								'id'      => 'font_display',
								'type'    => 'select',
								'std'     => '',
								'options' => [
									''         => '',
									'auto'     => __( 'Auto', 'ideapark-goldish' ),
									'block'    => __( 'Block', 'ideapark-goldish' ),
									'swap'     => __( 'Swap', 'ideapark-goldish' ),
									'fallback' => __( 'Fallback', 'ideapark-goldish' ),
									'optional' => __( 'Optional', 'ideapark-goldish' ),
								],
							],
						],
					],
				],
			];

			return $meta_boxes;
		} );
	}
}

new Ideapark_Custom_Fonts();