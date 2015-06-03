<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfHtmlGenerator implements Hf_iMarkupGenerator {
    private $cms;

    function __construct(
        Hf_iCms $contentManagementSystem,
        Hf_iAssetLocator $assetLocator
    ) {
        $this->cms = $contentManagementSystem;
        $this->assetLocator = $assetLocator;
    }

    public function tabs( $contents, $defaultTabNumber ) {
        $html = '[su_tabs active="' . $defaultTabNumber . '"]';

        foreach ( $contents as $title => $content ) {
            $html .= '[su_tab title="' . $title . '"]' . $content . '[/su_tab]';
        }

        return $this->cms->expandShortcodes( $html . '[/su_tabs]' );
    }

    public function element($tag, $content, $properties = array()) {
        $propertyString = $this->parseProperties($properties);
        return ($content !== null) ? "<$tag$propertyString>$content</$tag>" : "<$tag$propertyString />";
    }

    public function span($content, $properties) {
        return $this->element('span', $content, $properties);
    }

    public function paragraph( $content, $classes = NULL ) {
        $properties = ($classes) ? array('class'=>$classes) : array();
        return $this->element('p',$content,$properties);
    }

    public function div( $content, $classes = NULL ) {
        $properties = ($classes) ? array('class'=>$classes) : array();
        return $this->element('div',$content,$properties);
    }

    public function linkMarkup( $target, $content ) {
        $properties = array('href'=>$target);
        return $this->element('a',$content,$properties);
    }

    public function listMarkup( $items ) {
        $html = '';
        foreach ( $items as $item ) {
            $html .= $this->element('li',$item);
        }

        return $this->element('ul',$html);
    }

    public function errorMessage( $content ) {
        return $this->paragraph($content, 'error');
    }

    public function successMessage( $content ) {
        return $this->paragraph($content, 'success');
    }

    public function quotation( $quotation ) {
        $content = '"' . $quotation->post_content . '" — ' . $quotation->post_title;
        return $this->paragraph($content,'quote');
    }

    public function infoMessage( $content ) {
        return $this->paragraph($content, 'info');
    }

    public function form( $url, $content, $name ) {
        $properties = array('action'=>$url,'method'=>'post','name'=>$name);
        return $this->element('form',$content,$properties);
    }

    public function input($properties) {
        return $this->element('input',null,$properties);
    }

    public function buttonInput( $name, $label, $onclick ) {
        $properties = array('type'=>'button','name'=>$name, 'value'=>$label, 'onclick'=>$onclick);
        return $this->input($properties);
    }

    public function hiddenField( $name ) {
        $properties = array('type'=>'hidden','name'=>$name);
        return $this->input($properties);
    }

    public function redirectScript($url) {
        return $this->element('script',"window.location.replace('$url');");
    }

    public function refreshScript() {
        $url = $this->assetLocator->getCurrentPageUrl();
        return $this->redirectScript($url);
    }

    public function head($content, $level) {
        return $this->element("h$level",$content);
    }

    public function goalCard(
        $goalId,
        $goalVerb,
        $daysSinceLastReport,
        $currentStreak,
        $streaks
    ) {
        $stats = $this->stats($currentStreak,$streaks);
        $reportDiv = $this->reportDiv($goalVerb, $goalId, $daysSinceLastReport);

        return $this->div($stats.$reportDiv,'report-card');
    }

    private function periodPhrase($daysSinceLastReport)
    {
        if ($daysSinceLastReport === false) {
            $prefix = 'in the last';
            $time = '24 hours';
        } else {
            $days = round($daysSinceLastReport);
            if ($days == 0) {
                $prefix = 'since your';
                $time = 'last check-in';
            } else {
                $prefix = 'in the last';
                $time = ($days == 1) ? 'day' : "$days days";
            }
        }

        return $prefix .' <span class=\'duration\'><strong>'. $time .'</strong>?</span>';
    }

    private function stats($currentStreak,$streaks)
    {
        $header = '<h4>Personal Bests</h4>';
        $body = $this->statsTableBody($currentStreak,$streaks);
        $table = "<table>$body</table>";
        return $header . $table;
    }

    private function reportDiv($goalVerb, $goalId, $daysSinceLastReport)
    {
        $periodPhrase = $this->periodPhrase($daysSinceLastReport);
        $controls = $this->controls($goalId);
        $question = "Did you <strong class='verb'>$goalVerb</strong> $periodPhrase";
        return $this->div($question.$controls,'report');
    }

    private function parseProperties($properties)
    {
        $propertyString = '';
        foreach ($properties as $property => $value) {
            $propertyString .= " $property='$value'";
        }
        return $propertyString;
    }

    public function label($content, $properties) {
        return $this->element('label', $content, $properties);
    }

    private function reportButtons($goalId)
    {
        $successButton = $this->reportButton($goalId, 1, 'No', 'success');
        $setbackButton = $this->reportButton($goalId, 0, 'Yes', 'setback');

        return $successButton . $setbackButton;
    }

    private function controls($goalId)
    {
        $buttons = $this->reportButtons($goalId);
        return $this->div($buttons, 'controls');
    }

    private function reportButton($goalId, $value, $text, $class)
    {
        $buttonContent = "<input type='radio' name='$goalId' value='$value'> $text";
        return $this->label($buttonContent, array('class' => $class));
    }

    private function statsTableRows($currentStreak,$streaks)
    {
        rsort($streaks);

        $rankedStreaks = array();
        $rank = 1;
        foreach ($streaks as $streak) {
            $rankedStreaks[] = array($rank,$streak);
            $rank++;
        }

        $areaStreaks = $this->trimStreaksToNeighborhood($currentStreak, $rankedStreaks);

        $rows = '';
        foreach ($areaStreaks as $streak) {
            $isCurrent = $currentStreak === $streak[1];
            if ($isCurrent) {
                $rows .= $this->statsTableRow($streak,true);
                $currentStreak = null;
            } else {
                $rows .= $this->statsTableRow($streak,false);
            }

        }
        return $rows;
    }

    private function trimStreaksToNeighborhood($currentStreak, $streaks)
    {
        usort($streaks, function($a,$b) {
            return $a[0] - $b[0];
        });
        $len = count($streaks);
        $i = array_search($currentStreak, $streaks);
        if ($i < 3) {
            $streaks = array_slice($streaks, 0, 5);
            return $streaks;
        } elseif (($len - $i) < 3) {
            $streaks = array_slice($streaks, -5);
            return $streaks;
        } else {
            $streaks = array_slice($streaks, $i - 2, 5);
            return $streaks;
        }
    }

    private function statsTableBody($currentStreak,$streaks)
    {
        $rows = $this->statsTableRows($currentStreak,$streaks);
        $body = "<tbody>$rows</tbody>";
        return $body;
    }

    private function statsTableRow($streak,$isCurrent)
    {
        $lengthPhrase = $this->lengthPhrase($streak[1]);
        $class = ($isCurrent) ? ' class="current"' : '';
        return "<tr$class><td class='rank'>{$streak[0]}</td><td>$lengthPhrase</td></tr>";
    }

    private function lengthPhrase($length)
    {
        $d = round($length,1);
        return ($d != 1) ? "$d days" : "$d day";
    }
}