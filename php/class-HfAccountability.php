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
			add_shortcode( 'hfSettings', array(HfAccountability::get_instance(), 'settingsShortcode') );
			add_shortcode( 'hfGoals', array(HfAccountability::get_instance(), 'goalsShortcode') );
			add_shortcode( 'userButtons', array(new HfUserManager(), 'userButtonsShortcode') );
		}
		
		function goalsShortcode( $atts ) {
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
				
				return '<p class="success">Thanks for checking in!</p>' . $this->accountabilityForm($userID);
			} else {
				return $this->accountabilityForm($userID);
			}
		}
		
		private function accountabilityForm($userID) {
			$currentURL = $this->getCurrentPageUrl();
			$DbManager = new HfDbManager();
			$goalSubs = $DbManager->getRows('hf_user_goal', 'userID = ' . $userID);
			$html = '<form class="report-cards" action="'. $currentURL .'" method="post">';
			
			foreach ($goalSubs as $sub) {
				$goalID = $sub->goalID;
				$html .= $this->generateGoalCard($goalID, $userID);
			}
			
			return $html .= '<input class="submit" type="submit" name="submit" value="Submit" /></form>';
		}
		
		private function generateGoalCard($goalID, $userID) {
			$DbManager = new HfDbManager();
			$UserManager = new HfUserManager();
			$goal = $DbManager->getRow('hf_goal', 'goalID = ' . $goalID);
			$level = $UserManager->userGoalLevel($goalID, $userID);
			
			$wrapperOpen = '<div class="report-card">';
			$info = '<div class="info"><h2>'.$goal->title.'</h2>';
			if ($goal->description != '') {
				$info .= '<p>'.$goal->description.'</p></div>';
			} else {
				$info .= '</div>';
			}
			$stat1 = '<p class="stat">Level <span class="number">'.$level->levelID.'</span> '.$level->title.'</p>';
			$stat2 = '<p class="stat">Level <span class="number">'.round($UserManager->levelPercentComplete($goalID, $userID), 1).'%</span> Complete</p>';
			$stat3 = '<p class="stat">Days to <span class="number">'.round($UserManager->daysToNextLevel($goalID, $userID)).'</span> Next Level</p>';
			$bar = $UserManager->levelBarForGoal($goalID, $userID);
			$stats = '<div class="stats">' . $stat1 . $stat2 . $stat3 . $bar . '</div>';
			$controls = "<div class='controls'>
					<label><input type='radio' name='" . $goalID . "' value='0'> &#x2714;</label>
					<label><input type='radio' name='" . $goalID . "' value='1'> &#x2718;</label>
				</div>";
			$report = "<div class='report'>Have you fallen since your last check-in?".$controls."</div>";
			$wrapperClose = '</div>';
			
			return $wrapperOpen . $info . $stats . $report . $wrapperClose;
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
				$html = '[su_tabs]
						[su_tab title="Subscriptions"]'.$this->subscriptionSettings().'[/su_tab]
						[su_tab title="Account"][wppb-edit-profile][/su_tab]
					[/su_tabs]';
				return do_shortcode( $html );
			} else {
				$UserManager = new HfUserManager();
				return $UserManager->requireLogin();
			}
		}
		
		function subscriptionSettings() {
			$userManager = new HfUserManager();
			$userID = $userManager->getCurrentUserId();
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
			return $this->getURLByTitle('Goals');
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
		
		function donutChart($percent, $label) {
			$percent = $this->roundToMultiple($percent, 5);
			return '<div class="half_pie">
				    <div class="half_part_pie_one half_bar_color half_percentage" data-percentage="'.$percent.'"></div>
				    <div class="half_part_pie_two"></div>
				    <div class="half_part_pie_three"></div>	<span class="half_pie_icon iconfont-android"></span>
				</div>';
		}
		
		function roundToMultiple($number, $multiple) {
			return round($number/$multiple) * $multiple;
		}
		
		function createNonce($action, $lifeInSeconds = null) {
			$DbManager = new HfDbManager();
			$salt = wp_nonce_tick();
			$nonce = substr(wp_hash($salt . $action, 'nonce'), -12, 10);
			$DbManager->insertIntoDb('hf_nonce', array(
				'nonce' => $nonce,
				'salt' => $salt,
				'lifeInSeconds' => $lifeInSeconds));
			return $nonce;
		}
		
		function verifyNonce($nonce, $action) {
			$i = wp_nonce_tick();
		
			// Nonce generated 0-12 hours ago
			if ( substr(wp_hash($i . $action, 'nonce'), -12, 10) === $nonce )
				return 1;
			// Nonce generated 12-24 hours ago
			if ( substr(wp_hash(($i - 1) . $action, 'nonce'), -12, 10) === $nonce )
				return 2;
			// Invalid nonce
			return false;
		}

		function sudoReactivateExtension() {
			hfDeactivate();
			hfActivate();
		}
	}
}

?>