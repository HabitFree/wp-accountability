<?php

if ( !class_exists( "HfUserManager" ) ) {
    class HfUserManager {
        private $DbConnection;
        private $Messenger;
        private $UrlFinder;
        private $ContentManagementSystem;
        private $CodeLibrary;

        function HfUserManager( $Database, $Messenger, $UrlFinder, Hf_iContentManagementSystem $ContentManagementSystem, Hf_iCodeLibrary $CodeLibrary ) {
            $this->DbConnection            = $Database;
            $this->Messenger               = $Messenger;
            $this->UrlFinder               = $UrlFinder;
            $this->ContentManagementSystem = $ContentManagementSystem;
            $this->CodeLibrary             = $CodeLibrary;
        }

        function processAllUsers() {
            $users = get_users();
            foreach ( $users as $user ) {
                $this->processNewUser( $user->ID );
            }
        }

        function processNewUser( $userID ) {
            $table = "hf_user_goal";
            $data  = array('userID' => $userID,
                           'goalID' => 1);
            $this->DbConnection->insertIgnoreIntoDb( $table, $data );
            $settingsPageURL = $this->UrlFinder->getURLByTitle( 'Settings' );
            $message         = "<p>Welcome to HabitFree!
				You've been subscribed to periodic accountability emails. 
				You can <a href='" . $settingsPageURL . "'>edit your subscription settings by clicking here</a>.</p>";
            $this->Messenger->sendEmailToUser( $userID, 'Welcome!', $message );
        }

        function getCurrentUserLogin() {
            return $this->ContentManagementSystem->currentUser()->user_login;
        }

        function getCurrentUserId() {
            return $this->ContentManagementSystem->currentUser()->ID;
        }

        function userButtonsShortcode() {
            $URLFinder = new HfUrlFinder();
            $welcome   = 'Welcome back, ' . $this->getCurrentUserLogin() . ' | ';

            if ( is_user_logged_in() ) {
                $logOutUrl   = wp_logout_url( $URLFinder->getCurrentPageUrl() );
                $settingsURL = $URLFinder->getURLByTitle( 'Settings' );

                return $welcome .
                '<a href="' . $logOutUrl . '">Log Out</a> | <a href="' . $settingsURL . '">Settings</a>';
            } else {
                $registerURL = $URLFinder->getURLByTitle( 'Register' );
                $loginUrl    = $this->UrlFinder->getURLByTitle( 'Log In' );

                return '<a href="' . $loginUrl . '">Log In</a> | <a href="' . $registerURL . '">Register</a>';
            }

        }

        function getUsernameByID( $userID, $initialCaps = false ) {
            $user = get_userdata( $userID );
            if ( $initialCaps === true ) {
                return ucwords( $user->user_login );
            } else {
                return $user->user_login;
            }
        }

        function sendInvitation( $inviterID, $address, $daysToExpire ) {
            $inviteID        = $this->Messenger->generateInviteID();
            $inviteURL       = $this->Messenger->generateInviteURL( $inviteID );
            $inviterUsername = $this->getUsernameByID( $inviterID, true );
            $subject         = $inviterUsername . ' just invited you to partner with them at HabitFree!';
            $body            = "<p>" . $inviterUsername . " would like to become accountability partners with you on HabitFree. HabitFree is a community of young people striving for God's ideal of purity and Christian freedom.</p><p><a href='" . $inviteURL . "'>Click here to join " . $inviterUsername . " in his quest!</a></p>";

            $emailID = $this->Messenger->sendEmailToAddress( $address, $subject, $body );

            $expirationDate = date( 'Y-m-d H:i:s', strtotime( '+' . $daysToExpire . ' days' ) );

            if ( $emailID !== false ) {
                $this->Messenger->recordInvite( $inviteID, $inviterID, $address, $emailID, $expirationDate );
            }

            return $inviteID;
        }

        public function processInvite($inviteeID, $nonce) {
            $inviterID = $this->getInviterID();
            $this->DbConnection->createRelationship($inviteeID, $inviterID);
            $this->DbConnection->deleteInvite($nonce);
        }

        private function getInviterID() {
            $nonce = $this->CodeLibrary->getGet('n');
            $invite = $this->Database->getInvite($nonce);
            $inviterID = $this->CodeLibrary->convertStringToInt($invite->inviterID);

            return $inviterID;
        }
    }
}

?>