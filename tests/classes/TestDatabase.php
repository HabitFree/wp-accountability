<?php

require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestDatabase extends HfTestCase {
    // Helper Functions

    // Tests

    public function testDbDataNullRemoval() {
        $Database = $this->Factory->makeDatabase();

        $data = array(
            'one'   => 'big one',
            'two'   => 'two',
            'three' => null,
            'four'  => 4,
            'five'  => 'null',
            'six'   => 0,
            'seven' => false
        );

        $expectedData = array(
            'one'   => 'big one',
            'two'   => 'two',
            'four'  => 4,
            'five'  => 'null',
            'six'   => 0,
            'seven' => false
        );

        $this->assertEquals( $Database->removeNullValuePairs( $data ), $expectedData );
    }

    public function testSchemaColumnObjectCreation() {
        $Database     = $this->Factory->makeDatabase();
        $columnObject = $Database->createColumnSchemaObject( 'openTime', 'timestamp', 'YES', '', null, '' );

        $expected          = new StdClass;
        $expected->Field   = 'openTime';
        $expected->Type    = 'timestamp';
        $expected->Null    = 'YES';
        $expected->Key     = '';
        $expected->Default = null;
        $expected->Extra   = '';

        $this->assertEquals( $columnObject, $expected );
    }

    public function testEmailTableSchema() {
        $Database      = $this->Factory->makeDatabase();
        $currentSchema = $Database->getTableSchema( 'hf_email' );

        $expectedSchema = array(
            'emailID'        => $Database->createColumnSchemaObject( 'emailID', 'int(11)', 'NO', 'PRI', null, 'auto_increment' ),
            'sendTime'       => $Database->createColumnSchemaObject( 'sendTime', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '' ),
            'subject'        => $Database->createColumnSchemaObject( 'subject', 'varchar(500)', 'NO', '', null, '' ),
            'body'           => $Database->createColumnSchemaObject( 'body', 'text', 'NO', '', null, '' ),
            'userID'         => $Database->createColumnSchemaObject( 'userID', 'int(11)', 'NO', 'MUL', null, '' ),
            'deliveryStatus' => $Database->createColumnSchemaObject( 'deliveryStatus', 'bit(1)', 'NO', '', "b'0'", '' ),
            'openTime'       => $Database->createColumnSchemaObject( 'openTime', 'datetime', 'YES', '', null, '' ),
            'address'        => $Database->createColumnSchemaObject( 'address', 'varchar(80)', 'YES', '', null, '' )
        );

        $this->assertEquals( $currentSchema, $expectedSchema );
    }

    public function testGoalTableSchema() {
        $Database      = $this->Factory->makeDatabase();
        $currentSchema = $Database->getTableSchema( 'hf_goal' );

        $expectedSchema = array(
            'goalID'      => $Database->createColumnSchemaObject( 'goalID', 'int(11)', 'NO', 'PRI', null, 'auto_increment' ),
            'title'       => $Database->createColumnSchemaObject( 'title', 'varchar(500)', 'NO', '', null, '' ),
            'description' => $Database->createColumnSchemaObject( 'description', 'text', 'YES', '', null, '' ),
            'thumbnail'   => $Database->createColumnSchemaObject( 'thumbnail', 'varchar(80)', 'YES', '', null, '' ),
            'isPositive'  => $Database->createColumnSchemaObject( 'isPositive', 'bit(1)', 'NO', '', "b'0'", '' ),
            'isPrivate'   => $Database->createColumnSchemaObject( 'isPrivate', 'bit(1)', 'NO', '', "b'1'", '' ),
            'creatorID'   => $Database->createColumnSchemaObject( 'creatorID', 'int(11)', 'YES', 'MUL', null, '' ),
            'dateCreated' => $Database->createColumnSchemaObject( 'dateCreated', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '' )
        );

        $this->assertEquals( $currentSchema, $expectedSchema );
    }

    public function testReportTableSchema() {
        $Database      = $this->Factory->makeDatabase();
        $currentSchema = $Database->getTableSchema( 'hf_report' );

        $expectedSchema = array(
            'reportID'         => $Database->createColumnSchemaObject( 'reportID', 'int(11)', 'NO', 'PRI', null, 'auto_increment' ),
            'userID'           => $Database->createColumnSchemaObject( 'userID', 'int(11)', 'NO', 'MUL', null, '' ),
            'goalID'           => $Database->createColumnSchemaObject( 'goalID', 'int(11)', 'NO', 'MUL', null, '' ),
            'referringEmailID' => $Database->createColumnSchemaObject( 'referringEmailID', 'int(11)', 'YES', 'MUL', null, '' ),
            'isSuccessful'     => $Database->createColumnSchemaObject( 'isSuccessful', 'tinyint(4)', 'NO', '', null, '' ),
            'date'             => $Database->createColumnSchemaObject( 'date', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '' )
        );

        $this->assertEquals( $currentSchema, $expectedSchema );
    }

    public function testUserGoalTableSchema() {
        $Database      = $this->Factory->makeDatabase();
        $currentSchema = $Database->getTableSchema( 'hf_user_goal' );

        $expectedSchema = array(
            'userID'      => $Database->createColumnSchemaObject( 'userID', 'int(11)', 'NO', 'PRI', null, '' ),
            'goalID'      => $Database->createColumnSchemaObject( 'goalID', 'int(11)', 'NO', 'PRI', null, '' ),
            'dateStarted' => $Database->createColumnSchemaObject( 'dateStarted', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '' ),
            'isActive'    => $Database->createColumnSchemaObject( 'isActive', 'bit(1)', 'NO', '', "b'1'", '' )
        );

        $this->assertEquals( $currentSchema, $expectedSchema );
    }

    public function testLevelTableSchema() {
        $Database      = $this->Factory->makeDatabase();
        $currentSchema = $Database->getTableSchema( 'hf_level' );

        $expectedSchema = array(
            'levelID'       => $Database->createColumnSchemaObject( 'levelID', 'int(11)', 'NO', 'PRI', null, '' ),
            'title'         => $Database->createColumnSchemaObject( 'title', 'varchar(500)', 'NO', '', null, '' ),
            'description'   => $Database->createColumnSchemaObject( 'description', 'text', 'YES', '', null, '' ),
            'size'          => $Database->createColumnSchemaObject( 'size', 'int(11)', 'NO', '', null, '' ),
            'emailInterval' => $Database->createColumnSchemaObject( 'emailInterval', 'int(11)', 'NO', '', null, '' ),
            'target'        => $Database->createColumnSchemaObject( 'target', 'int(11)', 'NO', '', null, '' ),
        );

        $this->assertEquals( $currentSchema, $expectedSchema );
    }

    public function testInviteTableSchema() {
        $Database      = $this->Factory->makeDatabase();
        $currentSchema = $Database->getTableSchema( 'hf_invite' );

        $expectedSchema = array(
            'inviteID'       => $Database->createColumnSchemaObject( 'inviteID', 'varchar(250)', 'NO', 'PRI', null, '' ),
            'inviterID'      => $Database->createColumnSchemaObject( 'inviterID', 'int(11)', 'NO', 'MUL', null, '' ),
            'inviteeEmail'   => $Database->createColumnSchemaObject( 'inviteeEmail', 'varchar(80)', 'NO', '', null, '' ),
            'emailID'        => $Database->createColumnSchemaObject( 'emailID', 'int(11)', 'NO', 'MUL', null, '' ),
            'expirationDate' => $Database->createColumnSchemaObject( 'expirationDate', 'datetime', 'NO', '', null, '' )
        );

        $this->assertEquals( $currentSchema, $expectedSchema );
    }

    public function testRelationshipTableSchema() {
        $Database      = $this->Factory->makeDatabase();
        $currentSchema = $Database->getTableSchema( 'hf_relationship' );

        $expectedSchema = array(
            'userID1' => $Database->createColumnSchemaObject( 'userID1', 'int(11)', 'NO', 'PRI', null, '' ),
            'userID2' => $Database->createColumnSchemaObject( 'userID2', 'int(11)', 'NO', 'PRI', null, '' )
        );

        $this->assertEquals( $currentSchema, $expectedSchema );
    }

    public function testReportRequestTableSchema() {
        $Database      = $this->Factory->makeDatabase();
        $currentSchema = $Database->getTableSchema( 'hf_report_request' );

        $expectedSchema = array(
            'requestID'      => $Database->createColumnSchemaObject( 'requestID', 'varchar(250)', 'NO', 'PRI', null, '' ),
            'userID'         => $Database->createColumnSchemaObject( 'userID', 'int(11)', 'NO', 'MUL', null, '' ),
            'emailID'        => $Database->createColumnSchemaObject( 'emailID', 'int(11)', 'NO', 'MUL', null, '' ),
            'expirationDate' => $Database->createColumnSchemaObject( 'expirationDate', 'datetime', 'NO', '', null, '' )
        );

        $this->assertEquals( $expectedSchema, $currentSchema );
    }

    public function testDaysSinceLastEmail() {
        $this->setReturnValue( $this->MockCms, 'getVar', '2014-05-27 16:04:29' );
        $this->setReturnValue( $this->MockCodeLibrary, 'convertStringToTime', 1401224669.0 );
        $this->setReturnValue( $this->MockCodeLibrary, 'getCurrentTime', 1401483869.0 );

        $result = $this->DatabaseWithMockedDependencies->daysSinceLastEmail( 1 );

        $this->assertEquals( $result, 3 );
    }

    public function testDatabaseHasDeleteInvitationMethod() {
        $this->assertTrue( method_exists( $this->DatabaseWithMockedDependencies, 'deleteInvite' ) );
    }

    public function testDatabaseCallsDeleteRowsMethod() {
        $this->expectOnce( $this->MockCms, 'deleteRows' );
        $this->DatabaseWithMockedDependencies->deleteInvite( 777 );
    }

    public function testGetGoalSubscriptions() {
        $this->expectOnce( $this->MockCms, 'getRows' );
        $this->DatabaseWithMockedDependencies->getGoalSubscriptions( 1 );
    }

    public function testInsertIntoDbCallsCmsInsert() {
        $this->expectOnce( $this->MockCms, 'insertIntoDb', array('wptests_duck', array('bill')) );
        $this->DatabaseWithMockedDependencies->insertIntoDb( 'duck', array('bill') );
    }

    public function testRecordReportRequest() {
        $table     = "hf_report_request";
        $requestId = 555;
        $userId    = 1;
        $emailId   = 7;
        $expirationDate = 'fakeDate';
        $data      = array(
            'requestID' => $requestId,
            'userID'    => $userId,
            'emailID'   => $emailId,
            'expirationDate' => $expirationDate
        );

        $this->expectOnce( $this->MockCms, 'insertIntoDb', array('wptests_' . $table, $data) );

        $this->DatabaseWithMockedDependencies->recordReportRequest( $requestId, $userId, $emailId, $expirationDate );
    }

    public function testIsReportRequestValid() {
        $this->setReturnValue( $this->MockCms, 'getResults', array(new stdClass()) );

        $this->assertTrue( $this->DatabaseWithMockedDependencies->isReportRequestValid( 555 ) );
    }

    public function testIsReportRequestValidReturnsFalse() {
        $this->assertFalse( $this->DatabaseWithMockedDependencies->isReportRequestValid( 555 ) );
    }

    public function testDeleteReportRequest() {
        $this->setReturnValue( $this->MockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->MockCms, 'deleteRows', array('wptests_hf_report_request', array('requestID' => 555)) );
        $this->DatabaseWithMockedDependencies->deleteReportRequest( 555 );
    }

    public function testGetReportRequestUserId() {
        $mockReportRequest         = new stdClass();
        $mockReportRequest->userID = 5;

        $this->setReturnValue( $this->MockCms, 'getRow', $mockReportRequest );

        $actual = $this->DatabaseWithMockedDependencies->getReportRequestUserId( 555 );

        $this->assertEquals( 5, $actual );
    }

    public function testGetReportRequestIdQueryFormat() {
        $mockReportRequest         = new stdClass();
        $mockReportRequest->userID = 5;

        $this->setReturnValue( $this->MockCms, 'getRow', $mockReportRequest );
        $this->expectOnce( $this->MockCms, 'getRow', array('hf_report_request', "requestID = '555'") );

        $this->DatabaseWithMockedDependencies->getReportRequestUserId( 555 );
    }

    public function testUpdateExpirationDate() {
        $data = array(
            'expirationDate' => '2014-06-19 15:58:37'
        );

        $where = array(
            'requestID' => 555
        );

        $this->setReturnValue($this->MockCms, 'getDbPrefix', 'wptests_');
        $this->expectOnce( $this->MockCms, 'updateRowsSafe', array('wptests_hf_report_request', $data, $where) );

        $this->DatabaseWithMockedDependencies->updateReportRequestExpirationDate( 555, 1403211517 );
    }
}