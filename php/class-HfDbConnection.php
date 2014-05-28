<?php

if (!class_exists("HfDbConnection")) {
	class HfDbConnection {
		private $dbVersion = "3.7";
		
		function HfDbConnection() { //constructor
		}
		
		function installDb() {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$installedDbVersion = get_option( "hfDbVersion" );
			
			if ( $installedDbVersion != $this->dbVersion ) {
				$emailTableSql = "CREATE TABLE " . $prefix .  "hf_email (
					emailID int NOT NULL AUTO_INCREMENT,
					sendTime timestamp DEFAULT current_timestamp NOT NULL,
					subject VARCHAR(500) NOT NULL,
					body text NOT NULL,
					userID int NOT NULL,
					deliveryStatus bit(1) DEFAULT 0 NOT NULL,
					openTime datetime NULL,
					address varchar(80) NULL,
					KEY userID (userID),
					PRIMARY KEY  (emailID)
				);";
				
				$goalTableSql = "CREATE TABLE " . $prefix . "hf_goal (
					goalID int NOT NULL AUTO_INCREMENT,
					title VARCHAR(500) NOT NULL,
					description text NULL,
					thumbnail VARCHAR(80) NULL,
					isPositive bit(1) DEFAULT 0 NOT NULL,
					isPrivate bit(1) DEFAULT 1 NOT NULL,
					creatorID int NULL,
					dateCreated timestamp DEFAULT current_timestamp NOT NULL,
					KEY creatorID (creatorID),
					PRIMARY KEY  (goalID)
				);";
				
				$reportTableSql = "CREATE TABLE " . $prefix . "hf_report (
					reportID int NOT NULL AUTO_INCREMENT,
					userID int NOT NULL,
					goalID int NOT NULL,
					referringEmailID INT NULL,
					isSuccessful tinyint NOT NULL,
					date timestamp DEFAULT current_timestamp NOT NULL,
					KEY userID (userID),
					KEY goalID (goalID),
					KEY referringEmailID (referringEmailID),
					PRIMARY KEY  (reportID)
				);";
				
				$userGoalTableSql = "CREATE TABLE " . $prefix . "hf_user_goal (
					userID int NOT NULL,
					goalID int NOT NULL,
					dateStarted timestamp DEFAULT current_timestamp NOT NULL,
					isActive bit(1) DEFAULT 1 NOT NULL,
					PRIMARY KEY  (userID, goalID)
				);";
				
				$levelTableSql = "CREATE TABLE " . $prefix . "hf_level (
					levelID int NOT NULL,
					title VARCHAR(500) NOT NULL,
					description text NULL,
					size int NOT NULL,
					emailInterval int NOT NULL,
					target int NOT NULL,
					PRIMARY KEY  (levelID)
				);";
				
				$inviteTableSql = "CREATE TABLE " . $prefix . "hf_invite (
					inviteID varchar(250) NOT NULL,
					inviterID int NOT NULL,
					inviteeEmail VARCHAR(80) NOT NULL,
					emailID int NOT NULL,
					expirationDate datetime NOT NULL,
					KEY inviterID (inviterID),
					KEY emailID (emailID),
					PRIMARY KEY  (inviteID)
				);";
			
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $emailTableSql );
				dbDelta( $goalTableSql );
				dbDelta( $reportTableSql );
				dbDelta( $userGoalTableSql );
				dbDelta( $levelTableSql );
				dbDelta( $inviteTableSql );
				
				$defaultGoal = array( 'goalID' => 1,
					'title' => 'Pornography Abstinence',
					'isPositive' => 1,
					'isPrivate' => 0 );
				
				$this->insertUpdateIntoDb('hf_goal', $defaultGoal);
				
				$defaultLevel0 = array(
						'levelID' => 0,
						'title' => 'Hibernation',
						'size' => 0,
						'emailInterval' => 0,
						'target' => 0
					);
				
				$defaultLevel1 = array(
						'levelID' => 1,
						'title' => 'Dawn',
						'size' => 2,
						'emailInterval' => 1,
						'target' => 14
					);
				
				$defaultLevel2 = array(
						'levelID' => 2,
						'title' => 'Breach',
						'size' => 5,
						'emailInterval' => 7,
						'target' => 30
					);
				
				$defaultLevel3 = array(
						'levelID' => 3,
						'title' => 'Progress',
						'size' => 10,
						'emailInterval' => 14,
						'target' => 90
					);
				
				$defaultLevel4 = array(
						'levelID' => 4,
						'title' => 'Conquest',
						'size' => 15,
						'emailInterval' => 30,
						'target' => 365
					);
				
				$defaultLevel5 = array(
						'levelID' => 5,
						'title' => 'Conquering',
						'size' => 30,
						'emailInterval' => 90,
						'target' => 1095 // 3 years
					);
				
				$defaultLevel6 = array(
						'levelID' => 6,
						'title' => 'Triumph',
						'size' => 60,
						'emailInterval' => 365,
						'target' => 1095 // 3 years
					);
				
				$defaultLevel7 = array(
						'levelID' => 7,
						'title' => 'Vigilance',
						'size' => 0,
						'emailInterval' => 365,
						'target' => 0
					);
				
				$this->insertUpdateIntoDb('hf_level', $defaultLevel0);
				$this->insertUpdateIntoDb('hf_level', $defaultLevel1);
				$this->insertUpdateIntoDb('hf_level', $defaultLevel2);
				$this->insertUpdateIntoDb('hf_level', $defaultLevel3);
				$this->insertUpdateIntoDb('hf_level', $defaultLevel4);
				$this->insertUpdateIntoDb('hf_level', $defaultLevel5);
				$this->insertUpdateIntoDb('hf_level', $defaultLevel6);
				$this->insertUpdateIntoDb('hf_level', $defaultLevel7);
				
				update_option( "hfDbVersion", $this->dbVersion );
			}
		}
		
		function insertUpdateIntoDb($table, $data) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$data = $this->removeNullValuePairs($data);
			$data = $this->escapeData($data);
			$cols = '';
			$vals = '';
			$pairs = '';
			
			foreach ($data as $col=>$value) {
				$cols .= $col . ',';
				if (is_int($value)) {
					$vals .= $value . ',';
					$pairs .= $col . '=' . $value . ',';
				} else {
					$vals .= "'" . $value . "',";
					$pairs .= $col . "='" . $value . "',";
				}
			}
			
			$cols = trim($cols, ',');
			$vals = trim($vals, ',');
			$pairs = trim($pairs, ',');
			
			$wpdb->query("INSERT INTO " . $prefix . $table .
					"(" . $cols . ")
					VALUES (" . $vals . ")
					ON DUPLICATE KEY UPDATE " . $pairs );
		}
		
		function insertIntoDb($table, $data) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tableName = $prefix . $table;
			$data = $this->removeNullValuePairs($data);
			$success = $wpdb->insert( $tableName, $data );
			if ($success === false) {
				$wpdb->print_error();
			}
			return $success;
		}
		
		function insertMultipleRows($table, $rows) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tableName = $prefix . $table;
			foreach ($rows as $row=>$values) {
				$data = $this->removeNullValuePairs($values);
				$wpdb->insert( $tableName, $data );
			}
		}
		
		function removeNullValuePairs($array) {
			foreach ($array as $key=>$value) {
				if ($value === null) {
					unset($array[$key]);
				}
			}
			return $array;
		}
		
		function escapeData($data) {
			foreach ($data as $col=>$val) {
				$col = esc_sql( $col );
				$val = esc_sql( $val );
			}
			return $data;
		}
		
		function insertIgnoreIntoDb($table, $data) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tableName = $prefix . $table;
			$data = $this->escapeData($data);
			$setValues = '';
			foreach ($data as $col => $val) {
				if ($setValues !== '') {
					$setValues .= ", ";
				}
				$setValues .= "`" . $col . "` = ";
				if (is_int($val)) {
					$setValues .= $val;
				} else {
					$setValues .= "`" . $val . "`";
				}
			}
			
			$query = "INSERT IGNORE INTO `" . $tableName . "` SET " . $setValues . ";";
			
			$wpdb->query($query);
		}
		
		function getRow($table, $criterion) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			return $wpdb->get_row("SELECT * FROM " . $prefix . $table . " WHERE " . $criterion);
		}
		
		function getRows($table, $where, $outputType = OBJECT) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			if ($where === null) {
				return $wpdb->get_results("SELECT * FROM " . $prefix . $table, $outputType);
			} else {
				return $wpdb->get_results("SELECT * FROM " . $prefix . $table . " WHERE " . $where, $outputType);
			}
		}
		
		function generateEmailID() {
			$table = 'hf_email';
			$select = 'max(emailID)';
			return $this->getVar($table, $select) + 1;
		}
		
		function getVar($table, $select, $where = null) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tableName = $prefix . $table;
			if ($where === null) {
				return $wpdb->get_var("SELECT " . $select . " FROM " . $tableName);
			} else {
				return $wpdb->get_var("SELECT " . $select .
					" FROM " . $tableName .
					" WHERE " . $where );
			}
		}
		
		function updateRows($table, $data, $where) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tableName = $prefix . $table;
			return $wpdb->update($tableName, $data, $where);
		}
		
		function emptyTable($table) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tableName = $prefix . $table;
			$wpdb->query("TRUNCATE TABLE " . $tableName );
		}
		
		function createColumnSchemaObject($field, $type, $null, $key, $default, $extra) {
			$column = new StdClass;
			$column->Field = $field;
			$column->Type = $type;
			$column->Null = $null;
			$column->Key = $key;
			$column->Default = $default;
			$column->Extra = $extra;
			return $column;
		}
		
		function getTableSchema($table) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tableName = $prefix . $table;
			return $wpdb->get_results('SHOW COLUMNS FROM ' . $tableName, OBJECT_K);
		}
		
		function getTable($table) {
			return $this->getRows($table, null, ARRAY_A);
		}
		
		function deleteRow($table, $where) {
			global $wpdb;
			$tableName = $this->getFullTableName($table);
			return $wpdb->query( 'DELETE FROM ' . $tableName . ' WHERE ' . $where );
		}
		
		function getFullTableName($table) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			return $prefix . $table;
		}
		
		function countRowsInTable($table) {
			$rows = $this->getTable($table);
			return count($rows);
		}

        function sudoReactivateExtension() {
            hfDeactivate();
            hfActivate();
        }
	}
}
?>