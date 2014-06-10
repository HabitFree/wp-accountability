<?php

require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

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

    public function testDaysSinceLastEmail() {
        $WebsiteAPI = $this->myMakeMock('HfWordPressInterface');
        $PhpApi     = $this->myMakeMock('HfPhpLibrary');

        $this->mySetReturnValue($WebsiteAPI, 'getVar', '2014-05-27 16:04:29');
        $this->mySetReturnValue($PhpApi, 'convertStringToTime', 1401224669.0);
        $this->mySetReturnValue($PhpApi, 'getCurrentTime', 1401483869.0);

        $Database = new HfMysqlDatabase( $WebsiteAPI, $PhpApi );
        $result   = $Database->daysSinceLastEmail( 1 );

        $this->assertEquals( $result, 3 );
    }
}