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
    	$this->assertEqual(class_exists("HfAccountability"), True);
    }
    
    public function testMainObjectExistence() {
    	global $HfMain;
    	$this->assertEqual(is_object($HfMain), True);
    }
    
    public function testMainObjectType() {
    	global $HfMain;
    	$this->assertEqual($HfMain instanceof HfAccountability, True);
    }
    
	public function testGettingCurrentUserLogin() {
		$user = wp_get_current_user();
		$DbConnection = new HfDbConnection();
        $HtmlGenerator = new HfHtmlGenerator();
		$UserManager = new HfUserManager($DbConnection, $HtmlGenerator);
		$this->assertEqual($UserManager->getCurrentUserLogin(), $user->user_login);
	}
    
    public function testShortcodeRegistration() {
    	$this->assertEqual(shortcode_exists('hfSettings'), true);
    }
    
    public function testDbDataNullRemoval() {
    	$DbManager = new HfDbConnection();
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
  		$DbManager = new HfDbConnection();
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
		$DbManager = new HfDbConnection();
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
		$DbManager = new HfDbConnection();
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
		$DbManager = new HfDbConnection();
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
		$DbManager = new HfDbConnection();
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
		$DbManager = new HfDbConnection();
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
		$DbManager = new HfDbConnection();
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
        Mock::generate('HfUrlGenerator');
        Mock::generate('HfUserManager');
        Mock::generate('HfSecurity');
        Mock::generate('HfDbConnection');
        Mock::generate('HfWordPressInterface');

        $UrlFinder      = new MockHfUrlFinder();
        $UrlGenerator   = new MockHfUrlGenerator();
        $UserManager    = new MockHfUserManager();
        $Security       = new MockHfSecurity();
        $DbConnection   = new MockHfDbConnection();
        $WordPressApi   = new MockHfWordPressInterface();

        $Security->returns('createRandomString', 555);
        $DbConnection->returns('getVar', 5);

        $Mailer = new HfMailer($UrlFinder, $UrlGenerator, $UserManager, $Security, $DbConnection, $WordPressApi);
        $result = $Mailer->sendInvitation(1, 'me@test.com', 3);

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
        Mock::generate('HfUrlGenerator');
        Mock::generate('HfUserManager');
        Mock::generate('HfSecurity');
        Mock::generate('HfDbConnection');
        Mock::generate('HfWordPressInterface');

        $UrlFinder      = new MockHfUrlFinder();
        $UrlGenerator   = new MockHfUrlGenerator();
        $UserManager    = new MockHfUserManager();
        $Security       = new MockHfSecurity();
        $DbConnection   = new MockHfDbConnection();
        $WordPressApi   = new MockHfWordPressInterface();

        $Security->returns('createRandomString', 555);
        $DbConnection->returns('getVar', 5);
        $WordPressApi->returns('sendWpEmail', true);

        $Mailer = new HfMailer($UrlFinder, $UrlGenerator, $UserManager, $Security, $DbConnection, $WordPressApi);
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

        $Mailer->sendInvitation(1, 'me@test.com', 3);
    }

    public function testSendEmailByUserID() {
        Mock::generate('HfUrlFinder');
        Mock::generate('HfUrlGenerator');
        Mock::generate('HfUserManager');
        Mock::generate('HfSecurity');
        Mock::generate('HfDbConnection');
        Mock::generate('HfWordPressInterface');

        $UrlFinder      = new MockHfUrlFinder();
        $UrlGenerator   = new MockHfUrlGenerator();
        $UserManager    = new MockHfUserManager();
        $Security       = new MockHfSecurity();
        $DbConnection   = new MockHfDbConnection();
        $WordPressApi   = new MockHfWordPressInterface();

        $WordPressApi->returns('sendWpEmail', true);
        $DbConnection->returns('getVar', 5);

        $Mailer = new HfMailer($UrlFinder, $UrlGenerator, $UserManager, $Security, $DbConnection, $WordPressApi);
        $result = $Mailer->sendEmailToUser(1, 'test', 'test');

        $this->assertEqual($result, 5);
    }

    public function testSendEmailToUserAndSpecifyEmailID() {
        Mock::generate('HfUrlFinder');
        Mock::generate('HfUrlGenerator');
        Mock::generate('HfUserManager');
        Mock::generate('HfSecurity');
        Mock::generate('HfDbConnection');
        Mock::generate('HfWordPressInterface');

        $UrlFinder      = new MockHfUrlFinder();
        $UrlGenerator   = new MockHfUrlGenerator();
        $UserManager    = new MockHfUserManager();
        $Security       = new MockHfSecurity();
        $DbConnection   = new MockHfDbConnection();
        $WordPressApi   = new MockHfWordPressInterface();

        $Mailer = new HfMailer($UrlFinder, $UrlGenerator, $UserManager, $Security, $DbConnection, $WordPressApi);

        $userID     = 1;
        $subject    = 'test subject';
        $body       = 'test body';
        $emailID    = 123;

        $WordPressApi->returns('sendWpEmail', true);
        $WordPressApi->returns('getUserEmail', 'me@test.com');

        $expectedRecord = array(
            'subject'   => $subject,
            'body'      => $body,
            'userID'    => $userID,
            'emailID'   => $emailID,
            'address'   => 'me@test.com'
        );

        $DbConnection->expectOnce(
            'insertIntoDb',
            array('hf_email', $expectedRecord ));

        $Mailer->sendEmailToUserAndSpecifyEmailID($userID, $subject, $body, $emailID);
    }
}

?>