<?php

if (!class_exists("HfUserManager")) {
	class HfUserManager {
		private $DbConnection;
        private $HtmlGenerator;

        function HfUserManager($DbConnection, $HtmlGenerator) {
            $this->HtmlGenerator = $HtmlGenerator;
			$this->DbConnection = $DbConnection;
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
		
		function processNewUser($userID) {
            $UserManager = new HfUserManager($this->DbConnection, $this->HtmlGenerator);
            $URLFinder = new HfUrlFinder();
            $UrlGenerator = new HfUrlGenerator();
            $Security = new HfSecurity();
            $Mailer = new HfMailer($URLFinder, $UrlGenerator, $UserManager, $Security, $this->DbConnection);
            $HtmlGenerator = new HfHtmlGenerator();
			$HfMain = new HfAccountability($HtmlGenerator, $UserManager, $Mailer, $URLFinder, $this->DbConnection);
			$table = "hf_user_goal";
			$data = array( 'userID' => $userID,
				'goalID' => 1 );
			$this->DbConnection->insertIgnoreIntoDb($table, $data);
			$settingsPageURL = $HfMain->getURLByTitle('Settings');
			$message = "<p>Welcome to HabitFree! 
				You've been subscribed to periodic accountability emails. 
				You can <a href='".$settingsPageURL."'>edit your subscription settings by clicking here</a>.</p>";
			$Mailer->sendEmailToUser($userID, 'Welcome!', $message);
		}
		
		function getCurrentUserLogin() {
			return $this->currentUser()->user_login;
		}
		
		function getCurrentUserId() {
			return $this->currentUser()->ID;
		}
		
		function userGoalLevel($goalID, $userID) {		
			$daysOfSuccess = $this->daysOfSuccess($goalID, $userID);	
			$whereLevel = 'target > ' . $daysOfSuccess . ' ORDER BY target ASC';
			return $this->DbConnection->getRow('hf_level', $whereLevel);
		}
		
		function daysToNextLevel($goalID, $userID) {
			$daysOfSuccess = $this->daysOfSuccess($goalID, $userID);
			$target = $this->currentLevelTarget($daysOfSuccess);
			return $target - $daysOfSuccess;
		}
		
		function currentLevelTarget($daysOfSuccess) {
			$whereCurrentLevel = 'target > ' . $daysOfSuccess . ' ORDER BY target ASC';
			return $this->DbConnection->getVar('hf_level', 'target', $whereCurrentLevel);
		}
		
		function levelBarForGoal($goalID, $userID) {
            $percent = $this->levelPercentComplete($goalID, $userID);
			return $this->HtmlGenerator->progressBar($percent, '');
		}
		
		function nextLevelName($daysOfSuccess) {
			$whereCurrentLevel = 'target > ' . $daysOfSuccess . ' ORDER BY target ASC';
			$currentLevelID = $this->DbConnection->getVar('hf_level', 'levelID', $whereCurrentLevel);
			$whereNextLevel = 'levelID = ' . ($currentLevelID + 1);
			return $this->DbConnection->getVar('hf_level', 'title', $whereNextLevel);
		}
		
		function levelPercentComplete($goalID, $userID) {
			$daysOfSuccess = $this->daysOfSuccess($goalID, $userID);
			return ($this->daysOfSuccess($goalID, $userID) / $this->currentLevelTarget($daysOfSuccess)) * 100;
		}

		function daysOfSuccess($goalID, $userID) {
			global $wpdb;
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
			
			$dateInSecondsOfFirstSuccess = strtotime($this->DbConnection->getVar($table, $select, $whereFirstSuccess));
			$dateInSecondsOfLastSuccess = strtotime($this->DbConnection->getVar($table, $select, $whereLastSuccess));
			$dateInSecondsOfLastFail = strtotime($this->DbConnection->getVar($table, $select, $whereLastFail));
			
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
			$URLFinder = new HfUrlFinder();
			$welcome = 'Welcome back, ' . $this->getCurrentUserLogin() . ' | ';
			$logInOutLink = wp_loginout( $URLFinder->getCurrentPageUrl(), false );
			if ( is_user_logged_in() ) {
				$settingsURL = $URLFinder->getURLByTitle('Settings');
				return $welcome . $logInOutLink . ' | <a href="'.$settingsURL.'">Settings</a>';
			} else {
				$registerURL = $URLFinder->getURLByTitle('Register');
				return $logInOutLink . ' | <a href="'.$registerURL.'">Register</a>';
			}
				
		}

		function requireLogin() {
			return 'You must be logged in to view this page. ' . wp_login_form( array('echo' => false) );
		}
		
		function isAnyGoalDue($userID) {
			$goalSubs = $this->DbConnection->getRows('hf_user_goal', 'userID = ' . $userID);
			foreach ($goalSubs as $goalSub) {
				if ($this->isGoalDue($goalSub->goalID, $userID)) {
					return true;
				}
			}
			return false;
		}
		
		function isGoalDue($goalID, $userID) {
			$level = $this->userGoalLevel($goalID, $userID);
			$emailInterval = $level->emailInterval;
			$daysSinceLastReport = $this->daysSinceLastReport($goalID, $userID);
			return $daysSinceLastReport > $emailInterval;
		}
		
		function daysSinceLastReport($goalID, $userID) {
			global $wpdb;
			$prefix = $wpdb->prefix;
			$table = 'hf_report';
			$tableName = $prefix . $table;
			$whereLastReport = 'goalID = ' . $goalID .
				' AND userID = ' . $userID .
				' AND reportID=( SELECT max(reportID) FROM '.$tableName.' )';
			$dateInSecondsOfLastReport = strtotime($this->DbConnection->getVar('hf_report', 'date', $whereLastReport));
			$secondsInADay = 86400;
			return ( time() - $dateInSecondsOfLastReport ) / $secondsInADay;
		}

		function getUsernameByID($userID, $initialCaps = false) {
			$user = get_userdata( $userID );
			if ($initialCaps === true) {
				return ucwords($user->user_login);
			} else {
				return $user->user_login;
			}
		}
	}
}

?>