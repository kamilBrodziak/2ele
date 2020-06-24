<?php

namespace Inc\Base;

class WooCommerceSettings {

    public function register() {
        $this->filters();
        $this->actions();
    }

    public function filters() {
        add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
//        add_filter( 'woocommerce_is_sold_individually','__return_true', 10, 2 );
        add_filter('woocommerce_reset_variations_link', '__return_empty_string');
        add_filter( 'wc_add_to_cart_message', '__return');
        add_filter( 'loop_shop_per_page', [$this, 'productsPerPage'], 20 );

    }


    function productsPerPage( $cols ) {
        return getProductsPerPageAmount();
    }

    public function actions() {
        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
        remove_action( 'woocommerce_checkout_order_review' , 'woocommerce_checkout_payment', 20);
        add_action('woocommerce_checkout_after_order_review', 'woocommerce_checkout_payment', 20);
        add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 21 );
    }
}