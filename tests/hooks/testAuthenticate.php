<?php
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );
require_once( dirname(dirname(dirname( __FILE__ ))) . '/hooks/authenticate.php' );

class testAuthenticate extends HfTestCase {
    public function testAbspathExists() {
        $this->assertTrue(defined('ABSPATH'));
    }

    public function testAuthenticateHookClassExists() {
        $this->assertTrue( class_exists( 'HfAuthenticateHook' ) );
    }

    public function testAuthenticateHookSignsUserIn() {
        $_POST['username'] = 'jo';
        $_POST['password'] = 'pass';

        $Cms = $this->MockCms;
        $this->expectOnce($Cms, 'authenticateUser', array('jo', 'pass'));

        $hook = new HfAuthenticateHook($Cms);
        $hook->authenticate();
    }
}
