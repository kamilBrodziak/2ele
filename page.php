<?php
$context = Timber::context();

$timber_post = new Timber\Post();
$context['post'] = $timber_post;
if(is_cart()) {
    $context = loadCartIntoContext($context);
    Timber::render(array('page-cart.twig'), $context);
//    Timber::render( array( 'page-' . $timber_post->post_name . '.twig', 'page.twig' ), $context );

} else if(is_account_page()) {
    if(!is_lost_password_page()) {
        $context['accountUrl'] = get_permalink(get_option('woocommerce_myaccount_page_id'));
        $url = $_SERVER['REQUEST_URI'];
        $url_components = parse_url($url);
        parse_str($url_components['query'], $params);
        $context['whichPageShow'] = ($params['page'] != 'register') ? 'login' : 'register';
        $context['lostPasswordPage'] = wp_lostpassword_url();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isUserLogged()) {
            if (isset($_POST['login'])) {
                $credentials = [
                    'user_login' => $_POST['username'],
                    'user_password' => $_POST['password'],
                    'remember' => $_POST['remember']
                ];
                $user = wp_signon($credentials, false);
                if (is_wp_error($user)) {
                    $context['loginNotices'] = $user->get_error_message();
                } else {
                    wp_set_current_user($user);
                }
            } else if (isset($_POST['register'])) {
                $isEmailValid = ($_POST['email'] == $_POST['confirmEmail']);
                $isPasswordValid = ($_POST['password'] == $_POST['confirmPassword']);
                $usernameExist = username_exists($_POST['username']);
                $emailExist = email_exists($_POST['email']);
                if (!$isEmailValid || !$isPasswordValid || $usernameExist || $emailExist) {
                    $context['registerNotices'] = [];
                    if (!$isEmailValid) {
                        array_push($context['registerNotices'], 'Wpisane emaile różnią się!');
                        if (!$emailExist) {
                            $context['registerEmail'] = $_POST['email'];
                        }
                    }
                    if (!$isPasswordValid) {
                        array_push($context['registerNotices'], 'Wpisane hasła różnią się!');
                    }
                    if ($usernameExist) {
                        array_push($context['registerNotices'], 'Konto o takiej nazwie użytkownika już istnieje!');
                    } else {
                        $context['registerUsername'] = $_POST['username'];
                    }
                    if ($emailExist) {
                        array_push($context['registerNotices'], 'Konto o takim adresie email już istnieje!');
                    }
                } else {
                    $userID = wp_create_user($_POST['username'], $_POST['password'], $_POST['email']);
                    if (is_wp_error($userID)) {
                        $context['loginNotices'];
                    } else {
                        $context['loginNotices'] = 'Konto zostało pomyślnie utworzone! Na podany email ' .
                            'został wysłany link aktywacyjny. Proszę przed logowaniem aktywować konto.';
                    }
                }
            }
        }
    }
    if(isUserLogged()) {
        $context['accountPageUrls'] = [];
        $accountMenuItems = wc_get_account_menu_items();
        foreach($accountMenuItems as $key => $value) {
            $context['accountPageUrls'][$value] = wc_get_account_endpoint_url( $key );
        }
//        $context['addresses']['Adres rozliczeniowy'] = wc_get_account_formatted_address( 'billing', $customer_id);
//        $context['addresses']['Adres do wysyłki'] = wc_get_account_formatted_address( 'shipping', $customer_id);
        global $wp;
        if(is_wc_endpoint_url( 'edit-address' ) ){
            if( $wp->query_vars['edit-address'] != 'billing' && $wp->query_vars['edit-address'] != 'shipping') {
                $customer_id = get_current_user_id();
                foreach (['billing' => 'Adres rozliczeniowy', 'shipping' => 'Adres do wysyłki'] as $name => $title) {
                    $context['addresses'][$title]['editUrl'] = wc_get_account_endpoint_url('edit-address') . $name;
                    $context['addresses'][$title]['firstName'] = get_user_meta($customer_id, $name . '_first_name', true);
                    $context['addresses'][$title]['lastName'] = get_user_meta($customer_id, $name . '_last_name', true);
                    $context['addresses'][$title]['company'] = get_user_meta($customer_id, $name . '_company', true);
                    $context['addresses'][$title]['address1'] = get_user_meta($customer_id, $name . '_address_1', true);
                    $context['addresses'][$title]['address2'] = get_user_meta($customer_id, $name . '_address_2', true);
                    $context['addresses'][$title]['postcode'] = get_user_meta($customer_id, $name . '_postcode', true);
                    $context['addresses'][$title]['phone'] = get_user_meta($customer_id, $name . '_phone', true);
                    $context['addresses'][$title]['city'] = get_user_meta($customer_id, $name . '_city', true);
                    //            $context['addresses'][$title]['state'] = get_user_meta( $customer_id, $name . '_state', true );
                    //            $context['addresses'][$title]['country'] = get_user_meta( $customer_id, $name . '_country', true );
                }
            } else {
                $context['isEditAddress'] = true;
            }
        } else if(is_wc_endpoint_url('edit-account')) {
            $context['isDetails'] = true;
        } else if(is_wc_endpoint_url('view-order')) {
            $context['isViewOrder'] = true;
        }
        Timber::render(array('page-account.twig'), $context);
    } else {
        if(is_lost_password_page()) {
            Timber::render(array('page-account.twig'), $context);
        } else {
            $context['privacyPolicyUrl'] = get_privacy_policy_url();
            Timber::render(array('page-login.twig'), $context);
        }

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
