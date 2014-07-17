<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iCodeLibrary {
    public function getCurrentTime();
    public function convertStringToTime($string);
    public function randomKeyFromArray($array);
} 