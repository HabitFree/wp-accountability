<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iView {
    public function displayContent($string);
    public function displayErrorMessage($string);
    public function displaySuccessMessage($string);
    public function displayInfoMessage($string);
    public function displayWarningMessage($string);
} 