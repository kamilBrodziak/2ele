<?php
$context = Timber::context();

$timber_post = new Timber\Post();
$context['post'] = $timber_post;
if(is_cart()) {
    $context = loadCartIntoContext($context);
    Timber::render(array('page-cart.twig'), $context);
//    Timber::render( array( 'page-' . $timber_post->post_name . '.twig', 'page.twig' ), $context );

} else if(is_account_page()) {
    $context['accountUrl'] = get_permalink(get_option('woocommerce_myaccount_page_id'));
    $url = $_SERVER['REQUEST_URI'];
    $url_components = parse_url($url);
    parse_str($url_components['query'], $params);
    $context['whichPageShow'] = ($params['page'] != 'register') ? 'login' : 'register';
    $context['lostPasswordPage'] = wp_lostpassword_url();
    if($_SERVER['REQUEST_METHOD'] == 'POST' && !isUserLogged()) {
        if(isset($_POST['login'])) {
            $credentials = [
                'user_login' => $_POST['username'],
                'user_password' => $_POST['password'],
                'remember' => $_POST['remember']
            ];
            $user = wp_signon($credentials, false);
            if(is_wp_error($user)) {
                $context['loginNotices'] = $user->get_error_message();
            } else {
                wp_set_current_user($user);
            }
        } else if(isset($_POST['register'])) {
            $isEmailValid = ($_POST['email'] == $_POST['confirmEmail']);
            $isPasswordValid = ($_POST['password'] == $_POST['confirmPassword']);
            $usernameExist = username_exists($_POST['username']);
            $emailExist = email_exists($_POST['email']);
            if(!$isEmailValid || !$isPasswordValid || $usernameExist || $emailExist) {
                $context['registerNotices'] = [];
                if(!$isEmailValid) {
                    array_push($context['registerNotices'], 'Wpisane emaile różnią się!');
                    if(!$emailExist) {
                        $context['registerEmail'] = $_POST['email'];
                    }
                }
                if(!$isPasswordValid) {
                    array_push($context['registerNotices'], 'Wpisane hasła różnią się!');
                }
                if($usernameExist) {
                    array_push($context['registerNotices'], 'Konto o takiej nazwie użytkownika już istnieje!');
                } else {
                    $context['registerUsername'] = $_POST['username'];
                }
                if($emailExist) {
                    array_push($context['registerNotices'], 'Konto o takim adresie email już istnieje!');
                }
            } else {
                $userID = wp_create_user($_POST['username'], $_POST['password'], $_POST['email']);
                if(is_wp_error($userID)) {
                    $context['loginNotices'];
                } else {
                    $context['loginNotices'] = 'Konto zostało pomyślnie utworzone! Na podany email ' .
                    'został wysłany link aktywacyjny. Proszę przed logowaniem aktywować konto.';
                }
            }
        }
    }
    if(isUserLogged()) {
        Timber::render(array('page-account.twig'), $context);
    } else {
        $context['privacyPolicyUrl'] = get_privacy_policy_url();
        Timber::render(array('page-login.twig'), $context);
    }
} else if(is_checkout()) {
    if(!is_order_received_page()) {
        $context['isUserLoggedIn'] = isUserLogged();
        do_action( 'woocommerce_check_cart_items' );
        WC()->cart->calculate_totals();
        WC()->cart->show_shipping();
        $checkout = WC()->checkout();
        if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! $context['isUserLoggedIn'] ) {
            $context['loginRequired'] = true;
        }
        $non_js_checkout = ! empty( $_POST['woocommerce_checkout_update_totals'] );
        if(!$context['isUserLoggedIn']) {
            $context['privacyPolicyUrl'] = get_privacy_policy_url();
            $context['accountUrl'] = get_permalink(get_option('woocommerce_myaccount_page_id'));
            $context['whichPageShow'] = 'login';
            $context['lostPasswordPage'] = wp_lostpassword_url();
        }
        $context['products'] = getCart();
        $context['checkoutUrl'] = getCheckoutUrl();
        $context['cartTotal'] = getCartTotal();
        $context['stage'] = 1;
        Timber::render(array('page-checkout.twig'), $context);
    } else {
        Timber::render(array('page-thankyou.twig'), $context);
    }

} else {
    Timber::render( array( 'page-' . $timber_post->post_name . '.twig', 'page.twig' ), $context );

}
