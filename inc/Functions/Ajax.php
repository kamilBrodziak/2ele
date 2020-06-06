<?php
/**
 * @package starterWordpressTheme
 */

function changePage() {
    $context = Timber::context();
    $context['currentPage'] = $_POST['page'];
    $data = [
        'post_type' => 'product',
        'posts_per_page' => getProductsPerPageAmount(),
        'paged' => $context['currentPage']
    ];
    if($_POST['category']) {
        $context['category'] = $_POST['category'];
        $data['product_cat'] = $_POST['category'];
    }
    if($_POST['search']) {
        $data['s'] = $_POST['search'];
    }
    $context['posts'] = new Timber\PostQuery($data);
    $context['products'] = $context['posts'];
    $context['pagination'] = Timber::get_pagination([
        'end_size'     => 1,
        'mid_size'     => 2,
        'total'        => $_POST['maxPage'],
        'current' => $context['currentPage']
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



function sendUserEmail() {
    $name = wp_strip_all_tags($_POST['name']);
    $email = wp_strip_all_tags($_POST['email']);
    $message = wp_strip_all_tags($_POST['message']);

//	echo $name . ',' . $email . ',' . $message;
    $to = get_bloginfo('admin_email');
    $subject = "Szczęśliwy związek formularz kontaktowy - $name";
    $headers[] = "From: " . get_bloginfo('name') . " <$to>";
    $headers[] = "Reply-To: $name <$email>";
    $headers[] = 'Content-Type: text/html: charset=UTF-8';
    wp_mail($to, $subject, $message, $headers);
    die();
}
add_action('wp_ajax_nopriv_sendUserEmail', 'sendUserEmail');
add_action('wp_ajax_sendUserEmail', 'sendUserEmail');

//function mailtrap($phpmailer) {
//	$phpmailer->isSMTP();
//	$phpmailer->Host = 'smtp.mailtrap.io';
//	$phpmailer->SMTPAuth = true;
//	$phpmailer->Port = 2525;
//	$phpmailer->Username = '8270f6cf69c91b';
//	$phpmailer->Password = 'e542610cedb223';
//}
//
//add_action('phpmailer_init', 'mailtrap');