<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestDatabase extends HfTestCase {
    public function testDbDataNullRemoval() {
        $Database = $this->factory->makeDatabase();

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
        $this->setReturnValue( $this->mockCms, 'getVar', '2014-05-27 16:04:29' );
        $this->setReturnValue( $this->mockCodeLibrary, 'convertStringToTime', 1401224669.0 );
        $this->setReturnValue( $this->mockCodeLibrary, 'getCurrentTime', 1401483869.0 );

        $result = $this->mockedDatabase->daysSinceLastEmail( 1 );

        $this->assertEquals( $result, 3 );
    }

    public function testDatabaseHasDeleteInvitationMethod() {
        $this->assertTrue( method_exists( $this->mockedDatabase, 'deleteInvite' ) );
    }

    public function testDatabaseCallsDeleteRowsMethod() {
        $this->expectOnce( $this->mockCms, 'deleteRows' );
        $this->mockedDatabase->deleteInvite( 777 );
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

        $this->setReturnValue( $this->mockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->mockCms, 'insertIntoDb', array( 'wptests_' . $table, $data ) );

        $this->mockedDatabase->recordReportRequest( $requestId, $userId, $emailId, $expirationDate );
    }

    public function testIsReportRequestValid() {
        $this->setReturnValue( $this->mockCms, 'getResults', array( new stdClass() ) );

        $this->assertTrue( $this->mockedDatabase->isReportRequestValid( 555 ) );
    }

    public function testIsReportRequestValidReturnsFalse() {
        $this->assertFalse( $this->mockedDatabase->isReportRequestValid( 555 ) );
    }

    public function testDeleteReportRequest() {
        $this->setReturnValue( $this->mockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->mockCms, 'deleteRows', array( 'wptests_hf_report_request', array( 'requestID' => 555 ) ) );
        $this->mockedDatabase->deleteReportRequest( 555 );
    }

    public function testGetReportRequestUserId() {
        $mockReportRequest         = new stdClass();
        $mockReportRequest->userID = 5;

        $this->setReturnValue( $this->mockCms, 'getRow', $mockReportRequest );

        $actual = $this->mockedDatabase->getReportRequestUserId( 555 );

        $this->assertEquals( 5, $actual );
    }

    public function testUpdateExpirationDate() {
        $data = array(
            'expirationDate' => '2014-06-19 15:58:37'
        );

        $where = array(
            'requestID' => 555
        );

        $this->setReturnValue( $this->mockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->mockCms, 'updateRowsSafe', array( 'wptests_hf_report_request', $data, $where ) );

        $this->mockedDatabase->updateReportRequestExpirationDate( 555, 1403211517 );
    }

    public function testGetAllInvitesReturnsInvites() {
        $this->setReturnValue( $this->mockCms, 'getResults', 'duck' );
        $this->assertEquals( $this->mockedDatabase->getAllInvites(), 'duck' );
    }

    public function testGetAllReportRequestsReturnsReportRequests() {
        $this->setReturnValue( $this->mockCms, 'getResults', 'duck' );
        $this->assertEquals( $this->mockedDatabase->getAllReportRequests(), 'duck' );
    }

    public function testGetQuotationsGetsQuotations() {
        $this->expectOnce( $this->mockCms, 'getResults', array( 'query' ) );
        $this->setReturnValue( $this->mockCms, 'getVar', 1 );
        $this->setReturnValue( $this->mockCms, 'prepareQuery', 'query' );
        $this->mockedDatabase->getQuotations( 'For Setback' );
    }

    public function testGetQuotationsReturnsQuotations() {
        $this->setReturnValue( $this->mockCms, 'getResults', 'duck' );

        $actual   = $this->mockedDatabase->getQuotations( 'For Setback' );
        $expected = 'duck';

        $this->assertEquals( $expected, $actual );
    }

    public function testDeleteRelationshipDeletesRelationship() {
        $table = 'wptests_hf_relationship';
        $where = array(
            'userID1' => 4,
            'userID2' => 5
        );

        $this->expectOnce( $this->mockCms, 'deleteRows', array( $table, $where ) );

        $this->mockedDatabase->deleteRelationship( 4, 5 );
    }

    public function testDeleteRelationshipSortsIds() {
        $table = 'wptests_hf_relationship';
        $where = array(
            'userID1' => 4,
            'userID2' => 5
        );

        $this->expectOnce( $this->mockCms, 'deleteRows', array( $table, $where ) );

        $this->mockedDatabase->deleteRelationship( 5, 4 );
    }

    public function testGenerateEmailId() {
        $this->expectOnce( $this->mockCms, 'getVar', array( "SELECT max(emailID) FROM wptests_hf_email" ) );
        $this->mockedDatabase->generateEmailId();
    }

    public function testDaysSinceSecondToLastEmailPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT sendTime FROM (SELECT * FROM wptests_hf_email WHERE userID = %d ORDER BY emailID DESC LIMIT 2) AS T ORDER BY emailID LIMIT 1',
            array( 1 )
        ) );

        $this->mockedDatabase->daysSinceSecondToLastEmail( 1 );
    }

    public function testDaysSinceSecondToLastEmailUsesPreparedQuery() {
        $this->setReturnValue( $this->mockCms, 'prepareQuery', 'duck' );

        $this->expectOnce( $this->mockCms, 'getVar', array( 'duck' ) );

        $this->mockedDatabase->daysSinceSecondToLastEmail( 1 );
    }

    public function testDatabaseUsesCmsToGetOptionWhenInstallingDatabase() {
        $this->expectOnce( $this->mockCms, 'getOption', array( 'hfDbVersion' ) );

        $this->mockedDatabase->installDb();
    }

    public function testDatabaseUsesCmsInsertOrReplaceRowWhenInstallingDatabase() {
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
            $this->mockCms,
            'insertOrReplaceRow',
            $this->mockedDatabase,
            'installDb',
            $argSets
        );
    }

    public function testInvocationOrderIndependentArgsAssertion() {
        $levelFormat = array( '%d', '%s', '%d', '%d', '%d' );

        $defaultLevel6 = array(
            'levelID'       => 6,
            'title'         => 'Triumph',
            'size'          => 60,
            'emailInterval' => 365,
            'target'        => 1095 // 3 years
        );

        $this->assertMethodCallsMethodWithArgsAtAnyTime(
            $this->mockCms,
            'insertOrReplaceRow',
            $this->mockedDatabase,
            'installDb',
            array( array(
                'wptests_hf_level',
                $defaultLevel6,
                $levelFormat
            ) )
        );
    }

    public function testInstallDbPopulatesGoalTable() {
        $defaultGoal = array(
            'goalID'     => 1,
            'title'      => 'Freedom from Pornography',
            'isPositive' => 1,
            'isPrivate'  => 0
        );

        $levelFormat = array( '%d', '%s', '%d', '%d' );

        $this->assertMethodCallsMethodWithArgsAtAnyTime(
            $this->mockCms,
            'insertOrReplaceRow',
            $this->mockedDatabase,
            'installDb',
            array( array(
                'wptests_hf_goal',
                $defaultGoal,
                $levelFormat
            ) )
        );
    }

    public function testInstallDbPopulatesGoalTableWithSelfAbuseGoal() {
        $defaultGoal = array(
            'goalID'     => 2,
            'title'      => 'Freedom from Self-Abuse',
            'isPositive' => 1,
            'isPrivate'  => 0
        );

        $levelFormat = array( '%d', '%s', '%d', '%d' );

        $this->assertMethodCallsMethodWithArgsAtAnyTime(
            $this->mockCms,
            'insertOrReplaceRow',
            $this->mockedDatabase,
            'installDb',
            array( array(
                'wptests_hf_goal',
                $defaultGoal,
                $levelFormat
            ) )
        );
    }

    public function testCreateRelationshipCreatesRelationship() {
        $expectedRow = array(
            'userID1' => 1,
            'userID2' => 2
        );

        $this->expectOnce( $this->mockCms, 'insertOrReplaceRow', array(
            'wptests_hf_relationship',
            $expectedRow,
            array( '%d', '%d' )
        ) );
        $this->mockedDatabase->createRelationship( 1, 2 );
    }

    public function testSetDefaultGoalSubscriptionAddsDefaultGoalSubscription() {
        $subOne = array(
            'userID' => 7,
            'goalID' => 1
        );
        $subTwo = array(
            'userID' => 7,
            'goalID' => 2
        );

        $this->expectAt( $this->mockCms, 'insertOrReplaceRow', 1, array( 'wptests_hf_user_goal', $subOne, array( '%d', '%d' ) ) );
        $this->expectAt( $this->mockCms, 'insertOrReplaceRow', 2, array( 'wptests_hf_user_goal', $subTwo, array( '%d', '%d' ) ) );
        $this->mockedDatabase->setDefaultGoalSubscription( 7 );
    }

    public function testRecordInviteRecordsInvite() {
        $expectedRow = array(
            'inviteID'       => 1,
            'inviterID'      => 2,
            'inviteeEmail'   => 3,
            'emailID'        => 4,
            'expirationDate' => 5
        );

        $this->setReturnValue( $this->mockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->mockCms, 'insertIntoDb', array(
                'wptests_hf_invite',
                $expectedRow,
                array( '%s', '%d', '%s', '%d', '%s' )
            )
        );

        $this->mockedDatabase->recordInvite( 1, 2, 3, 4, 5 );
    }

    public function testRecordAccountabilityReportRecordsAccountabilityReport() {
        $expectedRow = array(
            'userID'           => 1,
            'goalID'           => 2,
            'isSuccessful'     => 3,
            'referringEmailID' => 4
        );

        $this->setReturnValue( $this->mockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->mockCms, 'insertIntoDb', array( 'wptests_hf_report', $expectedRow ) );

        $this->mockedDatabase->recordAccountabilityReport( 1, 2, 3, 4 );
    }

    public function testRecordAccountabilityReportDoesntIncludeReferringEmailIdWhenNotGiven() {
        $expectedRow = array(
            'userID'       => 1,
            'goalID'       => 2,
            'isSuccessful' => 3
        );

        $this->setReturnValue( $this->mockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->mockCms, 'insertIntoDb', array( 'wptests_hf_report', $expectedRow ) );

        $this->mockedDatabase->recordAccountabilityReport( 1, 2, 3 );
    }

    public function testRecordEmailRecordsEmail() {
        $expectedRow = array(
            'subject' => 2,
            'body'    => 3,
            'userID'  => 1,
            'emailID' => 4,
            'address' => 5
        );

        $this->setReturnValue( $this->mockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->mockCms, 'insertIntoDb', array( 'wptests_hf_email', $expectedRow ) );

        $this->mockedDatabase->recordEmail( 1, 2, 3, 4, 5 );
    }

    public function testRecordReportRequestRecordsReportRequest() {
        $expectedRow = array(
            'requestID'      => 1,
            'userID'         => 2,
            'emailID'        => 3,
            'expirationDate' => 4
        );

        $this->setReturnValue( $this->mockCms, 'getDbPrefix', 'wptests_' );
        $this->expectOnce( $this->mockCms, 'insertIntoDb', array( 'wptests_hf_report_request', $expectedRow ) );

        $this->mockedDatabase->recordReportRequest( 1, 2, 3, 4 );
    }

    public function testIsReportRequestValidPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            "SELECT * FROM wptests_hf_report_request WHERE requestID = %d",
            array( 7 )
        ) );

        $this->mockedDatabase->isReportRequestValid( 7 );
    }

    public function testGetQuotationsPreparesQuery() {
        $format = "SELECT * FROM wptests_posts INNER JOIN wptests_term_relationships
            WHERE post_type = 'hf_quotation' AND post_status = 'publish' AND object_id = id AND term_taxonomy_id = %d";
        $this->expectAt( $this->mockCms, 'prepareQuery', 4, array(
            $format,
            array( 2 )
        ) );

        $this->setReturnValue( $this->mockCms, 'getVar', 2 );
        $this->mockedDatabase->getQuotations( 'Big-C-Context' );
    }

    public function testGetPartnersPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT * FROM wptests_users INNER JOIN wptests_hf_relationship
            WHERE (userID1 = ID OR userID2 = ID)
            AND (userID1 = %d OR userID2 = %d) AND ID != %d',
            array( 2, 2, 2 )
        ) );

        $this->mockedDatabase->getPartners( 2 );
    }

    public function testGetContextIdPreparesQuery() {
        $this->expectAt( $this->mockCms, 'prepareQuery', 1, array(
            "SELECT term_id FROM wptests_terms WHERE name = %s",
            array( 'context' )
        ) );

        $this->mockedDatabase->getQuotations( 'context' );
    }

    public function testGetContextIdUsesPreparedQuery() {
        $this->setReturnValue( $this->mockCms, 'prepareQuery', 'duckVal' );
        $this->expectOnce( $this->mockCms, 'getVar', array( 'duckVal' ) );

        $this->mockedDatabase->getQuotations( 'context' );
    }

    public function testGetContextIdLooksUpPassedContext() {
        $this->expectAt( $this->mockCms, 'prepareQuery', 1, array(
            "SELECT term_id FROM wptests_terms WHERE name = %s",
            array( 'anotherContext' )
        ) );

        $this->mockedDatabase->getQuotations( 'anotherContext' );
    }

    public function testDaysSinceLastEmailPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT sendTime FROM wptests_hf_email WHERE userID = %d ORDER BY emailID DESC LIMIT 1',
            array( 7 )
        ) );

        $this->mockedDatabase->daysSinceLastEmail( 7 );
    }

    public function testTimeOfFirstSuccessPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT date FROM wptests_hf_report WHERE reportID=( SELECT min(reportID) FROM wptests_hf_report WHERE ' .
            'isSuccessful = 1 AND goalID = %d AND userID = %d)',
            array( 1, 7 )
        ) );

        $this->mockedDatabase->timeOfFirstSuccess( 1, 7 );
    }

    public function testTimeOfLastSuccessPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT date FROM wptests_hf_report WHERE reportID=( SELECT max(reportID) FROM wptests_hf_report WHERE ' .
            'isSuccessful = 1 AND goalID = %d AND userID = %d)',
            array( 1, 7 )
        ) );

        $this->mockedDatabase->timeOfLastSuccess( 1, 7 );
    }

    public function testTimeOfLastFailPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT date FROM wptests_hf_report WHERE reportID=( SELECT max(reportID) FROM wptests_hf_report WHERE ' .
            'goalID = %d AND userID = %d AND NOT isSuccessful = 1)',
            array( 1, 7 )
        ) );

        $this->mockedDatabase->timeOfLastFail( 1, 7 );
    }

    public function testGetLevelPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT * FROM wptests_hf_level WHERE target > %d ORDER BY target ASC',
            array( 13 )
        ) );

        $this->mockedDatabase->getLevel( 13 );
    }

    public function testGetLevelUsesOnlyPreparedQuery() {
        $this->setReturnValue( $this->mockCms, 'prepareQuery', 'duck' );
        $this->expectOnce( $this->mockCms, 'getRow', array( 'duck' ) );
        $this->mockedDatabase->getLevel( 13 );
    }

    public function testDaysSinceLastReportPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT date FROM wptests_hf_report ' .
            'WHERE reportID=( SELECT max(reportID) FROM wptests_hf_report WHERE goalID = %d AND userID = %d )',
            array( 3, 7 )
        ) );

        $this->mockedDatabase->daysSinceLastReport( 3, 7 );
    }

    public function testDaysSinceAnyReportPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT date FROM wptests_hf_report
            WHERE userID = %d
            AND reportID=( SELECT max(reportID) FROM wptests_hf_report )',
            array( 7 )
        ) );

        $this->mockedDatabase->daysSinceAnyReport( 7 );
    }

    public function testIdOfLastEmailQuery() {
        $this->expectOnce( $this->mockCms, 'getVar', array(
            'SELECT max(emailID) FROM wptests_hf_email' ) );

        $this->mockedDatabase->idOfLastEmail();
    }

    public function testGetInviterIdPreparesQuery() {
        $this->setMockInviteReturnValue();

        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            "SELECT * FROM wptests_hf_invite WHERE inviteID = %s",
            array( 343 )
        ) );

        $this->mockedDatabase->getInviterId( 343 );
    }

    private function setMockInviteReturnValue() {
        $MockInvite            = new stdClass();
        $MockInvite->inviterID = 2;

        $this->setReturnValue( $this->mockCms, 'getRow', $MockInvite );
    }

    public function testGetInviterIdUsesPreparedQueryOnly() {
        $this->setMockInviteReturnValue();
        $this->setReturnValue( $this->mockCms, 'prepareQuery', 'duck' );
        $this->expectOnce( $this->mockCms, 'getRow', array( 'duck' ) );

        $this->mockedDatabase->getInviterId( 343 );
    }

    public function testIsEmailValidPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT * FROM wptests_hf_email WHERE userID = %d AND emailID = %d',
            array( 1, 3 )
        ) );

        $this->mockedDatabase->isEmailValid( 1, 3 );
    }

    public function testIsEmailValidUsesOnlyPreparedQuery() {
        $this->setReturnValue( $this->mockCms, 'prepareQuery', 'duck' );
        $this->expectOnce( $this->mockCms, 'getRow', array( 'duck' ) );
        $this->mockedDatabase->isEmailValid( 1, 3 );
    }

    public function testGetGoalPreparesQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT * FROM wptests_hf_goal WHERE goalID = %d',
            array( 3 )
        ) );

        $this->mockedDatabase->getGoal( 3 );
    }

    public function testGetGoalUsesOnlyPreparedQuery() {
        $this->setReturnValue( $this->mockCms, 'prepareQuery', 'duck' );
        $this->expectOnce( $this->mockCms, 'getRow', array( 'duck' ) );
        $this->mockedDatabase->getGoal( 3 );
    }

    public function testGetReportRequestUserIdPreparesQuery() {
        $this->setMockRequestReturnValue();

        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT * FROM wptests_hf_report_request WHERE requestID = %s',
            array( 9 )
        ) );

        $this->mockedDatabase->getReportRequestUserId( 9 );
    }

    private function setMockRequestReturnValue() {
        $MockRequest         = new stdClass();
        $MockRequest->userID = 5;
        $this->setReturnValue( $this->mockCms, 'getRow', $MockRequest );
    }

    public function testGetReportRequestIdUsesOnlyPreparedQuery() {
        $this->setMockRequestReturnValue();

        $this->setReturnValue( $this->mockCms, 'prepareQuery', 'duck' );
        $this->expectOnce( $this->mockCms, 'getRow', array( 'duck' ) );
        $this->mockedDatabase->getReportRequestUserId( 9 );
    }

    public function testGetAllInvitesUsesGetResults() {
        $expected = 'SELECT * FROM wptests_hf_invite';
        $this->expectOnce( $this->mockCms, 'getResults', array( $expected ) );
        $this->mockedDatabase->getAllInvites();
    }

    public function testGetAllReportRequestsUsesGetResults() {
        $expected = 'SELECT * FROM wptests_hf_report_request';
        $this->expectOnce( $this->mockCms, 'getResults', array( $expected ) );
        $this->mockedDatabase->getAllReportRequests();
    }

    public function testGetGoalSubscriptionsPreparesNewQuery() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT * FROM wptests_hf_user_goal WHERE userID = %d',
            array( 7 )
        ) );

        $this->mockedDatabase->getGoalSubscriptions( 7 );
    }

    public function testGetGoalSubscriptionsUsesPreparedQuery() {
        $this->setReturnValue( $this->mockCms, 'prepareQuery', 'duck' );
        $this->expectOnce( $this->mockCms, 'getResults', array( 'duck' ) );
        $this->mockedDatabase->getGoalSubscriptions( 7 );
    }

    public function testGetGoalSubscriptionsUsesPassedValue() {
        $this->expectOnce( $this->mockCms, 'prepareQuery', array(
            'SELECT * FROM wptests_hf_user_goal WHERE userID = %d',
            array( 3 )
        ) );

        $this->mockedDatabase->getGoalSubscriptions( 3 );
    }
}