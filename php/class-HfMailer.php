<?php

if (!class_exists("HfMailer")) {
	class HfMailer {
	
		function HfMailer() { //constructor
			//nothing here
		}
		
		function sendEmail($userID, $subject, $message, $emailID = null) {
			$to = get_userdata( $userID )->user_email;
			$success = wp_mail( $to, $subject, $message, $headers, $attachments );
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
		
		function sendEmailUpdates() {
			$users = get_users(array(
				'meta_key' => 'Subscribed',
				'meta_value' => true
			));
			foreach ($users as $user) {
				$HfMain = new HfAccountability();
				$DbManager = new HfDbManager();
				$subject = 'Testing HabitFree accountability';
				$userID = $user->ID;
				$emailID = $DbManager->generateEmailID();
				
				$reportURL = $HfMain->getReportPageURL() .
					'&userID=' . $userID .
					'&emailID=' . $emailID;
				$message = "<p>Time to <a href='" . $reportURL . "'>check in</a>.</p>";
				
				$this->sendEmail($userID, $subject, $message, $emailID);
			}
		}
		
		function cronAdd5min( $schedules ) {
			// Adds once weekly to the existing schedules.
			$schedules['5min'] = array(
				'interval' => 5*60,
				'display' => __( 'Once every 5 minutes' )
			);
			return $schedules;
		}
		
		function markAsDelivered( $emailID ) {
			$DbManager = new HfDbManager();
			$table = 'hf_email';
			$data = array( 'deliveryStatus' => 1 );
			$where = array( 'emailID' => $emailID );
			$DbManager->updateRows($table, $data, $where);
		}
		
		function mandrillWebhookShortcode( $atts ) {
			$this->sendEmail(1, 'Webhook Test', 'Trying, trying...');

			if(isset($_POST)) {
				$data = json_decode($_POST['mandrill_events']);
				$message = var_dump($data);
				$this->sendEmail(1, 'Webhook Test Data', $message);
			}
		}
	}
}
?>