<?php
/**
 * Created by PhpStorm.
 * User: michaeleckert
 * Date: 6/1/14
 * Time: 4:11 PM
 */

namespace tests;

require_once(dirname(__FILE__) . '/hf-accountability.classes');

class TestPHPUnit extends \PHPUnit_Framework_TestCase {
    public function testTestingFramework() {
        $this->assertEquals(1, 1);
    }
}

class GeneralWordPress extends \PHPUnit_Framework_TestCase {
    public function testGettingCurrentUserLogin() {
        $ApiInterface   = new HfWordPressInterface();
        $user           = wp_get_current_user($ApiInterface);
        $PhpApi         = new HfPhpLibrary();
        $DbConnection   = new HfDatabase($ApiInterface, $PhpApi);
        $UrlFinder      = new HfUrlFinder();
        $UrlGenerator   = new HfUrlGenerator();
        $Security       = new HfSecurity();
        $Mailer         = new HfMailer($UrlFinder, $UrlGenerator, $Security, $DbConnection, $ApiInterface);
        $UserManager    = new HfUserManager($DbConnection, $Mailer, $UrlFinder, $ApiInterface, $PhpApi);

        $this->assertEquals($UserManager->getCurrentUserLogin(), $user->user_login);
    }
}