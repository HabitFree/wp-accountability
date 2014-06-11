<?php

class HfPhpLibrary implements Hf_iCodeLibrary {
    public function getCurrentTime() {
        return time();
    }

    public function isPostEmpty( $name ) {
        return empty( $_POST[$name] );
    }

    public function getPost( $name ) {
        return $_POST[$name];
    }

    public function getGet( $name ) {
        return $_GET[$name];
    }
} 