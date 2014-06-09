<?php

interface Hf_iCodeLibrary {
    public function getCurrentTime();

    public function convertStringToTime($string);

    public function convertStringToInt($string);

    public function printToScreen($string);

    public function getUrlParameter($name);

    public function isUrlParameterEmpty($name);

    public function convertIntToString($int);

    public function isPostEmpty($name);

    public function getPost($name);

    public function getGet($name);
} 