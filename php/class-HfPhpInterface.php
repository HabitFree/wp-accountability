<?php

class HfPhpInterface {
    public function getCurrentTime() {
        return time();
    }

    public function convertStringToTime($string) {
        return strtotime($string);
    }

    public function convertStringToInt($string) {
        return intval($string);
    }
} 