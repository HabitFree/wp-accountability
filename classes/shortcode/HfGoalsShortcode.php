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
    private $timber;

    function __construct(
        HfUserManager $UserManager,
        HfMailer $Messenger,
        HfUrlFinder $PageLocator,
        HfGoals $Goals,
        HfSecurity $Security,
        HfHtmlGenerator $MarkupGenerator,
        HfPhpLibrary $CodeLibrary,
        HfMysqlDatabase $Database,
        HfTimber $timber
    ) {
        $this->UserManager     = $UserManager;
        $this->Messenger       = $Messenger;
        $this->PageLocator     = $PageLocator;
        $this->Goals           = $Goals;
        $this->Security        = $Security;
        $this->MarkupGenerator = $MarkupGenerator;
        $this->CodeLibrary     = $CodeLibrary;
        $this->Database        = $Database;
        $this->timber          = $timber;
    }

    public function getOutput() {
        $viewData = [];

        if ( !$this->isUserAuthorized() ) {
            return $this->Security->requireLogin();
        }

        $userID = $this->determineUserID();
        $this->updateReportRequest();

        if ( $this->isSubmitted() ) {
            $this->submitAccountabilityReports( $userID );

            $quotationMessage = $this->makeQuotationMessage();
            $successMessage   = $this->MarkupGenerator->successMessage( 'Thanks for checking in!' );

            $viewData["content"] =  $successMessage . $quotationMessage . $this->form( $userID );
        } else {
            // add form data
        }

        $this->timber->render("goals.twig",$viewData);
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

        return $quotation ? $this->MarkupGenerator->quotation( $quotation ) : null;
    }

    private function form( $userID ) {
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

        $this->Messenger->sendReportNotificationEmail( $Partner->ID, $subject, $body );
    }

    private function determineQuotationContext() {
        return ( $this->didReportSetback() ) ? 'For Setback' : 'For Success';
    }

    private function generatePartnerReportBody( $Partner, $reporterUsername ) {
        $greeting = $this->MarkupGenerator->paragraph( "Hello, " . $Partner->user_login . "," );
        $intro    = $this->MarkupGenerator->paragraph(
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

        return $this->MarkupGenerator->listMarkup( $reports );
    }

    private function generateReportsListItem( $goalId, $isSuccessful ) {
        $verb = $this->Goals->getGoalTitle( $goalId );
        $color = ($isSuccessful) ? '#088A08' : '#8A0808';
        $status = ($isSuccessful) ? 'Success' : 'Setback';

        return "Don't $verb: <span style='color:$color;'>$status</span>";
    }
}