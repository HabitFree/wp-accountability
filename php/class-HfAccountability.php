<?php

if (!class_exists("HfAccountability")) {
	class HfAccountability {
		
		protected static $instance = NULL;
		
		function HfAccountability() { //constructor
			add_action('init', array($this, 'registerShortcodes') );
		}
		
		public static function get_instance() {
			NULL === self::$instance and self::$instance = new self;
			return self::$instance;
		}
		
		function registerShortcodes() {
			add_shortcode( 'hfSubscriptionSettings', array(HfAccountability::get_instance(), 'subscriptionSettingsShortcode') );
			add_shortcode( 'hfReport', array(HfAccountability::get_instance(), 'reportShortcode') );
			add_shortcode( 'userButtons', array(new HfUserManager(), 'userButtonsShortcode') );
		}
		
		function reportShortcode( $atts ) {
			$UserManager = new HfUserManager();
			
			if ( !is_user_logged_in() ) {
				if ( empty($_GET['userID']) || !$this->emailIsValid($_GET['userID'], $_GET['emailID']) ) {
					return $UserManager->requireLogin();
				}
			}
			
			$currentURL = $this->getCurrentPageUrl();
			if ( isset($_GET['userID']) && $this->emailIsValid($_GET['userID'], $_GET['emailID']) ) {
				$userID = $_GET['userID'];
				$Mailer = new HfMailer();
				$Mailer->markAsDelivered($_GET['emailID']);
			} else {
				$userID = $UserManager->getCurrentUserId();
			}
			
			if ( isset($_POST['submit']) ) {
				if ( isset($_GET['emailID']) ) {
					$emailID = $_GET['emailID'];
				} else {
					$emailID = null;
				}
				
				foreach ($_POST as $key => $value) {
					if ($key == 'submit') {
						continue;
					}
					$this->submitAccountabilityReport($userID, $key, $value, $emailID);
				}
				
				return 'Thanks for checking in!';
			} else {
				return $this->accountabilityForm($userID);
			}
		}
		
		private function accountabilityForm($userID) {
			$currentURL = $this->getCurrentPageUrl();
			$DbManager = new HfDbManager();
			$UserManager = new HfUserManager();
			$goalSubs = $DbManager->getRows('hf_user_goal', 'userID = ' . $userID);
			$html = '<form action="'. $currentURL .'" method="post">';
			
			foreach ($goalSubs as $sub) {
				$goalID = $sub->goalID;
				$goal = $DbManager->getRow('hf_goal', 'goalID = ' . $goalID);
				$level = $UserManager->userGoalLevel($goalID, $userID);
				$html .= "<p>Level " . $level->stageID . ": " . $level->title . ":<br />" .
					$goal->title . "<br />" . $UserManager->levelBarForGoal($goalID, $userID) . "<br />
						<span class='status'>
							<label><input type='radio' name='" . $goalID . "' value='1'> Yes</label>
							<label><input type='radio' name='" . $goalID . "' value='0'> No</label>
						</span>
					</p>";
			}
			
			return $html .= '<input type="submit" name="submit" value="Submit" /></form>';
		}
		
		private function submitAccountabilityReport($userID, $goalID, $isSuccessful, $emailID = null) {
			$dbManager = new HfDbManager();
			$data = array(
				'userID' => $userID,
				'goalID' => $goalID,
				'isSuccessful' => $isSuccessful,
				'referringEmailID' => $emailID );
			$dbManager->insertIntoDb('hf_report', $data);
		}
		
		private function emailIsValid($userID, $emailID) {
			$dbManager = new HfDbManager();
			
			$email = $dbManager->getRow('hf_email',
				'userID = ' . $userID .
				' AND emailID = ' . $emailID);
			
			return $email != null;
		}
		
		function settingsShortcode( $atts ) {
			if ( is_user_logged_in() ) {
				return $this->subscriptionSettings() . do_shortcode( '[wppb-edit-profile]' );
			} else {
				$UserManager = new HfUserManager();
				return $UserManager->requireLogin();
			}	
		}
		
		function subscriptionSettingsShortcode() {
			$userManager = new HfUserManager();
			$userID = $userManager->getCurrentUserId();
			$message = '';
			
			if(isset($_POST) && array_key_exists('formSubmit',$_POST)) {
				$varSubscription = isset($_POST['accountability']);
				update_user_meta( $userID, "Subscribed", $varSubscription );
				$message = '<p class="notice">Your changes have been saved.</p>';
			}
			
			if (get_user_meta( $userID, "Subscribed", true )) {
				$additionalProperties = 'checked="checked"';
			} else {
				$additionalProperties = '';
			}
			
			$currentURL = $this->getCurrentPageUrl();
			$html = $message . '<form action="'. $currentURL .'" method="post">
					<p><label>
						<input type="checkbox" name="accountability" value="yes" '. $additionalProperties .' />
						Keep me accountable by email.
					</label></p>
					<input type="submit" name="formSubmit" value="Save changes" />
				</form>';
			return $html;
		}
		
		function getCurrentPageUrl() {
			$pageURL = 'http';
			if( isset($_SERVER["HTTPS"]) ) {
				if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			}
			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			return $pageURL;
		}
		
		function getReportPageURL() {
			return $this->getURLByTitle('Report');
		}
		
		function getURLByTitle($title) {
			$page = get_page_by_title( $title );
			return get_permalink( $page->ID );
		}
		
		function progressBar($percent, $label) {
			return '<div class="meter">
					<span class="label">'.$label.'</span>
					<span class="progress" style="width: '.$percent.'%">'.$label.'</span>
				</div>';
		}
	}
}

?>