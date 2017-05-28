<?php

class HfDependencyChecker
{
    public function __construct( $cms ) {
        $this->cms = $cms;
    }

    public function checkForDependencyErrors() {
        if (! $this->cms->isPluginActive( "timber" ) ) {
            $this->cms->addAdminErrorMessage( "hf-accountability requires the following plugins: timber" );
        }
    }
}