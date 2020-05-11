<?php

namespace Inc\Base;

class StarterSite extends Timber\Site{
    /** Add timber support. */
    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'themeSupports' ) );
        add_filter( 'timber/context', array( $this, 'addToContext' ) );
        add_filter( 'timber/twig', array( $this, 'addToTwig' ) );
        add_action( 'init', array( $this, 'registerPostTypes' ) );
        add_action( 'init', array( $this, 'registerTaxonomies' ) );
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action ('wp_head', 'rsd_link');
        remove_action( 'wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11, 0);
        parent::__construct();
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
        $twig->addFilter( new Twig\TwigFilter( 'myfoo', array( $this, 'myfoo' ) ) );
        return $twig;
    }
}