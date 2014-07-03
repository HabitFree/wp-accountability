<?php

interface Hf_iCodeLibrary {
    public function getCurrentTime();
    public function convertStringToTime($string);
    public function randomKeyFromArray($array);
} 