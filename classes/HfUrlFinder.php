<?php

class HfUrlFinder implements Hf_iAssetLocator {
    private $Cms;

    function __construct( Hf_iContentManagementSystem $ContentManagementSystem ) {
        $this->Cms = $ContentManagementSystem;
    }

    public function getCurrentPageUrl() {
        $pageURL = 'http';
        if ( isset( $_SERVER["HTTPS"] ) ) {
            if ( $_SERVER["HTTPS"] == "on" ) {
                $pageURL .= "s";
            }
        }
        $pageURL .= "://";
        if ( $_SERVER["SERVER_PORT"] != "80" ) {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }

    public function getPageUrlByTitle( $title ) {
        print('Title: ');
        var_dump($title);
        $page = get_page_by_title( $title );

        return get_permalink( $page->ID );
    }

    public function getPluginAssetUrl( $fileName ) {
        return $this->Cms->getPluginAssetUrl( $fileName );
    }
}
