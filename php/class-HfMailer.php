<?php

if (!class_exists("HfMailer")) {
	class HfMailer {
	
		function HfMailer() { //constructor
			//nothing here
		}
		
		function sendEmail($userID, $subject, $message, $emailID = null) {
			$to = get_userdata( $userID )->user_email;
			$success = wp_mail( $to, $subject, $message );
			if ($success) {
				$this->recordEmail($userID, $subject, $message, $emailID);
			}
		}
		
		function recordEmail($userID, $subject, $message, $emailID = null) {
			$HfDbMain = new HfDbManager();
			$table = "hf_email";
			$data = array( 'subject' => $subject,
				'body' => $message,
				'userID' => $userID,
				'emailID' => $emailID );
			$HfDbMain->insertIntoDb($table, $data);
		}
		
		function sendReportRequestEmails() {
			$UserManager = new HfUserManager();
			$users = get_users(array(
				'meta_key' => 'hfSubscribed',
				'meta_value' => true
			));
			foreach ($users as $user) {
				if ($UserManager->isAnyGoalDue($user->userID)) {
					$this->sendReportRequestEmail($user->userID);
				}
			}
		}
		
		function sendReportRequestEmail($userID) {
			$DbManager = new HfDbManager();
			$subject = "How's it going?";
			$emailID = $DbManager->generateEmailID();
			$reportURL = $this->generateReportURL($userID, $emailID);
			$message = "<p>Time to <a href='" . $reportURL . "'>check in</a>.</p>";
			
			$this->sendEmail($userID, $subject, $message, $emailID);
		}
		
		function generateReportURL($userID, $emailID) {
			$HfMain = new HfAccountability();
			$baseURL = $HfMain->getReportPageURL();
			
			if (strpos($baseURL,'?') !== false) {
				return $baseURL . '&userID=' . $userID . '&emailID=' . $emailID;
			} else {
				return $baseURL . '?userID=' . $userID . '&emailID=' . $emailID;
			}
		}
		
		function markAsDelivered( $emailID ) {
			$DbManager = new HfDbManager();
			$table = 'hf_email';
			$data = array( 'deliveryStatus' => 1 );
			$where = array( 'emailID' => $emailID );
			$DbManager->updateRows($table, $data, $where);
		}
	}
}
?>