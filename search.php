<?php
$timber_post = new Timber\Post();
$context['post'] = $timber_post;
$context = Timber::context();
global $productsController;
$templates = array('search.twig', 'page-sklep.twig', 'index.twig');
$context['currentPage'] = (get_query_var('paged')) ? get_query_var('paged') : 1;
$context['searchQuery'] = get_search_query();
$context['title'] = 'Wyniki wyszukiwania: ' . $context['searchQuery'];
$args = [
    's' => $context['searchQuery'],
    'paged' => $context['currentPage']
];
$context['products'] = $productsController->loadProductsFromDB($args);
$context['pagination'] = Timber::get_pagination([
    'end_size'     => 1,
    'mid_size'     => 2
]);

Timber::render($templates, $context);