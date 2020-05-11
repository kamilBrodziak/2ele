<?php
/**
 * @package starterWordpressTheme
 */

//defined('ABSPATH') or die( 'Hey, you can\t access this file!' );

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

Timber::$dirname = ['templates/frontEnd', 'templates/backend'];
Timber::$autoescape = false;

function activateTheme() {
    Inc\Base\Activate::activate();
}
register_activation_hook( __FILE__, 'activateTheme');

function deactivateTheme() {
    Inc\Base\Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivateTheme');

class StarterSite extends Timber\Site {
    /** Add timber support. */
    public function register() {
        add_action( 'after_setup_theme', array( $this, 'themeSupports' ) );
        add_filter( 'timber/context', array( $this, 'addToContext' ) );
        add_filter( 'timber/twig', array( $this, 'addToTwig' ) );
        add_action( 'init', array( $this, 'registerPostTypes' ) );
        add_action( 'init', array( $this, 'registerTaxonomies' ) );
        $this->removeWordpressUnnecessaryActions();
        parent::__construct();
    }

    public function removeWordpressUnnecessaryActions() {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action ('wp_head', 'rsd_link');
        remove_action( 'wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11, 0);
    }

    public function registerPostTypes() {}
    public function registerTaxonomies() {}

    public function addToContext( $context ) {
        $context['menu']  = new Timber\Menu();
        $context['site']  = $this;
        return $context;
    }

    public function themeSupports() {
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support('html5',
                          array(
                              //				'comment-form',
                              //				'comment-list',
                              //				'gallery',
                              //				'caption',
                          )
        );
        add_theme_support('post-formats',
                          array(
                              'aside',
                              'image',
                              'video',
                              'quote',
                              'link',
                              'gallery',
                              'audio',
                          )
        );
        add_theme_support( 'menus' );
        add_theme_support( 'woocommerce' );
    }

    public function addToTwig( $twig ) {
        $twig->addExtension( new Twig\Extension\StringLoaderExtension() );
        //        $twig->addFilter( new Twig\TwigFilter( 'myfoo', array( $this, 'myfoo' ) ) );
        return $twig;
    }
}
new StarterSite();

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

