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
        'paged' => $context['currentPage'],
        'post_status' => 'publish'
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
    $context['cartNotices'] = $_POST['notices'];
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

function searchAjax() {
    $context = Timber::context();
    $args = [
        'post_type' => 'product',
        's' => $_POST['searchPhrase'],
        'posts_per_page' => 30,
        'post_status' => 'publish'
    ];
    $context['posts'] = new Timber\PostQuery($args);
    Timber::render('partials/search/searchResults.twig', $context);
    die();
}
add_action('wp_ajax_nopriv_searchAjax', 'searchAjax');
add_action('wp_ajax_searchAjax', 'searchAjax');


function addOrderWidget() {
    $context = Timber::context();
    $context['products'] = getCart();
    $context['checkoutUrl'] = getCheckoutUrl();
    $context['cartTotal'] = getCartTotal();
//    $context['nextUrl'] = $_POST['nextUrl'];
    Timber::render('partials/orderWidget.twig', $context);
    die();
}
add_action('wp_ajax_nopriv_addOrderWidget', 'addOrderWidget');
add_action('wp_ajax_addOrderWidget', 'addOrderWidget');

function loadCartWidget() {
    $context = Timber::context();
    $context['products'] = getCart();
    $context['checkoutUrl'] = getCheckoutUrl();
    $context['cartTotal'] = getCartTotal();
    Timber::render('partials/cartWidget.twig', $context);
}
add_action('wp_ajax_nopriv_loadCartWidget', 'loadCartWidget');
add_action('wp_ajax_loadCartWidget', 'loadCartWidget');

function isUserUnique() {
    if($_POST['type'] == 'email') {
        if(email_exists($_POST['value'])) {
            echo "false";
        } else {
            echo "true";
        }
    } else {
        if(username_exists($_POST['value'])) {
            echo "false";
        } else {
            echo "true";
        }
    }
    die();
}
add_action('wp_ajax_nopriv_isUserUnique', 'isUserUnique');
add_action('wp_ajax_isUserUnique', 'isUserUnique');

function userRegisterAjax() {
    $usernameExist = username_exists($_POST['username']);
    $emailExist = email_exists($_POST['email']);
    if($usernameExist || $emailExist) {
        if($usernameExist) {
            echo 'Konto o takiej nazwie użytkownika już istnieje!';
        }
        if($emailExist) {
            echo 'Konto o takim adresie email już istnieje!';
        }
        die();
    } else {
//        $userID = wp_create_user($_POST['username'], $_POST['password'], $_POST['email']);
        $userID = wc_create_new_customer( $_POST['email'], $_POST['username'], $_POST['password'] );
        if(is_wp_error($userID)) {
            echo implode(" ", $userID->get_error_messages());
        } else {
            echo 'success';
        }
    }
    die();
}

add_action('wp_ajax_nopriv_userRegisterAjax', 'userRegisterAjax');
add_action('wp_ajax_userRegisterAjax', 'userRegisterAjax');

function userLoginAjax() {
    $usernameExist = username_exists($_POST['username']);
    $emailExist = email_exists($_POST['username']);
    if($usernameExist || $emailExist) {
       $authenticate = wp_authenticate($_POST['username'], $_POST['password']);
       if($authenticate) {
           $credentials = [
               'user_login' => $_POST['username'],
               'user_password' => $_POST['password'],
               'remember' => $_POST['remember']
           ];
//           $userID = $usernameExist ? $usernameExist : $emailExist;
//           $approved_status = get_user_meta($userID, 'user_activation_status', true);
//           $verificationUrl = getVerificationUrl($userID);
//           if($approved_status == 0) {
//               echo $userID;
//               echo 'Aby się zalogować prosimy najpierw zweryfikować konto poprzez link wysłany na Twój email.' .
//                   ' <a target="_blank" href="' . $verificationUrl . '" class="blackLink">Wyślij ponownie link weryfikacyjny</a>';
//           } else {
               $user = wp_signon($credentials, false);
               if(is_wp_error($user)) {
                   echo $user->get_error_message();
               } else {
                   wp_set_current_user($user);
                   echo 'success';
               }
//           }

       } else {
           echo 'Niepoprawna nazwa użytkownika, email bądź hasło. Spróbuj ponownie lub użyj opcji "Zapomniałem hasła"';
       }
    } else {
        echo 'Niepoprawna nazwa użytkownika, email bądź hasło. Spróbuj ponownie lub użyj opcji "Zapomniałem hasła"';
    }
    die();
}

add_action('wp_ajax_nopriv_userLoginAjax', 'userLoginAjax');
add_action('wp_ajax_userLoginAjax', 'userLoginAjax');

function userLogoutAjax() {
    wp_logout();
}

add_action('wp_ajax_nopriv_userLogoutAjax', 'userLogoutAjax');
add_action('wp_ajax_userLogoutAjax', 'userLogoutAjax');

function loadLoginWidgetAjax() {
    $context = Timber::context();
    if(isUserLogged()) {
        $user = wp_get_current_user();
        $context['username'] = $user->user_login;
        Timber::render('partials/logoutWidget.twig');
    } else {
        $context['privacyPolicyUrl'] = get_privacy_policy_url();
        $context['accountUrl'] = get_permalink(get_option('woocommerce_myaccount_page_id'));
        $context['whichPageShow'] = 'login';
        $context['lostPasswordPage'] = wp_lostpassword_url();
        $context['showPrivileges'] = 'true';
        Timber::render('partials/loginWidget.twig');
    }
    die();
}
add_action('wp_ajax_nopriv_loadLoginWidgetAjax', 'loadLoginWidgetAjax');
add_action('wp_ajax_loadLoginWidgetAjax', 'loadLoginWidgetAjax');