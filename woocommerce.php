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
	$context['products'] = Timber::get_posts();

	Timber::render( 'page-sklep.twig', $context );
}