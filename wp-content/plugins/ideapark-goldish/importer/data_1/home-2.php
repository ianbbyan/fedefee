<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$home_page_id   = ( $page =  ideapark_get_page_by_title( 'Home 2' ) ) ? $page->ID : 0;
$logo_id        = ( $page = ideapark_get_page_by_title( 'logo-white', OBJECT, 'attachment' ) ) ? $page->ID : 0;

$mods                       = [];
$mods['to_top_button']      = true;
$mods['switch_image_on_hover']  = true;
$mods['header_blocks_1']    = 'logo=1(top-left)|menu=1(center-center)|buttons=1(top-right)|social=0|other=1(bottom-left)|address=1(bottom-right)|phone=1(bottom-right)|email=0|hours=0|lang=0';
$mods['transparent_header'] = true;

$options = [];
if ( $home_page_id ) {
	$options['page_on_front'] = $home_page_id;
}

if ( $logo_id ) {
	$control_name                              = 'logo_sticky';
	$params                                    = wp_get_attachment_image_src( $logo_id, 'full' );
	$mods[ $control_name ]                     = wp_get_attachment_url( $logo_id );
	$mods[ $control_name . '__url' ]           = $params[0];
	$mods[ $control_name . '__attachment_id' ] = $logo_id;
	$mods[ $control_name . '__width' ]         = $params[1];
	$mods[ $control_name . '__height' ]        = $params[2];
}

$theme_home = [
	'title'      => __( 'Home 2', 'ideapark-goldish' ),
	'screenshot' => 'home-2.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo/home-2/',
	'mods'       => $mods,
	'options'    => $options,
];