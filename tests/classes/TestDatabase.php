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

        $this->setReturnValue($this->MockCms, 'getDbPrefix', 'wptests_');
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
        $this->expectOnce( $this->MockCms, 'getResults', array('query') );
        $this->setReturnValue( $this->MockCms, 'getVar', 1 );
        $this->setReturnValue($this->MockCms, 'prepareQuery', 'query');
        $this->DatabaseWithMockedDependencies->getQuotations( 'For Setback' );
    }

    public function testGetQuotationsReturnsQuotations() {
        $this->setReturnValue( $this->MockCms, 'getResults', 'duck' );

        $actual   = $this->DatabaseWithMockedDependencies->getQuotations( 'For Setback' );
        $expected = 'duck';

        $this->assertEquals( $expected, $actual );
    }

    public function testDeleteRelationshipDeletesRelationship() {
        $table = 'wptests_hf_relationship';
        $where = array(
            'userID1' => 4,
            'userID2' => 5
        );

        $this->expectOnce( $this->MockCms, 'deleteRows', array($table, $where) );

        $this->DatabaseWithMockedDependencies->deleteRelationship( 4, 5 );
    }

    public function testDeleteRelationshipSortsIds() {
        $table = 'wptests_hf_relationship';
        $where = array(
            'userID1' => 4,
            'userID2' => 5
        );

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
        $this->expectOnce( $this->MockCms, 'prepareQuery', array("SELECT max(emailID) FROM %s", array('wptests_hf_email')) );
        $this->DatabaseWithMockedDependencies->generateEmailId();
    }

    public function testDaysSinceSecondToLastEmailPreparesQuery() {
        $this->expectOnce( $this->MockCms, 'prepareQuery', array(
            'SELECT sendTime FROM (SELECT * FROM %s WHERE userID = %d ORDER BY emailID DESC LIMIT 2) AS T ORDER BY emailID LIMIT 1',
            array('wptests_hf_email', 1)
        ) );

        $this->DatabaseWithMockedDependencies->daysSinceSecondToLastEmail( 1 );
    }

    public function testDaysSinceSecondToLastEmailUsesPreparedQuery() {
        $this->setReturnValue( $this->MockCms, 'prepareQuery', 'duck' );

        $this->expectOnce( $this->MockCms, 'getVar', array('duck') );

        $this->DatabaseWithMockedDependencies->daysSinceSecondToLastEmail( 1 );
    }

    public function testDatabaseUsesCmsToGetOptionWhenInstallingDatabase() {
        $this->expectOnce( $this->MockCms, 'getOption', array('hfDbVersion') );

        $this->DatabaseWithMockedDependencies->installDb();
    }

    public function testDatabaseUsesCmsinsertOrReplaceRowWhenInstallingDatabase() {
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

        $argSets = array();

        foreach ( $levels as $index => $level ) {
            $argSets[$index] = array(
                'wptests_hf_level',
                $level,
                $levelFormat
            );
        }

        $this->assertMethodCallsMethodWithArgsAtAnyTime(
            $this->MockCms,
            'insertOrReplaceRow',
            $this->DatabaseWithMockedDependencies,
            'installDb',
            $argSets
        );
    }

    public function testInvocationOrderIndependentArgsAssertion() {
        $levelFormat = array('%d', '%s', '%d', '%d', '%d');

        $defaultLevel6 = array(
            'levelID'       => 6,
            'title'         => 'Triumph',
            'size'          => 60,
            'emailInterval' => 365,
            'target'        => 1095 // 3 years
        );

        $this->assertMethodCallsMethodWithArgsAtAnyTime(
            $this->MockCms,
            'insertOrReplaceRow',
            $this->DatabaseWithMockedDependencies,
            'installDb',
            array(array(
                'wptests_hf_level',
                $defaultLevel6,
                $levelFormat
            ))
        );
    }

    public function testInstallDbPopulatesGoalTable() {
        $defaultGoal = array(
            'goalID'     => 1,
            'title'      => 'Pornography Abstinence',
            'isPositive' => 1,
            'isPrivate'  => 0
        );

        $levelFormat = array('%d', '%s', '%d', '%d');

        $this->assertMethodCallsMethodWithArgsAtAnyTime(
            $this->MockCms,
            'insertOrReplaceRow',
            $this->DatabaseWithMockedDependencies,
            'installDb',
            array(array(
                'wptests_hf_goal',
                $defaultGoal,
                $levelFormat
            ))
        );
    }

    public function testCreateRelationshipCreatesRelationship() {
        $expectedRow = array(
            'userID1' => 1,
            'userID2' => 2
        );

        $this->expectOnce( $this->MockCms, 'insertOrReplaceRow', array(
            'wptests_hf_relationship',
            $expectedRow,
            array('%d', '%d')
        ) );
        $this->DatabaseWithMockedDependencies->createRelationship( 1, 2 );
    }

    public function testSetDefaultGoalSubscriptionAddsDefaultGoalSubscription() {
        $expectedData = array(
            'userID' => 7,
            'goalID' => 1
        );

        $this->expectOnce( $this->MockCms, 'insertOrReplaceRow', array('wptests_hf_user_goal', $expectedData, array('%d', '%d')) );
        $this->DatabaseWithMockedDependencies->setDefaultGoalSubscription(7);
    }

    public function testRecordInviteRecordsInvite() {
        $expectedRow = array(
            'inviteID'       => 1,
            'inviterID'      => 2,
            'inviteeEmail'   => 3,
            'emailID'        => 4,
            'expirationDate' => 5
        );

        $this->setReturnValue($this->MockCms, 'getDbPrefix', 'wptests_');
        $this->expectOnce($this->MockCms, 'insertIntoDb', array('wptests_hf_invite', $expectedRow));

        $this->DatabaseWithMockedDependencies->recordInvite(1,2,3,4,5);
    }

    public function testRecordAccountabilityReportRecordsAccountabilityReport() {
        $expectedRow = array(
            'userID'           => 1,
            'goalID'           => 2,
            'isSuccessful'     => 3,
            'referringEmailID' => 4
        );

        $this->setReturnValue($this->MockCms, 'getDbPrefix', 'wptests_');
        $this->expectOnce($this->MockCms, 'insertIntoDb', array('wptests_hf_report', $expectedRow));

        $this->DatabaseWithMockedDependencies->recordAccountabilityReport(1,2,3,4);
    }

    public function testRecordAccountabilityReportDoesntIncludeReferringEmailIdWhenNotGiven() {
        $expectedRow = array(
            'userID'           => 1,
            'goalID'           => 2,
            'isSuccessful'     => 3
        );

        $this->setReturnValue($this->MockCms, 'getDbPrefix', 'wptests_');
        $this->expectOnce($this->MockCms, 'insertIntoDb', array('wptests_hf_report', $expectedRow));

        $this->DatabaseWithMockedDependencies->recordAccountabilityReport(1,2,3);
    }

    public function testRecordEmailRecordsEmail() {
        $expectedRow = array(
            'subject' => 2,
            'body'    => 3,
            'userID'  => 1,
            'emailID' => 4,
            'address' => 5
        );

        $this->setReturnValue($this->MockCms, 'getDbPrefix', 'wptests_');
        $this->expectOnce($this->MockCms, 'insertIntoDb', array('wptests_hf_email', $expectedRow));

        $this->DatabaseWithMockedDependencies->recordEmail(1,2,3,4,5);
    }

    public function testRecordReportRequestRecordsReportRequest() {
        $expectedRow = array(
            'requestID'      => 1,
            'userID'         => 2,
            'emailID'        => 3,
            'expirationDate' => 4
        );

        $this->setReturnValue($this->MockCms, 'getDbPrefix', 'wptests_');
        $this->expectOnce($this->MockCms, 'insertIntoDb', array('wptests_hf_report_request', $expectedRow));

        $this->DatabaseWithMockedDependencies->recordReportRequest(1,2,3,4);
    }

    public function testIsReportRequestValidPreparesQuery() {
        $this->expectOnce( $this->MockCms, 'prepareQuery', array(
            "SELECT * FROM %s WHERE requestID = %d",
            array('wptests_hf_report_request', 7)
        ) );

        $this->DatabaseWithMockedDependencies->isReportRequestValid( 7 );
    }

    public function testGetQuotationsPreparesQuery() {
        $this->expectAt( $this->MockCms, 'prepareQuery', 4, array(
            "SELECT * FROM %s INNER JOIN %s WHERE post_type =  'hf_quotation' AND post_status =  'publish' AND object_id = id AND term_taxonomy_id = %d",
            array('wptests_posts', 'wptests_term_relationships', 2)
        ) );

        $this->setReturnValue($this->MockCms, 'getVar', 2);
        $this->DatabaseWithMockedDependencies->getQuotations( 'Big-C-Context' );
    }

    public function testGetPartnersPreparesQuery() {
        $this->expectOnce( $this->MockCms, 'prepareQuery', array(
            'SELECT * FROM %s INNER JOIN %s
            WHERE (userID1 = ID OR userID2 = ID)
            AND (userID1 = %d OR userID2 = %d) AND ID != $d',
            array('wptests_users', 'wptests_hf_relationship', 2, 2, 2)
        ) );

        $this->DatabaseWithMockedDependencies->getPartners( 2 );
    }

    public function testGetContextIdPreparesQuery() {
        $this->expectAt($this->MockCms, 'prepareQuery', 1, array(
            "SELECT term_id FROM %s WHERE name = %s",
            array('wptests_terms', 'context')
        ));

        $this->DatabaseWithMockedDependencies->getQuotations('context');
    }

    public function testGetContextIdUsesPreparedQuery() {
        $this->setReturnValue($this->MockCms, 'prepareQuery', 'duckVal');
        $this->expectOnce($this->MockCms, 'getVar', array('duckVal'));

        $this->DatabaseWithMockedDependencies->getQuotations('context');
    }

    public function testGetContextIdLooksUpPassedContext() {
        $this->expectAt($this->MockCms, 'prepareQuery', 1, array(
            "SELECT term_id FROM %s WHERE name = %s",
            array('wptests_terms', 'anotherContext')
        ));

        $this->DatabaseWithMockedDependencies->getQuotations('anotherContext');
    }

    public function testDaysSinceLastEmailPreparesQuery() {
        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            'SELECT sendTime FROM %s WHERE userID = %d ORDER BY emailID DESC LIMIT 1',
            array('wptests_hf_email', 7)
        ));

        $this->DatabaseWithMockedDependencies->daysSinceLastEmail(7);
    }

    public function testTimeOfFirstSuccessPreparesQuery() {
        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            'SELECT date FROM %s
            WHERE goalID = %d AND userID = %d
            AND reportID=( SELECT min(reportID) FROM %s WHERE isSuccessful = 1)',
            array('wptests_hf_report', 1, 7, 'wptests_hf_report')
        ));

        $this->DatabaseWithMockedDependencies->timeOfFirstSuccess(1, 7);
    }

    public function testTimeOfLastSuccessPreparesQuery() {
        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            'SELECT date FROM %s
            WHERE goalID = %d AND userID = %d
            AND reportID=( SELECT max(reportID) FROM %s WHERE isSuccessful = 1)',
            array('wptests_hf_report', 1, 7, 'wptests_hf_report')
        ));

        $this->DatabaseWithMockedDependencies->timeOfLastSuccess(1, 7);
    }

    public function testTimeOfLastFailPreparesQuery() {
        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            'SELECT date FROM %s
            WHERE goalID = %d AND userID = %d
            AND reportID=( SELECT max(reportID) FROM %s WHERE NOT isSuccessful = 1)',
            array('wptests_hf_report', 1, 7, 'wptests_hf_report')
        ));

        $this->DatabaseWithMockedDependencies->timeOfLastFail(1, 7);
    }

    public function testGetLevelPreparesQuery() {
        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            'target > %d ORDER BY target ASC',
            array(13)
        ));

        $this->DatabaseWithMockedDependencies->getLevel(13);
    }

    public function testDaysSinceLastReportPreparesQuery() {
        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            'SELECT date FROM %s
            WHERE goalID = %d AND userID = %d
            AND reportID=( SELECT max(reportID) FROM %s )',
            array('wptests_hf_report', 3, 7, 'wptests_hf_report')
        ));

        $this->DatabaseWithMockedDependencies->daysSinceLastReport(3, 7);
    }

    public function testDaysSinceAnyReportPreparesQuery() {
        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            'SELECT date FROM %s
            WHERE userID = %d
            AND reportID=( SELECT max(reportID) FROM %s )',
            array('wptests_hf_report', 7, 'wptests_hf_report')
        ));

        $this->DatabaseWithMockedDependencies->daysSinceAnyReport(7);
    }

    public function testIdOfLastEmailPreparesQuery() {
        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            'SELECT max(emailID) FROM %s',
            array('wptests_hf_email')
        ));

        $this->DatabaseWithMockedDependencies->idOfLastEmail();
    }

    public function testGetInviterIdPreparesQuery() {
        $MockInvite = new stdClass();
        $MockInvite->inviterID = 2;

        $this->setReturnValue($this->MockCms, 'getRow', $MockInvite);

        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            "inviteID = %s",
            array(343)
        ));

        $this->DatabaseWithMockedDependencies->getInviterID(343);
    }

    public function testIsEmailValidPreparesQuery() {
        $this->expectOnce($this->MockCms, 'prepareQuery', array(
            'userID = %d AND emailID = %d',
            array(1, 3)
        ));

        $this->DatabaseWithMockedDependencies->isEmailValid(1, 3);
    }
}