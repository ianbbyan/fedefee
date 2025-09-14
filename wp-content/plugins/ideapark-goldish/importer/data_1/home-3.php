<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$footer_page_id = ( $page = ideapark_get_page_by_title( 'Footer (white)', OBJECT, 'html_block' ) ) ? $page->ID : 0;
$home_page_id   = ( $page = ideapark_get_page_by_title( 'Home 3' ) ) ? $page->ID : 0;
$logo_id        = ( $page = ideapark_get_page_by_title( 'logo-white', OBJECT, 'attachment' ) ) ? $page->ID : 0;

$mods                                   = [];
$mods['header_top_color']               = '#FFFFFF';
$mods['header_top_accent_color']        = '#181818';
$mods['header_top_background_color']    = '#C6AD8A';
$mods['mobile_header_color']            = '#FFFFFF';
$mods['mobile_header_background_color'] = '#C6AD8A';
$mods['logo']                           = '';
$mods['header_blocks_1']                = 'social=1(top-left)|logo=1(top-center)|menu=1(bottom-center)|buttons=1(top-right)|other=0|address=0|phone=1(bottom-left)|email=0|hours=1(bottom-left)|lang=1(bottom-right)';

if ( $footer_page_id ) {
	$mods['footer_page'] = $footer_page_id;
}

$options = [];
if ( $home_page_id ) {
	$options['page_on_front'] = $home_page_id;
}

if ( $logo_id ) {
	$params = wp_get_attachment_image_src( $logo_id, 'full' );
	foreach ( [ 'logo', 'logo_mobile' ] as $control_name ) {
		$mods[ $control_name ]                     = wp_get_attachment_url( $logo_id );
		$mods[ $control_name . '__url' ]           = $params[0];
		$mods[ $control_name . '__attachment_id' ] = $logo_id;
		$mods[ $control_name . '__width' ]         = $params[1];
		$mods[ $control_name . '__height' ]        = $params[2];
	}
}

$theme_home = [
	'title'      => __( 'Home 3', 'ideapark-goldish' ),
	'screenshot' => 'home-3.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo/home-3/',
	'mods'       => $mods,
	'options'    => $options,
];