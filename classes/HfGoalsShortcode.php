<?php

class HfGoalsShortcode implements Hf_iShortcode {
    private $UserManager;
    private $Messenger;
    private $PageLocator;
    private $Goals;
    private $Security;
    private $MarkupGenerator;

    function __construct( Hf_iUserManager $UserManager, Hf_iMessenger $Messenger, Hf_iAssetLocator $PageLocator, Hf_iGoals $Goals, Hf_iSecurity $Security, Hf_iMarkupGenerator $MarkupGenerator ) {
        $this->UserManager     = $UserManager;
        $this->Messenger       = $Messenger;
        $this->PageLocator     = $PageLocator;
        $this->Goals           = $Goals;
        $this->Security        = $Security;
        $this->MarkupGenerator = $MarkupGenerator;
    }

    public function getOutput() {
        if ( !$this->isUserAuthorized() ) {
            return $this->Security->requireLogin();
        }

        $userID = $this->determineUserID();

        if ( $this->isRequested() ) {
            $this->Messenger->deleteReportRequest( $_GET['n'] );
        }

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
        } elseif ( !$this->isRequested() ) {
            return false;
        } elseif ( $this->Messenger->isReportRequestValid( $_GET['n'] ) ) {
            return true;
        } else {
            return false;
        }
    }

    private function determineUserID() {
        if ( $this->UserManager->isUserLoggedIn() ) {
            return $this->UserManager->getCurrentUserId();
        } elseif ( $this->isRequested() ) {
            return $this->Messenger->getReportRequestUserId( $_GET['n'] );
        }
    }

    private function submitAccountabilityReports( $userID ) {
        foreach ( $_POST as $key => $value ) {
            if ( $key == 'submit' ) {
                continue;
            }
            $this->Goals->recordAccountabilityReport( $userID, $key, $value, null );
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
        $greeting = $this->MarkupGenerator->makeParagraph( "Hello, " . $Partner->user_login . "," );
        $intro    = $this->MarkupGenerator->makeParagraph(
            "Your friend " . $reporterUsername . " just reported on their progress. Here's how they're doing:"
        );

        $reports = $this->generateReportsList();
        $body    = $greeting . $intro . $reports;

        return $body;
    }

    private function generateReportsList() {
        $reports = array();

        foreach ( $_POST as $goalId => $isSuccessful ) {
            if ( $goalId == 'submit' ) {
                continue;
            } else {
                $reports[] = $this->generateReportsListItem( $goalId, $isSuccessful );
            }
        };

        return $this->MarkupGenerator->makeList( $reports );
    }

    private function generateReportsListItem( $goalId, $isSuccessful ) {
        $goalTitle = $this->Goals->getGoalTitle( $goalId );
        $report    = $goalTitle . ': ';
        $report .= ( $isSuccessful ) ? 'Success' : 'Failure';

        return $report;
    }

    private function isRequested() {
        return !empty( $_GET['n'] );
    }
}