<?php
/**
 * @package 2eleTheme
 */
class ProductsController {
    private $productsPerPage = 20;

    public function __construct() {
        add_filter( 'loop_shop_per_page', [$this, 'productsPerPage'], 20 );
        add_action( 'pre_get_posts', [$this, 'customQueryPostsPerPage'] );
    }

    function customQueryPostsPerPage( $query ) {
        if ( $query->is_main_query() && !is_admin() ) {
            $query->set( 'posts_per_page', $this->productsPerPage());
        }
    }
    function productsPerPage( $cols = 0 ) {
        return $this->productsPerPage;
    }

    public function loadProductFromDB($id) {
        $product = wc_get_product($id);
        $galleryIDs = $product->get_gallery_image_ids();
        $gallery = [];
        if(count($galleryIDs) > 0) {
            array_unshift($galleryIDs, $product->get_image_id());
            foreach ($galleryIDs as $id) {
                $img = wp_get_attachment_url($id);
                $alt = get_post_meta( $id, '_wp_attachment_image_alt', true);
                $gallery[] = ['src' => $img, 'alt' => $alt];
            }
        }
        $productDetails = [
            'id' => $id,
            'price' => $product->get_regular_price(),
            'isVariable' => $product->is_type('variable'),
            'gallery' => $gallery
        ];
        if($productDetails['isVariable']) {
            $productDetails['variable'] = $this->getVariableProductDetails($product);
        }
        if($product->is_on_sale()) {
            $productDetails['salePrice'] = $product->get_sale_price();
        }

        return $productDetails;

    }

    public function loadProductsFromDB($argsList = []) {
        $products = [];
        $args = [
            'post_type' => 'product',
            'posts_per_page' => $this->productsPerPage,
            'post_status' => 'publish'
        ];
        if(!empty($argsList['paged'])) $args['paged'] = $argsList['paged'];
        if(!empty($argsList['product_cat'])) $args['product_cat'] = $argsList['product_cat'];
        if(!empty($argsList['exclude_category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $argsList['exclude_category'],
                    'operator' => 'NOT IN'
                ]
            ];
        }
        $args['meta_query'] = [
            [
                'key' => '_stock_status',
                'value' => ['instock', 'onbackorder'],
                'compare' => 'IN'
            ],
        ];
        if(!empty($argsList['s'])) {
            $args['s'] = $argsList['s'];
        } else {
            $args['order'] = 'ASC';
            $args['orderby'] = 'title';
        }
        $posts = new Timber\PostQuery($args);
        foreach ($posts as $post) {
            $product = wc_get_product($post->id);
            $productDetails = [
                'id' => $post->id,
                'title' => $post->title,
                'link' => $post->link,
                'thumbnail' => [
                    'src' => $post->thumbnail->src,
                    'alt' => $post->thumbnail->alt
                ],
                'price' => number_format((float)$product->get_regular_price(), 2),
                'isVariable' => $product->is_type('variable')
            ];
            if($productDetails['isVariable']) {
                $productDetails['variable'] = $this->getVariableProductDetails($product);
            } else {
                $productDetails['maxQuantity'] = $product->backorders_allowed() ? 99 : $product->get_stock_quantity();
            }
            if($product->is_on_sale()) {
                $productDetails['salePrice'] = number_format((float)$product->get_sale_price(), 2);
            }
            $products[] = $productDetails;
        }
        return $products;
    }

    private function getVariableProductDetails($product) {
        $minPrice = $product->get_variation_regular_price( 'min' );
        $maxPrice = $product->get_variation_regular_price( 'max' );

        $variableDetails = [
            'isPricesDiffer' => $minPrice != $maxPrice,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice
        ];

        if($product->is_on_sale()) {
            $minSalePrice = $product->get_variation_sale_price( 'min' );
            $maxSalePrice = $product->get_variation_sale_price( 'max' );
            $variableDetails['isSalePricesDiffer'] = $minSalePrice != $maxSalePrice;
            $variableDetails['minSalePrice'] = $minSalePrice;
            $variableDetails['maxSalePrice'] = $maxSalePrice;
        }

        $variableDetails['variations'] = $this->getProductVariations($product);
        return $variableDetails;
    }

    private function getProductVariations($product) {
        $variations = [];
        foreach ($product->get_available_variations() as $variation) {
            if($variation['variation_is_active'] && $variation['variation_is_visible'] && $variation['is_in_stock']) {
                $attrLabel = key($variation['attributes']);
                $variationDetails = [
                    'id' => $variation['variation_id'],
                    'name' => $variation['attributes'][$attrLabel],
                    'variationLabel' => str_replace("attribute_", "", $attrLabel),
                    'imageSrc' => $variation['image']['url'],
                    'imageAlt' => $variation['image']['alt'],
                    'price' => $variation['display_price']];
                $variationDetails['maxQuantity'] = $variation['backorders_allowed'] ? 999 :
                    ($variation['max_qty'] == "" ? 999 : $variation['max_qty']);
                if($variationDetails['maxQuantity'] != 0)
                    $variations[] = $variationDetails;
            }
        }
        return $variations;
    }
}
$productsController = new ProductsController();

function getCartProductsQuantity() {
    return WC()->cart->cart_contents_count;
}

function getCartQuantityNotices($products) {
    $cartNotices = [];
    foreach ($products as $productDetails) {
        if($productDetails['maxQuantityNotBackorder'] !== NULL &&
            $productDetails['maxQuantityNotBackorder'] < $productDetails['quantity']) {
            $notice = $productDetails['maxQuantityNotBackorder'] !== 0 ?
                'Na magazynie jest obecnie sztuk ' . $productDetails['maxQuantityNotBackorder']
                . " produktu " . $productDetails['title'] :
                'Na magazynie nie ma obecnie produktu ' . $productDetails['title'];

            $cartNotices[] = $notice .
                ". Możesz sfinalizować zamówienie, jednak będzie ono opóźnione."  ;
        }
    }
    return $cartNotices;
}

function getCart($gift = null) {
    $cart = WC()->cart;

    $products = [];
    $cartItems = $cart->get_cart();
    foreach($cartItems as $key => $values) {
        $products[] = getCartProductDetails($values, $values['data']);
    }

    if(!empty($gift) && !empty($gift['enabled']) && $gift['enabled']) {
        $total = 0;
        $categoryIDs = [];
        foreach ($gift['excludeCategories'] as $category) {
            if($category != "") {
                $categoryIDs[] = get_term_by( 'slug', $category, 'product_cat' )->term_id;
            }
        }
        $userLoggedIn = is_user_logged_in();
        foreach ($cartItems as $key => $values) {
            if($values['product_id'] == $gift['ID']) {
                $cart->remove_cart_item($key);
                $ind = 0;
                foreach ($products as $product) {
                    if($product['id'] == $gift['ID']) {
                        unset($products[$ind]);
                    }
                    $ind++;
                }
                continue;
            }
            $product = $values['data'];
            $productCategoryIDs = $product->get_category_ids();
            if($product->is_type('variation')) {
                $parent = wc_get_product($product->get_parent_id());
                $productCategoryIDs = $parent->get_category_ids();
            }
            if(count(array_diff($productCategoryIDs, $categoryIDs)) == count($productCategoryIDs)) {
                $price = floatval(floatval($product->get_price()) * $values['quantity']);
                $total += $price;
            }
        }
        $minimumTotalSpent = $total > $gift['cartTotal'];
        $hasNotBoughtItem = $userLoggedIn && !has_bought_items(get_current_user_id(), $gift['ID']);
        if(!$minimumTotalSpent) {
            $gift['message'] = sprintf($gift['messages']['minimumTotal'], $gift['cartTotal'] . "zł", implode(", ", $gift['excludeCategories']));
        } else if(!$userLoggedIn) {
            $gift['message'] = $gift['messages']['notLogged'];
        } else if(!$hasNotBoughtItem) {
            $product = wc_get_product($gift['ID']);
            $gift['message'] = sprintf($gift['messages']['alreadyAcquired'], $product->get_name());
        } else {
            $key = $cart->add_to_cart($gift['ID']);
            $cartItem = $cart->get_cart_item($key);
            $product = $cartItem['data'];
            $details = getCartProductDetails($cartItem, $product);
            $details['maxQuantity'] = 1;
            $cart->calculate_totals();
            $products[] = $details;
            $gift['message'] = sprintf($gift['messages']['success'], $product->get_name());
        }
    }


    return ['products' => $products, 'gift' => $gift];
}

function getGiftDetails() {
    $gift = ['enabled' => false];
    $options = get_option('2eleThemeGift');
    if(!empty($options['2eleThemeGiftsEnable']) && $options['2eleThemeGiftsEnable']) {
        $gift = [
            'enabled' => true,
            'ID' => $options['2eleThemeGiftsID'],
            'cartTotal' => floatval($options['2eleThemeGiftsCartTotal']),
            'excludeCategories' => explode(";", $options['2eleThemeGiftsExcludedCategories']),
            'messages' => [
                'notLogged' => $options['2eleThemeGiftsNotLoggedInText'],
                'alreadyAcquired' => $options['2eleThemeGiftsAlreadyAcquiredText'] ,
                'minimumTotal' => $options['2eleThemeGiftsMinimumCartTotalText'] ,
                'success' => $options['2eleThemeGiftsAddedText']
            ]
        ];
    }
    return $gift;
}

function has_bought_items( $user_var = 0,  $product_ids = 0 ) {
    global $wpdb;

    // Based on user ID (registered users)
    if ( is_numeric( $user_var) ) {
        $meta_key     = '_customer_user';
        $meta_value   = $user_var == 0 ? (int) get_current_user_id() : (int) $user_var;
    }
    // Based on billing email (Guest users)
    else {
        $meta_key     = '_billing_email';
        $meta_value   = sanitize_email( $user_var );
    }

    $paid_statuses    = array_map( 'esc_sql', wc_get_is_paid_statuses() );
    $product_ids      = is_array( $product_ids ) ? implode(',', $product_ids) : $product_ids;

    $line_meta_value  = $product_ids !=  ( 0 || '' ) ? 'AND woim.meta_value IN ('.$product_ids.')' : 'AND woim.meta_value != 0';

    // Count the number of products
    $count = $wpdb->get_var( "
        SELECT COUNT(p.ID) FROM {$wpdb->prefix}posts AS p
        INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
        INNER JOIN {$wpdb->prefix}woocommerce_order_items AS woi ON p.ID = woi.order_id
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woim ON woi.order_item_id = woim.order_item_id
        WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $paid_statuses ) . "' )
        AND pm.meta_key = '$meta_key'
        AND pm.meta_value = '$meta_value'
        AND woim.meta_key IN ( '_product_id', '_variation_id' ) $line_meta_value 
    " );

    // Return true if count is higher than 0 (or false)
    return $count > 0;
}

function getCartProductDetails($values, $product) {
    $productDetails = [
        'id' => $values['product_id'],
        'quantity' => $values['quantity'],
        'url' => get_permalink($values['product_id']),
        'key' => $values['key'],
        'removeUrl' => wc_get_cart_remove_url($values['key']),
    ];
    if($product->is_type('variable')) {
        $variationID = $values['variation_id'];
        $productDetails['variationID'] = $variationID;
    }

    if($product->managing_stock()) {
        $quantity = $product->get_stock_quantity();
        $quantity = $quantity >= 0 ? $quantity : 0;
        if($product->backorders_allowed()) {
            $productDetails['maxQuantity'] = 999;
            $productDetails['maxQuantityNotBackorder'] = $quantity;
        } else {
            $productDetails['maxQuantity'] = $quantity;
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
    return $productDetails;
}



function getCartTotal() {
    return WC()->cart->get_subtotal();
}



function getShippingMethods() {
    $shipping = WC()->shipping->get_shipping_methods();
//    WC()->shipping->calculate_shipping(WC()->shipping->get_packages(['country' => 'PL']));
//    $shipping_methods = WC()->shipping->packages;
    $activeMethods = [];
//    foreach($shipping_methods[0]['rates'] as $id => $shipping_method) {
//        $activeMethods[] = [
//            'id' => $shipping_method->method_id,
//            'type' => $shipping_method->method_id,
//            'name' => $shipping_method->label,
//            'price' => number_format($shipping_method->cost, 2, ',', ' ')
//        ];
//    }
    $shippingMethods = WC()->shipping->load_shipping_methods();
    foreach ($shippingMethods as $id => $shippingMethod) {
        if(isset($shippingMethod->enabled) && $shippingMethod->enabled == 'yes') {
            $activeMethods[$id] = [
                'title' => $shippingMethod->title,
                'tax_status' => $shippingMethod->tax_status
            ];
        }
    }
    $zones = WC_Shipping_Zones::get_zones();
//    var_dump(WC_Shipping_Zones::get_zones());
//    var_dump($zones);
    $shippingZones = [];
    foreach ($zones as $id => $zoneAttributes) {
//        var_dump(WC_Shipping_Zones::get_zone($id));
        $shippingZones[$id] = WC_Shipping_Zones::get_zone($id);
    }
//    var_dump(WC()->session->get_session_data());
    WC()->shipping->calculate_shipping(WC()->cart->get_shipping_packages());
    $packages = WC()->shipping->get_packages();

//    var_dump($packages);
    foreach ($packages as $i => $package) {
        $chosen_method = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
//        var_dump($package['rates']);
        foreach ($package['rates'] as $j => $rate) {
//            var_dump($rate->id);
//            var_dump($rate->instance_id);
//            var_dump($rate->label);
//            var_dump($rate->)
//            var_dump($rate->cost);
        }
    }
//    var_dump(WC()->session->chosen_shipping_methods);
    foreach ($shippingZones as $id => $zone) {
//        var_dump($zone);
        $zoneMethods = $zone->get_shipping_methods(true);
//        var_dump($zoneMethods);
        foreach ($zoneMethods as $method) {
//            var_dump($method->get_instance_id());
//            var_dump($method->get_title());
//            var_dump($method->cost);
//            var_dump($method->calculate_shipping());
        }
//        foreach ($zoneMethods as $id => $flexibleMethods) {
//            var_dump($id);
//            var_dump($zoneMethods[$id]);
//        }
    }
//    var_dump(WC_Shipping_Zone::get_shipping_methods( true ));
//    var_dump('================================');
//    var_dump(WC()->shipping->get_packages());
}


function custom_override_checkout_fields( $fields = array() ) {
    unset($fields['billing']['billing_country']);
    unset($fields['shipping']['shipping_country']);
    return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );


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