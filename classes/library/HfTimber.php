<?php

class HfTimber
{
    public function render( $template, $data ) {
        Timber::$locations = dirname( __FILE__ ) . "/../../twig/";
        $context = Timber::get_context();
        $context = array_merge( $context, $data );
        Timber::render($template, $context);
    }
}