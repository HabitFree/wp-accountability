<?php

class HfGoals implements Hf_iGoals {
    private $Database;
    private $MarkupGenerator;
    private $ContentManagementSystem;
    private $Messenger;

    function __construct(
        Hf_iMessenger $Messenger,
        Hf_iCms $ContentManagementSystem,
        Hf_iMarkupGenerator $MarkupGenerator,
        Hf_iDatabase $Database
    ) {
        $this->Messenger               = $Messenger;
        $this->ContentManagementSystem = $ContentManagementSystem;
        $this->MarkupGenerator         = $MarkupGenerator;
        $this->Database                = $Database;
    }

    function generateGoalCard( $sub ) {
        $goalID        = intval( $sub->goalID );
        $userID        = intval( $sub->userID );
        $goal          = $this->Database->getGoal( $goalID, 2 );
        $daysOfSuccess = $this->daysOfSuccess( $goalID, $userID );
        $level         = $this->Database->getLevel( $daysOfSuccess );
        $wrapperOpen   = '<div class="report-card">';
        $info          = '<div class="about"><h2>' . $goal->title . '</h2>';
        if ( $goal->description != '' ) {
            $info .= '<p>' . $goal->description . '</p></div>';
        } else {
            $info .= '</div>';
        }

        $controls     = "<div class='controls'>
					<label class='success'><input type='radio' name='" . $goalID . "' value='1'> No</label>
                    <label class='setback'><input type='radio' name='" . $goalID . "' value='0'> Yes</label>
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

    function daysOfSuccess( $goalId, $userId ) {
        $dateInSecondsOfFirstSuccess = $this->Database->timeOfFirstSuccess( $goalId, $userId );
        $dateInSecondsOfLastSuccess  = $this->Database->timeOfLastSuccess( $goalId, $userId );
        $dateInSecondsOfLastFail     = $this->Database->timeOfLastFail( $goalId, $userId );

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

    function levelPercentComplete( $goalId, $userId ) {
        $daysOfSuccess = $this->daysOfSuccess( $goalId, $userId );

        return ( $this->daysOfSuccess( $goalId, $userId ) / $this->currentLevelTarget( $daysOfSuccess ) ) * 100;
    }

    function daysToNextLevel( $goalId, $userId ) {
        $daysOfSuccess = $this->daysOfSuccess( $goalId, $userId );
        $target        = $this->currentLevelTarget( $daysOfSuccess );

        return $target - $daysOfSuccess;
    }

    function levelBarForGoal( $goalId, $userId ) {
        $percent = $this->levelPercentComplete( $goalId, $userId );

        return $this->MarkupGenerator->progressBar( $percent, '' );
    }

    function currentLevelTarget( $daysOfSuccess ) {
        $level = $this->Database->getLevel( $daysOfSuccess );

        return $level->target;
    }

    function sendReportRequestEmails() {
        $users = $this->ContentManagementSystem->getSubscribedUsers();

        foreach ( $users as $user ) {
            if ( $this->isAnyGoalDue( $user->ID ) and !$this->Messenger->isThrottled( $user->ID ) ) {
                $this->Messenger->sendReportRequestEmail( $user->ID );
            }
        }
    }

    private function isAnyGoalDue( $userId ) {
        $goalSubs = $this->getGoalSubscriptions( $userId );
        foreach ( $goalSubs as $goalSub ) {
            if ( $this->isGoalDue( $goalSub->goalID, $userId ) ) {
                return true;
            }
        }

        return false;
    }

    public function getGoalSubscriptions( $userId ) {
        return $this->Database->getGoalSubscriptions( $userId );
    }

    private function isGoalDue( $goalId, $userId ) {
        $daysOfSuccess       = $this->daysOfSuccess( $goalId, $userId );
        $level               = $this->Database->getLevel( $goalId, $userId, $daysOfSuccess );
        $emailInterval       = $level->emailInterval;
        $daysSinceLastReport = $this->Database->daysSinceLastReport( $goalId, $userId );

        return $daysSinceLastReport > $emailInterval;
    }

    public function getGoalTitle( $goalId ) {
        return $this->Database->getGoal( $goalId )->title;
    }

    public function recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId = null ) {
        $this->Database->recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId );
    }
} 