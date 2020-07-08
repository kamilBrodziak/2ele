<?php
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
        $productDetails = [
            'id' => $id,
            'price' => $product->get_regular_price(),
            'isVariable' => $product->is_type('variable')
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
        if($argsList['paged']) $args['paged'] = $argsList['paged'];
        if($argsList['product_cat']) $args['product_cat'] = $argsList['product_cat'];
        if($argsList['s']) $args['s'] = $argsList['s'];
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
        $products[] = $productDetails;
    }
    return $products;
//    return WC()->cart->get_cart_contents();
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
            var_dump($rate->id);
            var_dump($rate->instance_id);
            var_dump($rate->label);
//            var_dump($rate->)
            var_dump($rate->cost);
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