<?php

interface Hf_iCodeLibrary {
    public function getCurrentTime();

    public function convertStringToTime($string);

    public function convertStringToInt($string);

    public function printToScreen($string);
} 