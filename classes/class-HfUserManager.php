<?php

if (!class_exists("HfUserManager")) {
	class HfUserManager {
		private $DbConnection;
        private $Messenger;
        private $UrlFinder;
        private $ContentManagementSystem;
        private $CodeLibrary;

        function HfUserManager($Database, $Messenger, $UrlFinder, Hf_iContentManagementSystem $ContentManagementSystem, Hf_iCodeLibrary $CodeLibrary) {
			$this->DbConnection             = $Database;
            $this->Messenger                = $Messenger;
            $this->UrlFinder                = $UrlFinder;
            $this->ContentManagementSystem  = $ContentManagementSystem;
            $this->CodeLibrary              = $CodeLibrary;
		}
		
		function processAllUsers() {
			$users = get_users();
            foreach ($users as $user) {
                $this->processNewUser($user->ID);
            }
		}
		
		function processNewUser($userID) {
			$table = "hf_user_goal";
			$data = array( 'userID' => $userID,
				'goalID' => 1 );
            $this->DbConnection->insertIgnoreIntoDb($table, $data);
            $settingsPageURL = $this->UrlFinder->getURLByTitle('Settings');
            $message = "<p>Welcome to HabitFree!
				You've been subscribed to periodic accountability emails. 
				You can <a href='".$settingsPageURL."'>edit your subscription settings by clicking here</a>.</p>";
			$this->Messenger->sendEmailToUser($userID, 'Welcome!', $message);
		}
		
		function getCurrentUserLogin() {
			return $this->ContentManagementSystem->currentUser()->user_login;
		}
		
		function getCurrentUserId() {
			return $this->ContentManagementSystem->currentUser()->ID;
		}
		
		function userButtonsShortcode() {
			$URLFinder = new HfUrlFinder();
			$welcome = 'Welcome back, ' . $this->getCurrentUserLogin() . ' | ';
			$logInOutLink = wp_loginout( $URLFinder->getCurrentPageUrl(), false );
			if ( is_user_logged_in() ) {
				$settingsURL = $URLFinder->getURLByTitle('Settings');
				return $welcome . $logInOutLink . ' | <a href="'.$settingsURL.'">Settings</a>';
			} else {
				$registerURL = $URLFinder->getURLByTitle('Register');
				return $logInOutLink . ' | <a href="'.$registerURL.'">Register</a>';
			}
				
		}

		function requireLogin() {
			return 'You must be logged in to view this page. ' . wp_login_form( array('echo' => false) );
		}

		function getUsernameByID($userID, $initialCaps = false) {
			$user = get_userdata( $userID );
			if ($initialCaps === true) {
				return ucwords($user->user_login);
			} else {
				return $user->user_login;
			}
		}

        function sendInvitation( $inviterID, $address, $daysToExpire ) {
            $inviteID			= $this->Messenger->generateInviteID();
            $inviteURL			= $this->Messenger->generateInviteURL($inviteID);
            $inviterUsername	= $this->getUsernameByID( $inviterID, true );
            $subject			= $inviterUsername . ' just invited you to partner with them at HabitFree!';
            $body				= "<p>" . $inviterUsername . " would like to become accountability partners with you on HabitFree. HabitFree is a community of young people striving for God's ideal of purity and Christian freedom.</p><p><a href='" . $inviteURL . "'>Click here to join " . $inviterUsername . " in his quest!</a></p>";

            $emailID = $this->Messenger->sendEmailToAddress($address, $subject, $body);

            $expirationDate = date('Y-m-d H:i:s', strtotime('+'.$daysToExpire.' days'));

            if ($emailID !== false) {
                $this->Messenger->recordInvite($inviteID, $inviterID, $address, $emailID, $expirationDate);
            }

            return $inviteID;
        }

        public function registerShortcode() {
            $html       = '';
            $currentUrl = $this->UrlFinder->getCurrentPageUrl();
            $Form       = new HfGenericForm($currentUrl);

            $username   = '';
            $email      = '';

            if ( isset($_POST['submit']) ) {
                if ( $this->isRegistrationFormValid() ) {
                    $this->registerNewUser($_POST['username'], $_POST['password'], $_POST['email']);
                    return '<p class="success">You have been successfully registered. Welcome to HabitFree!</p>
                        <p><a href="/">Onward</a></p>';
                }

                $html       .= $this->getRegistrationFormErrors();
                $username   = $_POST['username'];
                $email      = $_POST['email'];
            }

            $Form->addTextBox('username', 'Username', $username, true);
            $Form->addTextBox('email', 'Email', $email, true);
            $Form->addPasswordBox('password', 'Password', true);
            $Form->addPasswordBox('passwordConfirmation', 'Confirm Password', true);
            $Form->addSubmitButton('submit', 'Register');

            $html .= $Form->getHtml();

            return $html;
        }

        private function isRegistrationFormValid() {
            return ( !empty($_POST['username'])
                and !empty($_POST['email'])
                and filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)
                and !empty($_POST['password'])
                and !empty($_POST['passwordConfirmation'])
                and $_POST['password'] === $_POST['passwordConfirmation'] )
                and !username_exists( $_POST['username'] );
        }

        private function getRegistrationFormErrors() {
            $html = '';

            if ( empty($_POST['username']) ) {
                $html .= '<p class="fail">Please provide a username.</p>';
            }

            if ( username_exists( $_POST['username'] ) ) {
                $html .= '<p class="fail">Sorry, that username is already taken. Please select another.</p>';
            }

            if ( empty($_POST['email']) or !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) {
                $html .= '<p class="fail">Please provide a valid email address.</p>';
            }

            if ( empty($_POST['password']) ) {
                $html .= '<p class="fail">Please provide a password.</p>';
            }

            if ( empty($_POST['passwordConfirmation']) ) {
                $html .= '<p class="fail">Please confirm your password.</p>';
            }

            if ( $_POST['password'] !== $_POST['passwordConfirmation'] ) {
                $html .= "<p class='fail'>Those passwords don't match. Please retype and confirm your desired password.</p>";
            }

            return $html;
        }

        private function registerNewUser($username, $password, $email) {
            $userID = $this->ContentManagementSystem->createUser( $username, $password, $email );
            $this->processNewUser($userID);

            if ( isset($_GET['n']) ) {
                $invite = $this->DbConnection->getInvite($_GET['n']);
                $inviterID = $this->CodeLibrary->convertStringToInt($invite->inviterID);
                $this->DbConnection->createRelationship($userID, $inviterID);
            }
        }
	}
}

?>