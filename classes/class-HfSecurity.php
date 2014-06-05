<?php

if (!class_exists("HfSecurity")) {
    class HfSecurity {

        function HfSecurity() {
        }

        function createRandomString($length) {
            $randomBits = openssl_random_pseudo_bytes($length / 2);
            return bin2hex($randomBits);
        }
    }
}
?>