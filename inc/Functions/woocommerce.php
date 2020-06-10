<?php
function timber_set_product( $post ) {
    global $product;

    if ( is_woocommerce() ) {
        $product = wc_get_product( $post->ID );
    }
}

function getProductRegularPrice($productID) {
    return wc_get_product($productID)->get_regular_price();
}

function getVariableProductRegularPrice($productID) {
    $product = wc_get_product($productID);
    $minPrice = $product->get_variation_regular_price( 'min' );
    $maxPrice = $product->get_variation_regular_price( 'max' );
    if($minPrice == $maxPrice) {
        return "".number_format($minPrice, 2)." zł";
    } else {
        return "".number_format($minPrice, 2)." - ".number_format($maxPrice)." zł";
    }
}

function getVariableProductSalePrice($productID) {
    $product = wc_get_product($productID);
    $minPrice = $product->get_variation_sale_price( 'min' );
    $maxPrice = $product->get_variation_sale_price( 'max' );
    if($minPrice == $maxPrice) {
        return "".number_format($minPrice, 2)." zł";
    } else {
        return "".number_format($minPrice, 2)." - ".number_format($maxPrice)." zł";
    }
}

function getVariableProductRegularMaxPrice($productID) {
    return wc_get_product($productID)->get_variation_regular_price( 'max' );
}

function getProductSalePrice($productID) {
    return wc_get_product($productID)->get_sale_price();
}

function getCartProductsQuantity() {
    return WC()->cart->cart_contents_count;
}

function isVariableProduct($productID) {
    $product = wc_get_product($productID);
    return $product->is_type('variable');
}

function hasAvailableVariations($productID) {
    $product = wc_get_product($productID);

    if($product->get_available_variations()) {
        return true;
    }
    return false;
}

function getProductVariations($productID) {
    $product = wc_get_product($productID);
    $variations = [];

    foreach ($product->get_available_variations() as $variation) {
        $variationID = $variation['variation_id'];
        $variationProduct = wc_get_product($variationID);
        $variationAttribute = $variationProduct->get_variation_attributes();
        $variationAttributeLabel = key($variationAttribute);
        if($variationProduct->is_in_stock()) {
            $variation = ['id' => $variationID,
                'name' => $variationAttribute[$variationAttributeLabel],
                'imageSrc' => $variation['image']['url'],
                'price' => $variationProduct->get_price()];

            if($variationProduct->managing_stock()) {
                $variation['maxQuantity'] = $variationProduct->get_stock_quantity();
            }
            $variations[] = $variation;
        }

    }
    return $variations;
}

function getProductVariationLabel($productID) {
    $variationID = wc_get_product($productID)->get_available_variations()[0]['variation_id'];
    $variationAttributeLabel = key(wc_get_product($variationID)->get_variation_attributes());
    return str_replace("attribute_", "", $variationAttributeLabel);
}

function getProductMaxQuantity($productID) {
    $product = wc_get_product($productID);
    if($product->managing_stock()) {
        if($product->backorders_allowed()) {
            return 99;
        }
        return $product->get_stock_quantity();
    }
    return 99;
}

function getCart() {
    $cartItems = WC()->cart->get_cart();
    $products = [];
    foreach($cartItems as $item => $values) {
        $product = wc_get_product($values['product_id']);
        $productDetails = [
            'id' => $values['product_id'],
            'quantity' => $values['quantity'],
            'url' => get_permalink($values['product_id']),
            'key' => $values['key'],
            'removeUrl' => wc_get_cart_remove_url($values['key']),
        ];
        if($product->is_type('variable')) {
            $variationID = $values['variation_id'];
            $product = wc_get_product($variationID);
            $productDetails['variationID'] = $variationID;
        }
        if($product->managing_stock()) {
            if($product->backorders_allowed()) {
                $productDetails['maxQuantity'] = 99;
            } else {
                $productDetails['maxQuantity'] = $product->get_stock_quantity();
            }
        }
        $productDetails['price'] = $product->get_price();
        $productDetails['title'] = $product->get_name();
        if($product->is_on_sale()) {
            $productDetails['regularPrice'] = $product->get_regular_price();
        }
        $productImgID = $product->get_image_id();
        $productDetails['imgSrc'] = wp_get_attachment_image_url($productImgID , 'full' );
        $productDetails['imgAlt'] = get_post_meta($productImgID, '_wp_attachment_image_alt', TRUE);
        $products[] = $productDetails;
    }
    return $products;
//    return WC()->cart->get_cart_contents();
}

function getCheckoutUrl() {
    return WC()->cart->get_checkout_url();
}

function getCartTotal() {
    return WC()->cart->get_subtotal();
}

function isSimpleProduct($id) {
    return wc_get_product($id)->is_type('simple');
}

function getShippingMethods() {
    return WC()->shipping->get_shipping_methods();
}




function custom_override_checkout_fields( $fields = array() ) {
    unset($fields['billing']['billing_country']);
    unset($fields['shipping']['shipping_country']);
    return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

function isUserLogged() {
    return is_user_logged_in();
}

function getAccountUrl() {
    return get_permalink( get_option('woocommerce_myaccount_page_id') );
}










//add_filter('add_to_cart_redirect', 'addToCartRedirectToCheckout');
function addToCartRedirectToCheckout() {
    global $woocommerce;
    return $woocommerce->cart->get_checkout_url();
}

function remove_cart_item_before_add_to_cart( $passed, $product_id, $quantity ) {
    if( ! WC()->cart->is_empty() )
        WC()->cart->empty_cart();
    return $passed;
}
//add_filter( 'woocommerce_add_to_cart_validation', 'remove_cart_item_before_add_to_cart', 20, 3 );

function wc_remove_options_text( $args ){
    $args['show_option_none'] = '';
    return $args;
}
//add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'wc_remove_options_text');

function woocommerce_custom_single_add_to_cart_text() {
    return __( 'Kup teraz', 'woocommerce' );
}
//add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text' );