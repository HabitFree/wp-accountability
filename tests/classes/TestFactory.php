<?php
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestFactory extends HfTestCase {
    // Helper Functions
    
    // Tests

    public function testFactoryClassExists() {
        $this->assertTrue( class_exists( 'HfFactory' ) );
    }

    public function testFactoryMakeGoals() {
        $Goals = $this->Factory->makeGoals();

        $this->assertTrue( is_a( $Goals, 'HfGoals' ) );
    }

    public function testFactoryMakeUserManager() {
        $UserManager = $this->Factory->makeUserManager();

        $this->assertTrue( is_a( $UserManager, 'HfUserManager' ) );
    }

    public function testFactoryMakeMailer() {
        $Mailer = $this->Factory->makeMessenger();

        $this->assertTrue( is_a( $Mailer, 'HfMailer' ) );
    }

    public function testFactoryMakeUrlFinder() {
        $UrlFinder = $this->Factory->makeAssetLocator();

        $this->assertTrue( is_a( $UrlFinder, 'HfUrlFinder' ) );
    }

    public function testFactoryMakeHtmlGenerator() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();

        $this->assertTrue( is_a( $HtmlGenerator, 'HfHtmlGenerator' ) );
    }

    public function testFactoryMakeDatabase() {
        $Database = $this->Factory->makeDatabase();

        $this->assertTrue( is_a( $Database, 'HfMysqlDatabase' ) );
    }

    public function testFactoryMakePhpLibrary() {
        $PhpLibrary = $this->Factory->makeCodeLibrary();

        $this->assertTrue( is_a( $PhpLibrary, 'HfPhpLibrary' ) );
    }

    public function testFactoryMakeWordPressInterface() {
        $WordPressInterface = $this->Factory->makeCms();

        $this->assertTrue( is_a( $WordPressInterface, 'HfWordPress' ) );
    }

    public function testFactoryMakeSecurity() {
        $Security = $this->Factory->makeSecurity();

        $this->assertTrue( is_a( $Security, 'HfSecurity' ) );
    }

    public function testFactoryMakeSettingsShortcode() {
        $SettingsShortcode = $this->Factory->makeSettingsShortcode();

        $this->assertTrue( is_a( $SettingsShortcode, 'HfSettingsShortcode' ) );
    }
}
