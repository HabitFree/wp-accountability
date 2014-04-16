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
			$invitationURL		= $this->generateInviteURL($inviteID);
			$inviterUsername	= $UserManager->getUsernameByID( $inviterID, true );
			$subject			= $inviterUsername . ' just invited you to join them at HabitFree!';
			$body				= "<p>HabitFree is a community of young people striving for God's ideal of purity and Christian freedom.</p><p><a href='" . $invitationURL . "'>Click here to join " . $inviterUsername . " in his quest!</a></p>";
			
			$this->sendEmail(null, $subject, $body, null, $destinationEmail);
			return $inviteID;
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