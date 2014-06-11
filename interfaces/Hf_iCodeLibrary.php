<?php

interface Hf_iCodeLibrary {
    public function getCurrentTime();

    public function getPost( $name );

    public function getGet( $name );
} 