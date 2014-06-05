<?php

class HfPhpLibrary implements Hf_iCodeLibrary {
    public function getCurrentTime() {
        return time();
    }

    public function convertStringToTime($string) {
        return strtotime($string);
    }

    public function convertStringToInt($string) {
        return intval($string);
    }

    public function printToScreen($string) {
        echo $string;
    }

    public function getUrlParameter($name) {
        return $_GET[$name];
    }

    public function isUrlParameterEmpty($name) {
        return empty($_GET[$name]);
    }

    public function convertIntToString($int) {
        return strval($int);
    }

    public function isPostEmpty($name) {
        return empty($_POST[$name]);
    }

    public function getPost($name) {
        return $_POST[$name];
    }

    public function getGet($name) {
        return $_GET[$name];
    }
} 