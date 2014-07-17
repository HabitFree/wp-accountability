<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfPhpLibrary implements Hf_iCodeLibrary {
    public function getCurrentTime() {
        return time();
    }

    public function convertStringToTime($string) {
        return strtotime($string);
    }

    public function randomKeyFromArray($array) {
        return array_rand($array, 1);
    }
} 