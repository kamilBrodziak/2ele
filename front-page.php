<?php
$context = Timber::context();
$context['post'] = new Timber\Post();
$templates = array( 'front-page.twig' );
Timber::render( $templates, $context );