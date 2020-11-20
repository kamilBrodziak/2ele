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
        add_filter( 'wc_add_to_cart_message_html', '__return_false' );
        add_filter( 'woocommerce_checkout_fields' , [$this,'overrideCheckoutFields'] );
        add_filter( 'woocommerce_billing_fields' , [$this,'overrideBillingFields'] );
        add_filter( 'woocommerce_shipping_fields' , [$this,'overrideShippingFields'] );
        add_filter('woocommerce_product_data_store_cpt_get_products_query', [$this,'excludeCategory'], 10, 2);
    }
    function overrideCheckoutFields( $fields ) {
        unset($fields['billing']['billing_country']);
        unset($fields['shipping']['shipping_country']);
        return $fields;
    }

    function overrideBillingFields( $fields ) {
        unset($fields['billing_country']);
        array_splice($fields, 3, 0,  [
            'nip' => [
                'type' => 'text',
                'label' => __('NIP', 'woocommerce'),
                'required' => false
            ]
        ]);
        return $fields;
    }

    function overrideShippingFields( $fields ) {
        unset($fields['shipping_country']);
        unset($fields['shipping_email']);
        return $fields;
    }

    function excludeCategory($query, $query_vars) {
        var_dump($query_vars);
        if (!empty($query_vars['exclude_category'])) {
            $query['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $query_vars['exclude_category'], // Use the value of previous block of code
                'operator' => 'NOT IN',
            );
        }
        return $query;
    }

    public function actions() {
        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
        remove_action( 'woocommerce_checkout_order_review' , 'woocommerce_checkout_payment', 20);
        remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation');
        add_action('woocommerce_checkout_after_order_review', 'woocommerce_checkout_payment', 20);
        add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 21 );
    }
}