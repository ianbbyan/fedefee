<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$home_page_id   = ( $page = ideapark_get_page_by_title( 'Home 10' ) ) ? $page->ID : 0;
$footer_page_id = ( $page = ideapark_get_page_by_title( 'Footer (full black)', OBJECT, 'html_block' ) ) ? $page->ID : 0;
$logo_id        = ( $page = ideapark_get_page_by_title( 'logo-white', OBJECT, 'attachment' ) ) ? $page->ID : 0;

$mods                                    = [];
$mods['to_top_button']                   = true;
$mods['switch_image_on_hover']           = true;
$mods['transparent_header']              = true;
$mods['accent_color']                    = '#404040';
$mods['transparent_header_accent_color'] = '#A1A1A1';

if ( $footer_page_id ) {
	$mods['footer_page'] = $footer_page_id;
}

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
	'title'      => __( 'Home 10', 'ideapark-goldish' ),
	'screenshot' => 'home-10.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo/home-10/',
	'mods'       => $mods,
	'options'    => $options,
];