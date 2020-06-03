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
    }

    public function registerPostTypes() {}
    public function registerTaxonomies() {}

    public function addToContext( $context ) {
        $context['menu']  = new Timber\Menu('main');
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
//                $twig->addFilter( new Twig\TwigFilter( 'myfoo', array( $this, 'myfoo' ) ) );
        return $twig;
    }
}
new StarterSite();

if(class_exists('Inc\\Init')) {
    Inc\Init::registerServices();
}

require get_template_directory() . '/inc/Functions/Ajax.php';
require get_template_directory() . '/inc/Functions/woocommerce.php';



function getImgDir() {
    return get_template_directory_uri() . "/static/frontend/img";
}

