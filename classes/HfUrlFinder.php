<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfUrlFinder implements Hf_iAssetLocator {
    private $Cms;

    function __construct( Hf_iCms $ContentManagementSystem ) {
        $this->Cms = $ContentManagementSystem;
    }

    public function getCurrentPageUrl() {
        $pageURL = $this->getHttpOrHttps();
        if ( $_SERVER["SERVER_PORT"] != "80" ) {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }

    private function getHttpOrHttps() {
        $prefix = 'http';
        if ( isset( $_SERVER["HTTPS"] ) ) {
            if ( $_SERVER["HTTPS"] == "on" ) {
                $prefix .= "s";
            }
        }
        $prefix .= "://";

        return $prefix;
    }

    public function getPluginAssetUrl( $fileName ) {
        return $this->Cms->getPluginAssetUrl( $fileName );
    }

    public function getHomePageUrl() {
        return $this->Cms->getHomeUrl();
    }

    public function getLogoutUrl( $redirect ) {
        return $this->Cms->getLogoutUrl( $redirect );
    }

    public function getLoginUrl() {
        return $this->getPageUrlByTitle( 'Authenticate' );
    }

    public function getPageUrlByTitle( $title ) {
        $page = $this->Cms->getPageByTitle( $title );

        return $this->Cms->getPermalink( $page->ID );
    }
}
