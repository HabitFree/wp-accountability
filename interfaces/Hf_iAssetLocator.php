<?php

interface Hf_iAssetLocator {
    public function getCurrentPageUrl();

    public function getPageUrlByTitle( $title );

    public function getPluginAssetUrl( $fileName );

    public function getHomePageUrl();

    public function getLogoutUrl($redirect);

    public function getLoginUrl();
}