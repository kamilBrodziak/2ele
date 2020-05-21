<?php
$context            = Timber::context();
if ( is_singular( 'product' ) ) {
	$context['post']    = Timber::get_post();
	$product            = wc_get_product( $context['post']->ID );
	$context['product'] = $product;
	// Restore the context and loop back to the main query loop.
	wp_reset_postdata();

	Timber::render( 'single-product.twig', $context );
} else {
    //  if site category
	$url = "$_SERVER[REQUEST_URI]";
    $url_components = parse_url($url);
    parse_str($url_components['query'], $params);
    echo '<script>console.log("' . $params['page'] .'")</script>';
    $context['paged'] = 1;
    if($params['page']) {
        $context['paged'] = $params['page'];
    }

    if ( is_product_category() ) {
        $queried_object = get_queried_object();
        $term_id = $queried_object->term_id;
        $context['category'] = get_term( $term_id, 'product_cat' );
        $context['title'] = single_term_title( '', false );
        global $wp_query;
        $context['maxNumPages'] = $wp_query->max_num_pages;
//        $context['paged'] = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $context['firstPage'] = strtok(esc_url_raw(get_pagenum_link(1)), '?');
        $context['products'] = Timber::get_posts([
             'post_type' => 'product',
             'product_cat' => $context['title'],
             'orderby' => [
                 'title' => 'ASC'
             ],
             'posts_per_page' => 20,
             'paged' => $context['paged']
         ]);
    } else {
        $context['products'] = Timber::get_posts();
    }



	Timber::render( 'page-shop.twig', $context );
}
