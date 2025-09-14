<?php
/**
 * Goldish-Child functions and definitions
 *
 * @package goldish-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/** Enqueue the child theme stylesheet **/
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'goldish-child-style', get_stylesheet_directory_uri() . '/style.css', PHP_INT_MAX );
}, PHP_INT_MAX );
// Add noindex to URLs with ?add-to-cart=
function fedefee_noindex_add_to_cart_pages() {
    if ( isset($_GET['add-to-cart']) ) {
        echo '<meta name="robots" content="noindex, follow">';
    }
}
add_action('wp_head', 'fedefee_noindex_add_to_cart_pages');
