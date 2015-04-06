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

    public function tabs( $contents, $defaultTabNumber ) {
        $html = '[su_tabs active="' . $defaultTabNumber . '"]';

        foreach ( $contents as $title => $content ) {
            $html .= '[su_tab title="' . $title . '"]' . $content . '[/su_tab]';
        }

        return $this->Cms->expandShortcodes( $html . '[/su_tabs]' );
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

        return $this->div($stats.$reportDiv,'report-card');
    }

    private function periodPhrase($daysSinceLastReport)
    {
        if ($daysSinceLastReport === false) {
            return 'in the last <strong>24 hours</strong>';
        } else {
            $days = round($daysSinceLastReport);
            if ($days == 0) {
                $elapsed = 'less than a day';
            } else if ($days == 1) {
                $elapsed = '1 day';
            } else {
                $elapsed = "$days days";
            }
            return "since your last check-in <strong>$elapsed ago</strong>";
        }
    }

    private function stats($currentStreak, $longestStreak, $goalId)
    {
        $currentStreak = round($currentStreak, 1);
        $longestStreak = round($longestStreak, 1);

        $offset = (1 - ($currentStreak / $longestStreak)) * 300;
        $isGlowing = $currentStreak == $longestStreak;

        $graph = $this->donutGraph($currentStreak, $longestStreak, $goalId, $isGlowing);
        $style = $this->donutGraphCss($goalId, $offset);

        return $this->div($graph . $style,'stats');
    }

    private function reportDiv($goalVerb, $goalId, $daysSinceLastReport)
    {
        $periodPhrase = $this->periodPhrase($daysSinceLastReport);
        $controls = $this->controls($goalId);
        $question = "Did you <strong>$goalVerb</strong> $periodPhrase?";
        return $this->div($question.$controls,'report');
    }

    private function glowStyle($isGlowing)
    {
        $glowStyle = 'style="filter:url(#glow)"';
        return ($isGlowing) ? $glowStyle : '';
    }

    private function glowDefinition($isGlowing)
    {
        $glowDefinition = "<defs>
<filter id='glow'>
            <feGaussianBlur stdDeviation='5' result='coloredBlur'/>
            <feMerge>
                <feMergeNode in='coloredBlur'/>
                <feMergeNode in='SourceGraphic'/>
            </feMerge>
        </filter>
    </defs>";

        return ($isGlowing) ? $glowDefinition : '';
    }

    private function donutSvg($isGlowing)
    {
        $glowDef = $this->glowDefinition($isGlowing);
        $glowStyle = $this->glowStyle($isGlowing);

        return "<svg width='120' height='120' xmlns='http://www.w3.org/2000/svg'>
                $glowDef
                 <g>
                  <title>Layer 1</title>
                  <circle id='circle' class='circle_animation' r='47.7465' cy='60' cx='60' stroke-width='12' stroke='#BA0000' fill='none' $glowStyle/>
                 </g>
                </svg>";
    }

    private function parseProperties($properties)
    {
        $propertyString = '';
        foreach ($properties as $property => $value) {
            $propertyString .= " $property='$value'";
        }
        return $propertyString;
    }

    private function donutLabel($currentStreak, $longestStreak)
    {
        $top = $this->span($currentStreak, array('class' => 'top', 'title' => 'Current Streak'));
        $bottom = $this->span($longestStreak, array('title' => 'Longest Streak'));

        return $this->element('h2', $top . $bottom);
    }

    private function donutGraph($currentStreak, $longestStreak, $goalId, $isGlowing)
    {
        $graphSvg = $this->donutSvg($isGlowing);
        $label = $this->donutLabel($currentStreak, $longestStreak);

        return $this->div($label . $graphSvg, "donut graph$goalId");
    }

    private function donutGraphCss($goalId, $offset)
    {
        $style = "<style>
                .graph$goalId .circle_animation {
                  -webkit-animation: graph$goalId 1s ease-out forwards;
                  animation: graph$goalId 1s ease-out forwards;
                }
                @-webkit-keyframes graph$goalId { to { stroke-dashoffset: $offset; } }
                @keyframes graph$goalId { to { stroke-dashoffset: $offset; } }
            </style>";
        return $style;
    }

    private function reportButtons($goalId)
    {
        $successButton = "<label class='success'><input type='radio' name='$goalId' value='1'> No</label>";
        $setbackButton = "<label class='setback'><input type='radio' name='$goalId' value='0'> Yes</label>";
        return $successButton . $setbackButton;
    }

    private function controls($goalId)
    {
        $buttons = $this->reportButtons($goalId);
        return $this->div($buttons, 'controls');
    }
}