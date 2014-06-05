<?php

if (!class_exists("HfMailer")) {
	class HfMailer {
        private $Security;
        private $DbConnection;
        private $UrlFinder;
        private $UrlGenerator;
        private $ContentManagementSystem;

        function HfMailer($UrlFinder, $UrlGenerator, $Security, $DbConnection, Hf_iContentManagementSystem $ContentManagementSystem) {
            $this->DbConnection = $DbConnection;
            $this->Security = $Security;
            $this->UrlFinder = $UrlFinder;
            $this->UrlGenerator = $UrlGenerator;
            $this->ContentManagementSystem = $ContentManagementSystem;
		}

        function sendEmailToUser($userID, $subject, $body) {
            $to = $this->ContentManagementSystem->getUserEmail($userID);
            $this->ContentManagementSystem->sendWpEmail($to, $subject, $body);
            $emailID = intval($this->ContentManagementSystem->getVar('hf_email', 'max(emailID)'));

            $this->DbConnection->recordEmail($userID, $subject, $body, $emailID, $to);
        }

        function sendEmailToAddress($address, $subject, $body) {
            $success = $this->ContentManagementSystem->sendWpEmail($address, $subject, $body);
            $emailID = $this->DbConnection->idOfLastEmail();

            if($success) {
                $this->DbConnection->recordEmail(null, $subject, $body, $emailID, $address);
                return $emailID;
            } else {
                return false;
            }
        }

        function sendReportRequestEmail($userID) {
            $subject = "How's it going?";
            $emailID = $this->DbConnection->generateEmailID();
            $reportURL = $this->generateReportURL($userID, $emailID);
            $message = "<p>Time to <a href='" . $reportURL . "'>check in</a>.</p>";
            $this->sendEmailToUserAndSpecifyEmailID($userID, $subject, $message, $emailID);
        }

        function sendEmailToUserAndSpecifyEmailID($userID, $subject, $body, $emailID) {
            $to = $this->ContentManagementSystem->getUserEmail($userID);
            $success = $this->ContentManagementSystem->sendWpEmail($to, $subject, $body);

            if($success) {
                $this->DbConnection->recordEmail($userID, $subject, $body, $emailID, $to);
                return $emailID;
            } else {
                return false;
            }
        }
		
		function markAsDelivered( $emailID ) {
			$table = 'hf_email';
			$data = array( 'deliveryStatus' => 1 );
			$where = array( 'emailID' => $emailID );
			$this->DbConnection->updateRows($table, $data, $where);
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

        public function isThrottled($userID) {
            if ($this->hasNotRespondedSinceLastEmail($userID)) {
                return !$this->emailIntervalExceeded($userID);
            } else {
                return false;
            }
        }

        private function hasNotRespondedSinceLastEmail($userID) {
            $daysSinceLastReport        = $this->DbConnection->daysSinceAnyReport($userID);
            $daysSinceLastEmail         = $this->DbConnection->daysSinceLastEmail($userID);

            return $daysSinceLastReport > $daysSinceLastEmail;
        }

        private function daysBetweenLastTwoEmails($userID) {
            $daysSinceLastEmail         = $this->DbConnection->daysSinceLastEmail($userID);
            $daysSinceSecondToLastEmail = $this->DbConnection->daysSinceSecondToLastEmail($userID);

            return $daysSinceSecondToLastEmail - $daysSinceLastEmail;
        }

        private function emailIntervalExceeded($userID) {
            $daysSinceLastEmail = $this->DbConnection->daysSinceLastEmail($userID);
            $currentInterval = $this->currentInterval($userID);

            return $daysSinceLastEmail > $currentInterval;
        }

        private function currentInterval($userID) {
            $lastInterval = $this->daysBetweenLastTwoEmails($userID);
            $currentInterval = $lastInterval * 2;

            return $currentInterval;
        }
    }
}
?>