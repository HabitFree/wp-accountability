<?php

class HfWebView implements Hf_iView {
    private $CodeLibrary;

    function __construct(Hf_iCodeLibrary $CodeLibrary) {
        $this->CodeLibrary = $CodeLibrary;
    }

    public function displayContent($string) {}

    public function displayErrorMessage($string) {
        $this->CodeLibrary->printToScreen('<p class="error">' . $string . '</p>');
    }

    public function displaySuccessMessage($string) {
        $this->CodeLibrary->printToScreen('<p class="success">' . $string . '</p>');
    }

    public function displayInfoMessage($string) {
        $this->CodeLibrary->printToScreen('<p class="info">' . $string . '</p>');
    }

    public function displayWarningMessage($string) {}
} 