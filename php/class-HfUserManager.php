<?php

if (!class_exists("HfUserManager")) {
	class HfUserManager {
		
		function HfUserManager() { //constructor
		}
		
		private function currentUser() {
			return wp_get_current_user();
		}
		
		function processAllUsers() {
			$users = get_users();
			foreach ($users as $user) {
				$this->processNewUser($user->ID);
			}
		}
		
		private function processNewUser($userID) {
			$HfDbMain = new HfDbManager();
			$table = "hf_user_goal";
			$data = array( 'userID' => $userID,
				'goalID' => 1 );
			$HfDbMain->insertIgnoreIntoDb($table, $data);
		}
		
		function getCurrentUserLogin() {
			return $this->currentUser()->user_login;
		}
		
		function getCurrentUserId() {
			return $this->currentUser()->ID;
		}
		
		function userGoalLevel($goalID, $userID = null) {
			$DbManager = new HfDbManager();
			if ($userID === null) {
				$userID = $this->getCurrentUserId();
			}
			
			global $wpdb;
			$prefix = $wpdb->prefix;
			$table = 'hf_report';
			$tableName = $prefix . $table;
			$select = 'date';
			$whereLastSuccess = 'goalID = ' . $goalID .
				' AND userID = ' . $userID .
				' AND reportID=(
					SELECT max(reportID) 
					FROM ' . $tableName . 
					' WHERE isSuccessful = 1)';
			$whereLastFail = 'goalID = ' . $goalID .
				' AND userID = ' . $userID .
				' AND reportID=(
					SELECT max(reportID) 
					FROM ' . $tableName . 
					' WHERE NOT isSuccessful = 1)';
					
			$dateOfLastSuccess = strtotime($DbManager->getVar($table, $select, $whereLastSuccess));
			$dateOfLastFail = strtotime($DbManager->getVar($table, $select, $whereLastFail));
			
			$difference = $dateOfLastSuccess - $dateOfLastFail;
			$differenceInDays = $difference / 86400;
			if ($differenceInDays < 0) {
				$differenceInDays = 0;
			}
			$whereLevel = 'target > ' . $differenceInDays . ' ORDER BY target ASC';
			
			return $DbManager->getRow('hf_stage', $whereLevel);
		}
	}
}

?>