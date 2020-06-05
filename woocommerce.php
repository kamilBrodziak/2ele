<?php
$context            = Timber::context();
if ( is_singular( 'product' ) ) {
	$context['post']    = Timber::get_post();
	$product            = wc_get_product( $context['post']->ID );
	$context['product'] = $product;
	wp_reset_postdata();

	Timber::render( 'single-product.twig', $context );
} else {
    $posts = Timber::get_posts();
    $context['products'] = $posts;
    if ( is_product_category() || is_shop()) {
        $queried_object = get_queried_object();
        $term_id = $queried_object->term_id;
        $context['category'] = get_term( $term_id, 'product_cat' );
        $context['title'] = single_term_title( '', false );
        $context['currentPage'] = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $context['products'] = Timber::get_posts([
            'post_type' => 'product',
            'product_cat' => $context['title'],
            'orderby' => [
                'title' => 'ASC'
            ],
            'posts_per_page' => getProductsPerPageAmount(),
            'paged' => $context['currentPage']
         ]);
    }
    $context['pagination'] = Timber::get_pagination([
        'end_size'     => 1,
        'mid_size'     => 2
    ]);
	Timber::render( 'page-shop.twig', $context );
}
