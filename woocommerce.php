<?php
$context            = Timber::context();
global $productsController;


//$categories = get_terms('product_cat');
//foreach ($categories as $key => $category) {
//    var_dump($category->name);
//}
if ( is_singular( 'product' ) ) {
    $post = Timber::get_post();
	$context['post']    = $post;

	$context['product'] = $productsController->loadProductFromDB($context['post']->ID);
    $context['product']['title'] = $post->title;
    $context['product']['link'] = $post->link;
	$context['product']['thumbnail'] = [
	    'src' => $post->thumbnail->src,
        'alt' => $post->thumbnail->alt
    ];
	wp_reset_postdata();

	Timber::render( 'single-product.twig', $context );
} else {
//    $posts = Timber::get_posts();
//    $context['products'] = $posts;
//    if ( is_product_category() || is_shop()) {
//        $time1 = round(microtime(true) * 1000);

        $context['title'] = single_term_title( '', false );
        $context['currentPage'] = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = [
            'paged' => $context['currentPage']
        ];
        if(is_product_category()) {
            $queried_object = get_queried_object();
            $term_id = $queried_object->term_id;
            $category = get_term( $term_id, 'product_cat' );
            $context['category'] = [
                'slug' => $category->slug,
                'description' => $category->description
            ];
            if($context['category']['slug'] == 'sprzet' && !is_user_logged_in()) {
                Timber::render( '404.twig', $context );
                die();
            }
//            $context['category'] = get_term( $term_id, 'product_cat' )->slug;
            $args['product_cat'] = $category->slug;
        } else {
            $args['exclude_category'] = 'Sprzęt';
        }

    $context['products'] = $productsController->loadProductsFromDB($args);
//    $time2 = round(microtime(true) * 1000);
//    var_dump(($time2 - $time1)/1000);
//    }
    $context['pagination'] = Timber::get_pagination([
        'end_size'     => 1,
        'mid_size'     => 2
    ]);
	Timber::render( 'page-shop.twig', $context );
}
