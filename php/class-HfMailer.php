<?php

if (!class_exists("HfMailer")) {
	class HfMailer {
		private $UserManager;
        private $Security;
        private $DbConnection;
        private $Url;

        function HfMailer($Url, $UserManager, $Security, $DbConnection) {
            $this->DbConnection = $DbConnection;
			$this->UserManager = $UserManager;
            $this->Security = $Security;
            $this->Url = $Url;
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
				$emailID = intval($this->DbConnection->getVar('hf_email', 'max(emailID)'));
			}
			
			if ($success) {
				return $emailID;
			} else {
				return false;
			}
			
		}
		
		function recordEmail($userID, $subject, $message, $emailID = null, $emailAddress = null) {
			$table = "hf_email";
			$data = array( 'subject' => $subject,
				'body' => $message,
				'userID' => $userID,
				'emailID' => $emailID,
				'address' => $emailAddress );
			$this->DbConnection->insertIntoDb($table, $data);
		}
		
		function sendReportRequestEmails() {
			$users = get_users(array(
				'meta_key' => 'hfSubscribed',
				'meta_value' => true
			));
			foreach ($users as $user) {
				if ($this->UserManager->isAnyGoalDue($user->ID)) {
					$this->sendReportRequestEmail($user->ID);
				}
			}
		}
		
		function sendReportRequestEmail($userID) {
			$subject = "How's it going?";
			$emailID = $this->DbConnection->generateEmailID();
			$reportURL = $this->generateReportURL($userID, $emailID);
			$message = "<p>Time to <a href='" . $reportURL . "'>check in</a>.</p>";
			$this->sendEmail($userID, $subject, $message, $emailID);
		}
		
		function markAsDelivered( $emailID ) {
			$table = 'hf_email';
			$data = array( 'deliveryStatus' => 1 );
			$where = array( 'emailID' => $emailID );
			$this->DbConnection->updateRows($table, $data, $where);
		}
		
		function sendInvitation( $inviterID, $destinationEmail, $daysToExpire ) {
			$inviteID			= $this->generateInviteID();
			$inviteURL			= $this->generateInviteURL($inviteID);
			$inviterUsername	= $this->UserManager->getUsernameByID( $inviterID, true );
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
			$table = "hf_invite";
			$data = array( 'inviteID' => $inviteID,
				'inviterID' => $inviterID,
				'inviteeEmail' => $inviteeEmail,
				'emailID' => $emailID,
				'expirationDate' => $expirationDate );
			return $this->DbConnection->insertIntoDb($table, $data);
		}
		
		function generateInviteID() {
			return $this->Security->createRandomString(250);
		}
		
		function generateReportURL($userID, $emailID) {
            $baseURL = $this->Url->getReportPageURL();
			
			$parameters = array(
					'userID'	=> $userID,
					'emailID'	=> $emailID
				);
			
			return $this->Url->addParametersToUrl($baseURL, $parameters);
		}
		
		function generateInviteURL($inviteID) {
			$baseURL = $this->Url->getURLByTitle('Register');
			
			$parameters = array(
					'n' => $inviteID
				);
			
			return $this->Url->addParametersToUrl($baseURL, $parameters);
		}
	}
}
?>