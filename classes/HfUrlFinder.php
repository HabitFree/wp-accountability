<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfUrlFinder implements Hf_iAssetLocator {
    private $cms;

    function __construct( Hf_iCms $contentManagementSystem ) {
        $this->cms = $contentManagementSystem;
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
        return $this->cms->getPluginAssetUrl( $fileName );
    }

    public function getHomePageUrl() {
        return $this->cms->getHomeUrl();
    }

    public function getLogoutUrl( $redirect ) {
        return $this->cms->getLogoutUrl( $redirect );
    }

    public function getLoginUrl() {
        return $this->getPageUrlByTitle( 'Authenticate' );
    }

    public function getPageUrlByTitle( $title ) {
        $page = $this->cms->getPageByTitle( $title );

        return $this->cms->getPermalink( $page->ID );
    }
}
