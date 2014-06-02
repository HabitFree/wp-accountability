<?php

if (!class_exists("HfMain")) {
	class HfMain {
		
		protected static $instance = NULL;
        private $DbConnection;
        private $UserManager;
        private $Mailer;
        private $URLFinder;
        private $HtmlGenerator;
        private $Goals;
        private $LanguageApi;

        function HfMain($HtmlGenerator, $UserManager, $Mailer, $URLFinder, $DbConnection, $Goals, $LanguageApi) {
            $this->UserManager      = $UserManager;
            $this->Mailer           = $Mailer;
            $this->DbConnection     = $DbConnection;
            $this->URLFinder        = $URLFinder;
            $this->HtmlGenerator    = $HtmlGenerator;
            $this->Goals            = $Goals;
            $this->LanguageApi      = $LanguageApi;

			add_action('init', array($this, 'registerShortcodes') );
		}
		
		public static function get_instance() {
            $WebsiteApi         = new HfWordPressInterface();
            $PHPAPI             = new HfPhpInterface();
            $DbConnection       = new HfDatabase($WebsiteApi, $PHPAPI);
            $HtmlGenerator      = new HfHtmlGenerator();
            $Security           = new HfSecurity();
            $UrlGenerator       = new HfUrlGenerator();
            $UrlFinder          = new HfUrlFinder();
            $Messenger          = new HfMailer($UrlFinder, $UrlGenerator, $Security, $DbConnection, $WebsiteApi);
            $UserManager        = new HfUserManager($DbConnection, $Messenger, $UrlFinder, $WebsiteApi);
            $Goals              = new HfGoals($Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection);
            $LanguageApi        = new HfPhpInterface();

			NULL === self::$instance and self::$instance = new self($HtmlGenerator, $UserManager, $Messenger, $UrlFinder, $DbConnection, $Goals, $LanguageApi);
			return self::$instance;
		}
		
		function registerShortcodes() {
			add_shortcode( 'hfSettings', array(HfMain::get_instance(), 'settingsShortcode') );
			add_shortcode( 'hfGoals', array(HfMain::get_instance(), 'goalsShortcode') );
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
				$goalID = $this->LanguageApi->convertStringToInt($sub->goalID);
				$html .= $this->Goals->generateGoalCard($goalID, $userID);
			}

            return $html .= '<input class="submit" type="submit" name="submit" value="Submit" /></form>';
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
			
			$currentURL = $this->URLFinder->getCurrentPageURL();
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