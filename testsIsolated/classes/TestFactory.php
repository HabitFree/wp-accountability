<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestFactory extends HfTestCase {
    // Helper Functions
    
    // Tests

    public function testFactoryClassExists() {
        $this->assertTrue( class_exists( 'HfFactory' ) );
    }

    public function testFactoryMakeGoals() {
        $Goals = $this->factory->makeGoals();

        $this->assertInstanceOf('HfGoals', $Goals);
    }

    public function testFactoryMakeUserManager() {
        $UserManager = $this->factory->makeUserManager();

        $this->assertInstanceOf('HfUserManager', $UserManager);
    }

    public function testFactoryMakeMailer() {
        $Mailer = $this->factory->makeMessenger();

        $this->assertInstanceOf('HfMailer', $Mailer);
    }

    public function testFactoryMakeUrlFinder() {
        $UrlFinder = $this->factory->makeAssetLocator();

        $this->assertInstanceOf('HfUrlFinder', $UrlFinder);
    }

    public function testFactoryMakeHtmlGenerator() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();

        $this->assertInstanceOf('HfHtmlGenerator', $HtmlGenerator);
    }

    public function testFactoryMakeDatabase() {
        $Database = $this->factory->makeDatabase();

        $this->assertInstanceOf('HfMysqlDatabase', $Database);
    }

    public function testFactoryMakePhpLibrary() {
        $PhpLibrary = $this->factory->makeCodeLibrary();

        $this->assertInstanceOf('HfPhpLibrary', $PhpLibrary);
    }

    public function testFactoryMakeWordPressInterface() {
        $WordPressInterface = $this->factory->makeCms();

        $this->assertInstanceOf('HfWordPress', $WordPressInterface);
    }

    public function testFactoryMakeSecurity() {
        $Security = $this->factory->makeSecurity();

        $this->assertInstanceOf('HfSecurity', $Security);
    }

    public function testFactoryMakeSettingsShortcode() {
        $SettingsShortcode = $this->factory->makeSettingsShortcode();

        $this->assertInstanceOf('HfSettingsShortcode', $SettingsShortcode);
    }

    public function testFactoryMakeManagePartnersShortcode() {
        $ManagePartnersShortcode = $this->factory->makeManagePartnersShortcode();
        $this->assertInstanceOf('HfManagePartnersShortcode', $ManagePartnersShortcode);
    }

    public function testMakeLoginForm() {
        $f = $this->factory->makeLoginForm('jo');
        $this->assertInstanceOf('HfLoginForm', $f);
    }

    public function testMakeDependencyChecker() {
        $d = $this->factory->makeDependencyChecker();
        $this->assertInstanceOf('HfDependencyChecker', $d);
    }
}
