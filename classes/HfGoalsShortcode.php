<?php

class HfGoalsShortcode implements Hf_iShortcode {
    private $UserManager;
    private $Messenger;
    private $PageLocator;
    private $Goals;
    private $Security;
    private $MarkupGenerator;

    function __construct( Hf_iUserManager $UserManager, Hf_iMessenger $Messenger, Hf_iAssetLocator $PageLocator, Hf_iGoals $Goals, Hf_iSecurity $Security, Hf_iMarkupGenerator $MarkupGenerator ) {
        $this->UserManager = $UserManager;
        $this->Messenger   = $Messenger;
        $this->PageLocator = $PageLocator;
        $this->Goals       = $Goals;
        $this->Security    = $Security;
        $this->MarkupGenerator = $MarkupGenerator;
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
        if ( $this->UserManager->isUserLoggedIn() ) {
            return true;
        } elseif ( empty( $_GET['userID'] ) ) {
            return false;
        } elseif ( $this->Messenger->isEmailValid( $_GET['userID'], $_GET['emailID'] ) ) {
            return true;
        } else {
            return false;
        }
    }

    private function determineUserID() {
        if ( isset( $_GET['userID'] ) && $this->Messenger->isEmailValid( $_GET['userID'], $_GET['emailID'] ) ) {
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
            $this->Goals->recordAccountabilityReport( $userID, $key, $value, $emailID );
        }

        $this->notifyPartners( $userID );
    }

    private function notifyPartners( $userID ) {
        $Partners = $this->UserManager->getPartners( $userID );
        foreach ( $Partners as $Partner ) {
            $this->notifyPartner( $Partner );
        }
    }

    private function buildForm( $userID ) {
        $currentURL         = $this->PageLocator->getCurrentPageURL();
        $goalSubs           = $this->Goals->getGoalSubscriptions( $userID );
        $AccountabilityForm = new HfAccountabilityForm( $currentURL, $this->Goals );

        $AccountabilityForm->populate( $goalSubs );

        return $AccountabilityForm->getHtml();
    }

    private function notifyPartner( $Partner ) {
        $reporterUsername = $this->UserManager->getCurrentUserLogin();
        $subject          = $reporterUsername . ' just reported';
        $body             = $this->generatePartnerReportBody( $Partner, $reporterUsername );

        $this->Messenger->sendEmailToUser( $Partner->ID, $subject, $body );
    }

    private function generatePartnerReportBody( $Partner, $reporterUsername ) {
        $greeting = $this->MarkupGenerator->makeParagraph("Hello, " . $Partner->user_login . ",");
        $intro = $this->MarkupGenerator->makeParagraph(
            "Your friend " . $reporterUsername . " just reported on their progress. Here's how they're doing:"
        );

        $reports = $this->generateReportsList();
        $body     = $greeting . $intro . $reports;

        return $body;
    }

    private function generateReportsList() {
        $reports = array();

        foreach ( $_POST as $goalId => $value ) {
            if ( $goalId == 'submit' ) {
                continue;
            } else {
                $reports[] = $this->generateReportsListItem( $goalId, $value );
            }
        };

        return $this->MarkupGenerator->makeList($reports);
    }

    private function generateReportsListItem( $goalId, $value ) {
        $goalTitle = $this->Goals->getGoalTitle( $goalId );
        $report = $goalTitle . ': ';
        $report .= ( $value === 0 ) ? 'Success' : 'Failure';

        return $report;
    }
}