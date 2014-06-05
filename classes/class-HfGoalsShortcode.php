<?php

class HfGoalsShortcode implements Hf_iShortcode {
    private $Database;
    private $UserManager;
    private $Messenger;
    private $UrlFinder;
    private $Goals;
    private $CodeLibrary;

    function __construct($UserManager, $Messenger, $UrlFinder, $Database, $Goals, Hf_iCodeLibrary $CodeLibrary) {
        $this->UserManager      = $UserManager;
        $this->Messenger        = $Messenger;
        $this->Database         = $Database;
        $this->UrlFinder        = $UrlFinder;
        $this->Goals            = $Goals;
        $this->CodeLibrary      = $CodeLibrary;
    }

    public function getOutput() {
        if ( !$this->isUserAuthorized() ) {
            return $this->UserManager->requireLogin();
        }

        $userID = $this->determineUserID();

        if ( isset($_POST['submit']) ) {
            $this->submitAccountabilityReports($userID);

            return '<p class="success">Thanks for checking in!</p>' . $this->buildForm($userID);
        } else {
            return $this->buildForm($userID);
        }
    }

    private function isUserAuthorized() {
        if ( is_user_logged_in() ) {
            return true;
        } elseif ( $this->CodeLibrary->isUrlParameterEmpty('userID') ) {
            return false;
        } elseif ( $this->Database->emailIsValid( $this->CodeLibrary->getUrlParameter('userID'), $this->CodeLibrary->getUrlParameter('emailID') ) ) {
            return true;
        } else {
            return false;
        }
    }

    private function determineUserID() {
        if ( isset($_GET['userID']) && $this->Database->emailIsValid($_GET['userID'], $_GET['emailID']) ) {
            $this->Messenger->markAsDelivered($_GET['emailID']);
            return $_GET['userID'];
        } else {
            return $this->UserManager->getCurrentUserId();
        }
    }

    private function submitAccountabilityReports($userID) {
        if ( isset($_GET['emailID']) ) {
            $emailID = $_GET['emailID'];
        } else {
            $emailID = null;
        }

        foreach ($_POST as $key => $value) {
            if ($key == 'submit') {
                continue;
            }
            $this->Database->submitAccountabilityReport($userID, $key, $value, $emailID);
        }
    }

    private function buildForm($userID) {
        $currentURL         = $this->UrlFinder->getCurrentPageURL();
        $goalSubs           = $this->Database->getGoalSubscriptions($userID);
        $AccountabilityForm = new HfAccountabilityForm($currentURL, $this->Goals);

        $AccountabilityForm->populate($goalSubs);

        return $AccountabilityForm->getHtml();
    }
} 