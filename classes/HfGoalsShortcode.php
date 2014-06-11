<?php

class HfGoalsShortcode implements Hf_iShortcode {
    private $Database;
    private $UserManager;
    private $Messenger;
    private $PageLocator;
    private $Goals;
    private $Security;
    private $Cms;

    function __construct( Hf_iUserManager $UserManager, Hf_iMessenger $Messenger, Hf_iAssetLocator $PageLocator, Hf_iDatabase $Database, Hf_iGoals $Goals, Hf_iSecurity $Security, Hf_iContentManagementSystem $ContentManagementSystem ) {
        $this->UserManager = $UserManager;
        $this->Messenger   = $Messenger;
        $this->Database    = $Database;
        $this->PageLocator = $PageLocator;
        $this->Goals       = $Goals;
        $this->Security    = $Security;
        $this->Cms         = $ContentManagementSystem;
    }

    public function getOutput() {
        if ( !$this->isUserAuthorized() ) {
            return $this->Security->requireLogin();
        }

        $userID = $this->determineUserID();

        if ( isset( $_POST['submit'] ) ) {
            $this->submitAccountabilityReports( $userID );

            return '<p class="success">Thanks for checking in!</p>' . $this->buildForm( $userID );
        } else {
            return $this->buildForm( $userID );
        }
    }

    private function isUserAuthorized() {
        if ( $this->Cms->isUserLoggedIn() ) {
            return true;
        } elseif ( empty( $_GET['userID'] ) ) {
            return false;
        } elseif ( $this->Database->emailIsValid( $_GET['userID'], $_GET['emailID'] ) ) {
            return true;
        } else {
            return false;
        }
    }

    private function determineUserID() {
        if ( isset( $_GET['userID'] ) && $this->Database->emailIsValid( $_GET['userID'], $_GET['emailID'] ) ) {
            $this->Messenger->markAsDelivered( $_GET['emailID'] );

            return $_GET['userID'];
        } else {
            return $this->UserManager->getCurrentUserId();
        }
    }

    private function submitAccountabilityReports( $userID ) {
        if ( isset( $_GET['emailID'] ) ) {
            $emailID = $_GET['emailID'];
        } else {
            $emailID = null;
        }

        foreach ( $_POST as $key => $value ) {
            if ( $key == 'submit' ) {
                continue;
            }
            $this->Database->submitAccountabilityReport( $userID, $key, $value, $emailID );
        }
    }

    private function buildForm( $userID ) {
        $currentURL         = $this->PageLocator->getCurrentPageURL();
        $goalSubs           = $this->Database->getGoalSubscriptions( $userID );
        $AccountabilityForm = new HfAccountabilityForm( $currentURL, $this->Goals );

        $AccountabilityForm->populate( $goalSubs );

        return $AccountabilityForm->getHtml();
    }
} 