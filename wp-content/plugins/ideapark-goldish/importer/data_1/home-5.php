<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$footer_page_id = ( $page =  ideapark_get_page_by_title( 'Footer (dark green)', OBJECT, 'html_block' ) ) ? $page->ID : 0;
$home_page_id   = ( $page =  ideapark_get_page_by_title( 'Home 5' ) ) ? $page->ID : 0;

$mods                            = [];
$mods['switch_image_on_hover']   = true;
$mods['product_buttons_layout']  = 'buttons-2';
$mods['accent_color']            = $mods['featured_badge_color'] = $mods['new_badge_color'] = '#24823E';
$mods['hidden_product_category'] = 0;
$mods['top_menu_submenu_layout'] = 'compact';
$mods['header_blocks_1']         = 'logo=1(center-left)|menu=1(center-center)|buttons=1(center-right)|social=0|other=0|address=0|phone=0|email=0|hours=0|lang=0';

if ( $footer_page_id ) {
	$mods['footer_page'] = $footer_page_id;
}

$options = [];
if ( $home_page_id ) {
	$options['page_on_front'] = $home_page_id;
}

$theme_home = [
	'title'      => __( 'Home 5', 'ideapark-goldish' ),
	'screenshot' => 'home-5.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo/home-5/',
	'mods'       => $mods,
	'options'    => $options,
];