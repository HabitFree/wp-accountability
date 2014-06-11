<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestDump extends HfTestCase {
    // helpers

    private function makeDatabaseMockDependencies() {
        $Cms         = $this->myMakeMock( 'HfWordPressInterface' );
        $CodeLibrary = $this->myMakeMock( 'HfPhpLibrary' );

        return array($Cms, $CodeLibrary);
    }

    // tests

    public function testViewInterfaceExists() {
        $this->assertTrue( interface_exists( 'Hf_iView' ) );
    }

    public function testSettingsShortcodeClassExists() {
        $this->assertTrue( class_exists( 'HfSettingsShortcode' ) );
    }

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
        $UrlFinder = $this->Factory->makeUrlFinder();

        $this->assertTrue( is_a( $UrlFinder, 'HfUrlFinder' ) );
    }

    public function testFactoryMakeHtmlGenerator() {
        $HtmlGenerator = $this->Factory->makeHtmlGenerator();

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
        $WordPressInterface = $this->Factory->makeContentManagementSystem();

        $this->assertTrue( is_a( $WordPressInterface, 'HfWordPressInterface' ) );
    }

    public function testFactoryMakeSecurity() {
        $Security = $this->Factory->makeSecurity();

        $this->assertTrue( is_a( $Security, 'HfSecurity' ) );
    }

    public function testFactoryMakeSettingsShortcode() {
        $SettingsShortcode = $this->Factory->makeSettingsShortcode();

        $this->assertTrue( is_a( $SettingsShortcode, 'HfSettingsShortcode' ) );
    }

    public function testSettingsShortcodeOutputsAnything() {
        $SettingsShortcode = $this->Factory->makeSettingsShortcode();
        $output            = $SettingsShortcode->getOutput();

        $this->assertTrue( strlen( $output ) > 0 );
    }

    public function testGoalsShortcodeClassExists() {
        $this->assertTrue( class_exists( 'HfGoalsShortcode' ) );
    }

    public function testGoalsShortcodeOutputsAnything() {
        $GoalsShortcode = $this->Factory->makeGoalsShortcode();
        $output         = $GoalsShortcode->getOutput();

        $this->assertTrue( strlen( $output ) > 0 );
    }

    public function testFormAbstractClassExists() {
        $this->assertTrue( class_exists( 'HfForm' ) );
    }

    public function testHfAccountabilityFormClassExists() {
        $this->assertTrue( class_exists( 'HfAccountabilityForm' ) );
    }

    public function testHfAccountabilityFormClassHasPopulateMethod() {
        $Goals              = $this->myMakeMock( 'HfGoals' );
        $AccountabilityForm = new HfAccountabilityForm( 'test.com', $Goals );
        $this->assertTrue( method_exists( $AccountabilityForm, 'populate' ) );
    }

    public function testGetGoalSubscriptions() {
        list( $Cms, $CodeLibrary ) = $this->makeDatabaseMockDependencies();

        $this->myExpectOnce( $Cms, 'getRows' );

        $Database = new HfMysqlDatabase( $Cms, $CodeLibrary );

        $Database->getGoalSubscriptions( 1 );
    }

    public function testSendEmailReportRequests() {
        $Factory = new HfFactory();
        $Goals   = $Factory->makeGoals();
        $Goals->sendReportRequestEmails();
    }

    public function testCmsHasDeleteRowsFunction() {
        $Cms = new HfWordPressInterface();

        $this->assertTrue( method_exists( $Cms, 'deleteRows' ) );
    }

    public function testDatabaseHasDeleteInvitationMethod() {
        list( $Cms, $CodeLibrary ) = $this->makeDatabaseMockDependencies();

        $Database = new HfMysqlDatabase( $Cms, $CodeLibrary );

        $this->assertTrue( method_exists( $Database, 'deleteInvite' ) );
    }

    public function testDatabaseCallsDeleteRowsMethod() {
        list( $Cms, $CodeLibrary ) = $this->makeDatabaseMockDependencies();

        $Database = new HfMysqlDatabase( $Cms, $CodeLibrary );

        $this->myExpectOnce( $Cms, 'deleteRows' );

        $Database->deleteInvite( 777 );
    }

    public function testIsEmailTakenMethodExists() {
        $Factory = new HfFactory();
        $Cms     = $Factory->makeContentManagementSystem();

        $this->assertTrue( method_exists( $Cms, 'isEmailTaken' ) );
    }

    public function testHtmlGeneratorCreatesTabs() {
        $HtmlGenerator = $this->Factory->makeHtmlGenerator();

        $contents = array(
            'duck1' => 'quack',
            'duck2' => 'quack, quack',
            'duck3' => 'quack, quack, quack'
        );

        $expected = '[su_tabs active="1"][su_tab title="duck1"]quack[/su_tab][su_tab title="duck2"]quack, quack[/su_tab][su_tab title="duck3"]quack, quack, quack[/su_tab][/su_tabs]';

        $result = $HtmlGenerator->generateTabs( $contents, 1 );

        $this->assertTrue( strstr( $result, $expected ) != false );
    }

    public function testHtmlGeneratorCreatesDifferentTabs() {
        $HtmlGenerator = $this->Factory->makeHtmlGenerator();

        $contents = array(
            'duck1' => 'quack',
            'duck2' => 'quack, quack'
        );

        $expected = '[su_tabs active="2"][su_tab title="duck1"]quack[/su_tab][su_tab title="duck2"]quack, quack[/su_tab][/su_tabs]';

        $result = $HtmlGenerator->generateTabs( $contents, 2 );

        $isStringThere = ( strstr( $result, $expected ) != false );
        $this->assertTrue( $isStringThere );
    }

    public function testMakeAuthenticateShortcode() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $this->assertTrue( is_a( $AuthenticateShortcode, 'HfAuthenticateShortcode' ) );
    }
} 