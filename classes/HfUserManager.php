<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfUserManager implements Hf_iUserManager {
    private $database;
    private $messenger;
    private $assetLocator;
    private $cms;
    private $codeLibrary;

    function __construct( Hf_iDatabase $database, Hf_iMessenger $messenger, Hf_iAssetLocator $pageLocator, Hf_iCms $contentManagementSystem, Hf_iCodeLibrary $codeLibrary ) {
        $this->database     = $database;
        $this->messenger    = $messenger;
        $this->assetLocator = $pageLocator;
        $this->cms          = $contentManagementSystem;
        $this->codeLibrary  = $codeLibrary;
    }

    function processAllUsers() {
        $users = $this->cms->getUsers();
        foreach ( $users as $user ) {
            $this->database->setDefaultGoalSubscription($user->ID);
        }
    }

    function processNewUser( $userId ) {
        $this->database->setDefaultGoalSubscription($userId);
        $this->sendWelcomeMessage($userId);
    }

    function getCurrentUserLogin() {
        $user = $this->cms->currentUser();
        return $user->user_login;
    }

    function getCurrentUserId() {
        return $this->cms->currentUser()->ID;
    }

    public function sendInvitation( $inviterId, $address ) {
        $inviteId        = $this->messenger->generateSecureEmailId();
        $inviteURL       = $this->messenger->generateInviteURL( $inviteId );
        $inviterUsername = $this->getUsernameById( $inviterId, true );
        $subject         = $inviterUsername . ' just invited you to partner with them at HabitFree!';
        $body            = "<p>" . $inviterUsername . " would like to become accountability partners with you on HabitFree. HabitFree is a community of young people striving for God's ideal of purity and Christian freedom.</p><p><a href='" . $inviteURL . "'>Click here to join " . $inviterUsername . " in his quest!</a></p>";

        $emailId = $this->messenger->sendEmailToAddress( $address, $subject, $body );

        $expirationDate = $this->generateExpirationDate( 7 );

        if ( $emailId !== false ) {
            $this->database->recordInvite( $inviteId, $inviterId, $address, $emailId, $expirationDate );
        }

        return $inviteId;
    }

    private function generateExpirationDate( $daysToExpire ) {
        $expirationTime = $this->codeLibrary->convertStringToTime( '+' . $daysToExpire . ' days' );

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

    public function processInvite( $inviteeId, $nonce ) {
        $this->messenger->deleteExpiredInvites();

        $inviterId = $this->database->getInviterId( $nonce );

        $this->database->createRelationship( $inviteeId, $inviterId );
        $this->database->deleteInvite( $nonce );
    }

    public function isUserLoggedIn() {
        return $this->cms->isUserLoggedIn();
    }

    public function getPartners( $userId ) {
        return $this->database->getPartners( $userId );
    }

    public function deleteRelationship($userId1, $userId2) {
        $this->database->deleteRelationship($userId1, $userId2);
    }

    private function sendWelcomeMessage($userId)
    {
        $settingsPageURL = $this->assetLocator->getPageUrlByTitle('Settings');
        $message = "<p>Welcome to HabitFree! You can <a href='$settingsPageURL'>edit your account settings by clicking here</a>.</p>";
        $this->messenger->sendEmailToUser($userId, 'Welcome!', $message);
    }
}