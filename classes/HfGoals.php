<?php

class HfGoals implements Hf_iGoals {
    private $Database;
    private $View;
    private $ContentManagementSystem;
    private $Messenger;
    private $CodeLibrary;

    function __construct( Hf_iMessenger $Messenger, Hf_iContentManagementSystem $ContentManagementSystem, Hf_iDisplayCodeGenerator $View, Hf_iDatabase $Database, Hf_iCodeLibrary $CodeLibrary ) {
        $this->Messenger               = $Messenger;
        $this->ContentManagementSystem = $ContentManagementSystem;
        $this->View                    = $View;
        $this->Database                = $Database;
        $this->CodeLibrary             = $CodeLibrary;
    }

    function generateGoalCard( $sub ) {
        $goalID        = intval( $sub->goalID );
        $userID        = intval( $sub->userID );
        $goal          = $this->Database->getRow( 'hf_goal', 'goalID = ' . $goalID );
        $daysOfSuccess = $this->daysOfSuccess( $goalID, $userID );
        $level         = $this->Database->level( $daysOfSuccess );
        $wrapperOpen   = '<div class="report-card">';
        $info          = '<div class="info"><h2>' . $goal->title . '</h2>';
        if ( $goal->description != '' ) {
            $info .= '<p>' . $goal->description . '</p></div>';
        } else {
            $info .= '</div>';
        }

        $controls     = "<div class='controls'>
					<label><input type='radio' name='" . $goalID . "' value='0'> &#x2714;</label>
					<label><input type='radio' name='" . $goalID . "' value='1'> &#x2718;</label>
				</div>";
        $report       = "<div class='report'>Have you fallen since your last check-in?" . $controls . "</div>";
        $main         = '<div class="main">' . $info . $report . '</div>';
        $stat1        = '<p class="stat">Level <span class="number">' . $level->levelID . '</span> ' . $level->title . '</p>';
        $stat2        = '<p class="stat">Level <span class="number">' . round( $this->levelPercentComplete( $goalID, $userID ), 1 ) . '%</span> Complete</p>';
        $stat3        = '<p class="stat">Days to <span class="number">' . round( $this->daysToNextLevel( $goalID, $userID ) ) . '</span> Next Level</p>';
        $bar          = $this->levelBarForGoal( $goalID, $userID );
        $stats        = '<div class="stats">' . $stat1 . $stat2 . $stat3 . $bar . '</div>';
        $wrapperClose = '</div>';

        return $wrapperOpen . $main . $stats . $wrapperClose;
    }

    function levelPercentComplete( $goalID, $userID ) {
        $daysOfSuccess = $this->daysOfSuccess( $goalID, $userID );

        return ( $this->daysOfSuccess( $goalID, $userID ) / $this->currentLevelTarget( $daysOfSuccess ) ) * 100;
    }

    function daysOfSuccess( $goalID, $userID ) {
        $dateInSecondsOfFirstSuccess = $this->Database->timeOfFirstSuccess( $goalID, $userID );
        $dateInSecondsOfLastSuccess  = $this->Database->timeOfLastSuccess( $goalID, $userID );
        $dateInSecondsOfLastFail     = $this->Database->timeOfLastFail( $goalID, $userID );

        $secondsInADay = 86400;

        if ( !$dateInSecondsOfLastSuccess ) {
            $daysOfSuccess = 0;
        } elseif ( !$dateInSecondsOfLastFail ) {
            $daysOfSuccess = ( $dateInSecondsOfLastSuccess - $dateInSecondsOfFirstSuccess ) / $secondsInADay;
        } else {
            $difference    = $dateInSecondsOfLastSuccess - $dateInSecondsOfLastFail;
            $daysOfSuccess = $difference / $secondsInADay;
            if ( $daysOfSuccess < 0 ) {
                $daysOfSuccess = 0;
            }
        }

        return $daysOfSuccess;
    }

    function currentLevelTarget( $daysOfSuccess ) {
        $level = $this->Database->level( $daysOfSuccess );

        return $level->target;
    }

    function daysToNextLevel( $goalID, $userID ) {
        $daysOfSuccess = $this->daysOfSuccess( $goalID, $userID );
        $target        = $this->currentLevelTarget( $daysOfSuccess );

        return $target - $daysOfSuccess;
    }

    function levelBarForGoal( $goalID, $userID ) {
        $percent = $this->levelPercentComplete( $goalID, $userID );

        return $this->View->progressBar( $percent, '' );
    }

    function sendReportRequestEmails() {
        $users = $this->ContentManagementSystem->getSubscribedUsers();

        foreach ( $users as $user ) {
            if ( $this->isAnyGoalDue( $user->ID ) and !$this->Messenger->isThrottled( $user->ID ) ) {
                $this->Messenger->sendReportRequestEmail( $user->ID );
            }
        }
    }

    private function isAnyGoalDue( $userID ) {
        $goalSubs = $this->Database->getRows( 'hf_user_goal', 'userID = ' . $userID );
        foreach ( $goalSubs as $goalSub ) {
            if ( $this->isGoalDue( $goalSub->goalID, $userID ) ) {
                return true;
            }
        }

        return false;
    }

    private function isGoalDue( $goalID, $userID ) {
        $daysOfSuccess       = $this->daysOfSuccess( $goalID, $userID );
        $level               = $this->Database->level( $goalID, $userID, $daysOfSuccess );
        $emailInterval       = $level->emailInterval;
        $daysSinceLastReport = $this->Database->daysSinceLastReport( $goalID, $userID );

        return $daysSinceLastReport > $emailInterval;
    }
} 