<?php

interface Hf_iCodeLibrary {
    public function getCurrentTime();

    public function isPostEmpty( $name );

    public function getPost( $name );

    public function getGet( $name );
} 