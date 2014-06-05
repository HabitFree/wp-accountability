<?php

class HfUrlGenerator {

    function __construct() {
    }

    function addParametersToUrl($url, $parameters) {
        $name = key($parameters);
        $value = array_shift($parameters);

        if (strpos($url,'?') !== false) {
            $url .= '&' . $name . '=' . $value;
        } else {
            $url .= '?' . $name . '=' . $value;
        }

        if ( count($parameters) > 0 ) {
            $url = $this->addParametersToUrl($url, $parameters);
        }

        return $url;
    }
}