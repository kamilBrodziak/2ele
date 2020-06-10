<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * To generate specific templates for your pages you can use:
 * /mytheme/templates/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::context();

$timber_post = new Timber\Post();
$context['post'] = $timber_post;
if(is_cart()) {
    $context['products'] = getCart();
    $context['checkoutUrl'] = getCheckoutUrl();
    $context['cartTotal'] = getCartTotal();
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
    Timber::render(array('page-woocommerce.twig'), $context);
} else {
    Timber::render( array( 'page-' . $timber_post->post_name . '.twig', 'page.twig' ), $context );

}
