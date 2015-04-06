<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestHtmlGenerator extends HfTestCase {
    public function testCreateEmptyList() {
        $list = $this->factory->makeMarkupGenerator()->listMarkup(array());

        $this->assertEquals('<ul></ul>', $list);
    }

    public function testCreateListWithOneItem() {
        $list = $this->factory->makeMarkupGenerator()->listMarkup(array('item'));
        $expected = '<ul><li>item</li></ul>';

        $this->assertEquals($expected, $list);
    }

    public function testCreateListWithTwoItems() {
        $list = $this->factory->makeMarkupGenerator()->listMarkup(array('item 1', 'item 2', 'item 3'));
        $expected = '<ul><li>item 1</li><li>item 2</li><li>item 3</li></ul>';

        $this->assertEquals($expected, $list);
    }

    public function testHtmlGeneratorCreatesTabs() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();

        $contents = array(
            'duck1' => 'quack',
            'duck2' => 'quack, quack',
            'duck3' => 'quack, quack, quack'
        );

        $expected = '[su_tabs active="1"][su_tab title="duck1"]quack[/su_tab][su_tab title="duck2"]quack, quack[/su_tab][su_tab title="duck3"]quack, quack, quack[/su_tab][/su_tabs]';

        $result = $HtmlGenerator->tabs( $contents, 1 );

        $this->assertTrue( strstr( $result, $expected ) != false );
    }

    public function testHtmlGeneratorCreatesDifferentTabs() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();

        $contents = array(
            'duck1' => 'quack',
            'duck2' => 'quack, quack'
        );

        $expected = '[su_tabs active="2"][su_tab title="duck1"]quack[/su_tab][su_tab title="duck2"]quack, quack[/su_tab][/su_tabs]';

        $result = $HtmlGenerator->tabs( $contents, 2 );

        $isStringThere = ( strstr( $result, $expected ) != false );
        $this->assertTrue( $isStringThere );
    }

    public function testMakeError() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();
        $result = $HtmlGenerator->errorMessage('duck');
        $this->assertEquals("<p class='error'>duck</p>", $result);
    }

    public function testMakeSuccessMessage() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();
        $result = $HtmlGenerator->successMessage('duck');
        $this->assertEquals("<p class='success'>duck</p>", $result);
    }

    public function testMakeQuoteMessage() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();

        $MockQuotation = new stdClass();
        $MockQuotation->post_content = 'hello';
        $MockQuotation->post_title = 'Nathan';

        $result = $HtmlGenerator->quotation($MockQuotation);

        $this->assertEquals("<p class='quote'>\"hello\" — Nathan</p>", $result);
    }

    public function testMakeForm() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();
        $expected = '<form action=\'pond.net\' method=\'post\' name=\'waterform\'>duck</form>';
        $actual = $HtmlGenerator->form('pond.net', 'duck', 'waterform');
        $this->assertEquals($expected, $actual);
    }

    public function testMakeButton() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();
        $name = 'DUCK';
        $label = 'duck';
        $onclick = 'quack';
        $expected = "<input type='button' name='$name' value='$label' onclick='$onclick' />";
        $actual = $HtmlGenerator->buttonInput($name, $label, $onclick);
        $this->assertEquals($expected, $actual);
    }

    public function testMakeHiddenField() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();
        $actual = $HtmlGenerator->hiddenField('ghost');
        $expected = '<input type=\'hidden\' name=\'ghost\' />';
        $this->assertEquals($expected, $actual);
    }

    public function testMakeInfoMessage() {
        $HtmlGenerator = $this->factory->makeMarkupGenerator();
        $result = $HtmlGenerator->infoMessage('duck');
        $this->assertEquals("<p class='info'>duck</p>", $result);
    }

    public function testMakeRedirectScript() {
        $result = $this->mockedMarkupGenerator->redirectScript('duck');
        $this->assertEquals('<script>window.location.replace("duck");</script>', $result);
    }

    public function testMakeRefreshScript() {
        $this->setReturnValue($this->mockAssetLocator,'getCurrentPageUrl','duck');
        $result = $this->mockedMarkupGenerator->refreshScript();
        $this->assertEquals('<script>window.location.replace("duck");</script>', $result);
    }

    public function testMakeGoalCard() {
        $verb = 'Title';
        $goalDescription = 'Description';
        $goalId = 1;
        $daysSinceLastReport = 3;
        $levelBar = '';

        $result = $this->mockedMarkupGenerator->goalCard(
            $verb,
            $goalDescription,
            $goalId,
            $daysSinceLastReport,
            1,
            2,
            $levelBar
        );

        $reportDiv = $this->makeReportDiv($verb, 'since your last check-in <strong>3 days ago</strong>');
        $expected = $this->makeReportCard($reportDiv,1,2,$goalId);

        $this->assertEquals($expected, $result);
    }

    public function testMakeGoalCardDoesntIncludeEmptyDescriptionParagraph() {
        $verb = 'Title';
        $goalDescription = '';
        $goalId = 1;
        $daysSinceLastReport = 3;
        $levelBar = '';

        $result = $this->mockedMarkupGenerator->goalCard(
            $verb,
            $goalDescription,
            $goalId,
            $daysSinceLastReport,
            1,
            2,
            $levelBar
        );

        $reportDiv = $this->makeReportDiv($verb, 'since your last check-in <strong>3 days ago</strong>');
        $expected = $this->makeReportCard($reportDiv,1,2,$goalId);

        $this->assertEquals($result, $expected);
    }

    public function testMakeParagraphWithClass() {
        $result = $this->mockedMarkupGenerator->paragraph('duck','classy');
        $expected = "<p class='classy'>duck</p>";
        $this->assertEquals($expected, $result);
    }

    public function testMakeGoalCardDoesntSay1Days() {
        $verb = 'Title';
        $goalDescription = '';
        $goalId = 1;
        $daysSinceLastReport = 1;
        $levelBar = '';

        $result = $this->mockedMarkupGenerator->goalCard(
            $verb,
            $goalDescription,
            $goalId,
            $daysSinceLastReport,
            1,
            2,
            $levelBar
        );

        $reportDiv = $this->makeReportDiv($verb, 'since your last check-in <strong>1 day ago</strong>');
        $expected = $this->makeReportCard($reportDiv,1,2, $goalId);

        $this->assertEquals($result, $expected);
    }

    public function testMakeGoalCardSaysToday() {
        $goalTitle = 'Title';
        $goalDescription = '';
        $goalId = 1;
        $daysSinceLastReport = 0;
        $levelBar = '';

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalTitle,
            $goalDescription,
            $goalId,
            $daysSinceLastReport,
            1,
            2,
            $levelBar
        );

        $reportDiv = $this->makeReportDiv($goalTitle, 'since your last check-in <strong>less than a day ago</strong>');
        $expected = $this->makeReportCard($reportDiv, 1, 2,$goalId);

        $this->assertEquals($result, $expected);
    }

    public function testMakeGoalCardRecognizesNoReport() {
        $verb = 'Title';
        $goalDescription = '';
        $goalId = 1;
        $daysSinceLastReport = false;
        $levelBar = '';

        $result = $this->mockedMarkupGenerator->goalCard(
            $verb,
            $goalDescription,
            $goalId,
            $daysSinceLastReport,
            1,
            2,
            $levelBar
        );

        $reportDiv = $this->makeReportDiv($verb,'in the last <strong>24 hours</strong>');
        $expected = $this->makeReportCard($reportDiv,1,2,$goalId);

        $this->assertEquals($result, $expected);
    }

    public function testMakeGoalCardRoundsNumbers() {
        $verb = 'verb';
        $goalDescription = '';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $levelBar = '';

        $result = $this->mockedMarkupGenerator->goalCard(
            $verb,
            $goalDescription,
            $goalId,
            $daysSinceLastReport,
            1.111,
            2.222,
            $levelBar
        );

        $reportDiv = $this->makeReportDiv($verb, 'since your last check-in <strong>3 days ago</strong>');
        $expected = $this->makeReportCard($reportDiv,1.1,2.2,$goalId);

        $this->assertEquals($result, $expected);
    }

    private function makeReportDiv($verb, $periodPhrase)
    {
        $reportDiv = "<div class='report'>Did you <strong>$verb</strong> $periodPhrase?<div class='controls'>" .
            "<label class='success'><input type='radio' name='1' value='1'> No</label>" .
            "<label class='setback'><input type='radio' name='1' value='0'> Yes</label>" .
            "</div></div>";
        return $reportDiv;
    }

    private function makeReportCard($reportDiv, $currentStreak, $longestStreak, $goalId)
    {
        $stats = $this->makeStats($currentStreak, $longestStreak, $goalId);

        return "<div class='report-card'>$stats$reportDiv</div>";
    }

    public function testMakeGoalCardRecognizesOneDay() {
        $verb = 'verb';
        $goalDescription = '';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $levelBar = '';

        $result = $this->mockedMarkupGenerator->goalCard(
            $verb,
            $goalDescription,
            $goalId,
            $daysSinceLastReport,
            1,
            1,
            $levelBar
        );

        $reportDiv = $this->makeReportDiv($verb, 'since your last check-in <strong>3 days ago</strong>');
        $expected = $this->makeReportCard($reportDiv,1,1,$goalId);

        $this->assertEquals($result, $expected);
    }

    private function makeStats($currentStreak, $longestStreak, $goalId)
    {
        $offset = (1 - ($currentStreak / $longestStreak)) * 300;
        $isGlowing = $currentStreak == $longestStreak;
        if ($isGlowing) {
            $glowStyle = 'style="filter:url(#glow)"';
            $glowDef = "<defs><filter id='glow'><feGaussianBlur stdDeviation='5' result='coloredBlur' /><feMerge><feMergeNode in='coloredBlur' /><feMergeNode in='SourceGraphic' /></feMerge></filter></defs>";
        } else {
            $glowStyle = '';
            $glowDef = '';
        }

        $graph = "<div class='donut graph$goalId'><h2><span class='top' title='Current Streak'>$currentStreak</span><span title='Longest Streak'>$longestStreak</span></h2><svg width='120' height='120' xmlns='http://www.w3.org/2000/svg'>
                $glowDef
                 <g>
                  <title>Layer 1</title>
                  <circle id='circle' class='circle_animation' r='47.7465' cy='60' cx='60' stroke-width='12' stroke='#BA0000' fill='none' $glowStyle/>
                 </g>
                </svg></div>";
        $css = $this->donutCss($goalId, $offset);
        return "<div class='stats'>$graph$css</div>";
    }

    private function donutCss($goalId, $offset)
    {
        return "<style>
                .graph$goalId .circle_animation {
                  -webkit-animation: graph$goalId 1s ease-out forwards;
                  animation: graph$goalId 1s ease-out forwards;
                }
                @-webkit-keyframes graph$goalId { to { stroke-dashoffset: $offset; } }
                @keyframes graph$goalId { to { stroke-dashoffset: $offset; } }
            </style>";
    }
}
