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
}
