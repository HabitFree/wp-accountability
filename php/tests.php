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
		$UserManager = new HfUserManager();
		$this->assertEqual($UserManager->getCurrentUserLogin(), $user->user_login);
	}
    
    public function testShortcodeRegistration() {
    	$this->assertEqual(shortcode_exists('hfSettings'), true);
    }
    
    public function testDbDataNullRemoval() {
    	$DbManager = new HfDbManager();
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
    
	/*
    public function testTableEmpty() {
    	$DbManager = new HfDbManager();
		$backup = $DbManager->getTable('hf_email');
		
		$DbManager->emptyTable('hf_email');
		
		$rows = $DbManager->getTable('hf_email');
		$rowCount = count($rows);
		
		$DbManager->insertMultipleRows('hf_email', $backup);
		
    	$this->assertEqual($rowCount, 0);
    }
	
	public function testTableBackupAndRestore() {
		$DbManager = new HfDbManager();
		$backup = $DbManager->getRows('hf_email', null, ARRAY_A);
		
		$DbManager->emptyTable('hf_email');
		
		$DbManager->insertMultipleRows('hf_email', $backup);
		
    	$this->assertEqual($DbManager->getRows('hf_email', null, ARRAY_A), $backup);
	}
	*/

	public function testSchemaColumnObjectCreation() {
  		$DbManager = new HfDbManager();
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
		$DbManager = new HfDbManager();
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
		$DbManager = new HfDbManager();
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
		$DbManager = new HfDbManager();
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
		$DbManager = new HfDbManager();
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
		$DbManager = new HfDbManager();
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
		$DbManager = new HfDbManager();
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
		$HfMain = new HfAccountability();
		$randomString = $HfMain->createRandomString(400);
		$this->assertEqual(strlen($randomString), 400);
	}
	
	public function testInviteEmailSending() {
		$HfMain				= new HfAccountability();
		$Mailer				= new HfMailer();
		$DbManager			= new HfDbManager();
		
		$table				= 'hf_email';
		$rows 				= $DbManager->getTable($table);
		$startRowCount 		= count($rows);

		$inviterID			= 1;
		$inviter			= get_userdata( $inviterID );
		$inviterName		= $inviter->user_login;
		$destinationEmail	= 'hftest@mailinator.com';
		$daysToExpire		= 10;
		$invitationID		= $Mailer->sendInvitation($inviterID, $destinationEmail, $daysToExpire);
		$invitationURL		= $HfMain->getURLByTitle('Register') . '?n=' . $invitationID;
		
		global $wpdb;
		$prefix				= $wpdb->prefix;
		$tableName			= $prefix . $table;
		$email				= $DbManager->getRow($table, 'emailID=( SELECT max(emailID) FROM '.$tableName.' )');
		
		$DbManager->deleteRow($table, 'emailID='.$email->emailID);
		$DbManager->deleteRow('hf_invite', 'emailID='.$email->emailID);
		$rows  				= $DbManager->getTable($table);
		$endRowCount 		= count($rows);
		
		$this->assertEqual($email->sendTime, date('Y-m-d H:i:s'));
		$this->assertEqual($email->subject, ucwords($inviterName) . ' just invited you to join them at HabitFree!');
		$this->assertEqual($email->body,
			"<p>HabitFree is a community of young people striving for God's ideal of purity and Christian freedom.</p><p><a href='" . $invitationURL . "'>Click here to join " . ucwords($inviterName) . " in his quest!</a></p>");
		$this->assertEqual($email->userID, 0);
		$this->assertEqual($email->address, $destinationEmail);
		$this->assertEqual($startRowCount, $endRowCount);
	}
	
	public function testPHPandMySQLtimezonesMatch() {
		$phpTime	= date('Y-m-d H:i:s');
		global $wpdb;
		$mysqlTime	= $wpdb->get_results("SELECT NOW()", ARRAY_A);
		$this->assertEqual($phpTime, $mysqlTime[0]['NOW()']);
	}
	
	public function testInviteStorageInInviteTable() {
		$Mailer				= new HfMailer();
		$DbManager			= new HfDbManager();
		
		$table				= 'hf_invite';
		$startInviteRowCount= $DbManager->countRowsInTable($table);
		$startEmailRowCount	= $DbManager->countRowsInTable('hf_email');
		
		$inviterID			= 1;
		$destinationEmail	= 'hftest@mailinator.com';
		$daysToExpire		= 10;
		$inviteID			= $Mailer->sendInvitation($inviterID, $destinationEmail, $daysToExpire);
		
		global $wpdb;
		$prefix				= $wpdb->prefix;
		$tableName			= $prefix . $table;
		$invite				= $DbManager->getRow($table, 'emailID=( SELECT max(emailID) FROM '.$tableName.' )');
		$inviteTable		= $DbManager->getFullTableName('hf_invite');
		$emailID			= $DbManager->getVar('hf_invite', 'emailID', 'inviteID="'.$inviteID.'"');
		
		$DbManager->deleteRow($table, 'inviteID="'.$inviteID.'"');
		$DbManager->deleteRow('hf_email', 'emailID='.$emailID);
		$endInviteRowCount 	= $DbManager->countRowsInTable($table);
		$endEmailRowCount	= $DbManager->countRowsInTable('hf_email');
		
		$this->assertEqual($invite->inviteID, $inviteID);
		$this->assertEqual($invite->inviterID, $inviterID);
		$this->assertEqual($invite->inviteeEmail, $destinationEmail);
		$this->assertEqual($invite->expirationDate, date('Y-m-d H:i:s', strtotime('+'.$daysToExpire.' days')));
		$this->assertEqual($startInviteRowCount, $endInviteRowCount);
		$this->assertEqual($startEmailRowCount, $endEmailRowCount);
	}

	public function testTest() {
		$UserManager = new HfUserManager();
		$Mailer = new HfMailer();
		var_dump($UserManager->isAnyGoalDue(1));
		$Mailer->sendReportRequestEmails();
		
	}
}

?>