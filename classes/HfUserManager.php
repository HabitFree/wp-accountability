<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfUserManager implements Hf_iUserManager {
    private $Database;
    private $Messenger;
    private $AssetLocator;
    private $Cms;
    private $CodeLibrary;

    function HfUserManager( Hf_iDatabase $Database, Hf_iMessenger $Messenger, Hf_iAssetLocator $PageLocator, Hf_iCms $ContentManagementSystem, Hf_iCodeLibrary $CodeLibrary ) {
        $this->Database     = $Database;
        $this->Messenger    = $Messenger;
        $this->AssetLocator = $PageLocator;
        $this->Cms          = $ContentManagementSystem;
        $this->CodeLibrary  = $CodeLibrary;
    }

    function processAllUsers() {
        $users = get_users();
        foreach ( $users as $user ) {
            $this->processNewUser( $user->ID );
        }
    }

    function processNewUser( $userId ) {
        $this->Database->setDefaultGoalSubscription($userId);
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

    public function sendInvitation( $inviterId, $address ) {
        $inviteId        = $this->Messenger->generateSecureEmailId();
        $inviteURL       = $this->Messenger->generateInviteURL( $inviteId );
        $inviterUsername = $this->getUsernameById( $inviterId, true );
        $subject         = $inviterUsername . ' just invited you to partner with them at HabitFree!';
        $body            = "<p>" . $inviterUsername . " would like to become accountability partners with you on HabitFree. HabitFree is a community of young people striving for God's ideal of purity and Christian freedom.</p><p><a href='" . $inviteURL . "'>Click here to join " . $inviterUsername . " in his quest!</a></p>";

        $emailId = $this->Messenger->sendEmailToAddress( $address, $subject, $body );

        $expirationDate = $this->generateExpirationDate( 7 );

        if ( $emailId !== false ) {
            $this->Database->recordInvite( $inviteId, $inviterId, $address, $emailId, $expirationDate );
        }

        return $inviteId;
    }

    private function generateExpirationDate( $daysToExpire ) {
        $expirationTime = $this->CodeLibrary->convertStringToTime( '+' . $daysToExpire . ' days' );

        return date( 'Y-m-d H:i:s', $expirationTime );
    }

    public function getUsernameById( $userId, $initialCaps = false ) {
        $user = get_userdata( $userId );
        if ( $initialCaps === true ) {
            return ucwords( $user->user_login );
        } else {
            return $user->user_login;
        }
    }

    public function processInvite( $inviteeEmail, $nonce ) {
        $this->Messenger->deleteExpiredInvites();

        $inviteeID = $this->Cms->getUserIdByEmail( $inviteeEmail );
        $inviterID = $this->Database->getInviterId( $nonce );

        $this->Database->createRelationship( $inviteeID, $inviterID );
        $this->Database->deleteInvite( $nonce );
    }

    public function isUserLoggedIn() {
        return $this->Cms->isUserLoggedIn();
    }

    public function getPartners( $userId ) {
        return $this->Database->getPartners( $userId );
    }

    public function deleteRelationship($userId1, $userId2) {
        $this->Database->deleteRelationship($userId1, $userId2);
    }
}