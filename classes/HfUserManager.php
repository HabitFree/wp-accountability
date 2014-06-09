<?php

class HfUserManager implements Hf_iUserManager {
    private $Database;
    private $Messenger;
    private $AssetLocator;
    private $ContentManagementSystem;
    private $CodeLibrary;

    function HfUserManager( Hf_iDatabase $Database, Hf_iMessenger $Messenger, Hf_iAssetLocator $PageLocator, Hf_iContentManagementSystem $ContentManagementSystem, Hf_iCodeLibrary $CodeLibrary ) {
        $this->Database                = $Database;
        $this->Messenger               = $Messenger;
        $this->AssetLocator            = $PageLocator;
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
        $this->Database->insertIgnoreIntoDb( $table, $data );
        $settingsPageURL = $this->AssetLocator->getPageUrlByTitle( 'Settings' );
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
        $welcome = 'Welcome back, ' . $this->getCurrentUserLogin() . ' | ';

        if ( is_user_logged_in() ) {
            $logOutUrl   = wp_logout_url( $this->AssetLocator->getCurrentPageUrl() );
            $settingsURL = $this->AssetLocator->getPageUrlByTitle( 'Settings' );

            return $welcome .
            '<a href="' . $logOutUrl . '">Log Out</a> | <a href="' . $settingsURL . '">Settings</a>';
        } else {
            $registerURL = $this->AssetLocator->getPageUrlByTitle( 'Register' );
            $loginUrl    = $this->AssetLocator->getPageUrlByTitle( 'Log In' );

            return '<a href="' . $loginUrl . '">Log In</a> | <a href="' . $registerURL . '">Register</a>';
        }

    }

    public function sendInvitation( $inviterID, $address, $daysToExpire ) {
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

    private function getUsernameByID( $userID, $initialCaps = false ) {
        $user = get_userdata( $userID );
        if ( $initialCaps === true ) {
            return ucwords( $user->user_login );
        } else {
            return $user->user_login;
        }
    }

    public function processInvite( $inviteeID, $nonce ) {
        $inviterID = $this->getInviterID();
        $this->Database->createRelationship( $inviteeID, $inviterID );
        $this->Database->deleteInvite( $nonce );
    }

    private function getInviterID() {
        $nonce     = $this->CodeLibrary->getGet( 'n' );
        $invite    = $this->Database->getInvite( $nonce );
        $inviterID = $this->CodeLibrary->convertStringToInt( $invite->inviterID );

        return $inviterID;
    }
}