<?php
$context = Timber::context();

$timber_post = new Timber\Post();
$context['post'] = $timber_post;
if(is_cart()) {
    $context = loadCartIntoContext($context);
    Timber::render(array('page-cart.twig'), $context);

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
        global $wp;
        if(is_wc_endpoint_url( 'edit-address' ) ){
            if( $wp->query_vars['edit-address'] != 'billing' && $wp->query_vars['edit-address'] != 'shipping') {
                $customer_id = get_current_user_id();
                foreach (['billing' => 'Adres rozliczeniowy', 'shipping' => 'Adres do wysyłki'] as $name => $title) {
                    $context['addresses'][$title] = [
                        'editUrl' =>   wc_get_account_endpoint_url('edit-address') . $name,
                        'firstName' => get_user_meta($customer_id, $name . '_first_name', true),
                        'lastName' => get_user_meta($customer_id, $name . '_last_name', true),
                        'company' => get_user_meta($customer_id, $name . '_company', true),
                        'address1' => get_user_meta($customer_id, $name . '_address_1', true),
                        'address2' => get_user_meta($customer_id, $name . '_address_2', true),
                        'postcode' => get_user_meta($customer_id, $name . '_postcode', true),
                        'phone' => get_user_meta($customer_id, $name . '_phone', true),
                        'city' => get_user_meta($customer_id, $name . '_city', true)
                    ];
                }
            } else {
                $context['isEditAddress'] = true;
            }
        } else if(is_wc_endpoint_url('edit-account')) {
            $context['isDetails'] = true;
        } else if(is_wc_endpoint_url('orders')) {
            $context['isOrders'] = true;
            $paged = (get_query_var('orders')) ? (int)get_query_var('orders') : 1;
            $context = loadAccountOrdersPageIntoContext($context, $paged);
        } else if(is_wc_endpoint_url('view-order')) {
            $context['isViewOrder'] = true;
            $orderID = (get_query_var('view-order')) ? (int)get_query_var('view-order') : -1;
            $context = loadAccountViewOrderPageIntoContext($context, $orderID);
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
        $context['isViewOrder'] = true;
        $orderID = (get_query_var('order-received')) ? (int)get_query_var('order-received') : -1;
        $context = loadAccountViewOrderPageIntoContext($context, $orderID);
        Timber::render(array('page-thankyou.twig'), $context);
    }

} else{
    Timber::render( array( 'page-' . $timber_post->post_name . '.twig', 'base.twig' ), $context );

}


function loadAccountOrdersPageIntoContext($context, $paged) {
    $customerOrders = wc_get_orders([
        'meta_key' => '_customer_user',
        'meta_value' => get_current_user_id(),
        'paginate' => true,
        'limit' => 5,
        'paged' => $paged
    ]);
    $context['currentPage'] = $paged;
    $context['pagination']['pages'] = [];
    if($paged > 1) {
        $context['pagination']['prev']['link'] = wc_get_endpoint_url( 'orders', $paged - 1 );

    }
    if($paged < $customerOrders->max_num_pages) {
        $context['pagination']['next']['link'] = wc_get_endpoint_url( 'orders', $paged + 1 );
    }
    $startIndex = max(1, $paged - 2);
    $endIndex = min($paged + 2, $customerOrders->max_num_pages);
    if($startIndex > 2) {
        $context['pagination']['pages'][] = ['title' => 1, 'link' => wc_get_endpoint_url( 'orders', 1)];
        $context['pagination']['pages'][] = ['title' => '...'];
    }

    if($endIndex < $customerOrders->max_num_pages - 2) {
        $context['pagination']['pages'][] = ['title' => '...'];
        $context['pagination']['pages'][] = [
            'title' => $customerOrders->max_num_pages,
            'link' => wc_get_endpoint_url( 'orders', $customerOrders->max_num_pages)
        ];
    }
    for($i = $startIndex; $i <= $endIndex; ++$i) {
        if($i == $paged) {
            $context['pagination']['pages'][] = ['title' => $i];
        } else {
            $context['pagination']['pages'][] = ['title' => $i, 'link' => wc_get_endpoint_url( 'orders', $i)];
        }
    }
    $context['ordersTable'] = [];
    $context['ordersTable']['orders'] = [];
    $context['ordersTable']['columnNames'] = [];
    foreach(wc_get_account_orders_columns() as $columnID => $columnName) {
        $context['ordersTable']['columnNames'][] = $columnName;
    }
    foreach($customerOrders->orders as $order) {
        $order = wc_get_order($order);
        $itemCount = $order->get_item_count() - $order->get_item_count_refunded();
        $status = wc_get_order_status_name($order->get_status());
        $viewOrder = $order->get_view_order_url();
        $number = $order->get_order_number();
        $date = wc_format_datetime( $order->get_date_created());
        $datetime = $order->get_date_created()->date('c');
        $total = $order->get_formatted_order_total();
        $actions = wc_get_account_orders_actions($order);
        $actionsTable = [];
        if(!empty($actions)) {
            foreach ($actions as $key => $action) {
                $actionsTable[$action['name']] = $action['url'];
            }
        }
        $context['ordersTable']['orders'][] = [
            'number' => $number,
            'url' => $viewOrder,
            'date' => $date,
            'datetime' => $datetime,
            'status' => $status,
            'total' => $total,
            'count' => $itemCount,
            'actions' => $actionsTable
        ];
    }
    return $context;
}

function loadAccountViewOrderPageIntoContext($context, $orderID) {
    if($orderID != -1) {
        $order = wc_get_order($orderID);
        $orderItems = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
        $showPurchaseMote = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
        $showCustomerDetails = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
        $downloads = $order->get_downloadable_items();
        $showDownloads = $order->has_downloadable_item() && $order->is_download_permitted();
        if ($showDownloads) {
            $context['showDownloads'] = $showDownloads;
        }
        $context['orderObj'] = $order;
        $context['order']['items'] = [];
        foreach ($orderItems as $itemID => $item) {
            $product = $item->get_product();
            $isVisible = $product && $product->is_visible();
            $refundedQuantity = $order->get_qty_refunded_for_item($itemID);
            $quantity = $item->get_quantity();
            $title = $item->get_name();
            $productDetails = [
                'title' => $title,
                'quantity' => $quantity,
                'subtotal' => $order->get_formatted_line_subtotal($item)
            ];
            if ($isVisible) {
                $productDetails['url'] = $product->get_permalink($item);
            }
            if ($refundedQuantity) {
                $productDetails['refundedQuantity'] = $quantity - (-1 * $refundedQuantity);
            }
            $context['order']['items'][] = $productDetails;
        }
        foreach ($order->get_order_item_totals() as $key => $total) {
            $context['order']['details'][] = [
                'label' => $total['label'],
                'value' => ('payment_method' === $key) ? esc_html($total['value']) : wp_kses_post($total['value'])
            ];
        }
        $customerNote = $order->get_customer_note();
        if ($customerNote) {
            $context['order']['details'][] = [
                'label' => 'Notatka do zamówienia',
                'value' => $customerNote
            ];
        }

        $context['order']['details'][] = [
            'label' => 'Data zamówienia',
            'value' => wc_format_datetime($order->get_date_created())
        ];

        $context['order']['details'][] = [
            'label' => 'Status zamówienia',
            'value' => wc_get_order_status_name($order->get_status())
        ];

        $context['order']['id'] = $order->get_order_number();

        if ($showCustomerDetails) {
            $showShipping = !wc_ship_to_billing_address_only() && $order->needs_shipping_address();
            $billingAddress = [
                'firstName' => $order->get_billing_first_name(),
                'lastName' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address1' => $order->get_billing_address_1(),
                'address2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'postcode' => $order->get_billing_postcode()
            ];

            $billingPhone = $order->get_billing_phone();
            if ($billingPhone) {
                $billingAddress['phone'] = $billingPhone;
            }
            $billingEmail = $order->get_billing_email();
            if ($billingEmail) {
                $billingAddress['email'] = $billingEmail;
            }
            $context['order']['addresses']['Adres rozliczeniowy'] = $billingAddress;
            if ($showShipping) {
                $context['order']['addresses']['Adres do wysyłki'] = [
                    'firstName' => $order->get_shipping_first_name(),
                    'lastName' => $order->get_shipping_last_name(),
                    'company' => $order->get_shipping_company(),
                    'address1' => $order->get_shipping_address_1(),
                    'address2' => $order->get_shipping_address_2(),
                    'city' => $order->get_shipping_city(),
                    'postcode' => $order->get_shipping_postcode()
                ];
            }
        }
    }
    return $context;
}