<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGenericForm extends HfTestCase {
    // Helper Functions

    // Tests

    public function testHfFormClassExists() {
        $this->assertTrue( class_exists( 'HfGenericForm' ) );
    }

    public function testFormOuterTags() {
        $Form = new HfGenericForm( 'test.com', $this->MockHtmlGenerator );
        $html = $Form->getOutput();

        $this->assertEquals( $html, '<form action="test.com" method="post"></form>' );
    }

    public function testAddTextBoxInputToForm() {
        $Form  = new HfGenericForm( 'test.com', $this->MockHtmlGenerator );
        $name  = 'test';
        $label = 'Hello, there';

        $Form->addTextBox( $name, $label, '', false );

        $html = $Form->getOutput();

        $this->assertEquals( $html,
            '<form action="test.com" method="post"><p><label for="test">Hello, there: <input type="text" name="test" value="" /></label></p></form>'
        );
    }

    public function testAddSubmitButton() {
        $Form  = new HfGenericForm( 'test.com', $this->MockHtmlGenerator );
        $name  = 'submit';
        $label = 'Submit';

        $Form->addSubmitButton( $name, $label );

        $html = $Form->getOutput();

        $this->assertEquals( $html, '<form action="test.com" method="post"><p><input type="submit" name="submit" value="Submit" /></p></form>' );
    }

    public function testAddInfoBox() {
        $Form = new HfGenericForm('test.com', $this->MockHtmlGenerator);
        $Form->addInfoMessage('message');

        $result = $Form->getOutput();
        $expected = '<form action="test.com" method="post"><p class="info">message</p></form>';

        $this->assertEquals($expected, $result);
    }
}