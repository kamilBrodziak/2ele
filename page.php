<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * To generate specific templates for your pages you can use:
 * /mytheme/templates/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::context();

$timber_post = new Timber\Post();
$context['post'] = $timber_post;
if($timber_post->post_name == "spis-tresci" ) {
	$args = [
		'post_type' => 'post',
		'order' => 'DESC',
	];

	$context['posts'] = Timber::get_posts($args);
//	$postCount = [];
	$i = 1;
	foreach($context['posts'] as $post) {
		$post->{'postOrder'} = $i;
		$i++;
	}
}

if(is_cart()) {
    $context['products'] = getCart();
    $context['checkoutUrl'] = getCheckoutUrl();
    $context['cartTotal'] = getCartTotal();
    Timber::render(array('page-cart.twig'), $context);
//    Timber::render( array( 'page-' . $timber_post->post_name . '.twig', 'page.twig' ), $context );

} else if(is_account_page() || is_checkout()) {
    Timber::render(array('page-login.twig'), $context);

} else {
    Timber::render( array( 'page-' . $timber_post->post_name . '.twig', 'page.twig' ), $context );

}
