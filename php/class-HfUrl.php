<?php

if (!class_exists("HfUrl")) {
    class HfUrl {

        function HfUrl() {
        }

        function getCurrentPageUrl() {
            $pageURL = 'http';
            if( isset($_SERVER["HTTPS"]) ) {
                if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
            }
            $pageURL .= "://";
            if ($_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
            return $pageURL;
        }

        function getReportPageUrl() {
            return $this->getURLByTitle('Goals');
        }

        function getURLByTitle($title) {
            $page = get_page_by_title( $title );
            return get_permalink( $page->ID );
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
}

?>