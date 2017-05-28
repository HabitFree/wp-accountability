<?php

class HfDependencyChecker
{
    public function __construct( $cms ) {
        $this->cms = $cms;
    }

    public function getDependencyErrors() {
        if (! $this->cms->isPluginActive( "timber" ) ) {
            $this->cms->addSettingsError(
                "hfDependencyError",
                "hfDependencyError",
                "hf-accountability requires the following plugins: timber"
            );
        }
    }
}