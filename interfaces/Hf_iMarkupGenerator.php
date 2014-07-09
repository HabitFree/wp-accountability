<?php

interface Hf_iMarkupGenerator {
    public function progressBar( $percent, $label );

    public function generateTabs( $contents, $defaultTabNumber );

    public function makeParagraph( $content );

    public function makeLink( $target, $content );

    public function makeList( $items );

    public function makeError( $content );

    public function makeSuccessMessage( $content );

    public function makeQuoteMessage( $content );

    public function makeForm( $url, $content, $name );

    public function makeButton( $name, $label, $onclick );

    public function makeHiddenField( $name );
}