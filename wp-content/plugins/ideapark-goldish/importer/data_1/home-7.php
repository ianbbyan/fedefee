<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$footer_page_id = ( $page =  ideapark_get_page_by_title( 'Footer (white 2)', OBJECT, 'html_block' ) ) ? $page->ID : 0;
$home_page_id   = ( $page =  ideapark_get_page_by_title( 'Home 7' ) ) ? $page->ID : 0;
$logo_id        = ( $page = ideapark_get_page_by_title( 'logo-white', OBJECT, 'attachment' ) ) ? $page->ID : 0;

$mods                                   = [];
$mods['to_top_button']                  = true;
$mods['switch_image_on_hover']          = true;
$mods['to_top_button_color']            = '#704D6D';
$mods['accent_color']                   = '#704D6D';
$mods['header_top_color']               = '#FFFFFF';
$mods['header_top_accent_color']        = '#704D6D';
$mods['header_top_background_color']    = '#000000';
$mods['mobile_header_color']            = '#FFFFFF';
$mods['mobile_header_background_color'] = '#000000';
$mods['top_menu_submenu_color']         = '#FFFFFF';
$mods['top_menu_submenu_accent_color']  = '#704D6D';
$mods['top_menu_submenu_bg_color']      = '#000000';
$mods['logo']                           = '';
$mods['header_blocks_1']                = 'logo=1(top-left)|menu=1(center-center)|buttons=1(top-right)|social=0|other=1(bottom-left)|address=1(bottom-right)|phone=1(bottom-right)|email=0|hours=0|lang=0';

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
	'title'      => __( 'Home 7', 'ideapark-goldish' ),
	'screenshot' => 'home-7.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo/home-7/',
	'mods'       => $mods,
	'options'    => $options,
];