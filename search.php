<?php
$templates = array( 'search.twig', 'page-sklep.twig', 'index.twig' );
$context          = Timber::context();

$context['currentPage'] = (get_query_var('paged')) ? get_query_var('paged') : 1;
$context['searchQuery'] =  get_search_query();
$context['title'] = 'Wyniki wyszukiwania: ' . $context['searchQuery'];
$args = [
    'post_type' => 'product',
    'posts_per_page' => getProductsPerPageAmount(),
    's' => $context['searchQuery'],
    'paged' => $context['currentPage'],
    'post_status' => 'publish'
];
$context['posts'] = new Timber\PostQuery($args);
$context['products'] = $context['posts'];

$context['pagination'] = Timber::get_pagination([
    'end_size'     => 1,
    'mid_size'     => 2
]);

Timber::render( $templates, $context );