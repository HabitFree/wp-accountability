<?php

require_once(dirname(__FILE__) . '/../../../../wp-load.php' );

print_r('hello<br />');
print_r(plugins_url());

class HfAuthenticateHook {
    private $Cms;

    function __construct(Hf_iCms $Cms) {
        $this->Cms = $Cms;
    }

    public function authenticate() {
        $this->Cms->authenticateUser($_POST['username'], $_POST['password']);
    }
}

$Factory = new HfFactory();
$Hook = $Factory->makeAuthenticateHook();
$Hook->authenticate();