<?php

if (!class_exists("HfDbManager")) {
	class HfDbManager {
		private $dbVersion = "2.4";
		
		function HfDbManager() { //constructor
		}
		
		function installDb() {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$installedDbVersion = get_option( "hfDbVersion" );
			
			if ( $installedDbVersion != $this->dbVersion ) {
				$sql = "
				CREATE TABLE " . $prefix .  "hf_email (
					emailID int NOT NULL AUTO_INCREMENT,
					sendTime timestamp DEFAULT current_timestamp NOT NULL,
					subject VARCHAR(500) NOT NULL,
					body text NOT NULL,
					userID int NOT NULL,
					deliveryStatus bit(1) DEFAULT 0 NOT NULL,
					openTime timestamp NULL,
					KEY userID (userID),
					PRIMARY KEY  (emailID)
				);
				
				CREATE TABLE " . $prefix . "hf_goal (
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
				);
				
				CREATE TABLE " . $prefix . "hf_report (
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
				);
				
				CREATE TABLE " . $prefix . "hf_user_goal (
					userID int NOT NULL,
					goalID int NOT NULL,
					dateStarted timestamp DEFAULT current_timestamp NOT NULL,
					isActive bit(1) DEFAULT 1 NOT NULL,
					PRIMARY KEY  (userID, goalID)
				);
				
				CREATE TABLE " . $prefix . "hf_stage (
					stageID int NOT NULL,
					title VARCHAR(500) NOT NULL,
					description text NULL,
					size int NOT NULL,
					emailInterval int NOT NULL,
					target int NOT NULL,
					PRIMARY KEY  (stageID)
				);
				
				";
			
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
				
				$defaultGoal = array( 'goalID' => 1,
					'title' => 'I only viewed sexually pure material on the Internet',
					'isPositive' => 1,
					'isPrivate' => 0 );
				
				$this->insertUpdateIntoDb('hf_goal', $defaultGoal);
				
				$defaultStage0 = array(
						'stageID' => 0,
						'title' => 'Hibernation',
						'size' => 0,
						'emailInterval' => 0,
						'target' => 0
					);
				
				$defaultStage1 = array(
						'stageID' => 1,
						'title' => 'Dawn',
						'size' => 2,
						'emailInterval' => 1,
						'target' => 14
					);
				
				$defaultStage2 = array(
						'stageID' => 2,
						'title' => 'Breach',
						'size' => 5,
						'emailInterval' => 7,
						'target' => 30
					);
				
				$defaultStage3 = array(
						'stageID' => 3,
						'title' => 'Progress',
						'size' => 10,
						'emailInterval' => 14,
						'target' => 90
					);
				
				$defaultStage4 = array(
						'stageID' => 4,
						'title' => 'Conquest',
						'size' => 15,
						'emailInterval' => 30,
						'target' => 365
					);
				
				$defaultStage5 = array(
						'stageID' => 5,
						'title' => 'Conquering',
						'size' => 30,
						'emailInterval' => 90,
						'target' => 1095 // 3 years
					);
				
				$defaultStage6 = array(
						'stageID' => 6,
						'title' => 'Triumph',
						'size' => 60,
						'emailInterval' => 365,
						'target' => 1095 // 3 years
					);
				
				$defaultStage7 = array(
						'stageID' => 7,
						'title' => 'Vigilance',
						'size' => 0,
						'emailInterval' => 365,
						'target' => 0
					);
				
				$this->insertUpdateIntoDb('hf_stage', $defaultStage0);
				$this->insertUpdateIntoDb('hf_stage', $defaultStage1);
				$this->insertUpdateIntoDb('hf_stage', $defaultStage2);
				$this->insertUpdateIntoDb('hf_stage', $defaultStage3);
				$this->insertUpdateIntoDb('hf_stage', $defaultStage4);
				$this->insertUpdateIntoDb('hf_stage', $defaultStage5);
				$this->insertUpdateIntoDb('hf_stage', $defaultStage6);
				$this->insertUpdateIntoDb('hf_stage', $defaultStage7);
				
				update_option( "hfDbVersion", $this->dbVersion );
			}
		}
		
		function insertUpdateIntoDb($table, $data) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$data = $this->removeNullValuePairs($data);
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
			$wpdb->insert( $tableName, $data );
		}
		
		function removeNullValuePairs($array) {
			foreach ($array as $key=>$value) {
				if ($value === null) {
					unset($array[$key]);
				}
			}
			return $array;
		}
		
		function insertIgnoreIntoDb($table, $data) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tableName = $prefix . $table;
			
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
		
		function getRows($table, $criterion, $outputType = OBJECT) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			return $wpdb->get_results("SELECT * FROM " . $prefix . $table . " WHERE " . $criterion, $outputType);
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
	}
}
?>