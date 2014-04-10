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
			
			$daysOfSuccess = $this->daysOfSuccess($goalID, $userID);
			
			$whereLevel = 'target > ' . $daysOfSuccess . ' ORDER BY target ASC';
			return $DbManager->getRow('hf_stage', $whereLevel);
		}
		
		function daysToNextLevel($goalID, $userID = null) {
			if ($userID === null) {
				$userID = $this->getCurrentUserId();
			}
			$daysOfSuccess = $this->daysOfSuccess($goalID, $userID);
			$target = $this->currentLevelTarget($daysOfSuccess);
			return $target - $daysOfSuccess;
		}
		
		function currentLevelTarget($daysOfSuccess) {
			$DbManager = new HfDbManager();
			$whereCurrentLevel = 'target > ' . $daysOfSuccess . ' ORDER BY target ASC';
			return $DbManager->getVar('hf_stage', 'target', $whereCurrentLevel);
		}
		
		function levelBarForGoal($goalID, $userID = null) {
			$HfMain = new HfAccountability();
			if ($userID === null) {
				$userID = $this->getCurrentUserId();
			}
			$percent = $this->levelPercentComplete($goalID, $userID);
			$label = round($percent, 1) . '% Complete—' . round($this->daysToNextLevel($goalID, $userID)) .
				' Days to Next Level: ' . $this->nextLevelName($daysOfSuccess);
			return $HfMain->progressBar($percent, $label);
		}
		
		function nextLevelName($daysOfSuccess) {
			$DbManager = new HfDbManager();
			$daysOfSuccess = $this->daysOfSuccess($goalID);
			$whereCurrentLevel = 'target > ' . $daysOfSuccess . ' ORDER BY target ASC';
			$currentLevelID = $DbManager->getVar('hf_stage', 'stageID', $whereCurrentLevel);
			$whereNextLevel = 'stageID = ' . ($currentLevelID + 1);
			return $DbManager->getVar('hf_stage', 'title', $whereNextLevel);
		}
		
		function levelPercentComplete($goalID, $userID = null) {
			if ($userID === null) {
				$userID = $this->getCurrentUserId();
			}
			$daysOfSuccess = $this->daysOfSuccess($goalID, $userID);
			return ($this->daysOfSuccess($goalID, $userID) / $this->currentLevelTarget($daysOfSuccess)) * 100;
		}

		function daysOfSuccess($goalID, $userID = null) {
			if ($userID === null) {
				$userID = $this->getCurrentUserId();
			}
			
			global $wpdb;
			$DbManager = new HfDbManager();
			$prefix = $wpdb->prefix;
			$table = 'hf_report';
			$tableName = $prefix . $table;
			$select = 'date';
			
			$whereFirstSuccess = 'goalID = ' . $goalID .
				' AND userID = ' . $userID .
				' AND reportID=(
					SELECT min(reportID) 
					FROM ' . $tableName . 
					' WHERE isSuccessful = 1)';
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
			
			$dateInSecondsOfFirstSuccess = strtotime($DbManager->getVar($table, $select, $whereFirstSuccess));
			$dateInSecondsOfLastSuccess = strtotime($DbManager->getVar($table, $select, $whereLastSuccess));
			$dateInSecondsOfLastFail = strtotime($DbManager->getVar($table, $select, $whereLastFail));
			
			$secondsInADay = 86400;
			
			if (!$dateInSecondsOfLastSuccess) {
				$daysOfSuccess = 0;
			} elseif (!$dateInSecondsOfLastFail) {
				$daysOfSuccess = ($dateInSecondsOfLastSuccess - $dateInSecondsOfFirstSuccess) / $secondsInADay;
			} else {
				$difference = $dateInSecondsOfLastSuccess - $dateInSecondsOfLastFail;
				$daysOfSuccess = $difference / $secondsInADay;
				if ($daysOfSuccess < 0) {
					$daysOfSuccess = 0;
				}
			}
			
			return $daysOfSuccess;
		}
		
		function userButtonsShortcode() {
			$HfMain = new HfAccountability();
			$welcome = 'Welcome back, ' . $this->getCurrentUserLogin() . ' | ';
			$logInOutLink = wp_loginout( $HfMain->getCurrentPageUrl(), false );
			if ( is_user_logged_in() ) {
				$settingsURL = $HfMain->getURLByTitle('Settings');
				return $welcome . $logInOutLink . ' | <a href="'.$settingsURL.'">Settings</a>';
			} else {
				$registerURL = $HfMain->getURLByTitle('Register');
				return $logInOutLink . ' | <a href="'.$registerURL.'">Register</a>';
			}
				
		}

		function requireLogin() {
			return 'You must be logged in to view this page. ' . wp_login_form( array('echo' => false) );
		}
	}
}

?>