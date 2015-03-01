<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iMarkupGenerator {
    public function progressBar( $percent, $label );

    public function generateTabs( $contents, $defaultTabNumber );

    public function makeParagraph( $content );

    public function makeLink( $target, $content );

    public function makeList( $items );

    public function makeErrorMessage( $content );

    public function makeSuccessMessage( $content );

    public function makeQuoteMessage( $content );

    public function makeForm( $url, $content, $name );

    public function makeButtonInput( $name, $label, $onclick );

    public function makeHiddenField( $name );

    public function makeInfoMessage($content);

    public function makeRedirectScript($url);

    public function makeHeader($content, $level);

    public function makeGoalCard(
        $goalVerb,
        $goalDescription,
        $goalId,
        $daysSinceLastReport,
        $levelId,
        $levelTitle,
        $levelPercent,
        $levelDaysToComplete,
        $levelBar
    );

    public function makeRefreshScript();
}