<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestFactory extends HfTestCase {
    // Helper Functions
    
    // Tests

    public function testFactoryClassExists() {
        $this->assertTrue( class_exists( 'HfFactory' ) );
    }

    public function testFactoryMakeGoals() {
        $Goals = $this->Factory->makeGoals();

        $this->assertInstanceOf('HfGoals', $Goals);
    }

    public function testFactoryMakeUserManager() {
        $UserManager = $this->Factory->makeUserManager();

        $this->assertInstanceOf('HfUserManager', $UserManager);
    }

    public function testFactoryMakeMailer() {
        $Mailer = $this->Factory->makeMessenger();

        $this->assertInstanceOf('HfMailer', $Mailer);
    }

    public function testFactoryMakeUrlFinder() {
        $UrlFinder = $this->Factory->makeAssetLocator();

        $this->assertInstanceOf('HfUrlFinder', $UrlFinder);
    }

    public function testFactoryMakeHtmlGenerator() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();

        $this->assertInstanceOf('HfHtmlGenerator', $HtmlGenerator);
    }

    public function testFactoryMakeDatabase() {
        $Database = $this->Factory->makeDatabase();

        $this->assertInstanceOf('HfMysqlDatabase', $Database);
    }

    public function testFactoryMakePhpLibrary() {
        $PhpLibrary = $this->Factory->makeCodeLibrary();

        $this->assertInstanceOf('HfPhpLibrary', $PhpLibrary);
    }

    public function testFactoryMakeWordPressInterface() {
        $WordPressInterface = $this->Factory->makeCms();

        $this->assertInstanceOf('HfWordPress', $WordPressInterface);
    }

    public function testFactoryMakeSecurity() {
        $Security = $this->Factory->makeSecurity();

        $this->assertInstanceOf('HfSecurity', $Security);
    }

    public function testFactoryMakeSettingsShortcode() {
        $SettingsShortcode = $this->Factory->makeSettingsShortcode();

        $this->assertInstanceOf('HfSettingsShortcode', $SettingsShortcode);
    }

    public function testFactoryMakeManagePartnersShortcode() {
        $ManagePartnersShortcode = $this->Factory->makeManagePartnersShortcode();
        $this->assertInstanceOf('HfManagePartnersShortcode', $ManagePartnersShortcode);
    }

    public function testMakeLoginForm() {
        $f = $this->Factory->makeLoginForm('jo');
        $this->assertInstanceOf('HfLoginForm', $f);
    }
}
