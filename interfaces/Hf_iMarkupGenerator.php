<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iMarkupGenerator {
    public function progressBar( $percent, $label );

    public function tabs( $contents, $defaultTabNumber );

    public function paragraph( $content );

    public function linkMarkup( $target, $content );

    public function listMarkup( $items );

    public function errorMessage( $content );

    public function successMessage( $content );

    public function quotation( $content );

    public function form( $url, $content, $name );

    public function buttonInput( $name, $label, $onclick );

    public function hiddenField( $name );

    public function infoMessage($content);

    public function redirectScript($url);

    public function head($content, $level);

    public function goalCard(
        $goalVerb,
        $goalDescription,
        $goalId,
        $daysSinceLastReport,
        $currentStreak,
        $longestStreak
    );

    public function refreshScript();
}