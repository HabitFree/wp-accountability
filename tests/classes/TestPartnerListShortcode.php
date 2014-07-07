<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestPartnerListShortcode extends HfTestCase {
    public function testPartnerListShortcodeImplementsShortcodeInterface() {
        $PartnerListShortcode = new HfPartnerListShortcode();
        $this->assertTrue( $this->classImplementsInterface( $PartnerListShortcode, 'Hf_iShortcode' ) );
    }
}
