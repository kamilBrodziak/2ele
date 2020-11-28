<?php
/**
 * @package 2eleTheme
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

//function activateTheme() {
//    Inc\Base\Activate::activate();
//}
//register_activation_hook( __FILE__, 'activateTheme');
//
//function deactivateTheme() {
//    Inc\Base\Deactivate::deactivate();
//}
//register_deactivation_hook( __FILE__, 'deactivateTheme');

class StarterSite extends Timber\Site {
    /** Add timber support. */
    public function __construct() {
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
        foreach ( array( 'pre_term_description' ) as $filter ) {
            remove_filter( $filter, 'wp_filter_kses' );
        }
        foreach ( array( 'term_description' ) as $filter ) {
            remove_filter( $filter, 'wp_kses_data' );
        }
    }

    public function registerPostTypes() {}
    public function registerTaxonomies() {}

    public function addToContext( $context ) {
        $context['menu'] = [
            'main' => new Timber\Menu('main'),
            'footer' => new Timber\Menu('footer')
        ];
        $context['site']  = $this;
        $newsletterOptions = get_option('2eleTheme');
        $newsletterEnabled = isset($newsletterOptions['2eleThemeNewsletterEnable']) ? $newsletterOptions['2eleThemeNewsletterEnable'] : false;
        if($newsletterEnabled) {
            $context['newsletter'] = [
                'title' => isset($newsletterOptions['2eleThemeNewsletterTitle']) ? $newsletterOptions['2eleThemeNewsletterTitle'] : '',
                'action' => isset($newsletterOptions['2eleThemeNewsletterAction']) ? $newsletterOptions['2eleThemeNewsletterAction'] : '',
                'email' => [
                    'label' => isset($newsletterOptions['2eleThemeNewsletterEmailLabel']) ? $newsletterOptions['2eleThemeNewsletterEmailLabel'] : ''
                ],
                'button' => [
                    'text' => isset($newsletterOptions['2eleThemeNewsletterSubmitText']) ? $newsletterOptions['2eleThemeNewsletterSubmitText'] : ''
                ]
            ];
            $nameEnabled = isset($newsletterOptions['2eleThemeNewsletterNameEnable']) ? $newsletterOptions['2eleThemeNewsletterNameEnable'] : false;
            if($nameEnabled) {
                $context['newsletter']['name'] = [
                    'label' => isset($newsletterOptions['2eleThemeNewsletterNameLabel']) ? $newsletterOptions['2eleThemeNewsletterNameLabel'] : ''
                ];
            }
        }

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
//        add_theme_support( 'menus' );
        register_nav_menus( array(
            'main' => 'Main menu',
            'footer' => 'Footer menu'
        ) );
        add_theme_support( 'woocommerce' );
    }

    public function addToTwig( $twig ) {
        $twig->addExtension( new Twig\Extension\StringLoaderExtension() );
//                $twig->addFilter( new Twig\TwigFilter( 'myfoo', array( $this, 'myfoo' ) ) );
        return $twig;
    }
}
new StarterSite();

if(class_exists('eleTheme\\Inc\\Init')) {
    eleTheme\Inc\Init::registerServices();
}

require get_template_directory() . '/inc/Functions/Ajax.php';
require get_template_directory() . '/inc/Functions/woocommerce.php';

function loadCartIntoContext($context) {
    $context['products'] = getCart();
    $context['cartNotices'] = getCartQuantityNotices($context['products']);
    $context['checkoutUrl'] = getCheckoutUrl();
    $context['cartTotal'] = getCartTotal();
    $context = loadCartTotalsIntoContext($context);
    $context['stage'] = 0;
    return $context;
}

function loadCartTotalsIntoContext($context, $cart = null) {
    if(is_null($cart)) $cart = WC()->cart;
    $cartTotals = [];
    $cartTotals[] = [
        'name' => 'Suma:',
        'value' => number_format($cart->get_subtotal(), 2, ',', ' ') . ' zł'
    ];
//    $context['cartSubtotal'] = [$cart->get_subtotal()];
    $cartSubtotalAfterDiscount = $cart->get_cart_contents_total();
    $couponDiscount = $cart->get_discount_total();
    if($couponDiscount != 0) {
//        $context['couponDiscount'] = $couponDiscount;
        $cartTotals[] = [
            'result' => true,
            'name' => 'Zniżka za kupony:',
            'value' => number_format($couponDiscount, 2, ',', ' ') . ' zł'
        ];
    }
    if($cartSubtotalAfterDiscount != $context['cartSubtotal']) {
//        $context['cartSubtotalAfterDiscount'] = $cartSubtotalAfterDiscount;
        $cartTotals[] = [
            'result' => false,
            'name' => 'Do zapłaty (bez dostawy):',
            'value' => number_format($cartSubtotalAfterDiscount, 2, ',', ' ') . ' zł'
        ];
    }
    $context['cartTotals'] = $cartTotals;
    return $context;
}

function getImgDir() {
    return get_template_directory_uri() . "/static/frontend/img";
}

function getVerificationUrl() {
    return get_site_url() . '/user-verification/';
}

function getCheckoutUrl() {
    return WC()->cart->get_checkout_url();
}

function getCartUrl() {
    return WC()->cart->get_cart_url();
}

function isUserLogged() {
    return is_user_logged_in();
}

function getAccountUrl() {
    return get_permalink( get_option('woocommerce_myaccount_page_id') );
}


