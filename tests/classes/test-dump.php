<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestDump extends HfTestCase {
    // helpers

    private function makeRegisterShortcodeMockDependencies() {
        $UrlFinder   = $this->myMakeMock('HfUrlFinder');
        $Database    = $this->myMakeMock('HfMysqlDatabase');
        $PhpLibrary  = $this->myMakeMock('HfPhpLibrary');
        $Cms         = $this->myMakeMock('HfWordPressInterface');
        $UserManager = $this->myMakeMock('HfUserManager');

        return array($UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager);
    }

    private function makeDatabaseMockDependencies() {
        $Cms         = $this->myMakeMock('HfWordPressInterface');
        $CodeLibrary = $this->myMakeMock('HfPhpLibrary');

        return array($Cms, $CodeLibrary);
    }

    private function makeLogInShortcodeMockDependencies() {
        $UrlFinder   = $this->myMakeMock('HfUrlFinder');
        $PhpLibrary  = $this->myMakeMock('HfPhpLibrary');
        $Cms         = $this->myMakeMock('HfWordPressInterface');
        $UserManager = $this->myMakeMock('HfUserManager');

        return array($UrlFinder, $PhpLibrary, $Cms, $UserManager);
    }

    private function classImplementsInterface( $class, $interface ) {
        $interfacesImplemented = class_implements( $class );

        return in_array( $interface, $interfacesImplemented );
    }

    // tests

    public function testSendReportRequestEmailsSendsEmailWhenReportDue() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->myMakeMock( 'HfMysqlDatabase' );
        $CodeLibrary   = $this->myMakeMock( 'HfPhpLibrary' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->mySetReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->mySetReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->mySetReturnValue( $DbConnection, 'level', $mockLevel );

        $this->mySetReturnValue( $DbConnection, 'daysSinceLastEmail', 2 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceLastReport', 2 );
        $this->mySetReturnValue( $Messenger, 'isThrottled', false );

        $this->myExpectAtLeastOnce( $Messenger, 'sendReportRequestEmail' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary );
        $Goals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenReportNotDue() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->myMakeMock( 'HfMysqlDatabase' );
        $CodeLibrary   = $this->myMakeMock( 'HfPhpLibrary' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->mySetReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->mySetReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->mySetReturnValue( $DbConnection, 'level', $mockLevel );

        $this->mySetReturnValue( $DbConnection, 'daysSinceLastEmail', 2 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceLastReport', 0 );

        $this->myExpectNever( $Messenger, 'sendReportRequestEmail' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary );
        $Goals->sendReportRequestEmails();
    }

    public function testIsThrottledReturnsFalse() {
        $UrlFinder    = $this->myMakeMock( 'HfUrlFinder' );
        $Security     = $this->myMakeMock( 'HfSecurity' );
        $DbConnection = $this->myMakeMock( 'HfMysqlDatabase' );
        $ApiInterface = $this->myMakeMock( 'HfWordPressInterface' );

        $this->mySetReturnValue( $DbConnection, 'daysSinceAnyReport', 100 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceLastEmail', 10 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceSecondToLastEmail', 12 );

        $Mailer = new HfMailer( $UrlFinder, $Security, $DbConnection, $ApiInterface );
        $result = $Mailer->isThrottled( 1 );

        $this->assertEquals( $result, false );
    }

    public function testIsThrottledReturnsTrue() {
        $UrlFinder    = $this->myMakeMock( 'HfUrlFinder' );
        $Security     = $this->myMakeMock( 'HfSecurity' );
        $DbConnection = $this->myMakeMock( 'HfMysqlDatabase' );
        $ApiInterface = $this->myMakeMock( 'HfWordPressInterface' );

        $this->mySetReturnValue( $DbConnection, 'daysSinceAnyReport', 100 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceLastEmail', 10 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceSecondToLastEmail', 17 );

        $Mailer = new HfMailer( $UrlFinder, $Security, $DbConnection, $ApiInterface );
        $result = $Mailer->isThrottled( 1 );

        $this->assertEquals( $result, true );
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenUserThrottled() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->myMakeMock( 'HfMysqlDatabase' );
        $CodeLibrary   = $this->myMakeMock( 'HfPhpLibrary' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->mySetReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->mySetReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->mySetReturnValue( $DbConnection, 'level', $mockLevel );

        $this->mySetReturnValue( $DbConnection, 'daysSinceLastEmail', 2 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceLastReport', 5 );
        $this->mySetReturnValue( $Messenger, 'IsThrottled', true );

        $this->myExpectNever( $Messenger, 'sendReportRequestEmail' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary );
        $Goals->sendReportRequestEmails();
    }

    public function testStringToInt() {
        $PhpApi = new HfPhpLibrary();
        $string = '7';
        $int    = $PhpApi->convertStringToInt( $string );

        $this->assertTrue( $int === 7 );
    }

    public function testCurrentLevelTarget() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->myMakeMock( 'HfMysqlDatabase' );
        $CodeLibrary   = $this->myMakeMock( 'HfPhpLibrary' );

        $mockLevel         = new stdClass();
        $mockLevel->target = 14;
        $this->mySetReturnValue( $DbConnection, 'level', $mockLevel );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary );

        $target = $Goals->currentLevelTarget( 5 );

        $this->assertEquals( $target, 14 );
    }

    public function testHfFormClassExists() {
        $this->assertTrue( class_exists( 'HfGenericForm' ) );
    }

    public function testFormOuterTags() {
        $Form = new HfGenericForm( 'test.com' );
        $html = $Form->getHtml();

        $this->assertEquals( $html, '<form action="test.com" method="post"></form>' );
    }

    public function testAddTextBoxInputToForm() {
        $Form  = new HfGenericForm( 'test.com' );
        $name  = 'test';
        $label = 'Hello, there';

        $Form->addTextBox( $name, $label, '', false );

        $html = $Form->getHtml();

        $this->assertEquals( $html,
            '<form action="test.com" method="post"><p><label for="test">Hello, there: <input type="text" name="test" value="" /></label></p></form>'
        );
    }

    public function testAddSubmitButton() {
        $Form  = new HfGenericForm( 'test.com' );
        $name  = 'submit';
        $label = 'Submit';

        $Form->addSubmitButton( $name, $label );

        $html = $Form->getHtml();

        $this->assertEquals( $html, '<form action="test.com" method="post"><p><input type="submit" name="submit" value="Submit" /></p></form>' );
    }

    public function testGenerateAdminPanelButtons() {
        $Mailer       = $this->myMakeMock( 'HfMailer' );
        $URLFinder    = $this->myMakeMock( 'HfUrlFinder' );
        $DbConnection = $this->myMakeMock( 'HfMysqlDatabase' );
        $UserManager  = $this->myMakeMock( 'HfUserManager' );
        $Cms          = $this->myMakeMock( 'HfWordPressInterface' );

        $this->mySetReturnValue( $URLFinder, 'getCurrentPageURL', 'test.com' );

        $AdminPanel = new HfAdminPanel( $Mailer, $URLFinder, $DbConnection, $UserManager, $Cms );

        $expectedHtml = '<form action="test.com" method="post"><p><input type="submit" name="sendTestReportRequestEmail" value="Send test report request email" /></p><p><input type="submit" name="sendTestInvite" value="Send test invite" /></p><p><input type="submit" name="sudoReactivateExtension" value="Sudo reactivate extension" /></p></form>';
        $resultHtml   = $AdminPanel->generateAdminPanelForm();

        $this->assertEquals( $expectedHtml, $resultHtml );
    }

    public function testRegistrationShortcodeExists() {
        $this->assertTrue( shortcode_exists( 'hfAuthenticate' ) );
    }

    public function testRegistrationShortcodeHtml() {
        list( $UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager ) = $this->makeRegisterShortcodeMockDependencies();

        $this->mySetReturnValue( $UrlFinder, 'getCurrentPageURL', 'test.com' );
        $this->mySetReturnValue( $PhpLibrary, 'isPostEmpty', true );

        $RegisterShortcode = new HfRegisterShortcode( $UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager );

        $expectedHtml = '<form action="test.com" method="post"><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="email"><span class="required">*</span> Email: <input type="text" name="email" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><label for="passwordConfirmation"><span class="required">*</span> Confirm Password: <input type="password" name="passwordConfirmation" required /></label></p><p><input type="submit" name="submit" value="Register" /></p></form>';
        $resultHtml   = $RegisterShortcode->getOutput();

        $this->assertEquals( $expectedHtml, $resultHtml );
    }

    public function testWordPressPrintToScreenMethodExists() {
        $Php = new HfPhpLibrary();

        $this->assertTrue( method_exists( $Php, 'printToScreen' ) );
    }

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

    public function testRegisterShortcodeExists() {
        $this->assertTrue( class_exists( 'HfRegisterShortcode' ) );
    }

    public function testRegisterShortcodeUsesShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( 'HfRegisterShortcode', 'Hf_iShortcode' ) );
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

    public function testRegisterShortcodeCallsDeleteInvitation() {
        list( $UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager ) = $this->makeRegisterShortcodeMockDependencies();

        $this->mySetReturnValue( $PhpLibrary, 'isPostEmpty', false );
        $this->mySetReturnValue( $PhpLibrary, 'isUrlParameterEmpty', false );
        $this->mySetReturnValue( $PhpLibrary, 'getPost', 'test@gmail.com' );

        $mockInvite            = new stdClass();
        $mockInvite->inviterID = 777;

        $this->mySetReturnValue( $Database, 'getInvite', $mockInvite );

        $this->myExpectOnce( $UserManager, 'processInvite' );

        $RegisterShortcode = new HfRegisterShortcode( $UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager );

        $RegisterShortcode->getOutput();
    }

    public function testIsEmailTakenMethodExists() {
        $Factory = new HfFactory();
        $Cms     = $Factory->makeContentManagementSystem();

        $this->assertTrue( method_exists( $Cms, 'isEmailTaken' ) );
    }

    public function testRegisterShortcodeRejectsTakenEmails() {
        list( $UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager ) = $this->makeRegisterShortcodeMockDependencies();

        $RegisterShortcode = new HfRegisterShortcode( $UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager );

        $this->mySetReturnValue( $PhpLibrary, 'isPostEmpty', false );
        $this->mySetReturnValue( $PhpLibrary, 'isUrlParameterEmpty', false );
        $this->mySetReturnValue( $PhpLibrary, 'getPost', 'test@gmail.com' );

        $this->mySetReturnValue( $Cms, 'isEmailTaken', true );

        $mockInvite            = new stdClass();
        $mockInvite->inviterID = 777;

        $this->mySetReturnValue( $Database, 'getInvite', $mockInvite );

        $this->myExpectAtLeastOnce( $Cms, 'isEmailTaken' );
        $this->myExpectNever( $Cms, 'createUser' );

        $output = $RegisterShortcode->getOutput();

        $this->assertTrue( strstr( $output, "<p class='fail'>Oops. That email is already in use.</p>" ) != false );
    }

    public function testLogInShortcodeImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( 'HfLogInShortcode', 'Hf_iShortcode' ) );
    }

    public function testLogInShortcodeOutputsLogInForm() {
        list( $UrlFinder, $PhpLibrary, $Cms, $UserManager ) = $this->makeLogInShortcodeMockDependencies();

        $this->mySetReturnValue( $PhpLibrary, 'isUrlParameterEmpty', true );
        $this->mySetReturnValue( $PhpLibrary, 'isPostEmpty', true );
        $this->mySetReturnValue( $UrlFinder, 'getCurrentPageUrl', 'test.com' );

        $LogInShortcode = new HfLogInShortcode( $UrlFinder, $PhpLibrary, $Cms, $UserManager );
        $resultHtml     = $LogInShortcode->getOutput();

        $expectedHtml = '<form action="test.com" method="post"><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><input type="submit" name="submit" value="Log In" /></p></form>';

        $this->assertEquals( $resultHtml, $expectedHtml );
    }

    public function testLogInShortcodeWithAlternateActionUrl() {
        list( $UrlFinder, $PhpLibrary, $Cms, $UserManager ) = $this->makeLogInShortcodeMockDependencies();

        $this->mySetReturnValue( $PhpLibrary, 'isUrlParameterEmpty', true );
        $this->mySetReturnValue( $PhpLibrary, 'isPostEmpty', true );
        $this->mySetReturnValue( $UrlFinder, 'getCurrentPageUrl', 'anothertest.com' );

        $LogInShortcode = new HfLogInShortcode( $UrlFinder, $PhpLibrary, $Cms, $UserManager );
        $resultHtml     = $LogInShortcode->getOutput();

        $expectedHtml = '<form action="anothertest.com" method="post"><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><input type="submit" name="submit" value="Log In" /></p></form>';

        $this->assertEquals( $resultHtml, $expectedHtml );
    }

    public function testLogInShortcodeOutputsSuccessMessage() {
        list( $UrlFinder, $PhpLibrary, $Cms, $UserManager ) = $this->makeLogInShortcodeMockDependencies();

        $this->mySetReturnValue( $PhpLibrary, 'isUrlParameterEmpty', true );
        $this->mySetReturnValue( $PhpLibrary, 'isPostEmpty', false );
        $this->mySetReturnValue( $Cms, 'authenticateUser', new stdClass() );
        $this->mySetReturnValue( $Cms, 'isError', false );

        $LogInShortcode = new HfLogInShortcode( $UrlFinder, $PhpLibrary, $Cms, $UserManager );

        $resultHtml   = $LogInShortcode->getOutput();
        $expectedHtml = '<p class="success">You have been successfully logged in.</p><p><a href="/">Onward!</a></p>';

        $this->assertEquals( $resultHtml, $expectedHtml );
    }

    public function testLogInShortcodeDisplaysEmptyFieldErrors() {
        list( $UrlFinder, $PhpLibrary, $Cms, $UserManager ) = $this->makeLogInShortcodeMockDependencies();

        $this->mySetReturnValue( $PhpLibrary, 'isUrlParameterEmpty', true );
        $this->mySetReturnValues($PhpLibrary, 'isPostEmpty', array(false, true, false, true, true, true, true));

        $LogInShortcode = new HfLogInShortcode( $UrlFinder, $PhpLibrary, $Cms, $UserManager );

        $resultHtml   = $LogInShortcode->getOutput();
        $expectedHtml = '<p class="fail">Please provide a valid username and password combination.</p>';

        $isStringThere = (strstr( $resultHtml, $expectedHtml ) != false);
        var_dump($isStringThere);
        $this->assertTrue( $isStringThere );
    }

    public function testLogInShortcodeAuthenticatesUser() {
        list( $UrlFinder, $PhpLibrary, $Cms, $UserManager ) = $this->makeLogInShortcodeMockDependencies();

        $this->mySetReturnValue( $PhpLibrary, 'isUrlParameterEmpty', true );
        $this->myExpectOnce( $Cms, 'authenticateUser' );

        $LogInShortcode = new HfLogInShortcode( $UrlFinder, $PhpLibrary, $Cms, $UserManager );

        $LogInShortcode->getOutput();
    }

    public function testLogInShortcodeOutputsErrorMessageWhenLogInUnsuccessful() {
        list( $UrlFinder, $PhpLibrary, $Cms, $UserManager ) = $this->makeLogInShortcodeMockDependencies();

        $this->mySetReturnValue( $PhpLibrary, 'isUrlParameterEmpty', true );
        $this->mySetReturnValue( $PhpLibrary, 'isPostEmpty', false );
        $this->mySetReturnValue( $Cms, 'isError', true );

        $LogInShortcode = new HfLogInShortcode( $UrlFinder, $PhpLibrary, $Cms, $UserManager );

        $resultHtml   = $LogInShortcode->getOutput();
        $expectedHtml = '<p class="fail">Please provide a valid username and password combination.</p>';

        $this->assertTrue( strstr( $resultHtml, $expectedHtml ) != false );
    }

    public function testLogInShortcodeLooksForUsernameAndPassword() {
        list( $UrlFinder, $PhpLibrary, $Cms, $UserManager ) = $this->makeLogInShortcodeMockDependencies();

        $this->mySetReturnValue( $PhpLibrary, 'isUrlParameterEmpty', true );

        $this->myExpectAt($PhpLibrary, 'getPost', 0, array('username'));
        $this->myExpectAt($PhpLibrary, 'getPost', 1, array('password'));

        $LogInShortcode = new HfLogInShortcode( $UrlFinder, $PhpLibrary, $Cms, $UserManager );

        $LogInShortcode->getOutput();
    }

    public function testLogInShortcodeChecksForNonce() {
        list( $UrlFinder, $PhpLibrary, $Cms, $UserManager ) = $this->makeLogInShortcodeMockDependencies();


        $this->mySetReturnValue( $PhpLibrary, 'isPostEmpty', false );
        $this->mySetReturnValue( $Cms, 'isError', false );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $this->mySetReturnValue( $Cms, 'authenticateUser', $mockUser );

        $this->myExpectAtLeastOnce( $PhpLibrary, 'getUrlParameter', array('n') );

        $LogInShortcode = new HfLogInShortcode( $UrlFinder, $PhpLibrary, $Cms, $UserManager );
        $LogInShortcode->getOutput();
    }

    public function testLogInShortcodeCreatesRelationship() {
        list( $UrlFinder, $PhpLibrary, $Cms, $UserManager ) = $this->makeLogInShortcodeMockDependencies();

        $this->mySetReturnValue( $PhpLibrary, 'isPostEmpty', false );
        $this->mySetReturnValue( $Cms, 'isError', false );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $this->mySetReturnValue( $Cms, 'authenticateUser', $mockUser );

        $this->myExpectAtLeastOnce( $UserManager, 'processInvite' );

        $LogInShortcode = new HfLogInShortcode( $UrlFinder, $PhpLibrary, $Cms, $UserManager );
        $LogInShortcode->getOutput();
    }

    public function testAuthenticateShortcodeClassExists() {
        $this->assertTrue( class_exists( 'HfAuthenticateShortcode' ) );
    }

    public function testAuthenticateShortcodeClassImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( 'HfAuthenticateShortcode', 'Hf_iShortcode' ) );
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

        $isStringThere = (strstr( $result, $expected ) != false);
        $this->assertTrue( $isStringThere );
    }

    public function testAuthenticateShortcodeGeneratesTabs() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $isStringThere = (strstr( $result, '[su_tabs active="1"]' ) != false);
        $this->assertTrue( $isStringThere );
    }

    public function testAuthenticateShortcodeGeneratesLogInTab() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $this->assertTrue( strstr( $result, '[su_tab title="Log In"]' ) != false );
    }

    public function testAuthenticateShortcodeGeneratesRegisterTab() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $this->assertTrue( strstr( $result, '[su_tab title="Register"]' ) != false );
    }

    public function testAuthenticateShortcodeIncludesLogInForm() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $logInHtml = '<form action="anothertest.com" method="post"><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><input type="submit" name="login" value="Log In" /></p></form>';

        $this->assertTrue( strstr( $result, $logInHtml ) != false );
    }

    public function testMakeAuthenticateShortcode() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $this->assertTrue( is_a( $AuthenticateShortcode, 'HfAuthenticateShortcode' ) );
    }
} 