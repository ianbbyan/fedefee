<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$home_page_id = ( $page =  ideapark_get_page_by_title( 'Home 9' ) ) ? $page->ID : 0;
$footer_page_id = ( $page =  ideapark_get_page_by_title( 'Footer (white 3)', OBJECT, 'html_block' ) ) ? $page->ID : 0;

$mods                          = [];
$mods['to_top_button']         = true;
$mods['switch_image_on_hover'] = true;
$mods['accent_color']          = '#404040';

if ( $footer_page_id ) {
	$mods['footer_page'] = $footer_page_id;
}


$options = [];
if ( $home_page_id ) {
	$options['page_on_front'] = $home_page_id;
}

$theme_home = [
	'title'      => __( 'Home 9', 'ideapark-goldish' ),
	'screenshot' => 'home-9.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo/home-9/',
	'mods'       => $mods,
	'options'    => $options,
];