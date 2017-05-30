<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfGoals implements Hf_iGoals {
    private $database;
    private $markupGenerator;
    private $cms;
    private $messenger;
    private $codeLibrary;
    private $streaks;
    private $health;

    function __construct(
        HfMailer $messenger,
        HfWordPress $cms,
        HfHtmlGenerator $markupGenerator,
        HfMysqlDatabase $database,
        HfPhpLibrary $codeLibrary,
        HfStreaks $streaks,
        HfHealth $health
    ) {
        $this->messenger = $messenger;
        $this->cms = $cms;
        $this->markupGenerator = $markupGenerator;
        $this->database = $database;
        $this->codeLibrary = $codeLibrary;
        $this->streaks = $streaks;
        $this->health = $health;
    }

    private function currentStreak( $goalId, $userId ) {
        $streaks = $this->streaks->streaks($goalId, $userId);
        return end($streaks);
    }

    private function longestStreak( $goalId, $userId ) {
        $streaks = $this->streaks->streaks($goalId, $userId);
        return max($streaks);
    }

    function levelPercentComplete( $goalId, $userId ) {
        $daysOfSuccess = $this->currentStreak( $goalId, $userId );

        return ( $this->currentStreak( $goalId, $userId ) / $this->currentLevelTarget( $daysOfSuccess ) ) * 100;
    }

    function daysToNextLevel( $goalId, $userId ) {
        $daysOfSuccess = $this->currentStreak( $goalId, $userId );
        $target        = $this->currentLevelTarget( $daysOfSuccess );

        return $target - $daysOfSuccess;
    }

    function currentLevelTarget( $daysOfSuccess ) {
        $level = $this->database->getLevel( $daysOfSuccess );

        return $level->target;
    }

    function sendReportRequestEmails() {
        $users = $this->cms->getSubscribedUsers();

        foreach ( $users as $user ) {
            if ( $this->isAnyGoalDue( $user->ID ) and !$this->messenger->isThrottled( $user->ID ) ) {
                $this->messenger->sendReportRequestEmail( $user->ID );
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
        return $this->database->getGoalSubscriptions( $userId );
    }

    private function isGoalDue( $goalId, $userId ) {
        $daysOfSuccess       = $this->currentStreak( $goalId, $userId );
        $level               = $this->database->getLevel( $goalId, $userId, $daysOfSuccess );
        $emailInterval       = $level->emailInterval;
        $daysSinceLastReport = $this->database->daysSinceLastReport( $goalId, $userId );

        return $daysSinceLastReport > $emailInterval;
    }

    public function getGoalTitle( $goalId ) {
        return $this->database->getGoal( $goalId )->title;
    }

    public function recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId = null ) {
        $this->database->recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId );
    }

    private function getGoalCardData($sub)
    {
        $userId = intval($sub->userID);

        $goalId = intval($sub->goalID);
        $goal = $this->database->getGoal($goalId);
        $daysSinceLastReport = $this->database->daysSinceLastReport($goalId, $userId);

        $currentStreak = $this->currentStreak($goalId, $userId);
        $longestStreak = $this->longestStreak($goalId, $userId);
        $health = $this->health->getHealth($goalId, $userId) * 100;

        return [
            $goalId,
            $goal->title,
            $daysSinceLastReport,
            $currentStreak,
            $longestStreak,
            $health
        ];
    }

    public function getGoalCardsData( $userId ) {
        $subs = $this->getGoalSubscriptions( $userId );

        return array_map( function( $sub ) {
            $data =  $this->getGoalCardData( $sub );

            return [
                "id" => $data[0],
                "title" => $data[1],
                "daysSinceLastReport" => $data[2],
                "currentStreak" => $data[3],
                "longestStreak" => $data[4],
                "health" => $data[5]
            ];
        }, $subs );
    }
} 