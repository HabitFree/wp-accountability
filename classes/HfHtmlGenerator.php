<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfHtmlGenerator implements Hf_iMarkupGenerator {
    private $Cms;

    function __construct(
        Hf_iCms $ContentManagementSystem,
        Hf_iAssetLocator $assetLocator
    ) {
        $this->Cms = $ContentManagementSystem;
        $this->assetLocator = $assetLocator;
    }

    public function progressBar( $percent, $label ) {
        return '<div class="meter">
					<span class="label">' . $label . '</span>
					<span class="progress" style="width: ' . $percent*100 . '%">' . $label . '</span>
				</div>';
    }

    public function tabs( $contents, $defaultTabNumber ) {
        $html = '[su_tabs active="' . $defaultTabNumber . '"]';

        foreach ( $contents as $title => $content ) {
            $html .= '[su_tab title="' . $title . '"]' . $content . '[/su_tab]';
        }

        return $this->Cms->expandShortcodes( $html . '[/su_tabs]' );
    }

    public function paragraph( $content, $classes = NULL ) {
        $properties = ($classes === NULL ? '' : " class='$classes'");
        return "<p$properties>$content</p>";
    }

    public function linkMarkup( $target, $content ) {
        return '<a href="' . $target . '">' . $content . '</a>';
    }

    public function listMarkup( $items ) {
        $html = '';
        foreach ( $items as $item ) {
            $html .= '<li>' . $item . '</li>';
        }

        return '<ul>' . $html . '</ul>';
    }

    public function errorMessage( $content ) {
        return $this->paragraph($content, 'error');
    }

    public function successMessage( $content ) {
        return $this->paragraph($content, 'success');
    }

    public function quotation( $quotation ) {
        return '<p class="quote">"' . $quotation->post_content . '" — ' . $quotation->post_title . '</p>';
    }

    public function makeInfoMessage( $content ) {
        return '<p class="info">' . $content . '</p>';
    }

    public function form( $url, $content, $name ) {
        return '<form action="' . $url . '" method="post" name="' . $name . '">' . $content . '</form>';
    }

    public function buttonInput( $name, $label, $onclick ) {
        return '<input type="button" name="' . $name . '" value="' . $label . '" onclick="' . $onclick . '" />';
    }

    public function hiddenField( $name ) {
        return '<input type="hidden" name="' . $name . '" />';
    }

    public function redirectScript($url) {
        return '<script>window.location.replace("'.$url.'");</script>';
    }

    public function refreshScript() {
        $url = $this->assetLocator->getCurrentPageUrl();
        return $this->redirectScript($url);
    }

    public function head($content, $level) {
        return "<h$level>$content</h$level>";
    }

    public function goalCard(
        $goalVerb,
        $goalDescription,
        $goalId,
        $daysSinceLastReport,
        $currentStreak,
        $longestStreak
    ) {
        $stats = $this->stats($currentStreak, $longestStreak, $goalId);
        $reportDiv = $this->reportDiv($goalVerb, $goalId, $daysSinceLastReport);

        return "<div class='report-card'>$stats<div class='main'>$reportDiv</div></div>";
    }

    private function periodPhrase($daysSinceLastReport)
    {
        if ($daysSinceLastReport === false) {
            return 'in the last 24 hours';
        } else {
            $days = round($daysSinceLastReport);
            if ($days == 0) {
                $elapsed = 'less than a day';
            } else if ($days == 1) {
                $elapsed = '1 day';
            } else {
                $elapsed = "$days days";
            }
            return "since your last check-in $elapsed ago";
        }
    }

    private function stats($currentStreak, $longestStreak, $goalId)
    {
        $currentStreak = round($currentStreak, 1);
        $longestStreak = round($longestStreak, 1);

        $offset = (1 - ($currentStreak / $longestStreak)) * 300;
        
        return "<div class='stats donut graph$goalId'>
                <h2><span class='top'>$currentStreak</span>$longestStreak</h2>
                <svg width='120' height='120' xmlns='http://www.w3.org/2000/svg'>
                 <g>
                  <title>Layer 1</title>
                  <circle id='circle' class='circle_animation' r='47.7465' cy='60' cx='60' stroke-width='12' stroke='#BA0000' fill='none'/>
                 </g>
                </svg>
            </div>
            <style>
                .graph$goalId .circle_animation {
                  -webkit-animation: graph$goalId 1s ease-out forwards;
                  animation: graph$goalId 1s ease-out forwards;
                }
                @-webkit-keyframes graph$goalId { to { stroke-dashoffset: $offset; } }
                @keyframes graph$goalId { to { stroke-dashoffset: $offset; } }
            </style>";
    }

    private function reportDiv($goalVerb, $goalId, $daysSinceLastReport)
    {
        $periodPhrase = $this->periodPhrase($daysSinceLastReport);
        $reportDiv = "<div class='report'>Did you <em>$goalVerb</em> $periodPhrase?<div class='controls'>" .
            "<label class='success'><input type='radio' name='$goalId' value='1'> No</label>" .
            "<label class='setback'><input type='radio' name='$goalId' value='0'> Yes</label>" .
            "</div></div>";
        return $reportDiv;
    }
}