<?php

require_once(dirname(__FILE__) . '/../hf-accountability.php');

class UnitWpSimpleTest extends UnitTestCase {
    private $functionsFacade;
    private $Factory;

    public function __construct() {
        $this->Factory = new HfFactory();
    }

    public function setUp() {

    }

//    Helper Functions

    private function makeUserManagerMockDependencies() {
        Mock::generate('HfDatabase');
        Mock::generate('HfMailer');
        Mock::generate('HfUrlFinder');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfPhpLibrary');

        $UrlFinder  = new MockHfUrlFinder();
        $Database   = new MockHfDatabase();
        $Messenger  = new MockHfMailer();
        $Cms        = new MockHfWordPressInterface();
        $PhpApi     = new MockHfPhpLibrary();

        return array($UrlFinder, $Database, $Messenger, $Cms, $PhpApi);
    }

    private function makeRegisterShortcodeMockDependencies() {
        Mock::generate('HfUrlFinder');
        Mock::generate('HfDatabase');
        Mock::generate('HfPhpLibrary');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfUserManager');

        $UrlFinder      = new MockHfUrlFinder();
        $Database       = new MockHfDatabase();
        $PhpLibrary     = new MockHfPhpLibrary();
        $Cms            = new MockHfWordPressInterface();
        $UserManager    = new MockHfUserManager();

        return array($UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager);
    }

    private function makeDatabaseMockDependencies() {
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfPhpLibrary');

        $Cms            = new MockHfWordPressInterface();
        $CodeLibrary    = new MockHfPhpLibrary();

        return array($Cms, $CodeLibrary);
    }

    private function classImplementsInterface($class, $interface) {
        $interfacesImplemented = class_implements($class);
        return in_array($interface, $interfacesImplemented);
    }

//    Tests

    public function testTestingFramework() {
        $this->assertEqual(1, 1);
    }

//     Below this still to be migrated to PHPUnit

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

		$this->assertEqual($UserManager->getCurrentUserLogin(), $user->user_login);
	}

    public function testShortcodeRegistration() {
    	$this->assertEqual(shortcode_exists('hfSettings'), true);
    }

    public function testDbDataNullRemoval() {
        $Database = $this->Factory->makeDatabase();

    	$data = array(
				'one' => 'big one',
				'two' => 'two',
				'three' => null,
				'four' => 4,
				'five' => 'null',
				'six' => 0,
				'seven' => false
			);

		$expectedData = array(
				'one' => 'big one',
				'two' => 'two',
				'four' => 4,
				'five' => 'null',
				'six' => 0,
				'seven' => false
			);

    	$this->assertEqual($Database->removeNullValuePairs($data), $expectedData);
    }

	public function testSchemaColumnObjectCreation() {
        $Database           = $this->Factory->makeDatabase();
		$columnObject       = $Database->createColumnSchemaObject('openTime', 'timestamp', 'YES', '', null, '');

		$expected           = new StdClass;
		$expected->Field    = 'openTime';
		$expected->Type     = 'timestamp';
		$expected->Null     = 'YES';
		$expected->Key      = '';
		$expected->Default  = null;
		$expected->Extra    = '';

		$this->assertEqual($columnObject, $expected);
	}

	public function testEmailTableSchema() {
        $Database       = $this->Factory->makeDatabase();
        $currentSchema  = $Database->getTableSchema('hf_email');

		$expectedSchema = array(
				'emailID'		=> $Database->createColumnSchemaObject('emailID', 'int(11)', 'NO', 'PRI', null, 'auto_increment'),
				'sendTime'		=> $Database->createColumnSchemaObject('sendTime', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', ''),
				'subject'		=> $Database->createColumnSchemaObject('subject', 'varchar(500)', 'NO', '', null, ''),
				'body'			=> $Database->createColumnSchemaObject('body', 'text', 'NO', '', null, ''),
				'userID'		=> $Database->createColumnSchemaObject('userID', 'int(11)', 'NO', 'MUL', null, ''),
				'deliveryStatus'=> $Database->createColumnSchemaObject('deliveryStatus', 'bit(1)', 'NO', '', "b'0'", ''),
				'openTime'		=> $Database->createColumnSchemaObject('openTime', 'datetime', 'YES', '', null, ''),
				'address'		=> $Database->createColumnSchemaObject('address', 'varchar(80)', 'YES', '', null, '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testGoalTableSchema() {
        $Database       = $this->Factory->makeDatabase();
		$currentSchema  = $Database->getTableSchema('hf_goal');

		$expectedSchema = array(
				'goalID'		=> $Database->createColumnSchemaObject('goalID', 'int(11)', 'NO', 'PRI', null, 'auto_increment'),
				'title'			=> $Database->createColumnSchemaObject('title', 'varchar(500)', 'NO', '', null, ''),
				'description'	=> $Database->createColumnSchemaObject('description', 'text', 'YES', '', null, ''),
				'thumbnail'		=> $Database->createColumnSchemaObject('thumbnail', 'varchar(80)', 'YES', '', null, ''),
				'isPositive'	=> $Database->createColumnSchemaObject('isPositive', 'bit(1)', 'NO', '', "b'0'", ''),
				'isPrivate'		=> $Database->createColumnSchemaObject('isPrivate', 'bit(1)', 'NO', '', "b'1'", ''),
				'creatorID'		=> $Database->createColumnSchemaObject('creatorID', 'int(11)', 'YES', 'MUL', null, ''),
				'dateCreated'	=> $Database->createColumnSchemaObject('dateCreated', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testReportTableSchema() {
        $Database       = $this->Factory->makeDatabase();
		$currentSchema  = $Database->getTableSchema('hf_report');

		$expectedSchema = array(
				'reportID'			=> $Database->createColumnSchemaObject('reportID', 'int(11)', 'NO', 'PRI', null, 'auto_increment'),
				'userID'			=> $Database->createColumnSchemaObject('userID', 'int(11)', 'NO', 'MUL', null, ''),
				'goalID'			=> $Database->createColumnSchemaObject('goalID', 'int(11)', 'NO', 'MUL', null, ''),
				'referringEmailID'	=> $Database->createColumnSchemaObject('referringEmailID', 'int(11)', 'YES', 'MUL', null, ''),
				'isSuccessful'		=> $Database->createColumnSchemaObject('isSuccessful', 'tinyint(4)', 'NO', '', null, ''),
				'date'				=> $Database->createColumnSchemaObject('date', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testUserGoalTableSchema() {
        $Database       = $this->Factory->makeDatabase();
		$currentSchema  = $Database->getTableSchema('hf_user_goal');

		$expectedSchema = array(
				'userID'		=> $Database->createColumnSchemaObject('userID', 'int(11)', 'NO', 'PRI', null, ''),
				'goalID'		=> $Database->createColumnSchemaObject('goalID', 'int(11)', 'NO', 'PRI', null, ''),
				'dateStarted'	=> $Database->createColumnSchemaObject('dateStarted', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', ''),
				'isActive'		=> $Database->createColumnSchemaObject('isActive', 'bit(1)', 'NO', '', "b'1'", '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testLevelTableSchema() {
        $Database       = $this->Factory->makeDatabase();
		$currentSchema  = $Database->getTableSchema('hf_level');

		$expectedSchema = array(
				'levelID'		=> $Database->createColumnSchemaObject('levelID', 'int(11)', 'NO', 'PRI', null, ''),
				'title'			=> $Database->createColumnSchemaObject('title', 'varchar(500)', 'NO', '', null, ''),
				'description'	=> $Database->createColumnSchemaObject('description', 'text', 'YES', '', null, ''),
				'size'			=> $Database->createColumnSchemaObject('size', 'int(11)', 'NO', '', null, ''),
				'emailInterval'	=> $Database->createColumnSchemaObject('emailInterval', 'int(11)', 'NO', '', null, ''),
				'target'		=> $Database->createColumnSchemaObject('target', 'int(11)', 'NO', '', null, ''),
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testInviteTableSchema() {
        $Database       = $this->Factory->makeDatabase();
		$currentSchema  = $Database->getTableSchema('hf_invite');

		$expectedSchema = array(
				'inviteID'		=> $Database->createColumnSchemaObject('inviteID', 'varchar(250)', 'NO', 'PRI', null, ''),
				'inviterID'		=> $Database->createColumnSchemaObject('inviterID', 'int(11)', 'NO', 'MUL', null, ''),
				'inviteeEmail'	=> $Database->createColumnSchemaObject('inviteeEmail', 'varchar(80)', 'NO', '', null, ''),
				'emailID'		=> $Database->createColumnSchemaObject('emailID', 'int(11)', 'NO', 'MUL', null, ''),
				'expirationDate'=> $Database->createColumnSchemaObject('expirationDate', 'datetime', 'NO', '', null, '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

    public function testRelationshipTableSchema() {
        $Database       = $this->Factory->makeDatabase();
        $currentSchema  = $Database->getTableSchema('hf_relationship');

        $expectedSchema = array(
            'userID1'		=> $Database->createColumnSchemaObject('userID1', 'int(11)', 'NO', 'PRI', null, ''),
            'userID2'		=> $Database->createColumnSchemaObject('userID2', 'int(11)', 'NO', 'PRI', null, '')
        );

        $this->assertEqual($currentSchema, $expectedSchema);
    }

	public function testRandomStringCreationLength() {
        $Security       = $this->Factory->makeSecurity();
		$randomString   = $Security->createRandomString(400);

		$this->assertEqual(strlen($randomString), 400);
	}

    public function testEmailInviteSendingUsingMocks() {
        list($UrlFinder, $Database, $Messenger, $Cms, $PhpApi) = $this->makeUserManagerMockDependencies();

        $Messenger->returns('generateInviteID', 555);
        $Database->returns('generateEmailID', 5);

        $UserManager = new HfUserManager($Database, $Messenger, $UrlFinder, $Cms, $PhpApi);
        $result = $UserManager->sendInvitation(1, 'me@test.com', 3);

        $this->assertEqual($result, 555);
    }

	public function testPHPandMySQLtimezonesMatch() {
		$phpTime	= date('Y-m-d H:i:s');
		global $wpdb;
		$mysqlTime	= $wpdb->get_results("SELECT NOW()", ARRAY_A);
		$this->assertEqual($phpTime, $mysqlTime[0]['NOW()']);
	}

    public function testInviteStorageInInviteTableUsingMocks() {
        list($UrlFinder, $Database, $Messenger, $Cms, $PhpApi) = $this->makeUserManagerMockDependencies();

        $UserManager = new HfUserManager($Database, $Messenger, $UrlFinder, $Cms, $PhpApi);

        $Database->returns('generateEmailID', 5);

        $expirationDate = date('Y-m-d H:i:s', strtotime('+'. 3 .' days'));

        $expectedRecord = array(
            'inviteID' => 555,
            'inviterID' => 1,
            'inviteeEmail' => 'me@test.com',
            'emailID' => 5,
            'expirationDate' => $expirationDate
        );

        $Database->expectAt(
            1, 'insertIntoDb',
            array('hf_invite', $expectedRecord ));

        $UserManager->sendInvitation(1, 'me@test.com', 3);
    }

    public function testSendEmailByUserID() {
        Mock::generate('HfUrlFinder');
        Mock::generate('HfUrlGenerator');
        Mock::generate('HfSecurity');
        Mock::generate('HfDatabase');
        Mock::generate('HfWordPressInterface');

        $UrlFinder      = new MockHfUrlFinder();
        $UrlGenerator   = new MockHfUrlGenerator();
        $Security       = new MockHfSecurity();
        $DbConnection   = new MockHfDatabase();
        $WordPressApi   = new MockHfWordPressInterface();

        $WordPressApi->returns('getVar', 5);
        $WordPressApi->returns('getUserEmail', 'me@test.com');

        $DbConnection->expectOnce(
            'recordEmail',
            array(1, 'test', 'test', 5, 'me@test.com'));

        $Mailer = new HfMailer($UrlFinder, $UrlGenerator, $Security, $DbConnection, $WordPressApi);
        $Mailer->sendEmailToUser(1, 'test', 'test');
    }

    public function testSendEmailToUserAndSpecifyEmailID() {
        Mock::generate('HfUrlFinder');
        Mock::generate('HfUrlGenerator');
        Mock::generate('HfSecurity');
        Mock::generate('HfDatabase');
        Mock::generate('HfWordPressInterface');

        $UrlFinder      = new MockHfUrlFinder();
        $UrlGenerator   = new MockHfUrlGenerator();
        $Security       = new MockHfSecurity();
        $DbConnection   = new MockHfDatabase();
        $WordPressApi   = new MockHfWordPressInterface();

        $Mailer = new HfMailer($UrlFinder, $UrlGenerator, $Security, $DbConnection, $WordPressApi);

        $userID     = 1;
        $subject    = 'test subject';
        $body       = 'test body';
        $emailID    = 123;

        $WordPressApi->returns('sendWpEmail', true);
        $WordPressApi->returns('getUserEmail', 'me@test.com');

        $DbConnection->expectOnce(
            'recordEmail',
            array( $userID, $subject, $body, $emailID, 'me@test.com' ));

        $Mailer->sendEmailToUserAndSpecifyEmailID($userID, $subject, $body, $emailID);

    }

    public function testSendReportRequestEmailsChecksThrottling() {
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfDatabase');
        Mock::generate('HfPhpLibrary');

        $Messenger                  = new MockHfMailer();
        $WebsiteApi                 = new MockHfWordPressInterface();
        $HtmlGenerator              = new MockHfHtmlGenerator();
        $DbConnection               = new MockHfDatabase();
        $CodeLibrary                = new MockHfPhpLibrary();

        $mockUser                   = new stdClass();
        $mockUser->ID               = 1;
        $mockUsers                  = array($mockUser);
        $WebsiteApi->returns('getSubscribedUsers', $mockUsers);

        $mockGoalSub                = new stdClass();
        $mockGoalSub->goalID        = 1;
        $mockGoalSubs               = array($mockGoalSub);
        $DbConnection->returns('getRows', $mockGoalSubs);

        $mockLevel                  = new stdClass();
        $mockLevel->emailInterval   = 1;
        $DbConnection->returns('level', $mockLevel);

        $DbConnection->returns('daysSinceLastReport', 2);
        $Messenger->returns('isThrottled', true);

        $Messenger->expectAtLeastOnce('isThrottled');

        $Goals = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary);
        $Goals->sendReportRequestEmails();
    }

    public function testDaysSinceLastEmail() {
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfPhpLibrary');

        $WebsiteAPI = new MockHfWordPressInterface();
        $PhpApi     = new MockHfPhpLibrary();

        $WebsiteAPI->returns('getVar', '2014-05-27 16:04:29');
        $PhpApi->returns('convertStringToTime', 1401224669.0);
        $PhpApi->returns('getCurrentTime', 1401483869.0);

        $Database   = new HfDatabase($WebsiteAPI, $PhpApi);
        $result     = $Database->daysSinceLastEmail(1);

        $this->assertEqual($result, 3);
    }

    public function testSendReportRequestEmailsSendsEmailWhenReportDue() {
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfDatabase');
        Mock::generate('HfPhpLibrary');

        $Messenger                  = new MockHfMailer();
        $WebsiteApi                 = new MockHfWordPressInterface();
        $HtmlGenerator              = new MockHfHtmlGenerator();
        $DbConnection               = new MockHfDatabase();
        $CodeLibrary                = new MockHfPhpLibrary();

        $mockUser                   = new stdClass();
        $mockUser->ID               = 1;
        $mockUsers                  = array($mockUser);
        $WebsiteApi->returns('getSubscribedUsers', $mockUsers);

        $mockGoalSub                = new stdClass();
        $mockGoalSub->goalID        = 1;
        $mockGoalSubs               = array($mockGoalSub);
        $DbConnection->returns('getRows', $mockGoalSubs);

        $mockLevel                  = new stdClass();
        $mockLevel->emailInterval   = 1;
        $DbConnection->returns('level', $mockLevel);

        $DbConnection->returns('daysSinceLastEmail', 2);
        $DbConnection->returns('daysSinceLastReport', 2);
        $Messenger->returns('isThrottled', false);

        $Messenger->expectAtLeastOnce('sendReportRequestEmail');

        $Goals = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary);
        $Goals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenReportNotDue() {
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfDatabase');
        Mock::generate('HfPhpLibrary');

        $Messenger                  = new MockHfMailer();
        $WebsiteApi                 = new MockHfWordPressInterface();
        $HtmlGenerator              = new MockHfHtmlGenerator();
        $DbConnection               = new MockHfDatabase();
        $CodeLibrary                = new MockHfPhpLibrary();

        $mockUser                   = new stdClass();
        $mockUser->ID               = 1;
        $mockUsers                  = array($mockUser);
        $WebsiteApi->returns('getSubscribedUsers', $mockUsers);

        $mockGoalSub                = new stdClass();
        $mockGoalSub->goalID        = 1;
        $mockGoalSubs               = array($mockGoalSub);
        $DbConnection->returns('getRows', $mockGoalSubs);

        $mockLevel                  = new stdClass();
        $mockLevel->emailInterval   = 1;
        $DbConnection->returns('level', $mockLevel);

        $DbConnection->returns('daysSinceLastEmail', 2);
        $DbConnection->returns('daysSinceLastReport', 0);

        $Messenger->expectNever('sendReportRequestEmail');

        $Goals = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary);
        $Goals->sendReportRequestEmails();
    }

    public function testIsThrottledReturnsFalse() {
        Mock::generate('HfUrlFinder');
        Mock::generate('HfUrlGenerator');
        Mock::generate('HfSecurity');
        Mock::generate('HfDatabase');
        Mock::generate('HfWordPressInterface');

        $UrlFinder      = new MockHfUrlFinder();
        $UrlGenerator   = new MockHfUrlGenerator();
        $Security       = new MockHfSecurity();
        $DbConnection   = new MockHfDatabase();
        $ApiInterface   = new MockHfWordPressInterface();

        $DbConnection->returns('daysSinceAnyReport',100);
        $DbConnection->returns('daysSinceLastEmail',10);
        $DbConnection->returns('daysSinceSecondToLastEmail',12);

        $Mailer         = new HfMailer($UrlFinder, $UrlGenerator, $Security, $DbConnection, $ApiInterface);
        $result         = $Mailer->isThrottled(1);

        $this->assertEqual($result, false);
    }

    public function testIsThrottledReturnsTrue() {
        Mock::generate('HfUrlFinder');
        Mock::generate('HfUrlGenerator');
        Mock::generate('HfSecurity');
        Mock::generate('HfDatabase');
        Mock::generate('HfWordPressInterface');

        $UrlFinder      = new MockHfUrlFinder();
        $UrlGenerator   = new MockHfUrlGenerator();
        $Security       = new MockHfSecurity();
        $DbConnection   = new MockHfDatabase();
        $ApiInterface   = new MockHfWordPressInterface();

        $DbConnection->returns('daysSinceAnyReport',100);
        $DbConnection->returns('daysSinceLastEmail',10);
        $DbConnection->returns('daysSinceSecondToLastEmail',17);

        $Mailer         = new HfMailer($UrlFinder, $UrlGenerator, $Security, $DbConnection, $ApiInterface);
        $result         = $Mailer->isThrottled(1);

        $this->assertEqual($result, true);
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenUserThrottled() {
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfDatabase');
        Mock::generate('HfPhpLibrary');

        $Messenger                  = new MockHfMailer();
        $WebsiteApi                 = new MockHfWordPressInterface();
        $HtmlGenerator              = new MockHfHtmlGenerator();
        $DbConnection               = new MockHfDatabase();
        $CodeLibrary                = new MockHfPhpLibrary();

        $mockUser                   = new stdClass();
        $mockUser->ID               = 1;
        $mockUsers                  = array($mockUser);
        $WebsiteApi->returns('getSubscribedUsers', $mockUsers);

        $mockGoalSub                = new stdClass();
        $mockGoalSub->goalID        = 1;
        $mockGoalSubs               = array($mockGoalSub);
        $DbConnection->returns('getRows', $mockGoalSubs);

        $mockLevel                  = new stdClass();
        $mockLevel->emailInterval   = 1;
        $DbConnection->returns('level', $mockLevel);

        $DbConnection->returns('daysSinceLastEmail', 2);
        $DbConnection->returns('daysSinceLastReport', 5);
        $Messenger->returns('IsThrottled', true);

        $Messenger->expectNever('sendReportRequestEmail');

        $Goals = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary);
        $Goals->sendReportRequestEmails();
    }

    public function testStringToInt() {
        $PhpApi = new HfPhpLibrary();
        $string = '7';
        $int = $PhpApi->convertStringToInt($string);

        $this->assertTrue($int === 7);
    }

    public function testCurrentLevelTarget() {
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfDatabase');
        Mock::generate('HfPhpLibrary');

        $Messenger          = new MockHfMailer();
        $WebsiteApi         = new MockHfWordPressInterface();
        $HtmlGenerator      = new MockHfHtmlGenerator();
        $DbConnection       = new MockHfDatabase();
        $CodeLibrary        = new MockHfPhpLibrary();

        $mockLevel          = new stdClass();
        $mockLevel->target  = 14;
        $DbConnection->returns('level', $mockLevel);

        $Goals = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary);

        $target = $Goals->currentLevelTarget(5);

        $this->assertEqual($target, 14);
    }

    public function testHfFormClassExists() {
        $this->assertTrue(class_exists('HfGenericForm'));
    }

    public function testFormOuterTags() {
        $Form = new HfGenericForm('test.com');
        $html = $Form->getHtml();

        $this->assertEqual($html, '<form action="test.com" method="post"></form>');
    }

    public function testAddTextBoxInputToForm() {
        $Form   = new HfGenericForm('test.com');
        $name   = 'test';
        $label  = 'Hello, there';

        $Form->addTextBox($name, $label, '', false);

        $html = $Form->getHtml();

        $this->assertEqual($html,
            '<form action="test.com" method="post"><p><label for="test">Hello, there: <input type="text" name="test" value="" /></label></p></form>'
        );
    }

    public function testAddSubmitButton() {
        $Form   = new HfGenericForm('test.com');
        $name   = 'submit';
        $label  = 'Submit';

        $Form->addSubmitButton($name, $label);

        $html = $Form->getHtml();

        $this->assertEqual($html, '<form action="test.com" method="post"><p><input type="submit" name="submit" value="Submit" /></p></form>');
    }

    public function testGenerateAdminPanelButtons() {
        Mock::generate('HfMailer');
        Mock::generate('HfUrlFinder');
        Mock::generate('HfDatabase');
        Mock::generate('HfUserManager');

        $Mailer         = new MockHfMailer();
        $URLFinder      = new MockHfUrlFinder();
        $DbConnection   = new MockHfDatabase();
        $UserManager    = new MockHfUserManager();

        $URLFinder->returns('getCurrentPageURL', 'test.com');

        $AdminPanel = new HfAdminPanel($Mailer, $URLFinder, $DbConnection, $UserManager);

        $expectedHtml = '<form action="test.com" method="post"><p><input type="submit" name="sendTestReportRequestEmail" value="Send test report request email" /></p><p><input type="submit" name="sendTestInvite" value="Send test invite" /></p><p><input type="submit" name="sudoReactivateExtension" value="Sudo reactivate extension" /></p></form>';
        $resultHtml = $AdminPanel->generateAdminPanelForm();

        $this->assertEqual($expectedHtml, $resultHtml);
    }

    public function testRegistrationShortcodeExists() {
        $this->assertTrue(shortcode_exists('hfRegister'));
    }

    public function testRegistrationShortcodeHtml() {
        list($UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager) = $this->makeRegisterShortcodeMockDependencies();

        $UrlFinder->returns('getCurrentPageURL', 'test.com');
        $PhpLibrary->returns('isPostEmpty', true);

        $RegisterShortcode    = new HfRegisterShortcode($UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager);

        $expectedHtml   = '<form action="test.com" method="post"><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="email"><span class="required">*</span> Email: <input type="text" name="email" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><label for="passwordConfirmation"><span class="required">*</span> Confirm Password: <input type="password" name="passwordConfirmation" required /></label></p><p><input type="submit" name="submit" value="Register" /></p></form>';
        $resultHtml     = $RegisterShortcode->getOutput();

        $this->assertEqual($expectedHtml, $resultHtml);
    }

    public function testWordPressPrintToScreenMethodExists() {
        $Php = new HfPhpLibrary();

        $this->assertTrue(method_exists($Php, 'printToScreen'));
    }

    public function testViewInterfaceExists() {
        $this->assertTrue(interface_exists('Hf_iView'));
    }

    public function testWebViewClassExists() {
        $this->assertTrue(class_exists('HfWebView'));
    }

    public function testDisplayErrorMessage() {
        Mock::generate('HfPhpLibrary');

        $PhpLibrary = new MockHfPhpLibrary();
        $WebView    = new HfWebView($PhpLibrary);
        $message    = '<p class="error">Error!</p>';

        $PhpLibrary->expectOnce('printToScreen', array($message));

        $WebView->displayErrorMessage('Error!');
    }

    public function testDisplaySuccessMessage() {
        Mock::generate('HfPhpLibrary');

        $PhpLibrary = new MockHfPhpLibrary();
        $WebView    = new HfWebView($PhpLibrary);
        $message    = '<p class="success">Success!</p>';

        $PhpLibrary->expectOnce('printToScreen', array($message));

        $WebView->displaySuccessMessage('Success!');
    }

    public function testDisplayInfoMessage() {
        Mock::generate('HfPhpLibrary');

        $PhpLibrary = new MockHfPhpLibrary();
        $WebView    = new HfWebView($PhpLibrary);
        $message    = '<p class="info">FYI</p>';

        $PhpLibrary->expectOnce('printToScreen', array($message));

        $WebView->displayInfoMessage('FYI');
    }

    public function testSettingsShortcodeClassExists() {
        $this->assertTrue(class_exists('HfSettingsShortcode'));
    }

    public function testFactoryClassExists() {
        $this->assertTrue(class_exists('HfFactory'));
    }

    public function testFactoryMakeGoals() {
        $Factory = new HfFactory();
        $Goals = $Factory->makeGoals();

        $this->assertTrue(is_a($Goals, 'HfGoals'));
    }

    public function testFactoryMakeUserManager() {
        $Factory = new HfFactory();
        $UserManager = $Factory->makeUserManager();

        $this->assertTrue(is_a($UserManager, 'HfUserManager'));
    }

    public function testFactoryMakeMailer() {
        $Factory = new HfFactory();
        $Mailer = $Factory->makeMessenger();

        $this->assertTrue(is_a($Mailer, 'HfMailer'));
    }

    public function testFactoryMakeUrlFinder() {
        $Factory = new HfFactory();
        $UrlFinder = $Factory->makeUrlFinder();

        $this->assertTrue(is_a($UrlFinder, 'HfUrlFinder'));
    }

    public function testFactoryMakeHtmlGenerator() {
        $Factory = new HfFactory();
        $HtmlGenerator = $Factory->makeHtmlGenerator();

        $this->assertTrue(is_a($HtmlGenerator, 'HfHtmlGenerator'));
    }

    public function testFactoryMakeDatabase() {
        $Factory = new HfFactory();
        $Database = $Factory->makeDatabase();

        $this->assertTrue(is_a($Database, 'HfDatabase'));
    }

    public function testFactoryMakePhpLibrary() {
        $Factory = new HfFactory();
        $PhpLibrary = $Factory->makeCodeLibrary();

        $this->assertTrue(is_a($PhpLibrary, 'HfPhpLibrary'));
    }

    public function testFactoryMakeWordPressInterface() {
        $Factory = new HfFactory();
        $WordPressInterface = $Factory->makeContentManagementSystem();

        $this->assertTrue(is_a($WordPressInterface, 'HfWordPressInterface'));
    }

    public function testFactoryMakeSecurity() {
        $Factory = new HfFactory();
        $Security = $Factory->makeSecurity();

        $this->assertTrue(is_a($Security, 'HfSecurity'));
    }

    public function testFactoryMakeUrlGenerator() {
        $Factory = new HfFactory();
        $UrlGenerator = $Factory->makeUrlGenerator();

        $this->assertTrue(is_a($UrlGenerator, 'HfUrlGenerator'));
    }

    public function testFactoryMakeSettingsShortcode() {
        $Factory = new HfFactory();
        $SettingsShortcode = $Factory->makeSettingsShortcode();

        $this->assertTrue(is_a($SettingsShortcode, 'HfSettingsShortcode'));
    }

    public function testSettingsShortcodeOutputsAnything() {
        $Factory            = new HfFactory();
        $SettingsShortcode  = $Factory->makeSettingsShortcode();
        $output             = $SettingsShortcode->getOutput();

        $this->assertTrue(strlen($output) > 0);
    }

    public function testGoalsShortcodeClassExists() {
        $this->assertTrue(class_exists('HfGoalsShortcode'));
    }

    public function testGoalsShortcodeOutputsAnything() {
        $Factory            = new HfFactory();
        $GoalsShortcode     = $Factory->makeGoalsShortcode();
        $output             = $GoalsShortcode->getOutput();

        $this->assertTrue(strlen($output) > 0);
    }

    public function testFormAbstractClassExists() {
        $this->assertTrue(class_exists('HfForm'));
    }

    public function testHfAccountabilityFormClassExists() {
        $this->assertTrue(class_exists('HfAccountabilityForm'));
    }

    public function testHfAccountabilityFormClassHasPopulateMethod() {
        Mock::generate('HfGoals');
        $Goals = new MockHfGoals();
        $AccountabilityForm = new HfAccountabilityForm('test.com', $Goals);
        $this->assertTrue(method_exists ( $AccountabilityForm, 'populate' ));
    }

    public function testGetGoalSubscriptions() {
        list($Cms, $CodeLibrary) = $this->makeDatabaseMockDependencies();

        $Cms->expectOnce('getRows');

        $Database       = new HfDatabase($Cms, $CodeLibrary);

        $Database->getGoalSubscriptions(1);
    }

    public function testSendEmailReportRequests() {
        $Factory = new HfFactory();
        $Goals = $Factory->makeGoals();
        $Goals->sendReportRequestEmails();
    }

    public function testRegisterShortcodeExists() {
        $this->assertTrue(class_exists('HfRegisterShortcode'));
    }

    public function testRegisterShortcodeUsesShortcodeInterface() {
        $this->assertTrue($this->classImplementsInterface('HfRegisterShortcode', 'Hf_iShortcode'));
    }

    public function testCmsHasDeleteRowsFunction() {
        $Cms = new HfWordPressInterface();

        $this->assertTrue(method_exists($Cms, 'deleteRows'));
    }

    public function testDatabaseHasDeleteInvitationMethod() {
        list($Cms, $CodeLibrary) = $this->makeDatabaseMockDependencies();

        $Database = new HfDatabase($Cms, $CodeLibrary);

        $this->assertTrue(method_exists($Database, 'deleteInvite'));
    }

    public function testDatabaseCallsDeleteRowsMethod() {
        list($Cms, $CodeLibrary) = $this->makeDatabaseMockDependencies();

        $Database = new HfDatabase($Cms, $CodeLibrary);

        $Cms->expectOnce('deleteRows');

        $Database->deleteInvite(777);
    }

    public function testRegisterShortcodeCallsDeleteInvitation() {
        list($UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager) = $this->makeRegisterShortcodeMockDependencies();

        $PhpLibrary->returns('isPostEmpty', false);
        $PhpLibrary->returns('isUrlParameterEmpty', false);
        $PhpLibrary->returns('getPost', 'test@gmail.com');

        $mockInvite             = new stdClass();
        $mockInvite->inviterID  = 777;

        $Database->returns('getInvite', $mockInvite);

        $Database->expectOnce('deleteInvite');

        $RegisterShortcode = new HfRegisterShortcode($UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager);

        $RegisterShortcode->getOutput();
    }

    public function testIsEmailTakenMethodExists() {
        $Factory    = new HfFactory();
        $Cms        = $Factory->makeContentManagementSystem();

        $this->assertTrue(method_exists($Cms, 'isEmailTaken'));
    }

    public function testRegisterShortcodeRejectsTakenEmails() {
        list($UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager) = $this->makeRegisterShortcodeMockDependencies();

        $RegisterShortcode = new HfRegisterShortcode($UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager);

        $PhpLibrary->returns('isPostEmpty', false);
        $PhpLibrary->returns('isUrlParameterEmpty', false);
        $PhpLibrary->returns('getPost', 'test@gmail.com');

        $Cms->returns('isEmailTaken', true);

        $mockInvite             = new stdClass();
        $mockInvite->inviterID  = 777;

        $Database->returns('getInvite', $mockInvite);

        $Cms->expectAtLeastOnce('isEmailTaken');
        $Cms->expectNever('createUser');

        $output = $RegisterShortcode->getOutput();

        $this->assertTrue(strstr($output, "<p class='fail'>Oops. That email is already in use.</p>"));
    }
}

?>