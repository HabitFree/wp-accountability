<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iMarkupGenerator {
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
        $goalId,
        $goalVerb,
        $daysSinceLastReport,
        $currentStreak,
        $health
    );

    public function refreshScript();

    public function input($properties);

    public function label($content, $properties);
}