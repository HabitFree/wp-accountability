<?php

class HfPhpLibrary implements Hf_iCodeLibrary {
    public function getCurrentTime() {
        return time();
    }

    public function convertStringToTime($string) {
        return strtotime($string);
    }
} 