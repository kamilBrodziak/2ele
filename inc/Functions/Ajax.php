<?php
/**
 * @package starterWordpressTheme
 */

function changePage() {
    $paged = $_POST['page'];
    $context = Timber::context();
    $context['maxNumPages'] = $_POST['pageCount'];
    $context['paged'] = $paged;
    $context['firstPage'] = $_POST['firstPage'];
    $context['products'] = Timber::get_posts([
        'post_type' => 'product',
        'product_cat' => $_POST['category'],
        'orderby' => [
            'title' => 'ASC'
        ],
        'posts_per_page' => 20,
        'paged' => $paged
    ]);

    Timber::render('partials/productListWidget.twig', $context);
    die();
}
add_action('wp_ajax_nopriv_changePage', 'changePage');
add_action('wp_ajax_changePage', 'changePage');