<?php

if (!class_exists("HfMailer")) {
	class HfMailer {
		private $UserManager;
        private $Security;
        private $DbConnection;
        private $UrlFinder;
        private $UrlGenerator;
        private $ApiInterface;

        function HfMailer($UrlFinder, $UrlGenerator, $UserManager, $Security, $DbConnection, $ApiInterface) {
            $this->DbConnection = $DbConnection;
			$this->UserManager = $UserManager;
            $this->Security = $Security;
            $this->UrlFinder = $UrlFinder;
            $this->UrlGenerator = $UrlGenerator;
            $this->ApiInterface = $ApiInterface;
		}

        function sendEmailToUser($userID, $subject, $body) {
            $to = $this->ApiInterface->getUserEmail($userID);
            $success = $this->ApiInterface->sendWpEmail($to, $subject, $body);
            $emailID = intval($this->DbConnection->getVar('hf_email', 'max(emailID)'));

            if($success) {
                $this->recordEmail($userID, $subject, $body, $emailID, $to);
                return $emailID;
            } else {
                return false;
            }
        }

        function sendEmailToAddress($address, $subject, $body) {
            $success = $this->ApiInterface->sendWpEmail($address, $subject, $body);
            $emailID = intval($this->DbConnection->getVar('hf_email', 'max(emailID)'));

            if($success) {
                $this->recordEmail(null, $subject, $body, $emailID, $address);
                return $emailID;
            } else {
                return false;
            }
        }

        function sendEmailToUserAndSpecifyEmailID($userID, $subject, $body, $emailID) {
            $to = $this->ApiInterface->getUserEmail($userID);
            $success = $this->ApiInterface->sendWpEmail($to, $subject, $body);

            if($success) {
                $this->recordEmail($userID, $subject, $body, $emailID, $to);
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
			$this->sendEmailToUserAndSpecifyEmailID($userID, $subject, $message, $emailID);
		}
		
		function markAsDelivered( $emailID ) {
			$table = 'hf_email';
			$data = array( 'deliveryStatus' => 1 );
			$where = array( 'emailID' => $emailID );
			$this->DbConnection->updateRows($table, $data, $where);
		}
		
		function sendInvitation( $inviterID, $address, $daysToExpire ) {
			$inviteID			= $this->generateInviteID();
			$inviteURL			= $this->generateInviteURL($inviteID);
			$inviterUsername	= $this->UserManager->getUsernameByID( $inviterID, true );
			$subject			= $inviterUsername . ' just invited you to join them at HabitFree!';
			$body				= "<p>HabitFree is a community of young people striving for God's ideal of purity and Christian freedom.</p><p><a href='" . $inviteURL . "'>Click here to join " . $inviterUsername . " in his quest!</a></p>";
			
			$emailID = $this->sendEmailToAddress($address, $subject, $body);
			
			$expirationDate = date('Y-m-d H:i:s', strtotime('+'.$daysToExpire.' days'));
			
			if ($emailID !== false) {
				$this->recordInvite($inviteID, $inviterID, $address, $emailID, $expirationDate);
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
            $baseURL = $this->UrlFinder->getReportPageURL();
			
			$parameters = array(
					'userID'	=> $userID,
					'emailID'	=> $emailID
				);
			
			return $this->UrlGenerator->addParametersToUrl($baseURL, $parameters);
		}
		
		function generateInviteURL($inviteID) {
			$baseURL = $this->UrlFinder->getURLByTitle('Register');
			
			$parameters = array(
					'n' => $inviteID
				);
			
			return $this->UrlGenerator->addParametersToUrl($baseURL, $parameters);
		}
	}
}
?>