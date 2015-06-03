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
        $result = $this->mockedMarkupGenerator->redirectScript('url');
        $this->assertEquals($this->redirectScript('url'), $result);
    }

    public function testMakeRefreshScript() {
        $this->setReturnValue($this->mockAssetLocator,'getCurrentPageUrl','url');
        $result = $this->mockedMarkupGenerator->refreshScript();
        $this->assertEquals($this->redirectScript('url'), $result);
    }

    public function testMakeGoalCard() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3;

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            0,
            array()
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>3 days</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,0,array());

        $this->assertEquals($expected, $result);
    }

    public function testMakeParagraphWithClass() {
        $result = $this->mockedMarkupGenerator->paragraph('duck','classy');
        $expected = "<p class='classy'>duck</p>";
        $this->assertEquals($expected, $result);
    }

    public function testMakeGoalCardDoesntSay1Days() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 1;

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            0,
            array()
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>day</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,0,array());

        $this->assertEquals($result, $expected);
    }

    public function testMakeGoalCardSaysToday() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 0;

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            0,
            array()
        );

        $reportDiv = $this->makeReportDiv($verb, 'since your <span class=\'duration\'><strong>last check-in</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv, 0, array());

        $this->assertEquals($result, $expected);
    }

    public function testMakeGoalCardRecognizesNoReport() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = false;

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            0,
            array()
        );

        $reportDiv = $this->makeReportDiv($verb,'in the last <span class=\'duration\'><strong>24 hours</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,0,array());

        $this->assertEquals($result, $expected);
    }

    public function testMakeGoalCardRoundsNumbers() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            0,
            array()
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>3 days</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,0,array());

        $this->assertEquals($result, $expected);
    }

    public function testRoundsStreakLength() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $streaks = array(1,2,3,0);

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            0,
            $streaks
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>3 days</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,0,$streaks);

        $this->assertEquals($expected, $result);
    }

    private function makeReportDiv($verb, $periodPhrase)
    {
        $controls = "<div class='controls'>" .
            "<label class='success'><input type='radio' name='1' value='1'> No</label>" .
            "<label class='setback'><input type='radio' name='1' value='0'> Yes</label>" .
            "</div>";
        $reportDiv = "<div class='report'>Did you <strong class='verb'>$verb</strong> $periodPhrase$controls</div>";
        return $reportDiv;
    }

    private function makeReportCard($reportDiv, $currentStreak, $streaks)
    {
        $stats = $this->makeStats($currentStreak,$streaks);

        return "<div class='report-card'>$stats$reportDiv</div>";
    }

    private function makeStats($currentStreak,$streaks)
    {
        $streaks = array_slice($streaks,-10);
        $rows = $this->makeRows($currentStreak,$streaks);

        $header = '<h4>Personal Bests</h4>';
        $body = "<tbody>$rows</tbody>";
        $table = "<table>$body</table>";

        return $header . $table;
    }

    private function redirectScript($url)
    {
        return "<script>window.location.replace('$url');</script>";
    }

    public function testUsesDaysWord() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $streaks = array(array('length'=>3.3333,'date'=>'date','rank'=>'rank'));

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            0,
            $streaks
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>3 days</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,0,$streaks);

        $this->assertEquals($expected, $result);
    }

    private function makeRows($currentStreak, $streaks)
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
                $row = $this->makeRow($streak, true);
                $currentStreak = null;
            } else {
                $row = $this->makeRow($streak, false);
            }

            $rows .= $row;
        }
        return $rows;
    }

    private function makeRow($streak, $isCurrent)
    {
        $lengthPhrase = $this->streakPhrase($streak[1]);
        $class = ($isCurrent) ? ' class="current"' : '';
        $row = "<tr$class><td class='rank'>{$streak[0]}</td><td>$lengthPhrase</td></tr>";
        return $row;
    }

    private function streakPhrase($length)
    {
        $d = round($length,1);
        return ($d != 1) ? "$d days" : "$d day";
    }

    public function testSaysSingleDay() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $streaks = array(array('length'=>1,'date'=>'date','rank'=>'rank'));

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            0,
            $streaks
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>3 days</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,0,$streaks);

        $this->assertEquals($expected, $result);
    }

    public function testOnlyShowsFiveStreaks() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $currentStreak = 1;
        $streaks = array(1,1,1,1,1,1);

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            $currentStreak,
            $streaks
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>3 days</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,$currentStreak,$streaks);

        $this->assertEquals($expected,$result);
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

    public function testSortsStreaks() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $currentStreak = 1;
        $streaks = array(3,1,7,2,4);

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            $currentStreak,
            $streaks
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>3 days</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,$currentStreak,$streaks);

        $this->assertEquals($expected,$result);
    }

    public function testTrimsStreaksRelativeToCurrentStreak() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $currentStreak = 1;
        $streaks = array(3,1,7,2,4,100);

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            $currentStreak,
            $streaks
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>3 days</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,$currentStreak,$streaks);

        $this->assertEquals($expected,$result);
    }

    public function testRanksStreaks() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $currentStreak = 1;
        $streaks = array(1,1,1,1,1);

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            $currentStreak,
            $streaks
        );

        $reportDiv = $this->makeReportDiv($verb, 'in the last <span class=\'duration\'><strong>3 days</strong>?</span>');
        $expected = $this->makeReportCard($reportDiv,$currentStreak,$streaks);

        $this->assertEquals($expected,$result);
    }
}
