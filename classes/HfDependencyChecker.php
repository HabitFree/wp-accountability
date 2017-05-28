<?php

class HfDependencyChecker
{
    public function __construct( $cms ) {
        $this->cms = $cms;
    }

    public function getDependencyErrors() {
        $this->cms->isPluginActive( "timber" );
    }
}