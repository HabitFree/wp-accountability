<?php

require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestDatabase extends HfTestCase {
    // Helper Functions

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
        $columnObject = $this->createColumnSchemaObject( 'openTime', 'timestamp', 'YES', '', null, '' );

        $expected          = new StdClass;
        $expected->Field   = 'openTime';
        $expected->Type    = 'timestamp';
        $expected->Null    = 'YES';
        $expected->Key     = '';
        $expected->Default = null;
        $expected->Extra   = '';

        $this->assertEquals( $columnObject, $expected );
    }

    private function createColumnSchemaObject( $field, $type, $null, $key, $default, $extra ) {
        $column          = new StdClass;
        $column->Field   = $field;
        $column->Type    = $type;
        $column->Null    = $null;
        $column->Key     = $key;
        $column->Default = $default;
        $column->Extra   = $extra;

        return $column;
    }

    // Tests

    public function testEmailTableSchema() {
        $expectedSchema = array(
            'emailID'        => $this->createColumnSchemaObject( 'emailID', 'int(11)', 'NO', 'PRI', null, 'auto_increment' ),
            'sendTime'       => $this->createColumnSchemaObject( 'sendTime', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '' ),
            'subject'        => $this->createColumnSchemaObject( 'subject', 'varchar(500)', 'NO', '', null, '' ),
            'body'           => $this->createColumnSchemaObject( 'body', 'text', 'NO', '', null, '' ),
            'userID'         => $this->createColumnSchemaObject( 'userID', 'int(11)', 'NO', 'MUL', null, '' ),
            'deliveryStatus' => $this->createColumnSchemaObject( 'deliveryStatus', 'bit(1)', 'NO', '', "b'0'", '' ),
            'openTime'       => $this->createColumnSchemaObject( 'openTime', 'datetime', 'YES', '', null, '' ),
            'address'        => $this->createColumnSchemaObject( 'address', 'varchar(80)', 'YES', '', null, '' )
        );

        $this->assertTableImplementsSchema( $expectedSchema, 'hf_email' );
    }

    private function assertTableImplementsSchema( $expectedSchema, $table ) {
        $currentSchema = $this->getTableSchema( $table );
        $this->assertEquals( $expectedSchema, $currentSchema );
    }

    private function getTableSchema( $table ) {
        global $wpdb;
        $prefix    = $wpdb->prefix;
        $tableName = $prefix . $table;

        return $wpdb->get_results( 'SHOW COLUMNS FROM ' . $tableName, OBJECT_K );
    }

    public function testGoalTableSchema() {
        $expectedSchema = array(
            'goalID'      => $this->createColumnSchemaObject( 'goalID', 'int(11)', 'NO', 'PRI', null, 'auto_increment' ),
            'title'       => $this->createColumnSchemaObject( 'title', 'varchar(500)', 'NO', '', null, '' ),
            'description' => $this->createColumnSchemaObject( 'description', 'text', 'YES', '', null, '' ),
            'thumbnail'   => $this->createColumnSchemaObject( 'thumbnail', 'varchar(80)', 'YES', '', null, '' ),
            'isPositive'  => $this->createColumnSchemaObject( 'isPositive', 'bit(1)', 'NO', '', "b'0'", '' ),
            'isPrivate'   => $this->createColumnSchemaObject( 'isPrivate', 'bit(1)', 'NO', '', "b'1'", '' ),
            'creatorID'   => $this->createColumnSchemaObject( 'creatorID', 'int(11)', 'YES', 'MUL', null, '' ),
            'dateCreated' => $this->createColumnSchemaObject( 'dateCreated', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '' )
        );

        $this->assertTableImplementsSchema( $expectedSchema, 'hf_goal' );
    }

    public function testReportTableSchema() {
        $expectedSchema = array(
            'reportID'         => $this->createColumnSchemaObject( 'reportID', 'int(11)', 'NO', 'PRI', null, 'auto_increment' ),
            'userID'           => $this->createColumnSchemaObject( 'userID', 'int(11)', 'NO', 'MUL', null, '' ),
            'goalID'           => $this->createColumnSchemaObject( 'goalID', 'int(11)', 'NO', 'MUL', null, '' ),
            'referringEmailID' => $this->createColumnSchemaObject( 'referringEmailID', 'int(11)', 'YES', 'MUL', null, '' ),
            'isSuccessful'     => $this->createColumnSchemaObject( 'isSuccessful', 'tinyint(4)', 'NO', '', null, '' ),
            'date'             => $this->createColumnSchemaObject( 'date', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '' )
        );

        $this->assertTableImplementsSchema( $expectedSchema, 'hf_report' );
    }

    public function testUserGoalTableSchema() {
        $expectedSchema = array(
            'userID'      => $this->createColumnSchemaObject( 'userID', 'int(11)', 'NO', 'PRI', null, '' ),
            'goalID'      => $this->createColumnSchemaObject( 'goalID', 'int(11)', 'NO', 'PRI', null, '' ),
            'dateStarted' => $this->createColumnSchemaObject( 'dateStarted', 'timestamp', 'NO', '', 'CURRENT_TIMESTAMP', '' ),
            'isActive'    => $this->createColumnSchemaObject( 'isActive', 'bit(1)', 'NO', '', "b'1'", '' )
        );

        $this->assertTableImplementsSchema( $expectedSchema, 'hf_user_goal' );
    }

    public function testLevelTableSchema() {
        $expectedSchema = array(
            'levelID'       => $this->createColumnSchemaObject( 'levelID', 'int(11)', 'NO', 'PRI', null, '' ),
            'title'         => $this->createColumnSchemaObject( 'title', 'varchar(500)', 'NO', '', null, '' ),
            'description'   => $this->createColumnSchemaObject( 'description', 'text', 'YES', '', null, '' ),
            'size'          => $this->createColumnSchemaObject( 'size', 'int(11)', 'NO', '', null, '' ),
            'emailInterval' => $this->createColumnSchemaObject( 'emailInterval', 'int(11)', 'NO', '', null, '' ),
            'target'        => $this->createColumnSchemaObject( 'target', 'int(11)', 'NO', '', null, '' ),
        );

        $this->assertTableImplementsSchema( $expectedSchema, 'hf_level' );
    }

    public function testInviteTableSchema() {
        $expectedSchema = array(
            'inviteID'       => $this->createColumnSchemaObject( 'inviteID', 'varchar(250)', 'NO', 'PRI', null, '' ),
            'inviterID'      => $this->createColumnSchemaObject( 'inviterID', 'int(11)', 'NO', 'MUL', null, '' ),
            'inviteeEmail'   => $this->createColumnSchemaObject( 'inviteeEmail', 'varchar(80)', 'NO', '', null, '' ),
            'emailID'        => $this->createColumnSchemaObject( 'emailID', 'int(11)', 'NO', 'MUL', null, '' ),
            'expirationDate' => $this->createColumnSchemaObject( 'expirationDate', 'datetime', 'NO', '', null, '' )
        );

        $this->assertTableImplementsSchema( $expectedSchema, 'hf_invite' );
    }

    public function testRelationshipTableSchema() {
        $expectedSchema = array(
            'userID1' => $this->createColumnSchemaObject( 'userID1', 'int(11)', 'NO', 'PRI', null, '' ),
            'userID2' => $this->createColumnSchemaObject( 'userID2', 'int(11)', 'NO', 'PRI', null, '' )
        );

        $this->assertTableImplementsSchema( $expectedSchema, 'hf_relationship' );
    }

    public function testReportRequestTableSchema() {
        $expectedSchema = array(
            'requestID'      => $this->createColumnSchemaObject( 'requestID', 'varchar(250)', 'NO', 'PRI', null, '' ),
            'userID'         => $this->createColumnSchemaObject( 'userID', 'int(11)', 'NO', 'MUL', null, '' ),
            'emailID'        => $this->createColumnSchemaObject( 'emailID', 'int(11)', 'NO', 'MUL', null, '' ),
            'expirationDate' => $this->createColumnSchemaObject( 'expirationDate', 'datetime', 'NO', '', null, '' )
        );

        $this->assertTableImplementsSchema( $expectedSchema, 'hf_report_request' );
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
        $table          = "hf_report_request";
        $requestId      = 555;
        $userId         = 1;
        $emailId        = 7;
        $expirationDate = 'fakeDate';
        $data           = array(
            'requestID'      => $requestId,
            'userID'         => $userId,
            'emailID'        => $emailId,
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

        $this->setReturnValue( $this->MockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->MockCms, 'updateRowsSafe', array('wptests_hf_report_request', $data, $where) );

        $this->DatabaseWithMockedDependencies->updateReportRequestExpirationDate( 555, 1403211517 );
    }

    public function testGetAllInvitesGetsInvites() {
        $this->expectOnce( $this->MockCms, 'getRows', array('hf_invite', null) );
        $this->DatabaseWithMockedDependencies->getAllInvites();
    }

    public function testGetAllInvitesReturnsInvites() {
        $this->setReturnValue( $this->MockCms, 'getRows', 'duck' );
        $this->assertEquals( $this->DatabaseWithMockedDependencies->getAllInvites(), 'duck' );
    }

    public function testGetAllReportRequestsGetsReportRequests() {
        $this->expectOnce( $this->MockCms, 'getRows', array('hf_report_request', null) );
        $this->DatabaseWithMockedDependencies->getAllReportRequests();
    }

    public function testGetAllReportRequestsReturnsReportRequests() {
        $this->setReturnValue( $this->MockCms, 'getRows', 'duck' );
        $this->assertEquals( $this->DatabaseWithMockedDependencies->getAllReportRequests(), 'duck' );
    }

    public function testGetQuotationsGetsQuotations() {
        $expectedQuery = "SELECT * FROM wptest_posts INNER JOIN wptest_term_relationships WHERE post_type =  'hf_quotation' AND post_status =  'publish' AND object_id = id AND term_taxonomy_id = 1";
        $this->expectOnce( $this->MockCms, 'getResults', array($expectedQuery) );
        $this->setReturnValue( $this->MockCms, 'getDbPrefix', 'wptest_' );
        $this->setReturnValue( $this->MockCms, 'getVar', 1 );
        $this->DatabaseWithMockedDependencies->getQuotations( 'For Setback' );
    }

    public function testGetQuotationsReturnsQuotations() {
        $this->setReturnValue( $this->MockCms, 'getResults', 'duck' );

        $actual   = $this->DatabaseWithMockedDependencies->getQuotations( 'For Setback' );
        $expected = 'duck';

        $this->assertEquals( $expected, $actual );
    }

    public function testGetQuotationsLooksUpTermId() {
        $expectedQuery = "SELECT term_id FROM wptest_terms WHERE name = 'For Setback'";
        $this->expectOnce( $this->MockCms, 'getVar', array($expectedQuery) );
        $this->setReturnValue( $this->MockCms, 'getDbPrefix', 'wptest_' );

        $this->DatabaseWithMockedDependencies->getQuotations( 'For Setback' );
    }

    public function testGetQuotationsLooksUpPassedTermName() {
        $expectedQuery = "SELECT term_id FROM wptest_terms WHERE name = 'For Success'";
        $this->expectOnce( $this->MockCms, 'getVar', array($expectedQuery) );
        $this->setReturnValue( $this->MockCms, 'getDbPrefix', 'wptest_' );

        $this->DatabaseWithMockedDependencies->getQuotations( 'For Success' );
    }

    public function testDeleteRelationshipDeletesRelationship() {
        $table = 'wptest_hf_relationship';
        $where = array(
            'userID1' => 4,
            'userID2' => 5
        );

        $this->setReturnValue( $this->MockCms, 'getDbPrefix', 'wptest_' );
        $this->expectOnce( $this->MockCms, 'deleteRows', array($table, $where) );

        $this->DatabaseWithMockedDependencies->deleteRelationship( 4, 5 );
    }

    public function testDeleteRelationshipSortsIds() {
        $table = 'wptest_hf_relationship';
        $where = array(
            'userID1' => 4,
            'userID2' => 5
        );

        $this->setReturnValue( $this->MockCms, 'getDbPrefix', 'wptest_' );
        $this->expectOnce( $this->MockCms, 'deleteRows', array($table, $where) );

        $this->DatabaseWithMockedDependencies->deleteRelationship( 5, 4 );
    }

    public function testGenerateEmailId() {
        $this->expectOnce( $this->MockCms, 'getVar', array("SELECT max(emailID) FROM 'wptest_hf_email'") );
        $this->setReturnValue( $this->MockCms, 'prepareQuery', "SELECT max(emailID) FROM 'wptest_hf_email'" );
        $this->DatabaseWithMockedDependencies->generateEmailId();
    }

    public function testGenerateEmailIdPreparesQuery() {
        $this->expectOnce( $this->MockCms, 'prepareQuery' );
        $this->DatabaseWithMockedDependencies->generateEmailId();
    }

    public function testGenerateEmailIdPreparesQueryProperly() {
        $this->expectOnce( $this->MockCms, 'prepareQuery', array("SELECT max(emailID) FROM %s", array('wptest_hf_email')) );
        $this->setReturnValue( $this->MockCms, 'getDbPrefix', 'wptest_' );
        $this->DatabaseWithMockedDependencies->generateEmailId();
    }

    public function testDaysSinceSecondToLastEmailPreparesQuery() {
        $this->setReturnValue( $this->MockCms, 'getDbPrefix', 'wptest_' );

        $this->expectOnce( $this->MockCms, 'prepareQuery', array(
            'SELECT sendTime FROM (SELECT * FROM %s WHERE userID = %d ORDER BY emailID DESC LIMIT 2) AS T ORDER BY emailID LIMIT 1',
            array('wptest_hf_email', 1)
        ) );

        $this->DatabaseWithMockedDependencies->daysSinceSecondToLastEmail( 1 );
    }

    public function testDaysSinceSecondToLastEmailUsesPreparedQuery() {
        $this->setReturnValue( $this->MockCms, 'prepareQuery', 'duck' );

        $this->expectOnce( $this->MockCms, 'getVar', array('duck') );

        $this->DatabaseWithMockedDependencies->daysSinceSecondToLastEmail( 1 );
    }

    public function testDatabaseUsesCmsToGetOptionWhenInstallingDatabase() {
        $this->expectOnce($this->MockCms, 'getOption', array('hfDbVersion'));

        $this->DatabaseWithMockedDependencies->installDb();
    }

    public function testDatabaseUsesCmsReplaceRowWhenInstallingDatabase() {
        $defaultLevel0 = array(
            'levelID'       => 0,
            'title'         => 'Hibernation',
            'size'          => 0,
            'emailInterval' => 0,
            'target'        => 0
        );

        $defaultLevel1 = array(
            'levelID'       => 1,
            'title'         => 'Dawn',
            'size'          => 2,
            'emailInterval' => 1,
            'target'        => 14
        );

        $defaultLevel2 = array(
            'levelID'       => 2,
            'title'         => 'Breach',
            'size'          => 5,
            'emailInterval' => 7,
            'target'        => 30
        );

        $defaultLevel3 = array(
            'levelID'       => 3,
            'title'         => 'Progress',
            'size'          => 10,
            'emailInterval' => 14,
            'target'        => 90
        );

        $defaultLevel4 = array(
            'levelID'       => 4,
            'title'         => 'Conquest',
            'size'          => 15,
            'emailInterval' => 30,
            'target'        => 365
        );

        $defaultLevel5 = array(
            'levelID'       => 5,
            'title'         => 'Conquering',
            'size'          => 30,
            'emailInterval' => 90,
            'target'        => 1095 // 3 years
        );

        $defaultLevel6 = array(
            'levelID'       => 6,
            'title'         => 'Triumph',
            'size'          => 60,
            'emailInterval' => 365,
            'target'        => 1095 // 3 years
        );

        $defaultLevel7 = array(
            'levelID'       => 7,
            'title'         => 'Vigilance',
            'size'          => 0,
            'emailInterval' => 365,
            'target'        => 0
        );

        $levels = array(
            $defaultLevel0,
            $defaultLevel1,
            $defaultLevel2,
            $defaultLevel3,
            $defaultLevel4,
            $defaultLevel5,
            $defaultLevel6,
            $defaultLevel7,
        );

        $levelFormat = array(
            '%d',
            '%s',
            '%d',
            '%d',
            '%d'
        );

        foreach($levels as $index=>$level) {
            $this->expectAt($this->MockCms, 'replaceRow', $index + 1, array(
                'wptest_hf_level',
                $level,
                $levelFormat
            ));
        }

        $this->DatabaseWithMockedDependencies->installDb();
    }
}