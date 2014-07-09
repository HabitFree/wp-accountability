<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestHtmlGenerator extends HfTestCase {
    // Helper Functions

    // Tests

    public function testCreateEmptyList() {
        $list = $this->Factory->makeMarkupGenerator()->makeList(array());

        $this->assertEquals('<ul></ul>', $list);
    }

    public function testCreateListWithOneItem() {
        $list = $this->Factory->makeMarkupGenerator()->makeList(array('item'));
        $expected = '<ul><li>item</li></ul>';

        $this->assertEquals($expected, $list);
    }

    public function testCreateListWithTwoItems() {
        $list = $this->Factory->makeMarkupGenerator()->makeList(array('item 1', 'item 2', 'item 3'));
        $expected = '<ul><li>item 1</li><li>item 2</li><li>item 3</li></ul>';

        $this->assertEquals($expected, $list);
    }

    public function testHtmlGeneratorCreatesTabs() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();

        $contents = array(
            'duck1' => 'quack',
            'duck2' => 'quack, quack',
            'duck3' => 'quack, quack, quack'
        );

        $expected = '[su_tabs active="1"][su_tab title="duck1"]quack[/su_tab][su_tab title="duck2"]quack, quack[/su_tab][su_tab title="duck3"]quack, quack, quack[/su_tab][/su_tabs]';

        $result = $HtmlGenerator->generateTabs( $contents, 1 );

        $this->assertTrue( strstr( $result, $expected ) != false );
    }

    public function testHtmlGeneratorCreatesDifferentTabs() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();

        $contents = array(
            'duck1' => 'quack',
            'duck2' => 'quack, quack'
        );

        $expected = '[su_tabs active="2"][su_tab title="duck1"]quack[/su_tab][su_tab title="duck2"]quack, quack[/su_tab][/su_tabs]';

        $result = $HtmlGenerator->generateTabs( $contents, 2 );

        $isStringThere = ( strstr( $result, $expected ) != false );
        $this->assertTrue( $isStringThere );
    }

    public function testMakeError() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();
        $result = $HtmlGenerator->makeError('duck');
        $this->assertEquals('<p class="error">duck</p>', $result);
    }

    public function testMakeSuccessMessage() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();
        $result = $HtmlGenerator->makeSuccessMessage('duck');
        $this->assertEquals('<p class="success">duck</p>', $result);
    }

    public function testMakeQuoteMessage() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();

        $MockQuotation = new stdClass();
        $MockQuotation->post_content = 'hello';
        $MockQuotation->post_title = 'Nathan';

        $result = $HtmlGenerator->makeQuoteMessage($MockQuotation);

        $this->assertEquals('<p class="quote">"hello" — Nathan</p>', $result);
    }

    public function testMakeForm() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();
        $expected = '<form action="pond.net" method="post" name="waterform">duck</form>';
        $actual = $HtmlGenerator->makeForm('pond.net', 'duck', 'waterform');
        $this->assertEquals($expected, $actual);
    }

    public function testMakeButton() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();
        $name = 'DUCK';
        $label = 'duck';
        $onclick = 'quack';
        $expected = '<input type="button" name="'.$name.'" value="'.$label.'" onclick="'.$onclick.'" />';
        $actual = $HtmlGenerator->makeButton($name, $label, $onclick);
        $this->assertEquals($expected, $actual);
    }

    public function testMakeHiddenField() {
        $HtmlGenerator = $this->Factory->makeMarkupGenerator();
        $actual = $HtmlGenerator->makeHiddenField('ghost');
        $expected = '<input type="hidden" name="ghost" />';
        $this->assertEquals($expected, $actual);
    }
}
