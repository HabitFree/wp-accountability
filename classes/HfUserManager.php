<?php

class HfUserManager implements Hf_iUserManager {
    private $Database;
    private $Messenger;
    private $AssetLocator;
    private $Cms;

    function HfUserManager( Hf_iDatabase $Database, Hf_iMessenger $Messenger, Hf_iAssetLocator $PageLocator, Hf_iContentManagementSystem $ContentManagementSystem ) {
        $this->Database                = $Database;
        $this->Messenger               = $Messenger;
        $this->AssetLocator            = $PageLocator;
        $this->Cms = $ContentManagementSystem;
    }

    function processAllUsers() {
        $users = get_users();
        foreach ( $users as $user ) {
            $this->processNewUser( $user->ID );
        }
    }

    function processNewUser( $userId ) {
        $table = "hf_user_goal";
        $data  = array('userID' => $userId,
                       'goalID' => 1);
        $this->Database->insertIgnoreIntoDb( $table, $data );
        $settingsPageURL = $this->AssetLocator->getPageUrlByTitle( 'Settings' );
        $message         = "<p>Welcome to HabitFree!
				You've been subscribed to periodic accountability emails. 
				You can <a href='" . $settingsPageURL . "'>edit your subscription settings by clicking here</a>.</p>";
        $this->Messenger->sendEmailToUser( $userId, 'Welcome!', $message );
    }

    function getCurrentUserLogin() {
        return $this->Cms->currentUser()->user_login;
    }

    function getCurrentUserId() {
        return $this->Cms->currentUser()->ID;
    }

    function userButtonsShortcode() {
        $welcome = 'Welcome back, ' . $this->getCurrentUserLogin() . ' | ';

        if ( is_user_logged_in() ) {
            $logOutUrl   = wp_logout_url( $this->AssetLocator->getCurrentPageUrl() );
            $settingsUrl = $this->AssetLocator->getPageUrlByTitle( 'Settings' );

            return $welcome .
            '<a href="' . $logOutUrl . '">Log Out</a> | <a href="' . $settingsUrl . '">Settings</a>';
        } else {
            $registerUrl = $this->AssetLocator->getPageUrlByTitle( 'Register' );
            $loginUrl    = $this->AssetLocator->getPageUrlByTitle( 'Log In' );

            return '<a href="' . $loginUrl . '">Log In</a> | <a href="' . $registerUrl . '">Register</a>';
        }

    }

    public function sendInvitation( $inviterId, $address, $daysToExpire ) {
        $inviteId        = $this->Messenger->generateInviteID();
        $inviteURL       = $this->Messenger->generateInviteURL( $inviteId );
        $inviterUsername = $this->getUsernameById( $inviterId, true );
        $subject         = $inviterUsername . ' just invited you to partner with them at HabitFree!';
        $body            = "<p>" . $inviterUsername . " would like to become accountability partners with you on HabitFree. HabitFree is a community of young people striving for God's ideal of purity and Christian freedom.</p><p><a href='" . $inviteURL . "'>Click here to join " . $inviterUsername . " in his quest!</a></p>";

        $emailId = $this->Messenger->sendEmailToAddress( $address, $subject, $body );

        $expirationDate = date( 'Y-m-d H:i:s', strtotime( '+' . $daysToExpire . ' days' ) );

        if ( $emailId !== false ) {
            $this->Messenger->recordInvite( $inviteId, $inviterId, $address, $emailId, $expirationDate );
        }

        return $inviteId;
    }

    private function getUsernameById( $userId, $initialCaps = false ) {
        $user = get_userdata( $userId );
        if ( $initialCaps === true ) {
            return ucwords( $user->user_login );
        } else {
            return $user->user_login;
        }
    }

    public function processInvite( $inviteeEmail, $nonce ) {
        $inviteeID = $this->Cms->getUserIdByEmail($inviteeEmail);
        $inviterID = $this->Database->getInviterID($nonce);
        $this->Database->createRelationship( $inviteeID, $inviterID );
        $this->Database->deleteInvite( $nonce );
    }
}