<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfGoalsShortcode implements Hf_iShortcode {
    private $UserManager;
    private $Messenger;
    private $PageLocator;
    private $Goals;
    private $Security;
    private $MarkupGenerator;
    private $CodeLibrary;
    private $Database;

    function __construct( Hf_iUserManager $UserManager, Hf_iMessenger $Messenger, Hf_iAssetLocator $PageLocator, Hf_iGoals $Goals, Hf_iSecurity $Security, Hf_iMarkupGenerator $MarkupGenerator, Hf_iCodeLibrary $CodeLibrary, Hf_iDatabase $Database ) {
        $this->UserManager     = $UserManager;
        $this->Messenger       = $Messenger;
        $this->PageLocator     = $PageLocator;
        $this->Goals           = $Goals;
        $this->Security        = $Security;
        $this->MarkupGenerator = $MarkupGenerator;
        $this->CodeLibrary     = $CodeLibrary;
        $this->Database        = $Database;
    }

    public function getOutput() {
        if ( !$this->isUserAuthorized() ) {
            return $this->Security->requireLogin();
        }

        $userID = $this->determineUserID();
        $this->updateReportRequest();

        if ( $this->isSubmitted() ) {
            $this->submitAccountabilityReports( $userID );

            $quotationMessage = $this->makeQuotationMessage();
            $successMessage   = $this->MarkupGenerator->makeSuccessMessage( 'Thanks for checking in!' );

            return $successMessage . $quotationMessage . $this->buildForm( $userID );
        } else {
            return $this->buildForm( $userID );
        }
    }

    private function isUserAuthorized() {
        $this->Messenger->deleteExpiredReportRequests();

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

    private function updateReportRequest() {
        if ( $this->isRequested() and $this->Messenger->isReportRequestValid( $_GET['n'] ) ) {
            if ( $this->isSubmitted() ) {
                $this->Messenger->deleteReportRequest( $_GET['n'] );
            } else {
                $this->updateReportRequestExpirationDateToOneHourFromNow();
            }
        }
    }

    private function isSubmitted() {
        return isset( $_POST['submit'] );
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

    private function makeQuotationMessage() {
        $quotation = $this->selectQuotation();

        return $quotation ? $this->MarkupGenerator->makeQuoteMessage( $quotation ) : null;
    }

    private function buildForm( $userID ) {
        $currentURL         = $this->PageLocator->getCurrentPageURL();
        $goalSubs           = $this->Goals->getGoalSubscriptions( $userID );
        $AccountabilityForm = new HfAccountabilityForm( $currentURL, $this->Goals );

        $AccountabilityForm->populate( $goalSubs );

        return $AccountabilityForm->getOutput();
    }

    private function isRequested() {
        return !empty( $_GET['n'] );
    }

    private function updateReportRequestExpirationDateToOneHourFromNow() {
        $oneHour = 60 * 60;
        $this->Messenger->updateReportRequestExpirationDate( $_GET['n'], $this->CodeLibrary->getCurrentTime() + $oneHour );
    }

    private function notifyPartners( $userID ) {
        $Partners = $this->UserManager->getPartners( $userID );
        foreach ( $Partners as $Partner ) {
            $this->notifyPartner( $Partner, $userID );
        }
    }

    private function selectQuotation() {
        $context    = $this->determineQuotationContext();
        $quotations = $this->Database->getQuotations( $context );
        $key        = $this->CodeLibrary->randomKeyFromArray( $quotations );

        return $quotations ? $quotations[$key] : null;
    }

    private function notifyPartner( $Partner, $userId ) {
        $reporterUsername = $this->UserManager->getUsernameById( $userId );
        $subject          = $reporterUsername . ' just reported';
        $body             = $this->generatePartnerReportBody( $Partner, $reporterUsername );

        $this->Messenger->sendEmailToUser( $Partner->ID, $subject, $body );
    }

    private function determineQuotationContext() {
        return ( $this->didReportSetback() ) ? 'For Setback' : 'For Success';
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

    private function didReportSetback() {
        return in_array( '0', $_POST, true );
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
        $report .= ( $isSuccessful ) ? 'Success' : 'Setback';

        return $report;
    }
}