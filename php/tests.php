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
    
    public function testMainClassExistence() {
    	$this->assertEqual(class_exists("HfMain"), True);
    }
    
    public function testMainObjectExistence() {
    	global $HfMain;
    	$this->assertEqual(is_object($HfMain), True);
    }
    
    public function testMainObjectType() {
    	global $HfMain;
    	$this->assertEqual($HfMain instanceof HfMain, True);
    }
    
	public function testGettingCurrentUserLogin() {
        $ApiInterface   = new HfWordPressInterface();
        $user           = wp_get_current_user($ApiInterface);
        $PHPAPI         = new HfPhpInterface();
        $DbConnection   = new HfDatabase($ApiInterface, $PHPAPI);
        $UrlFinder      = new HfUrlFinder();
        $UrlGenerator   = new HfUrlGenerator();
        $Security       = new HfSecurity();
        $Mailer         = new HfMailer($UrlFinder, $UrlGenerator, $Security, $DbConnection, $ApiInterface);
		$UserManager    = new HfUserManager($DbConnection, $Mailer, $UrlFinder, $ApiInterface);

		$this->assertEqual($UserManager->getCurrentUserLogin(), $user->user_login);
	}
    
    public function testShortcodeRegistration() {
    	$this->assertEqual(shortcode_exists('hfSettings'), true);
    }
    
    public function testDbDataNullRemoval() {
        $WebsiteApi         = new HfWordPressInterface();
        $PHPAPI         = new HfPhpInterface();
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
        $PHPAPI         = new HfPhpInterface();
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
        $PHPAPI         = new HfPhpInterface();
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
        $PHPAPI         = new HfPhpInterface();
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
        $PHPAPI         = new HfPhpInterface();
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
        $PHPAPI         = new HfPhpInterface();
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
        $PHPAPI         = new HfPhpInterface();
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
        $PHPAPI         = new HfPhpInterface();
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

        $UrlFinder      = new MockHfUrlFinder();
        $DbConnection   = new MockHfDatabase();
        $HtmlGenerator  = new MockHfHtmlGenerator();
        $Messenger      = new MockHfMailer();
        $WebsiteApi     = new MockHfWordPressInterface();

        $Messenger->returns('generateInviteID', 555);
        $DbConnection->returns('generateEmailID', 5);

        $UserManager = new HfUserManager($DbConnection, $Messenger, $UrlFinder, $WebsiteApi);
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

        $UrlFinder      = new MockHfUrlFinder();
        $DbConnection   = new MockHfDatabase();
        $Messenger      = new MockHfMailer();
        $WebsiteApi     = new MockHfWordPressInterface();

        $DbConnection->returns('generateEmailID', 5);

        $UserManager = new HfUserManager($DbConnection, $Messenger, $UrlFinder, $WebsiteApi);
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

        $Messenger                  = new MockHfMailer();
        $WebsiteApi                 = new MockHfWordPressInterface();
        $HtmlGenerator              = new MockHfHtmlGenerator();
        $DbConnection               = new MockHfDatabase();

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
        $Messenger->returns('notThrottled', true);

        $Messenger->expectAtLeastOnce('notThrottled');

        $Goals = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection);
        $Goals->sendReportRequestEmails();
    }

    public function testDaysSinceLastEmail() {
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfPhpInterface');

        $WebsiteAPI = new MockHfWordPressInterface();
        $PhpApi     = new MockHfPhpInterface();

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

        $Messenger                  = new MockHfMailer();
        $WebsiteApi                 = new MockHfWordPressInterface();
        $HtmlGenerator              = new MockHfHtmlGenerator();
        $DbConnection               = new MockHfDatabase();

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
        $Messenger->returns('notThrottled', true);

        $Messenger->expectAtLeastOnce('sendReportRequestEmail');

        $Goals = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection);
        $Goals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenReportNotDue() {
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfDatabase');

        $Messenger                  = new MockHfMailer();
        $WebsiteApi                 = new MockHfWordPressInterface();
        $HtmlGenerator              = new MockHfHtmlGenerator();
        $DbConnection               = new MockHfDatabase();

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

        $Goals = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection);
        $Goals->sendReportRequestEmails();
    }

    public function testNotThrottledReturnsTrue() {
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
        $result         = $Mailer->notThrottled(1);

        $this->assertEqual($result, true);
    }

    public function testNotThrottledReturnsFalse() {
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
        $result         = $Mailer->notThrottled(1);

        $this->assertEqual($result, false);
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenUserThrottled() {
        Mock::generate('HfMailer');
        Mock::generate('HfWordPressInterface');
        Mock::generate('HfHtmlGenerator');
        Mock::generate('HfDatabase');

        $Messenger                  = new MockHfMailer();
        $WebsiteApi                 = new MockHfWordPressInterface();
        $HtmlGenerator              = new MockHfHtmlGenerator();
        $DbConnection               = new MockHfDatabase();

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
        $Messenger->returns('notThrottled', false);

        $Messenger->expectNever('sendReportRequestEmail');

        $Goals = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection);
        $Goals->sendReportRequestEmails();
    }
}

?>