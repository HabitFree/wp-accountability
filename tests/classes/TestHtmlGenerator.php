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
            50
        );

        $this->assertContains('in the last <span class=\'duration\'><strong>3 days</strong>?</span>', $result);
    }

    public function testMakeParagraphWithClass() {
        $result = $this->mockedMarkupGenerator->paragraph('duck','classy');
        $expected = "<p class='classy'>duck</p>";
        $this->assertEquals($expected, $result);
    }

    private function redirectScript($url)
    {
        return "<script>window.location.replace('$url');</script>";
    }

    public function testIncludesIdChartDivs() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $currentStreak = 1;
        $health = 50;

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            $currentStreak,
            $health
        );

        $needle = "<div id='chart1'>";

        $this->assertContains($needle, $result);
    }

    public function testIncludesIndividualChartInitScripts() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $currentStreak = 1;
        $health = 50;

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            $currentStreak,
            $health
        );

        $needle = "google.charts.setOnLoadCallback(drawChart1);";
        $this->assertContains($needle, $result);
    }

    public function testMultipliesHealthBy100() {
        $verb = 'Title';
        $goalId = 1;
        $daysSinceLastReport = 3.1415;
        $currentStreak = 1;
        $health = .5;

        $result = $this->mockedMarkupGenerator->goalCard(
            $goalId,
            $verb,
            $daysSinceLastReport,
            $currentStreak,
            $health
        );

        $needle = "50";
        $this->assertContains($needle, $result);
    }
}
