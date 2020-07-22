<?php
/**
 * @package starterWordpressTheme
 */

namespace Inc\Base;


class Enqueue extends BaseController {
    public function register() {
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdmin'));
//        if($this->isActivated('nameThemeFeature')) {
//            add_action('wp_enqueue_scripts', array($this, 'enqueueFeature'));
//            add_filter('script_loader_tag', array($this, 'addAsyncOrDefer'), 10, 2);
//        }
        add_action('wp_enqueue_scripts', array($this, 'enqueueFrontEnd'));
    }

    function addAsyncOrDefer($tag, $handle) {
        if(!is_admin()) {
            if (strpos($handle, 'async') !== false) {
                return str_replace('<script ', '<script async ', $tag);
            } else if (strpos($handle, 'defer') !== false) {
                return str_replace('<script ', '<script defer="defer" ', $tag);
            } else {
                return $tag;
            }
        } else {
            return $tag;
        }
    }

    function enqueueAdmin($hook) {
//        if('themeNameDashboard' == $hook) {
//            wp_enqueue_media();
//            wp_enqueue_style('kBPaStyle', $this->pluginUrl. 'assets/css/aStyle.css');
//            wp_enqueue_script('jqueryMin', 'https://code.jquery.com/jquery-3.5.0.min.js', NULL, NULL, false);
//            wp_enqueue_script('aScript', $this->pluginUrl . 'assets/aScript.js', array('jqueryMin'), null, true);
//        }
//
//        if('themeNameFeatureCSS' == $hook) {
//            wp_enqueue_script('ace', $this->pluginUrl . 'assets/js/ace/ace.js', array('jquery'), null, true);
//            wp_enqueue_script('customCSS', $this->pluginUrl . 'assets/js/admin/customCSS.js', array('jqueryMin'), null, true);
//        }
    }

    function enqueueFrontEnd() {
        // custom CSS
//        if($this->isActivated('') && file_exists($this->pluginPath . 'assets/css/fcStyle.min.css')) {
//            wp_enqueue_style('', $this->pluginUrl . 'assets/css/fcStyle.min.css');
//        } else {
//            wp_enqueue_style('s', $this->pluginUrl . 'assets/css/fStyle.min.css');
//        }
//        wp_enqueue_script('fScript-defer', $this->pluginUrl . 'assets/fScript.min.js', array('jquery'), null, true);

        wp_deregister_script('jquery');
        wp_deregister_script( 'wp-embed' );
        wp_register_script('jquery-defer', includes_url('/js/jquery/jquery.min.js'),false, '3.5.1', true);
        wp_dequeue_style( 'wc-block-style' );
        if(!is_checkout()) {
            wp_deregister_script('woocommerce');
            wp_deregister_script('wc-cart-fragments');
//            wp_enqueue_script('jquery-defer');
        } else {
//            wp_enqueue_script('jquery', includes_url('/js/jquery/jquery.min.js'), NULL, '3.5.1', false);
        }
        wp_enqueue_script('jquery', includes_url('/js/jquery/jquery.min.js'), NULL, '3.5.1', false);

//        wp_enqueue_script('woocommerce');
//        wp_enqueue_script('wc-checkout');
        wp_register_script('siteJS-defer', get_template_directory_uri() . '/static/frontend/js/site.min.js', array('jquery'), '3.0.2', true);
        global $wp_query;
        wp_localize_script( 'siteJS-defer', 'ajaxPaginationParams', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'posts' => json_encode( $wp_query->query_vars ),
            'currentPage' => $wp_query->query_vars['paged'] ? $wp_query->query_vars['paged'] : 1,
            'maxPage' => $wp_query->max_num_pages,
            'firstPage' => strtok(get_pagenum_link(1), '?')
        ) );
        wp_enqueue_script('siteJS-defer');
        wp_enqueue_style('siteStyle', get_template_directory_uri() . '/static/frontend/css/style.css', null, '3.0.6', 'all');
        wp_dequeue_style( 'wp-block-library' );
        wp_dequeue_style( 'wp-block-library-theme' );
    }
}