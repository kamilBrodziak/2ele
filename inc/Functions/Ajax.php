<?php
/**
 * @package starterWordpressTheme
 */

function changePage() {
    $paged = $_POST['page'];
    $context = Timber::context();
    $context['maxNumPages'] = $_POST['pageCount'];
    $context['paged'] = $paged;
    $context['firstPage'] = $_POST['firstPage'];
    $context['products'] = Timber::get_posts([
        'post_type' => 'product',
        'product_cat' => $_POST['category'],
        'orderby' => [
            'title' => 'ASC'
        ],
        'posts_per_page' => 20,
        'paged' => $paged
    ]);

    Timber::render('partials/productListWidget.twig', $context);
    die();
}
add_action('wp_ajax_nopriv_changePage', 'changePage');
add_action('wp_ajax_changePage', 'changePage');

function addProductWidget() {
    $productID = $_POST['productID'];
    $context = Timber::context();
    $context['post'] = Timber::get_post((int)$productID);
    $product            = wc_get_product( $context['post']->ID );
    $context['product'] = $product;
    // Restore the context and loop back to the main query loop.
    wp_reset_postdata();
    Timber::render('partials/productWidget.twig', $context);
    die();

}
add_action('wp_ajax_nopriv_addProductWidget', 'addProductWidget');
add_action('wp_ajax_addProductWidget', 'addProductWidget');

function addProductToCart() {
    $productID = $_POST['productID'];
    $quantity = $_POST['quantity'];
    $variationID = $_POST['variationID'];
    if($variationID) {
        WC()->cart->add_to_cart((int)$productID, (int)$quantity, (int)$variationID);
    } else {
        WC()->cart->add_to_cart((int)$productID, (int)$quantity);
    }

    die();
}
add_action('wp_ajax_nopriv_addProductToCart', 'addProductToCart');
add_action('wp_ajax_addProductToCart', 'addProductToCart');

function removeProductFromCart() {
    $productKey = $_POST['productKey'];
    WC()->cart->remove_cart_item($productKey);

    $context = Timber::context();
    $context['products'] = getCart();
    $context['checkoutUrl'] = getCheckoutUrl();
    $context['cartTotal'] = getCartTotal();
    wp_reset_postdata();
    Timber::render('partials/cartWidget.twig', $context);
    die();
}

add_action('wp_ajax_nopriv_removeProductFromCart', 'removeProductFromCart');
add_action('wp_ajax_removeProductFromCart', 'removeProductFromCart');

function changeProductQuantityInCart() {
    $productKey = $_POST['productKey'];
    WC()->cart->set_quantity($productKey, $_POST['quantity']);

    $context = Timber::context();
    $context['products'] = getCart();
    $context['checkoutUrl'] = getCheckoutUrl();
    $context['cartTotal'] = getCartTotal();
    wp_reset_postdata();
    Timber::render('partials/cartWidget.twig', $context);
    die();
}
add_action('wp_ajax_nopriv_changeProductQuantityInCart', 'changeProductQuantityInCart');
add_action('wp_ajax_changeProductQuantityInCart', 'changeProductQuantityInCart');