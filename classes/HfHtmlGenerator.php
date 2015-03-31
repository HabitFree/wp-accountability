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

    public function generateTabs( $contents, $defaultTabNumber ) {
        $html = '[su_tabs active="' . $defaultTabNumber . '"]';

        foreach ( $contents as $title => $content ) {
            $html .= '[su_tab title="' . $title . '"]' . $content . '[/su_tab]';
        }

        return $this->Cms->expandShortcodes( $html . '[/su_tabs]' );
    }

    public function makeParagraph( $content, $classes = NULL ) {
        $properties = ($classes === NULL ? '' : " class='$classes'");
        return "<p$properties>$content</p>";
    }

    public function makeLink( $target, $content ) {
        return '<a href="' . $target . '">' . $content . '</a>';
    }

    public function makeList( $items ) {
        $html = '';
        foreach ( $items as $item ) {
            $html .= '<li>' . $item . '</li>';
        }

        return '<ul>' . $html . '</ul>';
    }

    public function makeErrorMessage( $content ) {
        return $this->makeParagraph($content, 'error');
    }

    public function makeSuccessMessage( $content ) {
        return $this->makeParagraph($content, 'success');
    }

    public function makeQuoteMessage( $quotation ) {
        return '<p class="quote">"' . $quotation->post_content . '" — ' . $quotation->post_title . '</p>';
    }

    public function makeInfoMessage( $content ) {
        return '<p class="info">' . $content . '</p>';
    }

    public function makeForm( $url, $content, $name ) {
        return '<form action="' . $url . '" method="post" name="' . $name . '">' . $content . '</form>';
    }

    public function makeButtonInput( $name, $label, $onclick ) {
        return '<input type="button" name="' . $name . '" value="' . $label . '" onclick="' . $onclick . '" />';
    }

    public function makeHiddenField( $name ) {
        return '<input type="hidden" name="' . $name . '" />';
    }

    public function makeRedirectScript($url) {
        return '<script>window.location.replace("'.$url.'");</script>';
    }

    public function makeRefreshScript() {
        $url = $this->assetLocator->getCurrentPageUrl();
        return $this->makeRedirectScript($url);
    }

    public function makeHeader($content, $level) {
        return "<h$level>$content</h$level>";
    }

    public function makeGoalCard(
        $goalVerb,
        $goalDescription,
        $goalId,
        $daysSinceLastReport,
        $currentStreak,
        $longestStreak,
        $progressBar
    ) {
        $periodPhrase = $this->makePeriodPhrase($daysSinceLastReport);

        return "<div class='report-card'>" .
        "<div class='main'>" .
        "<div class='report'>Did you <em>$goalVerb</em> $periodPhrase?<div class='controls'>" .
        "<label class='success'><input type='radio' name='$goalId' value='1'> No</label>" .
        "<label class='setback'><input type='radio' name='$goalId' value='0'> Yes</label>" .
        "</div></div></div>" .
        "<div class='stats'>" .
        "<p class='stat'>Current Streak <span class='number'>$currentStreak</span> Days</p>" .
        "<p class='stat'>Longest Streak <span class='number'>$longestStreak</span> Days</p>" .
        $progressBar .
        "</div></div>";
    }

    private function makePeriodPhrase($daysSinceLastReport)
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
}