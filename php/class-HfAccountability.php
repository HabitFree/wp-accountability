<?php

if (!class_exists("HfAccountability")) {
	class HfAccountability {
		
		protected static $instance = NULL;
        private $DbConnection;
        private $UserManager;
        private $Mailer;
        private $URLFinder;
        private $HtmlGenerator;

        function HfAccountability($HtmlGenerator, $UserManager, $Mailer, $URLFinder, $DbConnection) {
            $this->UserManager = $UserManager;
            $this->Mailer = $Mailer;
            $this->DbConnection = $DbConnection;
            $this->URLFinder = $URLFinder;
            $this->HtmlGenerator = $HtmlGenerator;
			add_action('init', array($this, 'registerShortcodes') );
		}
		
		public static function get_instance() {
            $DbConnection = new HfDbConnection();
            $HtmlGenerator = new HfHtmlGenerator();
            $UserManager = new HfUserManager($DbConnection, $HtmlGenerator);
            $URLFinder = new HfUrlFinder();
            $UrlGenerator = new HfUrlGenerator();
            $Security = new HfSecurity();
            $WordPressInterface = new HfWordPressInterface();
            $Mailer = new HfMailer($URLFinder, $UrlGenerator, $UserManager, $Security, $DbConnection, $WordPressInterface);
			NULL === self::$instance and self::$instance = new self($HtmlGenerator, $UserManager, $Mailer, $URLFinder, $DbConnection);
			return self::$instance;
		}
		
		function registerShortcodes() {
			add_shortcode( 'hfSettings', array(HfAccountability::get_instance(), 'settingsShortcode') );
			add_shortcode( 'hfGoals', array(HfAccountability::get_instance(), 'goalsShortcode') );
			add_shortcode( 'userButtons', array($this->UserManager, 'userButtonsShortcode') );
		}
		
		function goalsShortcode( $atts ) {
			if ( !is_user_logged_in() ) {
				if ( empty($_GET['userID']) || !$this->emailIsValid($_GET['userID'], $_GET['emailID']) ) {
					return $this->UserManager->requireLogin();
				}
			}

			if ( isset($_GET['userID']) && $this->emailIsValid($_GET['userID'], $_GET['emailID']) ) {
				$userID = $_GET['userID'];
				$this->Mailer->markAsDelivered($_GET['emailID']);
			} else {
				$userID = $this->UserManager->getCurrentUserId();
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
				
				return '<p class="success">Thanks for checking in!</p>' . $this->accountabilityForm($userID);
			} else {
				return $this->accountabilityForm($userID);
			}
		}
		
		private function accountabilityForm($userID) {
			$currentURL = $this->URLFinder->getCurrentPageURL();
			$goalSubs = $this->DbConnection->getRows('hf_user_goal', 'userID = ' . $userID);
			$html = '<form class="report-cards" action="'. $currentURL .'" method="post">';

			foreach ($goalSubs as $sub) {
				$goalID = $sub->goalID;
				$html .= $this->generateGoalCard($goalID, $userID);
			}

			return $html .= '<input class="submit" type="submit" name="submit" value="Submit" /></form>';
		}
		
		private function generateGoalCard($goalID, $userID) {

			$goal = $this->DbConnection->getRow('hf_goal', 'goalID = ' . $goalID);
			$level = $this->UserManager->userGoalLevel($goalID, $userID);
			$wrapperOpen = '<div class="report-card">';
			$info = '<div class="info"><h2>'.$goal->title.'</h2>';
			if ($goal->description != '') {
				$info .= '<p>'.$goal->description.'</p></div>';
			} else {
				$info .= '</div>';
			}

			$controls = "<div class='controls'>
					<label><input type='radio' name='" . $goalID . "' value='0'> &#x2714;</label>
					<label><input type='radio' name='" . $goalID . "' value='1'> &#x2718;</label>
				</div>";
			$report = "<div class='report'>Have you fallen since your last check-in?".$controls."</div>";
			$main = '<div class="main">' . $info . $report . '</div>';
			$stat1 = '<p class="stat">Level <span class="number">'.$level->levelID.'</span> '.$level->title.'</p>';
			$stat2 = '<p class="stat">Level <span class="number">'.round($this->UserManager->levelPercentComplete($goalID, $userID), 1).'%</span> Complete</p>';
            $stat3 = '<p class="stat">Days to <span class="number">'.round($this->UserManager->daysToNextLevel($goalID, $userID)).'</span> Next Level</p>';
            $bar = $this->UserManager->levelBarForGoal($goalID, $userID);
			$stats = '<div class="stats">' . $stat1 . $stat2 . $stat3 . $bar . '</div>';
			$wrapperClose = '</div>';

			return $wrapperOpen . $main . $stats . $wrapperClose;
		}
		
		private function submitAccountabilityReport($userID, $goalID, $isSuccessful, $emailID = null) {
			$data = array(
				'userID' => $userID,
				'goalID' => $goalID,
				'isSuccessful' => $isSuccessful,
				'referringEmailID' => $emailID );
			$this->DbConnection->insertIntoDb('hf_report', $data);
		}
		
		private function emailIsValid($userID, $emailID) {
			$email = $this->DbConnection->getRow('hf_email',
				'userID = ' . $userID .
				' AND emailID = ' . $emailID);
			
			return $email != null;
		}
		
		function settingsShortcode( $atts ) {
			if ( is_user_logged_in() ) {
				$html = '[su_tabs]
						[su_tab title="Subscriptions"]'.$this->subscriptionSettings().'[/su_tab]
						[su_tab title="Account"][wppb-edit-profile][/su_tab]
					[/su_tabs]';
				return do_shortcode( $html );
			} else {
				return $this->UserManager->requireLogin();
			}
		}
		
		function subscriptionSettings() {
			$userID = $this->UserManager->getCurrentUserId();
			$message = '';
			
			if(isset($_POST) && array_key_exists('formSubmit',$_POST)) {
				$varSubscription = isset($_POST['accountability']);
				update_user_meta( $userID, "hfSubscribed", $varSubscription );
				$message = '<p class="success">Your changes have been saved.</p>';
			}
			
			if (get_user_meta( $userID, "hfSubscribed", true )) {
				$additionalProperties = 'checked="checked"';
			} else {
				$additionalProperties = '';
			}
			
			$currentURL = $this->getCurrentPageURL();
			$html = $message . '<form action="'. $currentURL .'" method="post">
					<p><label>
						<input type="checkbox" name="accountability" value="yes" '. $additionalProperties .' />
						Keep me accountable by email.
					</label></p>
					<input type="submit" name="formSubmit" value="Save changes" />
				</form>';
			return $html;
		}
	}
}

?>