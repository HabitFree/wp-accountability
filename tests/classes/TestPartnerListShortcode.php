<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestPartnerListShortcode extends HfTestCase {
    public function testPartnerListShortcodeImplementsShortcodeInterface() {
        $PartnerListShortcode = new HfPartnerListShortcode($this->MockUserManager);
        $this->assertTrue( $this->classImplementsInterface( $PartnerListShortcode, 'Hf_iShortcode' ) );
    }

    public function testPartnerListShortcodeGetsPartners() {
        $this->expectOnce($this->MockUserManager, 'getPartners');
        $PartnerListShortcode = new HfPartnerListShortcode($this->MockUserManager);
        $PartnerListShortcode->getOutput();
    }
}
