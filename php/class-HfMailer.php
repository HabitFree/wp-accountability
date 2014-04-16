<?php

if (!class_exists("HfMailer")) {
	class HfMailer {
	
		function HfMailer() { //constructor
			//nothing here
		}
		
		function sendEmail($userID, $subject, $message, $emailID = null, $emailAddress = null) {
			if ($emailAddress != null) {
				$to = $emailAddress;
			} else {
				$to = get_userdata( $userID )->user_email;
			}
			$success = wp_mail( $to, $subject, $message );
			
			if ($success) {
				$this->recordEmail($userID, $subject, $message, $emailID, $emailAddress);
			}
			
			if ($emailID === null) {
				$DbManager = new HfDbManager();
				$emailID = intval($DbManager->getVar('hf_email', 'max(emailID)'));
			}
			
			if ($success) {
				return $emailID;
			} else {
				return false;
			}
			
		}
		
		function recordEmail($userID, $subject, $message, $emailID = null, $emailAddress = null) {
			$HfDbMain = new HfDbManager();
			$table = "hf_email";
			$data = array( 'subject' => $subject,
				'body' => $message,
				'userID' => $userID,
				'emailID' => $emailID,
				'address' => $emailAddress );
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
		
		function markAsDelivered( $emailID ) {
			$DbManager = new HfDbManager();
			$table = 'hf_email';
			$data = array( 'deliveryStatus' => 1 );
			$where = array( 'emailID' => $emailID );
			$DbManager->updateRows($table, $data, $where);
		}
		
		function sendInvitation( $inviterID, $destinationEmail, $daysToExpire ) {
			$UserManager		= new HfUserManager();
			$inviteID			= $this->generateInviteID();
			$inviteURL			= $this->generateInviteURL($inviteID);
			$inviterUsername	= $UserManager->getUsernameByID( $inviterID, true );
			$subject			= $inviterUsername . ' just invited you to join them at HabitFree!';
			$body				= "<p>HabitFree is a community of young people striving for God's ideal of purity and Christian freedom.</p><p><a href='" . $inviteURL . "'>Click here to join " . $inviterUsername . " in his quest!</a></p>";
			
			$emailID = $this->sendEmail(null, $subject, $body, null, $destinationEmail);
			
			$expirationDate = date('Y-m-d H:i:s', strtotime('+'.$daysToExpire.' days'));
			
			if ($emailID !== false) {
				$this->recordInvite($inviteID, $inviterID, $destinationEmail, $emailID, $expirationDate);
			}
			
			return $inviteID;
		}
		
		function recordInvite($inviteID, $inviterID, $inviteeEmail, $emailID, $expirationDate) {
			$HfDbMain = new HfDbManager();
			$table = "hf_invite";
			$data = array( 'inviteID' => $inviteID,
				'inviterID' => $inviterID,
				'inviteeEmail' => $inviteeEmail,
				'emailID' => $emailID,
				'expirationDate' => $expirationDate );
			return $HfDbMain->insertIntoDb($table, $data);
		}
		
		function generateInviteID() {
			$HfMain = new HfAccountability();
			return $HfMain->createRandomString(250);
		}
		
		function generateReportURL($userID, $emailID) {
			$HfMain = new HfAccountability();
			$baseURL = $HfMain->getReportPageURL();
			
			$parameters = array(
					'userID'	=> $userID,
					'emailID'	=> $emailID
				);
			
			return $this->urlPlusParameters($baseURL, $parameters);
		}
		
		function generateInviteURL($inviteID) {
			$HfMain = new HfAccountability();
			$baseURL = $HfMain->getURLByTitle('Register');
			
			$parameters = array(
					'n' => $inviteID
				);
			
			return $this->urlPlusParameters($baseURL, $parameters);
		}
		
		function urlPlusParameters($url, $parameters) {
			$name = key($parameters);
			$value = array_shift($parameters);
			
			if (strpos($url,'?') !== false) {
				$url .= '&' . $name . '=' . $value;
			} else {
				$url .= '?' . $name . '=' . $value;
			}
			
			if ( count($parameters) > 0 ) {
				$url = urlPlusParameters($url, $parameters);
			}
			
			return $url;
		}
	}
}
?>