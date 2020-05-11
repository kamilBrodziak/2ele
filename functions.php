<?php
/**
 * Name Theme
 *
 * @package starterWordpressTheme
 */

defined('ABSPATH') or die( 'Hey, you can\t access this file!' );

$composer_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
	$timber = new Timber\Timber();
}

if ( ! class_exists( 'Timber' ) ) {
	add_filter(
		'template_include',
		function( $template ) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		}
	);
	return;
}

Timber::$dirname = ['templates'];
Timber::$autoescape = false;

function activateTheme() {
    Inc\Base\Activate::activate();
}
register_activation_hook( __FILE__, [$this, 'activateTheme']);

function deactivateTheme() {
    Inc\Base\Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, [$this, 'deactivateTheme']);

if(class_exists('Inc\\Init')) {
    Inc\Init::registerServices();
}












/**
 * WOOCOMMERCE
 **/

function timber_set_product( $post ) {
	global $product;

	if ( is_woocommerce() ) {
		$product = wc_get_product( $post->ID );
	}
}

add_filter('add_to_cart_redirect', 'addToCartRedirectToCheckout');
function addToCartRedirectToCheckout() {
	global $woocommerce;
	return $woocommerce->cart->get_checkout_url();
}

add_filter( 'woocommerce_add_to_cart_validation', 'remove_cart_item_before_add_to_cart', 20, 3 );
function remove_cart_item_before_add_to_cart( $passed, $product_id, $quantity ) {
	if( ! WC()->cart->is_empty() )
		WC()->cart->empty_cart();
	return $passed;
}

add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'wc_remove_options_text');
function wc_remove_options_text( $args ){
	$args['show_option_none'] = '';
	return $args;
}


function isSimpleProduct($id) {
	return wc_get_product($id)->is_type('simple');
}


add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text' );
function woocommerce_custom_single_add_to_cart_text() {
	return __( 'Kup teraz', 'woocommerce' );
}


add_filter('woocommerce_billing_fields','wpb_custom_billing_fields');
function wpb_custom_billing_fields( $fields = array() ) {
	unset($fields['billing_company']);
	return $fields;
}


function getWCProducts() {
	return Timber::get_posts( [
		'post_type'      => 'product',
		'orderby' => [
			'date' => 'ASC'
		]
   ] );
}

