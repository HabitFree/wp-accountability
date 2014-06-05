<?php

require_once(dirname(__FILE__) . '/../hf-accountability.php');

class UnitWpSimpleTest extends UnitTestCase {
    private $functionsFacade;

    public function __construct() {
    }

    public function setUp() {
    }

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
        $WebsiteApi         = new HfWordPressInterface();
        $PHPAPI         = new HfPhpLibrary();
        $DbManager = new HfDatabase($WebsiteApi, $PHPAPI);
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

    	$this->assertEqual($DbManager->removeNullValuePairs($data), $expectedData);
    }

	public function testSchemaColumnObjectCreation() {
        $ApiInterface   = new HfWordPressInterface();
        $PHPAPI         = new HfPhpLibrary();
        $DbManager = new HfDatabase($ApiInterface, $PHPAPI);
		$columnObject = $DbManager->createColumnSchemaObject('openTime', 'timestamp', 'YES', '', null, '');

		$expected = new StdClass;
		$expected->Field = 'openTime';
		$expected->Type = 'timestamp';
		$expected->Null = 'YES';
		$expected->Key = '';
		$expected->Default = null;
		$expected->Extra = '';

		$this->assertEqual($columnObject, $expected);
	}

	public function testEmailTableSchema() {
        $WebsiteApi         = new HfWordPressInterface();
        $PHPAPI         = new HfPhpLibrary();
        $DbManager = new HfDatabase($WebsiteApi, $PHPAPI);
		$currentSchema = $DbManager->getTableSchema('hf_email');

		$expectedSchema = array(
				'emailID'		=> $DbManager->createColumnSchemaObject('emailID', 'int(11)', 'NO', 'PRI', null, 'auto_increment'),
				'sendTime'		=> $DbManager->createColumnSchemaObject('sendTime', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', ''),
				'subject'		=> $DbManager->createColumnSchemaObject('subject', 'varchar(500)', 'NO', '', null, ''),
				'body'			=> $DbManager->createColumnSchemaObject('body', 'text', 'NO', '', null, ''),
				'userID'		=> $DbManager->createColumnSchemaObject('userID', 'int(11)', 'NO', 'MUL', null, ''),
				'deliveryStatus'=> $DbManager->createColumnSchemaObject('deliveryStatus', 'bit(1)', 'NO', '', "b'0'", ''),
				'openTime'		=> $DbManager->createColumnSchemaObject('openTime', 'datetime', 'YES', '', null, ''),
				'address'		=> $DbManager->createColumnSchemaObject('address', 'varchar(80)', 'YES', '', null, '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testGoalTableSchema() {
        $ApiInterface   = new HfWordPressInterface();
        $PHPAPI         = new HfPhpLibrary();
        $DbManager = new HfDatabase($ApiInterface, $PHPAPI);
		$currentSchema = $DbManager->getTableSchema('hf_goal');

		$expectedSchema = array(
				'goalID'		=> $DbManager->createColumnSchemaObject('goalID', 'int(11)', 'NO', 'PRI', null, 'auto_increment'),
				'title'			=> $DbManager->createColumnSchemaObject('title', 'varchar(500)', 'NO', '', null, ''),
				'description'	=> $DbManager->createColumnSchemaObject('description', 'text', 'YES', '', null, ''),
				'thumbnail'		=> $DbManager->createColumnSchemaObject('thumbnail', 'varchar(80)', 'YES', '', null, ''),
				'isPositive'	=> $DbManager->createColumnSchemaObject('isPositive', 'bit(1)', 'NO', '', "b'0'", ''),
				'isPrivate'		=> $DbManager->createColumnSchemaObject('isPrivate', 'bit(1)', 'NO', '', "b'1'", ''),
				'creatorID'		=> $DbManager->createColumnSchemaObject('creatorID', 'int(11)', 'YES', 'MUL', null, ''),
				'dateCreated'	=> $DbManager->createColumnSchemaObject('dateCreated', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testReportTableSchema() {
        $ApiInterface   = new HfWordPressInterface();
        $PHPAPI         = new HfPhpLibrary();
        $DbManager = new HfDatabase($ApiInterface, $PHPAPI);
		$currentSchema = $DbManager->getTableSchema('hf_report');

		$expectedSchema = array(
				'reportID'			=> $DbManager->createColumnSchemaObject('reportID', 'int(11)', 'NO', 'PRI', null, 'auto_increment'),
				'userID'			=> $DbManager->createColumnSchemaObject('userID', 'int(11)', 'NO', 'MUL', null, ''),
				'goalID'			=> $DbManager->createColumnSchemaObject('goalID', 'int(11)', 'NO', 'MUL', null, ''),
				'referringEmailID'	=> $DbManager->createColumnSchemaObject('referringEmailID', 'int(11)', 'YES', 'MUL', null, ''),
				'isSuccessful'		=> $DbManager->createColumnSchemaObject('isSuccessful', 'tinyint(4)', 'NO', '', null, ''),
				'date'				=> $DbManager->createColumnSchemaObject('date', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testUserGoalTableSchema() {
        $ApiInterface   = new HfWordPressInterface();
        $PHPAPI         = new HfPhpLibrary();
        $DbManager = new HfDatabase($ApiInterface, $PHPAPI);
		$currentSchema = $DbManager->getTableSchema('hf_user_goal');

		$expectedSchema = array(
				'userID'		=> $DbManager->createColumnSchemaObject('userID', 'int(11)', 'NO', 'PRI', null, ''),
				'goalID'		=> $DbManager->createColumnSchemaObject('goalID', 'int(11)', 'NO', 'PRI', null, ''),
				'dateStarted'	=> $DbManager->createColumnSchemaObject('dateStarted', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', ''),
				'isActive'		=> $DbManager->createColumnSchemaObject('isActive', 'bit(1)', 'NO', '', "b'1'", '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testLevelTableSchema() {
        $ApiInterface   = new HfWordPressInterface();
        $PHPAPI         = new HfPhpLibrary();
        $DbManager = new HfDatabase($ApiInterface, $PHPAPI);
		$currentSchema = $DbManager->getTableSchema('hf_level');

		$expectedSchema = array(
				'levelID'		=> $DbManager->createColumnSchemaObject('levelID', 'int(11)', 'NO', 'PRI', null, ''),
				'title'			=> $DbManager->createColumnSchemaObject('title', 'varchar(500)', 'NO', '', null, ''),
				'description'	=> $DbManager->createColumnSchemaObject('description', 'text', 'YES', '', null, ''),
				'size'			=> $DbManager->createColumnSchemaObject('size', 'int(11)', 'NO', '', null, ''),
				'emailInterval'	=> $DbManager->createColumnSchemaObject('emailInterval', 'int(11)', 'NO', '', null, ''),
				'target'		=> $DbManager->createColumnSchemaObject('target', 'int(11)', 'NO', '', null, ''),
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

	public function testInviteTableSchema() {
        $ApiInterface   = new HfWordPressInterface();
        $PHPAPI         = new HfPhpLibrary();
        $DbManager = new HfDatabase($ApiInterface, $PHPAPI);
		$currentSchema = $DbManager->getTableSchema('hf_invite');

		$expectedSchema = array(
				'inviteID'		=> $DbManager->createColumnSchemaObject('inviteID', 'varchar(250)', 'NO', 'PRI', null, ''),
				'inviterID'		=> $DbManager->createColumnSchemaObject('inviterID', 'int(11)', 'NO', 'MUL', null, ''),
				'inviteeEmail'	=> $DbManager->createColumnSchemaObject('inviteeEmail', 'varchar(80)', 'NO', '', null, ''),
				'emailID'		=> $DbManager->createColumnSchemaObject('emailID', 'int(11)', 'NO', 'MUL', null, ''),
				'expirationDate'=> $DbManager->createColumnSchemaObject('expirationDate', 'datetime', 'NO', '', null, '')
			);

		$this->assertEqual($currentSchema, $expectedSchema);
	}

    public function testRelationshipTableSchema() {
        $ApiInterface   = new HfWordPressInterface();
        $PHPAPI         = new HfPhpLibrary();
        $DbManager = new HfDatabase($ApiInterface, $PHPAPI);
        $currentSchema = $DbManager->getTableSchema('hf_relationship');

        $expectedSchema = array(
            'userID1'		=> $DbManager->createColumnSchemaObject('userID1', 'int(11)', 'NO', 'PRI', null, ''),
            'userID2'		=> $DbManager->createColumnSchemaObject('userID2', 'int(11)', 'NO', 'PRI', null, '')
        );

        $this->assertEqual($currentSchema, $expectedSchema);
    }

	public function testRandomStringCreationLength() {
        $Security = new HfSecurity();
		$randomString = $Security->createRandomString(400);
		$this->assertEqual(strlen($randomString), 400);
	}

    public function testEmailInviteSendingUsingMocks() {
        Mock::generate('HfUrlFinder');
        Mock::generate('HfDatabase');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfPhpLibrary');

        $UrlFinder      = new MockHfUrlFinder();
        $DbConnection   = new MockHfDatabase();
        $Messenger      = new MockHfMailer();
        $WebsiteApi     = new MockHfWordPressInterface();
        $PhpApi         = new MockHfPhpLibrary();

        $Messenger->returns('generateInviteID', 555);
        $DbConnection->returns('generateEmailID', 5);

        $UserManager = new HfUserManager($DbConnection, $Messenger, $UrlFinder, $WebsiteApi, $PhpApi);
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
        Mock::generate('HfUrlFinder');
        Mock::generate('HfDatabase');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfPhpLibrary');

        $UrlFinder      = new MockHfUrlFinder();
        $DbConnection   = new MockHfDatabase();
        $Messenger      = new MockHfMailer();
        $WebsiteApi     = new MockHfWordPressInterface();
        $PhpApi         = new MockHfPhpLibrary();

        $DbConnection->returns('generateEmailID', 5);

        $UserManager = new HfUserManager($DbConnection, $Messenger, $UrlFinder, $WebsiteApi, $PhpApi);
        $expirationDate = date('Y-m-d H:i:s', strtotime('+'. 3 .' days'));

        $expectedRecord = array(
            'inviteID' => 555,
            'inviterID' => 1,
            'inviteeEmail' => 'me@test.com',
            'emailID' => 5,
            'expirationDate' => $expirationDate
        );

        $DbConnection->expectAt(
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
        Mock::generate('HfUrlFinder');
        Mock::generate('HfDatabase');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfPhpLibrary');

        $UrlFinder      = new MockHfUrlFinder();
        $DbConnection   = new MockHfDatabase();
        $Messenger      = new MockHfMailer();
        $WebsiteApi     = new MockHfWordPressInterface();
        $PhpApi         = new MockHfPhpLibrary();

        $UrlFinder->returns('getCurrentPageURL', 'test.com');

        $UserManager    = new HfUserManager($DbConnection, $Messenger, $UrlFinder, $WebsiteApi, $PhpApi);

        $expectedHtml   = '<form action="test.com" method="post"><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="email"><span class="required">*</span> Email: <input type="text" name="email" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><label for="passwordConfirmation"><span class="required">*</span> Confirm Password: <input type="password" name="passwordConfirmation" required /></label></p><p><input type="submit" name="submit" value="Register" /></p></form>';
        $resultHtml     = $UserManager->registerShortcode();

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
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfPhpLibrary');

        $Cms            = new MockHfWordPressInterface();
        $CodeLibrary    = new MockHfPhpLibrary();

        $Cms->expectOnce('getRows');

        $Database       = new HfDatabase($Cms, $CodeLibrary);

        $Database->getGoalSubscriptions(1);
    }

    public function testSendEmailReportRequests() {
        $Factory = new HfFactory();
        $Goals = $Factory->makeGoals();
        $Goals->sendReportRequestEmails();
    }
}

?>