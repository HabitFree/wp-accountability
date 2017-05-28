<?php

echo "hello";

$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER["SERVER_PORT"] = 8080;
$_SERVER["REQUEST_URI"] = '';

define('ABSPATH','testing');

require dirname(dirname( __FILE__ )) . '/hf-autoload.php';