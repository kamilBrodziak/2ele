<?php

$context = Timber::context();
$context['posts'] = new Timber\PostQuery();
$templates = array( 'front-page.twig' );
Timber::render( $templates, $context );
